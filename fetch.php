<?php    
add_action('wp_ajax_fetch_episodes', 'fetch_new_episodes');
function fetch_new_episodes($args) {


   $file_headers = @get_headers($url);

   if( !$file_headers || substr($file_headers[0], 9, 3) != "200") {
      error_log( 'not_exist' );
      return false; 
   } else {
      $content = file_get_contents($url); 
      try {
         $rss = new SimpleXmlElement($content); 
         if(!isset($rss->channel->item)){
            return false;
         }
      } 
      catch(Exception $e){ 
         error_log( 'not_valid_xml' );
         return false; 
      }

   error_log( 'valid' );
   }

   error_log( 'end' );

   if ( isset( $_GET['settings-updated'] ) ) {
      echo "<div class='updated'><p>Theme settings updated successfully.</p></div>";
  } 

}




