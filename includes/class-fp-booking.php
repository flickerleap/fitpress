<?php
/**
 * Post Types
 *
 * Registers post types and taxonomies.
 *
 * @class     FP_Post_Types
 * @version   2.5.0
 * @package   FitPress/Classes/Products
 * @category  Class
 * @author    Digital Leap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FP_Post_Types Class.
 */
class FP_Booking {

	public $query_vars = array();

	/**
	 * Hook in methods.
	 */
    public function __construct(){

		add_action( 'init', array( $this, 'add_endpoints' ) );

		add_action( 'template_redirect', array( $this, 'booking_hook' ) );

		add_action( 'wp_ajax_make_booking', array( $this, 'make_booking_callback' ) );
		add_action( 'wp_ajax_cancel_booking', array( $this, 'cancel_booking_callback' ) );

		add_action( 'init', array( $this, 'register_post_types' ), 5 );

	}

	public function add_endpoints(){

		foreach ( $this->query_vars as $var ) {
			add_rewrite_endpoint( $var, EP_ROOT | EP_PAGES );
		}

	}

	public static function make_booking_callback( ){

		$result = self::make_booking( true );

		$user_id = get_current_user_id();

		$result = array_merge($result, self::booking_response_data( $_REQUEST['session_id'] ));

		$result = json_encode($result);
		echo $result;

		die();

	}

	public function cancel_booking_callback( ){

		$result = self::cancel_booking( true );

		$user_id = get_current_user_id();

		$result = array_merge($result, self::booking_response_data( $_REQUEST['session_id'] ));

		$result = json_encode($result);
		echo $result;

		die();

	}

