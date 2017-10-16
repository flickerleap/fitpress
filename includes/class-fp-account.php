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
 * FP_Account Class.
 */
class FP_Account {

	public $query_vars = array();

	/**
	 * Hook in methods.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'add_endpoints' ) );

		add_action( 'template_redirect', array( $this, 'maybe_show_details_alert' ) );

		add_action( 'template_redirect', array( $this, 'save_account_details' ) );
		add_action( 'wp_loaded', array( $this, 'process_login' ), 20 );
		add_action( 'wp_loaded', array( $this, 'process_lost_password' ), 20 );
		add_action( 'wp_loaded', array( $this, 'process_reset_password' ), 20 );

		add_action( 'template_redirect', array( $this, 'update_membership' ) );

		if ( ! is_admin() ) :
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
		endif;

		$this->init_query_vars();

		add_shortcode( 'fitpress_account', array( $this, 'output' ) );

	}

	public function add_endpoints() {

		foreach ( $this->query_vars as $var ) {
			add_rewrite_endpoint( $var, EP_ROOT | EP_PAGES );
		}

	}

	public function maybe_show_details_alert() {

		global $wp;
		global $wp_query;

		if ( $_SERVER['REQUEST_METHOD'] === 'POST' || is_admin() ) :
			return;
		endif;

		$user_id      = (int) get_current_user_id();
		$details_page = isset( $wp->query_vars['update-account'] ) || get_page_by_path( 'sign-up' )->ID == $wp_query->queried_object_id;

		if ( $user_id && ! $details_page && ( ! get_user_meta( $user_id, 'first_name', true ) || ! get_user_meta( $user_id, 'last_name', true ) || ! get_user_meta( $user_id, 'contact_number', true ) || ! get_user_meta( $user_id, 'emergency_contact_name', true ) || ! get_user_meta( $user_id, 'emergency_contact_number', true ) ) ) :
			fp_add_flash_message(
				sprintf(
					__( 'We do not have all your details, please update them %shere%s.', 'fitpress' ),
					'<a href="' . fp_customer_edit_account_url() . '">',
					'</a>'
				),
				'error'
			);
		endif;
	}

	/**
	 * Init query vars by loading options.
	 */
	public function init_query_vars() {
		$this->query_vars = array(
			'update-account',
			'lost-password',
			'member-logout',
			'book',
			'make-booking',
			'cancel-booking',
			'membership',
		);
	}

	/**
	 * add_query_vars function.
	 *
	 * @access public
	 *
	 * @param array $vars
	 *
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
			} elseif ( isset( $wp->query_vars[ $var ] ) ) {
				$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
			}
		}
	}

	public function output( $atts ) {

		global $wp;

		$return = '';

		if ( ! is_user_logged_in() ) {

			if ( isset( $wp->query_vars['lost-password'] ) ) {

				$return .= self::lost_password();

			} else {

				$return .= fp_get_template_html( 'account/form-login.php' );

			}

		} else {

			$return .= self::account_menu();

			if ( isset( $wp->query_vars['update-account'] ) ) {

				$return .= self::edit_account();

			} elseif ( isset( $wp->query_vars['book'] ) ) {

				$return .= self::book_sessions();

			} elseif ( isset( $wp->query_vars['membership'] ) ) {

				$return .= self::membership();

			} else {

				$return .= self::account( $atts );

			}
		}

		return $return;

	}


	/**
	 * My account page
	 *
	 * @param $atts
	 *
	 * @return string
	 */
	private static function account( $atts ) {
		extract( shortcode_atts( array(), $atts ) );

		return fp_get_template_html( 'account/account.php', array(
			'current_user'    => get_userdata( get_current_user_id() ),
			'booked_sessions' => FP_Booking::get_booked_sessions( array( 'member_id' => get_current_user_id() ) ),
		) );
	}


