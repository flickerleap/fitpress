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
 * Class FP_Admin
 */
class FP_Admin {

	protected $settings = null;
	protected $email_settings = null;

	/**
	 * Hook in methods.
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'fitpress_dashboard' ), 5 );

		//Setting up columns.
		add_filter( 'manage_users_columns', array( $this, 'user_column_header' ), 9, 1 );
		add_action( 'manage_users_custom_column', array( $this, 'user_column_data' ), 15, 3 );
		add_filter( 'manage_users_sortable_columns', array( $this, 'user_column_sortable' ) );
		add_action( 'pre_get_users', array( $this, 'sort_by_membership' ) );

		add_action( 'admin_init', array( $this, 'init_settings' ) );

	}

	public function fitpress_dashboard() {

		//add an item to the menu
		add_menu_page(
			'FitPress Dashboard',
			'FitPress',
			'manage_options',
			'fitpress',
			array( $this, 'fitpress_dashboard_render' ),
			'dashicons-heart',
			'55.77'
		);

		//add an item to the menu
		add_submenu_page(
			'fitpress',
			'FitPress Settings',
			'Settings',
			'manage_options',
			'fp_settings',
			array( $this, 'fitpress_settings_render' )
		);

	}

	public function fitpress_dashboard_render() {

		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'todays-bookings';

		$tabs = array(
			'todays-bookings'    => 'Today\'s Bookings',
			'tomorrows-bookings' => 'Tomorrow\'s Bookings',
		);

		$tabs = apply_filters( 'fitpress_dashboard_tabs', $tabs );

		?>
        <div class="wrap">
            <h2>FitPress Dashboard</h2>
            <h2 class="nav-tab-wrapper">
				<?php foreach ( $tabs as $tab_key => $tab_name ): ?>
                    <a href="?page=fitpress&amp;tab=<?php echo $tab_key; ?>"
                       class="nav-tab <?php echo $active_tab == $tab_key ? 'nav-tab-active' : ''; ?>"><?php echo $tab_name; ?></a>
				<?php endforeach; ?>
            </h2>
			<?php switch ( $active_tab ):
				case 'tomorrows-bookings':
					$this->render_day_bookings( strtotime( 'tomorrow midnight' ) );
					break;
				case 'todays-bookings':
				case 'default':
					$this->render_day_bookings( strtotime( 'today midnight' ) );
					break;

			endswitch; ?>
        </div>
		<?php

	}

	public function render_day_bookings( $start_time ) {

		$day_bookings = FP_Booking::get_day_bookings( $start_time );

		if ( ! empty( $day_bookings ) ):

			foreach ( $day_bookings as $session => $bookings ):

				echo '<h3>' . $session . '</h3>';

				if ( ! empty( $bookings ) ):

					?>
                    <ol>
					<?php foreach ( $bookings as $booking ): ?>
                    <li>
                        <a href="<?php echo get_edit_user_link( $booking['user']->ID ); ?>"><?php echo $booking['user']->display_name; ?></a>
                    </li>
				<?php endforeach; ?>
                    </ol><?php

				else:
					echo '<p>No bookings for this session.</p>';
				endif;

			endforeach;

		else:

			echo '<p>There are no sessions on this day.</p>';

		endif;

	}

	public function fitpress_settings_render() {

		?>
        <div class="wrap">
            <h2>FitPress Settings</h2>
			<?php $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general'; ?>
			<?php $tabs = apply_filters( 'fitpress_settings_tabs', array(
				'general' => 'General',
				'email'   => 'Email'
			) ); ?>
            <h2 class="nav-tab-wrapper">
				<?php foreach ( $tabs as $tab => $name ) : ?>
                    <a href="?page=fp_settings&tab=<?php echo $tab; ?>"
                       class="nav-tab <?php echo $active_tab == $tab ? 'nav-tab-active' : ''; ?>"><?php echo $name; ?></a>
				<?php endforeach; ?>
            </h2>
            <form method="POST" action="options.php">
				<?php
				switch ( $active_tab ) :
					case 'email':
						$this->email_settings = get_option( 'fitpress_email_settings' );
						settings_fields( 'fp_email_settings' );
						do_settings_sections( 'fp_email_settings' );
						break;
					case 'general':
						$this->settings = get_option( 'fitpress_settings' );
						settings_fields( 'fp_settings' );
						do_settings_sections( 'fp_settings' );
						break;
				endswitch;
				?>
				<?php
				submit_button();
				?>
            </form>
        </div>
		<?php

	}

	public function init_settings() {

		add_settings_section(
			'general_settings',
			'General settings',
			array( $this, 'general_settings_callback_function' ),
			'fp_settings'
		);

		add_settings_field(
			'booking_time_limit',
			'Booking Time Limit',
			array( $this, 'booking_time_limit_callback_function' ),
			'fp_settings',
			'general_settings'
		);
		add_settings_field(
			'cancellation_time_limit',
			'Cancellation Time Limit',
			array( $this, 'cancellation_time_limit_callback_function' ),
			'fp_settings',
			'general_settings'
		);
		add_settings_field(
			'setup',
			'Setup',
			array( $this, 'setup_callback_function' ),
			'fp_settings',
			'general_settings'
		);

		add_settings_section(
			'email_settings',
			'Email settings',
			array( $this, 'email_settings_callback_function' ),
			'fp_email_settings'
		);

		add_settings_field(
			'background_color',
			'Email Background Colour',
			array( $this, 'email_background_color_callback_function' ),
			'fp_email_settings',
			'email_settings'
		);

		add_settings_field(
			'body_background_color',
			'Body Background Colour',
			array( $this, 'email_body_background_color_callback_function' ),
			'fp_email_settings',
			'email_settings'
		);

		add_settings_field(
			'text_color',
			'Text Colour',
			array( $this, 'email_text_color_callback_function' ),
			'fp_email_settings',
			'email_settings'
		);

		add_settings_field(
			'header_background_color',
			'Header Background Colour',
			array( $this, 'email_header_background_color_callback_function' ),
			'fp_email_settings',
			'email_settings'
		);

		add_settings_field(
			'header_text_color',
			'Header Text Colour',
			array( $this, 'email_header_text_color_callback_function' ),
			'fp_email_settings',
			'email_settings'
		);

		add_settings_field(
			'header_image',
			'Header Image',
			array( $this, 'email_header_image_callback_function' ),
			'fp_email_settings',
			'email_settings'
		);

		add_settings_field(
			'footer',
			'Footer',
			array( $this, 'email_footer_callback_function' ),
			'fp_email_settings',
			'email_settings'
		);

		add_settings_field(
			'email_from_name',
			'Email From Name',
			array( $this, 'email_from_name_callback_function' ),
			'fp_email_settings',
			'email_settings'
		);

		add_settings_field(
			'email_from_address',
			'Email From Address',
			array( $this, 'email_from_address_callback_function' ),
			'fp_email_settings',
			'email_settings'
		);

		register_setting( 'fp_settings', 'fitpress_settings' );
		register_setting( 'fp_email_settings', 'fitpress_email_settings' );

	}

	public function setup_callback_function() {
		echo 'If the automated setup did not run, <a href="' . add_query_arg( 'fp-setup', 'run' ) . '">click here to run it</a>.';
	}

	public function general_settings_callback_function() {
	}

	public function email_settings_callback_function() {
	}

	public function email_background_color_callback_function() {
		$value = ( ! empty( $this->email_settings['background_color'] ) ) ? $this->email_settings['background_color'] : '#eeeeee';
		echo '<input name="fitpress_email_settings[background_color]" id="background_color" class="small" type="text" value="' . $value . '" />';
	}

	public function email_body_background_color_callback_function() {
		$value = ( ! empty( $this->email_settings['body_background_color'] ) ) ? $this->email_settings['body_background_color'] : '#ffffff';
		echo '<input name="fitpress_email_settings[body_background_color]" id="body_background_color" class="small" type="text" value="' . $value . '" />';
	}

	public function email_text_color_callback_function() {
		$value = ( ! empty( $this->email_settings['text_color'] ) ) ? $this->email_settings['text_color'] : '#333333';
		echo '<input name="fitpress_email_settings[text_color]" id="text_color" class="small" type="text" value="' . $value . '" />';
	}

	public function email_header_background_color_callback_function() {
		$value = ( ! empty( $this->email_settings['header_background_color'] ) ) ? $this->email_settings['header_background_color'] : '#ffffff';
		echo '<input name="fitpress_email_settings[header_background_color]" id="header_background_color" class="small" type="text" value="' . $value . '" />';
	}

	public function email_header_text_color_callback_function() {
		$value = ( ! empty( $this->email_settings['header_text_color'] ) ) ? $this->email_settings['header_text_color'] : '#444444';
		echo '<input name="fitpress_email_settings[header_text_color]" id="header_text_color" class="small" type="text" value="' . $value . '" />';
	}

	public function email_header_image_callback_function() {
		$value = ( ! empty( $this->email_settings['header_image'] ) ) ? $this->email_settings['header_image'] : '';
		echo '<input name="fitpress_email_settings[header_image]" id="header_image" class="small" type="text" value="' . $value . '" />';
	}

	public function email_footer_callback_function() {
		$value = ( ! empty( $this->email_settings['footer'] ) ) ? $this->email_settings['footer'] : 'Powered by FitPress';
		echo '<textarea name="fitpress_email_settings[footer]" id="footer">' . $value . '</textarea>';
	}

	public function email_from_name_callback_function() {
		$value = ( ! empty( $this->email_settings['from_name'] ) ) ? $this->email_settings['from_name'] : get_bloginfo( 'name' );
		echo '<input name="fitpress_email_settings[from_name]" id="from_name" class="small" type="text" value="' . $value . '" />';
	}

	public function email_from_address_callback_function() {
		$value = ( ! empty( $this->email_settings['from_address'] ) ) ? $this->email_settings['from_address'] : get_bloginfo( 'admin_email' );
		echo '<input name="fitpress_email_settings[from_address]" id="from_address" class="small" type="text" value="' . $value . '" />';
	}

	public function booking_time_limit_callback_function() {
		$value = ( ! empty( $this->settings['booking_time_limit'] ) ) ? $this->settings['booking_time_limit'] : '30';
		echo '<input name="fitpress_settings[booking_time_limit]" id="booking_time_limit" class="small" type="number" value="' . $value . '" /> minutes';
	}

	public function cancellation_time_limit_callback_function() {
		$value = ( ! empty( $this->settings['cancellation_time_limit'] ) ) ? $this->settings['cancellation_time_limit'] : '30';
		echo '<input name="fitpress_settings[cancellation_time_limit]" id="cancellation_time_limit" class="small" type="number"  value="' . $value . '" /> minutes';
	}

	/*
	* Setup Column and data for users page with sortable
	*/
	public static function user_column_header( $column ) {

		$column['membership'] = __( 'Membership', 'fitpress' );

		$column['membership_status'] = __( 'Status', 'fitpress' );

		$column['expiration_date'] = __( 'Expiration Date', 'fitpress' );

		return $column;

	}

