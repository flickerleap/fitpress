<?php
/**
 * Notifications
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
 * FP_Notification Class.
 */
class FP_Notification {

	/**
	 * Hook in methods.
	 */
	public function __construct() {

		add_action( 'fitpress_daily_cron', array( $this, 'send_daily_notifications' ) );
		add_action( 'fitpress_hourly_cron', array( $this, 'send_hourly_notifications' ) );

	}

	public function send_daily_notifications() {

		$notifications = apply_filters( 'fitpress_daily_notifications', array() );

		if ( ! empty( $notifications ) ) :

			foreach ( $notifications as $notification ) :

				$fp_email = new FP_Email( array( 'template' => $notification['template'] ) );

				$fp_email->send_email( $notification['email'], $notification['subject'], array( 'header'  => $notification['header'],
				                                                                                'message' => $notification['message']
				) );

			endforeach;

		endif;

	}

	public function send_hourly_notifications() {

		$notifications = apply_filters( 'fitpress_hourly_notifications', array() );

		if ( ! empty( $notifications ) ) :

			foreach ( $notifications as $notification ) :

				$fp_email = new FP_Email( array( 'template' => $notification['template'] ) );

				$fp_email->send_email( $notification['email'], $notification['subject'], array( 'header'  => $notification['header'],
				                                                                                'message' => $notification['message']
				) );

			endforeach;

		endif;

	}
}

new FP_Notification();
