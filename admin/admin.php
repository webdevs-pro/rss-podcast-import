<?php 

// plugin settings link in plugins admin page
add_filter('plugin_action_links_' . RFPI_PLUGIN_BASENAME, function ( $links ) {
	$settings_link = '<a href="' . admin_url( 'options-general.php?page=rfpi_options' ) . '">' . __('Settings') . '</a>';

	array_unshift( $links, $settings_link );
	return $links;
});

/* ----------------------------------------------------------------------------- */
/* Add Menu Page */
/* ----------------------------------------------------------------------------- */ 


// create custom plugin settings menu
add_action('admin_menu', 'rfpi_create_menu');

function rfpi_create_menu() {

	//create new top-level menu
	//add_menu_page('My Cool Plugin Settings', 'Cool Settings', 'administrator', __FILE__, 'rfpi_settings_page' , plugins_url('/images/icon.png', __FILE__) );
   add_options_page(
      __('RSS Feed Podcast Importer Settings','rss-podcast-import'), 
      __('RFPI settings','rss-podcast-import'), 
      'manage_options', 
      'rfpi_options', 
      'rfpi_settings_page'
   );
	//call register settings function
	add_action( 'admin_init', 'register_rfpi_settings' );
}


function register_rfpi_settings() {
	//register our settings
   register_setting( 'rfpi-settings-group', 'rfpi_post_types' );
   register_setting( 'rfpi-settings-group', 'rfpi_wpmf_taxonomy' );
   if ( get_option('rfpi_post_types') === false ) {
      update_option( 'rfpi_post_types', array('post') ); // default checked
   } 
   
}

function rfpi_settings_page() {
?>
<div class="wrap">
<h1><?php echo __('YouTube Featured Image Settings','rss-podcast-import') ?></h1>

<form method="post" action="options.php">
   <?php settings_fields( 'rfpi-settings-group' ); ?>
   <?php do_settings_sections( 'rfpi-settings-group' ); ?>
   <table class="form-table">

      <tr valign="top">
      <th scope="row">Post types</th>
      <td>
         <?php
         $post_types = get_post_types(['public'=>true]);

         $value = get_option('rfpi_post_types');

         foreach ( $post_types as $post_type ) :

         $checked = '';

         $checked = ( @in_array( $post_type , $value ) ) ? 'checked="checked"': '';?>

         <label><input type="checkbox" name="rfpi_post_types[]" value="<?php echo $post_type; ?>" <?php echo $checked; ?> /> <?php echo $post_type; ?></label><br />

         <?php endforeach; ?>
      </td>
      </tr>

      
      <tr valign="top">
      <th scope="row">WP Media Folder plugin folder ID for uploaded images</th>
      <td><input type="text" name="rfpi_wpmf_taxonomy" value="<?php echo esc_attr( get_option('rfpi_wpmf_taxonomy') ); ?>" /></td>
      </tr>
    </table>


    
    <?php submit_button(); ?>

</form>
</div>
<?php }