# Development

Use an isolated WordPress install for tests. Never run the integration fixture against an existing site: it inserts fictional pages, projects, notes and drafts and configures reading/permalink settings.

The theme and plugin have no frontend build step. Python builds deterministic installation ZIPs. Source syntax is checked with `php -l` and `node --check`; the privacy gate and its synthetic tests run with Python.

## Disposable WordPress Playground

Install Node.js 24.18+ with npm 11.16+ and use the official [WordPress Playground CLI documentation](https://developer.wordpress.org/playground/developers/local-development/wp-playground-cli/).

Build packages, generate a Blueprint for the required versions, then run the integration fixture without starting a browser. The fixture installs the ZIPs using WordPress's actual installation steps:

```sh
python tools/build-release.py
python tools/wordpress-blueprint.py --php=8.3 --wp=latest --output=./.cache/blueprint-latest-8.3.json
npx --yes @wp-playground/cli@3.1.56 run-blueprint --wp=latest --php=8.3 --mount=./dist:/packages --mount=./tests:/tests --blueprint=./.cache/blueprint-latest-8.3.json
```

For a local preview, replace `run-blueprint` with `server --port=9400 --workers=1`, then open `http://127.0.0.1:9400/`. The server uses a disposable WordPress/SQLite environment; it contains fictional fixtures only. Stop it with Ctrl+C. Windows paths with drive letters may be mounted with `--mount-dir <host-path> <virtual-path>`; relative paths avoid drive-letter ambiguity.

The Blueprint explicitly declares `preferredVersions` and matching `FLOAT_TEST_EXPECTED_PHP` / `FLOAT_TEST_EXPECTED_WP` constants. Playground CLI 3.1.56 can ignore command-line version overrides when reading a local Blueprint bundle, so the banner alone does not prove the actual runtime. The integration fixture checks the running PHP major/minor version and the requested WordPress version branch; `latest` is resolved by Playground and its actual version is recorded in the result. The checked-in Blueprint defaults to PHP 8.3/latest WordPress. To test the minimum versions, generate another with `--php=8.1 --wp=6.5`. The generator accepts these two PHP versions and WordPress 6.5 or latest, and refuses to overwrite the checked-in template.

CI generates a temporary Blueprint for each matrix entry and checks WordPress 6.5/PHP 8.1 and the latest stable WordPress/PHP 8.3. Browser checks should cover desktop/mobile widths, both languages and themes, custom menu/logo behavior, optional collection drafts and a plugin-disabled native-blog view. Keep screenshots and runtime output outside this repository, except the deliberately sanitized theme screenshot.

## Navigation checks

Navigation is implemented in `inc/navigation.php`, `assets/css/navigation.css`, and `assets/js/navigation.js` in the theme. It uses native links and nested lists, with separate disclosure buttons rather than application-menu roles. Menu hierarchy comes from WordPress; no content import is required. The optional English label and bilingual description fields use per-item nonces and the `edit_theme_options` capability.

Use fictional menu labels and project content in the isolated fixture. Check both the generated default menu and an assigned custom menu:

- Default projects include only published, non-password-protected records. Project detail pages identify the project section, and the View all projects link opens the archive.
- Custom menus preserve order and nested parent relationships. English labels and descriptions survive a menu save; blank English fields fall back to the original label. Descriptions appear on submenu links, while the footer remains a flat first-level list.
- Parent links navigate independently from their disclosure buttons. Tab, arrow keys, Escape, outside clicks, and focus leaving the navigation close or enter the appropriate level. Closing the mobile panel with Escape or its background button restores focus to the menu button.
- Test short and long custom labels at desktop widths, including the adaptive drawer when the normal row cannot fit. Resize with an open menu, switch language, and verify that hidden panels cannot receive keyboard focus.
- Check narrow phones, landscape layouts, administrator toolbar offsets, independent panel scrolling, and language/theme controls inside the panel. There should be no horizontal page overflow.
- Check the compact header after scrolling, reduced-motion preferences, and a JavaScript-disabled page with accessible navigation links.

Search is not included in the navigation release. Keep menu save checks isolated from any production menu or database.

## Release

Review the complete staged tree, use a separate private denylist for personal identifiers, run checks, build packages and review their members. Use a commit identity that does not disclose a private email. Push only the public source repository. The v1.1.0 release contains `float-space-1.1.0.zip`, the unchanged `float-content-1.0.0.zip`, and `SHA256SUMS.txt`; theme and plugin versions are tracked separately in the builder. Publish these files after CI passes. Do not publish a live database, configuration or WordPress snapshot.
