<?php
/**
 * Frontend Actions
 *
 * Creates frontend features like membership shortcodes and signup
 *
 * @class     FP_Frontend
 * @version   2.5.0
 * @package   FitPress/Classes/Frontend
 * @category  Class
 * @author    Digital Leap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FP_Post_Types Class.
 */
class FP_Frontend {

	public $query_vars;

	/**
	 * We only want a single instance of this class.
	 */
	private static $instance = null;

	/*
	* Creates or returns an instance of this class.
	*
	* @return  FP_Membership A single instance of this class.
	*/
	public static function get_instance( ) {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	} // end get_instance;

	/**
	 * Hook in methods.
	 */
	public function __construct(){

		add_shortcode( 'fitpress_memberships', array( $this, 'membership_output' ) );

		add_action( 'init', array( $this, 'add_endpoints' ) );

		add_action( 'template_redirect', array( $this, 'process_signup' ) );

		if ( ! is_admin() ) :
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
			add_action( 'parse_request', array( $this, 'maybe_notification_request' ), 0 );
		endif;

		$this->init_query_vars();

		add_shortcode( 'fitpress_signup', array( $this, 'signup_output' ) );

	}

	public function add_endpoints(){

		foreach ( $this->query_vars as $var ) {
			add_rewrite_endpoint( $var, EP_ROOT | EP_PAGES );
		}

	}

	/**
	 * Init query vars by loading options.
	 */
	public function init_query_vars() {
		$this->query_vars = array(
			'checkout',
			'confirm',
			'notify',
			'cancel',
		);
	}

	/**
	 * add_query_vars function.
	 *
	 * @access public
	 * @param array $vars
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		foreach ( $this->query_vars as $key => $var ) {
			$vars[] = $key;
		}

		return $vars;
	}

	/**
	 * Parse the request and look for query vars - endpoints may not be supported
	 */
	public function parse_request() {
		global $wp;

		// Map query vars to their keys, or get them if endpoints are not supported
		foreach ( $this->query_vars as $key => $var ) {
			if ( isset( $_GET[ $var ] ) ) {
				$wp->query_vars[ $key ] = $_GET[ $var ];
			}

			elseif ( isset( $wp->query_vars[ $var ] ) ) {
				$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
			}
		}
	}

	public function maybe_notification_request(){

		global $wp;

		if ( isset( $wp->query_vars['notify'] ) ) :

			self::notify();

		endif;

	}

	public function membership_output( $atts ) {

		$args = array(
			'post_type' => 'membership',
			'posts_per_page' => '10',
		);

		$memberships = new WP_Query( $args );

		$signup = apply_filters( 'fp_membership_signup_button', array( 'text' => 'Enquire', 'link' => fp_get_page_permalink( 'sign-up' ) ) );

		fp_get_template_html( 'general/shortcode.php', array( 'memberships' => $memberships, 'signup' => $signup ) );

	}

	public function signup_output( $atts ) {

		global $wp;

		$return = '';

		if ( isset( $wp->query_vars['checkout'] ) ) :

			return self::checkout();

		elseif ( isset( $wp->query_vars['cancel'] ) ) :

			return self::cancel();

		elseif ( isset( $wp->query_vars['confirm'] ) ) :

			return fp_get_template_html( 'sign-up/confirm.php' );

		else :

			$memberships = FP_Membership::get_memberships();

			return fp_get_template_html( 'sign-up/sign-up.php', array(
				'memberships' => $memberships,
				'current_user' 	=> get_userdata( get_current_user_id() ),
			) );

		endif;

	}

	/**
	 * My account page
	 *
	 * @param  array $atts
	 */
	private static function checkout( ) {

		$payment = new FP_Payment();

		$payment_methods = $payment->get_methods();

		$user_id = get_current_user_id();

		$membership = FP_Membership::get_user_membership( $user_id );

		$current_user = get_userdata( $user_id );

		$return =  fp_get_template_html( 'sign-up/checkout.php', array(
			'current_user' 	=> $current_user,
			'membership' => $membership,
		) );

		foreach( $payment_methods as $method => $name ):

			$return .= apply_filters( 'fitpress_payment_method_' . $method, $membership, $current_user );

		endforeach;

		return $return;

	}

