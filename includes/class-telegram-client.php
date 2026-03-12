<?php
/**
 * Telegram Client Class
 *
 * @package VoxHash\WPTPN
 */

namespace VoxHash\WPTPN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Telegram Client Class
 */
class TelegramClient {
    
    /**
     * Telegram API base URL
     *
     * @var string
     */
    private $api_base_url = 'https://api.telegram.org/bot';
    
    /**
     * Bot token
     *
     * @var string
     */
    private $bot_token;
    
    /**
     * Rate limiting settings
     *
     * @var array
     */
    private $rate_limit_settings;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->bot_token = get_option('bot_token', '');
        $this->rate_limit_settings = array(
            'max_retries' => get_option('rate_limit_retries', 5),
            'base_delay' => get_option('rate_limit_delay', 5),
        );
    }
    
    /**
     * Initialize
     */
    public function init() {
        // Initialize if needed
    }
    
    /**
     * Set bot token
     *
     * @param string $token Bot token
     */
    public function set_bot_token($token) {
        $this->bot_token = $token;
    }
    
    /**
     * Get bot token
     *
     * @return string
     */
    public function get_bot_token() {
        return $this->bot_token;
    }
    
    /**
     * Test bot connection
     *
     * @return array
     */
    public function test_connection() {
        if (empty($this->bot_token)) {
            return array(
                'success' => false,
                'error' => __('Bot token is not set', 'wp-telegram-post-notifier'),
            );
        }
        
        $response = $this->make_request('getMe');
        
        if ($response['success']) {
            return array(
                'success' => true,
                'data' => $response['data'],
            );
        }
        
        return array(
            'success' => false,
            'error' => $response['error'],
        );
    }
    
    /**
     * Send message
     *
     * @param string $chat_id Chat ID
     * @param string $text Message text
     * @param array $options Message options
     * @return array
     */
    public function send_message($chat_id, $text, $options = array()) {
        $defaults = array(
            'parse_mode' => get_option('parse_mode', 'MarkdownV2'),
            'disable_web_page_preview' => get_option('disable_web_page_preview', false),
            'disable_notification' => get_option('send_silent', false),
        );
        
        $options = array_merge($defaults, $options);
        
        $data = array(
            'chat_id' => $chat_id,
            'text' => $text,
        );
        
        // Add optional parameters
        if (!empty($options['parse_mode'])) {
            $data['parse_mode'] = $options['parse_mode'];
        }
        
        if (isset($options['disable_web_page_preview'])) {
            $data['disable_web_page_preview'] = $options['disable_web_page_preview'];
        }
        
        if (isset($options['disable_notification'])) {
            $data['disable_notification'] = $options['disable_notification'];
        }
        
        if (!empty($options['reply_markup'])) {
            $data['reply_markup'] = $options['reply_markup'];
        }
        
        return $this->make_request('sendMessage', $data);
    }
    
    /**
     * Send photo
     *
     * @param string $chat_id Chat ID
     * @param string $photo Photo URL or file path
     * @param string $caption Photo caption
     * @param array $options Message options
     * @return array
     */
    public function send_photo($chat_id, $photo, $caption = '', $options = array()) {
        $defaults = array(
            'parse_mode' => get_option('parse_mode', 'MarkdownV2'),
            'disable_notification' => get_option('send_silent', false),
        );
        
        $options = array_merge($defaults, $options);
        
        $data = array(
            'chat_id' => $chat_id,
            'photo' => $photo,
        );
        
        if (!empty($caption)) {
            $data['caption'] = $caption;
        }
        
        // Add optional parameters
        if (!empty($options['parse_mode'])) {
            $data['parse_mode'] = $options['parse_mode'];
        }
        
        if (isset($options['disable_notification'])) {
            $data['disable_notification'] = $options['disable_notification'];
        }
        
        if (!empty($options['reply_markup'])) {
            $data['reply_markup'] = $options['reply_markup'];
        }
        
        $response = $this->make_request('sendPhoto', $data);
        
        // If photo fails due to size, fallback to text with image URL
        if (!$response['success'] && isset($response['error_code']) && $response['error_code'] === 413) {
            $text = $caption;
            if (!empty($photo)) {
                $text = $photo . "\n\n" . $text;
            }
            
            return $this->send_message($chat_id, $text, $options);
        }
        
        return $response;
    }
    
    /**
     * Send message with photo
     *
     * @param string $chat_id Chat ID
     * @param string $text Message text
     * @param string $photo_url Photo URL
     * @param array $options Message options
     * @return array
     */
    public function send_message_with_photo($chat_id, $text, $photo_url, $options = array()) {
        if (empty($photo_url)) {
            return $this->send_message($chat_id, $text, $options);
        }
        
        return $this->send_photo($chat_id, $photo_url, $text, $options);
    }
    
    /**
     * Make API request with retry logic
     *
     * @param string $method API method
     * @param array $data Request data
     * @param int $attempt Current attempt
     * @return array
     */
    private function make_request($method, $data = array(), $attempt = 1) {
        if (empty($this->bot_token)) {
            return array(
                'success' => false,
                'error' => __('Bot token is not set', 'wp-telegram-post-notifier'),
            );
        }
        
        $url = $this->api_base_url . $this->bot_token . '/' . $method;
        
        $args = array(
            'method' => 'POST',
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode($data),
        );
        
        // Apply filters
        $args = apply_filters('wptpn_http_args', $args, $method, $data);
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            log_message("Telegram API request failed: {$error_message}", 'error', array(
                'method' => $method,
                'data' => $data,
            ));
            
            return array(
                'success' => false,
                'error' => $error_message,
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        
        // Handle rate limiting
        if ($response_code === 429) {
            if ($attempt <= $this->rate_limit_settings['max_retries']) {
                $retry_after = isset($response_data['parameters']['retry_after']) 
                    ? $response_data['parameters']['retry_after'] 
                    : $this->rate_limit_settings['base_delay'] * pow(2, $attempt - 1);
                
                // Add jitter to prevent thundering herd
                $jitter = wp_rand(0, 1000) / 1000;
                $retry_after += $jitter;
                
                log_message("Rate limited, retrying in {$retry_after} seconds (attempt {$attempt})", 'warning', array(
                    'method' => $method,
                    'retry_after' => $retry_after,
                ));
                
                sleep($retry_after);
                return $this->make_request($method, $data, $attempt + 1);
            } else {
                log_message("Rate limit exceeded, max retries reached", 'error', array(
                    'method' => $method,
                    'max_retries' => $this->rate_limit_settings['max_retries'],
                ));
                
                return array(
                    'success' => false,
                    'error' => __('Rate limit exceeded', 'wp-telegram-post-notifier'),
                );
            }
        }
        
        // Handle server errors
        if ($response_code >= 500) {
            if ($attempt <= $this->rate_limit_settings['max_retries']) {
                $delay = $this->rate_limit_settings['base_delay'] * pow(2, $attempt - 1);
                $jitter = wp_rand(0, 1000) / 1000;
                $delay += $jitter;
                
                log_message("Server error {$response_code}, retrying in {$delay} seconds (attempt {$attempt})", 'warning', array(
                    'method' => $method,
                    'response_code' => $response_code,
                ));
                
                sleep($delay);
                return $this->make_request($method, $data, $attempt + 1);
            } else {
                log_message("Server error {$response_code}, max retries reached", 'error', array(
                    'method' => $method,
                    'response_code' => $response_code,
                ));
                
                return array(
                    'success' => false,
                    'error' => sprintf(__('Server error: %d', 'wp-telegram-post-notifier'), $response_code),
                );
            }
        }
        
        // Handle API errors
        if (!$response_data['ok']) {
            $error_message = isset($response_data['description']) 
                ? $response_data['description'] 
                : __('Unknown API error', 'wp-telegram-post-notifier');
            
            log_message("Telegram API error: {$error_message}", 'error', array(
                'method' => $method,
                'response' => $response_data,
            ));
            
            return array(
                'success' => false,
                'error' => $error_message,
                'error_code' => isset($response_data['error_code']) ? $response_data['error_code'] : null,
            );
        }
        
        return array(
            'success' => true,
            'data' => $response_data['result'],
        );
    }
    
    /**
     * Get chat information
     *
     * @param string $chat_id Chat ID
     * @return array
     */
    public function get_chat($chat_id) {
        return $this->make_request('getChat', array('chat_id' => $chat_id));
    }
    
    /**
     * Get bot information
     *
     * @return array
     */
    public function get_me() {
        return $this->make_request('getMe');
    }
    
    /**
     * Validate chat ID format
     *
     * @param string $chat_id Chat ID
     * @return bool
     */
    public function is_valid_chat_id($chat_id) {
        // Channel format: @channelname or -1001234567890
        // Group format: -1234567890
        // User format: 1234567890
        return preg_match('/^(@[a-zA-Z0-9_]+|(-100)?\d+)$/', $chat_id);
    }
    
    /**
     * Format chat ID for API
     *
     * @param string $chat_id Chat ID
     * @return string
     */
    public function format_chat_id($chat_id) {
        // Remove @ symbol for channels
        if (strpos($chat_id, '@') === 0) {
            return $chat_id;
        }
        
        // Ensure numeric chat IDs are strings
        if (is_numeric($chat_id)) {
            return (string) $chat_id;
        }
        
        return $chat_id;
    }
}
