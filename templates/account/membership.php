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
<h3>Membership</h3>
<?php if ( $membership ) :?>
	<?php do_action( 'fitpress_before_membership', $membership_id );?>
	<p>
		Package: <?php echo $membership['name'];?><br />
		Status: <?php echo $membership['status'];?><br />
		Term: <?php echo $membership['term'];?><br />
		Amount:  R <?php echo $membership['price'];?>
		<?php if ( 'Once Off' != $membership['term'] ) :?>
			<br />Renewal Date: <?php echo date( 'j F Y', $membership['renewal_date'] );?>
		<?php endif;?>
	</p>
	<?php if ( 'Once Off' != $membership['term'] ) :?>
		<h3>Update Membership</h3>
		<form method="post">
			<p class="form-row">
				<label for="package_id">Change Package:</label>
				<select name="package_id">
					<?php foreach ( $packages as $id => $package ) :?>
						<?php if ( $id != $membership['package_id'] && 'Once Off' != $package['term'] ) :?>
							<option value="<?php echo $id;?>"><?php echo $package['name'];?></option>
						<?php endif;?>
					<?php endforeach;?>
				</select>
			</p>

			<p class="form-row form-row-submit">
				<?php wp_nonce_field( 'update_membership' ); ?>
				<input type="submit" class="btn button" name="update_membership" value="<?php _e( 'Update Membership', 'fitpress' ); ?>" />
				<input type="hidden" name="action" value="update_membership" />
			</p>
		</form>
	<?php else : ?>
		<h3>Update Membership</h3>
		<a href="<?php echo fp_get_page_permalink( 'sign-up' );?>?membership_id=<?php echo $membership['package_id'];?>" class="btn button">Buy More Credits</a>
		<form action="<?php echo fp_get_page_permalink( 'sign-up' );?>" method="get">
			<p class="form-row">
				<label for="membership_id">Upgrade Package:</label>
				<select name="membership_id">
					<?php foreach ( $packages as $id => $package ) : ?>
						<?php if ( $id != $membership['package_id'] && 'Once Off' != $package['term'] ) :?>
							<option value="<?php echo $id;?>"><?php echo $package['name'];?></option>
						<?php endif;?>
					<?php endforeach;?>
				</select>
			</p>

			<p class="form-row form-row-submit">
				<input type="submit" class="btn button" name="update_membership" value="<?php _e( 'Upgrade Membership', 'fitpress' ); ?>" />
			</p>
		</form>
	<?php endif;?>
	<?php do_action( 'fitpress_after_membership', $membership_id );?>
<?php else : ?>
	<p>You currently don't have a membership. Buy one now?</p>
	<a href="<?php echo fp_get_page_permalink( 'sign-up' );?>" class="btn button">Buy a Membership</a>
<?php endif;?>
