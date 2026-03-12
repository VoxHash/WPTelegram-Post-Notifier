<?php
/**
 * Admin Class
 *
 * @package VoxHash\WPTPN
 */

namespace VoxHash\WPTPN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Class
 */
class Admin {
    
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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_notices', array($this, 'show_admin_notices'));
        add_action('admin_init', array($this, 'handle_admin_actions'));
        
        // Add capabilities
        add_action('init', array($this, 'add_capabilities'));
        
        // Add post row actions
        add_filter('post_row_actions', array($this, 'add_post_row_actions'), 10, 2);
        add_filter('page_row_actions', array($this, 'add_post_row_actions'), 10, 2);
        
        // Add bulk actions
        add_filter('bulk_actions-edit-post', array($this, 'add_bulk_actions'));
        add_filter('bulk_actions-edit-page', array($this, 'add_bulk_actions'));
        add_filter('handle_bulk_actions-edit-post', array($this, 'handle_bulk_actions'), 10, 3);
        add_filter('handle_bulk_actions-edit-page', array($this, 'handle_bulk_actions'), 10, 3);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main settings page
        add_options_page(
            __('Telegram Notifier', 'wp-telegram-post-notifier'),
            __('Telegram Notifier', 'wp-telegram-post-notifier'),
            'manage_wptpn',
            'wptpn-settings',
            array($this, 'render_settings_page')
        );
        
