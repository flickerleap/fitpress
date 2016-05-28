jQuery( document ).ready( function( $ ) {

	var classTimeCount = 0;
	var template = $( '.class-time:last' ).clone( );

	$( 'body' ).on( 'click', '.add-class-time', function( e ) {

		e.preventDefault();
		classTimeCount--;

		var classTime = template.clone( ).find( '.class-start-time' ).each( function( ) {

			$(this).attr('name', 'class_times[' + classTimeCount + '][start_time]');

		} ).end();
      classTime = classTime.clone( ).find( '.class-end-time' ).each( function( ) {

         $(this).attr('name', 'class_times[' + classTimeCount + '][end_time]');

      } ).end();
      classTime = classTime.clone( ).find( '.class-day' ).each( function( ) {

         $(this).attr('name', 'class_time_days[' + classTimeCount + '][]');

      } ).end();
      $('.add-class-time').before( classTime );

	} );
} );