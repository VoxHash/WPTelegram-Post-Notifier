# Getting Started

WP Telegram Post Notifier sends Telegram messages when WordPress posts (and optionally WooCommerce products) are published or updated.

## Who this is for

- WordPress site owners who want automated Telegram channel/group updates
- Agencies and publishers routing content by category, tag, or post type
- Developers extending delivery via REST, hooks, and Action Scheduler

## Prerequisites

| Requirement | Minimum |
|---|---|
| WordPress | 6.3+ |
| PHP | 7.4+ |
| MySQL / MariaDB | 5.6+ / 10.1+ |
| Telegram | Bot token from [@BotFather](https://t.me/BotFather) |
| Channel/chat access | Bot must be able to post (admin on channels) |

## Path to first notification

1. Install and activate the plugin — [Installation](installation.md)
2. Create a Telegram bot and add it to your channel — [Quick Start](quick-start.md)
3. Enter the bot token and destination chat ID under **Settings → Telegram Notifier**
4. Publish a test post and confirm the message in Telegram

## Development environment

```bash
git clone https://github.com/VoxHash/WPTelegram-Post-Notifier.git
cd WPTelegram-Post-Notifier
npm install --legacy-peer-deps
composer install
npm run build
```

Runtime secrets live in WordPress options (bot token), not in `.env` for production sites. For local development you still need a real bot token and chat ID to exercise Telegram APIs.

Next: [Quick Start](quick-start.md) or [Configuration](configuration.md).
