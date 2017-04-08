<?php
/**
 * Sign Up page
 *
 * @author 		FitPress
 * @package 	FitPress/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

fp_display_flash_message();
?>

<form method="post">

	<h3>Member Details</h3>

	<p class="form-row form-row-wide">
		<label for="membership_id"><?php _e( 'Membership', 'fitpress' ); ?> <span class="required">*</span></label>
		<select name="membership_id" id="membership_id">
			<?php $passed_membership_id = isset( $_GET['membership_id'] ) ? $_GET['membership_id'] : false;?>
			<?php foreach( $memberships as $membership_id => $membership ) : ?>
				<option value="<?php echo $membership_id;?>" <?php selected( $membership_id, $passed_membership_id, true ); ?>>
					<?php echo $membership['name'];?>
				</option>
			<?php endforeach;?>
		</select>
	</p>

	<p class="form-row form-row-first">
		<label for="account_first_name"><?php _e( 'First name', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="account_first_name" id="account_first_name" value="<?php echo esc_attr( isset( $_POST['account_first_name'] ) ? $_POST['account_first_name'] : '' ); ?>" />
	</p>
	<p class="form-row form-row-last">
		<label for="account_last_name"><?php _e( 'Last name', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="account_last_name" id="account_last_name" value="<?php echo esc_attr( isset( $_POST['account_last_name'] ) ? $_POST['account_last_name'] : '' ); ?>" />
	</p>
	<div class="clear"></div>

	<p class="form-row form-row-first">
		<label for="account_email"><?php _e( 'Email address', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="email" class="input-text" name="account_email" id="account_email" value="<?php echo esc_attr( isset( $_POST['account_email'] ) ? $_POST['account_email'] : '' ); ?>" />
	</p>

	<p class="form-row form-row-last">
		<label for="contact_number"><?php _e( 'Contact Number', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="tel" class="input-text" name="contact_number" id="contact_number" value="<?php echo esc_attr( isset( $_POST['contact_number'] ) ? $_POST['contact_number'] : '' ); ?>" />
	</p>

	<p class="form-row form-row-first">
		<label for="emergency_contact_name"><?php _e( 'Emergency Contact Name', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="emergency_contact_name" id="emergency_contact_name" value="<?php echo esc_attr( isset( $_POST['emergency_contact_name'] ) ? $_POST['emergency_contact_name'] : '' ); ?>" />
	</p>
	<p class="form-row form-row-last">
		<label for="emergency_contact_number"><?php _e( 'Emergency Contact Number', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="emergency_contact_number" id="emergency_contact_number" value="<?php echo esc_attr( isset( $_POST['emergency_contact_number'] ) ? $_POST['emergency_contact_number'] : '' ); ?>" />
	</p>
	<div class="clearfix"></div>

	<h3>Log In Details</h3>

	<p class="form-row form-row-wide">
		<label for="account_username"><?php _e( 'Username', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="account_username" id="account_username" value="<?php echo esc_attr( isset( $_POST['account_username'] ) ? $_POST['account_username'] : '' ); ?>" />
	</p>

	<p class="form-row form-row-first">
		<label for="password_1"><?php _e( 'Password', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="password" class="input-text" name="password_1" id="password_1" />
	</p>
	<p class="form-row form-row-last">
		<label for="password_2"><?php _e( 'Confirm Password', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="password" class="input-text" name="password_2" id="password_2" />
	</p>
	<div class="clearfix"></div>

	<p class="form-row form-row-submit">
		<?php wp_nonce_field( 'signup_account' ); ?>
		<input type="submit" class="btn button" name="signup_account" value="<?php _e( 'Sign Up', 'fitpress' ); ?>" />
		<input type="hidden" name="action" value="signup_account" />
	</p>

</form>
