# WP Telegram Post Notifier

[![Build](https://img.shields.io/github/actions/workflow/status/VoxHash/WPTelegram-Post-Notifier/ci.yml)](https://github.com/VoxHash/WPTelegram-Post-Notifier/actions)
[![License](https://img.shields.io/github/license/VoxHash/WPTelegram-Post-Notifier)](LICENSE)
[![Release](https://img.shields.io/github/v/release/VoxHash/WPTelegram-Post-Notifier?sort=semver)](https://github.com/VoxHash/WPTelegram-Post-Notifier/releases)
[![Docs](https://img.shields.io/badge/docs-website-blue)](./docs/index.md)

> A production-grade WordPress plugin that posts to Telegram channels/chats when content changes. Features advanced templating, routing rules, async processing, and comprehensive admin interface.

## ✨ Features

- **Telegram Integration**: Send notifications to channels, groups, and private chats
- **Advanced Templating**: Customizable message templates with dynamic tokens
- **Routing Rules**: Route different content to different destinations based on categories, tags, or post types
- **Async Processing**: Non-blocking queue system using Action Scheduler
- **Comprehensive Logging**: Detailed logs with filtering and export capabilities
- **Health Monitoring**: Site Health integration for monitoring plugin status
- **Modern Admin Interface**: React-based admin interface with real-time testing and preview
- **WooCommerce Support**: Special support for product notifications and price changes

## 🧭 Table of Contents

- [Quick Start](#-quick-start)
- [Installation](#-installation)
- [Usage](#-usage)
- [Configuration](#-configuration)
- [Examples](#-examples)
- [Architecture](#-architecture)
- [Roadmap](#-roadmap)
- [Contributing](#-contributing)
- [License](#-license)

## 🚀 Quick Start

```bash
# 1) Install
npm install
composer install

# 2) Build
npm run build

# 3) Run tests
npm run test
```

## 💿 Installation

See [docs/installation.md](docs/installation.md) for platform-specific steps.

## 🛠 Usage

Basic usage here. Advanced usage in [docs/usage.md](docs/usage.md).

```bash
# Build production assets
npm run build

# Run tests
npm run test
npm run e2e

# Package plugin
npm run package
```

## ⚙️ Configuration

- Minimal config in this README
- Full reference: [docs/configuration.md](docs/configuration.md)

| Variable | Description | Default |
|---|---|---|
| `WPTPN_BOT_TOKEN` | Telegram bot token | — |
| `WPTPN_DESTINATIONS` | Array of destination chat IDs | `[]` |
| `WPTPN_TEMPLATE` | Message template with tokens | Default template |

## 📚 Examples

- Start here: [docs/examples/example-01.md](docs/examples/example-01.md)
- More: [docs/examples/](docs/examples/)

## 🧩 Architecture

High-level overview: The plugin uses WordPress hooks to detect post changes, processes notifications asynchronously via Action Scheduler, and sends messages to Telegram using the Telegram Bot API. See [docs/architecture.md](docs/architecture.md).

## 🗺 Roadmap

Planned milestones live in [ROADMAP.md](ROADMAP.md). For changes, see [CHANGELOG.md](CHANGELOG.md).

## 🤝 Contributing

We welcome PRs! Please read [CONTRIBUTING.md](CONTRIBUTING.md) and follow the PR template.

## 🔒 Security

Please report vulnerabilities via [SECURITY.md](SECURITY.md).

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
