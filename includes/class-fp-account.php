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
class FP_Account {

	public $query_vars = array();

	/**
	 * Hook in methods.
	 */
    public function __construct(){

		add_action( 'init', array( $this, 'add_endpoints' ) );
		
		add_action( 'template_redirect', array( $this, 'save_account_details' ) );
		add_action( 'wp_loaded', array( $this, 'process_login' ), 20 );
		add_action( 'wp_loaded', array( $this, 'process_lost_password' ), 20 );
		add_action( 'wp_loaded', array( $this, 'process_reset_password' ), 20 );

		if ( ! is_admin() ) {
			add_filter( 'query_vars', array( $this, 'add_query_vars'), 0 );
			add_action( 'parse_request', array( $this, 'parse_request'), 0 );
		}

		$this->init_query_vars();

		add_shortcode( 'fitpress_account', array( $this, 'output' ) );

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
		// Query vars to add to WP
		$this->query_vars = array(
			// My account actions
			'edit-account',
			'lost-password',
			'member-logout',
			'book',
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

	public function output( $atts ){

		global $wp;

		if ( ! is_user_logged_in() ) {

			if ( isset( $wp->query_vars['lost-password'] ) ) {

				self::lost_password();

			} else {

				fp_get_template( 'account/form-login.php' );

			}

		} else {

			self::account_menu();

			if ( isset( $wp->query_vars['edit-account'] ) ) {

				self::edit_account();

			} elseif ( isset( $wp->query_vars['book'] ) ) {

				self::book_sessions();

			} else {

				self::account( $atts );

			}
		}

	}

	/**
	 * My account page
	 *
	 * @param  array $atts
	 */
	private static function account( $atts ) {
		extract( shortcode_atts( array(
		), $atts ) );

		fp_get_template( 'account/account.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() ),
			'booked_sessions' => FP_Booking::get_booked_sessions( array( 'user_id' => get_current_user_id() ) ),
		) );
	}

	/**
	 * My account page
	 *
	 * @param  array $atts
	 */
	private static function account_menu( ) {

		fp_get_template( 'account/menu.php', array(
			'current_user' 	=> get_user_by( 'id', get_current_user_id() )
		) );

	}

	/**
	 * Edit account details page
	 */
	private static function edit_account() {
		fp_get_template( 'account/form-edit-account.php', array( 'user' => get_user_by( 'id', get_current_user_id() ) ) );
	}

	/**
	 * Edit account details page
	 */
	private static function book_sessions() {

		$session_data = FP_Booking::bookable_sesssion();

		fp_get_template( 'account/calender-bookings.php',  array( 'sessions' => $session_data['sessions'], 'user_id' => $session_data['user_id'], 'credits' => $session_data['credits'] ) );

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
			if( is_object( $user ) ) {
				$args['form'] = 'reset_password';
				$args['key'] = esc_attr( $_GET['key'] );
				$args['login'] = esc_attr( $_GET['login'] );
			}
		} elseif ( isset( $_GET['reset'] ) ) {
			//wc_add_notice( __( 'Your password has been reset.', 'woocommerce' ) . ' <a href="' . wc_get_page_permalink( 'myaccount' ) . '">' . __( 'Log in', 'woocommerce' ) . '</a>' );
		}

		fp_get_template( 'account/form-lost-password.php', $args );
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

			//wc_add_notice( __( 'Enter a username or e-mail address.', 'woocommerce' ), 'error' );
			return false;

		} else {
			// Check on username first, as customers can use emails as usernames.
			$login = trim( $_POST['user_login'] );
			$user_data = get_user_by( 'login', $login );
		}

		// If no user found, check if it login is email and lookup user based on email.
		if ( ! $user_data && is_email( $_POST['user_login'] ) ) {
			$user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
		}

		do_action( 'lostpassword_post' );

		if ( ! $user_data ) {
			//wc_add_notice( __( 'Invalid username or e-mail.', 'woocommerce' ), 'error' );
			return false;
		}

