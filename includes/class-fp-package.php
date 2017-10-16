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
 * Class FP_Membership
 */
class FP_Package {

	/**
	 * We only want a single instance of this class.
	 *
	 * @var Object $instance
	 */
	private static $instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return  FP_Package A single instance of this class.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	} // end get_instance;

	/**
	 * Hook in methods.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register_post_types' ) );

		add_action( 'show_user_profile', array( $this, 'show_membership_profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'show_membership_profile_fields' ) );

		add_action( 'personal_options_update', array( $this, 'save_membership_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_membership_profile_fields' ) );

		if ( is_admin() ) {
			add_action( 'load-post.php', array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
		}

		if ( ! wp_get_schedule( 'maybe_send_member_list_hook' ) ):
			$start = strtotime( 'tomorrow' );
			wp_schedule_event( $start, 'daily', 'maybe_send_member_list_hook' );
		endif;

		add_action( 'maybe_send_member_list_hook', __CLASS__ . '::maybe_send_member_list' );

		add_action( 'wp_ajax_fp_find_member', array( $this, 'find_member_callback' ) );

	}

	public static function find_member_callback() {

		$members = self::get_members( 'ID', false, $_REQUEST['search'] );

		$result = array();

		if ( ! empty( $members ) ) :

			$result['type'] = 'found';

			foreach ( $members as &$member ) :
				$user_id              = get_post_meta( $member->ID, '_fp_user_id', true );
				$user                 = get_user_by( 'id', $user_id );
				$member->user_id      = $user_id;
				$member->display_name = $user->display_name;
			endforeach;
			unset( $member );
			$result['members'] = $members;

		else :

			$result['type'] = 'not-found';

		endif;

		$result = json_encode( $result );
		echo $result;

		die();

	}

	/**
	 * Maybe send member list function
	 *
	 * @param bool $force
	 * @param bool $none_members
	 */
	public static function maybe_send_member_list( $force = false, $none_members = false ) {

		if ( 1 == date( 'j' ) || $force ) :

			$members = FP_Package::get_members( 'all', $none_members );

			$memberships = FP_Package::get_memberships();

			// Check for results
			if ( ! empty( $members ) ) :

				$lines[] = array(
					'member_id',
					'user_email',
					'first_name',
					'last_name',
					'package',
					'status',
					'expiration_date',
					'renewal_date',
				);

				foreach ( $members as $member ):

					$data = array();

					$user_id = get_post_meta( $member->ID, '_fp_user_id', true );
					$user    = get_user_by( 'ID', $user_id );

					$data['member_id']  = $member->ID;
					$data['user_email'] = $user->user_email;
					$data['first_name'] = $user->first_name;
					$data['last_name']  = $user->last_name;

					if ( ! $none_members ):

						$package_id = get_post_meta( $member->ID, '_fp_package_id', true );

						$membership_status = new FP_Membership_Status( $member->ID );

						$data['package']         = $memberships[ $package_id ]['name'];
						$data['status']          = $membership_status->get_status();
						$data['expiration_date'] = date( 'j F Y', get_post_meta( $member->ID, '_fp_expiration_date', true ) );
						$data['renewal_date']    = date( 'j F Y', get_post_meta( $member->ID, '_fp_renewal_date', true ) );

					endif;

					$lines[] = $data;

				endforeach;

				$subject = ( $none_members ) ? 'Inactive Members' : 'Active Members';

				$path = FP_PLUGIN_DIR . 'export/' . date( 'Y-m-d' ) . ' ' . $subject . '.csv';

				$fh = fopen( $path, 'w' ) or die( 'Cannot open the file: ' . $path );

				foreach ( $lines as $line ) :
					fputcsv( $fh, $line, ',' );
				endforeach;

				fclose( $fh );

				$attachments = array( $path );

				$fp_email = new FP_Email();

				$fp_email->send_email( get_bloginfo( 'admin_email' ), $subject, array( 'header'  => $subject,
				                                                                       'message' => 'Here\'s the member list :)'
				), $attachments );

			endif;

		endif;

	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {
		if ( post_type_exists( 'membership' ) ) {
			return;
		}

		do_action( 'action_register_post_type' );

		register_post_type( 'membership',
			array(
				'labels'              => array(
					'name'                  => __( 'Packages', 'fitpress' ),
					'singular_name'         => __( 'Package', 'fitpress' ),
					'menu_name'             => _x( 'Packages', 'Admin menu name', 'fitpress' ),
					'add_new'               => __( 'Add Package', 'fitpress' ),
					'add_new_item'          => __( 'Add New Package', 'fitpress' ),
					'edit'                  => __( 'Edit', 'fitpress' ),
					'edit_item'             => __( 'Edit Package', 'fitpress' ),
					'new_item'              => __( 'New Package', 'fitpress' ),
					'view'                  => __( 'View Package', 'fitpress' ),
					'view_item'             => __( 'View Package', 'fitpress' ),
					'search_items'          => __( 'Search Products', 'fitpress' ),
					'not_found'             => __( 'No packages found', 'fitpress' ),
					'not_found_in_trash'    => __( 'No packages found in trash', 'fitpress' ),
					'parent'                => __( 'Parent Package', 'fitpress' ),
					'featured_image'        => __( 'Package Image', 'fitpress' ),
					'set_featured_image'    => __( 'Set package image', 'fitpress' ),
					'remove_featured_image' => __( 'Remove package image', 'fitpress' ),
					'use_featured_image'    => __( 'Use as package image', 'fitpress' ),
				),
				'description'         => __( 'This is where you can add new memberships to your website.', 'fitpress' ),
				'public'              => false,
				'show_ui'             => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => true,
				'supports'            => array( 'title' ),
				'has_archive'         => false,
				'show_in_nav_menus'   => true,
				'show_in_menu'        => 'fitpress',
			)
		);
	}

	/**
	 * Meta box initialization.
	 */
	public function init_metabox() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
	}

	/**
	 * Adds the meta box.
	 */
	public function add_metabox() {
		add_meta_box(
			'membership-data',
			__( 'Membership Data', 'fit-press' ),
			array( $this, 'render_metabox' ),
			'membership',
			'advanced',
			'default'
		);

	}

	/**
	 * Renders the meta box.
	 *
	 * @param $post
	 */
	public function render_metabox( $post ) {
		// Add nonce for security and authentication.
		wp_nonce_field( FP_PLUGIN_FILE, 'membership_nonce' );

		$package_data = get_post_meta( $post->ID, "membership_data", true );

		?>
        <p>
            <label for="credits">Credits</label>
            <input name="credits" type="text"
                   value="<?php echo isset( $package_data['credits'] ) ? $package_data['credits'] : ''; ?>">
        </p>
        <p>
            <label for="expiration_date">Expiration Date</label>
            <select name="expiration_date">
                <option value="Once Off" <?php selected( isset( $package_data['expiration_date'] ) ? $package_data['expiration_date'] : '', 'None' ); ?>>
                    None
                </option>
                <option value="+1 month" <?php selected( isset( $package_data['expiration_date'] ) ? $package_data['expiration_date'] : '', '+1 month' ); ?>>
                    +1 Month
                </option>
                <option value="+3 months" <?php selected( isset( $package_data['expiration_date'] ) ? $package_data['expiration_date'] : '', '+3 months' ); ?>>
                    +3 Months
                </option>
                <option value="+6 months" <?php selected( isset( $package_data['expiration_date'] ) ? $package_data['expiration_date'] : '', '+6 months' ); ?>>
                    +6 Months
                </option>
                <option value="+1 year" <?php selected( isset( $package_data['expiration_date'] ) ? $package_data['expiration_date'] : '', '+1 year' ); ?>>
                    +1 Year
                </option>
            </select>
        </p>
		<?php
		do_action( 'fitpress_after_package_fields', $package_data, $post->ID );
	}

	/**
	 * Handles saving the meta box.
	 *
	 * @param int $post_id Post ID.
	 * @param WP_Post $post Post object.
	 *
	 * @return null
	 */
	public function save_metabox( $post_id, $post ) {
		// Add nonce for security and authentication.
		$nonce_name   = isset( $_POST['membership_nonce'] ) ? $_POST['membership_nonce'] : '';
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
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$package_data = get_post_meta( $post->ID, 'membership_data', true );

		if ( isset( $_POST["credits"] ) ) {
			$package_data['credits'] = $_POST["credits"];
		}

		if ( isset( $_POST['expiration_date'] ) ) {
			$package_data['expiration_date'] = $_POST['expiration_date'];
		}

		$package_data = apply_filters( 'fitpress_before_package_save', $package_data );

		update_post_meta( $post_id, "membership_data", $package_data );

	}

	function show_membership_profile_fields( $user ) {

		?>

        <h3>Member Contact Details</h3>

		<?php $contact_number = get_user_meta( $user->ID, 'contact_number', true ); ?>
		<?php $emergency_contact_name = get_user_meta( $user->ID, 'emergency_contact_name', true ); ?>
		<?php $emergency_contact_number = get_user_meta( $user->ID, 'emergency_contact_number', true ); ?>

        <table class="form-table">

            <tr>
                <th><label for="contact_number">Contact Number</label></th>

                <td>
                    <input type="text" name="contact_number" id="contact_number"
                           value="<?php echo esc_attr( ( $contact_number ) ? $contact_number : '' ); ?>"/><br/>
                </td>
            </tr>

            <tr>
                <th><label for="emergency_contact_name">Emergency Contact Name</label></th>

                <td>
                    <input type="text" name="emergency_contact_name" id="emergency_contact_name"
                           value="<?php echo esc_attr( ( $emergency_contact_name ) ? $emergency_contact_name : '' ); ?>"/><br/>
                </td>
            </tr>

            <tr>
                <th><label for="emergency_contact_number">Emergency Contact Number</label></th>

                <td>
                    <input type="text" name="emergency_contact_number" id="emergency_contact_number"
                           value="<?php echo esc_attr( ( $emergency_contact_number ) ? $emergency_contact_number : '' ); ?>"/><br/>
                </td>
            </tr>

        </table>

        <h3>FitPress Membership Details</h3>

		<?php $memberships = $this->get_memberships( true ); ?>

		<?php if ( $memberships ): ?>

			<?php $membership_id = get_user_meta( $user->ID, 'fitpress_membership_id', true ); ?>
			<?php $credits = get_user_meta( $user->ID, 'fitpress_credits', true ); ?>
			<?php $membership_date_timestamp = get_user_meta( $user->ID, 'fitpress_membership_date', true ); ?>
			<?php $membership_date = date( 'j F Y', $membership_date_timestamp ? intval( $membership_date_timestamp ) : strtotime( 'NOW' ) ); ?>

            <table class="form-table">

                <tr>
                    <th><label for="membership_id">Membership</label></th>

                    <td>
                        <select name="membership_id" id="membership_id">
							<?php foreach ( $memberships as $id => $membership ): ?>
                                <option value="<?php echo $id; ?>" <?php echo selected( $id, ( $membership_id ) ? $membership_id : '' ); ?>><?php echo $membership; ?></option>
							<?php endforeach; ?>
                        </select>
                        <span class="description">Please select the membership type.</span>
                    </td>
                </tr>

                <tr>
                    <th><label for="credits">Update Credits Now</label></th>

                    <td>
                        <input type="hidden" name="update_credits" id="update_credits" class="regular-text" value="0"/>
                        <input type="checkbox" name="update_credits" id="update_credits" class="regular-check"
                               value="1"/><br/>
                        <span class="description">Update credits now if a membership has changed</span>
                    </td>
                </tr>

                <tr>
                    <th><label for="credits">Credits</label></th>

                    <td>
                        <input type="text" name="credits" id="credits"
                               value="<?php echo esc_attr( ( $credits ) ? $credits : 0 ); ?>" class="small-text"
                               readonly="readonly"/><br/>
                        <span class="description">Please enter the new credit amount.</span>
                    </td>
                </tr>

                <tr>
                    <th><label for="membership_date">Membership Start Date</label></th>

                    <td>
                        <input type="text" name="membership_date" id="membership_date"
                               value="<?php echo esc_attr( $membership_date ); ?>" class="regular-text"/>
                    </td>
                </tr>

				<?php do_action( 'fitpress_after_membership_profile_fields', $user->ID, $membership_id ); ?>

            </table>

		<?php else: ?>

            <p>You need to add memberships before updating active users.</p>

		<?php endif; ?>

		<?php

	}

	function save_membership_profile_fields( $member_id ) {

		if ( ! current_user_can( 'edit_user', $member_id ) ) {
			return false;
		}

		if ( isset( $_POST['membership_id'] ) && isset( $_POST['credits'] ) && isset( $_POST['membership_date'] ) ) :

			$old_membership_id   = get_user_meta( $member_id, 'fitpress_membership_id', true );
			$old_credits         = get_user_meta( $member_id, 'fitpress_credits', true );
			$old_membership_date = get_user_meta( $member_id, 'fitpress_membership_date', true );

			$membership_id = $_POST['membership_id'];

			$credits = $_POST['credits'];

			$membership_date = strtotime( $_POST['membership_date'] );

			if ( $old_membership_id != $membership_id && ( $_POST['update_credits'] == 1 || ! $old_membership_id || $old_membership_id == 0 ) ):

				$credits = FP_Credit::update_member_credits( $_POST['membership_id'], $old_membership_id, $old_credits );

			endif;

			do_action( 'fitpress_before_membership_profile_save', array( 'member_id'         => $member_id,
			                                                             'old_membership_id' => $old_membership_id
			) );

			update_user_meta( $member_id, 'fitpress_membership_id', $membership_id, $old_membership_id );
			update_user_meta( $member_id, 'fitpress_credits', $credits, $old_credits );
			update_user_meta( $member_id, 'fitpress_membership_date', $membership_date, $old_membership_date );

		endif;

		update_user_meta( $member_id, 'contact_number', $_POST['contact_number'] );
		update_user_meta( $member_id, 'emergency_contact_name', $_POST['emergency_contact_name'] );
		update_user_meta( $member_id, 'emergency_contact_number', $_POST['emergency_contact_number'] );

	}

	public static function quick_member_add( $member_id = null, $membership_id = null ) {

		if ( ! $member_id || ! $membership_id ) :
			return;
		endif;

		$credits = FP_Credit::update_member_credits( $membership_id, 0, 0 );

		do_action( 'fitpress_before_membership_profile_save', array( 'member_id'         => $member_id,
		                                                             'old_membership_id' => false
		) );

		update_user_meta( $member_id, 'fitpress_membership_id', $membership_id );
		update_user_meta( $member_id, 'fitpress_credits', $credits );

	}

	public static function get_memberships( $select = false ) {

		$args = array(
			'post_type'      => 'membership',
			'orderby'        => 'post_title',
			'order'          => 'ASC',
			'posts_per_page' => '-1',
		);

		$memberships_obj = new WP_Query( $args );

		if ( $memberships_obj->have_posts() ) :

			$memberships = array();

			foreach ( $memberships_obj->posts as $membership ) :

				if ( $select ) :

					$memberships[ $membership->ID ] = $membership->post_title;

				else :

					$membership_data = get_post_meta( $membership->ID, 'membership_data', true );

					$memberships[ $membership->ID ] = array(
						'name' => $membership->post_title,
					);

					foreach ( $membership_data as $key => $value ) :

						$memberships[ $membership->ID ][ $key ] = $value;

					endforeach;

				endif;

			endforeach;

			return $memberships;

		else :

			return false;

		endif;

	}

	public static function get_membership( $membership_ids = array() ) {

		if ( ! is_array( $membership_ids ) ) :
			$membership_ids = array( $membership_ids );
		endif;

		$args = array(
			'post_type' => 'membership',
			'orderby'   => 'post_title',
			'order'     => 'ASC',
			'post__in'  => $membership_ids,
		);

		$memberships_obj = new WP_Query( $args );

		if ( $memberships_obj->have_posts() ):

			$memberships = array();

			foreach ( $memberships_obj->posts as $membership ):

				$membership_data = get_post_meta( $membership->ID, "membership_data", true );

				$memberships[ $membership->ID ] = array(
					'name' => $membership->post_title
				);

				foreach ( $membership_data as $key => $value ):

					$memberships[ $membership->ID ][ $key ] = $value;

				endforeach;

			endforeach;

			return $memberships;

		else:

			return false;

		endif;

	}

	/**
	 * Get a list of active or non-active members. Can also search.
	 *
	 * @param Mixed $fields Fields to return. Defaults to ID.
	 * @param Bool $none_members Set to true to return none members.
	 * @param Mixed $search Set to string to search for a specific member.
	 */
	public static function get_members( $fields = 'ID', $none_members = false, $search = false ) {

		if ( $search ) :
			$search = '*' . esc_attr( $search ) . '*';
		endif;

		if ( $none_members ) :

			if ( $search ) :

				$user_args = array(
					'search' => $search,
					'fields' => $fields,
				);

				$users = new WP_User_Query( $user_args );

				$args = array(
					'meta_query'     => array(
						array(
							'key'     => '_fp_membership_status',
							'value'   => 'active',
							'compare' => '!=',
						),
						array(
							'key'     => '_fp_user_id',
							'value'   => $users,
							'compare' => 'IN',
						),
					),
					'search'         => $search,
					'fields'         => $fields,
					'posts_per_page' => '-1',
				);

			else :

				$args = array(
					'meta_query'     => array(
						array(
							'key'     => '_fp_membership_status',
							'value'   => 'active',
							'compare' => '!=',
						),
					),
					'fields'         => $fields,
					'posts_per_page' => '-1',
				);

			endif;

		else :

			if ( $search ) :

				$user_args = array(
					'search' => $search,
					'fields' => $fields,
				);

				$users = new WP_User_Query( $user_args );

				$args = array(
					'meta_query'     => array(
						array(
							'key'     => '_fp_membership_status',
							'value'   => 'active',
							'compare' => '=',
						),
						array(
							'key'     => '_fp_user_id',
							'value'   => $users->results,
							'compare' => 'IN',
						),
					),
					'search'         => $search,
					'fields'         => $fields,
					'posts_per_page' => '-1',
				);

			else :

				$args = array(
					'meta_query'     => array(
						array(
							'key'     => '_fp_membership_status',
							'value'   => 'active',
							'compare' => '=',
						),
					),
					'fields'         => $fields,
					'posts_per_page' => '-1',
				);

			endif;

		endif;

		$args['post_type'] = 'fp_member';

		$membership_query = new WP_Query( $args );

		return $membership_query->posts;

	}

	public static function get_user_membership( $user_id ) {

		$args = array(
			'post_type'  => 'fp_member',
			'meta_query' => array(
				array(
					'key'   => '_fp_user_id',
					'value' => $user_id,
				),
			),
		);

		$membership_obj = new WP_Query( $args );

		if ( $membership_obj->found_posts ) :

			$package_id = get_post_meta( $membership_obj->posts[0]->ID, '_fp_package_id', true );

			$package = FP_Package::get_membership( $package_id );

			$package[ $package_id ]['membership_id'] = $membership_obj->posts[0]->ID;
			$package[ $package_id ]['package_id']    = $package_id;

			return $package[ $package_id ];

		else :
			return false;
		endif;
	}

}

/**
 * Extension main function
 */
function __fp_membership_main() {
	FP_Package::get_instance();
}

// Initialize plugin when plugins are loaded
add_action( 'plugins_loaded', '__fp_membership_main' );
