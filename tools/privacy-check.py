#!/usr/bin/env python3
"""Privacy publication gate for a source tree or a release ZIP.

The optional private denylist contains one identifying term per line. Keep that
file outside the repository. Findings deliberately omit matched values. Passing
this gate does not replace visual review of screenshots or artwork.
"""

from __future__ import annotations

import argparse
from dataclasses import asdict, dataclass
import ipaddress
import json
from pathlib import Path, PurePosixPath
import re
import stat
import struct
import sys
import zipfile
import zlib


EXCLUDED_DIRS = {
    ".git", "dist", ".cache", "cache", "__pycache__", ".pytest_cache",
    "node_modules", ".venv", "venv",
}
PRIVATE_DIRS = {
    ".ssh", ".aws", ".codex", ".agents", ".backups", "backups",
    "deployment", "ops", "verification",
}
PRIVATE_FILES = {
    "wp-config.php", "wp-config-sample.php", "id_rsa", "id_dsa",
    "id_ecdsa", "id_ed25519", "authorized_keys", "known_hosts",
    ".netrc", ".npmrc", ".pypirc", ".gitconfig", "credentials",
    "server-before.json", "server-after.json", "activation-state.json",
}
PRIVATE_SUFFIXES = {
    ".pem", ".key", ".p12", ".pfx", ".sql", ".sqlite", ".sqlite3",
    ".db", ".dump", ".log", ".bak", ".backup", ".zip", ".tar",
    ".tgz", ".gz", ".7z", ".rar",
}
RASTER_SUFFIXES = {".png", ".jpg", ".jpeg", ".webp", ".gif", ".ico"}
MAX_FILE_BYTES = 16 * 1024 * 1024
MAX_TOTAL_BYTES = 256 * 1024 * 1024
MAX_ARCHIVE_MEMBERS = 10000

TOKEN_PATTERNS = {
    "private-key": re.compile(r"-----BEGIN (?:RSA |EC |DSA |OPENSSH |ENCRYPTED )?PRIVATE KEY-----"),
    "github-token": re.compile(r"\b(?:gh[pousr]_[A-Za-z0-9]{20,}|github_pat_[A-Za-z0-9_]{40,})\b"),
    "provider-token": re.compile(r"\bsk-(?:(?:proj|svcacct)-)?[A-Za-z0-9_-]{24,}\b"),
    "stripe-secret": re.compile(r"\b(?:sk|rk)_(?:live|test)_[A-Za-z0-9]{16,}\b"),
    "cloud-access-key": re.compile(r"\b(?:AKIA|ASIA)[A-Z0-9]{16}\b|\bAKID[A-Za-z0-9]{16,}\b"),
    "jwt-token": re.compile(r"\beyJ[A-Za-z0-9_-]{10,}\.[A-Za-z0-9_-]{10,}\.[A-Za-z0-9_-]{10,}\b"),
    "bearer-token": re.compile(r"\bBearer\s+[A-Za-z0-9._~+/-]{24,}={0,2}", re.I),
    "password-hash": re.compile(r"\$[PH]\$[A-Za-z0-9./]{31}|\$2[aby]\$\d{2}\$[./A-Za-z0-9]{53}"),
    "credential-uri": re.compile(r"\b(?:mysql|mariadb|postgres(?:ql)?|mongodb(?:\+srv)?|redis)://[^\s/:]+:[^\s/@]+@", re.I),
}
EMAIL = re.compile(r"\b[A-Za-z0-9.!#$%&'*+/=?^_`{|}~-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b")
IPV4 = re.compile(r"(?<![\w.])(?:\d{1,3}\.){3}\d{1,3}(?![\w.])")
CREDENTIAL_NAME = r"(?:DB_PASSWORD|DB_PASS|MYSQL_PASSWORD|MYSQL_ROOT_PASSWORD|MARIADB_ROOT_PASSWORD|POSTGRES_PASSWORD|DATABASE_PASSWORD|DATABASE_URL|API_KEY|ACCESS_TOKEN|CLIENT_SECRET|AUTH_KEY|SECURE_AUTH_KEY|LOGGED_IN_KEY|NONCE_KEY|AUTH_SALT|SECURE_AUTH_SALT|LOGGED_IN_SALT|NONCE_SALT)"
CREDENTIAL_LITERALS = (
    re.compile(r"(?:['\"])?" + CREDENTIAL_NAME + r"(?:['\"])?\s*[:=]\s*(['\"])([^\r\n]*?)\1", re.I),
    re.compile(r"define\s*\(\s*(['\"])" + CREDENTIAL_NAME + r"\1\s*,\s*(['\"])([^\r\n]*?)\2", re.I),
    re.compile(r"^\s*(?:export\s+)?" + CREDENTIAL_NAME + r"\s*[:=]\s*([^\s'\"#][^\r\n#]*)", re.I | re.M),
)
PLACEHOLDER = re.compile(r"^(?:|example(?:[-_ ].*)?|placeholder|change[-_ ]?me|replace[-_ ].*|your[-_ ].*|<[^>]+>|\$\{[^}]+\}|null|none|false|(?:os\.(?:getenv|environ)|process\.env|getenv\(|env\().*)$", re.I)


