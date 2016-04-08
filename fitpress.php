<?php
/**
 * @package FitPress
 */
/*
Plugin Name: FitPress
Plugin URI: http://fitpress.com/
Description: FitPress is the first of its kind for managing CrossFit boxes and fitness gyms. It allows for member management and class bookings.
Version: 1.0
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
 * @version	1.0
 */
class FitPress{

	/**
	 * @var string
	 */
	public $version = '1.0';

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
		register_activation_hook( __FILE__, array( 'WC_Install', 'install' ) );
		add_action( 'after_setup_theme', array( $this, 'setup_environment' ) );
	}

	/**
	 * Define FP Constants
	 */
	private function define_constants() {

		$upload_dir = wp_upload_dir();

		$this->define( 'FP_PLUGIN_FILE', __FILE__ );
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
		include_once( 'includes/class-fp-post-types.php' );
	}

	/**
	 * Ensure theme and server variable compatibility
	 */
	public function setup_environment() {

		$this->define( 'FP_TEMPLATE_PATH', $this->template_path() );

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