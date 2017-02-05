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
class FP_Install {

	/**
	 * Install required FitPress pages
	 */
	public static function install() {

		$holidays = array(
			'1 January' => 'New Year\'s Day',
			'21 March' => 'Human Rights Day',
			'27 April' => 'Freedom Day',
			'1 May' => 'Workers Day',
			'16 June' => 'Youth Day',
			'9 August' => 'National Women\'s Day',
			'24 September' => 'Heritage Day',
			'16 December' => 'Day of Reconciliation',
			'25 December' => 'Christmas Day',
			'26 December' => 'Day of Goodwill',
		);

		update_option( 'fitpress_holidays', $holidays );

		if ( ! get_page_by_title( 'Account' ) ) :

			$account_page = array(
				'post_type' => 'page',
				'post_title' => 'Account',
				'post_status' => 'publish',
				'post_content' => '[fitpress_account]',
			);

			wp_insert_post( $account_page );

		endif;

		add_action( 'init', array( 'FP_Install', 'flush_rewrite_rules' ) );

		if ( ! file_exists( FP_PLUGIN_DIR . 'export' ) ) :
			mkdir( FP_PLUGIN_DIR . 'export', 0755, true );
		endif;

	}

	/**
	 * Flush permalinks once FP is installed
	 */
	public static function flush_rewrite_rules() {

		flush_rewrite_rules();

	}

}
