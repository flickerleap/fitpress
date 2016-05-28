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

	public static function install(){

		flush_rewrite_rules();

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

		$account_page = array(
			'post_type' => 'page',
			'post_title' => 'Account',
			'post_content' => '[fitpress_account]'
		);

		wp_update_post( $account_page );

	}

};
