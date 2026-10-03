#!/usr/bin/env python3
"""Build deterministic WordPress installation ZIPs from the two source folders."""
from __future__ import annotations

import hashlib
import re
import subprocess
import sys
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
VERSIONS = {'float-space': '1.1.0', 'float-content': '1.0.0'}
ALLOWED = {'.php', '.css', '.js', '.json', '.svg', '.txt', '.md', '.png'}
TEXT = ALLOWED - {'.png'}

def build_package(parent: str, slug: str, main_file: str) -> Path:
    version = VERSIONS[slug]
    source = ROOT / parent / slug
    assert source.is_dir(), f'Missing source folder: {parent}/{slug}'
    header = (source / main_file).read_text(encoding='utf-8')
    assert re.search(r'Version:\s*' + re.escape(version) + r'\b', header), 'Version mismatch'
    target = ROOT / 'dist' / f'{slug}-{version}.zip'
    with zipfile.ZipFile(target, 'w', compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for item in sorted(source.rglob('*')):
            assert not item.is_symlink(), f'Symlink rejected: {item.relative_to(ROOT)}'
            if not item.is_file():
                continue
            relative = item.relative_to(source)
            assert item.suffix.lower() in ALLOWED or relative.as_posix() == 'LICENSE', f'Unexpected file type: {relative}'
            assert not any(part.startswith('.') for part in relative.parts), f'Hidden file rejected: {relative}'
            assert item.suffix.lower() != '.png' or relative.as_posix() == 'screenshot.png', f'Unexpected raster: {relative}'
            data = item.read_bytes()
            if item.suffix.lower() in TEXT or relative.as_posix() == 'LICENSE':
                data = data.decode('utf-8-sig').replace('\r\n', '\n').encode('utf-8')
            info = zipfile.ZipInfo(f'{slug}/{relative.as_posix()}', date_time=(1980, 1, 1, 0, 0, 0))
            info.create_system = 3
            info.external_attr = 0o100644 << 16
            info.compress_type = zipfile.ZIP_DEFLATED
            archive.writestr(info, data)
    with zipfile.ZipFile(target) as archive:
        assert archive.testzip() is None, 'Archive integrity check failed'
        assert f'{slug}/LICENSE' in archive.namelist(), 'Package license missing'
        assert f'{slug}/{main_file}' in archive.namelist(), 'Installation entry missing'
    return target

def main() -> None:
    subprocess.run([sys.executable, str(ROOT / 'tools/privacy-check.py')], cwd=ROOT, check=True)
    (ROOT / 'dist').mkdir(exist_ok=True)
    packages = [build_package('theme', 'float-space', 'style.css'), build_package('plugin', 'float-content', 'float-content.php')]
    sums = []
    for package in packages:
        subprocess.run([sys.executable, str(ROOT / 'tools/privacy-check.py'), '--archive', str(package)], cwd=ROOT, check=True)
        sums.append(f'{hashlib.sha256(package.read_bytes()).hexdigest()}  {package.name}')
    (ROOT / 'dist/SHA256SUMS.txt').write_text('\n'.join(sums) + '\n', encoding='utf-8')
    print('\n'.join(sums))

if __name__ == '__main__':
    main()
