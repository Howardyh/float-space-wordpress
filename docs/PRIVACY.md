# Privacy in the public edition

Default public content is generic. GitHub, contact email and registration links are empty until a site owner explicitly fills them in. Activation does not create users, migrate credentials, import a production database or change existing user profiles.

The theme has no analytics, tracking pixel, advertising integration or connection to a private monitoring service. Frontend assets are served from the installed theme. Preferences and optional drafts stay in the visitor's browser. Local draft JSON import/export works locally and does not publish the draft.

The optional plugin lets an authorized administrator import draft files and publish validated configuration records. Publishing deliberately makes selected records publicly readable. Drafts and password-protected records are excluded from the collection endpoint. Public project/article/page content is subject to WordPress visibility settings.

**Public fields are public:** names, contact email, links, registration labels, image captions, published posts and project metadata may appear in HTML, feeds or API responses. Do not use them to store credentials or sensitive infrastructure details. WordPress user profiles, other plugins and the hosting service have their own privacy behavior; review them before deployment.

The release repository excludes database files, private keys, environment files, uploads, production configuration, deployment logs and backups. The privacy gate checks source and ZIP members for common secret formats and unintended operational files. An optional private denylist supports checks for site-specific names and identifiers without adding them to the public repository:

```sh
python tools/privacy-check.py --denylist /path/outside/repository/denylist.txt
```

Do not commit this private denylist. Automated checks do not detect every identifier or sensitive image; review graphics and rendered screenshots manually before publishing. Use redacted examples when reporting issues.
