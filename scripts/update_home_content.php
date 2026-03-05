<?php
// Load WordPress
require_once(dirname(dirname(__FILE__)) . '/wp-load.php');

// Get Home Page ID
$front_page_id = get_option('page_on_front');

if (!$front_page_id) {
    echo "No static front page set.\n";
    exit;
}

$post = get_post($front_page_id);
$content = $post->post_content;

// Check if shortcodes already exist
$new_content_added = false;

if (strpos($content, '[kleyn_services_list]') === false) {
    $content .= "\n\n<h2>Our Services</h2>\n[kleyn_services_list]";
    $new_content_added = true;
}

if (strpos($content, '[ai_scheduler_chat]') === false) {
    $content .= "\n\n<h3>Book an Appointment</h3>\n[ai_scheduler_chat]";
    $new_content_added = true;
}

if ($new_content_added) {
    $updated_post = array(
        'ID' => $front_page_id,
        'post_content' => $content,
    );

    wp_update_post($updated_post);
    echo "Home page updated successfully with services and chat widget.\n";
} else {
    echo "Home page already contains the shortcodes.\n";
}
