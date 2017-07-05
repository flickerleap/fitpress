jQuery(document).ready(function($) {

  $('.do-booking').live('click', function(e){

    e.preventDefault();

    if( $(this).hasClass('disabled') ){
		return;
    }

    var button = $(this);

    var old_text = $(this).text();

    $(this).html('<i class="fa fa-refresh fa-spin"></i>');
    $(this).addClass('disabled');

    jQuery.ajax({
         type : "post",
         dataType : "json",
         url : fp_booking.ajax_url,
         data : {action: $(this).data('action'), session_id: $(this).data('session-id')},
         success: function(response) {
            if(response.type == "success") {
              var cell = button.parent();
              var limit = cell.parent().find('.session-limit').text();
              if(limit != 'Unlimited'){
                  var availability = limit - response.bookings;
                  cell.parent().find('.session-bookings').text( availability );
              }
              $('.credits').text(response.credits);
              cell.html(response.action);
            }
            else {
              alert(response.message);
            }
         },
         error: function() {
           $(this).text(old_text);
           alert("There was an error. Please try again later.");
         }
      });

  });

});
