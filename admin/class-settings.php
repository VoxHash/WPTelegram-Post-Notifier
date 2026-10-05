<?php
/**
 * Settings Class
 *
 * @package VoxHash\WPTPN
 */

namespace VoxHash\WPTPN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings Class
 */
class Settings {
    
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
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // Register main settings group
        register_setting('wptpn_settings', 'wptpn_settings', array(
            'sanitize_callback' => array($this, 'sanitize_settings'),
        ));
        
        // Register individual options
        $this->register_individual_options();
    }
    
    /**
     * Register individual options
     */
    private function register_individual_options() {
        // Bot token
        add_option('wptpn_bot_token', '', '', 'no');
        
        // Destinations
        add_option('wptpn_destinations', array(), '', 'no');
        
        // Post types
        add_option('wptpn_post_types', array('post'), '', 'no');
        
        // Events
        add_option('wptpn_events', array('publish'), '', 'no');
        
        // Template
        add_option('wptpn_template', "New post: {post_title}\n\n{post_excerpt}\n\nRead more: {post_url}", '', 'no');
        
        // Parse mode
        add_option('wptpn_parse_mode', 'MarkdownV2', '', 'no');
        
        // Send silent
        add_option('wptpn_send_silent', false, '', 'no');
        
        // Disable web page preview
        add_option('wptpn_disable_web_page_preview', false, '', 'no');
        
        // Rate limiting
        add_option('wptpn_rate_limit_retries', 5, '', 'no');
        add_option('wptpn_rate_limit_delay', 5, '', 'no');
        
        // Logging
        add_option('wptpn_enable_logging', true, '', 'no');
        add_option('wptpn_log_retention_days', 30, '', 'no');
        
        // Telemetry
        add_option('wptpn_enable_telemetry', false, '', 'no');
        
        // Routing rules
        add_option('wptpn_routing_rules', array(), '', 'no');
    }
    
    /**
     * Sanitize settings
     *
     * @param array $input Input data
     * @return array
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Bot token
        if (isset($input['bot_token'])) {
            $sanitized['bot_token'] = sanitize_text_field($input['bot_token']);
        }
        
        // Destinations
        if (isset($input['destinations'])) {
            $sanitized['destinations'] = $this->sanitize_destinations($input['destinations']);
        }
        
        // Post types
        if (isset($input['post_types'])) {
            $sanitized['post_types'] = array_map('sanitize_text_field', (array) $input['post_types']);
        }
        
        // Events
        if (isset($input['events'])) {
            $sanitized['events'] = array_map('sanitize_text_field', (array) $input['events']);
        }
        
        // Template
        if (isset($input['template'])) {
            $sanitized['template'] = sanitize_textarea_field($input['template']);
        }
        
        // Parse mode
        if (isset($input['parse_mode'])) {
            $parse_modes = array_keys(get_parse_modes());
            $sanitized['parse_mode'] = in_array($input['parse_mode'], $parse_modes) ? $input['parse_mode'] : 'MarkdownV2';
        }
        
        // Boolean options
        $boolean_options = array(
            'send_silent',
            'disable_web_page_preview',
            'enable_logging',
            'enable_telemetry',
        );
        
        foreach ($boolean_options as $option) {
            if (isset($input[$option])) {
                $sanitized[$option] = (bool) $input[$option];
            }
        }
        
        // Numeric options
        if (isset($input['rate_limit_retries'])) {
            $sanitized['rate_limit_retries'] = max(1, min(10, intval($input['rate_limit_retries'])));
        }
        
        if (isset($input['rate_limit_delay'])) {
            $sanitized['rate_limit_delay'] = max(1, min(60, intval($input['rate_limit_delay'])));
        }
        
        if (isset($input['log_retention_days'])) {
            $sanitized['log_retention_days'] = max(1, min(365, intval($input['log_retention_days'])));
        }
        
        // Routing rules
        if (isset($input['routing_rules'])) {
            $sanitized['routing_rules'] = $this->sanitize_routing_rules($input['routing_rules']);
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize destinations
     *
     * @param array $destinations Destinations data
     * @return array
     */
    private function sanitize_destinations($destinations) {
        if (!is_array($destinations)) {
            return array();
        }
        
        $sanitized = array();
        
        foreach ($destinations as $destination) {
            if (!is_array($destination)) {
                continue;
            }
            
            $sanitized_destination = array();
            
            // ID
            if (isset($destination['id'])) {
                $sanitized_destination['id'] = sanitize_text_field($destination['id']);
            }
            
            // Name
            if (isset($destination['name'])) {
                $sanitized_destination['name'] = sanitize_text_field($destination['name']);
            }
            
            // Chat ID
            if (isset($destination['chat_id'])) {
                $sanitized_destination['chat_id'] = sanitize_text_field($destination['chat_id']);
            }
            
            // Template ID
            if (isset($destination['template_id'])) {
                $sanitized_destination['template_id'] = sanitize_text_field($destination['template_id']);
            }
            
            // Enabled
            if (isset($destination['enabled'])) {
                $sanitized_destination['enabled'] = (bool) $destination['enabled'];
            }
            
            $sanitized[] = $sanitized_destination;
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize routing rules
     *
     * @param array $rules Routing rules data
     * @return array
     */
    private function sanitize_routing_rules($rules) {
        if (!is_array($rules)) {
            return array();
        }
        
        $sanitized = array();
        
        // Categories
        if (isset($rules['categories']) && is_array($rules['categories'])) {
            $sanitized['categories'] = $this->sanitize_routing_rule_group($rules['categories']);
        }
        
        // Tags
        if (isset($rules['tags']) && is_array($rules['tags'])) {
            $sanitized['tags'] = $this->sanitize_routing_rule_group($rules['tags']);
        }
        
        // Post types
        if (isset($rules['post_types']) && is_array($rules['post_types'])) {
            $sanitized['post_types'] = $this->sanitize_post_type_rules($rules['post_types']);
        }
        
        // WooCommerce
        if (isset($rules['woocommerce']) && is_array($rules['woocommerce'])) {
            $sanitized['woocommerce'] = $this->sanitize_woocommerce_rules($rules['woocommerce']);
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize routing rule group
     *
     * @param array $rules Rules array
     * @return array
     */
    private function sanitize_routing_rule_group($rules) {
        $sanitized = array();
        
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            
            $sanitized_rule = array();
            
            // Name
            if (isset($rule['name'])) {
                $sanitized_rule['name'] = sanitize_text_field($rule['name']);
            }
            
            // Enabled
            if (isset($rule['enabled'])) {
                $sanitized_rule['enabled'] = (bool) $rule['enabled'];
            }
            
            // Categories/Tags
            if (isset($rule['categories']) && is_array($rule['categories'])) {
                $sanitized_rule['categories'] = array_map('intval', $rule['categories']);
            }
            
            if (isset($rule['tags']) && is_array($rule['tags'])) {
                $sanitized_rule['tags'] = array_map('intval', $rule['tags']);
            }
            
            // Destinations
            if (isset($rule['destinations']) && is_array($rule['destinations'])) {
                $sanitized_rule['destinations'] = array_map('sanitize_text_field', $rule['destinations']);
            }
            
            $sanitized[] = $sanitized_rule;
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize post type rules
     *
     * @param array $rules Post type rules
     * @return array
     */
    private function sanitize_post_type_rules($rules) {
        $sanitized = array();
        
        foreach ($rules as $post_type => $rule) {
            if (!is_array($rule)) {
                continue;
            }
            
            $sanitized_rule = array();
            
            // Name
            if (isset($rule['name'])) {
                $sanitized_rule['name'] = sanitize_text_field($rule['name']);
            }
            
            // Enabled
            if (isset($rule['enabled'])) {
                $sanitized_rule['enabled'] = (bool) $rule['enabled'];
            }
            
            // Destinations
            if (isset($rule['destinations']) && is_array($rule['destinations'])) {
                $sanitized_rule['destinations'] = array_map('sanitize_text_field', $rule['destinations']);
            }
            
            $sanitized[sanitize_text_field($post_type)] = $sanitized_rule;
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize WooCommerce rules
     *
     * @param array $rules WooCommerce rules
     * @return array
     */
    private function sanitize_woocommerce_rules($rules) {
        $sanitized = array();
        
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            
            $sanitized_rule = array();
            
            // Name
            if (isset($rule['name'])) {
                $sanitized_rule['name'] = sanitize_text_field($rule['name']);
            }
            
            // Enabled
            if (isset($rule['enabled'])) {
                $sanitized_rule['enabled'] = (bool) $rule['enabled'];
            }
            
            // Product categories
            if (isset($rule['product_categories']) && is_array($rule['product_categories'])) {
                $sanitized_rule['product_categories'] = array_map('intval', $rule['product_categories']);
            }
            
            // Product tags
            if (isset($rule['product_tags']) && is_array($rule['product_tags'])) {
                $sanitized_rule['product_tags'] = array_map('intval', $rule['product_tags']);
            }
            
            // Destinations
            if (isset($rule['destinations']) && is_array($rule['destinations'])) {
                $sanitized_rule['destinations'] = array_map('sanitize_text_field', $rule['destinations']);
            }
            
            $sanitized[] = $sanitized_rule;
        }
        
        return $sanitized;
    }
    
    /**
     * Get default settings
     *
     * @return array
     */
    public function get_default_settings() {
        return array(
            'bot_token' => '',
            'destinations' => array(),
            'post_types' => array('post'),
            'events' => array('publish'),
            'template' => "New post: {post_title}\n\n{post_excerpt}\n\nRead more: {post_url}",
            'parse_mode' => 'MarkdownV2',
            'send_silent' => false,
            'disable_web_page_preview' => false,
            'rate_limit_retries' => 5,
            'rate_limit_delay' => 5,
            'enable_logging' => true,
            'log_retention_days' => 30,
            'enable_telemetry' => false,
            'routing_rules' => array(),
        );
    }
    
    /**
     * Get settings
     *
     * @return array
     */
    public function get_settings() {
        $defaults = $this->get_default_settings();
        $settings = get_option('wptpn_settings', array());
        
        return wp_parse_args($settings, $defaults);
    }
    
    /**
     * Update settings
     *
     * @param array $settings Settings to update
     * @return bool
     */
    public function update_settings($settings) {
        $sanitized = $this->sanitize_settings($settings);
        return update_option('wptpn_settings', $sanitized);
    }
    
    /**
     * Reset settings to defaults
     *
     * @return bool
     */
    public function reset_settings() {
        $defaults = $this->get_default_settings();
        return update_option('wptpn_settings', $defaults);
    }
}
