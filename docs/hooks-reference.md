# 🔗 Hooks Reference

This document describes all the hooks (actions and filters) available in WP Telegram Post Notifier.

## 📋 Table of Contents

- [Actions](#actions)
- [Filters](#filters)
- [Usage Examples](#usage-examples)
- [Best Practices](#best-practices)

## 🎯 Actions

### `wptpn_before_send`

Fired before sending a notification to Telegram.

**Parameters:**
- `$payload` (array) - Notification payload containing post, destination, event, message, and photo_url
- `$context` (array) - Context array with post_id, chat_id, and event

**Example:**
```php
add_action('wptpn_before_send', function($payload, $context) {
    // Log the notification attempt
    error_log('Sending notification for post ' . $context['post_id']);
    
    // Modify the message before sending
    $payload['message'] = 'URGENT: ' . $payload['message'];
}, 10, 2);
```

### `wptpn_after_send`

Fired after successfully sending a notification to Telegram.

**Parameters:**
- `$result` (array) - Telegram API response result
- `$context` (array) - Context array with post_id, chat_id, and event

**Example:**
```php
add_action('wptpn_after_send', function($result, $context) {
    // Log successful send
    error_log('Notification sent successfully to ' . $context['chat_id']);
    
    // Update post meta
    update_post_meta($context['post_id'], 'wptpn_last_sent', current_time('mysql'));
}, 10, 2);
```

### `wptpn_send_failed`

Fired when sending a notification fails.

**Parameters:**
- `$error` (string) - Error message
- `$context` (array) - Context array with post_id, chat_id, and event

**Example:**
```php
add_action('wptpn_send_failed', function($error, $context) {
    // Log the error
    error_log('Failed to send notification: ' . $error);
    
    // Send admin email
    wp_mail(get_option('admin_email'), 'Telegram Notification Failed', $error);
}, 10, 2);
```

## 🔧 Filters

### `wptpn_destinations`

Modify the list of destinations for a post before sending notifications.

**Parameters:**
- `$destinations` (array) - Array of destination configurations
- `$post` (WP_Post) - Post object

**Returns:** (array) Modified destinations array

**Example:**
```php
add_filter('wptpn_destinations', function($destinations, $post) {
    // Add special destination for featured posts
    if (get_post_meta($post->ID, '_featured', true)) {
        $destinations[] = [
            'chat_id' => '@featured_channel',
            'template_id' => 'featured',
            'name' => 'Featured Channel',
            'type' => 'featured'
        ];
    }
    
    return $destinations;
}, 10, 2);
```

### `wptpn_template_tokens`

Modify the available tokens for template rendering.

**Parameters:**
- `$tokens` (array) - Array of token values
- `$post` (WP_Post) - Post object

**Returns:** (array) Modified tokens array

**Example:**
```php
add_filter('wptpn_template_tokens', function($tokens, $post) {
    // Add custom token for post reading time
    $tokens['reading_time'] = calculate_reading_time($post->post_content);
    
    // Add custom token for post views
    $tokens['post_views'] = get_post_meta($post->ID, 'post_views', true) ?: '0';
    
    return $tokens;
}, 10, 2);
```

### `wptpn_should_notify`

Control whether a notification should be sent for a specific post and event.

**Parameters:**
- `$should_notify` (bool) - Whether to send notification
- `$post` (WP_Post) - Post object
- `$event` (string) - Event type (publish, update, etc.)

**Returns:** (bool) Whether to send notification

**Example:**
```php
add_filter('wptpn_should_notify', function($should_notify, $post, $event) {
    // Don't notify for posts in draft status
    if ($post->post_status !== 'publish') {
        return false;
    }
    
    // Don't notify for posts older than 1 hour
    if (strtotime($post->post_date) < time() - HOUR_IN_SECONDS) {
        return false;
    }
    
    return $should_notify;
}, 10, 3);
```

### `wptpn_http_args`

Modify HTTP request arguments before sending to Telegram API.

**Parameters:**
- `$args` (array) - WordPress HTTP request arguments
- `$endpoint` (string) - Telegram API endpoint
- `$payload` (array) - Request payload

**Returns:** (array) Modified HTTP arguments

**Example:**
```php
add_filter('wptpn_http_args', function($args, $endpoint, $payload) {
    // Add custom headers
    $args['headers']['X-Custom-Header'] = 'Custom Value';
    
    // Modify timeout for specific endpoints
    if ($endpoint === 'sendPhoto') {
        $args['timeout'] = 60; // Longer timeout for photo uploads
    }
    
    return $args;
}, 10, 3);
```

## 💡 Usage Examples

### Custom Destination Based on Post Meta

```php
add_filter('wptpn_destinations', function($destinations, $post) {
    $priority = get_post_meta($post->ID, '_priority', true);
    
    if ($priority === 'high') {
        $destinations[] = [
            'chat_id' => '@urgent_channel',
            'template_id' => 'urgent',
            'name' => 'Urgent Channel',
            'type' => 'priority'
        ];
    }
    
    return $destinations;
}, 10, 2);
```

### Custom Template Tokens

```php
add_filter('wptpn_template_tokens', function($tokens, $post) {
    // Add custom tokens
    $tokens['post_rating'] = get_post_meta($post->ID, '_rating', true) ?: 'N/A';
    $tokens['post_category_count'] = count(get_the_category($post->ID));
    $tokens['post_word_count'] = str_word_count(strip_tags($post->post_content));
    
    return $tokens;
}, 10, 2);
```

### Conditional Notifications

```php
add_filter('wptpn_should_notify', function($should_notify, $post, $event) {
    // Only notify during business hours
    $current_hour = (int) date('H');
    if ($current_hour < 9 || $current_hour > 17) {
        return false;
    }
    
    // Don't notify on weekends
    if (date('N') >= 6) {
        return false;
    }
    
    return $should_notify;
}, 10, 3);
```

## 🎯 Best Practices

### 1. Always Use Proper Priority and Parameter Count
```php
// Good
add_action('wptpn_before_send', 'my_callback', 10, 2);

// Bad
add_action('wptpn_before_send', 'my_callback');
```

### 2. Sanitize and Validate Data
```php
add_filter('wptpn_template_tokens', function($tokens, $post) {
    // Sanitize custom token
    $tokens['custom_field'] = sanitize_text_field(
        get_post_meta($post->ID, '_custom_field', true)
    );
    
    return $tokens;
}, 10, 2);
```

### 3. Use Descriptive Function Names
```php
// Good
add_action('wptpn_before_send', 'log_notification_attempt', 10, 2);

// Bad
add_action('wptpn_before_send', 'my_function', 10, 2);
```

### 4. Test Your Hooks Thoroughly
```php
// Test with different scenarios
add_filter('wptpn_should_notify', function($should_notify, $post, $event) {
    // Test with different post types
    if ($post->post_type === 'product') {
        return true; // Always notify for products
    }
    
    // Test with different events
    if ($event === 'publish') {
        return true; // Always notify for publish
    }
    
    return $should_notify;
}, 10, 3);
```

### 5. Consider Performance Impact
```php
add_filter('wptpn_template_tokens', function($tokens, $post) {
    // Cache expensive operations
    static $cache = [];
    
    if (!isset($cache[$post->ID])) {
        $cache[$post->ID] = expensive_calculation($post);
    }
    
    $tokens['expensive_data'] = $cache[$post->ID];
    
    return $tokens;
}, 10, 2);
```

## 📚 Additional Resources

- [Plugin Architecture](plugin-architecture.md)
- [REST API](rest-api.md)
- [Custom Development](custom-development.md)
- [Testing](testing.md)

---

**Need help?** Check our [Support Guide](getting-help.md) or [Contact Us](contact-information.md)

**Made with ❤️ by VoxHash**

*WP Telegram Post Notifier - Professional WordPress to Telegram integration!* 📱✨
