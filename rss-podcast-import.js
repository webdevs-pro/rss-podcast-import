jQuery(document).ready(function($) {

   $('#rfpi_fetch_now').click(function(e) {

      e.preventDefault();



      var url = $('#rfpi_fetch_url').val();

      var cat = $('#rfpi_category').val();

      if ( $("#rfpi_update_existing").attr("checked") == 'checked' ) {
         var mode = 'update';
      } else {
         var mode = 'new';
      }





		var fetch_rss_data = {
			action: 'fetch_rss',
         url: url,
         cat: cat,
		};

      var resp_obj;


      // FETCHING XML
		$.ajax({
			url: ajaxurl,
			type: 'POST',
         data: fetch_rss_data,
         async: false, 
			beforeSend: function( xhr ) {
            $('#rfpi_spinner').addClass('is-active');
            $('#rfpi_fetch_now').addClass('disabled');
            print_to_log('Fetching feed...');
			},
			success: function( response ) {
            

            resp_obj = $.parseJSON(response);

            
            resp_obj_count = Object.keys(resp_obj).length;

            // $('#rfpi_spinner').removeClass('is-active');
            // $('#rfpi_fetch_now').removeClass('disabled');


            print_to_log('Total episodes: ' + resp_obj_count);

            // $('#rfpi_ajax_result').html($('#rfpi_ajax_result').html() + response);
			}
      });



      // FETCHING ITEM
      var item = 1;
      // reverse foreach
      $.each(Object.keys(resp_obj).reverse(),function(i,key){

         var value = resp_obj[key];
         var json_data = JSON.stringify(value);

         var episode_data = {
            action: 'fetch_episode',
            data: json_data,
            mode: mode,
         };

         // fetching xml
         $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: episode_data,
            async: false, 
            beforeSend: function( xhr ) {
               print_to_log('Fetching episode: ' + item);
            },
            success: function( response ) {


               console.log(response);

               // response = $.parseJSON(response);

               


               print_to_log('Episode: ' + item + ':');
               print_to_log(response);

               // $('#rfpi_ajax_result').html($('#rfpi_ajax_result').html() + response);
            }
         });

         item++;

      });





      print_to_log('Done');
      $('#rfpi_spinner').removeClass('is-active');
      $('#rfpi_fetch_now').removeClass('disabled');












      function print_to_log(text) {
         $('#rfpi_ajax_result').html(text + '<br>' + $('#rfpi_ajax_result').html());
      }




      
   });
   
});