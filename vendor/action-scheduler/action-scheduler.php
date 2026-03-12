<?php
/**
 * Action Scheduler Bootstrap
 *
 * @package VoxHash\WPTPN
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load Action Scheduler
if (!class_exists('ActionScheduler')) {
    require_once __DIR__ . '/classes/ActionScheduler.php';
    require_once __DIR__ . '/classes/ActionScheduler_Store.php';
    require_once __DIR__ . '/classes/ActionScheduler_Logger.php';
    require_once __DIR__ . '/classes/ActionScheduler_QueueRunner.php';
    require_once __DIR__ . '/classes/ActionScheduler_AdminView.php';
    require_once __DIR__ . '/classes/ActionScheduler_AdminHelp.php';
    require_once __DIR__ . '/classes/ActionScheduler_ListTable.php';
    require_once __DIR__ . '/classes/ActionScheduler_QueueCleaner.php';
    require_once __DIR__ . '/classes/ActionScheduler_AsyncRequest_QueueRunner.php';
    require_once __DIR__ . '/classes/ActionScheduler_DataController.php';
    require_once __DIR__ . '/classes/ActionScheduler_DateTime.php';
    require_once __DIR__ . '/classes/ActionScheduler_LoggerSchema.php';
    require_once __DIR__ . '/classes/ActionScheduler_LogEntry.php';
    require_once __DIR__ . '/classes/ActionScheduler_LogEntryFormatter.php';
}