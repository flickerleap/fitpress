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
} 

fp_display_flash_message();
?>

<p class="myaccount_user">
	<?php
	printf(
		__( 'Hello <strong>%1$s</strong> (not %1$s? <a href="%2$s">Sign out</a>).', 'fitpress' ) . ' ',
		$current_user->display_name,
		fp_get_endpoint_url( 'member-logout', '', fp_get_page_permalink( 'account' ) )
	);
	?>
</p>

<?php do_action( 'fitpress_before_account_sessions' );?>

<h2>Currently Booked</h2>

<?php if( !empty( $booked_sessions ) ):?>

	<?php foreach( $booked_sessions as $session ):?>
		<ul>
			<li>
				<?php echo $session['class'] . ' on ' . $session['date'] . ' from ' . $session['start_time'] . ' to ' . $session['end_time'] . ') ' . $session['action'];  ?>
			</li>
		</ul>
	<?php endforeach;?>

<?php else:?>
	<p>
	<?php printf(
		__( 'It looks like you have not booked a class yet. Why not <a href="%1$s">book one</a> now?', 'fitpress' ) . ' ',
		fp_book_url( )
	);?>
	</p>
<?php endif;?>

<?php do_action( 'fitpress_after_account_sessions' );?>