        // Logs page
        add_management_page(
            __('Telegram Logs', 'wp-telegram-post-notifier'),
            __('Telegram Logs', 'wp-telegram-post-notifier'),
            'manage_wptpn',
            'wptpn-logs',
            array($this, 'render_logs_page')
        );
    }
    
    /**
     * Enqueue admin scripts
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our pages
        if (strpos($hook, 'wptpn') === false) {
            return;
        }
        
        // Enqueue React app for settings page
        if ($hook === 'settings_page_wptpn-settings') {
            wp_enqueue_script(
                'wptpn-admin',
                get_asset_url('admin/build/index.js'),
                array('wp-element', 'wp-api-fetch', 'wp-i18n'),
                WPTPN_VERSION,
                true
            );
            
            wp_enqueue_style(
                'wptpn-admin',
                get_asset_url('admin/build/index.css'),
                array(),
                WPTPN_VERSION
            );
            
            // Localize script
            wp_localize_script('wptpn-admin', 'wptpnAdmin', array(
                'apiUrl' => rest_url('wptpn/v1/'),
                'nonce' => wp_create_nonce('wp_rest'),
                'settings' => $this->get_settings_for_js(),
                'strings' => $this->get_admin_strings(),
            ));
        }
        
        // Enqueue logs page scripts
        if ($hook === 'tools_page_wptpn-logs') {
            wp_enqueue_script(
                'wptpn-logs',
                get_asset_url('admin/js/logs.js'),
                array('jquery'),
                WPTPN_VERSION,
                true
            );
            
            wp_enqueue_style(
                'wptpn-logs',
                get_asset_url('admin/css/logs.css'),
                array(),
                WPTPN_VERSION
            );
        }
    }
    
    /**
     * Show admin notices
     */
    public function show_admin_notices() {
        // Check for activation notice
        if (get_transient('wptpn_activated')) {
            delete_transient('wptpn_activated');
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php esc_html_e('WP Telegram Post Notifier activated successfully!', 'wp-telegram-post-notifier'); ?>
                    <a href="<?php echo esc_url(admin_url('options-general.php?page=wptpn-settings')); ?>">
                        <?php esc_html_e('Configure now', 'wp-telegram-post-notifier'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
        
        // Check for configuration issues
        $this->show_configuration_notices();
    }
    
    /**
     * Show configuration notices
     */
    private function show_configuration_notices() {
        $bot_token = get_option('bot_token', '');
        $destinations = get_option('destinations', array());
        $enabled_destinations = array_filter($destinations, function($dest) {
            return !empty($dest['enabled']);
        });
        
        if (empty($bot_token)) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <?php esc_html_e('WP Telegram Post Notifier: Bot token is not configured.', 'wp-telegram-post-notifier'); ?>
                    <a href="<?php echo esc_url(admin_url('options-general.php?page=wptpn-settings')); ?>">
                        <?php esc_html_e('Configure now', 'wp-telegram-post-notifier'); ?>
                    </a>
                </p>
            </div>
            <?php
        } elseif (empty($enabled_destinations)) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <?php esc_html_e('WP Telegram Post Notifier: No destinations configured.', 'wp-telegram-post-notifier'); ?>
                    <a href="<?php echo esc_url(admin_url('options-general.php?page=wptpn-settings')); ?>">
                        <?php esc_html_e('Configure now', 'wp-telegram-post-notifier'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
    
    /**
     * Handle admin actions
     */
    public function handle_admin_actions() {
        if (!current_user_can('manage_wptpn')) {
            return;
        }
        
        // Handle send test message
        if (isset($_POST['wptpn_send_test']) && wp_verify_nonce($_POST['_wpnonce'], 'wptpn_send_test')) {
            $this->handle_send_test_message();
        }
        
        // Handle clear logs
        if (isset($_POST['wptpn_clear_logs']) && wp_verify_nonce($_POST['_wpnonce'], 'wptpn_clear_logs')) {
            $this->handle_clear_logs();
        }
    }
    
    /**
     * Handle send test message
     */
    private function handle_send_test_message() {
        $message = sanitize_textarea_field($_POST['test_message'] ?? '');
        $destination = sanitize_text_field($_POST['test_destination'] ?? '');
        
        if (empty($message) || empty($destination)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . esc_html__('Please provide both message and destination.', 'wp-telegram-post-notifier') . '</p></div>';
            });
            return;
        }
        
        $telegram_client = Plugin::get_instance()->get_component('telegram_client');
        $result = $telegram_client->send_message($destination, $message);
        
        if ($result['success']) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success"><p>' . esc_html__('Test message sent successfully!', 'wp-telegram-post-notifier') . '</p></div>';
            });
        } else {
            add_action('admin_notices', function() use ($result) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Failed to send test message: ', 'wp-telegram-post-notifier') . esc_html($result['error']) . '</p></div>';
            });
        }
    }
    
    /**
     * Handle clear logs
     */
    private function handle_clear_logs() {
        $logger = Plugin::get_instance()->get_component('logger');
        $deleted = $logger->clear_all_logs();
        
        add_action('admin_notices', function() use ($deleted) {
            echo '<div class="notice notice-success"><p>' . sprintf(esc_html__('Cleared %d log entries.', 'wp-telegram-post-notifier'), $deleted) . '</p></div>';
        });
    }
    
    /**
     * Add capabilities
     */
    public function add_capabilities() {
        $role = get_role('administrator');
        if ($role) {
            $role->add_cap('manage_wptpn');
        }
    }
    
    /**
     * Add post row actions
     *
     * @param array $actions Existing actions
     * @param \WP_Post $post Post object
     * @return array
     */
    public function add_post_row_actions($actions, $post) {
        if (!current_user_can('manage_wptpn')) {
            return $actions;
        }
        
        $actions['wptpn_send'] = sprintf(
            '<a href="%s" onclick="return confirm(\'%s\')">%s</a>',
            wp_nonce_url(admin_url('admin.php?action=wptpn_send&post=' . $post->ID), 'wptpn_send_' . $post->ID),
            esc_js(__('Send this post to Telegram now?', 'wp-telegram-post-notifier')),
            __('Send to Telegram', 'wp-telegram-post-notifier')
        );
        
        return $actions;
    }
    
    /**
     * Add bulk actions
     *
     * @param array $actions Existing actions
     * @return array
     */
    public function add_bulk_actions($actions) {
        if (!current_user_can('manage_wptpn')) {
            return $actions;
        }
        
        $actions['wptpn_send'] = __('Send to Telegram', 'wp-telegram-post-notifier');
        
        return $actions;
    }
    
    /**
     * Handle bulk actions
     *
     * @param string $redirect_to Redirect URL
     * @param string $doaction Action name
     * @param array $post_ids Post IDs
     * @return string
     */
    public function handle_bulk_actions($redirect_to, $doaction, $post_ids) {
        if ($doaction !== 'wptpn_send' || !current_user_can('manage_wptpn')) {
            return $redirect_to;
        }
        
        $scheduler = Plugin::get_instance()->get_component('scheduler');
        $sent_count = 0;
        
        foreach ($post_ids as $post_id) {
            $result = $scheduler->enqueue_notification($post_id, 'manual');
            if ($result) {
                $sent_count++;
            }
        }
        
        $redirect_to = add_query_arg('wptpn_sent', $sent_count, $redirect_to);
        
        return $redirect_to;
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('WP Telegram Post Notifier Settings', 'wp-telegram-post-notifier'); ?></h1>
            <div id="wptpn-admin-app"></div>
        </div>
        <?php
    }
    
    /**
     * Render logs page
     */
    public function render_logs_page() {
        $logger = Plugin::get_instance()->get_component('logger');
        $logs = $logger->get_recent_logs(50);
        $stats = $logger->get_statistics();
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Telegram Logs', 'wp-telegram-post-notifier'); ?></h1>
            
            <div class="wptpn-logs-stats">
                <div class="stats-box">
                    <h3><?php esc_html_e('Statistics', 'wp-telegram-post-notifier'); ?></h3>
                    <p><?php printf(esc_html__('Total: %d | Success: %d | Errors: %d', 'wp-telegram-post-notifier'), $stats['total'], $stats['success'], $stats['error']); ?></p>
                </div>
            </div>
            
            <div class="wptpn-logs-actions">
                <form method="post" style="display: inline;">
                    <?php wp_nonce_field('wptpn_clear_logs'); ?>
                    <input type="submit" name="wptpn_clear_logs" class="button" value="<?php esc_attr_e('Clear All Logs', 'wp-telegram-post-notifier'); ?>" onclick="return confirm('<?php esc_js_e('Are you sure you want to clear all logs?', 'wp-telegram-post-notifier'); ?>')">
                </form>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('ID', 'wp-telegram-post-notifier'); ?></th>
                        <th><?php esc_html_e('Post', 'wp-telegram-post-notifier'); ?></th>
                        <th><?php esc_html_e('Event', 'wp-telegram-post-notifier'); ?></th>
                        <th><?php esc_html_e('Destination', 'wp-telegram-post-notifier'); ?></th>
                        <th><?php esc_html_e('Status', 'wp-telegram-post-notifier'); ?></th>
                        <th><?php esc_html_e('Message', 'wp-telegram-post-notifier'); ?></th>
                        <th><?php esc_html_e('Error', 'wp-telegram-post-notifier'); ?></th>
                        <th><?php esc_html_e('Date', 'wp-telegram-post-notifier'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="8"><?php esc_html_e('No logs found.', 'wp-telegram-post-notifier'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo esc_html($log['id']); ?></td>
                                <td>
                                    <?php
                                    $post = get_post($log['post_id']);
                                    if ($post) {
                                        printf('<a href="%s">%s</a>', get_edit_post_link($post->ID), esc_html($post->post_title));
                                    } else {
                                        echo esc_html($log['post_id']);
                                    }
                                    ?>
                                </td>
                                <td><?php echo esc_html($log['event']); ?></td>
                                <td><?php echo esc_html($log['destination']); ?></td>
                                <td>
                                    <span class="status-<?php echo esc_attr($log['status']); ?>">
                                        <?php echo esc_html(ucfirst($log['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(wp_trim_words($log['message'], 10)); ?></td>
                                <td><?php echo esc_html($log['error_message']); ?></td>
                                <td><?php echo esc_html(format_date($log['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Get settings for JavaScript
     *
     * @return array
     */
    private function get_settings_for_js() {
        return array(
            'bot_token' => get_option('bot_token', ''),
            'destinations' => get_option('destinations', array()),
            'post_types' => get_option('post_types', array('post')),
            'events' => get_option('events', array('publish')),
            'template' => get_option('template', "New post: {post_title}\n\n{post_excerpt}\n\nRead more: {post_url}"),
            'parse_mode' => get_option('parse_mode', 'MarkdownV2'),
            'send_silent' => get_option('send_silent', false),
            'disable_web_page_preview' => get_option('disable_web_page_preview', false),
            'rate_limit_retries' => get_option('rate_limit_retries', 5),
            'rate_limit_delay' => get_option('rate_limit_delay', 5),
            'enable_logging' => get_option('enable_logging', true),
            'log_retention_days' => get_option('log_retention_days', 30),
            'enable_telemetry' => get_option('enable_telemetry', false),
            'routing_rules' => get_option('routing_rules', array()),
        );
    }
    
    /**
     * Get admin strings for JavaScript
     *
     * @return array
     */
    private function get_admin_strings() {
        return array(
            'save' => __('Save Changes', 'wp-telegram-post-notifier'),
            'saving' => __('Saving...', 'wp-telegram-post-notifier'),
            'saved' => __('Settings saved successfully!', 'wp-telegram-post-notifier'),
            'error' => __('An error occurred. Please try again.', 'wp-telegram-post-notifier'),
            'confirm' => __('Are you sure?', 'wp-telegram-post-notifier'),
            'test_connection' => __('Test Connection', 'wp-telegram-post-notifier'),
            'test_message' => __('Send Test Message', 'wp-telegram-post-notifier'),
            'preview' => __('Preview', 'wp-telegram-post-notifier'),
        );
    }
}
