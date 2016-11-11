<?php
/**
 * My Account page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>

<?php global $wp;?>

<ul class="account-menu">
	<li<?php echo ( !isset( $wp->query_vars['book'] ) && !isset( $wp->query_vars['update-account'] ) ) ? ' class="active"': '';?>><a href="<?php echo fp_get_page_permalink( 'account' );?>">Account Dashboard</a></li>
	<li<?php echo ( isset( $wp->query_vars['book'] ) ) ? ' class="active"': '';?>><a href="<?php echo fp_book_url( 'account' );?>">Book Sessions</a></li>
	<li<?php echo (  isset( $wp->query_vars['update-account'] ) ) ? ' class="active"': '';?>><a href="<?php echo fp_customer_edit_account_url( );?>">Update Account</a></li>
	<li><a href="<?php echo fp_get_endpoint_url( 'member-logout' );?>">Logout</a></li>
</ul>
