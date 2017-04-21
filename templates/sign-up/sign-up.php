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

if ( is_user_logged_in() ) :
	$first_name = isset( $_POST['account_first_name'] ) ? $_POST['account_first_name'] : $current_user->first_name;
	$last_name = isset( $_POST['account_last_name'] ) ? $_POST['account_last_name'] : $current_user->last_name;
	$account_email = isset( $_POST['account_email'] ) ? $_POST['account_email'] : $current_user->user_email;
	$contact_number = isset( $_POST['contact_number'] ) ? $_POST['contact_number'] : $current_user->contact_number;
	$emergency_contact_name = isset( $_POST['emergency_contact_name'] ) ? $_POST['emergency_contact_name'] : $current_user->emergency_contact_name;
	$emergency_contact_number = isset( $_POST['emergency_contact_number'] ) ? $_POST['emergency_contact_number'] : $current_user->emergency_contact_number;
else :
	$first_name = isset( $_POST['account_first_name'] ) ? $_POST['account_first_name'] : '';
	$last_name = isset( $_POST['account_last_name'] ) ? $_POST['account_last_name'] : '';
	$account_email = isset( $_POST['account_email'] ) ? $_POST['account_email'] : '';
	$contact_number = isset( $_POST['contact_number'] ) ? $_POST['contact_number'] : '';
	$emergency_contact_name = isset( $_POST['emergency_contact_name'] ) ? $_POST['emergency_contact_name'] : '';
	$emergency_contact_number = isset( $_POST['emergency_contact_number'] ) ? $_POST['emergency_contact_number'] : '';
endif;
?>

<?php if ( ! is_user_logged_in() ) : ?>
	<div class="flash-message flash-message-info">
		If you already have an account, <a href="<?php echo fp_get_page_permalink( 'account' );?>?redirect_to=<?php echo urlencode( fp_get_page_permalink( 'sign-up' ) ); ?>">click here</a> to log in.
	</div>
<?php endif;?>

<form method="post">

	<h3>Member Details</h3>

	<p class="form-row form-row-wide">
		<label for="package_id"><?php _e( 'Membership', 'fitpress' ); ?> <span class="required">*</span></label>
		<select name="package_id" id="membership_id">
			<?php $passed_membership_id = isset( $_GET['membership_id'] ) ? $_GET['membership_id'] : false;?>
			<?php foreach( $packages as $package_id => $package ) : ?>
				<option value="<?php echo $package_id;?>" <?php selected( $package_id, $passed_membership_id, true ); ?>>
					<?php echo $package['name'];?>
				</option>
			<?php endforeach;?>
		</select>
	</p>

	<p class="form-row form-row-first">
		<label for="account_first_name"><?php _e( 'First name', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="account_first_name" id="account_first_name" value="<?php echo esc_attr( $first_name ); ?>" />
	</p>
	<p class="form-row form-row-last">
		<label for="account_last_name"><?php _e( 'Last name', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="account_last_name" id="account_last_name" value="<?php echo esc_attr( $last_name ); ?>" />
	</p>
	<div class="clear"></div>

	<p class="form-row form-row-first">
		<label for="account_email"><?php _e( 'Email address', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="email" class="input-text" name="account_email" id="account_email" value="<?php echo esc_attr( $account_email ); ?>" />
	</p>

	<p class="form-row form-row-last">
		<label for="contact_number"><?php _e( 'Contact Number', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="tel" class="input-text" name="contact_number" id="contact_number" value="<?php echo esc_attr( $contact_number ); ?>" />
	</p>

	<p class="form-row form-row-first">
		<label for="emergency_contact_name"><?php _e( 'Emergency Contact Name', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="emergency_contact_name" id="emergency_contact_name" value="<?php echo esc_attr( $emergency_contact_name ); ?>" />
	</p>
	<p class="form-row form-row-last">
		<label for="emergency_contact_number"><?php _e( 'Emergency Contact Number', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="emergency_contact_number" id="emergency_contact_number" value="<?php echo esc_attr( $emergency_contact_number ); ?>" />
	</p>
	<div class="clearfix"></div>

	<?php if ( ! is_user_logged_in() ) : ?>

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

	<?php endif;?>

	<p class="form-row form-row-submit">
		<?php wp_nonce_field( 'signup_account' ); ?>
		<input type="submit" class="btn button" name="signup_account" value="<?php _e( 'Sign Up', 'fitpress' ); ?>" />
		<input type="hidden" name="action" value="signup_account" />
	</p>

</form>
