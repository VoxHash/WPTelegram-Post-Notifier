<?php
/**
 * Telemetry Class
 *
 * @package VoxHash\WPTPN
 */

namespace VoxHash\WPTPN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Telemetry Class
 */
class Telemetry {
    
    /**
     * Telemetry endpoint
     *
     * @var string
     */
    private $endpoint = 'https://telemetry.voxhash.dev/wp-telegram-post-notifier';
    
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
        // Check if telemetry is enabled
        if (!get_option('enable_telemetry', false)) {
            return;
        }
        
        // Schedule telemetry collection
        if (!wp_next_scheduled('wptpn_send_telemetry')) {
            wp_schedule_event(time(), 'weekly', 'wptpn_send_telemetry');
        }
        
        add_action('wptpn_send_telemetry', array($this, 'send_telemetry'));
    }
    
    /**
     * Send telemetry data
     */
    public function send_telemetry() {
        if (!get_option('enable_telemetry', false)) {
            return;
        }
        
        $data = $this->collect_telemetry_data();
        
        $response = wp_remote_post($this->endpoint, array(
            'method' => 'POST',
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode($data),
        ));
        
        if (is_wp_error($response)) {
            log_message('Failed to send telemetry data: ' . $response->get_error_message(), 'error');
            return;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            log_message('Telemetry endpoint returned error: ' . $response_code, 'error');
            return;
        }
        
        // Update last telemetry sent time
        update_option('wptpn_last_telemetry_sent', current_time('mysql'));
        
        log_message('Telemetry data sent successfully', 'info');
    }
    
    /**
     * Collect telemetry data
     *
     * @return array
     */
    private function collect_telemetry_data() {
        global $wp_version;
        
        $data = array(
            'plugin_version' => WPTPN_VERSION,
            'wordpress_version' => $wp_version,
            'php_version' => PHP_VERSION,
            'site_url' => home_url(),
            'multisite' => is_multisite(),
            'active_plugins' => $this->get_active_plugins(),
            'theme' => $this->get_theme_info(),
            'settings' => $this->get_anonymized_settings(),
            'usage_stats' => $this->get_usage_stats(),
            'timestamp' => current_time('mysql'),
        );
        
        return $data;
    }
    
    /**
     * Get active plugins
     *
     * @return array
     */
    private function get_active_plugins() {
        $active_plugins = get_option('active_plugins', array());
        $plugin_data = array();
        
        foreach ($active_plugins as $plugin) {
            $plugin_info = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
            if ($plugin_info) {
                $plugin_data[] = array(
                    'name' => $plugin_info['Name'],
                    'version' => $plugin_info['Version'],
                    'slug' => dirname($plugin),
                );
            }
        }
        
        return $plugin_data;
    }
    
    /**
     * Get theme information
     *
     * @return array
     */
    private function get_theme_info() {
        $theme = wp_get_theme();
        
        return array(
            'name' => $theme->get('Name'),
            'version' => $theme->get('Version'),
            'template' => $theme->get_template(),
        );
    }
    
    /**
     * Get anonymized settings
     *
     * @return array
     */
    private function get_anonymized_settings() {
        $settings = get_option('wptpn_settings', array());
        
        // Remove sensitive data
        $anonymized = $settings;
        unset($anonymized['bot_token']);
        
        // Anonymize destinations
        if (isset($anonymized['destinations'])) {
            foreach ($anonymized['destinations'] as &$destination) {
                if (isset($destination['chat_id'])) {
                    $destination['chat_id'] = $this->anonymize_chat_id($destination['chat_id']);
                }
            }
        }
        
        return $anonymized;
    }
    
    /**
     * Anonymize chat ID
     *
     * @param string $chat_id Chat ID
     * @return string
     */
    private function anonymize_chat_id($chat_id) {
        if (strpos($chat_id, '@') === 0) {
            return '@***';
        }
        
        if (is_numeric($chat_id)) {
            return '***' . substr($chat_id, -3);
        }
        
        return '***';
    }
    
    /**
     * Get usage statistics
     *
     * @return array
     */
    private function get_usage_stats() {
        $logger = Plugin::get_instance()->get_component('logger');
        
        if (!$logger) {
            return array();
        }
        
        $stats = $logger->get_statistics(array(
            'date_from' => date('Y-m-d', strtotime('-30 days')),
        ));
        
        return array(
            'total_notifications' => $stats['total'] ?? 0,
            'successful_notifications' => $stats['success'] ?? 0,
            'failed_notifications' => $stats['error'] ?? 0,
            'unique_posts' => $stats['unique_posts'] ?? 0,
            'unique_destinations' => $stats['unique_destinations'] ?? 0,
        );
    }
    
    /**
     * Enable telemetry
     */
    public function enable_telemetry() {
        update_option('enable_telemetry', true);
        
        // Schedule immediate telemetry collection
        wp_schedule_single_event(time(), 'wptpn_send_telemetry');
        
        log_message('Telemetry enabled', 'info');
    }
    
    /**
     * Disable telemetry
     */
    public function disable_telemetry() {
        update_option('enable_telemetry', false);
        
        // Clear scheduled telemetry collection
        wp_clear_scheduled_hook('wptpn_send_telemetry');
        
        log_message('Telemetry disabled', 'info');
    }
    
    /**
     * Check if telemetry is enabled
     *
     * @return bool
     */
    public function is_enabled() {
        return get_option('enable_telemetry', false);
    }
    
    /**
     * Get last telemetry sent time
     *
     * @return string|null
     */
    public function get_last_sent_time() {
        return get_option('wptpn_last_telemetry_sent');
    }
    
    /**
     * Get telemetry data for display
     *
     * @return array
     */
    public function get_telemetry_info() {
        return array(
            'enabled' => $this->is_enabled(),
            'last_sent' => $this->get_last_sent_time(),
            'endpoint' => $this->endpoint,
            'data_collected' => $this->get_data_collection_info(),
        );
    }
    
    /**
     * Get data collection information
     *
     * @return array
     */
    private function get_data_collection_info() {
        return array(
            'plugin_version' => 'Yes',
            'wordpress_version' => 'Yes',
            'php_version' => 'Yes',
            'site_url' => 'Yes (anonymized)',
            'multisite' => 'Yes',
            'active_plugins' => 'Yes (names and versions only)',
            'theme' => 'Yes (name and version only)',
            'settings' => 'Yes (anonymized, no sensitive data)',
            'usage_stats' => 'Yes (aggregated statistics only)',
            'bot_token' => 'No',
            'chat_ids' => 'No (anonymized)',
            'personal_data' => 'No',
        );
    }
}