		if ( is_multisite() && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
			//wc_add_notice( __( 'Invalid username or e-mail.', 'woocommerce' ), 'error' );
			return false;
		}

		// redefining user_login ensures we return the right case in the email
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

		if ( ! $allow ) {

			//wc_add_notice( __( 'Password reset is not allowed for this user', 'woocommerce' ), 'error' );

			return false;

		} elseif ( is_wp_error( $allow ) ) {

			//wc_add_notice( $allow->get_error_message(), 'error' );

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

		$message = '<p>Click this link to reset your password: <a href="' . $reset_url . '">' . $reset_url . '</a></p>'; 

		$headers = array('Content-Type: text/html; charset=UTF-8');

		wp_mail( $user_email, get_bloginfo( 'name' ) . ' Password Reset Link', $message, $headers );

		//wc_add_notice( __( 'Check your e-mail for the confirmation link.', 'woocommerce' ) );
		return true;
	}

	/**
	 * Retrieves a user row based on password reset key and login
	 *
	 * @uses $wpdb WordPress Database object
	 *
	 * @param string $key Hash to validate sending user's password
	 * @param string $login The user login
	 * @return WP_USER|bool User's database row on success, false for invalid keys
	 */
	public static function check_password_reset_key( $key, $login ) {
		global $wpdb, $wp_hasher;

		$key = preg_replace( '/[^a-z0-9]/i', '', $key );

		if ( empty( $key ) || ! is_string( $key ) ) {
			wc_add_notice( __( 'Invalid key', 'woocommerce' ), 'error' );
			return false;
		}

		if ( empty( $login ) || ! is_string( $login ) ) {
			//wc_add_notice( __( 'Invalid key', 'woocommerce' ), 'error' );
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
			wc_add_notice( __( 'Invalid key', 'woocommerce' ), 'error' );
			return false;
		}

		return get_userdata( $user->ID );
	}

	/**
	 * Handles resetting the user's password.
	 *
	 * @access public
	 * @param object $user The user
	 * @param string $new_pass New password for the user in plaintext
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
				$creds  = array();

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
						$creds['user_login'] 	= $user->user_login;
					} else {
						throw new Exception( '<strong>' . __( 'Error', 'fitpress' ) . ':</strong> ' . __( 'A user could not be found with this email address.', 'fitpress' ) );
					}

				} else {
					$creds['user_login'] 	= $_POST['username'];
				}

				$creds['user_password'] = $_POST['password'];
				$creds['remember']      = isset( $_POST['rememberme'] );
				$secure_cookie          = is_ssl() ? true : false;
				$user                   = wp_signon( apply_filters( 'woocommerce_login_credentials', $creds ), $secure_cookie );

				if ( is_wp_error( $user ) ) {
					throw new Exception( $user->get_error_message() );
				} else {

					if ( ! empty( $_POST['redirect'] ) ) {
						$redirect = $_POST['redirect'];
					} elseif ( wp_get_referer() ) {
						$redirect = wp_get_referer();
					} else {
						$redirect = fp_get_page_permalink( 'account' );
					}

					// Feedback
					//wc_add_notice( sprintf( __( 'You are now logged in as <strong>%s</strong>', 'woocommerce' ), $user->display_name ) );

					wp_redirect( $redirect );
					exit;
				}

			} catch (Exception $e) {

				//wc_add_notice( apply_filters('login_errors', $e->getMessage() ), 'error' );

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
		$posted_fields = array( 'fp_reset_password', 'password_1', 'password_2', 'reset_key', 'reset_login', '_wpnonce' );

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
				//wc_add_notice( __( 'Please enter your password.', 'woocommerce' ), 'error' );
			}

			if ( $posted_fields[ 'password_1' ] !== $posted_fields[ 'password_2' ] ) {
				//wc_add_notice( __( 'Passwords do not match.', 'woocommerce' ), 'error' );
			}

			$errors = new WP_Error();

			do_action( 'validate_password_reset', $errors, $user );

			//wc_add_wp_error_notices( $errors );

			//if ( 0 === wc_notice_count( 'error' ) ) {
				self::reset_password( $user, $posted_fields['password_1'] );

				//do_action( 'woocommerce_customer_reset_password', $user );

				wp_redirect( add_query_arg( 'reset', 'true', remove_query_arg( array( 'key', 'login' ) ) ) );
				exit;
			//}
		}
	}

	/**
	 * Save the password/account details and redirect back to the my account page.
	 */
	public static function save_account_details() {

		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
			return;
		}

