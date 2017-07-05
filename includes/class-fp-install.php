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
 * FP_Install Class.
 */
class FP_Install {

	/**
	 * Install required FitPress pages
	 */
	public static function install() {

		$holidays = array(
			'1 January'    => 'New Year\'s Day',
			'21 March'     => 'Human Rights Day',
			'27 April'     => 'Freedom Day',
			'1 May'        => 'Workers Day',
			'16 June'      => 'Youth Day',
			'9 August'     => 'National Women\'s Day',
			'24 September' => 'Heritage Day',
			'16 December'  => 'Day of Reconciliation',
			'25 December'  => 'Christmas Day',
			'26 December'  => 'Day of Goodwill',
		);

		update_option( 'fitpress_holidays', $holidays );

		if ( ! get_page_by_title( 'Account' ) ) :

			$account_page = array(
				'post_type'    => 'page',
				'post_title'   => 'Account',
				'post_status'  => 'publish',
				'post_content' => '[fitpress_account]',
			);

			wp_insert_post( $account_page );

		endif;

		if ( ! get_page_by_title( 'Sign Up' ) ) :

			$signup_page = array(
				'post_type'    => 'page',
				'post_title'   => 'Sign Up',
				'post_status'  => 'publish',
				'post_content' => '[fitpress_signup]',
			);

			wp_insert_post( $signup_page );

		endif;

		add_action( 'init', array( 'FP_Install', 'flush_rewrite_rules' ) );

		if ( ! wp_get_schedule( 'fitpress_daily_cron' ) ) :
			$start = strtotime( 'tomorrow' );
			wp_schedule_event( $start, 'daily', 'fitpress_daily_cron' );
		endif;

		if ( ! wp_get_schedule( 'fitpress_hourly_cron' ) ) :
			$start = strtotime( 'tomorrow' );
			wp_schedule_event( $start, 'hourly', 'fitpress_hourly_cron' );
		endif;

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


	public static function maybe_update() {

		if ( isset( $_GET['fp_update'] ) ) :

			$version = get_option( 'fitpress_version', '1.0.0' );

			if ( version_compare( $version, FP_VERSION ) ) :

				if ( version_compare( $version, '1.1.0' ) ) :

					$args = array(
						'fields' => 'ID',
					);

					$member_query = new WP_User_Query( $args );

					$users = $member_query->get_results();

					foreach ( $users as $user_id ) :

						$old_membership = FP_Membership::get_user_membership( $user_id );

						if ( ! $old_membership ) :

							$package_id = get_user_meta( $user_id, 'fitpress_membership_id', true );

							if ( $package_id && $package_id !== 0 ) :

								$credits               = get_user_meta( $user_id, 'fitpress_credits', true );
								$membership_start_date = get_user_meta( $user_id, 'fitpress_membership_date', true );
								$renewal_date          = get_user_meta( $user_id, 'fitpress_next_invoice_date', true );
								$membership_status     = get_user_meta( $user_id, 'fitpress_membership_status', true );

								$membership_post = array(
									'post_title'   => 'Membership for user id: ' . $user_id,
									'post_content' => '',
									'post_status'  => 'publish',
									'post_type'    => 'fp_member',
								);

								$membership_id = wp_insert_post( $membership_post );

								update_post_meta( $membership_id, '_fp_user_id', $user_id );
								update_post_meta( $membership_id, '_fp_membership_status', $membership_status );
								update_post_meta( $membership_id, '_fp_package_id', $package_id );
								update_post_meta( $membership_id, '_fp_credits', $credits );
								update_post_meta( $membership_id, '_fp_membership_start_date', $membership_start_date );
								if ( $renewal_date && 0 !== $renewal_date ) :
									update_post_meta( $membership_id, '_fp_renewal_date', $renewal_date );
								endif;

							endif;

						endif;

					endforeach;

				endif;

				if ( version_compare( $version, '1.2' ) ) :

					if ( ! wp_get_schedule( 'fitpress_daily_cron' ) ) :
						$start = strtotime( 'tomorrow' );
						wp_schedule_event( $start, 'daily', 'fitpress_daily_cron' );
					endif;

				endif;

				if ( version_compare( $version, '1.3' ) ) :


					if ( ! wp_get_schedule( 'fitpress_hourly_cron' ) ) :
						$start = strtotime( 'tomorrow' );
						wp_schedule_event( $start, 'hourly', 'fitpress_hourly_cron' );
					endif;
				endif;

				self::flush_rewrite_rules();

				update_option( 'fitpress_version', FP_VERSION );

				wp_redirect( remove_query_arg( 'fp_update' ) );

			endif;

		endif;

	}

}
