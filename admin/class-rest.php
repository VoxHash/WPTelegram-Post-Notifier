<?php
/**
 * REST API Class
 *
 * @package VoxHash\WPTPN
 */

namespace VoxHash\WPTPN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST API Class
 */
class Rest {
    
    /**
     * Namespace
     *
     * @var string
     */
    private $namespace = 'wptpn/v1';
    
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
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST routes
     */
    public function register_routes() {
        // Settings endpoints
        register_rest_route($this->namespace, '/settings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_settings'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
        
        register_rest_route($this->namespace, '/settings', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_settings'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
        
        // Test connection
        register_rest_route($this->namespace, '/test-connection', array(
            'methods' => 'POST',
            'callback' => array($this, 'test_connection'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
        
        // Send test message
        register_rest_route($this->namespace, '/send-test', array(
            'methods' => 'POST',
            'callback' => array($this, 'send_test_message'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
        
        // Template preview
        register_rest_route($this->namespace, '/preview-template', array(
            'methods' => 'POST',
            'callback' => array($this, 'preview_template'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
        
        // Send now
        register_rest_route($this->namespace, '/send-now', array(
            'methods' => 'POST',
            'callback' => array($this, 'send_now'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
        
        // Logs endpoints
        register_rest_route($this->namespace, '/logs', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_logs'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
        
        register_rest_route($this->namespace, '/logs/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_log_stats'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
        
        // Health check
        register_rest_route($this->namespace, '/health', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_health_status'),
            'permission_callback' => array($this, 'check_permissions'),
        ));
    }
    
    /**
     * Check permissions
     *
     * @param \WP_REST_Request $request Request object
     * @return bool
     */
    public function check_permissions($request) {
        return current_user_can('manage_wptpn');
    }
    
    /**
     * Get settings
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function get_settings($request) {
        $settings = Plugin::get_instance()->get_component('settings')->get_settings();
        
        // Mask sensitive data
        if (!empty($settings['bot_token'])) {
            $settings['bot_token'] = mask_sensitive_data($settings['bot_token'], 4);
        }
        
        return new \WP_REST_Response($settings, 200);
    }
    
    /**
     * Update settings
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function update_settings($request) {
        $settings = $request->get_json_params();
        
        if (empty($settings)) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => __('No settings provided', 'wp-telegram-post-notifier'),
            ), 400);
        }
        
        $settings_component = Plugin::get_instance()->get_component('settings');
        $result = $settings_component->update_settings($settings);
        
        if ($result) {
            return new \WP_REST_Response(array(
                'success' => true,
                'message' => __('Settings updated successfully', 'wp-telegram-post-notifier'),
            ), 200);
        } else {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => __('Failed to update settings', 'wp-telegram-post-notifier'),
            ), 500);
        }
    }
    
    /**
     * Test connection
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function test_connection($request) {
        $params = $request->get_json_params();
        $bot_token = $params['bot_token'] ?? '';
        
        if (empty($bot_token)) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => __('Bot token is required', 'wp-telegram-post-notifier'),
            ), 400);
        }
        
        $telegram_client = Plugin::get_instance()->get_component('telegram_client');
        $telegram_client->set_bot_token($bot_token);
        
        $result = $telegram_client->test_connection();
        
        if ($result['success']) {
            return new \WP_REST_Response(array(
                'success' => true,
                'message' => __('Connection successful', 'wp-telegram-post-notifier'),
                'data' => $result['data'],
            ), 200);
        } else {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => $result['error'],
            ), 400);
        }
    }
    
    /**
     * Send test message
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function send_test_message($request) {
        $params = $request->get_json_params();
        $message = $params['message'] ?? '';
        $destination = $params['destination'] ?? '';
        
        if (empty($message) || empty($destination)) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => __('Message and destination are required', 'wp-telegram-post-notifier'),
            ), 400);
        }
        
        $telegram_client = Plugin::get_instance()->get_component('telegram_client');
        $result = $telegram_client->send_message($destination, $message);
        
        if ($result['success']) {
            return new \WP_REST_Response(array(
                'success' => true,
                'message' => __('Test message sent successfully', 'wp-telegram-post-notifier'),
            ), 200);
        } else {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => $result['error'],
            ), 400);
        }
    }
    
    /**
     * Preview template
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function preview_template($request) {
        $params = $request->get_json_params();
        $template_text = $params['template'] ?? '';
        $post_id = $params['post_id'] ?? null;
        
        if (empty($template_text)) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => __('Template text is required', 'wp-telegram-post-notifier'),
            ), 400);
        }
        
        $template = Plugin::get_instance()->get_component('template');
        $result = $template->preview($template_text, $post_id);
        
        if ($result['success']) {
            return new \WP_REST_Response(array(
                'success' => true,
                'data' => $result,
            ), 200);
        } else {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => $result['error'],
            ), 400);
        }
    }
    
    /**
     * Send now
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function send_now($request) {
        $params = $request->get_json_params();
        $post_id = $params['post_id'] ?? 0;
        $event = $params['event'] ?? 'manual';
        
        if (empty($post_id)) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => __('Post ID is required', 'wp-telegram-post-notifier'),
            ), 400);
        }
        
        $post = get_post($post_id);
        if (!$post) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => __('Post not found', 'wp-telegram-post-notifier'),
            ), 404);
        }
        
        $scheduler = Plugin::get_instance()->get_component('scheduler');
        $result = $scheduler->enqueue_notification($post_id, $event);
        
        if ($result) {
            return new \WP_REST_Response(array(
                'success' => true,
                'message' => __('Notification queued successfully', 'wp-telegram-post-notifier'),
                'action_id' => $result,
            ), 200);
        } else {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => __('Failed to queue notification', 'wp-telegram-post-notifier'),
            ), 500);
        }
    }
    
    /**
     * Get logs
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function get_logs($request) {
        $params = $request->get_params();
        
        $args = array(
            'post_id' => $params['post_id'] ?? null,
            'status' => $params['status'] ?? null,
            'destination' => $params['destination'] ?? null,
            'date_from' => $params['date_from'] ?? null,
            'date_to' => $params['date_to'] ?? null,
            'limit' => intval($params['limit'] ?? 50),
            'offset' => intval($params['offset'] ?? 0),
            'orderby' => $params['orderby'] ?? 'created_at',
            'order' => $params['order'] ?? 'DESC',
        );
        
        $logger = Plugin::get_instance()->get_component('logger');
        $logs = $logger->get_logs($args);
        $total = $logger->get_log_count($args);
        
        return new \WP_REST_Response(array(
            'logs' => $logs,
            'total' => $total,
            'pages' => ceil($total / $args['limit']),
        ), 200);
    }
    
    /**
     * Get log statistics
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function get_log_stats($request) {
        $params = $request->get_params();
        
        $args = array(
            'date_from' => $params['date_from'] ?? null,
            'date_to' => $params['date_to'] ?? null,
        );
        
        $logger = Plugin::get_instance()->get_component('logger');
        $stats = $logger->get_statistics($args);
        
        return new \WP_REST_Response($stats, 200);
    }
    
    /**
     * Get health status
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function get_health_status($request) {
        $health = Plugin::get_instance()->get_component('health');
        $status = $health->get_overall_status();
        
        return new \WP_REST_Response($status, 200);
    }
}