@dataclass(frozen=True, order=True)
class Finding:
    file: str
    line: int
    rule: str


def allowed_email(value: str) -> bool:
    domain = value.rsplit("@", 1)[-1].lower()
    return domain in {"example.com", "example.org", "example.net", "users.noreply.github.com"}


def filename_rules(name: str, archive: bool = False) -> list[str]:
    path = PurePosixPath(name.replace("\\", "/"))
    parts = [part.lower() for part in path.parts]
    basename = parts[-1] if parts else ""
    rules = []
    if path.is_absolute() or ".." in parts or "\\" in name or ":" in name:
        rules.append("unsafe-path")
    if any(part in PRIVATE_DIRS for part in parts):
        rules.append("operational-directory")
    if archive and any(part in EXCLUDED_DIRS for part in parts):
        rules.append("generated-or-repository-directory")
    if basename in PRIVATE_FILES or basename.startswith("private_config"):
        rules.append("operational-file")
    if basename == ".env" or (basename.startswith(".env.") and basename not in {".env.example", ".env.sample", ".env.template"}):
        rules.append("dot-secret-file")
    if PurePosixPath(basename).suffix in PRIVATE_SUFFIXES or basename.startswith("id_") and basename.endswith(".pub"):
        rules.append("credential-data-or-archive-file")
    return rules


def decode_text(data: bytes) -> str | None:
    if data.startswith((b"\xff\xfe", b"\xfe\xff")):
        return data.decode("utf-16", errors="replace")
    if b"\x00" in data:
        return None
    return data.decode("utf-8-sig", errors="replace")


