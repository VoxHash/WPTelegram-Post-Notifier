# Configuration

| Key | Type | Default | Description |
|---|---|---|---|
| `bot_token` | string | — | Telegram bot token from @BotFather |
| `destinations` | array | `[]` | Array of destination chat IDs |
| `template` | string | Default template | Message template with tokens |
| `parse_mode` | string | `MarkdownV2` | Parse mode: MarkdownV2, HTML, or None |
| `enable_logging` | boolean | `true` | Enable notification logging |
| `enable_updates` | boolean | `false` | Send notifications on post updates |
| `enable_scheduled` | boolean | `true` | Send notifications for scheduled posts |
| `rate_limit_delay` | integer | `1` | Delay between notifications in seconds |
