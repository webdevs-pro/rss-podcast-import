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
   register_setting( 'rfpi-settings-group', 'rfpi_update_existing' );
   register_setting( 'rfpi-settings-group', 'rfpi_category' );

   if ( get_option('rfpi_fetch_period') === false ) {
      update_option( 'rfpi_fetch_period', '48' ); // default checked
   } 
   if ( get_option('rfpi_update_existing') === false ) {
      update_option( 'rfpi_update_existing', '1' ); // default checked
   }    
   if ( get_option('rfpi_category') === false ) {
      update_option( 'rfpi_category', '1' ); // default cat id 1
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


            <!-- RSS URL FIELD -->
            <tr valign="top">
               <th scope="row">RSS feed url</th>
               <td>
                  <input id="rfpi_fetch_url" type="text" name="rfpi_feed_url" value="<?php echo esc_attr( get_option('rfpi_feed_url') ); ?>" style="width: 100%;" autocomplete="off" />
               </td>
            </tr>

            <!-- CHECK PERIOD -->
            <tr valign="top">
               <th scope="row">Fetch new episodes every</th>
               <td>
                  <?php
                  $rfpi_fetch_period = get_option('rfpi_fetch_period');

                  ?>
                  <select id="rfpi_fetch_period" name="rfpi_fetch_period" style="width: 100%;" autocomplete="off">
                     <option value="48" <?php selected($rfpi_fetch_period, '48'); ?>>48 hours</option>
                     <option value="24" <?php selected($rfpi_fetch_period, '24'); ?>>24 hours</option>
                     <option value="12" <?php selected($rfpi_fetch_period, '12'); ?>>12 hours</option>
                     <option value="manual" <?php selected($rfpi_fetch_period, 'manual'); ?>>Manual</option>
                  </select>
               </td>
            </tr>




            <!-- CATEGORY -->
            <tr valign="top">
               <th scope="row">Category</th>
               <td>
                  <?php
                  $rfpi_category = get_option('rfpi_category');

                  ?>
                  <select id="rfpi_category" name="rfpi_category" style="width: 100%;" autocomplete="off">
                     <?php $args = array(
                        'orderby' => 'name',
                        'parent' => 0,
                        'hide_empty' => 0,
                     );
                     $categories = get_categories( $args );
                     foreach ($categories as $category) { ?>
                        <?php if ($category->cat_ID == $rfpi_category) $selected = ' selected="selected"'; else $selected = ''; ?>
                        <option value="<?php echo $category->term_id; ?>" <?php echo $selected; ?> >
                           <?php echo $category->cat_name . ' (' . $category->category_count .')'; ?>
                        </option>
                     <?php } ?>
                  </select>
               </td>
            </tr>





            <!-- UPDATE EXISTING -->
            <tr valign="top">
               <th scope="row">Update existing episodes</th>
               <td>
                  <input type="checkbox" id="rfpi_update_existing" name="rfpi_update_existing"  value="1" <?php checked(get_option('rfpi_update_existing')); ?> autocomplete="off">
               </td>
            </tr>

            <!-- FETCH NOW BUTTON -->
            <tr valign="top">
               <th scope="row"></th>
               <td>
                  <button id="rfpi_fetch_now" class="button button-primary">Check new episodes</button><span id="rfpi_spinner" style="float: none;" class="spinner"></span>
               </td>
            </tr>

         </table>

         <div class="episodes_progress_text"></div>         
         <progress id="episodes_progress" max="100" value="0" style="width: 100%; display: none;"></progress>

         <div id="rfpi_ajax_result"></div>

         
         <?php submit_button(); ?>

      </form>
   </div>

</div>
<?php }



function rfpi_change_cron_after_save( $old_value, $new_value ) {

	if ( $old_value != $new_value ) {
      // This value has been changed. Insert code here.
      error_log($old_value);
      error_log($new_value);
      if ($new_value == '48' || $new_value == '24' || $new_value == '12' ) {
         error_log('changing to ' . $new_value);
         if ( !wp_next_scheduled( 'rfpi_fetch_new_episodes' ) ) {
            error_log('not');
            error_log(HOUR_IN_SECONDS * intval($new_value));
            wp_schedule_event(time(), $new_value . '_hours', 'rfpi_fetch_new_episodes');
         } else {
            error_log('re');
            wp_clear_scheduled_hook( 'rfpi_fetch_new_episodes' );
            // wp_reschedule_event(time(), $new_value . '_hours', 'rfpi_fetch_new_episodes');
            wp_schedule_event(time(), $new_value . '_hours', 'rfpi_fetch_new_episodes');

         }
      }
      if ($new_value == 'manual') {
         wp_clear_scheduled_hook( 'rfpi_fetch_new_episodes' );
      }

      $timestamp = wp_next_scheduled( 'rfpi_fetch_new_episodes' );

      error_log($timestamp);

 

	}

}
add_action( 'update_option_rfpi_fetch_period', 'rfpi_change_cron_after_save', 10, 2 );