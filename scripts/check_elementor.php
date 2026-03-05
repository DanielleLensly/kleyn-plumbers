<?php
require_once(dirname(dirname(__FILE__)) . '/wp-load.php');

$front_page_id = get_option('page_on_front');
echo "Front Page ID: $front_page_id\n";

$elementor_edit_mode = get_post_meta($front_page_id, '_elementor_edit_mode', true);
$elementor_template = get_post_meta($front_page_id, '_wp_page_template', true);

echo "Elementor Edit Mode: " . ($elementor_edit_mode ? 'YES' : 'NO') . "\n";
echo "Page Template: " . $elementor_template . "\n";
