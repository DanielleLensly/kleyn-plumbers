<?php
require_once(dirname(dirname(__FILE__)) . '/wp-load.php');

$front_page_id = get_option('page_on_front');
$post = get_post($front_page_id);

echo "Home Page ID: " . $front_page_id . "\n";
echo "Post Title: " . $post->post_title . "\n";
echo "Post Content:\n";
echo "--------------------------------------------------\n";
echo $post->post_content;
echo "\n--------------------------------------------------\n";

// Check if shortcode function exists
if (shortcode_exists('ai_scheduler_chat')) {
    echo "Shortcode [ai_scheduler_chat] is registered.\n";
} else {
    echo "Shortcode [ai_scheduler_chat] is NOT registered.\n";
}

// Try processing it manually
echo "Test Shortcode Processing:\n";
echo do_shortcode('[ai_scheduler_chat]');