	/**
	 * My account page
	 *
	 * @param  array $atts
	 */
	private static function notify( ) {

		$method = $_GET['method'];

		do_action( 'fitpress_payment_notify_' . $method, $_POST );

	}

	/**
	 * My account page
	 *
	 * @param  array $atts
	 */
	private static function cancel( ) {

		$return = '';

		$payment = new FP_Payment();

		$payment_methods = $payment->get_methods();

		$user_id = get_current_user_id();

		$membership = FP_Membership::get_user_membership( $user_id );

		$current_user = get_userdata( $user_id );

		$return .= fp_get_template( 'sign-up/checkout.php', array(
			'current_user' 	=> $current_user,
			'membership' => $membership,
		) );

		foreach( $payment_methods as $method => $name ):

			$return .= apply_filters( 'fitpress_payment_method_' . $method, $membership, $current_user );

		endforeach;

		return $return;

	}

	public function process_signup(){

		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) :
			return;
		endif;

		if ( empty( $_POST[ 'action' ] ) || 'signup_account' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'signup_account' ) ) :
			return;
		endif;

		$errors       = new WP_Error();
		$user         = new stdClass();

		$user->ID     = (int) get_current_user_id();

		$account_first_name = ! empty( $_POST[ 'account_first_name' ] ) ? sanitize_text_field( $_POST[ 'account_first_name' ] ) : '';
		$account_last_name  = ! empty( $_POST[ 'account_last_name' ] ) ? sanitize_text_field( $_POST[ 'account_last_name' ] ) : '';
		$account_email      = ! empty( $_POST[ 'account_email' ] ) ? sanitize_email( $_POST[ 'account_email' ] ) : '';
		$contact_number      = ! empty( $_POST[ 'contact_number' ] ) ? sanitize_text_field( $_POST[ 'contact_number' ] ) : '';

		$emergency_contact_name      = ! empty( $_POST[ 'emergency_contact_name' ] ) ? sanitize_text_field( $_POST[ 'emergency_contact_name' ] ) : '';
		$emergency_contact_number      = ! empty( $_POST[ 'emergency_contact_number' ] ) ? sanitize_text_field( $_POST[ 'emergency_contact_number' ] ) : '';
		$account_username      = ! empty( $_POST[ 'account_username' ] ) ? sanitize_text_field( $_POST[ 'account_username' ] ) : '';

		$membership_id		= ! empty( $_POST[ 'membership_id' ] ) ? $_POST[ 'membership_id' ] : '';
		$membership_status	= 'on-hold';

		$pass1              = ! empty( $_POST[ 'password_1' ] ) ? $_POST[ 'password_1' ] : '';
		$pass2              = ! empty( $_POST[ 'password_2' ] ) ? $_POST[ 'password_2' ] : '';

		if ( empty( $membership_id ) ) :
			fp_add_flash_message( __( 'Please select a package.', 'fitpress' ), 'error' );
		endif;

		if ( empty( $account_first_name ) || empty( $account_last_name ) ) :
			fp_add_flash_message( __( 'Please enter your name.', 'fitpress' ), 'error' );
		endif;

		if ( empty( $account_email ) || ! is_email( $account_email ) ) :
			fp_add_flash_message( __( 'Please provide a valid email address.', 'fitpress' ), 'error' );
		endif;

		if ( empty( $contact_number ) ) :
			fp_add_flash_message( __( 'Please enter your contact number.', 'fitpress' ), 'error' );
		endif;

		if ( empty( $emergency_contact_name ) || empty( $emergency_contact_number ) ) :
			fp_add_flash_message( __( 'Please enter an emergency contact.', 'fitpress' ), 'error' );
		endif;

		$email_user_id = email_exists( $account_email );
		$username_user_id = username_exists( $account_username );

		if ( $user->ID <= 0 ) :

			if ( $email_user_id && is_user_member_of_blog( $email_user_id, get_current_blog_id() ) ) :
				fp_add_flash_message( __( 'This email address is already registered.', 'fitpress' ), 'error' );
			endif;

			if ( empty( $account_username ) ) :
				fp_add_flash_message( __( 'Please choose a username.', 'fitpress' ), 'error' );
			elseif ( $username_user_id && is_user_member_of_blog( $username_user_id, get_current_blog_id() ) ) :
				fp_add_flash_message( __( 'A member with that username already exists. Please choose another username.', 'fitpress' ), 'error' );
			elseif ( $username_user_id && $email_user_id && $username_user_id != $email_user_id ):
				fp_add_flash_message( __( 'A member with that username already exists. Please choose another username.', 'fitpress' ), 'error' );
			elseif ( $username_user_id && $email_user_id && $username_user_id == $email_user_id ):
					add_user_to_blog( get_current_blog_id(), $username_user_id, 'subscriber' );
					update_user_meta( $username_user_id, 'fitpress_membership_id', $membership_id );
					update_user_meta( $username_user_id, 'fitpress_membership_status', $membership_status );
					fp_add_flash_message( __( 'A user account was found with your details. The account has been linked to this membership, please <a href="' . fp_get_page_permalink('account') . '">log in</a> to complete the sign up.', 'fitpress' ), 'error' );
			endif;

			if ( empty( $pass1 ) || empty( $pass2 ) ) :
				fp_add_flash_message( __( 'Please fill out all password fields.', 'fitpress' ), 'error' );
			elseif ( ! empty( $pass1 ) && $pass1 !== $pass2 ) :
				fp_add_flash_message( __( 'Passwords do not match.', 'fitpress' ), 'error' );
			endif;

		else :

			$current_user = get_user_by( 'id', $user->ID );

			if ( $email_user_id && is_user_member_of_blog( $email_user_id, get_current_blog_id() ) && $account_email !== $current_user->user_email ) :
				fp_add_flash_message( __( 'This email address is already registered.', 'fitpress' ), 'error' );
			endif;

		endif;

		if ( $errors->get_error_messages() ) :
			foreach ( $errors->get_error_messages() as $error ) :
				fp_add_flash_message( $error, 'error' );
			endforeach;
		endif;

		if ( fp_flash_message_count( 'error' ) === 0 ) :

			if ( $user->ID <= 0 ) :

				$user = array(
					'user_login' 	=> $account_username,
					'user_email' 	=> $account_email,
					'display_name'	=> $account_first_name . ' ' . $account_last_name,
					'user_pass'		=> $pass1,
				);

				$user_id = wp_insert_user( $user );

			else :

				$current_user = get_user_by( 'id', $user->ID );

				$user->user_email = $account_email;
				$user->display_name = $account_first_name . ' ' . $account_last_name;

				$user_id = wp_update_user( $user );

			endif;

			if ( ! is_wp_error( $user_id ) ) :

				update_user_meta( $user_id, 'first_name', $account_first_name );
				update_user_meta( $user_id, 'last_name', $account_last_name );
				update_user_meta( $user_id, 'contact_number', $contact_number );
				update_user_meta( $user_id, 'emergency_contact_name', $emergency_contact_name );
				update_user_meta( $user_id, 'emergency_contact_number', $emergency_contact_number );

				update_user_meta( $user_id, 'fitpress_membership_id', $membership_id );
				update_user_meta( $user_id, 'fitpress_membership_status', $membership_status );
				update_user_meta( $user_id, 'fitpress_membership_date', strtotime( date('j F Y') ) );

				fp_add_flash_message( __( 'Sign up complete.', 'fitpress' ) );

				wp_set_current_user( $user_id );
				wp_set_auth_cookie( $user_id );

				do_action( 'fitpress_member_signup', $user_id, $membership_id );

				wp_safe_redirect( apply_filters( 'fitpress_signup_redirect', fp_get_page_permalink( 'account' ) ) );
				exit;

			else :

				fp_add_flash_message( __( 'An error occurred while signing up. Please try again.', 'fitpress' ), 'error' );

			endif;

		endif;

	}

}

/**
 * Extension main function
 */
function __fp_frontend_main() {
	FP_Frontend::get_instance();
}

// Initialize plugin when plugins are loaded
add_action( 'plugins_loaded', '__fp_frontend_main' );