	public function booking_hook(){

		global $wp;

		if( isset( $wp->query_vars['make-booking'] ) )
			self::make_booking();

		if( isset( $wp->query_vars['cancel-booking']) )
			self::cancel_booking();

	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {
		if ( post_type_exists('fp_class') ) {
			return;
		}

		do_action( 'action_register_post_type' );

		register_post_type( 'fp_booking',
			array(
				'description'         => __( 'This is where you can add new products to your store.', 'fitpress' ),
				'public'              => false,
				'show_ui'             => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => false,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'has_archive'         => false,
				'show_in_nav_menus'   => false,
			)
		);

	}

	public function booking_response_data( $session_id ){

		$url = remove_query_arg( array( 'message' ) );

		$user_id = get_current_user_id( );

		$credits = get_user_meta( $user_id, 'fitpress_credits', true );

		$session = get_post( $session_id );
		$session->start_time = get_post_meta( $session->ID, '_fp_start_time', true);
		$session->end_time = get_post_meta( $session->ID, '_fp_end_time', true);
		$session_class_id = get_post_meta( $session->ID, '_fp_class_id', true);
		$class_info = get_post_meta( $session_class_id, 'fp_class_info', true);
		$session->class_limit = $class_info['limit'];

		$args = array(
			'post_type'  => 'fp_booking',
			'meta_query' => array(
				array(
					'key'    => '_fp_session_id',
					'value'  => $session->ID
				)
			),
			'posts_per_page' => -1
		);

		$bookings = new WP_Query( $args );

		$session->current_bookings = $bookings->found_posts;

		if( $credits <= 0 ):

			$action = 'Insuffient Credits';

		elseif( $session->current_bookings >= $session->class_limit && $session->class_limit != 0 ):

			$action = 'Session Full';

		elseif( self::is_booked( $session->ID, $user_id ) && strtotime( "+30 minutes", current_time( 'timestamp' ) ) <= $session->start_time ):

			$url = add_query_arg(
				array(
					'session_id'     => $session->ID
				),
				fp_cancel_booking_url()
			);

			$action = '<a href="' . $url . '" class="btn btn-flat btn-small btn-cancel do-booking" data-session-id="' . $session->ID . '" data-action="cancel_booking">Cancel</a>';

		elseif( self::is_booked( $session->ID, $user_id ) && date( 'G', current_time( 'timestamp' ) ) >= 0 && date( 'G', current_time( 'timestamp' ) ) < 12 && strtotime( "+30 minutes", current_time( 'timestamp' ) ) > $session->start_time ):

			// TODO Centralise time limit

			$action = 'Cannot Cancel';

		elseif( self::is_booked( $session->ID, $user_id ) && date( 'G', current_time( 'timestamp' ) ) >= 12 && date( 'G', current_time( 'timestamp' ) ) < 24 && strtotime( "+4 hours", current_time( 'timestamp' ) ) > $session->start_time ):

			// TODO Centralise time limit

			$action = 'Cannot Cancel';


		elseif( !self::is_booked( $session->ID, $user_id ) && current_time( 'timestamp' ) > $session->start_time ):

			// TODO Centralise time limit

			$action = 'Cannot Book';

		else:

			$url = add_query_arg(
				array(
					'session_id' => $session->ID,
				),
				fp_make_booking_url()
			);

			$action = '<a href="' . $url . '" class="btn btn-flat btn-small do-booking" data-subscription-key="' . $subscription_key . '" data-session-id="' . $session->ID . '" data-action="make_booking">Book</a>';

		endif;

		return array('action' => $action, 'credits' => $credits, 'bookings' => $session->current_bookings);

	}

	public static function bookable_sesssion( ){

		$user_id = get_current_user_id();
		$credits = 0;

		$credits = get_user_meta( $user_id, 'fitpress_credits', true );

		$raw_sessions = FP_Session::get_session();

		$sessions = array();

		foreach( $raw_sessions->posts as $session ):

			$url = remove_query_arg( array( 'message' ) );

			$session->start_time = get_post_meta( $session->ID, '_fp_start_time', true);
			$session->end_time = get_post_meta( $session->ID, '_fp_end_time', true);
			$session_class_id = get_post_meta( $session->ID, '_fp_class_id', true);
			$session->class_name = get_the_title( $session_class_id );
			$class_info = get_post_meta( $session_class_id, 'fp_class_info', true);
			$session->class_limit = $class_info['limit'];

			$session->action = '';
			$session->current_bookings = '';

			$args = array(
				'post_type'  => 'fp_booking',
				'meta_query' => array(
					array(
						'key'    => '_fp_session_id',
						'value'  => $session->ID
					)
				),
				'posts_per_page' => -1
			);

			$bookings = new WP_Query( $args );

			$session->current_bookings = $bookings->found_posts;

			if( $credits <= 0 ):

				$action = 'Insuffient Credits';

			elseif( $session->current_bookings >= $session->class_limit && $session->class_limit != 0 && !self::is_booked( $session->ID, $user_id ) ):

				$action = 'Session Full';

			elseif( self::is_booked( $session->ID, $user_id ) && strtotime( "+30 minutes", current_time( 'timestamp' ) ) <= $session->start_time ):

				$url = add_query_arg(
					array(
						'session_id'     => $session->ID,
					),
					fp_cancel_booking_url()
				);

				$action = '<a href="' . $url . '" class="btn btn-flat btn-small btn-cancel do-booking" data-session-id="' . $session->ID . '" data-action="cancel_booking">Cancel</a>';

			elseif( self::is_booked( $session->ID, $user_id ) && date( 'G', current_time( 'timestamp' ) ) >= 0 && date( 'G', current_time( 'timestamp' ) ) < 12 && strtotime( "+30 minutes", current_time( 'timestamp' ) ) > $session->start_time ):

				// TODO Centralise time limit

				$action = 'Cannot Cancel';

			elseif( self::is_booked( $session->ID, $user_id ) && date( 'G', current_time( 'timestamp' ) ) >= 12 && date( 'G', current_time( 'timestamp' ) ) < 24 && strtotime( "+4 hours", current_time( 'timestamp' ) ) > $session->start_time ):

				// TODO Centralise time limit

				$action = 'Cannot Cancel';

			elseif( !self::is_booked( $session->ID, $user_id ) && current_time( 'timestamp' ) > $session->start_time ):

				// TODO Centralise time limit

				$action = 'Cannot Book';

			else:

				$url = add_query_arg(
					array(
						'session_id' => $session->ID,
					),
					fp_make_booking_url()
				);

				$action = '<a href="' . $url . '" class="btn btn-flat btn-small do-booking" data-session-id="' . $session->ID . '" data-action="make_booking">Book</a>';

			endif;

			$session->action = $action;

			$sessions[ date( "l - j F Y", $session->start_time ) ][] = $session;

		endforeach;

		$session_data = array(
			'sessions'         => $sessions,
			'user_id'          => $user_id,
			'credits'          => $credits,
		);

		return $session_data;

	}

   public static function is_booked( $session_id, $user_id ){

		$args = array(
			'post_type'  => 'fp_booking',
			'meta_query' => array(
				array(
					'key'   => '_fp_session_id',
					'value' => $session_id,
				),
				array(
					'key'   => '_fp_user_id',
					'value' => $user_id,
				),
			),
		);

		$booking = new WP_Query( $args );

		if( $booking->have_posts() )
			return true;

		return false;

	}

	public static function make_booking( $ajax = false ){

		$user_id = get_current_user_id();

		$redirect_url = remove_query_arg(
			array( 'session_id' )
		);

		if( !isset( $_REQUEST['session_id'] ) || empty( $user_id ) ):

			$redirect_url = add_query_arg(
				array(
					'message' => 'Something went wrong. Awkward.'
				),
				fp_book_url()
			);

			if($ajax):

				$return = array(

				'type' => 'error',
				'message' => 'Missing data.'

				);

			return $return;

			else:

				header("Location: " . fp_book_url() );
				exit;

			endif;

		endif;

		$session_id = $_REQUEST['session_id'];

		$redirect_url = add_query_arg(
			array(
				'message' => 'You\'re already booked for that session.'
			),
			fp_book_url()
		);

		if( !self::is_booked( $session_id, $user_id ) ):

			$booking_date = get_post_meta( $session_id, '_fp_start_time', true );

			$fitness_booking_post = array(
				'post_title'     => 'Session Booking',
				'post_status'    => 'publish',
				'ping_status'    => 'closed',
				'comment_status' => 'closed',
				'post_type'      => 'fp_booking',
			);

			$fitness_booking_post_id = wp_insert_post( $fitness_booking_post );

			if( $fitness_booking_post_id ):
				update_post_meta( $fitness_booking_post_id, '_fp_session_id', $session_id );
				update_post_meta( $fitness_booking_post_id, '_fp_user_id', $user_id );
			endif;

			FP_Credit::modify_credits( -1, $user_id );

			do_action( 'make_booking',  $session_id, $user_id );

			$redirect_url = remove_query_arg(
				array( 'message' ),
				fp_book_url()
			);

		endif;

		if($ajax):

			$return = array(

				'type' => 'success',
				'message' => 'Booking made.'

			);

			return $return;

		else:

			header("Location: " . fp_book_url() );
			exit;

		endif;

	}

	public static function cancel_booking( $ajax = false ){

		$user_id = get_current_user_id();

		$redirect_url = remove_query_arg(
			array( 'session_id' )
		);

		if( !isset( $_REQUEST['session_id'] ) || empty( $user_id ) ):

			$redirect_url = add_query_arg(
				array(
					'message' => 'Something went wrong. Awkward.'
				),
				fp_book_url()
			);

			if($ajax):

				$return = array(
					'type' => 'error',
					'message' => 'Missing data.'
				);

				return $return;

			else:

				header("Location: " . fp_book_url() );
				exit;

			endif;

		endif;

		$session_id = $_REQUEST['session_id'];

		if( self::is_booked( $session_id, $user_id ) ):

			$args = array(
				'post_type'      => 'fp_booking',
				'meta_query' => array(
					array(
						'key'    => '_fp_session_id',
						'value'  => $session_id,
					),
					array(
						'key'    => '_fp_user_id',
						'value'  => $user_id,
					)
				),
			);

			$booking = new WP_Query( $args );

			if( $booking->post_count > 0 ):

				wp_delete_post( $booking->posts[0]->ID, true );

				FP_Credit::modify_credits( 1, $user_id );

				do_action( 'cancel_booking',  $session_id, $user_id );

			endif;

			$redirect_url = remove_query_arg(
				array( 'message' ),
				fp_book_url()
			);

		endif;

		if($ajax):

			$return = array(
				'type' => 'success',
				'message' => 'Booking cancelled.'
			);

			return $return;

		else:

			header("Location: " . fp_book_url() );
			exit;

		endif;

	}

	public static function get_booked_sessions( $params = array('user_id' => null, 'session_id' => null) ){

		$booking_data = array();

		if( !isset( $params['user_id'] ) && !isset( $params['session_id'] ) )
			return $booking_data;

		if( isset( $params['user_id'] ) ):

			$args = array(
				'post_type' => 'fp_session',
				'fields' => 'ids',
				array(
					'key'     => '_fp_start_time',
					'value'   => current_time( 'timestamp' ),
					'type'    => 'numeric',
					'compare' => '>'
				),
			);

			$session_ids = new WP_Query( $args );

			if( empty( $session_ids ) )
				return $booking_data;

			$args = array(
				'post_type'  => 'fp_booking',
				'meta_query' => array(
					array(
						'key'   => '_fp_user_id',
						'value' => $params['user_id'],
					),
					array(
						'key'     => '_fp_session_id',
						'value'   => $session_ids->posts,
						'compare' => 'IN'
					),
				),
			);

			$bookings = new WP_Query( $args );

			if( $bookings->have_posts() ):

				foreach( $bookings->posts as $booking ):

					$session_id = get_post_meta( $booking->ID, '_fp_session_id', true );
					$start_time = get_post_meta( $session_id, '_fp_start_time', true );
					$end_time = get_post_meta( $session_id, '_fp_end_time', true );
					$class_id = get_post_meta( $session_id, '_fp_class_id', true );

					$url = add_query_arg(
						array(
							'session_id' => $session_id
						),
						fp_cancel_booking_url()
					);

					$booking_data[] = array(
						'class' => get_the_title( $class_id ),
						'date' => date( 'l, j F Y', $start_time ),
						'start_time' => date( 'H:i', $start_time ),
						'end_time' => date( 'H:i', $start_time ),
						'action'     => '<a href="' . $url . '" class="btn btn-flat btn-small btn-cancel">Cancel</a>',
					);

				endforeach;

			endif;

		elseif( $params['session_id'] ):

			$args = array(
				'post_type'  => 'fp_booking',
				'meta_query' => array(
					array(
						'key'   => '_fp_session_id',
						'value' => $params['session_id'],
					)
				),
			);

			$bookings = new WP_Query( $args );

			if( $bookings->have_posts() ):

				foreach( $bookings->posts as $booking ):

					$session_id = $params['session_id'];
					$user_id = get_post_meta( $booking->ID, '_fp_user_id', true );
					$user = get_user_by( 'id', get_current_user_id() );

					$url = add_query_arg(
						array(
							'post' => $session_id,
							'action' => 'cancel-booking',
						),
						admin_url( 'post.php' )
					);

					$booking_data[] = array(
						'user' => $user,
						'action'     => '<a href="' . $url . '" class="btn btn-flat btn-small btn-cancel">Cancel</a>',
						'action'     => '',
					);

				endforeach;

			endif;

		endif;

		return $booking_data;

	}

}

/**
 * Extension main function
 */
function __fp_booking_main() {
    new FP_Booking();
}

// Initialize plugin when plugins are loaded
add_action( 'plugins_loaded', '__fp_booking_main' );
