<?php
/**
 * Main Plugin Class
 *
 * @package VoxHash\WPTPN
 */

namespace VoxHash\WPTPN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Plugin Class
 */
class Plugin {
    
    /**
     * Plugin instance
     *
     * @var Plugin
     */
    private static $instance = null;
    
    /**
     * Plugin components
     *
     * @var array
     */
    private $components = array();
    
    /**
     * Get plugin instance
     *
     * @return Plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
        $this->init_components();
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Load helper functions
        require_once WPTPN_PLUGIN_DIR . 'includes/helpers.php';
        
        // Load core classes
        require_once WPTPN_PLUGIN_DIR . 'includes/class-telegram-client.php';
        require_once WPTPN_PLUGIN_DIR . 'includes/class-scheduler.php';
        require_once WPTPN_PLUGIN_DIR . 'includes/class-router.php';
        require_once WPTPN_PLUGIN_DIR . 'includes/class-template.php';
        require_once WPTPN_PLUGIN_DIR . 'includes/class-logger.php';
        require_once WPTPN_PLUGIN_DIR . 'includes/class-health.php';
        require_once WPTPN_PLUGIN_DIR . 'includes/class-telemetry.php';
        
        // Load admin classes
        if (is_admin()) {
            require_once WPTPN_PLUGIN_DIR . 'admin/class-admin.php';
            require_once WPTPN_PLUGIN_DIR . 'admin/class-settings.php';
            require_once WPTPN_PLUGIN_DIR . 'admin/class-rest.php';
        }
        
        // Load public classes
        require_once WPTPN_PLUGIN_DIR . 'public/class-gutenberg.php';
        
        // Load Action Scheduler
        $this->load_action_scheduler();
    }
    
    /**
     * Load Action Scheduler library
     */
    private function load_action_scheduler() {
        if (!class_exists('ActionScheduler')) {
            require_once WPTPN_PLUGIN_DIR . 'vendor/action-scheduler/action-scheduler.php';
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('wp_loaded', array($this, 'wp_loaded'));
        
        // Activation/Deactivation hooks
        register_activation_hook(WPTPN_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(WPTPN_PLUGIN_FILE, array($this, 'deactivate'));
    }
    
    /**
     * Initialize components
     */
    private function init_components() {
        // Initialize core components
        $this->components['telegram_client'] = new TelegramClient();
        $this->components['scheduler'] = new Scheduler();
        $this->components['router'] = new Router();
        $this->components['template'] = new Template();
        $this->components['logger'] = new Logger();
        $this->components['health'] = new Health();
        $this->components['telemetry'] = new Telemetry();
        
        // Initialize admin components
        if (is_admin()) {
            $this->components['admin'] = new Admin();
            $this->components['settings'] = new Settings();
            $this->components['rest'] = new Rest();
        }
        
        // Initialize public components
        $this->components['gutenberg'] = new Gutenberg();
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Set up text domain
        load_plugin_textdomain('wp-telegram-post-notifier', false, dirname(WPTPN_PLUGIN_BASENAME) . '/languages');
        
        // Initialize components
        foreach ($this->components as $component) {
            if (method_exists($component, 'init')) {
                $component->init();
            }
        }
    }
    
    /**
     * Admin initialization
     */
    public function admin_init() {
        // Initialize admin-specific functionality
        if (isset($this->components['admin'])) {
            $this->components['admin']->init();
        }
    }
    
    /**
     * WordPress loaded
     */
    public function wp_loaded() {
        // Initialize components that need WordPress to be fully loaded
        foreach ($this->components as $component) {
            if (method_exists($component, 'wp_loaded')) {
                $component->wp_loaded();
            }
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $this->create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Schedule events
        $this->schedule_events();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation flag
        set_transient('wptpn_activated', true, 60);
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        $this->clear_scheduled_events();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin uninstall
     */
    public function uninstall() {
        // Remove database tables
        $this->drop_tables();
        
        // Remove options
        $this->remove_options();
        
        // Clear transients
        $this->clear_transients();
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create logs table
        $table_name = $wpdb->prefix . 'wptpn_logs';
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            event varchar(50) NOT NULL,
            destination varchar(255) NOT NULL,
            status varchar(20) NOT NULL,
            message text,
            error_message text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        
        // Update database version
        update_option('wptpn_db_version', WPTPN_VERSION);
    }
    
    /**
     * Drop database tables
     */
    private function drop_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wptpn_logs';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        
        delete_option('wptpn_db_version');
    }
    
    /**
     * Set default options
     */
    private function set_default_options() {
        $defaults = array(
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
        );
        
        add_option('wptpn_settings', $defaults);
    }
    
    /**
     * Remove options
     */
    private function remove_options() {
        delete_option('wptpn_settings');
        delete_option('wptpn_db_version');
    }
    
    /**
     * Clear transients
     */
    private function clear_transients() {
        global $wpdb;
        
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wptpn_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wptpn_%'");
    }
    
    /**
     * Schedule events
     */
    private function schedule_events() {
        // Schedule log cleanup
        if (!wp_next_scheduled('wptpn_cleanup_logs')) {
            wp_schedule_event(time(), 'daily', 'wptpn_cleanup_logs');
        }
    }
    
    /**
     * Clear scheduled events
     */
    private function clear_scheduled_events() {
        wp_clear_scheduled_hook('wptpn_cleanup_logs');
    }
    
    /**
     * Get component
     *
     * @param string $name Component name
     * @return mixed|null
     */
    public function get_component($name) {
        return isset($this->components[$name]) ? $this->components[$name] : null;
    }
    
    /**
     * Get all components
     *
     * @return array
     */
    public function get_components() {
        return $this->components;
    }
}