def raster_metadata(data: bytes) -> tuple[list[str], bool]:
    """Inspect common metadata containers, never infer visible image privacy."""
    texts, sensitive = [], False
    if data.startswith(b"\x89PNG\r\n\x1a\n"):
        offset = 8
        while offset + 12 <= len(data):
            size = struct.unpack(">I", data[offset:offset + 4])[0]
            kind = data[offset + 4:offset + 8]
            payload = data[offset + 8:offset + 8 + size]
            if len(payload) != size:
                break
            if kind in {b"eXIf"}:
                sensitive = True
            elif kind == b"tEXt":
                texts.append(payload.decode("latin-1", errors="replace"))
            elif kind == b"zTXt":
                try:
                    key, body = payload.split(b"\x00", 1)
                    decoder = zlib.decompressobj()
                    expanded = decoder.decompress(body[1:], MAX_FILE_BYTES)
                    if decoder.unconsumed_tail:
                        raise ValueError("metadata limit")
                    texts.append(key.decode("latin-1") + " " + expanded.decode("utf-8", errors="replace"))
                except (ValueError, zlib.error):
                    sensitive = True
            elif kind == b"iTXt":
                try:
                    key, body = payload.split(b"\x00", 1)
                    compressed = body[0]
                    language, translated, text = body[2:].split(b"\x00", 2)
                    if compressed:
                        decoder = zlib.decompressobj()
                        text = decoder.decompress(text, MAX_FILE_BYTES)
                        if decoder.unconsumed_tail:
                            raise ValueError("metadata limit")
                    texts.append((key + b" " + language + b" " + translated + b" " + text).decode("utf-8", errors="replace"))
                except (ValueError, IndexError, zlib.error):
                    sensitive = True
            offset += size + 12
    elif data.startswith(b"RIFF") and data[8:12] == b"WEBP":
        offset = 12
        while offset + 8 <= len(data):
            kind = data[offset:offset + 4]
            size = struct.unpack("<I", data[offset + 4:offset + 8])[0]
            if kind in {b"EXIF", b"XMP "}:
                sensitive = True
            offset += 8 + size + size % 2
    elif data.startswith(b"\xff\xd8"):
        sensitive = b"Exif\x00\x00" in data or b"http://ns.adobe.com/xap/" in data
    return texts, sensitive


