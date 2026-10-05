# CLI

## npm scripts (plugin development)

Run these from the repository root after `npm install --legacy-peer-deps`.

| Command | Purpose |
|---|---|
| `npm start` | Watch/build admin React assets (`wp-scripts start`) |
| `npm run build` | Production build into `admin/build/` |
| `npm run lint:js` | ESLint on `admin/src` |
| `npm run lint:php` | PHPCS using `phpcs.xml` (requires Composer PHPCS/WPCS) |
| `npm run test` | PHPUnit (`phpunit.xml.dist`; needs WordPress test suite) |
| `npm run e2e` | Playwright end-to-end tests |
| `npm run package` | Build zip at `dist/wp-telegram-post-notifier.zip` |

## Composer

```bash
composer install
composer test          # runs phpunit if configured
composer phpcs         # coding standards
```

## WP-CLI (on a WordPress site)

```bash
# Install from a release zip
wp plugin install https://github.com/VoxHash/WPTelegram-Post-Notifier/releases/latest/download/wp-telegram-post-notifier.zip --activate

# Activate if already present
wp plugin activate wp-telegram-post-notifier

# Inspect options (token is sensitive — do not share output)
wp option get wptpn_settings
```

The plugin does not ship a custom WP-CLI command namespace yet; management is via the admin UI and REST API (`wptpn/v1`).
