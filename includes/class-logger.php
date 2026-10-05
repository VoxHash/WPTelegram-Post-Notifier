<?php
/**
 * Logger Class
 *
 * @package VoxHash\WPTPN
 */

namespace VoxHash\WPTPN;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Logger Class
 */
class Logger {
    
    /**
     * Log table name
     *
     * @var string
     */
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wptpn_logs';
    }
    
    /**
     * Initialize
     */
    public function init() {
        // Initialize if needed
    }
    
    /**
     * Log notification
     *
     * @param int $post_id Post ID
     * @param string $event Event type
     * @param string $destination Destination
     * @param string $status Status
     * @param string $message Message sent
     * @param string $error_message Error message
     * @return int|false Log ID or false on failure
     */
    public function log_notification($post_id, $event, $destination, $status, $message = null, $error_message = null) {
        global $wpdb;
        
        $data = array(
            'post_id' => $post_id,
            'event' => $event,
            'destination' => $destination,
            'status' => $status,
            'message' => $message,
            'error_message' => $error_message,
            'created_at' => current_time('mysql'),
        );
        
        $result = $wpdb->insert($this->table_name, $data);
        
        if ($result === false) {
            error_log('WPTPN Logger: Failed to insert log entry - ' . $wpdb->last_error);
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get logs
     *
     * @param array $args Query arguments
     * @return array
     */
    public function get_logs($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'post_id' => null,
            'status' => null,
            'destination' => null,
            'date_from' => null,
            'date_to' => null,
            'limit' => 50,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where_conditions = array('1=1');
        $where_values = array();
        
        if (!empty($args['post_id'])) {
            $where_conditions[] = 'post_id = %d';
            $where_values[] = $args['post_id'];
        }
        
        if (!empty($args['status'])) {
            $where_conditions[] = 'status = %s';
            $where_values[] = $args['status'];
        }
        
        if (!empty($args['destination'])) {
            $where_conditions[] = 'destination = %s';
            $where_values[] = $args['destination'];
        }
        
        if (!empty($args['date_from'])) {
            $where_conditions[] = 'created_at >= %s';
            $where_values[] = $args['date_from'];
        }
        
        if (!empty($args['date_to'])) {
            $where_conditions[] = 'created_at <= %s';
            $where_values[] = $args['date_to'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        if (!$orderby) {
            $orderby = 'created_at DESC';
        }
        
        $limit = intval($args['limit']);
        $offset = intval($args['offset']);
        
        $sql = "SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY {$orderby} LIMIT {$limit} OFFSET {$offset}";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        return $results ? $results : array();
    }
    
    /**
     * Get log count
     *
     * @param array $args Query arguments
     * @return int
     */
    public function get_log_count($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'post_id' => null,
            'status' => null,
            'destination' => null,
            'date_from' => null,
            'date_to' => null,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where_conditions = array('1=1');
        $where_values = array();
        
        if (!empty($args['post_id'])) {
            $where_conditions[] = 'post_id = %d';
            $where_values[] = $args['post_id'];
        }
        
        if (!empty($args['status'])) {
            $where_conditions[] = 'status = %s';
            $where_values[] = $args['status'];
        }
        
        if (!empty($args['destination'])) {
            $where_conditions[] = 'destination = %s';
            $where_values[] = $args['destination'];
        }
        
        if (!empty($args['date_from'])) {
            $where_conditions[] = 'created_at >= %s';
            $where_values[] = $args['date_from'];
        }
        
        if (!empty($args['date_to'])) {
            $where_conditions[] = 'created_at <= %s';
            $where_values[] = $args['date_to'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        return intval($wpdb->get_var($sql));
    }
    
    /**
     * Get log statistics
     *
     * @param array $args Query arguments
     * @return array
     */
    public function get_statistics($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'date_from' => null,
            'date_to' => null,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where_conditions = array('1=1');
        $where_values = array();
        
        if (!empty($args['date_from'])) {
            $where_conditions[] = 'created_at >= %s';
            $where_values[] = $args['date_from'];
        }
        
        if (!empty($args['date_to'])) {
            $where_conditions[] = 'created_at <= %s';
            $where_values[] = $args['date_to'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $sql = "SELECT 
                    status,
                    COUNT(*) as count,
                    COUNT(DISTINCT post_id) as unique_posts,
                    COUNT(DISTINCT destination) as unique_destinations
                FROM {$this->table_name} 
                WHERE {$where_clause}
                GROUP BY status";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        $stats = array(
            'total' => 0,
            'success' => 0,
            'error' => 0,
            'unique_posts' => 0,
            'unique_destinations' => 0,
        );
        
        foreach ($results as $result) {
            $stats['total'] += $result['count'];
            $stats[$result['status']] = $result['count'];
            $stats['unique_posts'] = max($stats['unique_posts'], $result['unique_posts']);
            $stats['unique_destinations'] = max($stats['unique_destinations'], $result['unique_destinations']);
        }
        
        return $stats;
    }
    
    /**
     * Get recent logs
     *
     * @param int $limit Number of logs to retrieve
     * @return array
     */
    public function get_recent_logs($limit = 10) {
        return $this->get_logs(array(
            'limit' => $limit,
            'orderby' => 'created_at',
            'order' => 'DESC',
        ));
    }
    
    /**
     * Get logs by status
     *
     * @param string $status Status to filter by
     * @param int $limit Number of logs to retrieve
     * @return array
     */
    public function get_logs_by_status($status, $limit = 50) {
        return $this->get_logs(array(
            'status' => $status,
            'limit' => $limit,
        ));
    }
    
    /**
     * Get error logs
     *
     * @param int $limit Number of logs to retrieve
     * @return array
     */
    public function get_error_logs($limit = 50) {
        return $this->get_logs_by_status('error', $limit);
    }
    
    /**
     * Get success logs
     *
     * @param int $limit Number of logs to retrieve
     * @return array
     */
    public function get_success_logs($limit = 50) {
        return $this->get_logs_by_status('success', $limit);
    }
    
    /**
     * Cleanup old logs
     *
     * @param int $days Number of days to keep
     * @return int Number of logs deleted
     */
    public function cleanup_old_logs($days = null) {
        global $wpdb;
        
        if ($days === null) {
            $days = get_option('log_retention_days', 30);
        }
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $result = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE created_at < %s",
            $cutoff_date
        ));
        
        if ($result !== false) {
            log_message("Cleaned up {$result} old log entries", 'info', array(
                'days' => $days,
                'cutoff_date' => $cutoff_date,
            ));
        }
        
        return $result;
    }
    
    /**
     * Clear all logs
     *
     * @return int Number of logs deleted
     */
    public function clear_all_logs() {
        global $wpdb;
        
        $result = $wpdb->query("DELETE FROM {$this->table_name}");
        
        if ($result !== false) {
            log_message("Cleared all log entries", 'info', array(
                'count' => $result,
            ));
        }
        
        return $result;
    }
    
    /**
     * Get log by ID
     *
     * @param int $log_id Log ID
     * @return array|null
     */
    public function get_log($log_id) {
        global $wpdb;
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $log_id
        ), ARRAY_A);
        
        return $result ? $result : null;
    }
    
    /**
     * Delete log
     *
     * @param int $log_id Log ID
     * @return bool
     */
    public function delete_log($log_id) {
        global $wpdb;
        
        $result = $wpdb->delete($this->table_name, array('id' => $log_id));
        
        return $result !== false;
    }
    
    /**
     * Get destinations list
     *
     * @return array
     */
    public function get_destinations_list() {
        global $wpdb;
        
        $results = $wpdb->get_results(
            "SELECT DISTINCT destination FROM {$this->table_name} ORDER BY destination",
            ARRAY_A
        );
        
        $destinations = array();
        foreach ($results as $result) {
            $destinations[] = $result['destination'];
        }
        
        return $destinations;
    }
    
    /**
     * Get events list
     *
     * @return array
     */
    public function get_events_list() {
        global $wpdb;
        
        $results = $wpdb->get_results(
            "SELECT DISTINCT event FROM {$this->table_name} ORDER BY event",
            ARRAY_A
        );
        
        $events = array();
        foreach ($results as $result) {
            $events[] = $result['event'];
        }
        
        return $events;
    }
    
    /**
     * Export logs to CSV
     *
     * @param array $args Query arguments
     * @return string CSV content
     */
    public function export_csv($args = array()) {
        $logs = $this->get_logs($args);
        
        $csv_data = array();
        $csv_data[] = array(
            'ID',
            'Post ID',
            'Event',
            'Destination',
            'Status',
            'Message',
            'Error Message',
            'Created At',
        );
        
        foreach ($logs as $log) {
            $csv_data[] = array(
                $log['id'],
                $log['post_id'],
                $log['event'],
                $log['destination'],
                $log['status'],
                $log['message'],
                $log['error_message'],
                $log['created_at'],
            );
        }
        
        $output = '';
        foreach ($csv_data as $row) {
            $output .= implode(',', array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }
        
        return $output;
    }
}
