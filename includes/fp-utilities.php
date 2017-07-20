<?php
/**
 * Get other templates passing attributes and including the file.
 *
 * @access public
 *
 * @param string $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 *
 * @return void
 */
function fp_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}

	$located = fp_locate_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );

		return;
	}

	// Allow 3rd party plugin filter template file from their plugin
	//$located = apply_filters( 'fp_get_template', $located, $template_name, $args, $template_path, $default_path );

	include( $located );

}

/**
 * Like wc_get_template, but returns the HTML instead of outputting.
 * @see wc_get_template
 * @since 2.5.0
 *
 * @param string $template_name
 */
function fp_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	ob_start();
	fp_get_template( $template_name, $args, $template_path, $default_path );

	return ob_get_clean();
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *        yourtheme        /    $template_path    /    $template_name
 *        yourtheme        /    $template_name
 *        $default_path    /    $template_name
 *
 * @access public
 *
 * @param string $template_name
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 *
 * @return string
 */
function fp_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = FP()->template_path();
	}

	if ( ! $default_path ) {
		$default_path = FP()->plugin_path() . '/templates/';
	}

	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);

	// Get default template
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	// Return what we found
	return apply_filters( 'fitpress_locate_template', $template, $template_name, $template_path );
}

/**
 * Retrieve page ids - used for myaccount, edit_address, shop, cart, checkout, pay, view_order, terms. returns -1 if no page is found
 *
 * @param string $page
 *
 * @return int
 */
function fp_get_page_id( $page ) {

	if ( $page == 'pay' || $page == 'thanks' ) {
		_deprecated_argument( __FUNCTION__, '2.1', 'The "pay" and "thanks" pages are no-longer used - an endpoint is added to the checkout instead. To get a valid link use the WC_Order::get_checkout_payment_url() or WC_Order::get_checkout_order_received_url() methods instead.' );

		$page = 'checkout';
	}
	if ( $page == 'change_password' || $page == 'edit_address' || $page == 'lost_password' ) {
		_deprecated_argument( __FUNCTION__, '2.1', 'The "change_password", "edit_address" and "lost_password" pages are no-longer used - an endpoint is added to the my-account instead. To get a valid link use the fp_customer_edit_account_url() function instead.' );

		$page = 'account';
	}

	$page = apply_filters( 'woocommerce_get_' . $page . '_page_id', get_option( 'woocommerce_' . $page . '_page_id' ) );

	return $page ? absint( $page ) : - 1;
}

/**
 * Retrieve page permalink
 *
 * @param string $page
 *
 * @return string
 */
function fp_get_page_permalink( $page ) {
	$permalink = get_permalink( get_page_by_path( $page ) );

	return apply_filters( 'fitpress_get_' . $page . '_page_permalink', $permalink );
}

/**
 * Get endpoint URL
 *
 * Gets the URL for an endpoint, which varies depending on permalink settings.
 *
 * @return string
 */
function fp_get_endpoint_url( $endpoint, $value = '', $permalink = '' ) {
	if ( ! $permalink ) {
		$permalink = get_permalink();
	}

	// Map endpoint to options
	$endpoint = isset( FP()->query->query_vars[ $endpoint ] ) ? FP()->query->query_vars[ $endpoint ] : $endpoint;

	if ( get_option( 'permalink_structure' ) ) {
		if ( strstr( $permalink, '?' ) ) {
			$query_string = '?' . parse_url( $permalink, PHP_URL_QUERY );
			$permalink    = current( explode( '?', $permalink ) );
		} else {
			$query_string = '';
		}
		$url = trailingslashit( $permalink ) . $endpoint . '/' . $value . $query_string;
	} else {
		$url = add_query_arg( $endpoint, $value, $permalink );
	}

	return apply_filters( 'fitpress_get_endpoint_url', $url, $endpoint, $value, $permalink );
}

/**
 * Get the edit address slug translation.
 *
 * @param  string $id Address ID.
 * @param  bool $flip Flip the array to make it possible to retrieve the values ​​from both sides.
 *
 * @return string        Address slug i18n.
 */
