<?php
/**
 * Router Class
 *
 * @package VoxHash\WPTPN
 */

namespace VoxHash\WPTPN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Router Class
 */
class Router {
    
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
        // Initialize if needed
    }
    
    /**
     * Get destinations for post
     *
     * @param int $post_id Post ID
     * @return array
     */
    public function get_destinations_for_post($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return array();
        }
        
        $destinations = array();
        
        // Get default destinations
        $default_destinations = $this->get_default_destinations();
        $destinations = array_merge($destinations, $default_destinations);
        
        // Get category-based destinations
        $category_destinations = $this->get_category_destinations($post);
        $destinations = array_merge($destinations, $category_destinations);
        
        // Get tag-based destinations
        $tag_destinations = $this->get_tag_destinations($post);
        $destinations = array_merge($destinations, $tag_destinations);
        
        // Get post type specific destinations
        $post_type_destinations = $this->get_post_type_destinations($post);
        $destinations = array_merge($destinations, $post_type_destinations);
        
        // Get WooCommerce specific destinations
        if (is_woocommerce_active() && $post->post_type === 'product') {
            $woocommerce_destinations = $this->get_woocommerce_destinations($post);
            $destinations = array_merge($destinations, $woocommerce_destinations);
        }
        
        // Apply filters
        $destinations = apply_filters('wptpn_destinations', $destinations, $post);
        
        // Remove duplicates
        $destinations = $this->remove_duplicate_destinations($destinations);
        
        return $destinations;
    }
    
    /**
     * Get default destinations
     *
     * @return array
     */
    private function get_default_destinations() {
        $destinations = get_option('destinations', array());
        $default_destinations = array();
        
        foreach ($destinations as $destination) {
            if (empty($destination['enabled'])) {
                continue;
            }
            
            $default_destinations[] = array(
                'chat_id' => $destination['chat_id'],
                'template_id' => $destination['template_id'] ?? 'default',
                'name' => $destination['name'] ?? '',
                'type' => 'default',
            );
        }
        
        return $default_destinations;
    }
    
    /**
     * Get category-based destinations
     *
     * @param \WP_Post $post Post object
     * @return array
     */
    private function get_category_destinations($post) {
        $routing_rules = get_option('routing_rules', array());
        $destinations = array();
        
        if (empty($routing_rules['categories'])) {
            return $destinations;
        }
        
        $post_categories = wp_get_post_categories($post->ID, array('fields' => 'ids'));
        
        foreach ($routing_rules['categories'] as $rule) {
            if (empty($rule['enabled']) || empty($rule['destinations'])) {
                continue;
            }
            
            $rule_categories = $rule['categories'] ?? array();
            if (empty($rule_categories)) {
                continue;
            }
            
            // Check if post has any of the rule categories
            $has_category = !empty(array_intersect($post_categories, $rule_categories));
            
            if ($has_category) {
                foreach ($rule['destinations'] as $destination_id) {
                    $destination = $this->get_destination_by_id($destination_id);
                    if ($destination) {
                        $destinations[] = array(
                            'chat_id' => $destination['chat_id'],
                            'template_id' => $destination['template_id'] ?? 'default',
                            'name' => $destination['name'] ?? '',
                            'type' => 'category',
                            'rule_name' => $rule['name'] ?? '',
                        );
                    }
                }
            }
        }
        
        return $destinations;
    }
    
    /**
     * Get tag-based destinations
     *
     * @param \WP_Post $post Post object
     * @return array
     */
    private function get_tag_destinations($post) {
        $routing_rules = get_option('routing_rules', array());
        $destinations = array();
        
        if (empty($routing_rules['tags'])) {
            return $destinations;
        }
        
        $post_tags = wp_get_post_tags($post->ID, array('fields' => 'ids'));
        
        foreach ($routing_rules['tags'] as $rule) {
            if (empty($rule['enabled']) || empty($rule['destinations'])) {
                continue;
            }
            
            $rule_tags = $rule['tags'] ?? array();
            if (empty($rule_tags)) {
                continue;
            }
            
            // Check if post has any of the rule tags
            $has_tag = !empty(array_intersect($post_tags, $rule_tags));
            
            if ($has_tag) {
                foreach ($rule['destinations'] as $destination_id) {
                    $destination = $this->get_destination_by_id($destination_id);
                    if ($destination) {
                        $destinations[] = array(
                            'chat_id' => $destination['chat_id'],
                            'template_id' => $destination['template_id'] ?? 'default',
                            'name' => $destination['name'] ?? '',
                            'type' => 'tag',
                            'rule_name' => $rule['name'] ?? '',
                        );
                    }
                }
            }
        }
        
        return $destinations;
    }
    
    /**
     * Get post type specific destinations
     *
     * @param \WP_Post $post Post object
     * @return array
     */
    private function get_post_type_destinations($post) {
        $routing_rules = get_option('routing_rules', array());
        $destinations = array();
        
        if (empty($routing_rules['post_types'])) {
            return $destinations;
        }
        
        $post_type_rules = $routing_rules['post_types'] ?? array();
        
        if (isset($post_type_rules[$post->post_type])) {
            $rule = $post_type_rules[$post->post_type];
            
            if (!empty($rule['enabled']) && !empty($rule['destinations'])) {
                foreach ($rule['destinations'] as $destination_id) {
                    $destination = $this->get_destination_by_id($destination_id);
                    if ($destination) {
                        $destinations[] = array(
                            'chat_id' => $destination['chat_id'],
                            'template_id' => $destination['template_id'] ?? 'default',
                            'name' => $destination['name'] ?? '',
                            'type' => 'post_type',
                            'rule_name' => $rule['name'] ?? '',
                        );
                    }
                }
            }
        }
        
        return $destinations;
    }
    
    /**
     * Get WooCommerce specific destinations
     *
     * @param \WP_Post $post Post object
     * @return array
     */
    private function get_woocommerce_destinations($post) {
        $routing_rules = get_option('routing_rules', array());
        $destinations = array();
        
        if (empty($routing_rules['woocommerce'])) {
            return $destinations;
        }
        
        $woocommerce_rules = $routing_rules['woocommerce'] ?? array();
        
        // Check product categories
        $product_categories = wp_get_post_terms($post->ID, 'product_cat', array('fields' => 'ids'));
        
        foreach ($woocommerce_rules as $rule) {
            if (empty($rule['enabled']) || empty($rule['destinations'])) {
                continue;
            }
            
            $rule_categories = $rule['product_categories'] ?? array();
            $rule_tags = $rule['product_tags'] ?? array();
            
            $matches = false;
            
            // Check product categories
            if (!empty($rule_categories) && !empty(array_intersect($product_categories, $rule_categories))) {
                $matches = true;
            }
            
            // Check product tags
            if (!$matches && !empty($rule_tags)) {
                $product_tags = wp_get_post_terms($post->ID, 'product_tag', array('fields' => 'ids'));
                if (!empty(array_intersect($product_tags, $rule_tags))) {
                    $matches = true;
                }
            }
            
            if ($matches) {
                foreach ($rule['destinations'] as $destination_id) {
                    $destination = $this->get_destination_by_id($destination_id);
                    if ($destination) {
                        $destinations[] = array(
                            'chat_id' => $destination['chat_id'],
                            'template_id' => $destination['template_id'] ?? 'default',
                            'name' => $destination['name'] ?? '',
                            'type' => 'woocommerce',
                            'rule_name' => $rule['name'] ?? '',
                        );
                    }
                }
            }
        }
        
        return $destinations;
    }
    
    /**
     * Get destination by ID
     *
     * @param string $destination_id Destination ID
     * @return array|null
     */
    private function get_destination_by_id($destination_id) {
        $destinations = get_option('destinations', array());
        
        foreach ($destinations as $destination) {
            if ($destination['id'] === $destination_id) {
                return $destination;
            }
        }
        
        return null;
    }
    
    /**
     * Remove duplicate destinations
     *
     * @param array $destinations Destinations array
     * @return array
     */
    private function remove_duplicate_destinations($destinations) {
        $unique_destinations = array();
        $seen = array();
        
        foreach ($destinations as $destination) {
            $key = $destination['chat_id'] . '_' . ($destination['template_id'] ?? 'default');
            
            if (!isset($seen[$key])) {
                $unique_destinations[] = $destination;
                $seen[$key] = true;
            }
        }
        
        return $unique_destinations;
    }
    
    /**
     * Get available taxonomies for routing
     *
     * @return array
     */
    public function get_available_taxonomies() {
        $taxonomies = get_taxonomies(array('public' => true), 'objects');
        $available = array();
        
        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy->name === 'nav_menu' || $taxonomy->name === 'link_category') {
                continue;
            }
            
            $available[$taxonomy->name] = array(
                'name' => $taxonomy->name,
                'label' => $taxonomy->label,
                'object_type' => $taxonomy->object_type,
            );
        }
        
        return $available;
    }
    
    /**
     * Get taxonomy terms
     *
     * @param string $taxonomy Taxonomy name
     * @return array
     */
    public function get_taxonomy_terms($taxonomy) {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));
        
        if (is_wp_error($terms)) {
            return array();
        }
        
        $formatted_terms = array();
        foreach ($terms as $term) {
            $formatted_terms[] = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
            );
        }
        
        return $formatted_terms;
    }
    
    /**
     * Get routing rules
     *
     * @return array
     */
    public function get_routing_rules() {
        return get_option('routing_rules', array());
    }
    
    /**
     * Update routing rules
     *
     * @param array $rules Routing rules
     * @return bool
     */
    public function update_routing_rules($rules) {
        return update_option('routing_rules', $rules);
    }
    
    /**
     * Get destinations
     *
     * @return array
     */
    public function get_destinations() {
        return get_option('destinations', array());
    }
    
    /**
     * Update destinations
     *
     * @param array $destinations Destinations
     * @return bool
     */
    public function update_destinations($destinations) {
        return update_option('destinations', $destinations);
    }
}
