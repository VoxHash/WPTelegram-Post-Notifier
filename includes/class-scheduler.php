<?php
/**
 * Scheduler Class
 *
 * @package VoxHash\WPTPN
 */

namespace VoxHash\WPTPN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Scheduler Class
 */
class Scheduler {
    
    /**
     * Action group
     *
     * @var string
     */
    private $action_group = 'wptpn';
    
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
        add_action('wptpn_send_notification', array($this, 'send_notification'), 10, 1);
        add_action('wptpn_cleanup_logs', array($this, 'cleanup_logs'));
        
        // Initialize Action Scheduler
        $this->init_action_scheduler();
    }
    
    /**
     * Initialize Action Scheduler
     */
    private function init_action_scheduler() {
        if (!class_exists('ActionScheduler')) {
            return;
        }
        
        // Register action scheduler tables
        add_action('init', array($this, 'register_action_scheduler_tables'));
    }
    
    /**
     * Register Action Scheduler tables
     */
    public function register_action_scheduler_tables() {
        if (!class_exists('ActionScheduler')) {
            return;
        }
        
        ActionScheduler::store()->init();
    }
    
    /**
     * Enqueue notification
     *
     * @param int $post_id Post ID
     * @param string $event Event type
     * @param array $destinations Destinations
     * @param array $options Additional options
     * @return int|false Action ID or false on failure
     */
    public function enqueue_notification($post_id, $event, $destinations = array(), $options = array()) {
        if (empty($destinations)) {
            $router = Plugin::get_instance()->get_component('router');
            $destinations = $router->get_destinations_for_post($post_id);
        }
        
        if (empty($destinations)) {
            log_message("No destinations found for post {$post_id}", 'warning', array(
                'post_id' => $post_id,
                'event' => $event,
            ));
            return false;
        }
        
        $payload = array(
            'post_id' => $post_id,
            'event' => $event,
            'destinations' => $destinations,
            'options' => $options,
            'deduplication_key' => $this->generate_deduplication_key($post_id, $event),
        );
        
        // Check if action already exists
        if ($this->action_exists($payload['deduplication_key'])) {
            log_message("Action already exists for post {$post_id} event {$event}", 'info', array(
                'post_id' => $post_id,
                'event' => $event,
                'deduplication_key' => $payload['deduplication_key'],
            ));
            return false;
        }
        
        // Schedule the action
        $action_id = as_schedule_single_action(
            time(),
            'wptpn_send_notification',
            array($payload),
            $this->action_group
        );
        
        if ($action_id) {
            log_message("Notification enqueued for post {$post_id}", 'info', array(
                'post_id' => $post_id,
                'event' => $event,
                'action_id' => $action_id,
                'destinations' => $destinations,
            ));
        }
        
        return $action_id;
    }
    
    /**
     * Send notification (action handler)
     *
     * @param array $payload Action payload
     */
    public function send_notification($payload) {
        $post_id = $payload['post_id'];
        $event = $payload['event'];
        $destinations = $payload['destinations'];
        $options = $payload['options'];
        
        // Check if post still exists
        $post = get_post($post_id);
        if (!$post) {
            log_message("Post {$post_id} not found, skipping notification", 'warning', array(
                'post_id' => $post_id,
                'event' => $event,
            ));
            return;
        }
        
        // Check if we should notify
        $should_notify = apply_filters('wptpn_should_notify', true, $post, $event);
        if (!$should_notify) {
            log_message("Notification skipped for post {$post_id} due to filter", 'info', array(
                'post_id' => $post_id,
                'event' => $event,
            ));
            return;
        }
        
        // Get template engine
        $template = Plugin::get_instance()->get_component('template');
        $telegram_client = Plugin::get_instance()->get_component('telegram_client');
        $logger = Plugin::get_instance()->get_component('logger');
        
        // Process each destination
        foreach ($destinations as $destination) {
            $this->send_to_destination($post, $destination, $event, $options, $template, $telegram_client, $logger);
        }
    }
    
    /**
     * Send notification to specific destination
     *
     * @param \WP_Post $post Post object
     * @param array $destination Destination configuration
     * @param string $event Event type
     * @param array $options Additional options
     * @param Template $template Template engine
     * @param TelegramClient $telegram_client Telegram client
     * @param Logger $logger Logger
     */
    private function send_to_destination($post, $destination, $event, $options, $template, $telegram_client, $logger) {
        $chat_id = $destination['chat_id'];
        $template_id = $destination['template_id'] ?? 'default';
        
        // Generate message
        $message_data = $template->render($post, $template_id, $event);
        
        if (!$message_data['success']) {
            $logger->log_notification(
                $post->ID,
                $event,
                $chat_id,
                'error',
                null,
                $message_data['error']
            );
            return;
        }
        
        $text = $message_data['text'];
        $photo_url = $message_data['photo_url'];
        $reply_markup = $message_data['reply_markup'];
        
        // Prepare options
        $send_options = array_merge($options, array(
            'reply_markup' => $reply_markup,
        ));
        
        // Fire before send action
        do_action('wptpn_before_send', array(
            'post' => $post,
            'destination' => $destination,
            'event' => $event,
            'message' => $text,
            'photo_url' => $photo_url,
        ), array(
            'post_id' => $post->ID,
            'chat_id' => $chat_id,
        ));
        
        // Send message
        if (!empty($photo_url)) {
            $result = $telegram_client->send_message_with_photo($chat_id, $text, $photo_url, $send_options);
        } else {
            $result = $telegram_client->send_message($chat_id, $text, $send_options);
        }
        
        // Log result
        if ($result['success']) {
            $logger->log_notification(
                $post->ID,
                $event,
                $chat_id,
                'success',
                $text
            );
            
            // Fire after send action
            do_action('wptpn_after_send', $result, array(
                'post_id' => $post->ID,
                'chat_id' => $chat_id,
                'event' => $event,
            ));
        } else {
            $logger->log_notification(
                $post->ID,
                $event,
                $chat_id,
                'error',
                $text,
                $result['error']
            );
            
            // Fire send failed action
            do_action('wptpn_send_failed', $result['error'], array(
                'post_id' => $post->ID,
                'chat_id' => $chat_id,
                'event' => $event,
            ));
        }
    }
    
    /**
     * Check if action exists
     *
     * @param string $deduplication_key Deduplication key
     * @return bool
     */
    private function action_exists($deduplication_key) {
        if (!class_exists('ActionScheduler')) {
            return false;
        }
        
        $actions = as_get_scheduled_actions(array(
            'hook' => 'wptpn_send_notification',
            'group' => $this->action_group,
            'status' => ActionScheduler_Store::STATUS_PENDING,
        ));
        
        foreach ($actions as $action) {
            $args = $action->get_args();
            if (!empty($args[0]['deduplication_key']) && $args[0]['deduplication_key'] === $deduplication_key) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generate deduplication key
     *
     * @param int $post_id Post ID
     * @param string $event Event type
     * @return string
     */
    private function generate_deduplication_key($post_id, $event) {
        return 'wptpn_' . $post_id . '_' . $event . '_' . time();
    }
    
    /**
     * Cleanup logs
     */
    public function cleanup_logs() {
        $logger = Plugin::get_instance()->get_component('logger');
        if ($logger) {
            $logger->cleanup_old_logs();
        }
    }
    
    /**
     * Get pending actions count
     *
     * @return int
     */
    public function get_pending_actions_count() {
        if (!class_exists('ActionScheduler')) {
            return 0;
        }
        
        $actions = as_get_scheduled_actions(array(
            'hook' => 'wptpn_send_notification',
            'group' => $this->action_group,
            'status' => ActionScheduler_Store::STATUS_PENDING,
        ));
        
        return count($actions);
    }
    
    /**
     * Get failed actions count
     *
     * @return int
     */
    public function get_failed_actions_count() {
        if (!class_exists('ActionScheduler')) {
            return 0;
        }
        
        $actions = as_get_scheduled_actions(array(
            'hook' => 'wptpn_send_notification',
            'group' => $this->action_group,
            'status' => ActionScheduler_Store::STATUS_FAILED,
        ));
        
        return count($actions);
    }
    
    /**
     * Clear all actions
     */
    public function clear_all_actions() {
        if (!class_exists('ActionScheduler')) {
            return;
        }
        
        as_unschedule_all_actions('wptpn_send_notification', array(), $this->action_group);
    }
    
    /**
     * Get action scheduler status
     *
     * @return array
     */
    public function get_status() {
        if (!class_exists('ActionScheduler')) {
            return array(
                'available' => false,
                'message' => __('Action Scheduler is not available', 'wp-telegram-post-notifier'),
            );
        }
        
        $pending = $this->get_pending_actions_count();
        $failed = $this->get_failed_actions_count();
        
        return array(
            'available' => true,
            'pending' => $pending,
            'failed' => $failed,
            'message' => sprintf(
                __('Pending: %d, Failed: %d', 'wp-telegram-post-notifier'),
                $pending,
                $failed
            ),
        );
    }
}
