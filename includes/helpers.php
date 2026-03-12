<?php
/**
 * Helper Functions
 *
 * @package VoxHash\WPTPN
 */

namespace VoxHash\WPTPN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sanitize text field
 *
 * @param mixed $value Value to sanitize
 * @return string
 */
function sanitize_text($value) {
    return sanitize_text_field($value);
}

/**
 * Sanitize textarea
 *
 * @param mixed $value Value to sanitize
 * @return string
 */
function sanitize_textarea($value) {
    return sanitize_textarea_field($value);
}

/**
 * Sanitize email
 *
 * @param mixed $value Value to sanitize
 * @return string
 */
function sanitize_email($value) {
    return sanitize_email($value);
}

/**
 * Sanitize URL
 *
 * @param mixed $value Value to sanitize
 * @return string
 */
function sanitize_url($value) {
    return esc_url_raw($value);
}

/**
 * Sanitize array
 *
 * @param array $array Array to sanitize
 * @param string $type Sanitization type
 * @return array
 */
function sanitize_array($array, $type = 'text') {
    if (!is_array($array)) {
        return array();
    }
    
    $sanitized = array();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $sanitized[$key] = sanitize_array($value, $type);
        } else {
            switch ($type) {
                case 'email':
                    $sanitized[$key] = sanitize_email($value);
                    break;
                case 'url':
                    $sanitized[$key] = sanitize_url($value);
                    break;
                case 'textarea':
                    $sanitized[$key] = sanitize_textarea($value);
                    break;
                default:
                    $sanitized[$key] = sanitize_text($value);
            }
        }
    }
    
    return $sanitized;
}

/**
 * Escape HTML
 *
 * @param mixed $value Value to escape
 * @return string
 */
function escape_html($value) {
    return esc_html($value);
}

/**
 * Escape attributes
 *
 * @param mixed $value Value to escape
 * @return string
 */
function escape_attr($value) {
    return esc_attr($value);
}

/**
 * Escape JavaScript
 *
 * @param mixed $value Value to escape
 * @return string
 */
function escape_js($value) {
    return esc_js($value);
}

/**
 * Escape URL
 *
 * @param mixed $value Value to escape
 * @return string
 */
function escape_url($value) {
    return esc_url($value);
}

/**
 * Get plugin option
 *
 * @param string $key Option key
 * @param mixed $default Default value
 * @return mixed
 */
function get_option($key, $default = null) {
    $options = get_option('wptpn_settings', array());
    return isset($options[$key]) ? $options[$key] : $default;
}

/**
 * Update plugin option
 *
 * @param string $key Option key
 * @param mixed $value Option value
 * @return bool
 */
function update_option($key, $value) {
    $options = get_option('wptpn_settings', array());
    $options[$key] = $value;
    return update_option('wptpn_settings', $options);
}

/**
 * Get post types that support notifications
 *
 * @return array
 */
function get_supported_post_types() {
    $post_types = get_post_types(array('public' => true), 'objects');
    $supported = array();
    
    foreach ($post_types as $post_type) {
        if ($post_type->name === 'attachment') {
            continue;
        }
        $supported[$post_type->name] = $post_type->label;
    }
    
    return $supported;
}

/**
 * Get available events
 *
 * @return array
 */
function get_available_events() {
    return array(
        'publish' => __('Publish', 'wp-telegram-post-notifier'),
        'update' => __('Update', 'wp-telegram-post-notifier'),
        'future_to_publish' => __('Scheduled to Publish', 'wp-telegram-post-notifier'),
        'pending_to_publish' => __('Pending to Publish', 'wp-telegram-post-notifier'),
    );
}

/**
 * Get parse modes
 *
 * @return array
 */
function get_parse_modes() {
    return array(
        'MarkdownV2' => __('MarkdownV2', 'wp-telegram-post-notifier'),
        'HTML' => __('HTML', 'wp-telegram-post-notifier'),
        'Markdown' => __('Markdown', 'wp-telegram-post-notifier'),
    );
}

/**
 * Check if WooCommerce is active
 *
 * @return bool
 */
function is_woocommerce_active() {
    return class_exists('WooCommerce');
}

/**
 * Get WooCommerce events
 *
 * @return array
 */
function get_woocommerce_events() {
    if (!is_woocommerce_active()) {
        return array();
    }
    
    return array(
        'product_published' => __('Product Published', 'wp-telegram-post-notifier'),
        'product_updated' => __('Product Updated', 'wp-telegram-post-notifier'),
        'price_changed' => __('Price Changed', 'wp-telegram-post-notifier'),
    );
}

