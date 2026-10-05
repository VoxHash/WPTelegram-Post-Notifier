# Roadmap — WP Telegram Post Notifier

Current release: **1.0.0** (Telegram delivery, routing, templates, React admin, REST, Action Scheduler, logging, Site Health).

## Near term (1.1)

- Custom WP-CLI commands for test-send, health, and log inspection
- Ship or stub `admin/js/logs.js` / `admin/css/logs.css` so the Logs admin page has assets
- PHPUnit against WordPress test suite in CI (not only `php -l`)
- Harden ESLint/TypeScript toolchain so `npm run lint:js` is reliable in CI
- Reduce npm audit findings in the `@wordpress/scripts` toolchain where practical

## Mid term (1.2–1.x)

- Richer Telegram message types (documents, galleries, inline keyboards)
- Dashboard stats for delivery success/failure rates
- Conditional routing beyond category/tag/post type
- Template presets and import/export
- Multisite-aware settings and capability mapping

## Longer term

- Media optimization before Telegram upload
- Extension points / small add-on API
- Optional AI-assisted excerpts (opt-in, provider-configurable)
- Enterprise-oriented audit exports and SSO-friendly capability docs

## Explicitly not planned soon

- Native mobile companion app
- Replacing Action Scheduler with a custom queue

Feedback and proposals: open a [feature request](https://github.com/VoxHash/WPTelegram-Post-Notifier/issues/new?template=feature_request.md).
