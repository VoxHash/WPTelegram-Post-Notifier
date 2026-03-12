<?php
/**
 * Template Class
 *
 * @package VoxHash\WPTPN
 */

namespace VoxHash\WPTPN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template Class
 */
class Template {
    
    /**
     * Available tokens
     *
     * @var array
     */
    private $tokens = array();
    
    /**
     * Parse mode
     *
     * @var string
     */
    private $parse_mode;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->parse_mode = get_option('parse_mode', 'MarkdownV2');
        $this->init_tokens();
    }
    
    /**
     * Initialize
     */
    public function init() {
        // Initialize if needed
    }
    
    /**
     * Initialize available tokens
     */
    private function init_tokens() {
        $this->tokens = array(
            'site_name' => __('Site Name', 'wp-telegram-post-notifier'),
            'site_url' => __('Site URL', 'wp-telegram-post-notifier'),
            'post_title' => __('Post Title', 'wp-telegram-post-notifier'),
            'post_excerpt' => __('Post Excerpt', 'wp-telegram-post-notifier'),
            'post_content' => __('Post Content', 'wp-telegram-post-notifier'),
            'post_author' => __('Post Author', 'wp-telegram-post-notifier'),
            'post_date' => __('Post Date', 'wp-telegram-post-notifier'),
            'post_url' => __('Post URL', 'wp-telegram-post-notifier'),
            'short_url' => __('Short URL', 'wp-telegram-post-notifier'),
            'post_thumbnail_url' => __('Post Thumbnail URL', 'wp-telegram-post-notifier'),
            'categories' => __('Categories', 'wp-telegram-post-notifier'),
            'tags' => __('Tags', 'wp-telegram-post-notifier'),
            'price' => __('Price', 'wp-telegram-post-notifier'),
            'sku' => __('SKU', 'wp-telegram-post-notifier'),
            'stock_status' => __('Stock Status', 'wp-telegram-post-notifier'),
        );
    }
    
    /**
     * Render template
     *
     * @param \WP_Post $post Post object
     * @param string $template_id Template ID
     * @param string $event Event type
     * @return array
     */
    public function render($post, $template_id = 'default', $event = 'publish') {
        $template = $this->get_template($template_id);
        
        if (!$template) {
            return array(
                'success' => false,
                'error' => __('Template not found', 'wp-telegram-post-notifier'),
            );
        }
        
        // Get token values
        $token_values = $this->get_token_values($post, $event);
        
        // Apply filters
        $token_values = apply_filters('wptpn_template_tokens', $token_values, $post);
        
        // Replace tokens in template
        $text = $this->replace_tokens($template['text'], $token_values);
        
        // Escape for parse mode
        $text = $this->escape_for_parse_mode($text, $this->parse_mode);
        
        // Get photo URL
        $photo_url = $this->get_photo_url($post, $template);
        
        // Get reply markup
        $reply_markup = $this->get_reply_markup($template, $token_values);
        
        return array(
            'success' => true,
            'text' => $text,
            'photo_url' => $photo_url,
            'reply_markup' => $reply_markup,
        );
    }
    
    /**
     * Get template
     *
     * @param string $template_id Template ID
     * @return array|null
     */
    private function get_template($template_id) {
        $templates = get_option('templates', array());
        
        if ($template_id === 'default') {
            return array(
                'text' => get_option('template', "New post: {post_title}\n\n{post_excerpt}\n\nRead more: {post_url}"),
                'photo' => 'featured',
                'buttons' => array(),
            );
        }
        
        foreach ($templates as $template) {
            if ($template['id'] === $template_id) {
                return $template;
            }
        }
        
        return null;
    }
    
    /**
     * Get token values
     *
     * @param \WP_Post $post Post object
     * @param string $event Event type
     * @return array
     */
    private function get_token_values($post, $event) {
        $values = array();
        
        // Basic site information
        $values['site_name'] = get_bloginfo('name');
        $values['site_url'] = home_url();
        
        // Post information
        $values['post_title'] = get_the_title($post->ID);
        $values['post_excerpt'] = $this->get_post_excerpt($post);
        $values['post_content'] = $this->get_post_content($post);
        $values['post_author'] = get_the_author_meta('display_name', $post->post_author);
        $values['post_date'] = get_the_date('', $post->ID);
        $values['post_url'] = get_permalink($post->ID);
        $values['short_url'] = get_short_url($post->ID);
        $values['post_thumbnail_url'] = $this->get_post_thumbnail_url($post);
        
        // Categories and tags
        $values['categories'] = $this->get_post_categories($post);
        $values['tags'] = $this->get_post_tags($post);
        
        // WooCommerce specific tokens
        if (is_woocommerce_active() && $post->post_type === 'product') {
            $product = wc_get_product($post->ID);
            if ($product) {
                $values['price'] = $product->get_price_html();
                $values['sku'] = $product->get_sku();
                $values['stock_status'] = $product->get_stock_status();
            }
        }
        
        return $values;
    }
    
    /**
     * Get post excerpt
     *
     * @param \WP_Post $post Post object
     * @return string
     */
    private function get_post_excerpt($post) {
        if (!empty($post->post_excerpt)) {
            return $post->post_excerpt;
        }
        
        $excerpt = wp_trim_words($post->post_content, 55, '...');
        return $excerpt;
    }
    
    /**
     * Get post content
     *
     * @param \WP_Post $post Post object
     * @return string
     */
    private function get_post_content($post) {
        $content = $post->post_content;
        
        // Remove shortcodes
        $content = strip_shortcodes($content);
        
        // Remove HTML tags
        $content = wp_strip_all_tags($content);
        
        // Limit length
        $content = wp_trim_words($content, 100, '...');
        
        return $content;
    }
    
    /**
     * Get post thumbnail URL
     *
     * @param \WP_Post $post Post object
     * @return string
     */
    private function get_post_thumbnail_url($post) {
        $thumbnail_id = get_post_thumbnail_id($post->ID);
        
        if (!$thumbnail_id) {
            return '';
        }
        
        $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'full');
        
        return $thumbnail_url ? $thumbnail_url : '';
    }
    
    /**
     * Get post categories
     *
     * @param \WP_Post $post Post object
     * @return string
     */
    private function get_post_categories($post) {
        $categories = get_the_category($post->ID);
        
        if (empty($categories)) {
            return '';
        }
        
        $category_names = array();
        foreach ($categories as $category) {
            $category_names[] = $category->name;
        }
        
        return implode(', ', $category_names);
    }
    
    /**
     * Get post tags
     *
     * @param \WP_Post $post Post object
     * @return string
     */
    private function get_post_tags($post) {
        $tags = get_the_tags($post->ID);
        
        if (empty($tags)) {
            return '';
        }
        
        $tag_names = array();
        foreach ($tags as $tag) {
            $tag_names[] = $tag->name;
        }
        
        return implode(', ', $tag_names);
    }
    
    /**
     * Replace tokens in text
     *
     * @param string $text Text with tokens
     * @param array $values Token values
     * @return string
     */
    private function replace_tokens($text, $values) {
        foreach ($values as $token => $value) {
            $text = str_replace('{' . $token . '}', $value, $text);
        }
        
        return $text;
    }
    
    /**
     * Escape text for parse mode
     *
     * @param string $text Text to escape
     * @param string $parse_mode Parse mode
     * @return string
     */
    private function escape_for_parse_mode($text, $parse_mode) {
        switch ($parse_mode) {
            case 'MarkdownV2':
                return $this->escape_markdown_v2($text);
            case 'HTML':
                return $this->escape_html($text);
            case 'Markdown':
                return $this->escape_markdown($text);
            default:
                return $text;
        }
    }
    
    /**
     * Escape for MarkdownV2
     *
     * @param string $text Text to escape
     * @return string
     */
    private function escape_markdown_v2($text) {
        $chars = array('_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!');
        
        foreach ($chars as $char) {
            $text = str_replace($char, '\\' . $char, $text);
        }
        
        return $text;
    }
    
    /**
     * Escape for HTML
     *
     * @param string $text Text to escape
     * @return string
     */
    private function escape_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Escape for Markdown
     *
     * @param string $text Text to escape
     * @return string
     */
    private function escape_markdown($text) {
        $chars = array('\\', '`', '*', '_', '{', '}', '[', ']', '(', ')', '#', '+', '-', '.', '!');
        
        foreach ($chars as $char) {
            $text = str_replace($char, '\\' . $char, $text);
        }
        
        return $text;
    }
    
    /**
     * Get photo URL
     *
     * @param \WP_Post $post Post object
     * @param array $template Template configuration
     * @return string
     */
    private function get_photo_url($post, $template) {
        $photo_setting = $template['photo'] ?? 'featured';
        
        switch ($photo_setting) {
            case 'featured':
                return $this->get_post_thumbnail_url($post);
            case 'first_image':
                return $this->get_first_image_url($post);
            case 'none':
            default:
                return '';
        }
    }
    
    /**
     * Get first image URL from post content
     *
     * @param \WP_Post $post Post object
     * @return string
     */
    private function get_first_image_url($post) {
        $content = $post->post_content;
        
        // Look for images in content
        preg_match('/<img[^>]+src="([^"]+)"/', $content, $matches);
        
        if (!empty($matches[1])) {
            return $matches[1];
        }
        
        // Fallback to featured image
        return $this->get_post_thumbnail_url($post);
    }
    
    /**
     * Get reply markup
     *
     * @param array $template Template configuration
     * @param array $token_values Token values
     * @return string
     */
    private function get_reply_markup($template, $token_values) {
        $buttons = $template['buttons'] ?? array();
        
        if (empty($buttons)) {
            return '';
        }
        
        $inline_keyboard = array();
        
        foreach ($buttons as $button) {
            if (empty($button['text']) || empty($button['url'])) {
                continue;
            }
            
            $text = $this->replace_tokens($button['text'], $token_values);
            $url = $this->replace_tokens($button['url'], $token_values);
            
            $inline_keyboard[] = array(
                array(
                    'text' => $text,
                    'url' => $url,
                ),
            );
        }
        
        if (empty($inline_keyboard)) {
            return '';
        }
        
        return wp_json_encode(array(
            'inline_keyboard' => $inline_keyboard,
        ));
    }
    
    /**
     * Get available tokens
     *
     * @return array
     */
    public function get_available_tokens() {
        return $this->tokens;
    }
    
    /**
     * Preview template
     *
     * @param string $template_text Template text
     * @param int $post_id Post ID for preview
     * @return array
     */
    public function preview($template_text, $post_id = null) {
        if (!$post_id) {
            $posts = get_posts(array(
                'numberposts' => 1,
                'post_status' => 'publish',
            ));
            
            if (empty($posts)) {
                return array(
                    'success' => false,
                    'error' => __('No posts available for preview', 'wp-telegram-post-notifier'),
                );
            }
            
            $post_id = $posts[0]->ID;
        }
        
        $post = get_post($post_id);
        if (!$post) {
            return array(
                'success' => false,
                'error' => __('Post not found', 'wp-telegram-post-notifier'),
            );
        }
        
        // Get token values
        $token_values = $this->get_token_values($post, 'preview');
        
        // Replace tokens
        $text = $this->replace_tokens($template_text, $token_values);
        
        // Escape for different parse modes
        $previews = array();
        foreach (get_parse_modes() as $mode => $label) {
            $previews[$mode] = $this->escape_for_parse_mode($text, $mode);
        }
        
        return array(
            'success' => true,
            'previews' => $previews,
            'tokens_used' => $this->get_used_tokens($template_text),
        );
    }
    
    /**
     * Get used tokens in template
     *
     * @param string $template_text Template text
     * @return array
     */
    private function get_used_tokens($template_text) {
        $used_tokens = array();
        
        foreach (array_keys($this->tokens) as $token) {
            if (strpos($template_text, '{' . $token . '}') !== false) {
                $used_tokens[] = $token;
            }
        }
        
        return $used_tokens;
    }
    
    /**
     * Validate template
     *
     * @param string $template_text Template text
     * @return array
     */
    public function validate($template_text) {
        $errors = array();
        
        // Check for valid tokens
        preg_match_all('/\{([^}]+)\}/', $template_text, $matches);
        
        if (!empty($matches[1])) {
            $available_tokens = array_keys($this->tokens);
            
            foreach ($matches[1] as $token) {
                if (!in_array($token, $available_tokens)) {
                    $errors[] = sprintf(
                        __('Unknown token: %s', 'wp-telegram-post-notifier'),
                        $token
                    );
                }
            }
        }
        
        // Check for parse mode specific issues
        if ($this->parse_mode === 'MarkdownV2') {
            // Check for unescaped special characters
            $special_chars = array('_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!');
            
            foreach ($special_chars as $char) {
                if (strpos($template_text, $char) !== false && strpos($template_text, '\\' . $char) === false) {
                    $errors[] = sprintf(
                        __('Unescaped special character in MarkdownV2: %s', 'wp-telegram-post-notifier'),
                        $char
                    );
                }
            }
        }
        
        return array(
            'valid' => empty($errors),
            'errors' => $errors,
        );
    }
}