		if ( empty( $_POST[ 'action' ] ) || 'save_account_details' !== $_POST[ 'action' ] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_account_details' ) ) {
			return;
		}

		$update       = true;
		$errors       = new WP_Error();
		$user         = new stdClass();

		$user->ID     = (int) get_current_user_id();
		$current_user = get_user_by( 'id', $user->ID );

		if ( $user->ID <= 0 ) {
			return;
		}

		$account_first_name = ! empty( $_POST[ 'account_first_name' ] ) ? sanitize_text_field( $_POST[ 'account_first_name' ] ) : '';
		$account_last_name  = ! empty( $_POST[ 'account_last_name' ] ) ? sanitize_text_field( $_POST[ 'account_last_name' ] ) : '';
		$account_email      = ! empty( $_POST[ 'account_email' ] ) ? sanitize_email( $_POST[ 'account_email' ] ) : '';
		$pass_cur           = ! empty( $_POST[ 'password_current' ] ) ? $_POST[ 'password_current' ] : '';
		$pass1              = ! empty( $_POST[ 'password_1' ] ) ? $_POST[ 'password_1' ] : '';
		$pass2              = ! empty( $_POST[ 'password_2' ] ) ? $_POST[ 'password_2' ] : '';
		$save_pass          = true;

		$user->first_name   = $account_first_name;
		$user->last_name    = $account_last_name;
		$user->user_email   = $account_email;
		$user->display_name = $user->first_name;

		if ( empty( $account_first_name ) || empty( $account_last_name ) ) {
			//wc_add_notice( __( 'Please enter your name.', 'woocommerce' ), 'error' );
		}

		if ( empty( $account_email ) || ! is_email( $account_email ) ) {
			//wc_add_notice( __( 'Please provide a valid email address.', 'woocommerce' ), 'error' );
		} elseif ( email_exists( $account_email ) && $account_email !== $current_user->user_email ) {
			//wc_add_notice( __( 'This email address is already registered.', 'woocommerce' ), 'error' );
		}

		if ( ! empty( $pass1 ) && ! wp_check_password( $pass_cur, $current_user->user_pass, $current_user->ID ) ) {
			//wc_add_notice( __( 'Your current password is incorrect.', 'woocommerce' ), 'error' );
			$save_pass = false;
		}

		if ( ! empty( $pass_cur ) && empty( $pass1 ) && empty( $pass2 ) ) {
			//wc_add_notice( __( 'Please fill out all password fields.', 'woocommerce' ), 'error' );

			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && empty( $pass_cur ) ) {
			//wc_add_notice( __( 'Please enter your current password.', 'woocommerce' ), 'error' );

			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && empty( $pass2 ) ) {
			//wc_add_notice( __( 'Please re-enter your password.', 'woocommerce' ), 'error' );

			$save_pass = false;
		} elseif ( ! empty( $pass1 ) && $pass1 !== $pass2 ) {
			//wc_add_notice( __( 'Passwords do not match.', 'woocommerce' ), 'error' );

			$save_pass = false;
		}

		if ( $pass1 && $save_pass ) {
			$user->user_pass = $pass1;
		}

		if ( $errors->get_error_messages() ) {
			foreach ( $errors->get_error_messages() as $error ) {
				//wc_add_notice( $error, 'error' );
			}
		}

		//if ( wc_notice_count( 'error' ) === 0 ) {

			wp_update_user( $user ) ;

			//wc_add_notice( __( 'Account details changed successfully.', 'woocommerce' ) );

			wp_safe_redirect( fp_get_page_permalink( 'account' ) );
			exit;
		//}
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