function fp_edit_address_i18n( $id, $flip = false ) {
	$slugs = apply_filters( 'fitpress_edit_address_slugs', array(
		'billing'  => sanitize_title( _x( 'billing', 'edit-address-slug', 'woocommerce' ) ),
		'shipping' => sanitize_title( _x( 'shipping', 'edit-address-slug', 'woocommerce' ) )
	) );

	if ( $flip ) {
		$slugs = array_flip( $slugs );
	}

	if ( ! isset( $slugs[ $id ] ) ) {
		return $id;
	}

	return $slugs[ $id ];
}

/**
 * Returns the url to the lost password endpoint url
 *
 * @access public
 * @return string
 */
function fp_lostpassword_url() {
	return fp_get_endpoint_url( 'lost-password', '', fp_get_page_permalink( 'account' ) );
}

add_filter( 'lostpassword_url', 'fp_lostpassword_url', 10, 0 );

/**
 * Returns the url to the book endpoint url
 *
 * @access public
 * @return string
 */
function fp_book_url() {
	return fp_get_endpoint_url( 'book', '', fp_get_page_permalink( 'account' ) );
}

add_filter( 'book_url', 'fp_book_url', 10, 0 );

/**
 * Returns the url to the membership endpoint url
 *
 * @access public
 * @return string
 */
function fp_membership_url() {
	return fp_get_endpoint_url( 'membership', '', fp_get_page_permalink( 'account' ) );
}

add_filter( 'book_url', 'fp_membership_url', 10, 0 );

/**
 * Returns the url to the checkout endpoint url
 *
 * @access public
 * @return string
 */
function fp_checkout_url() {
	return fp_get_endpoint_url( 'checkout', '', fp_get_page_permalink( 'sign-up' ) );
}

add_filter( 'checkout_url', 'fp_checkout_url', 10, 0 );

/**
 * Returns the url to the cancel endpoint url
 *
 * @access public
 * @return string
 */
function fp_cancel_url() {
	return fp_get_endpoint_url( 'cancel', '', fp_get_page_permalink( 'sign-up' ) );
}

add_filter( 'cancel_url', 'fp_cancel_url', 10, 0 );

/**
 * Returns the url to the confirm endpoint url
 *
 * @access public
 * @return string
 */
function fp_confirm_url() {
	return fp_get_endpoint_url( 'confirm', '', fp_get_page_permalink( 'sign-up' ) );
}

add_filter( 'confirm_url', 'fp_confirm_url', 10, 0 );

/**
 * Returns the url to the notify endpoint url
 *
 * @access public
 * @return string
 */
function fp_notify_url() {
	return fp_get_endpoint_url( 'notify', '', fp_get_page_permalink( 'sign-up' ) );
}

add_filter( 'notify_url', 'fp_notify_url', 10, 0 );

/**
 * Returns the url to the make-booking endpoint url
 *
 * @access public
 * @return string
 */
function fp_make_booking_url() {
	return fp_get_endpoint_url( 'make-booking', '', fp_get_page_permalink( 'account' ) );
}

add_filter( 'make_booking_url', 'fp_make_booking_url', 10, 0 );

/**
 * Returns the url to the cancel-booking endpoint url
 *
 * @access public
 * @return string
 */
function fp_cancel_booking_url() {
	return fp_get_endpoint_url( 'cancel-booking', '', fp_get_page_permalink( 'account' ) );
}

add_filter( 'cancel_booking_url', 'fp_cancel_booking_url', 10, 0 );


/**
 * Get the link to the edit account details page
 *
 * @return string
 */
function fp_customer_edit_account_url() {
	$edit_account_url = fp_get_endpoint_url( 'update-account', '', fp_get_page_permalink( 'account' ) );

	return apply_filters( 'woocommerce_customer_edit_account_url', $edit_account_url );
}

/**
 * Handle redirects before content is output - hooked into template_redirect so is_page works.
 *
 * @return void
 */
function fp_template_redirect() {
	global $wp_query, $wp;

	if ( isset( $wp->query_vars['member-logout'] ) ) {
		wp_redirect( str_replace( '&amp;', '&', wp_logout_url( fp_get_page_permalink( 'account' ) ) ) );
		exit;
	}

}

