"""Synthetic publication-gate checks; fixtures never contain real identities."""

import json
from pathlib import Path
import subprocess
import struct
import sys
import tempfile
import unittest
import zipfile
import zlib


TOOL = Path(__file__).resolve().parents[1] / "tools" / "privacy-check.py"
TEST_WORKDIR = TOOL.parents[1] / ".cache" / "privacy-tests"


def temporary_directory():
    # Keep disposable fixtures in the repository's excluded cache directory.
    TEST_WORKDIR.mkdir(parents=True, exist_ok=True)
    directory = tempfile.TemporaryDirectory(dir=TEST_WORKDIR)
    if not Path(directory.name).resolve().is_relative_to(TEST_WORKDIR.resolve()):
        raise ValueError("Temporary fixture escaped its intended directory.")
    return directory


class PrivacyGateTests(unittest.TestCase):
    def scan(self, root, *extra):
        result = subprocess.run([sys.executable, str(TOOL), "--root", str(root), "--format", "json", *extra], capture_output=True, text=True, check=False)
        return result, json.loads(result.stdout)

    def fixture(self, root, name, text):
        target = Path(root) / name
        target.parent.mkdir(parents=True, exist_ok=True)
        target.write_text(text, encoding="utf-8")

    def test_generic_source_is_public(self):
        with temporary_directory() as root:
            self.fixture(root, "theme/style.css", "/* Theme Name: Example Portfolio */\n/* https://example.org/ */\n")
            self.fixture(root, "README.md", "Contact: author@example.com\nCopyright: 123+sample@users.noreply.github.com\nLocal preview: http://127.0.0.1:8080\nGPL: https://www.gnu.org/licenses/gpl-2.0.html\n")
            self.fixture(root, ".github/workflows/check.yml", "name: Checks\n")
            self.fixture(root, ".env.example", 'DB_PASSWORD="change-me"\n')
            result, report = self.scan(root)
            self.assertEqual(result.returncode, 0)
            self.assertEqual(report["status"], "PASS")

    def test_private_key_and_token_are_redacted(self):
        with temporary_directory() as root:
            key = "-----BEGIN " + "PRIVATE KEY-----"
            token = "ghp" + "_" + "A" * 36
            self.fixture(root, "unexpected.txt", key + "\n" + token + "\n")
            result, report = self.scan(root)
            self.assertEqual(result.returncode, 1)
            self.assertEqual({item["rule"] for item in report["findings"]}, {"private-key", "github-token"})
            self.assertNotIn(key, result.stdout)
            self.assertNotIn(token, result.stdout)

    def test_operational_files_rejected_without_reading(self):
        with temporary_directory() as root:
            self.fixture(root, "ops/nginx.conf", "generic operational config")
            self.fixture(root, "wp-config.php", "not read")
            self.fixture(root, ".env.production", "not read")
            self.fixture(root, "server-backup.sql", "not read")
            result, report = self.scan(root)
            self.assertEqual(result.returncode, 1)
            rules = {item["rule"] for item in report["findings"]}
            self.assertTrue({"operational-directory", "operational-file", "dot-secret-file", "credential-data-or-archive-file"}.issubset(rules))
            self.assertEqual(report["files_scanned"], 0)

    def test_private_denylist_is_not_echoed(self):
        with temporary_directory() as root, temporary_directory() as private:
            identifying_term = "synthetic" + "-owner-identity"
            denylist = Path(private) / "terms.txt"
            denylist.write_text(identifying_term + "\n", encoding="utf-8")
            self.fixture(root, "theme/logo.svg", '<svg><title>' + identifying_term.upper() + "</title></svg>")
            result, report = self.scan(root, "--denylist", str(denylist))
            self.assertEqual(result.returncode, 1)
            self.assertEqual(report["findings"][0]["rule"], "personal-denylist")
            self.assertNotIn(identifying_term, result.stdout.lower())
            self.assertNotIn(str(denylist), result.stdout)

    def test_live_email_ip_and_database_password_rejected(self):
        with temporary_directory() as root:
            email = "person" + "@" + "synthetic.invalid"
            address = "203.0." + "113.7"
            password = "synthetic" + "-credential"
            credential_name = "DB_" + "PASSWORD"
            self.fixture(root, "config.txt", email + "\n" + address + "\n" + credential_name + '="' + password + '"\n')
            result, report = self.scan(root)
            self.assertEqual(result.returncode, 1)
            self.assertEqual({item["rule"] for item in report["findings"]}, {"personal-email", "nonloopback-ipv4", "hardcoded-credential"})
            for sensitive in (email, address, password):
                self.assertNotIn(sensitive, result.stdout)

    def test_define_and_unquoted_credentials_are_rejected(self):
        with temporary_directory() as root:
            credential_name = "DB_" + "PASSWORD"
            credential = "synthetic" + "-credential"
            self.fixture(root, "settings.php", "<?php define('" + credential_name + "', '" + credential + "');")
            self.fixture(root, ".env.example", credential_name + "=" + credential + "\n")
            result, report = self.scan(root)
            self.assertEqual(result.returncode, 1)
            self.assertEqual(len(report["findings"]), 2)
            self.assertTrue(all(item["rule"] == "hardcoded-credential" for item in report["findings"]))
            self.assertNotIn(credential, result.stdout)

    def test_raster_metadata_scanned_and_visual_review_required(self):
        with temporary_directory() as root:
            email = "person" + "@" + "synthetic.invalid"
            metadata = b"Author\x00" + email.encode("ascii")
            chunk = struct.pack(">I", len(metadata)) + b"tEXt" + metadata
            image = b"\x89PNG\r\n\x1a\n" + chunk + struct.pack(">I", zlib.crc32(b"tEXt" + metadata))
            (Path(root) / "screenshot.png").write_bytes(image)
            result, report = self.scan(root)
            self.assertEqual(result.returncode, 1)
            self.assertEqual(report["findings"][0]["rule"], "personal-email")
            self.assertEqual(report["raster_files_review_required"], ["screenshot.png"])
            self.assertNotIn(email, result.stdout)

    def test_wordpress_session_salt_and_password_hash_rejected(self):
        with temporary_directory() as root:
            salt_name = "AUTH_" + "KEY"
            salt = "synthetic" + "-session-salt"
            password_hash = "$" + "P$" + "B" * 31
            bearer = "Bearer " + "C" * 32
            self.fixture(root, "accidental-copy.txt", "define('" + salt_name + "', '" + salt + "');\n" + password_hash + "\n" + bearer)
            result, report = self.scan(root)
            self.assertEqual(result.returncode, 1)
            self.assertEqual({item["rule"] for item in report["findings"]}, {"hardcoded-credential", "password-hash", "bearer-token"})
            for sensitive in (salt, password_hash, bearer):
                self.assertNotIn(sensitive, result.stdout)

    def test_source_ignores_dist_and_git(self):
        with temporary_directory() as root:
            self.fixture(root, "README.md", "Example project")
            self.fixture(root, "dist/site.zip", "generated package")
            self.fixture(root, ".git/config", "local repository state")
            result, report = self.scan(root)
            self.assertEqual(result.returncode, 0)
            self.assertEqual(report["files_scanned"], 1)

    def test_archive_inspects_final_bytes_and_unsafe_members(self):
        with temporary_directory() as root:
            package = Path(root) / "release.zip"
            token = "github" + "_pat_" + "B" * 50
            with zipfile.ZipFile(package, "w") as archive:
                archive.writestr("theme/style.css", "/* generic */")
                archive.writestr("theme/leak.txt", token)
                archive.writestr("../outside.txt", "invalid package path")
                archive.writestr("theme/.git/config", "repository state")
            result = subprocess.run([sys.executable, str(TOOL), "--archive", str(package), "--format", "json"], capture_output=True, text=True, check=False)
            report = json.loads(result.stdout)
            self.assertEqual(result.returncode, 1)
            self.assertTrue({"github-token", "unsafe-path", "generated-or-repository-directory"}.issubset({item["rule"] for item in report["findings"]}))
            self.assertNotIn(token, result.stdout)


if __name__ == "__main__":
    unittest.main()
