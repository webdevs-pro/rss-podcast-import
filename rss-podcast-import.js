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
            $('#episodes_progress').show();
            // print_to_log('Fetching feed...');
            $('.episodes_progress_text').html('Fetching feed...');
			},
			success: function( response ) {
            

            resp_obj = $.parseJSON(response);

            
            resp_obj_count = Object.keys(resp_obj).length;

            // $('#rfpi_spinner').removeClass('is-active');
            // $('#rfpi_fetch_now').removeClass('disabled');


            // print_to_log('Total episodes: ' + resp_obj_count);
            $('.episodes_progress_text').html('0 of ' + resp_obj_count);
            $('#episodes_progress').attr('max', resp_obj_count);

            // $('#rfpi_ajax_result').html($('#rfpi_ajax_result').html() + response);
			}
      });



      // FETCHING ITEM
      var item = 0;

      fetch_item();
      function fetch_item() {
   
         if(item >= resp_obj_count) {
            stop_fetching();
            return;
         }

         var value = resp_obj[item];
         
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
               // print_to_log('Fetching episode: ' + item);
            },
            success: function( response ) {


               console.log(response);

               // response = $.parseJSON(response);

               


               // print_to_log('Episode: ' + item + ':');
               // print_to_log(response);

               // $('#rfpi_ajax_result').html($('#rfpi_ajax_result').html() + response);
            }
         });



         item++;

         setTimeout( function() { 
            $('.episodes_progress_text').html(item + ' of ' + resp_obj_count);
            $('#episodes_progress').val(item);
            fetch_item(); 
         }, 100 );


      };



      function stop_fetching() {
         $('.episodes_progress_text').html($('.episodes_progress_text').html() + ', Done');
         // print_to_log('Done');
         $('#rfpi_spinner').removeClass('is-active');
         $('#rfpi_fetch_now').removeClass('disabled');
      }







      function print_to_log(text) {
         $('#rfpi_ajax_result').html(text + '<br>' + $('#rfpi_ajax_result').html());
      }




      
   });
   
});