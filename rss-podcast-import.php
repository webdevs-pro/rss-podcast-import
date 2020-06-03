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

load_plugin_textdomain( 'rss-podcast-import', false, basename( dirname( __FILE__ ) ) . '/languages' ); 

define('RFPI_PLUGIN_BASENAME', plugin_basename(__FILE__));

include( plugin_dir_path( __FILE__ ) . 'admin/admin.php');
include( plugin_dir_path( __FILE__ ) . '/fetch.php');

add_action('admin_enqueue_scripts', 'rfpi_admin_scripts');
function rfpi_admin_scripts( $hook ) {

    if( $hook == 'settings_page_rfpi_options' ) {

        wp_register_script('rss-podcast-import', plugin_dir_url( __FILE__ ) . '/rss-podcast-import.js', array('jquery'));
        wp_enqueue_script( 'rss-podcast-import');

    }

}


// ADD POST METABOX
function rfpi_add_metabox() {
    $screens = ['post'];
    foreach ($screens as $screen) {
        add_meta_box(
            'rfpi_metabox',           // Unique ID
            'Podcast',  // Box title
            'rfpi_metabox_html',  // Content callback, must be of type callable
            $screen,                  // Post type
            'side'                    // side
        );
    }
}
add_action('add_meta_boxes', 'rfpi_add_metabox');

function rfpi_metabox_html($post) {
    $select_value = get_post_meta($post->ID, 'rfpi_select_meta_key', true);
    $season = get_post_meta($post->ID, 'rfpi_season', true);
    $episode = get_post_meta($post->ID, 'rfpi_episode', true);
    $author = get_post_meta($post->ID, 'rfpi_author', true);
    $guid = get_post_meta($post->ID, 'rfpi_guid', true);
    $audio = get_post_meta($post->ID, 'rfpi_audio', true);
    $image = get_post_meta($post->ID, 'rfpi_image', true);

    ?>
    <!-- <label for="rfpi_select_field">Description for this field</label>
    <select name="rfpi_select_field" class="postbox">
        <option value="">Select something...</option>
        <option value="something" <?php selected($select_value, 'something'); ?>>Something</option>
        <option value="else" <?php selected($select_value, 'else'); ?>>Else</option>
    </select><br><br> -->

    <label for="rfpi_season"><div>Season</div></label>
    <input type="text" name="rfpi_season" value="<?php echo $season; ?>" /><br><br>

    <label for="rfpi_episode"><div>Episode</div></label>
    <input type="text" name="rfpi_episode" value="<?php echo $episode; ?>" /><br><br>

    <label for="rfpi_author"><div>Author</div></label>
    <input type="text" name="rfpi_author" value="<?php echo $author; ?>" /><br><br>
 
    <label for="rfpi_guid"><div>Guid</div></label>
    <input type="text" name="rfpi_guid" value="<?php echo $guid; ?>" /><br><br>
 
    <label for="rfpi_audio"><div>Audio</div></label>
    <input type="text" name="rfpi_audio" value="<?php echo $audio; ?>" /><br><br>

    <label for="rfpi_image"><div>Image</div></label>
    <input type="text" name="rfpi_image" value="<?php echo $image; ?>" /><br><br>

    <?php
}

function rfpi_save_postdata($post_id) {
    if (array_key_exists('rfpi_select_field', $_POST)) {
        update_post_meta(
            $post_id,
            'rfpi_select_meta_key',
            $_POST['rfpi_select_field']
        );
    }
    if (array_key_exists('rfpi_season', $_POST)) {
        update_post_meta(
            $post_id,
            'rfpi_season',
            $_POST['rfpi_season']
        );
    }
    if (array_key_exists('rfpi_episode', $_POST)) {
        update_post_meta(
            $post_id,
            'rfpi_episode',
            $_POST['rfpi_episode']
        );
    }
    if (array_key_exists('rfpi_author', $_POST)) {
        update_post_meta(
            $post_id,
            'rfpi_author',
            $_POST['rfpi_author']
        );
    }
    if (array_key_exists('rfpi_guid', $_POST)) {
        update_post_meta(
            $post_id,
            'rfpi_guid',
            $_POST['rfpi_guid']
        );
    }
    if (array_key_exists('rfpi_audio', $_POST)) {
        update_post_meta(
            $post_id,
            'rfpi_audio',
            $_POST['rfpi_audio']
        );
    }
    if (array_key_exists('rfpi_image', $_POST)) {
        update_post_meta(
            $post_id,
            'rfpi_image',
            $_POST['rfpi_image']
        );
    }
}
add_action('save_post', 'rfpi_save_postdata');





// plugin updates
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/webdevs-pro/rss-podcast-import',
	__FILE__,
	'rss-podcast-import'
);








