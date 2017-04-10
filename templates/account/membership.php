<?php
/**
 * Edit account form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.2.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

fp_display_flash_message();
?>
<p>
Current Membership:
<?php
if ( 'None' == $membership ) :
	echo $membership;
else :
	echo $membership['name'];
endif;
?>
</p>

<?php do_action( 'fitpress_after_membership', $member_id );?>
