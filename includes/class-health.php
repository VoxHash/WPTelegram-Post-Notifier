<?php
/**
 * Health Class
 *
 * @package VoxHash\WPTPN
 */

namespace VoxHash\WPTPN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Health Class
 */
class Health {
    
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
        add_filter('site_status_tests', array($this, 'add_health_checks'));
        add_filter('site_status_test_result', array($this, 'modify_health_check_results'), 10, 2);
    }
    
    /**
     * Add health checks
     *
     * @param array $tests Existing tests
     * @return array
     */
    public function add_health_checks($tests) {
        $tests['direct']['wptpn_bot_token'] = array(
            'label' => __('WP Telegram Post Notifier - Bot Token', 'wp-telegram-post-notifier'),
            'test' => array($this, 'test_bot_token'),
        );
        
        $tests['direct']['wptpn_destinations'] = array(
            'label' => __('WP Telegram Post Notifier - Destinations', 'wp-telegram-post-notifier'),
            'test' => array($this, 'test_destinations'),
        );
        
        $tests['direct']['wptpn_action_scheduler'] = array(
            'label' => __('WP Telegram Post Notifier - Action Scheduler', 'wp-telegram-post-notifier'),
            'test' => array($this, 'test_action_scheduler'),
        );
        
        $tests['direct']['wptpn_database'] = array(
            'label' => __('WP Telegram Post Notifier - Database', 'wp-telegram-post-notifier'),
            'test' => array($this, 'test_database'),
        );
        
        $tests['async']['wptpn_telegram_connection'] = array(
            'label' => __('WP Telegram Post Notifier - Telegram Connection', 'wp-telegram-post-notifier'),
            'test' => array($this, 'test_telegram_connection'),
        );
        
        return $tests;
    }
    
    /**
     * Test bot token
     *
     * @return array
     */
    public function test_bot_token() {
        $bot_token = get_option('bot_token', '');
        
        if (empty($bot_token)) {
            return array(
                'status' => 'critical',
                'label' => __('Bot token is not configured', 'wp-telegram-post-notifier'),
                'description' => sprintf(
                    '<p>%s</p>',
                    __('You need to configure a Telegram bot token to use this plugin.', 'wp-telegram-post-notifier')
                ),
                'actions' => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    admin_url('options-general.php?page=wptpn-settings'),
                    __('Configure bot token', 'wp-telegram-post-notifier')
                ),
            );
        }
        
        if (strlen($bot_token) < 10) {
            return array(
                'status' => 'critical',
                'label' => __('Bot token appears to be invalid', 'wp-telegram-post-notifier'),
                'description' => sprintf(
                    '<p>%s</p>',
                    __('The bot token you entered appears to be too short or invalid.', 'wp-telegram-post-notifier')
                ),
                'actions' => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    admin_url('options-general.php?page=wptpn-settings'),
                    __('Check bot token', 'wp-telegram-post-notifier')
                ),
            );
        }
        
        return array(
            'status' => 'good',
            'label' => __('Bot token is configured', 'wp-telegram-post-notifier'),
            'description' => sprintf(
                '<p>%s</p>',
                __('Bot token is configured and appears to be valid.', 'wp-telegram-post-notifier')
            ),
        );
    }
    
    /**
     * Test destinations
     *
     * @return array
     */
    public function test_destinations() {
        $destinations = get_option('destinations', array());
        $enabled_destinations = array_filter($destinations, function($dest) {
            return !empty($dest['enabled']);
        });
        
        if (empty($enabled_destinations)) {
            return array(
                'status' => 'critical',
                'label' => __('No destinations configured', 'wp-telegram-post-notifier'),
                'description' => sprintf(
                    '<p>%s</p>',
                    __('You need to configure at least one destination (channel or chat) to send notifications to.', 'wp-telegram-post-notifier')
                ),
                'actions' => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    admin_url('options-general.php?page=wptpn-settings'),
                    __('Configure destinations', 'wp-telegram-post-notifier')
                ),
            );
        }
        
        $invalid_destinations = array();
        foreach ($enabled_destinations as $destination) {
            if (empty($destination['chat_id'])) {
                $invalid_destinations[] = $destination['name'] ?? __('Unnamed destination', 'wp-telegram-post-notifier');
            }
        }
        
        if (!empty($invalid_destinations)) {
            return array(
                'status' => 'critical',
                'label' => __('Some destinations are invalid', 'wp-telegram-post-notifier'),
                'description' => sprintf(
                    '<p>%s</p><ul><li>%s</li></ul>',
                    __('The following destinations have invalid chat IDs:', 'wp-telegram-post-notifier'),
                    implode('</li><li>', $invalid_destinations)
                ),
                'actions' => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    admin_url('options-general.php?page=wptpn-settings'),
                    __('Fix destinations', 'wp-telegram-post-notifier')
                ),
            );
        }
        
        return array(
            'status' => 'good',
            'label' => sprintf(
                __('%d destination(s) configured', 'wp-telegram-post-notifier'),
                count($enabled_destinations)
            ),
            'description' => sprintf(
                '<p>%s</p>',
                __('All configured destinations appear to be valid.', 'wp-telegram-post-notifier')
            ),
        );
    }
    
    /**
     * Test Action Scheduler
     *
     * @return array
     */
    public function test_action_scheduler() {
        if (!class_exists('ActionScheduler')) {
            return array(
                'status' => 'critical',
                'label' => __('Action Scheduler not available', 'wp-telegram-post-notifier'),
                'description' => sprintf(
                    '<p>%s</p>',
                    __('Action Scheduler library is not available. This is required for async processing.', 'wp-telegram-post-notifier')
                ),
            );
        }
        
        $scheduler = Plugin::get_instance()->get_component('scheduler');
        $status = $scheduler->get_status();
        
        if (!$status['available']) {
            return array(
                'status' => 'critical',
                'label' => __('Action Scheduler not working', 'wp-telegram-post-notifier'),
                'description' => sprintf(
                    '<p>%s</p>',
                    $status['message']
                ),
            );
        }
        
        $pending = $status['pending'];
        $failed = $status['failed'];
        
        if ($failed > 10) {
            return array(
                'status' => 'recommended',
                'label' => __('High number of failed actions', 'wp-telegram-post-notifier'),
                'description' => sprintf(
                    '<p>%s</p>',
                    sprintf(
                        __('There are %d failed actions in the queue. This may indicate configuration issues.', 'wp-telegram-post-notifier'),
                        $failed
                    )
                ),
                'actions' => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    admin_url('admin.php?page=wptpn-logs'),
                    __('View logs', 'wp-telegram-post-notifier')
                ),
            );
        }
        
        return array(
            'status' => 'good',
            'label' => __('Action Scheduler is working', 'wp-telegram-post-notifier'),
            'description' => sprintf(
                '<p>%s</p>',
                $status['message']
            ),
        );
    }
    
    /**
     * Test database
     *
     * @return array
     */
    public function test_database() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wptpn_logs';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
        
        if (!$table_exists) {
            return array(
                'status' => 'critical',
                'label' => __('Database table missing', 'wp-telegram-post-notifier'),
                'description' => sprintf(
                    '<p>%s</p>',
                    __('The logs table is missing. Please deactivate and reactivate the plugin to create it.', 'wp-telegram-post-notifier')
                ),
            );
        }
        
        // Check table structure
        $columns = $wpdb->get_results("DESCRIBE {$table_name}", ARRAY_A);
        $required_columns = array('id', 'post_id', 'event', 'destination', 'status', 'created_at');
        
        $existing_columns = array_column($columns, 'Field');
        $missing_columns = array_diff($required_columns, $existing_columns);
        
        if (!empty($missing_columns)) {
            return array(
                'status' => 'critical',
                'label' => __('Database table structure is invalid', 'wp-telegram-post-notifier'),
                'description' => sprintf(
                    '<p>%s</p><ul><li>%s</li></ul>',
                    __('The logs table is missing required columns:', 'wp-telegram-post-notifier'),
                    implode('</li><li>', $missing_columns)
                ),
            );
        }
        
        // Check if we can write to the table
        $test_result = $wpdb->insert($table_name, array(
            'post_id' => 0,
            'event' => 'test',
            'destination' => 'test',
            'status' => 'test',
            'created_at' => current_time('mysql'),
        ));
        
        if ($test_result === false) {
            return array(
                'status' => 'critical',
                'label' => __('Cannot write to database table', 'wp-telegram-post-notifier'),
                'description' => sprintf(
                    '<p>%s</p><p><code>%s</code></p>',
                    __('Cannot write to the logs table. Database error:', 'wp-telegram-post-notifier'),
                    $wpdb->last_error
                ),
            );
        }
        
        // Clean up test record
        $wpdb->delete($table_name, array('event' => 'test'));
        
        return array(
            'status' => 'good',
            'label' => __('Database is working correctly', 'wp-telegram-post-notifier'),
            'description' => sprintf(
                '<p>%s</p>',
                __('The logs table exists and is writable.', 'wp-telegram-post-notifier')
            ),
        );
    }
    
    /**
     * Test Telegram connection
     *
     * @return array
     */
    public function test_telegram_connection() {
        $telegram_client = Plugin::get_instance()->get_component('telegram_client');
        $result = $telegram_client->test_connection();
        
        if (!$result['success']) {
            return array(
                'status' => 'critical',
                'label' => __('Cannot connect to Telegram', 'wp-telegram-post-notifier'),
                'description' => sprintf(
                    '<p>%s</p><p><code>%s</code></p>',
                    __('Failed to connect to Telegram API:', 'wp-telegram-post-notifier'),
                    $result['error']
                ),
                'actions' => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    admin_url('options-general.php?page=wptpn-settings'),
                    __('Check configuration', 'wp-telegram-post-notifier')
                ),
            );
        }
        
        $bot_info = $result['data'];
        $bot_username = $bot_info['username'] ?? 'Unknown';
        
        return array(
            'status' => 'good',
            'label' => __('Successfully connected to Telegram', 'wp-telegram-post-notifier'),
            'description' => sprintf(
                '<p>%s</p><p><strong>%s:</strong> @%s</p>',
                __('Successfully connected to Telegram API.', 'wp-telegram-post-notifier'),
                __('Bot username', 'wp-telegram-post-notifier'),
                $bot_username
            ),
        );
    }
    
    /**
     * Modify health check results
     *
     * @param array $result Test result
     * @param string $test_name Test name
     * @return array
     */
    public function modify_health_check_results($result, $test_name) {
        if (strpos($test_name, 'wptpn_') !== 0) {
            return $result;
        }
        
        // Add additional context for our tests
        if (isset($result['status']) && $result['status'] === 'critical') {
            $result['badge'] = array(
                'label' => __('WP Telegram Post Notifier', 'wp-telegram-post-notifier'),
                'color' => 'red',
            );
        }
        
        return $result;
    }
    
    /**
     * Get overall health status
     *
     * @return array
     */
    public function get_overall_status() {
        $tests = array(
            'bot_token' => $this->test_bot_token(),
            'destinations' => $this->test_destinations(),
            'action_scheduler' => $this->test_action_scheduler(),
            'database' => $this->test_database(),
        );
        
        $critical_count = 0;
        $recommended_count = 0;
        $good_count = 0;
        
        foreach ($tests as $test) {
            switch ($test['status']) {
                case 'critical':
                    $critical_count++;
                    break;
                case 'recommended':
                    $recommended_count++;
                    break;
                case 'good':
                    $good_count++;
                    break;
            }
        }
        
        if ($critical_count > 0) {
            $status = 'critical';
            $message = sprintf(
                __('%d critical issue(s) found', 'wp-telegram-post-notifier'),
                $critical_count
            );
        } elseif ($recommended_count > 0) {
            $status = 'recommended';
            $message = sprintf(
                __('%d recommended improvement(s) available', 'wp-telegram-post-notifier'),
                $recommended_count
            );
        } else {
            $status = 'good';
            $message = __('All systems operational', 'wp-telegram-post-notifier');
        }
        
        return array(
            'status' => $status,
            'message' => $message,
            'tests' => $tests,
            'counts' => array(
                'critical' => $critical_count,
                'recommended' => $recommended_count,
                'good' => $good_count,
            ),
        );
    }
}
