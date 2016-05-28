jQuery(document).ready(function($) {

  $('.do-booking').live('click', function(e){

    e.preventDefault();

    button = $(this);

    old_text = $(this).text();

    $(this).html('<i class="fa fa-refresh fa-spin"></i>');

    jQuery.ajax({
         type : "post",
         dataType : "json",
         url : fp_booking.ajax_url,
         data : {action: $(this).data('action'), session_id: $(this).data('session-id'), subscription_key: $(this).data('subscription-key')},
         success: function(response) {
            if(response.type == "success") {
              cell = button.parent();
              limit = cell.parent().find('.session-limit').text();
              if(limit != 'Unlimited'){
                  availability = limit - response.bookings;
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
         },
      });

  });

});
