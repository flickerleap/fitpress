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
<h2>Confirmed</h2>
<p>Your membership has been confirmed. Head to your <a href="<?php echo fp_get_page_permalink( 'account' );?>">account</a> to book a session.</p>
