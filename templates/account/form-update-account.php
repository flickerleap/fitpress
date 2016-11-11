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

<form action="" method="post">

	<p class="form-row form-row-first">
		<label for="account_first_name"><?php _e( 'First name', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="account_first_name" id="account_first_name" value="<?php echo esc_attr( isset( $_POST['account_first_name'] ) ? $_POST['account_first_name'] : $user->first_name ); ?>" />
	</p>
	<p class="form-row form-row-last">
		<label for="account_last_name"><?php _e( 'Last name', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="account_last_name" id="account_last_name" value="<?php echo esc_attr( isset( $_POST['account_last_name'] ) ? $_POST['account_last_name'] : $user->last_name ); ?>" />
	</p>
	<div class="clear"></div>

	<p class="form-row form-row-wide">
		<label for="account_email"><?php _e( 'Email address', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="email" class="input-text" name="account_email" id="account_email" value="<?php echo esc_attr( isset( $_POST['account_email'] ) ? $_POST['account_email'] : $user->user_email ); ?>" />
	</p>

	<p class="form-row form-row-wide">
		<label for="contact_number"><?php _e( 'Contact Number', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="tel" class="input-text" name="contact_number" id="contact_number" value="<?php echo esc_attr( isset( $_POST['contact_number'] ) ? $_POST['contact_number'] : $user->contact_number ); ?>" />
	</p>

	<p class="form-row form-row-first">
		<label for="emergency_contact_name"><?php _e( 'Emergency Contact Name', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="emergency_contact_name" id="emergency_contact_name" value="<?php echo esc_attr( isset( $_POST['emergency_contact_name'] ) ? $_POST['emergency_contact_name'] : $user->emergency_contact_name ); ?>" />
	</p>
	<p class="form-row form-row-last">
		<label for="emergency_contact_number"><?php _e( 'Emergency Contact Number', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="emergency_contact_number" id="emergency_contact_number" value="<?php echo esc_attr( isset( $_POST['emergency_contact_number'] ) ? $_POST['emergency_contact_number'] : $user->emergency_contact_number ); ?>" />
	</p>
	<div class="clear"></div>

	<fieldset>
		<legend><?php _e( 'Password Change', 'fitpress' ); ?></legend>

		<p class="form-row form-row-wide">
			<label for="password_current"><?php _e( 'Current Password (leave blank to leave unchanged)', 'fitpress' ); ?></label>
			<input type="password" class="input-text" name="password_current" id="password_current" />
		</p>
		<p class="form-row form-row-wide">
			<label for="password_1"><?php _e( 'New Password (leave blank to leave unchanged)', 'fitpress' ); ?></label>
			<input type="password" class="input-text" name="password_1" id="password_1" />
		</p>
		<p class="form-row form-row-wide">
			<label for="password_2"><?php _e( 'Confirm New Password', 'fitpress' ); ?></label>
			<input type="password" class="input-text" name="password_2" id="password_2" />
		</p>
	</fieldset>
	<div class="clear"></div>

	<p>
		<?php wp_nonce_field( 'save_account_details' ); ?>
		<input type="submit" class="btn" name="save_account_details" value="<?php _e( 'Save changes', 'fitpress' ); ?>" />
		<input type="hidden" name="action" value="save_account_details" />
	</p>

</form>
