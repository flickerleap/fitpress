<?php
/**
 * Login Form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.2.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

fp_display_flash_message();
?>

<h2><?php _e( 'Login', 'fitpress' ); ?></h2>

<form method="post" class="login">

	<p class="form-row form-row-wide">
		<label for="username"><?php _e( 'Username or email address', 'fitpress' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="username" id="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( $_POST['username'] ) : ''; ?>" />
	</p>
	<p class="form-row form-row-wide">
		<label for="password"><?php _e( 'Password', 'fitpress' ); ?> <span class="required">*</span></label>
		<input class="input-text" type="password" name="password" id="password" />
	</p>

	<p class="form-row">
		<?php wp_nonce_field( 'fitpress-login' ); ?>
		<?php if ( isset( $_GET['redirect_to'] ) && ! empty( $_GET['redirect_to'] ) || isset( $_POST['redirect_to'] ) && ! empty( $_POST['redirect_to'] ) ) :?>
			<input name="redirect_to" value="<?php echo esc_attr( isset( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : $_GET['redirect_to'] ); ?>" hidden />
		<?php endif;?>
		<input type="submit" class="btn" name="login" value="<?php _e( 'Login', 'fitpress' ); ?>" />
		<label for="rememberme" class="inline">
			<input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e( 'Remember me', 'fitpress' ); ?>
		</label>
	</p>
	<p class="lost_password">
		<a href="<?php echo esc_url( fp_lostpassword_url() ); ?>"><?php _e( 'Lost your password?', 'fitpress' ); ?></a>
	</p>

</form>
