<?php
/**
 * @package FitPress
 */
/*
Plugin Name: FitPress
Plugin URI: http://fitpress.co.za
Description: FitPress is the first of its kind for managing CrossFit boxes and fitness gyms. It allows for member management and class bookings.
Version: 1.3.0
Author: Digital Leap
Author URI: http://digitalleap.co.za/wordpress/
License: GPLv2 or later
Text Domain: fitpress
*/

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

if ( ! class_exists( 'FitPress' ) ) :

	/**
	 * Main FitPress Class
	 *
	 * @class FitPress
	 * @version 1.0
	 */
	class FitPress {

		/**
		 * @var string
		 */
		public $version = '1.3.0';

		public $query = '';

		public $flash_message;

		/**
		 * @var FitPress The single instance of the class
		 * @since 1.0
		 */
		protected static $_instance = null;

		/**
		 * Main FitPress Instance
		 *
		 * Ensures only one instance of FitPress is loaded or can be loaded.
		 *
		 * @since 1.0
		 * @static
		 * @see WC()
		 * @return FitPress - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 * @since 1.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'fitpress' ), '1.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 * @since 1.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'fitpress' ), '1.0' );
		}

		/**
		 * FitPress Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->includes();
			$this->init_hooks();

			do_action( 'fitpress_loaded' );
		}

		/**
		 * Hook into actions and filters
		 * @since  1.0
		 */
		private function init_hooks() {

			register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
			register_activation_hook( __FILE__, array( 'FP_Install', 'install' ) );

			add_action( 'init', array( 'FP_Install', 'maybe_update' ) );

			add_action( 'after_setup_theme', array( $this, 'setup_environment' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

			add_action( 'init', array( $this, 'maybe_run_install' ) );

		}

		/**
		 * Hook into actions and filters
		 * @since  1.0
		 */
		public function enqueue_admin_scripts() {

			wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js' );
			wp_enqueue_script( 'fitpress-admin-script', FP_PLUGIN_URL . '/assets/js/fitpress-admin.js', array( 'jquery' ) );

			wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
			wp_enqueue_style( 'fitpress-admin-style', FP_PLUGIN_URL . '/assets/css/fitpress-admin.css' );

		}

		/**
		 * Hook into actions and filters
		 * @since  1.0
		 */
		public function enqueue_frontend_scripts() {

			wp_enqueue_script( 'fitpress-script', FP_PLUGIN_URL . '/assets/js/fitpress.js', array( 'jquery' ) );

			wp_localize_script( 'fitpress-script', 'fp_booking', array( 'ajax_url' => self::ajax_url() ) );

			wp_enqueue_style( 'fitpress-style', FP_PLUGIN_URL . '/assets/css/fitpress.css' );
			wp_enqueue_style( 'font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css' );

		}

		/**
		 * Define FP Constants
		 */
		private function define_constants() {

			$upload_dir = wp_upload_dir();

			$this->define( 'FP_PLUGIN_FILE', __FILE__ );
			$this->define( 'FP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			$this->define( 'FP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			$this->define( 'FP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'FP_VERSION', $this->version );

		}

		/**
		 * Define constant if not already set
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {
			include_once( 'includes/fp-utilities.php' );
			include_once( 'includes/class-fp-install.php' );
			$this->query = include_once( 'includes/class-fp-account.php' );
			include_once( 'includes/class-fp-flash-message.php' );
			include_once( 'includes/class-fp-member.php' );
			include_once( 'includes/class-fp-membership.php' );
			include_once( 'includes/class-fp-membership-status.php' );
			include_once( 'includes/class-fp-credits.php' );
			include_once( 'includes/class-fp-classes.php' );
			include_once( 'includes/class-fp-session.php' );
			include_once( 'includes/class-fp-booking.php' );
			include_once( 'includes/class-fp-email.php' );
			include_once( 'includes/class-fp-notifications.php' );

			include_once( 'includes/class-fp-frontend.php' );

			if ( $this->is_request( 'admin' ) ) :

				include_once( 'includes/class-fp-admin.php' );

			endif;

			if ( $this->is_request( 'frontend' ) ) :

			endif;

			include_once( 'includes/notifications/class-fp-notifications-membership-expire.php' );
			include_once( 'includes/notifications/class-fp-notifications-bookings.php' );

		}

		/**
		 * What type of request is this?
		 *
		 * @param  string $type admin, ajax, cron or frontend.
		 * @return bool
		 */
		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin' :
					return is_admin();
				case 'ajax' :
					return defined( 'DOING_AJAX' );
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}
		}

		/**
		 * Ensure theme and server variable compatibility
		 */
		public function setup_environment() {

			$this->define( 'FP_TEMPLATE_PATH', $this->template_path() );

			if ( ! current_user_can( 'manage_options' ) ) {
				show_admin_bar( false );
			}

		}

		public function maybe_run_install() {
			if ( isset( $_GET['fp-setup'] ) ) :
				FP_Install::install();
				wp_redirect( wp_get_referer() );
			endif;
		}

		/**
		 * Get the plugin url.
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Get the template path.
		 * @return string
		 */
		public function template_path() {
			return apply_filters( 'fitpress_template_path', 'fitpress/' );
		}

		/**
		 * Get Ajax URL.
		 * @return string
		 */
		public function ajax_url() {
			return admin_url( 'admin-ajax.php', 'relative' );
		}

	}

endif;

/**
 * Returns the main instance of FP to prevent the need to use globals.
 *
 * @since  1.0
 * @return FitPress
 */
function FP() {
	return FitPress::instance();
}

// Global for backwards compatibility.
$GLOBALS['fitpress'] = FP();
