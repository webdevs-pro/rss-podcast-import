<?php
/*
Plugin Name: RSS Feed Podcast Importer
Plugin URI: https://github.com/webdevs-pro/rss-podcast-import/
Description: This plugin fetch RSS feed from BuzzSprout and import podcasts as posts to WordPress
Version: 1.0
Author: Alex Ischenko
Author URI: https://github.com/webdevs-pro/
Text Domain:  rss-podcast-import
*/


define('RFPI_PLUGIN_BASENAME', plugin_basename(__FILE__));


include( plugin_dir_path( __FILE__ ) . 'admin/admin.php');

// register metabox
function rfpi_add_metabox() {
    $screens = ['post'];
    foreach ($screens as $screen) {
        add_meta_box(
            'rfpi_metabox',           // Unique ID
            'Custom Meta Box Title',  // Box title
            'rfpi_metabox_html',  // Content callback, must be of type callable
            $screen,                  // Post type
            'side'                    // side
        );
    }
}
add_action('add_meta_boxes', 'rfpi_add_metabox');

function rfpi_metabox_html($post) {
    $value = get_post_meta($post->ID, '_rfpi_meta_key', true);
    ?>
    <label for="rfpi_field">Description for this field</label>
    <select name="rfpi_field" id="rfpi_field" class="postbox">
        <option value="">Select something...</option>
        <option value="something" <?php selected($value, 'something'); ?>>Something</option>
        <option value="else" <?php selected($value, 'else'); ?>>Else</option>
    </select>
    <?php
}



function rfpi_save_postdata($post_id) {
    if (array_key_exists('rfpi_field', $_POST)) {
        update_post_meta(
            $post_id,
            '_rfpi_meta_key',
            $_POST['rfpi_field']
        );
    }
}
add_action('save_post', 'rfpi_save_postdata');




load_plugin_textdomain( 'rss-podcast-import', false, basename( dirname( __FILE__ ) ) . '/languages' ); 











// plugin updates
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/webdevs-pro/rss-podcast-import',
	__FILE__,
	'rss-podcast-import'
);