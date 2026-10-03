#!/usr/bin/env python3
"""Generate a Playground test Blueprint with explicit, checked runtime versions."""
from __future__ import annotations

import argparse
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / 'tests' / 'blueprint.json'


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--php', choices=('8.1', '8.3'), default='8.3')
    parser.add_argument('--wp', choices=('6.5', 'latest'), default='latest')
    parser.add_argument('--output', type=Path, required=True)
    args = parser.parse_args()
    output = args.output.resolve()
    if output == SOURCE.resolve():
        parser.error('--output must not overwrite the source Blueprint')

    blueprint = json.loads(SOURCE.read_text(encoding='utf-8'))
    blueprint['preferredVersions'] = {'php': args.php, 'wp': args.wp}
    constants = blueprint.setdefault('constants', {})
    constants['FLOAT_TEST_EXPECTED_PHP'] = args.php
    constants['FLOAT_TEST_EXPECTED_WP'] = args.wp
    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(json.dumps(blueprint, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
    print(f'Blueprint generated for PHP {args.php} / WordPress {args.wp}: {output}')


if __name__ == '__main__':
    main()