class Scanner:
    def __init__(self, denylist: list[str]):
        self.denylist = [term.casefold() for term in denylist]
        self.findings: set[Finding] = set()
        self.scanned = 0
        self.total_bytes = 0
        self.rasters: list[str] = []

    def add(self, name: str, line: int, rule: str) -> None:
        self.findings.add(Finding(name, line, rule))

    def text(self, name: str, value: str, metadata: bool = False) -> None:
        def record(offset: int, rule: str) -> None:
            self.add(name, 0 if metadata else value.count("\n", 0, offset) + 1, rule)

        folded = value.casefold()
        for term in self.denylist:
            offset = folded.find(term)
            if offset >= 0:
                record(offset, "personal-denylist")
        for rule, pattern in TOKEN_PATTERNS.items():
            for match in pattern.finditer(value):
                record(match.start(), rule)
        for match in EMAIL.finditer(value):
            if not allowed_email(match.group()):
                record(match.start(), "personal-email")
        for match in IPV4.finditer(value):
            try:
                address = ipaddress.IPv4Address(match.group())
            except ipaddress.AddressValueError:
                continue
            if not address.is_loopback:
                record(match.start(), "nonloopback-ipv4")
        for pattern in CREDENTIAL_LITERALS:
            for match in pattern.finditer(value):
                literal = match.group(match.lastindex).strip()
                if not PLACEHOLDER.fullmatch(literal):
                    record(match.start(), "hardcoded-credential")

    def path(self, name: str, archive: bool) -> bool:
        rules = filename_rules(name, archive)
        for rule in rules:
            self.add(name, 0, rule)
        if any(term in name.casefold() for term in self.denylist):
            self.add(name, 0, "personal-denylist")
        return not rules

    def content(self, name: str, data: bytes) -> None:
        self.scanned += 1
        self.total_bytes += len(data)
        if self.total_bytes > MAX_TOTAL_BYTES:
            self.add(name, 0, "scan-total-size-limit")
            return
        if len(data) > MAX_FILE_BYTES:
            self.add(name, 0, "scan-file-size-limit")
            return
        if PurePosixPath(name).suffix.lower() in RASTER_SUFFIXES:
            self.rasters.append(name)
            texts, sensitive = raster_metadata(data)
            if sensitive:
                self.add(name, 0, "raster-private-or-unreadable-metadata")
            for value in texts:
                self.text(name, value, metadata=True)
            return
        value = decode_text(data)
        if value is None:
            self.add(name, 0, "unreviewable-binary-file")
        else:
            self.text(name, value)

    def source(self, root: Path) -> None:
        if not root.is_dir():
            raise ValueError("Source root is unavailable.")
        pending = [root]
        while pending:
            directory = pending.pop()
            for path in sorted(directory.iterdir()):
                name = path.relative_to(root).as_posix()
                if path.is_symlink():
                    self.add(name, 0, "symlink")
                    continue
                if path.is_dir():
                    if path.name.lower() in EXCLUDED_DIRS:
                        continue
                    if self.path(name, archive=False):
                        pending.append(path)
                elif path.is_file() and self.path(name, archive=False):
                    if path.stat().st_size > MAX_FILE_BYTES:
                        self.add(name, 0, "scan-file-size-limit")
                    else:
                        self.content(name, path.read_bytes())

    def archive(self, archive: Path) -> None:
        with zipfile.ZipFile(archive) as package:
            members = package.infolist()
            if len(members) > MAX_ARCHIVE_MEMBERS:
                raise ValueError("Archive contains too many entries.")
            names = set()
            total = 0
            for member in members:
                name = member.filename
                if name in names:
                    self.add(name, 0, "duplicate-archive-path")
                names.add(name)
                if not self.path(name, archive=True):
                    continue
                mode = (member.external_attr >> 16) & 0xFFFF
                if stat.S_ISLNK(mode):
                    self.add(name, 0, "symlink")
                    continue
                if member.is_dir():
                    continue
                total += member.file_size
                if member.flag_bits & 1:
                    self.add(name, 0, "encrypted-archive-member")
                elif member.file_size > MAX_FILE_BYTES or total > MAX_TOTAL_BYTES:
                    self.add(name, 0, "scan-archive-size-limit")
                else:
                    self.content(name, package.read(member))


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    inputs = parser.add_mutually_exclusive_group()
    inputs.add_argument("--root", type=Path, help="Source root; defaults to this tool's repository.")
    inputs.add_argument("--archive", type=Path, help="Scan the actual distributable ZIP instead of source.")
    parser.add_argument("--denylist", type=Path, help="Private newline-delimited identifying terms; never committed.")
    parser.add_argument("--format", choices=("text", "json"), default="text")
    args = parser.parse_args()
    try:
        denylist = []
        if args.denylist:
            try:
                denylist = [line.strip() for line in args.denylist.read_text(encoding="utf-8-sig").splitlines() if line.strip() and not line.lstrip().startswith("#")]
            except (OSError, UnicodeError):
                raise ValueError("Unable to read private denylist input.") from None
        scanner = Scanner(denylist)
        if args.archive:
            scanner.archive(args.archive)
        else:
            scanner.source((args.root or Path(__file__).resolve().parents[1]).resolve())
        report = {
            "status": "FAIL" if scanner.findings else "PASS",
            "files_scanned": scanner.scanned,
            "findings": [asdict(item) for item in sorted(scanner.findings)],
            "raster_files_review_required": sorted(scanner.rasters),
            "visual_review_note": "Raster files require visual review; this gate does not inspect visible image text.",
        }
        if args.format == "json":
            print(json.dumps(report, ensure_ascii=False, indent=2))
        else:
            print(f"Privacy gate: {report['status']} ({scanner.scanned} files scanned)")
            for finding in sorted(scanner.findings):
                print(f"{finding.file}:{finding.line}: {finding.rule}")
            if scanner.rasters:
                print(f"Visual review required for {len(scanner.rasters)} raster file(s).")
        return 1 if scanner.findings else 0
    except (OSError, ValueError, zipfile.BadZipFile, RuntimeError):
        # No input paths, private terms or offending data are echoed on errors.
        if args.format == "json":
            print(json.dumps({"status": "ERROR", "error": "Unable to safely scan the requested input."}))
        else:
            print("Privacy gate: ERROR. Unable to safely scan the requested input.")
        return 2


if __name__ == "__main__":
    sys.exit(main())
