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
class FP_Credit {

	/**
	 * Hook in methods.
	 */
    public function __construct(){

		if( !wp_get_schedule('maybe_reset_credits_hook') ):
			$start = strtotime( 'tomorrow' );
			wp_schedule_event( $start, 'daily', 'maybe_reset_credits_hook' );
		endif;

		add_action('maybe_reset_credits_hook', array( $this, 'maybe_reset_credits'));

	}

	/**
	* Adds credits to user if subscription is successfully activated
	*
	* @param int $user_id A user ID for the user that the subscription was activated for.
	* @param mixed $subscription_key The key referring to the activated subscription
	* @version 1.0
	* @since 0.1
	*/
	public static function maybe_reset_credits( $force = false ) {

		if( date('j') == 1 || $force ):

			$members = FP_Membership::get_members( );

			$memberships = FP_Membership::get_memberships( );

			// Check for results
			if (!empty($members)) {

			    foreach ( $members as $member_id ){
			        // get all the user's data
			        $membership_id = get_user_meta( $member_id, 'fitpress_membership_id', true );
			        $old_credits = get_user_meta( $member_id, 'fitpress_credits', true );

			        $credits = $memberships[ $membership_id ]['credits'];

					update_user_meta( $member_id, 'fitpress_credits', $credits, $old_credits);

			    }

			}

		endif;

	}

	/**
	* Adds credits to user if subscription is successfully activated
	*
	* @param int $user_id A user ID for the user that the subscription was activated for.
	* @param mixed $subscription_key The key referring to the activated subscription
	* @version 1.0
	* @since 0.1
	*/
	public static function update_member_credits( $new_membership_id, $current_membership_id, $current_credits ) {

		if( $new_membership_id == 0 )
			return $current_credits;

		if( !$current_membership_id ):
			$current_credits = 0;
			$membership_details = FP_Membership::get_membership( array( $new_membership_id ) );
		else:
			$membership_details = FP_Membership::get_membership( array( $new_membership_id, $current_membership_id ) );
		endif;

		if( $membership_details ):

			if( !$current_membership_id )
				return $membership_details[ $new_membership_id ]['credits'];

			if( $membership_details[ $new_membership_id ]['credits'] <= $membership_details[ $current_membership_id ]['credits'] )
				return $current_credits;
			
			$credits_difference = $membership_details[ $new_membership_id ]['credits'] - $membership_details[ $current_membership_id ]['credits'];

			return $current_credits + $credits_difference;

		else:

			return $current_credits;

		endif;

	}

	public static function modify_credits( $change, $member_id ){

		$current_credits = get_user_meta( $member_id, 'fitpress_credits', true );

		$new_credits = $current_credits + $change;

		update_user_meta( $member_id, 'fitpress_credits', $new_credits, $current_credits );

	}

}

/**
 * Extension main function
 */
function __fp_credit_main() {
    new FP_Credit();
}

// Initialize plugin when plugins are loaded
add_action( 'plugins_loaded', '__fp_credit_main' );
