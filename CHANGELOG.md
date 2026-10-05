# Changelog — WP Telegram Post Notifier

All notable changes to WP Telegram Post Notifier will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-10-05

### Added
- Complete Telegram Bot API integration with rate limiting, retries, and error handling
- Templating system with dynamic tokens and MarkdownV2 / HTML / plain parse modes
- Routing rules for category, tag, and post type destinations
- Async delivery via Action Scheduler
- Logging with filter/export support and WordPress Site Health checks
- React admin settings UI with connection test and template preview
- Gutenberg sidebar controls for per-post notification behavior
- REST API under `wptpn/v1` (settings, test, preview, send-now, logs, health)
- WooCommerce-oriented tokens (price, SKU, stock status)
- Standardized documentation kit (`README`, `docs/*`, contributing, security, support)
- GitHub issue/PR templates and CI workflows
- Composer tooling (`composer.json`) for PHPCS and PHPUnit

### Changed
- Admin webpack entry emits `admin/build/index.*` to match PHP enqueue paths
- Pinned `ajv@^8` so `@wordpress/scripts` builds succeed on modern npm/Node
- Clarified ROADMAP milestones against the shipped 1.0.0 feature set

### Fixed
- Build failure caused by `ajv` / `ajv-keywords` version mismatch during `npm run build`
- Admin asset URLs incorrectly prefixed with `assets/`, which broke enqueue of `admin/build/*`
- Webpack entry path so built files land at `admin/build/index.*` matching PHP enqueue
- Release packaging so Composer/npm toolchains are not bundled into the plugin zip
- Documentation kit drift (removed stray markdown outside the standard kit)
- Invalid PHPCS `parallel` argument in `phpcs.xml`

### Removed
- Non-kit markdown (`DEVELOPMENT_GOALS.md`, `GITHUB_TOPICS.md`, duplicate docs guides)
- Packaged zip artifacts from the git tree (`dist/` remains gitignored)

[Unreleased]: https://github.com/VoxHash/WPTelegram-Post-Notifier/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/VoxHash/WPTelegram-Post-Notifier/releases/tag/v1.0.0