	public function user_column_data( $value, $column_name, $user_id ) {

		if ( 'membership' == $column_name ) :
			return $this->get_membership( $user_id );
		endif;

		if ( 'membership_status' == $column_name ) :
			$membership        = FP_Membership::get_user_membership( $user_id );
			$membership_status = new FP_Membership_Status( $membership['membership_id'] );

			return '<span class="pill pill-' . $membership_status->get_status() . '">' . $membership_status->get_status() . '</span>';
		endif;

		if ( 'expiration_date' == $column_name ) :
			return $this->get_expiration_date( $user_id );
		endif;

		return $value;

	}

	public function sort_by_membership( $query ) {

		if ( 'membership' == $query->get( 'orderby' ) ) {

			$query->set( 'meta_query', array(
				'relation' => 'OR',
				array(
					'key'     => 'fitpress_membership_id',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key' => 'fitpress_membership_id',
				),
			) );

			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'meta_key', 'fitpress_membership_id' );

		}

		if ( 'membership_status' == $query->get( 'orderby' ) ) {

			$query->set( 'meta_query', array(
				'relation' => 'OR',
				array(
					'key'     => 'fitpress_membership_status',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key' => 'fitpress_membership_status',
				),
			) );

			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', 'fitpress_membership_status' );

		}

	}

	public function user_column_sortable( $columns ) {

		return $columns;

	}

	public function get_membership( $user_id ) {
		$membership = FP_Membership::get_user_membership( $user_id );

		if ( ! $membership ) :
			return '<a href="' . get_admin_url( null, 'post-new.php?post_type=fp_member&user_id=' . $user_id ) . '" class="button button-primary">Add</a>';
		else :
			return '<a href="' . get_admin_url( null, 'post.php?post=' . $membership['membership_id'] . '&action=edit' ) . '" class="button button-primary">Edit</a> <span class="pill">' . $membership['name'] . '</span>';
		endif;
	}

	public function get_expiration_date( $user_id ) {
		$membership = FP_Membership::get_user_membership( $user_id );

		$expiration_date = get_post_meta( $membership['membership_id'], '_fp_expiration_date', true );

		if ( $expiration_date && $expiration_date != 'N/A' ) :

			return date( 'j F Y', intval( $expiration_date ) );

		endif;

		return '';
	}
}

/**
 * Extension main function
 */
function __fp_admin_main() {
	new FP_Admin();
}

// Initialize plugin when plugins are loaded
add_action( 'plugins_loaded', '__fp_admin_main' );
