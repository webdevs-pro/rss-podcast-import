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
   register_setting( 'rfpi-settings-group', 'rfpi_feed_url' );
   register_setting( 'rfpi-settings-group', 'rfpi_fetch_period' );
   if ( get_option('rfpi_fetch_period') === false ) {
      update_option( 'rfpi_fetch_period', '24' ); // default checked
   } 
   
}

function rfpi_settings_page() {
?>
<div class="wrap">
   <h1><?php echo __('RSS Feed Podcast Importer Settings','rss-podcast-import') ?></h1>

   <div class="card">
      <form method="post" action="options.php">
         <?php settings_fields( 'rfpi-settings-group' ); ?>
         <?php do_settings_sections( 'rfpi-settings-group' ); ?>
         <table class="form-table">

            <tr valign="top">
               <th scope="row">RSS feed url</th>
               <td>
                  <input type="text" name="rfpi_feed_url" value="<?php echo esc_attr( get_option('rfpi_feed_url') ); ?>" style="width: 100%;" />
               </td>
            </tr>

            <tr valign="top">
               <th scope="row">Fetch new episodes every</th>
               <td>
                  <?php
                  $rfpi_fetch_period = get_option('rfpi_fetch_period')


                  ?>
                  <select name="rfpi_fetch_period" style="width: 100%;">
                     <option value="24" <?php selected($rfpi_fetch_period, '24'); ?>>24 hours</option>
                     <option value="12" <?php selected($rfpi_fetch_period, '12'); ?>>12 hours</option>
                     <option value="6" <?php selected($rfpi_fetch_period, '6'); ?>>6 hours</option>
                     <option value="3" <?php selected($rfpi_fetch_period, '3'); ?>>6 hours</option>
                     <option value="manual" <?php selected($rfpi_fetch_period, 'manual'); ?>>Manual</option>
                  </select>
               </td>
            </tr>

            <tr valign="top">
               <th scope="row"></th>
               <td>
                  <button id="rfpi_fetch_now" class="button button-primary">Check new episodes</button> 
               </td>
            </tr>

            <tr valign="top">
               <td>
                  <div class="">test</div>
               </td>
            </tr>

         </table>


         
         <?php submit_button(); ?>

      </form>
   </div>

</div>
<?php }