	/**
	 * My membership page
	 *
	 * @return string
	 */
	private static function membership() {

		$packages = FP_Package::get_memberships();

		$membership = FP_Package::get_user_membership( get_current_user_id() );

		if ( $membership ) :

			$membership_status    = new FP_Membership_Status( $membership['membership_id'] );
			$membership['status'] = $membership_status->get_status();

			$membership['renewal_date'] = get_post_meta( $membership['membership_id'], '_fp_renewal_date', true );
			$membership['expiration']   = get_post_meta( $membership['membership_id'], '_fp_expiration_date', true );

		endif;

		return fp_get_template_html( 'account/membership.php', array(
			'membership'    => $membership,
			'packages'      => $packages,
			'membership_id' => $membership['membership_id'],
		) );
	}

	/**
	 * Account menu
	 *
	 * @return string
	 */
	private static function account_menu() {

		return fp_get_template_html( 'account/menu.php', array(
			'current_user' => get_userdata( get_current_user_id() )
		) );

	}

	/**
	 * Edit account details page
	 */
	private static function edit_account() {
		return fp_get_template_html( 'account/form-update-account.php', array( 'user' => get_userdata( get_current_user_id() ) ) );
	}

	/**
	 * Edit account details page
	 */
	private static function book_sessions() {

		$session_data = FP_Booking::bookable_sesssion();

		return fp_get_template_html( 'account/calender-bookings.php', array(
			'sessions' => $session_data['sessions'],
			'user_id'  => $session_data['user_id'],
			'credits'  => $session_data['credits'],
		) );

	}

	/**
	 * Lost password page
	 */
	public static function lost_password() {

		global $post;

		// arguments to pass to template
		$args = array( 'form' => 'lost_password' );

		// process reset key / login from email confirmation link
		if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {

			$user = self::check_password_reset_key( $_GET['key'], $_GET['login'] );

			// reset key / login is correct, display reset password form with hidden key / login values
			if ( is_object( $user ) ) {
				$args['form']  = 'reset_password';
				$args['key']   = esc_attr( $_GET['key'] );
				$args['login'] = esc_attr( $_GET['login'] );
			}
		} elseif ( isset( $_GET['reset'] ) ) {
			fp_add_flash_message( __( 'Your password has been reset.', 'fitpress' ) . ' <a href="' . fp_get_page_permalink( 'account' ) . '">' . __( 'Log in', 'fitpress' ) . '</a>' );
		}

		return fp_get_template_html( 'account/form-lost-password.php', $args );
	}

	/**
	 * Handles sending password retrieval email to customer.
	 *
	 * Based on retrieve_password() in core wp-login.php
	 *
	 * @access public
	 * @uses $wpdb WordPress Database object
	 * @return bool True: when finish. False: on error
	 */
	public static function retrieve_password() {
		global $wpdb, $wp_hasher;

		if ( empty( $_POST['user_login'] ) ) {

			fp_add_flash_message( __( 'Enter a username or e-mail address.', 'fitpress' ), 'error' );

			return false;

		} else {
			// Check on username first, as customers can use emails as usernames.
			$login     = trim( $_POST['user_login'] );
			$user_data = get_user_by( 'login', $login );
		}

		// If no user found, check if it login is email and lookup user based on email.
		if ( ! $user_data && is_email( $_POST['user_login'] ) ) {
			$user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
		}

		do_action( 'lostpassword_post' );

		if ( ! $user_data ) {

			fp_add_flash_message( __( 'Invalid username or e-mail.', 'fitpress' ), 'error' );

			return false;
		}

		if ( is_multisite() && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
			fp_add_flash_message( __( 'Invalid username or e-mail.', 'fitpress' ), 'error' );

			return false;
		}

		// redefining user_login ensures we return the right case in the email
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

		if ( ! $allow ) {

			fp_add_flash_message( __( 'Password reset is not allowed for this user', 'fitpress' ), 'error' );

			return false;

		} elseif ( is_wp_error( $allow ) ) {

			fp_add_flash_message( $allow->get_error_message(), 'error' );

			return false;
		}

		$key = wp_generate_password( 20, false );

		do_action( 'retrieve_password_key', $user_login, $key );

		// Now insert the key, hashed, into the DB.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}

		$hashed = $wp_hasher->HashPassword( $key );

		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );

