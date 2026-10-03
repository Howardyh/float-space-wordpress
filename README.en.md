# FLOAT Space

A lightweight classic WordPress portfolio theme with large typography, restrained pointer motion, Chinese/English content, and dark/light preferences. The frontend uses plain HTML, CSS and JavaScript with no build framework.

This public edition contains generic copy and original SVG artwork. It includes a separate optional content plugin. It contains no production configuration, personal contact details, registration numbers, accounts, databases, deployment scripts or third-party game images.

![Public theme preview](theme/float-space/screenshot.png)

## Install

Requires WordPress 6.5+ and PHP 8.1+. Download the installable ZIP files from this repository's **v1.1.0 Release**. This release contains theme version 1.1.0 and the unchanged companion plugin version 1.0.0. GitHub's source-code ZIP is not a theme installation package.

1. Upload and activate `float-space-1.1.0.zip` in Appearance → Themes → Add New.
2. For project management, bilingual content and optional collections, upload and activate `float-content-1.0.0.zip` in Plugins → Add New.
3. Use the companion plugin's setup screen or create pages manually and select the static homepage and posts page in Settings → Reading.
4. Edit homepage copy, optional GitHub/contact links and optional registration links in Settings → FLOAT 网站文字. Configure your site logo and navigation in Appearance.

Plugin activation does not import projects, create users, migrate accounts or overwrite the site name. Optional fictional examples are intended only to demonstrate layout; review your own content before publishing.

## Features

- Native posts, separate project content, and bilingual title/excerpt/body fields with a Chinese fallback.
- Editable homepage, custom logo and navigation. Personal links and registration details are blank by default.
- Hierarchical navigation, project descriptions and current-section indicators. A drawer replaces the desktop row when space is limited, and includes language/theme controls on mobile.
- Browser-local language/theme preferences, keyboard navigation and reduced-motion support.
- Optional configuration collection with fictional original assets, local drafts, import/export, undo and copy controls. The examples do not represent a real game or valid game codes.
- Capability checks, nonces, field validation and publication checks in the companion plugin. Public configuration endpoints are read-only.

The theme supports the homepage and native posts without the plugin. Projects and collections require the companion plugin. Deactivation does not delete stored content.

## Configure navigation

Create a menu in Appearance → Menus and assign it to the primary navigation location. Drag items to set their order and parent relationships. Nested levels are preserved; two levels are recommended for common navigation. The footer menu displays the first level only.

Expand a menu item to edit **English label**, **Chinese description**, and **English description**. A blank English label uses the original name, with built-in translations for common default sections. Descriptions appear on submenu links; the native WordPress Description field can also provide the Chinese fallback. Save the menu to apply changes. These fields work without the companion plugin.

When no primary menu is assigned, the theme builds a default navigation from site content. The project submenu shows up to five public, non-password-protected projects and a View all projects link. Notes and optional collections follow the site's configuration.

The parent text link opens its destination; a separate arrow button expands its submenu. Tab follows the page order, arrow keys can enter submenus, and Escape closes one level and returns focus to its button. The mobile panel scrolls independently; closing it with Escape or its background button returns focus to the menu button. Links remain accessible without JavaScript. The desktop header becomes compact after scrolling, and the system reduced-motion preference disables navigation transitions. Search is deferred to a future version.

## Privacy and licensing

No analytics, tracking, remote monitoring connection or automatic draft upload is included. Contact and registration information intentionally entered into public settings is displayed to visitors. Never put secrets into public content fields. See [privacy](docs/PRIVACY.md) and [asset provenance](docs/ASSETS.md).

All bundled SVG graphics are original. Code and graphics are GPL-2.0-or-later. No third-party game artwork, hardware photography or fonts are bundled.

## Build

Requires Python 3.10+; no frontend build step is required.

```sh
python tools/privacy-check.py
python tools/build-release.py
python -m unittest discover -s tests -p "test_*.py"
```

`dist/` contains two installable ZIP files and SHA-256 checksums. Packaging is restricted to the theme and plugin subdirectories. Manually review source and screenshots before release; automated scanners cannot prove the absence of every form of personal information.

See [development instructions](docs/DEVELOPMENT.md) for isolated WordPress tests. This is a GitHub release, not a WordPress.org theme-directory approval.

Licensed under GPL-2.0-or-later; see [LICENSE](LICENSE).
