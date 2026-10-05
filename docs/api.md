# API

Namespace: `wptpn/v1`  
Capability required: `manage_wptpn` (administrators receive this on activation)

Base URL: `/wp-json/wptpn/v1/`

## REST endpoints

| Method | Route | Description |
|---|---|---|
| `GET` | `/settings` | Read plugin settings |
| `POST` | `/settings` | Update plugin settings |
| `POST` | `/test-connection` | Validate bot token with Telegram `getMe` |
| `POST` | `/send-test` | Send a test message to a destination |
| `POST` | `/preview-template` | Preview template with parse modes |
| `POST` | `/send-now` | Queue/send notification for a post |
| `GET` | `/logs` | List delivery logs |
| `GET` | `/logs/stats` | Log statistics |
| `GET` | `/health` | Plugin health status |

Authenticate with a logged-in WordPress user that has `manage_wptpn`, and pass the REST nonce (`X-WP-Nonce` / `wp_rest`).

### Example: test connection

```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: YOUR_NONCE" \
  -b "wordpress_logged_in_COOKIE" \
  https://example.com/wp-json/wptpn/v1/test-connection
```

## Actions

### `wptpn_before_send`

Fired before a Telegram send. Arguments: `$payload` (array), `$context` (`post_id`, `chat_id`, `event`).

### `wptpn_after_send`

Fired after a successful send. Arguments: `$result`, `$context`.

### `wptpn_send_failed`

Fired when send fails. Arguments: `$error` (string), `$context`.

## Filters

### `wptpn_should_notify`

`(bool $should, WP_Post $post, string $event)` — return `false` to skip.

### `wptpn_destinations`

`(array $destinations, WP_Post $post)` — alter target chats.

### `wptpn_template_tokens`

`(array $tokens, WP_Post $post)` — add or override template tokens.

### `wptpn_http_args`

`(array $args, string $method, array $data)` — modify Telegram HTTP request args.
