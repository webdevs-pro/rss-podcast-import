<?php    
add_action('wp_ajax_fetch_episodes', 'fetch_new_episodes_ajax');
function fetch_new_episodes_ajax() {

   $return = fetch_new_episodes(array(
      'url' => $_POST['url'],
      'mode' => $_POST['mode'],
      'cat' => $_POST['cat'],
   ));

   echo $return;

   wp_die();

}




function fetch_new_episodes($args) {

   error_log('--- RSS Podcast Import ---');

   $file_headers = @get_headers($args['url']);

   if( !$file_headers || substr($file_headers[0], 9, 3) != "200") {
      return 'Status: ERROR (File not exist)';
   } else {

      $content = file_get_contents($args['url']); 

      try {
         $rss = new SimpleXmlElement($content); 
         if(!isset($rss->channel->item)) {
            return false;
         }
      } 
      catch(Exception $e){ 
         return 'Status: ERROR (Invalid RSS feed)';
         wp_die();
      }  

   }

   // create/update posts
   $ns = $rss->getNamespaces(true);

   $max_items = 500;

   $created = 0;
   $updated = 0;

   $episodes = array();

   $items = $rss->channel->item;
   $count = count($items);

   if($count >= 500 ) return 'Status: ERROR (Too much episodes)';

   for ($i = $count-1; $i >= 0; $i--) {

      $item = $items[$i];
      
      $itunes_data = $item->children($ns["itunes"]);

      // RSS item array
      $episodes[$i]['title'] = isset(($item->title)) ? ((string) $item->title) : '';
      $episodes[$i]['description'] = isset(($itunes_data->summary)) ? ((string) $itunes_data->summary) : '';
      $episodes[$i]['content'] = isset(($item->description)) ? ((string) $item->description) : '';
      $episodes[$i]['author'] = isset(($itunes_data->author)) ? ((string) $itunes_data->author) : '';
      $episodes[$i]['image'] =  isset(($itunes_data->image)) ? ((string) $itunes_data->image->attributes()->href) : '';
      $episodes[$i]['audio'] = isset(($item->enclosure)) ? ((string) $item->enclosure->attributes()->url) : '';
      $episodes[$i]['date'] = isset(($item->pubDate)) ? ((string) $item->pubDate) : '';
      $episodes[$i]['season'] = isset(($itunes_data->season)) ? ((string) $itunes_data->season) : '';
      $episodes[$i]['episode'] = isset(($itunes_data->episode)) ? ((string) $itunes_data->episode) : '';
      $episodes[$i]['buzzsprout_guid'] = isset(($item->guid)) ? ((string) $item->guid) : '';


      // post array
      $post_content = '
         <!-- wp:audio -->
         <figure class="wp-block-audio"><audio controls src="' . $episodes[$i]['audio'] . '"></audio></figure>
         <!-- /wp:audio -->      
      ';
      $post_content .= $episodes[$i]['content'];

      $date = DateTime::createFromFormat('D, d M Y H:i:s P', $episodes[$i]['date']);

      $post_data = array(
         'post_date'     => $date->format('Y-m-d H:i:s'),
         'post_title'    => wp_strip_all_tags( $episodes[$i]['title'] ),
         'post_content'  => $post_content,
         'post_excerpt'  => wp_strip_all_tags( $episodes[$i]['description'] ),
         'post_status'   => 'publish',
         'post_author'   => get_current_user_id(),
         'post_category' => array($_POST['cat'],),
         'meta_input'    => [
            'rfpi_author'     => $episodes[$i]['author'],
            'rfpi_image'           => $episodes[$i]['image'],
            'rfpi_audio'           => $episodes[$i]['audio'],
            'rfpi_date'            => $episodes[$i]['date'],
            'rfpi_season'          => $episodes[$i]['season'],
            'rfpi_episode'         => $episodes[$i]['episode'],
            'rfpi_guid' => $episodes[$i]['buzzsprout_guid'],
         ]
      );

      // error_log(print_r($post_data,true));
      
      // check is post with GUID exist
      $query_args = array(
         'meta_key' => 'rfpi_guid',
         'meta_value' => $episodes[$i]['buzzsprout_guid'],
         'post_type' => 'post',
      );
      $posts = get_posts($query_args);

      if(array_key_exists('0', $posts)) {

         // post with same GUID exist
         if ($args['mode'] == 'update') {

            // update existing post
            $post_data['ID'] = $posts[0]->ID;
            wp_update_post( wp_slash($post_data) );

            // update featured image
            $current_image = get_post_meta('rfpi_image', $post_data['ID'], true);
            if($current_image != $post_data['meta_input']['rfpi_image']) {
               // error_log('Generating new image (update)');
               $old_thumbnail_id = get_post_thumbnail_id( $post_data['ID'] );
               wp_delete_attachment( $old_thumbnail_id);
               rfpi_generate_featured_image($post_data['meta_input']['rfpi_image'], $post_data['ID']);
            }
            

            $updated++;
            // error_log('Podcast ' . $episodes[$i]['buzzsprout_guid'] . ' exist, updating post.');



         } else {

            // skip post
            // error_log('Podcast ' . $episodes[$i]['buzzsprout_guid'] . ' exist, skip updating.');
            continue;

         }

      } else {

         // create new post
         $post_id = wp_insert_post( $post_data );
         if($post_data['meta_input']['rfpi_image'] != "") {
            // error_log('Generating new image (new)' . $i . '-' . $post_data['meta_input']['rfpi_image']);
            rfpi_generate_featured_image($post_data['meta_input']['rfpi_image'], $post_id);
         }
         $created++;
         // error_log('Podcast ' . $episodes[$i]['buzzsprout_guid'] . ' not exist, creating new post.');

      }



      wp_reset_postdata();

      	
      
   }

   $response = 'Status: OK<br>';
   $response .= 'Total: ' . $count . '<br>';
   $response .= 'New: ' . $created . '<br>';
   $response .= 'Updated: ' . $updated . '<br>';

   
   error_log($response);
   error_log('--------------------------');

   return $response;

}




function rfpi_generate_featured_image( $image_url, $post_id  ) {


   $file_headers = @get_headers($image_url);

   if ($file_headers && substr($file_headers[0], 9, 3) == "200") {


      require_once ABSPATH . 'wp-admin/includes/file.php';

      // upload image to wordpress
      $image_contents = file_get_contents($image_url);

      $upload = wp_upload_bits( basename($image_url), null, $image_contents );

      $wp_filetype = wp_check_filetype( basename( $upload['file'] ), null );

      $upload = apply_filters( 'wp_handle_upload', array(
         'file' => $upload['file'],
         'url'  => $upload['url'],
         'type' => $wp_filetype['type']
      ), 'sideload' );

      $attachment = array(
         'post_mime_type'	=> $upload['type'],
         'post_title'		=> get_the_title( $post_id ),
         'post_content'		=> '',
         'post_status'		=> 'inherit'
      );

      $attach_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );
      $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
      wp_update_attachment_metadata( $attach_id, $attach_data );
      set_post_thumbnail( $post_id, $attach_id );
      
      
   }

}