/**
 * Generate nonce
 *
 * @param string $action Action name
 * @return string
 */
function create_nonce($action = 'wptpn_nonce') {
    return wp_create_nonce($action);
}

/**
 * Verify nonce
 *
 * @param string $nonce Nonce value
 * @param string $action Action name
 * @return bool
 */
function verify_nonce($nonce, $action = 'wptpn_nonce') {
    return wp_verify_nonce($nonce, $action);
}

/**
 * Check user capability
 *
 * @param string $capability Capability to check
 * @return bool
 */
function current_user_can($capability = 'manage_wptpn') {
    return current_user_can($capability);
}

/**
 * Get user capability
 *
 * @return string
 */
function get_capability() {
    return 'manage_wptpn';
}

/**
 * Format date for display
 *
 * @param string $date Date string
 * @param string $format Date format
 * @return string
 */
function format_date($date, $format = 'Y-m-d H:i:s') {
    return date_i18n($format, strtotime($date));
}

/**
 * Get timezone offset
 *
 * @return int
 */
function get_timezone_offset() {
    return get_option('gmt_offset') * HOUR_IN_SECONDS;
}

/**
 * Log message
 *
 * @param string $message Log message
 * @param string $level Log level
 * @param array $context Log context
 */
function log_message($message, $level = 'info', $context = array()) {
    $logger = Plugin::get_instance()->get_component('logger');
    if ($logger) {
        $logger->log($message, $level, $context);
    }
}

/**
 * Get plugin URL
 *
 * @param string $path Path to append
 * @return string
 */
function get_plugin_url($path = '') {
    return WPTPN_PLUGIN_URL . ltrim($path, '/');
}

/**
 * Get plugin path
 *
 * @param string $path Path to append
 * @return string
 */
function get_plugin_path($path = '') {
    return WPTPN_PLUGIN_DIR . ltrim($path, '/');
}

/**
 * Get asset URL
 *
 * @param string $asset Asset path
 * @return string
 */
function get_asset_url($asset) {
    return get_plugin_url('assets/' . ltrim($asset, '/'));
}

/**
 * Get template path
 *
 * @param string $template Template name
 * @return string
 */
function get_template_path($template) {
    $template_path = get_plugin_path('templates/' . $template . '.php');
    
    if (file_exists($template_path)) {
        return $template_path;
    }
    
    return get_plugin_path('templates/default.php');
}

/**
 * Load template
 *
 * @param string $template Template name
 * @param array $vars Template variables
 */
function load_template($template, $vars = array()) {
    $template_path = get_template_path($template);
    
    if (file_exists($template_path)) {
        extract($vars);
        include $template_path;
    }
}

/**
 * Get short URL
 *
 * @param int $post_id Post ID
 * @return string
 */
function get_short_url($post_id) {
    $post_url = get_permalink($post_id);
    
    // Try to get native shortlink first
    $shortlink = wp_get_shortlink($post_id);
    if ($shortlink && $shortlink !== $post_url) {
        return $shortlink;
    }
    
    // Fallback to pretty permalink
    return $post_url;
}

/**
 * Mask sensitive data
 *
 * @param string $data Data to mask
 * @param int $visible_chars Number of visible characters
 * @return string
 */
function mask_sensitive_data($data, $visible_chars = 4) {
    if (strlen($data) <= $visible_chars) {
        return str_repeat('*', strlen($data));
    }
    
    $masked = substr($data, 0, $visible_chars) . str_repeat('*', strlen($data) - $visible_chars);
    return $masked;
}

/**
 * Check if string is JSON
 *
 * @param string $string String to check
 * @return bool
 */
function is_json($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Get array value with default
 *
 * @param array $array Array to search
 * @param string $key Key to search for
 * @param mixed $default Default value
 * @return mixed
 */
function array_get($array, $key, $default = null) {
    if (!is_array($array)) {
        return $default;
    }
    
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Deep merge arrays
 *
 * @param array $array1 First array
 * @param array $array2 Second array
 * @return array
 */
function array_merge_deep($array1, $array2) {
    $merged = $array1;
    
    foreach ($array2 as $key => $value) {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            $merged[$key] = array_merge_deep($merged[$key], $value);
        } else {
            $merged[$key] = $value;
        }
    }
    
    return $merged;
}
