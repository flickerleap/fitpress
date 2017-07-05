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
 * FP_Flash_Message Class.
 */
class FP_Flash_Message {

	public static function set( $message, $type = 'success' ) {

		if ( $message ):

			$_SESSION['fp_flash_message'][] = array(
				'type'    => $type,
				'message' => $message
			);

		else:

			unset( $_SESSION['fp_flash_message'] );

		endif;

	}

	public static function display() {

		if ( isset( $_SESSION['fp_flash_message'] ) ):

			$flash_message = '';

			foreach ( $_SESSION['fp_flash_message'] as $flash ):

				$flash_message .= '<div class="flash-message flash-message-' . $flash['type'] . '">';

				$flash_message .= $flash['message'];

				$flash_message .= '</div>';

			endforeach;

			unset( $_SESSION['fp_flash_message'] );

			echo $flash_message;

		endif;

	}

	public static function count( $type ) {

		$type_count = 0;

		if ( isset( $_SESSION['fp_flash_message'] ) ):

			foreach ( $_SESSION['fp_flash_message'] as $flash ):

				if ( $flash['type'] == $type ) {
					$type_count ++;
				}

			endforeach;

		endif;

		return $type_count;

	}

}
