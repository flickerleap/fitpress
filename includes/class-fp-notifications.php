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

		add_action( 'fitpress_after_membership_save', array( $this, 'package_update_notification' ) );

	}

	public function send_daily_notifications() {

		$notifications = apply_filters( 'fitpress_daily_notifications', array() );

		if ( ! empty( $notifications ) ) :

			foreach ( $notifications as $notification ) :

				$fp_email = new FP_Email( array( 'template' => $notification['template'] ) );

				$fp_email->send_email( $notification['email'], $notification['subject'], array( 'header' => $notification['header'], 'message' => $notification['message'] ) );

			endforeach;

		endif;

	}

	public function send_hourly_notifications() {

		$notifications = apply_filters( 'fitpress_hourly_notifications', array() );

		if ( ! empty( $notifications ) ) :

			foreach ( $notifications as $notification ) :

				$fp_email = new FP_Email( array( 'template' => $notification['template'] ) );

				$fp_email->send_email( $notification['email'], $notification['subject'], array( 'header' => $notification['header'], 'message' => $notification['message'] ) );

			endforeach;

		endif;

	}

	public function send_notification( $notification = null ) {

		if ( $notification ) :

			$fp_email = new FP_Email( array( 'template' => $notification['template'] ) );

			$fp_email->send_email( $notification['email'], $notification['subject'], array( 'header' => $notification['header'], 'message' => $notification['message'] ) );

		endif;

	}

	public function package_update_notification( $membership ) {

		if ( $membership['old_package_id'] && $membership['old_package_id'] != $membership['package_id'] ) {

			$member_id = get_post_meta( $membership['membership_id'], '_fp_user_id', true );

			$user = get_userdata( $member_id );

			$message = '<p><strong>' . $user->display_name . '</strong> has updated their package.</p>';
			$message .= '<p>From: <strong>' . get_the_title( $membership['old_package_id'] ) . '</strong>';
			$message .= '<br /> To: <strong>' . get_the_title( $membership['package_id'] ) . '</strong></p>';
			$message .= '<p>Kind regards,<br />' . get_bloginfo( 'name' ) . '</p>';

			$notification = array(
				'template' => 'email/notification.php',
				'email' => get_bloginfo( 'admin_email' ),
				'subject' => 'Package change for ' . $user->display_name,
				'header' => 'Package change',
				'message' => $message,
			);

			$this->send_notification( $notification );
		}

	}
}

new FP_Notification();