add_action( 'template_redirect', 'fp_template_redirect' );

function fp_maybe_manual_run() {
	if ( isset( $_GET['force_reset_credits'] ) ):
		FP_Credit::maybe_reset_credits( true );
		$url = remove_query_arg( array( 'force_reset_credits' ) );
		wp_redirect( $url );
	elseif ( isset( $_GET['force_create_sessions'] ) ):
		$FP_Session = new FP_Session();
		if ( isset( $_GET['start_day'] ) && ! empty( $_GET['start_day'] ) ) :
			$FP_Session->add_sessions( $_GET['start_day'], $_GET['force_create_sessions'] );
		else :
			$FP_Session->add_sessions( strtotime( 'tomorrow midnight' ), $_GET['force_create_sessions'] );
		endif;
		$url = remove_query_arg( array( 'force_create_sessions' ) );
		wp_redirect( $url );
	elseif ( isset( $_GET['member_id'] ) && isset( $_GET['membership_id'] ) ) :
		FP_Membership::quick_member_add( $_GET['member_id'], $_GET['membership_id'] );
		$url = remove_query_arg( array( 'member_id', 'membership_id' ) );
		wp_redirect( $url );
	elseif ( isset( $_GET['force_send_expire_reminder'] ) ) :
		include_once( FP_PLUGIN_DIR . 'includes/notifications/class-fp-notifications-membership-expire.php' );
		$notification = new FP_Notification();
		$notification->send_daily_notifications();
		$url = remove_query_arg( array( 'force_send_renewal_reminder' ) );
		wp_redirect( $url );
	elseif ( isset( $_GET['force_send_bookings'] ) ) :
		include_once( FP_PLUGIN_DIR . 'includes/notifications/class-fp-notifications-bookings.php' );
		$notification = new FP_Notification();
		$notification->send_hourly_notifications();
    $url = remove_query_arg( array( 'force_send_bookings' ) );
    wp_redirect( $url );
	elseif ( isset( $_GET['force_send_member_list'] ) ) :
		if ( $_GET['force_send_member_list'] == 'inactive' ) :
			FP_Membership::maybe_send_member_list( true, true );
		else :
			FP_Membership::maybe_send_member_list( true );
		endif;
		$url = remove_query_arg( array( 'force_send_member_list' ) );
		wp_redirect( $url );
	endif;
}

add_action( 'template_redirect', 'fp_maybe_manual_run' );

function fp_add_flash_message( $message, $type = 'success' ) {

	FP_Flash_Message::set( $message, $type );

}

function fp_display_flash_message() {

	FP_Flash_Message::display();

}

function fp_flash_message_count( $type ) {

	return FP_Flash_Message::count( $type );

}

if ( ! function_exists( 'write_log' ) ) {
	function write_log( $log ) {
		if ( WP_DEBUG && WP_DEBUG_LOG ):
			if ( is_array( $log ) || is_object( $log ) ) :
				error_log( print_r( $log, true ) );
			else :
				error_log( $log );
			endif;
		endif;
	}
}

/**
 * Returns the timezone string for a site, even if it's set to a UTC offset
 *
 * Adapted from http://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
 *
 * @return string valid PHP timezone string
 */
function wp_get_timezone_string() {

	// if site timezone string exists, return it.
	if ( $timezone = get_option( 'timezone_string' ) ) :
		return $timezone;
	endif;

	// get UTC offset, if it isn't set then return UTC.
	if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) ) :
		return 'UTC';
	endif;

	// adjust UTC offset from hours to seconds.
	$utc_offset *= 3600;

	// attempt to guess the timezone string from the UTC offset.
	if ( $timezone = timezone_name_from_abbr( '', $utc_offset, 0 ) ) {
		return $timezone;
	}

	// last try, guess timezone string manually.
	$is_dst = date( 'I' );

	foreach ( timezone_abbreviations_list() as $abbr ) {
		foreach ( $abbr as $city ) {
			if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset ) :
				return $city['timezone_id'];
			endif;
		}
	}

	// fallback to UTC.
	return 'UTC';
}