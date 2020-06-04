jQuery(document).ready(function($) {

   $('#rfpi_fetch_now').click(function(e) {

      e.preventDefault();

      $('#rfpi_spinner').addClass('is-active');
      $('#rfpi_fetch_now').addClass('disabled');
      $('#rfpi_ajax_result').html('Fetching feed...<br>');

      var url = $('#rfpi_fetch_url').val();

      var cat = $('#rfpi_category').val();

      if ( $("#rfpi_update_existing").attr("checked") == 'checked' ) {
         var mode = 'update';
      } else {
         var mode = 'new';
      }

		var data = {
			action: 'fetch_episodes',
         url: url,
         mode: mode,
         cat: cat,
		};


		$.post( ajaxurl, data, function(response) {


         $('#rfpi_spinner').removeClass('is-active');
         $('#rfpi_fetch_now').removeClass('disabled');

         $('#rfpi_ajax_result').html($('#rfpi_ajax_result').html() + response);
         
      });
      
   });
   
});