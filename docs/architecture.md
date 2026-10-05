# Architecture

## High-level flow

1. WordPress publish/update hooks fire in the scheduler component.
2. Router selects destinations from settings and routing rules (category, tag, post type).
3. Template engine replaces tokens and escapes for MarkdownV2 / HTML / plain text.
4. Action Scheduler queues the send for non-blocking delivery.
5. Telegram client calls Bot API with rate limiting and retries.
6. Logger records success/failure; Site Health exposes status.

## Main components

| Component | Location | Role |
|---|---|---|
| Bootstrap | `wp-telegram-post-notifier.php` | Constants, requirements, autoload entry |
| Plugin | `includes/class-plugin.php` | Wires components and Action Scheduler |
| Scheduler | `includes/class-scheduler.php` | Hooks, queue, send lifecycle actions |
| Router | `includes/class-router.php` | Destination selection |
| Template | `includes/class-template.php` | Tokens, preview, parse modes |
| Telegram client | `includes/class-telegram-client.php` | Bot API, backoff |
| Logger / Health / Telemetry | `includes/` | Observability |
| Admin + REST | `admin/` | Settings UI and `wptpn/v1` API |
| Gutenberg | `public/class-gutenberg.php` | Per-post controls |
| Admin UI | `admin/src` → `admin/build` | React settings app |

## Async delivery

Action Scheduler (vendored under `vendor/action-scheduler/`) runs queued `wptpn` actions so publishing a post does not block on Telegram latency.

## Security boundaries

- Bot token stored in WordPress options; admin UI masks display
- REST and admin screens require `manage_wptpn`
- Inputs sanitized; outputs escaped (see [SECURITY.md](../SECURITY.md))

## Build artifacts

```bash
npm run build     # writes admin/build/index.js|css|asset.php
npm run package   # zips plugin into dist/wp-telegram-post-notifier.zip
```
