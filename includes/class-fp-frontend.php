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

		add_shortcode( 'fitpress_signup', array( $this, 'signup_output' ) );
		add_action( 'template_redirect', array( $this, 'process_signup' ) );

	}

	public function membership_output( $atts ) {

		$args = array(
			'post_type' => 'membership',
			'posts_per_page' => '10',
		);

		$memberships = new WP_Query( $args );

		$signup = apply_filters( 'fp_membership_signup_button', array( 'text' => 'Enquire', 'link' => fp_get_page_permalink( 'sign-up' ) ) );

		fp_get_template( 'general/shortcode.php', array( 'memberships' => $memberships, 'signup' => $signup ) );

	}

	public function signup_output( $atts ) {

		fp_get_template( 'general/sign-up.php' );

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

		$account_first_name = ! empty( $_POST[ 'account_first_name' ] ) ? sanitize_text_field( $_POST[ 'account_first_name' ] ) : '';
		$account_last_name  = ! empty( $_POST[ 'account_last_name' ] ) ? sanitize_text_field( $_POST[ 'account_last_name' ] ) : '';
		$account_email      = ! empty( $_POST[ 'account_email' ] ) ? sanitize_email( $_POST[ 'account_email' ] ) : '';
		$contact_number      = ! empty( $_POST[ 'contact_number' ] ) ? sanitize_text_field( $_POST[ 'contact_number' ] ) : '';

		$emergency_contact_name      = ! empty( $_POST[ 'emergency_contact_name' ] ) ? sanitize_text_field( $_POST[ 'emergency_contact_name' ] ) : '';
		$emergency_contact_number      = ! empty( $_POST[ 'emergency_contact_number' ] ) ? sanitize_text_field( $_POST[ 'emergency_contact_number' ] ) : '';
		$account_username      = ! empty( $_POST[ 'account_username' ] ) ? sanitize_text_field( $_POST[ 'account_username' ] ) : '';

		$membership_id		= ! empty( $_GET[ 'membership_id' ] ) ? $_GET[ 'membership_id' ] : '';
		$membership_status	= 'on-hold';

		$pass1              = ! empty( $_POST[ 'password_1' ] ) ? $_POST[ 'password_1' ] : '';
		$pass2              = ! empty( $_POST[ 'password_2' ] ) ? $_POST[ 'password_2' ] : '';

		if ( empty( $membership_id ) ) :
			fp_add_flash_message( __( 'Please select a package.', 'fitpress' ), 'error' );
			wp_safe_redirect( home_url() );
			exit;
		endif;

		if ( empty( $account_first_name ) || empty( $account_last_name ) ) :
			fp_add_flash_message( __( 'Please enter your name.', 'fitpress' ), 'error' );
		endif;

		if ( empty( $account_email ) || ! is_email( $account_email ) ) :
			fp_add_flash_message( __( 'Please provide a valid email address.', 'fitpress' ), 'error' );
		elseif ( email_exists( $account_email ) && $account_email !== $current_user->user_email ) :
			fp_add_flash_message( __( 'This email address is already registered.', 'fitpress' ), 'error' );
		endif;

		if ( empty( $contact_number ) ) :
			fp_add_flash_message( __( 'Please enter your contact number.', 'fitpress' ), 'error' );
		endif;

		if ( empty( $emergency_contact_name ) || empty( $emergency_contact_number ) ) :
			fp_add_flash_message( __( 'Please enter an emergency cotnact.', 'fitpress' ), 'error' );
		endif;

		if ( empty( $account_username ) ) :
			fp_add_flash_message( __( 'Please choose a username.', 'fitpress' ), 'error' );
		elseif ( username_exists( $account_username ) ) :
			fp_add_flash_message( __( 'A member with that username already exists. Please choose another username.', 'fitpress' ), 'error' );
		endif;

		if ( empty( $pass1 ) || empty( $pass2 ) ) :
			fp_add_flash_message( __( 'Please fill out all password fields.', 'fitpress' ), 'error' );
		elseif ( ! empty( $pass1 ) && $pass1 !== $pass2 ) :
			fp_add_flash_message( __( 'Passwords do not match.', 'fitpress' ), 'error' );
		endif;

		if ( $errors->get_error_messages() ) :
			foreach ( $errors->get_error_messages() as $error ) :
				fp_add_flash_message( $error, 'error' );
			endforeach;
		endif;

		if ( fp_flash_message_count( 'error' ) === 0 ) :

			$user = array(
				'user_login' 	=> $account_username,
				'user_email' 	=> $account_email,
				'display_name'	=> $account_first_name . ' ' . $account_last_name,
				'user_pass'		=> $pass1,
			);

			$user_id = wp_insert_user( $user );

			if ( ! is_wp_error( $user_id ) ) :

				update_user_meta( $user_id, 'first_name', $account_first_name );
				update_user_meta( $user_id, 'last_name', $account_last_name );
				update_user_meta( $user_id, 'contact_number', $contact_number );
				update_user_meta( $user_id, 'emergency_contact_name', $emergency_contact_name );
				update_user_meta( $user_id, 'emergency_contact_number', $emergency_contact_number );

				update_user_meta( $user_id, 'fitpress_membership_id', $membership_id );
				update_user_meta( $user_id, 'fitpress_membership_status', $membership_status );

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