		$reset_url = fp_lostpassword_url() . '?login=' . $user_login . '&key=' . $key;

		$FP_Email = new FP_Email( array( 'template' => 'email/default.php' ) );

		$FP_Email->send_email( $user_email, get_bloginfo( 'name' ) . ' Password Reset Link', array(
			'header'  => 'Reset Password',
			'message' => $message = '<p>Click this link to reset your password: <a href="' . $reset_url . '">' . $reset_url . '</a></p>',
		) );

		fp_add_flash_message( __( 'Check your e-mail for the confirmation link.', 'fitpress' ) );

		return true;
	}

	/**
	 * Retrieves a user row based on password reset key and login
	 *
	 * @uses $wpdb WordPress Database object
	 *
	 * @param string $key Hash to validate sending user's password
	 * @param string $login The user login
	 *
	 * @return WP_USER|bool User's database row on success, false for invalid keys
	 */
	public static function check_password_reset_key( $key, $login ) {
		global $wpdb, $wp_hasher;

		$key = preg_replace( '/[^a-z0-9]/i', '', $key );

		if ( empty( $key ) || ! is_string( $key ) ) {
			fp_add_flash_message( __( 'Invalid key', 'fitpress' ), 'error' );

			return false;
		}

		if ( empty( $login ) || ! is_string( $login ) ) {
			fp_add_flash_message( __( 'Invalid key', 'fitpress' ), 'error' );

			return false;
		}

		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_login = %s", $login ) );

		if ( ! empty( $user ) ) {
			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . 'wp-includes/class-phpass.php';
				$wp_hasher = new PasswordHash( 8, true );
			}

			$valid = $wp_hasher->CheckPassword( $key, $user->user_activation_key );
		}

		if ( empty( $user ) || empty( $valid ) ) {
			fp_add_flash_message( __( 'Invalid key', 'fitpress' ), 'error' );

			return false;
		}

		return get_userdata( $user->ID );
	}

	/**
	 * Handles resetting the user's password.
	 *
	 * @access public
	 *
	 * @param object $user The user
	 * @param string $new_pass New password for the user in plaintext
	 *
	 * @return void
	 */
	public static function reset_password( $user, $new_pass ) {
		do_action( 'password_reset', $user, $new_pass );

		wp_set_password( $new_pass, $user->ID );

		wp_password_change_notification( $user );
	}

	/**
	 * Process the login form.
	 */
	public static function process_login() {
		if ( ! empty( $_POST['login'] ) && ! empty( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'fitpress-login' ) ) {

			try {
				$creds = array();

				$validation_error = new WP_Error();

				if ( $validation_error->get_error_code() ) {
					throw new Exception( '<strong>' . __( 'Error', 'fitpress' ) . ':</strong> ' . $validation_error->get_error_message() );
				}

				if ( empty( $_POST['username'] ) ) {
					throw new Exception( '<strong>' . __( 'Error', 'fitpress' ) . ':</strong> ' . __( 'Username is required.', 'fitpress' ) );
				}

				if ( empty( $_POST['password'] ) ) {
					throw new Exception( '<strong>' . __( 'Error', 'fitpress' ) . ':</strong> ' . __( 'Password is required.', 'fitpress' ) );
				}

				if ( is_email( $_POST['username'] ) && apply_filters( 'woocommerce_get_username_from_email', true ) ) {
					$user = get_user_by( 'email', $_POST['username'] );

					if ( isset( $user->user_login ) ) {
						$creds['user_login'] = $user->user_login;
					} else {
						throw new Exception( '<strong>' . __( 'Error', 'fitpress' ) . ':</strong> ' . __( 'A user could not be found with this email address.', 'fitpress' ) );
					}

				} else {
					$creds['user_login'] = $_POST['username'];
				}

				$creds['user_password'] = $_POST['password'];
				$creds['remember']      = isset( $_POST['rememberme'] );
				$secure_cookie          = is_ssl() ? true : false;
				$user                   = wp_signon( apply_filters( 'woocommerce_login_credentials', $creds ), $secure_cookie );

				if ( is_wp_error( $user ) ) {
					throw new Exception( $user->get_error_message() );
				} else {

					if ( ! empty( $_POST['redirect_to'] ) ) {
						$redirect = $_POST['redirect_to'];
					} elseif ( wp_get_referer() ) {
						$redirect = wp_get_referer();
					} else {
						$redirect = fp_get_page_permalink( 'account' );
					}

					// Feedback
					fp_add_flash_message( sprintf( __( 'You are now logged in as <strong>%s</strong>', 'fitpress' ), $user->display_name ) );

					wp_redirect( $redirect );
					exit;
				}

			} catch ( Exception $e ) {

				fp_add_flash_message( apply_filters( 'login_errors', $e->getMessage() ), 'error' );

			}
		}
	}

	/**
	 * Handle lost password form
	 */
	public static function process_lost_password() {
		if ( isset( $_POST['fp_reset_password'] ) && isset( $_POST['user_login'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'lost_password' ) ) {
			self::retrieve_password();
		}
	}

	/**
	 * Handle reset password form
	 */
	public static function process_reset_password() {
		$posted_fields = array(
			'fp_reset_password',
			'password_1',
			'password_2',
			'reset_key',
			'reset_login',
			'_wpnonce',
		);

		foreach ( $posted_fields as $field ) {
			if ( ! isset( $_POST[ $field ] ) ) {
				return;
			}
			$posted_fields[ $field ] = $_POST[ $field ];
		}

		if ( ! wp_verify_nonce( $posted_fields['_wpnonce'], 'reset_password' ) ) {
			return;
		}

		$user = self::check_password_reset_key( $posted_fields['reset_key'], $posted_fields['reset_login'] );

		if ( $user instanceof WP_User ) {
			if ( empty( $posted_fields['password_1'] ) ) {
				fp_add_flash_message( __( 'Please enter your password.', 'fitpress' ), 'error' );
			}

			if ( $posted_fields['password_1'] !== $posted_fields['password_2'] ) {
				fp_add_flash_message( __( 'Passwords do not match.', 'fitpress' ), 'error' );
			}

			$errors = new WP_Error();

			do_action( 'validate_password_reset', $errors, $user );

			//wc_add_wp_error_notices( $errors );

			if ( 0 === fp_flash_message_count( 'error' ) ) {
				self::reset_password( $user, $posted_fields['password_1'] );

				//do_action( 'woocommerce_customer_reset_password', $user );

				wp_redirect( add_query_arg( 'reset', 'true', remove_query_arg( array( 'key', 'login' ) ) ) );
				exit;
			}
		}
	}


	/**
	 * Save the password/account details and redirect back to the my account page.
	 */
	public static function update_membership() {

		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'update_membership' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'update_membership' ) ) {
			return;
		}

		$member_id = get_current_user_id();

		$membership = FP_Package::get_user_membership( $member_id );

		$membership_id = $membership['membership_id'];

		$old_package_id = $membership['package_id'];

		$package_id = sanitize_text_field( $_POST['package_id'] );

		do_action( 'fitpress_before_membership_save', array( 'membership_id' => $membership_id, 'package_id' => $package_id, 'old_package_id' => $old_package_id ) );

		update_post_meta( $membership_id, '_fp_package_id', $package_id );

		do_action( 'fitpress_after_membership_save', array( 'membership_id' => $membership_id, 'package_id' => $package_id, 'old_package_id' => $old_package_id ) );

	}

	/**
	 * Save the password/account details and redirect back to the my account page.
	 */
	public static function save_account_details() {

		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'save_account_details' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_account_details' ) ) {
			return;
		}

		$update = true;
		$errors = new WP_Error();
		$user   = new stdClass();

		$user->ID     = (int) get_current_user_id();
		$current_user = get_user_by( 'id', $user->ID );

		if ( $user->ID <= 0 ) {
			return;
		}

		$account_first_name       = ! empty( $_POST['account_first_name'] ) ? sanitize_text_field( $_POST['account_first_name'] ) : '';
		$account_last_name        = ! empty( $_POST['account_last_name'] ) ? sanitize_text_field( $_POST['account_last_name'] ) : '';
		$account_email            = ! empty( $_POST['account_email'] ) ? sanitize_email( $_POST['account_email'] ) : '';
		$contact_number           = ! empty( $_POST['contact_number'] ) ? sanitize_text_field( $_POST['contact_number'] ) : '';
		$emergency_contact_name   = ! empty( $_POST['emergency_contact_name'] ) ? sanitize_text_field( $_POST['emergency_contact_name'] ) : '';
		$emergency_contact_number = ! empty( $_POST['emergency_contact_number'] ) ? sanitize_text_field( $_POST['emergency_contact_number'] ) : '';
		$pass_cur                 = ! empty( $_POST['password_current'] ) ? $_POST['password_current'] : '';
		$pass1                    = ! empty( $_POST['password_1'] ) ? $_POST['password_1'] : '';
		$pass2                    = ! empty( $_POST['password_2'] ) ? $_POST['password_2'] : '';
		$save_pass                = true;

		$user->first_name   = $account_first_name;
		$user->last_name    = $account_last_name;
		$user->user_email   = $account_email;
		$user->display_name = $user->first_name;

		if ( empty( $account_first_name ) || empty( $account_last_name ) ) {
			fp_add_flash_message( __( 'Please enter your name.', 'fitpress' ), 'error' );
		}

		if ( empty( $account_email ) || ! is_email( $account_email ) ) {
			fp_add_flash_message( __( 'Please provide a valid email address.', 'fitpress' ), 'error' );
		} elseif ( email_exists( $account_email ) && $account_email !== $current_user->user_email ) {
			fp_add_flash_message( __( 'This email address is already registered.', 'fitpress' ), 'error' );
		}

		if ( empty( $contact_number ) ) {
			fp_add_flash_message( __( 'Please enter your contact number.', 'fitpress' ), 'error' );
		}

		if ( empty( $emergency_contact_name ) || empty( $emergency_contact_number ) ) {
			fp_add_flash_message( __( 'Please enter an emergency cotnact.', 'fitpress' ), 'error' );
		}

		if ( ! empty( $pass1 ) && ! wp_check_password( $pass_cur, $current_user->user_pass, $current_user->ID ) ) {
			fp_add_flash_message( __( 'Your current password is incorrect.', 'fitpress' ), 'error' );
			$save_pass = false;
		}

		if ( ! empty( $pass_cur ) && empty( $pass1 ) && empty( $pass2 ) ) {
			fp_add_flash_message( __( 'Please fill out all password fields.', 'fitpress' ), 'error' );

			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && empty( $pass_cur ) ) {
			fp_add_flash_message( __( 'Please enter your current password.', 'fitpress' ), 'error' );

			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && empty( $pass2 ) ) {
			fp_add_flash_message( __( 'Please re-enter your password.', 'fitpress' ), 'error' );

			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && $pass1 !== $pass2 ) {
			fp_add_flash_message( __( 'Passwords do not match.', 'fitpress' ), 'error' );

			$save_pass = false;
		}

		if ( $pass1 && $save_pass ) {
			$user->user_pass = $pass1;
		}

		if ( $errors->get_error_messages() ) {
			foreach ( $errors->get_error_messages() as $error ) {
				fp_add_flash_message( $error, 'error' );
			}
		}

		if ( fp_flash_message_count( 'error' ) === 0 ) {

			wp_update_user( $user );

			update_user_meta( $user->ID, 'contact_number', $contact_number );
			update_user_meta( $user->ID, 'emergency_contact_name', $emergency_contact_name );
			update_user_meta( $user->ID, 'emergency_contact_number', $emergency_contact_number );

			fp_add_flash_message( __( 'Account details changed successfully.', 'fitpress' ) );

			wp_safe_redirect( fp_get_page_permalink( 'account' ) );
			exit;

		}
	}

}

/**
 * Extension main function
 */
function __fp_account_main() {
	new FP_Account();
}

// Initialize plugin when plugins are loaded
add_action( 'plugins_loaded', '__fp_account_main' );
