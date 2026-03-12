<?php
/**
 * Gutenberg Class
 *
 * @package VoxHash\WPTPN
 */

namespace VoxHash\WPTPN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gutenberg Class
 */
class Gutenberg {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize if needed
    }
    
    /**
     * Initialize
     */
    public function init() {
        add_action('init', array($this, 'register_meta_fields'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
        add_action('rest_api_init', array($this, 'register_rest_fields'));
    }
    
    /**
     * Register meta fields
     */
    public function register_meta_fields() {
        $post_types = get_option('post_types', array('post'));
        
        foreach ($post_types as $post_type) {
            register_meta('post', 'wptpn_notify_on_update', array(
                'type' => 'boolean',
                'single' => true,
                'show_in_rest' => true,
                'default' => false,
            ));
            
            register_meta('post', 'wptpn_custom_template', array(
                'type' => 'string',
                'single' => true,
                'show_in_rest' => true,
                'default' => '',
            ));
        }
    }
    
    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets() {
        $post_types = get_option('post_types', array('post'));
        $current_screen = get_current_screen();
        
        if (!$current_screen || !in_array($current_screen->post_type, $post_types)) {
            return;
        }
        
        wp_enqueue_script(
            'wptpn-gutenberg',
            get_asset_url('public/js/gutenberg.js'),
            array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n'),
            WPTPN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'wptpn-gutenberg',
            get_asset_url('public/css/gutenberg.css'),
            array(),
            WPTPN_VERSION
        );
        
        // Localize script
        wp_localize_script('wptpn-gutenberg', 'wptpnGutenberg', array(
            'apiUrl' => rest_url('wptpn/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'strings' => $this->get_gutenberg_strings(),
            'settings' => $this->get_gutenberg_settings(),
        ));
    }
    
    /**
     * Register REST fields
     */
    public function register_rest_fields() {
        $post_types = get_option('post_types', array('post'));
        
        foreach ($post_types as $post_type) {
            register_rest_field($post_type, 'wptpn_notify_on_update', array(
                'get_callback' => array($this, 'get_meta_field'),
                'update_callback' => array($this, 'update_meta_field'),
                'schema' => array(
                    'type' => 'boolean',
                    'context' => array('edit'),
                ),
            ));
            
            register_rest_field($post_type, 'wptpn_custom_template', array(
                'get_callback' => array($this, 'get_meta_field'),
                'update_callback' => array($this, 'update_meta_field'),
                'schema' => array(
                    'type' => 'string',
                    'context' => array('edit'),
                ),
            ));
        }
    }
    
    /**
     * Get meta field
     *
     * @param array $object Post object
     * @param string $field_name Field name
     * @param \WP_REST_Request $request Request object
     * @return mixed
     */
    public function get_meta_field($object, $field_name, $request) {
        return get_post_meta($object['id'], $field_name, true);
    }
    
    /**
     * Update meta field
     *
     * @param mixed $value Field value
     * @param \WP_Post $object Post object
     * @param string $field_name Field name
     * @return bool
     */
    public function update_meta_field($value, $object, $field_name) {
        return update_post_meta($object->ID, $field_name, $value);
    }
    
    /**
     * Get Gutenberg strings
     *
     * @return array
     */
    private function get_gutenberg_strings() {
        return array(
            'panel_title' => __('Telegram Notifier', 'wp-telegram-post-notifier'),
            'notify_on_update' => __('Notify on update', 'wp-telegram-post-notifier'),
            'notify_on_update_desc' => __('Send notification when this post is updated', 'wp-telegram-post-notifier'),
            'custom_template' => __('Custom template', 'wp-telegram-post-notifier'),
            'custom_template_desc' => __('Override default template for this post', 'wp-telegram-post-notifier'),
            'preview_template' => __('Preview template', 'wp-telegram-post-notifier'),
            'send_test' => __('Send test notification', 'wp-telegram-post-notifier'),
            'test_sent' => __('Test notification sent!', 'wp-telegram-post-notifier'),
            'test_failed' => __('Failed to send test notification', 'wp-telegram-post-notifier'),
            'preview' => __('Preview', 'wp-telegram-post-notifier'),
            'close' => __('Close', 'wp-telegram-post-notifier'),
            'loading' => __('Loading...', 'wp-telegram-post-notifier'),
        );
    }
    
    /**
     * Get Gutenberg settings
     *
     * @return array
     */
    private function get_gutenberg_settings() {
        return array(
            'default_template' => get_option('template', "New post: {post_title}\n\n{post_excerpt}\n\nRead more: {post_url}"),
            'parse_mode' => get_option('parse_mode', 'MarkdownV2'),
            'available_tokens' => Plugin::get_instance()->get_component('template')->get_available_tokens(),
        );
    }
}
