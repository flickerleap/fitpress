<?php
/**
 * Members
 *
 * Manage members on fitpress
 *
 * @class     FP_Membership
 * @version   2.5.0
 * @package   FitPress/Classes/Members
 * @category  Class
 * @author    Digital Leap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FP_Membership
 */
class FP_Membership {

	/**
	 * Hook in methods.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register_post_types' ), 5 );

		if ( is_admin() ) {
			add_action( 'load-post.php', array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
		}

		add_action( 'fitpress_daily_cron', __CLASS__ . '::maybe_expire_memberships', 1 );

		add_action( 'init', array( $this, 'validate_query' ) );

	}

	/**
	 * Expire memberships
	 *
	 * Check to see if memberships have expired and change their membership.
	 */
	public static function maybe_expire_memberships() {

		$args = array(
			'post_type'      => 'fp_member',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => '_fp_membership_status',
					'value'   => 'active',
					'compare' => '=',
				),
				array(
					'relation' => 'OR',
					array(
						'key'     => '_fp_expiration_date',
						'value'   => array( strtotime( 'today midnight' ), strtotime( 'tomorrow midnight' ) - 1 ),
						'compare' => 'BETWEEN',
					),
					array(
						'key'     => '_fp_expiration_date',
						'value'   => strtotime( 'today midnight' ),
						'compare' => '=',
					),
				),
			),
			'posts_per_page' => '-1',
		);

		$memberships = new WP_Query( $args );

		if ( $memberships->found_posts ) :
			foreach ( $memberships->posts as $membership ) :
				the_post();
				$membership_id = $membership->ID;
				update_post_meta( $membership_id, '_fp_membership_status', 'expired' );
				update_post_meta( $membership_id, '_fp_credits', '0' );
			endforeach;
		endif;

		do_action( 'fitpress_expire_memberships' );

	}

	public function validate_query() {

		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'fp_member' && ! isset( $_GET['user_id'] ) ) :
			wp_redirect( get_admin_url( null, 'users.php' ) );
		endif;

	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {
		if ( post_type_exists( 'fp_member' ) ) {
			return;
		}

		register_post_type( 'fp_member',
			array(
				'labels'              => array(
					'name'                  => __( 'Members', 'fitpress' ),
					'singular_name'         => __( 'Member', 'fitpress' ),
					'menu_name'             => _x( 'Members', 'Admin menu name', 'fitpress' ),
					'add_new'               => __( 'Add Member', 'fitpress' ),
					'add_new_item'          => __( 'Add New Member', 'fitpress' ),
					'edit'                  => __( 'Edit', 'fitpress' ),
					'edit_item'             => __( 'Edit Member', 'fitpress' ),
					'new_item'              => __( 'New Member', 'fitpress' ),
					'view'                  => __( 'View Member', 'fitpress' ),
					'view_item'             => __( 'View Member', 'fitpress' ),
					'search_items'          => __( 'Search Members', 'fitpress' ),
					'not_found'             => __( 'No members found', 'fitpress' ),
					'not_found_in_trash'    => __( 'No members found in trash', 'fitpress' ),
					'parent'                => __( 'Parent Member', 'fitpress' ),
					'featured_image'        => __( 'Member Image', 'fitpress' ),
					'set_featured_image'    => __( 'Set member image', 'fitpress' ),
					'remove_featured_image' => __( 'Remove member image', 'fitpress' ),
					'use_featured_image'    => __( 'Use as member image', 'fitpress' ),
				),
				'description'         => __( 'This is where you can add new members to your site.', 'fitpress' ),
				'public'              => true,
				'show_ui'             => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => false,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'has_archive'         => false,
				'show_in_nav_menus'   => false,
				'show_in_menu'        => true,
				'supports'            => false,
				'menu_icon'			  => 'dashicons-groups',
			)
		);
	}

	/**
	 * Meta box initialization.
	 */
	public function init_metabox() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post', array( $this, 'save_session_metabox' ), 10, 2 );
	}

	/**
	 * Adds the meta box.
	 */
	public function add_metabox() {

		add_meta_box(
			'member-data',
			__( 'Member Information', 'fitpress' ),
			array( $this, 'render_member_metabox' ),
			'fp_member',
			'advanced',
			'default'
		);

		add_meta_box(
			'member-actions',
			__( 'Member Actions', 'fitpress' ),
			array( $this, 'render_actions_metabox' ),
			'fp_member',
			'side',
			'default'
		);

	}

	/**
	 * Renders the meta box.
	 *
	 * @param $post
	 */
	public function render_member_metabox( $post ) {
		// Add nonce for security and authentication.
		wp_nonce_field( FP_PLUGIN_FILE, 'session_nonce' );

		$packages = FP_Package::get_memberships( true );

		$user_id = get_post_meta( $post->ID, '_fp_user_id', true );

		$user_id = $user_id ? $user_id : $_GET['user_id']; ?>

        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>"/>

		<?php if ( $packages ) :

			// get existing user id

			$package_id = get_post_meta( $post->ID, '_fp_package_id', true );

			$user = get_userdata( $user_id );
			?>

            <p>
                <label for="package_id">Package</label>
                <select name="package_id" id="package_id">
					<?php foreach ( $packages as $id => $package ): ?>
                        <option value="<?php echo $id; ?>" <?php echo selected( $id, ( $package_id ) ? $package_id : '' ); ?>><?php echo $package; ?></option>
					<?php endforeach; ?>
                </select>
            </p>

			<?php if ( $package_id ) : ?>

			<?php
			$credits               = get_post_meta( $post->ID, '_fp_credits', true );
			$membership_start_date = date( 'j F Y', get_post_meta( $post->ID, '_fp_membership_start_date', true ) );
			$expiration_date       = get_post_meta( $post->ID, '_fp_expiration_date', true );
			$renewal_date          = get_post_meta( $post->ID, '_fp_renewal_date', true );
			?>

            <p>
                <label for="credits">Credits</label>
                <input type="text" name="credits" value="<?php echo ( $credits ) ? $credits : ''; ?>"/>
            </p>

            <p>
                <label for="membership_start_date">Start Date</label>
                <input type="text" name="membership_start_date"
                       value="<?php echo ( $membership_start_date ) ? $membership_start_date : ''; ?>"/>
            </p>

            <p>
                <label for="expiration_date">Expiration Date</label>
				<?php if ( $expiration_date && 'N/A' != $expiration_date ) : ?>
                    <input type="text" name="expiration_date"
                           value="<?php echo ( $expiration_date ) ? date( 'j F Y', $expiration_date ) : ''; ?>"/>
				<?php else : ?>
                    <input type="text" name="expiration_date" value="N/A"/>
				<?php endif; ?>
            </p>

			<?php do_action( 'fitpress_after_membership_fields', $post->ID, $user_id ); ?>

		<?php endif; ?>

            <p>
                <label for="username"><?php _e( 'Username', 'fitpress' ); ?></label>
                <input type="text" class="input-text" name="username" id="username"
                       value="<?php echo esc_attr( isset( $user->user_login ) ? $user->user_login : '' ); ?>"
                       disabled="disabled"/>
            </p>

            <p>
                <label for="account_first_name"><?php _e( 'First name', 'fitpress' ); ?></label>
                <input type="text" class="input-text" name="account_first_name" id="account_first_name"
                       value="<?php echo esc_attr( isset( $user->first_name ) ? $user->first_name : '' ); ?>"/>
            </p>

            <p>
                <label for="account_last_name"><?php _e( 'Last name', 'fitpress' ); ?></label>
                <input type="text" class="input-text" name="account_last_name" id="account_last_name"
                       value="<?php echo esc_attr( isset( $user->last_name ) ? $user->last_name : '' ); ?>"/>
            </p>

            <p>
                <label for="account_email"><?php _e( 'Email address', 'fitpress' ); ?></label>
                <input type="email" class="input-text" name="account_email" id="account_email"
                       value="<?php echo esc_attr( isset( $user->user_email ) ? $user->user_email : '' ); ?>"/>
            </p>

            <p>
                <label for="contact_number"><?php _e( 'Contact Number', 'fitpress' ); ?></label>
                <input type="tel" class="input-text" name="contact_number" id="contact_number"
                       value="<?php echo esc_attr( isset( $user->contact_number ) ? $user->contact_number : '' ); ?>"/>
            </p>

            <p>
                <label for="emergency_contact_name"><?php _e( 'Emergency Contact Name', 'fitpress' ); ?></label>
                <input type="text" class="input-text" name="emergency_contact_name" id="emergency_contact_name"
                       value="<?php echo esc_attr( isset( $user->emergency_contact_name ) ? $user->emergency_contact_name : '' ); ?>"/>
            </p>

            <p>
                <label for="emergency_contact_number"><?php _e( 'Emergency Contact Number', 'fitpress' ); ?></label>
                <input type="text" class="input-text" name="emergency_contact_number" id="emergency_contact_number"
                       value="<?php echo esc_attr( isset( $user->emergency_contact_number ) ? $user->emergency_contact_number : '' ); ?>"/>
            </p>

			<?php
		endif;
	}

	public function render_actions_metabox( $post ) {

		$user_id = get_post_meta( $post->ID, '_fp_user_id', true );

		$user_id = $user_id ? $user_id : $_GET['user_id'];

		$package_id = get_post_meta( $post->ID, '_fp_package_id', true );

		?>

		<?php if ( $package_id ) : ?>

            <p>
                <label for="update_credits">Update Credits?</label>
                <input type="checkbox" name="update_credits" value="1"/>
            </p>

		<?php endif; ?>

		<?php do_action( 'fitpress_after_membership_actions' ); ?>

		<?php

	}

	/**
	 * Handles saving session metabox
	 *
	 * @param $membership_id
	 * @param WP_Post $post Post object.
	 */
	public function save_session_metabox( $membership_id, $post ) {

		// Check if nonce is set.
		if ( 'fp_member' != $post->post_type ) {
			return;
		}

		// Add nonce for security and authentication.
		$nonce_name   = isset( $_POST['session_nonce'] ) ? $_POST['session_nonce'] : '';
		$nonce_action = FP_PLUGIN_FILE;

		// Check if nonce is set.
		if ( ! isset( $nonce_name ) ) {
			return;
		}

		// Check if nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
			return;
		}

		// Check if user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $membership_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $membership_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $membership_id ) ) {
			return;
		}

		$member_id = $_POST['user_id'];

		if ( isset( $_POST['package_id'] ) && ! empty( $_POST['package_id'] ) ) :

			$old_package_id            = get_post_meta( $membership_id, '_fp_package_id', true );
			$old_credits               = get_post_meta( $membership_id, '_fp_credits', true );
			$old_membership_start_date = get_post_meta( $membership_id, '_fp_membership_start_date', true );
			$old_expiration_date       = get_post_meta( $membership_id, '_fp_expiration_date', true );

			$package_id = $_POST['package_id'];

			$credits = isset( $_POST['credits'] ) ? $_POST['credits'] : '0';

			$update_credits = isset( $_POST['update_credits'] ) ? $_POST['update_credits'] : 0;

			if ( $old_package_id != $package_id && ( 1 == $update_credits || ! $old_package_id || 0 == $old_package_id ) ) :

				$credits = FP_Credit::update_member_credits( $package_id, $old_package_id, $old_credits );

			endif;

			if ( isset( $_POST['membership_start_date'] ) ) :
				$membership_start_date = strtotime( $_POST['membership_start_date'] );
			else :
				$membership_start_date = strtotime( 'today' );
			endif;

			if ( isset( $_POST['expiration_date'] ) && $old_package_id == $package_id ) :
				$expiration_date = strtotime( $_POST['expiration_date'] );
            elseif ( $old_package_id != $package_id ) :
				$package_data = FP_Package::get_membership( $package_id );
				if ( 'Once Off' != $package_data[ $package_id ]['term'] ) :
					$expiration_date = 'N/A';
				else :
					$expiration_date = strtotime( $package_data[ $package_id ]['expiration_date'] );
				endif;
			endif;

			do_action( 'fitpress_before_membership_save', array( 'membership_id'  => $post->ID,
			                                                     'package_id'     => $package_id,
			                                                     'old_package_id' => $old_package_id
			) );

			update_post_meta( $membership_id, '_fp_user_id', $member_id, $member_id );
			update_post_meta( $membership_id, '_fp_package_id', $package_id, $old_package_id );
			update_post_meta( $membership_id, '_fp_credits', $credits, $old_credits );
			update_post_meta( $membership_id, '_fp_membership_start_date', $membership_start_date, $old_membership_start_date );
			update_post_meta( $membership_id, '_fp_expiration_date', $expiration_date, $old_expiration_date );

			do_action( 'fitpress_after_membership_save', array( 'membership_id'  => $post->ID,
			                                                    'package_id'     => $package_id,
			                                                    'old_package_id' => $old_package_id
			) );

		endif;

		update_user_meta( $member_id, 'contact_number', $_POST['contact_number'] );
		update_user_meta( $member_id, 'emergency_contact_name', $_POST['emergency_contact_name'] );
		update_user_meta( $member_id, 'emergency_contact_number', $_POST['emergency_contact_number'] );

		$user = get_user_by( 'id', $member_id );

		$user->first_name   = $_POST['account_first_name'];
		$user->last_name    = $_POST['account_last_name'];
		$user->user_email   = $_POST['account_email'];
		$user->display_name = $_POST['account_first_name'] . ' ' . $_POST['account_last_name'];

		wp_update_user( $user );

	}

}

/**
 * Extension main function
 */
function __fp_membership_main() {
	new FP_Membership();
}

// Initialize plugin when plugins are loaded
add_action( 'plugins_loaded', '__fp_membership_main' );
