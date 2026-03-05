<?php
require_once(dirname(dirname(__FILE__)) . '/wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/plugin.php');

$plugin_path = 'ai-scheduler/ai-scheduler.php';
$result = activate_plugin($plugin_path);

if (is_wp_error($result)) {
    echo "Error activating plugin: " . $result->get_error_message() . "\n";
} else {
    echo "AI Scheduler plugin activated successfully.\n";
}
