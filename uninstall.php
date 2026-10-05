<?php
/**
 * Uninstall script for WP Telegram Post Notifier
 *
 * @package VoxHash\WPTPN
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load plugin constants
if (!defined('WPTPN_PLUGIN_DIR')) {
    define('WPTPN_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Load the plugin class to access uninstall methods
require_once WPTPN_PLUGIN_DIR . 'includes/class-plugin.php';

// Run uninstall
VoxHash\WPTPN\Plugin::get_instance()->uninstall();
