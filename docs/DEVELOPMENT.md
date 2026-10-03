# Development

Use an isolated WordPress install for tests. Never run the integration fixture against an existing site: it inserts fictional pages, projects, notes and drafts and configures reading/permalink settings.

The theme and plugin have no frontend build step. Python builds deterministic installation ZIPs. Source syntax is checked with `php -l` and `node --check`; the privacy gate and its synthetic tests run with Python.

## Disposable WordPress Playground

Install Node.js 24.18+ with npm 11.16+ and use the official [WordPress Playground CLI documentation](https://developer.wordpress.org/playground/developers/local-development/wp-playground-cli/).

Build packages, then run the integration fixture without starting a browser. The fixture installs the ZIPs using WordPress's actual installation steps:

```sh
python tools/build-release.py
npx --yes @wp-playground/cli@3.1.56 run-blueprint --wp=latest --php=8.3 --mount=./dist:/packages --mount=./tests:/tests --blueprint=./tests/blueprint.json
```

For a local preview, replace `run-blueprint` with `server --port=9400 --workers=1`, then open `http://127.0.0.1:9400/`. The server uses a disposable WordPress/SQLite environment; it contains fictional fixtures only. Stop it with Ctrl+C. Windows paths with drive letters may be mounted with `--mount-dir <host-path> <virtual-path>`; relative paths avoid drive-letter ambiguity.

CI checks WordPress 6.5/PHP 8.1 and the latest stable WordPress/PHP 8.3. Browser checks should cover desktop/mobile widths, both languages and themes, keyboard navigation, reduced motion, custom menu/logo behavior, optional collection drafts and a plugin-disabled native-blog view. Keep screenshots and runtime output outside this repository, except the deliberately sanitized theme screenshot.

## Release

Review the complete staged tree, use a separate private denylist for personal identifiers, run checks, build packages and review their members. Use a commit identity that does not disclose a private email. Push only the public source repository. Publish both installation ZIPs and `SHA256SUMS.txt` after CI passes. Do not publish a live database, configuration or WordPress snapshot.
