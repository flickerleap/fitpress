jQuery( document ).ready( function( $ ) {

	var classTimeCount = 0;

	$( '.add-member' ).data( 'added-members', [] );

	$( 'body' ).on( 'click', '.add-class-time', function( e ) {

		var template = $( '.class-time:last' ).clone( );

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
		classTime = classTime.clone( ).find( '.class-block' ).each( function( ) {

			$(this).attr('name', 'class_time_days[' + classTimeCount + '][]');

		} ).end();

		$('.add-class-time').before( classTime );

	} );

	$( '.found-member-list li' ).live( 'click', function( e ) {

		if( $('.add-member').val() === '' ){

			$('.add-member').val( $( this ).data( 'member-id' ) );
			$('.find-member-search').val( '' );
			$('.find-member-search').val( escape( '<span>' + $( this ).text() + '</span>' ) );
			$( '.add-member' ).data( 'added-members' ).push( $( this ).data( 'member-id' ) );

		}else{

			$('.add-member').val( $('.add-member').val() + ',' + $(this).data( 'member-id' ) );

		}

	} );

	$(".find-member-search").select2({
		ajax: {
			url: ajaxurl,
			dataType: 'json',
			type: 'post',
			delay: 250,
			data: function (params) {
				return {
					action: 'fp_find_member',
					search: params.term,
				};
			},
			processResults: function (data, params) {

				if( data.type == 'found' ){

					members = data.members;

					var members = $.map( members , function (obj) {
						obj.id = obj.ID;
						obj.text = obj.display_name;

						return obj;
					});

					return {
						results: members
					};

				} else {

					return {
						results: []
					};

				}

			},
			cache: true
		},
		minimumInputLength: 3,
		placeholder: "Search for a member",
		allowClear: true,
		selectOnClose: true,
		delay: 500
	});


} );
