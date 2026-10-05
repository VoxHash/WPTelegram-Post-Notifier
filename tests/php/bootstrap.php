<?php
/**
 * PHPUnit bootstrap file
 *
 * @package VoxHash\WPTPN
 */

// Load WordPress test environment
if (!defined('WP_TESTS_DIR')) {
    define('WP_TESTS_DIR', '/tmp/wordpress-tests-lib/');
}

// Load WordPress test functions
require_once WP_TESTS_DIR . 'includes/functions.php';

// Load the plugin
function _manually_load_plugin() {
    require dirname(dirname(__FILE__)) . '/wp-telegram-post-notifier.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment
require WP_TESTS_DIR . 'includes/bootstrap.php';
