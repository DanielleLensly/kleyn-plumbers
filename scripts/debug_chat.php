<?php
// Debug script to check if chat widget is being rendered
require_once('wp-load.php');

// Check if plugin is active
$active_plugins = get_option('active_plugins', []);
$plugin_active = in_array('ai-scheduler/ai-scheduler.php', $active_plugins);
echo "Plugin active: " . ($plugin_active ? 'YES' : 'NO') . "\n";

// Check if shortcode exists
$shortcode_exists = shortcode_exists('ai_scheduler_chat');
echo "Shortcode exists: " . ($shortcode_exists ? 'YES' : 'NO') . "\n";

// Execute shortcode
$chat_html = do_shortcode('[ai_scheduler_chat]');
$has_toggle = strpos($chat_html, 'ai-chat-toggle') !== false;
echo "Chat HTML contains toggle: " . ($has_toggle ? 'YES' : 'NO') . "\n";
echo "HTML length: " . strlen($chat_html) . " bytes\n";
