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
class FP_Booking_Notification {

	/**
	 * Hook in methods.
	 */
	public function __construct() {

		add_filter( 'fitpress_hourly_notifications', array( $this, 'send_membership_bookings' ) );

	}

	public function send_membership_bookings( $notifications ) {

		if ( 3 == (int) current_time( 'g' ) ) :

			$session_bookings = FP_Booking::get_day_bookings();

			if ( ! empty( $session_bookings ) ) :

				$message = '';

				$message .= '<p>Hi,</p>';
				$message .= '<p>Here are the bookings for today:</p>';

				foreach ( $session_bookings as $session => $bookings ) :
					if ( ! empty( $bookings ) ) :
						$message .= '<h3>' . $session . '</h3>';
						$message .= '<p>';
						foreach ( $bookings as $booking ) :
							$message .= $booking['user']->first_name . ' ' . $booking['user']->last_name . '<br />';
						endforeach;
						$message .= '</p>';
					endif;
				endforeach;

			else :

				$message .= '<p>Hi,</p>';
				$message .= '<p>There are no bookings for today.</p>';

			endif;

			$notifications[] = array(
				'template' => 'email/notification.php',
				'email' => get_bloginfo( 'admin_email' ),
				'subject' => 'Today\'s Bookings',
				'header' => 'Today\'s Bookings',
				'message' => $message,
			);

		endif;

		return $notifications;

	}
}

new FP_Booking_Notification();
