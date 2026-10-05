<?php
/**
 * Plugin Name: WP Telegram Post Notifier
 * Plugin URI: https://github.com/VoxHash/WPTelegram-Post-Notifier
 * Description: A production-grade WordPress plugin that posts to Telegram channels/chats when content changes. Features advanced templating, routing rules, async processing, and comprehensive admin interface.
 * Version: 1.0.0
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * Tested up to: 6.4
 * Author: VoxHash
 * Author URI: https://www.voxhash.dev/
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: wp-telegram-post-notifier
 * Domain Path: /languages
 * Network: false
 *
 * @package VoxHash\WPTPN
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPTPN_VERSION', '1.0.0');
define('WPTPN_PLUGIN_FILE', __FILE__);
define('WPTPN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPTPN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPTPN_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WPTPN_MIN_PHP_VERSION', '7.4');
define('WPTPN_MIN_WP_VERSION', '6.3');

/**
 * Check if the minimum requirements are met
 */
function wptpn_check_requirements() {
    global $wp_version;
    
    if (version_compare(PHP_VERSION, WPTPN_MIN_PHP_VERSION, '<')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            printf(
                /* translators: 1: Current PHP version, 2: Required PHP version */
                esc_html__('WP Telegram Post Notifier requires PHP %2$s or higher. You are running PHP %1$s.', 'wp-telegram-post-notifier'),
                PHP_VERSION,
                WPTPN_MIN_PHP_VERSION
            );
            echo '</p></div>';
        });
        return false;
    }
    
    if (version_compare($wp_version, WPTPN_MIN_WP_VERSION, '<')) {
        add_action('admin_notices', function() {
            global $wp_version;
            echo '<div class="notice notice-error"><p>';
            printf(
                /* translators: 1: Current WordPress version, 2: Required WordPress version */
                esc_html__('WP Telegram Post Notifier requires WordPress %2$s or higher. You are running WordPress %1$s.', 'wp-telegram-post-notifier'),
                $wp_version,
                WPTPN_MIN_WP_VERSION
            );
            echo '</p></div>';
        });
        return false;
    }
    
    return true;
}

/**
 * Initialize the plugin
 */
function wptpn_init() {
    if (!wptpn_check_requirements()) {
        return;
    }
    
    // Load text domain
    load_plugin_textdomain('wp-telegram-post-notifier', false, dirname(WPTPN_PLUGIN_BASENAME) . '/languages');
    
    // Load the main plugin class
    require_once WPTPN_PLUGIN_DIR . 'includes/class-plugin.php';
    
    // Initialize the plugin
    VoxHash\WPTPN\Plugin::get_instance();
}

// Hook into WordPress
add_action('plugins_loaded', 'wptpn_init');

/**
 * Plugin activation hook
 */
function wptpn_activate() {
    if (!wptpn_check_requirements()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            esc_html__('WP Telegram Post Notifier could not be activated due to insufficient requirements.', 'wp-telegram-post-notifier'),
            esc_html__('Plugin Activation Error', 'wp-telegram-post-notifier'),
            array('back_link' => true)
        );
    }
    
    // Load plugin class for activation
    require_once WPTPN_PLUGIN_DIR . 'includes/class-plugin.php';
    
    // Run activation
    VoxHash\WPTPN\Plugin::get_instance()->activate();
}
register_activation_hook(__FILE__, 'wptpn_activate');

/**
 * Plugin deactivation hook
 */
function wptpn_deactivate() {
    // Load plugin class for deactivation
    require_once WPTPN_PLUGIN_DIR . 'includes/class-plugin.php';
    
    // Run deactivation
    VoxHash\WPTPN\Plugin::get_instance()->deactivate();
}
register_deactivation_hook(__FILE__, 'wptpn_deactivate');

/**
 * Plugin uninstall hook
 */
function wptpn_uninstall() {
    require_once WPTPN_PLUGIN_DIR . 'uninstall.php';
}
register_uninstall_hook(__FILE__, 'wptpn_uninstall');
