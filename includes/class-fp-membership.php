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
 * FP_Post_Types Class.
 */
class FP_Membership {

	/**
	 * We only want a single instance of this class.
	 *
	 * @var Object $instance
	 */
	private static $instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return  FP_Membership A single instance of this class.
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
	public function __construct(){

		add_action( 'init', array( $this, 'register_post_types' ) );

		add_action( 'show_user_profile', array( $this, 'show_membership_profile_fields' ) );
		add_action( 'edit_user_profile',  array( $this, 'show_membership_profile_fields' ));

		add_action( 'personal_options_update', array( $this, 'save_membership_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_membership_profile_fields' ) );

		if ( is_admin() ) {
			add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
		}

		if( !wp_get_schedule('maybe_send_member_list_hook') ):
			$start = strtotime( 'tomorrow' );
			wp_schedule_event( $start, 'daily', 'maybe_send_member_list_hook' );
		endif;

		add_action('maybe_send_member_list_hook', __CLASS__ . '::maybe_send_member_list' );

		add_action( 'wp_ajax_fp_find_member', array( $this, 'find_member_callback' ) );

	}

	public static function find_member_callback( ){

		$members = self::get_members( array( 'ID', 'display_name' ), false, $_REQUEST['search'] );

		$result = array();

		if( !empty( $members ) ):

			$result['type'] = 'found';
			$result['members'] = $members;

		else:

			$result['type'] = 'not-found';

		endif;

		$result = json_encode($result);
		echo $result;

		die();

	}

	/**
	* Adds credits to user if subscription is successfully activated
	*
	* @param int $user_id A user ID for the user that the subscription was activated for.
	* @param mixed $subscription_key The key referring to the activated subscription
	* @version 1.0
	* @since 0.1
	*/
	public static function maybe_send_member_list( $force = false, $none_members = false ) {

		if( date('j') == 1 || $force ):

			$members = FP_Membership::get_members( 'all', $none_members );

			$memberships = FP_Membership::get_memberships( );

			// Check for results
			if (!empty($members)):

				$lines[] = array(
					'ID',
					'user_email',
					'first_name',
					'last_name',
					'membership',
					'price',
				);

				foreach ( $members as $member ):

					$data = array();

					$data['user_id'] = $member->ID;
					$data['user_email'] = $member->user_email;
					$data['first_name'] = $member->first_name;
					$data['last_name'] = $member->last_name;

					if( !$none_members ):

						$membership_id = get_user_meta( $member->ID, 'fitpress_membership_id', true );

						$data['membership'] = $memberships[ $membership_id ]['name'];
						$data['membership_price'] = $memberships[ $membership_id ]['price'];

					endif;

					$lines[] = $data;

				endforeach;

				$subject = ( $none_members ) ? 'Inactive Members' : 'Active Members';

				$path = FP_PLUGIN_DIR . 'export/' . date('Y-m-d') . ' ' . $subject . '.csv';

				$fh = fopen( $path, 'w') or die('Cannot open the file: ' . $path);

				foreach($lines as $line)
					fputcsv($fh, $line, ',');

				fclose($fh);

				$attachments = array( $path );

				$FP_Email = new FP_Email( );

				$FP_Email->send_email( 'heartbeat@crossfitexanimo.co.za', $subject, array( 'header' => $subject, 'message' => 'Here\'s the member list :)' ), $attachments );

			endif;

		endif;

	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {
		if ( post_type_exists('membership') ) {
			return;
		}

		do_action( 'action_register_post_type' );

		register_post_type( 'membership',
			array(
				'labels'             => array(
					'name'                  => __( 'Memberships', 'fitpress' ),
					'singular_name'         => __( 'Membership', 'fitpress' ),
					'menu_name'             => _x( 'Memberships', 'Admin menu name', 'fitpress' ),
					'add_new'               => __( 'Add Membership', 'fitpress' ),
					'add_new_item'          => __( 'Add New Membership', 'fitpress' ),
					'edit'                  => __( 'Edit', 'fitpress' ),
					'edit_item'             => __( 'Edit Membership', 'fitpress' ),
					'new_item'              => __( 'New Membership', 'fitpress' ),
					'view'                  => __( 'View Membership', 'fitpress' ),
					'view_item'             => __( 'View Membership', 'fitpress' ),
					'search_items'          => __( 'Search Products', 'fitpress' ),
					'not_found'             => __( 'No Memberships found', 'fitpress' ),
					'not_found_in_trash'    => __( 'No Memberships found in trash', 'fitpress' ),
					'parent'                => __( 'Parent Membership', 'fitpress' ),
					'featured_image'        => __( 'Membership Image', 'fitpress' ),
					'set_featured_image'    => __( 'Set membership image', 'fitpress' ),
					'remove_featured_image' => __( 'Remove membership image', 'fitpress' ),
					'use_featured_image'    => __( 'Use as membership image', 'fitpress' ),
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
				'show_in_menu'		  => 'fitpress',
			)
		);
	}

	/**
	 * Meta box initialization.
	 */
	public function init_metabox() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox'  )        );
		add_action( 'save_post',      array( $this, 'save_metabox' ), 10, 2 );
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
	 */
	public function render_metabox( $post ) {
		// Add nonce for security and authentication.
		wp_nonce_field( FP_PLUGIN_FILE, 'membership_nonce' );

		$membership_data = get_post_meta($post->ID, "membership_data", true);

		?>
		<p>
			<label for="credits">Credits</label>
			<input name="credits" type="text" value="<?php echo isset( $membership_data['credits'] ) ? $membership_data['credits'] : ''; ?>">
		</p>
		<?php
		do_action( 'fitpress_after_membership_fields', $membership_data, $post->ID );
	}

	/**
	 * Handles saving the meta box.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
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

		$membership_data = get_post_meta($post->ID, "membership_data", true);

		if(isset($_POST["credits"])){
			$membership_data['credits'] = $_POST["credits"];
		}

		$membership_data = apply_filters( 'fitpress_before_membership_save', $membership_data );

		update_post_meta($post_id, "membership_data", $membership_data);

	}

	function show_membership_profile_fields( $user ) {

	?>

		<h3>Member Contact Details</h3>

		<?php $contact_number = get_user_meta( $user->ID, 'contact_number', true );?>
		<?php $emergency_contact_name = get_user_meta( $user->ID, 'emergency_contact_name', true );?>
		<?php $emergency_contact_number = get_user_meta( $user->ID, 'emergency_contact_number', true );?>

		<table class="form-table">

			<tr>
				<th><label for="contact_number">Contact Number</label></th>

				<td>
					<input type="text" name="contact_number" id="contact_number" value="<?php echo esc_attr( ($contact_number) ? $contact_number : '' ); ?>" /><br />
				</td>
			</tr>

			<tr>
				<th><label for="emergency_contact_name">Emergency Contact Name</label></th>

				<td>
					<input type="text" name="emergency_contact_name" id="emergency_contact_name" value="<?php echo esc_attr( ($emergency_contact_name) ? $emergency_contact_name : '' ); ?>" /><br />
				</td>
			</tr>

			<tr>
				<th><label for="emergency_contact_number">Emergency Contact Number</label></th>

				<td>
					<input type="text" name="emergency_contact_number" id="emergency_contact_number" value="<?php echo esc_attr( ($emergency_contact_number) ? $emergency_contact_number : '' ); ?>" /><br />
				</td>
			</tr>

		</table>

		<h3>FitPress Membership Details</h3>

		<?php $memberships = $this->get_memberships( true );?>

		<?php if($memberships):?>

			<?php $membership_id = get_user_meta( $user->ID, 'fitpress_membership_id', true ); ?>
			<?php $credits = get_user_meta( $user->ID, 'fitpress_credits', true ); ?>
			<?php $membership_date = get_user_meta( $user->ID, 'fitpress_membership_date', true ); ?>

			<table class="form-table">

			<tr>
			<th><label for="membership_id">Membership</label></th>

			<td>
				<select name="membership_id" id="membership_id">
				<?php foreach( $memberships as $id => $membership ):?>
					<option value="<?php echo $id;?>" <?php echo selected( $id, ($membership_id) ? $membership_id : '' );?>><?php echo $membership;?></option>
				<?php endforeach;?>
				</select>
				<span class="description">Please select the membership type.</span>
			</td>
			</tr>

			<tr>
			<th><label for="credits">Update Credits Now</label></th>

			<td>
				<input type="hidden" name="update_credits" id="update_credits"  class="regular-text" value="0" />
				<input type="checkbox" name="update_credits" id="update_credits"  class="regular-check" value="1" /><br />
				<span class="description">Update credits now if a membership has changed</span>
			</td>
			</tr>

			<tr>
			<th><label for="credits">Credits</label></th>

			<td>
				<input type="text" name="credits" id="credits" value="<?php echo esc_attr( ($credits) ? $credits : 0 ); ?>" class="small-text" readonly="readonly" /><br />
				<span class="description">Please enter the new credit amount.</span>
			</td>
			</tr>

			<tr>
			<th><label for="membership_date">Membership Start Date</label></th>

			<td>
				<input type="text" name="membership_date" id="membership_date" value="<?php echo esc_attr( ($membership_date) ? $membership_date : date('j F Y') ); ?>" class="regular-text" />
			</td>
			</tr>

			<?php do_action( 'fitpress_after_membership_profile_fields', $user->ID, $membership_id );?>

			</table>

		<?php else:?>

			<p>You need to add memberships before updating active users.</p>

		<?php endif;?>

	<?php

	}

	function save_membership_profile_fields( $member_id ) {

		if ( !current_user_can( 'edit_user', $member_id ) )
			return false;

		if( isset( $_POST['membership_id'] ) && isset( $_POST['credits'] ) && isset( $_POST['membership_date'] ) ) :

			$old_membership_id = get_user_meta( $member_id, 'fitpress_membership_id', true );
			$old_credits = get_user_meta( $member_id, 'fitpress_credits', true );
			$old_membership_date = get_user_meta( $member_id, 'fitpress_membership_date', true );

			$membership_id = $_POST['membership_id'];

			$credits = $_POST['credits'];

			$membership_date = $_POST['membership_date'];

			if( $old_membership_id != $membership_id && ( $_POST['update_credits'] == 1 || !$old_membership_id || $old_membership_id == 0 ) ):

				$credits = FP_Credit::update_member_credits( $_POST['membership_id'], $old_membership_id, $old_credits );

			endif;

			do_action( 'fitpress_before_membership_profile_save', array( 'member_id' => $member_id, 'old_membership_id' => $old_membership_id ) );

			update_user_meta( $member_id, 'fitpress_membership_id', $membership_id, $old_membership_id );
			update_user_meta( $member_id, 'fitpress_credits', $credits, $old_credits );
			update_user_meta( $member_id, 'fitpress_membership_date', $membership_date, $old_membership_date );

		endif;

		update_user_meta( $member_id, 'contact_number', $_POST['contact_number'] );
		update_user_meta( $member_id, 'emergency_contact_name', $_POST['emergency_contact_name'] );
		update_user_meta( $member_id, 'emergency_contact_number', $_POST['emergency_contact_number'] );

	}

	public static function quick_member_add( $member_id = null, $membership_id = null ) {

		if( !$member_id || !$membership_id  )
			return;

		$credits = FP_Credit::update_member_credits( $membership_id, 0, 0 );

		do_action( 'fitpress_before_membership_profile_save', array( 'member_id' => $member_id, 'old_membership_id' => false ) );

		update_user_meta( $member_id, 'fitpress_membership_id', $membership_id );
		update_user_meta( $member_id, 'fitpress_credits', $credits );

	}

	public static function get_memberships( $select = false ){

		$args = array(
			'post_type' => 'membership',
			'orderby' => 'post_title',
			'order' => 'ASC',
			'posts_per_page' => '-1'
		);

		$memberships_obj = new WP_Query( $args );

		if($memberships_obj->have_posts()):

			$memberships = array();

			if( $select )
				$memberships = array( 0 => 'None' );

			foreach( $memberships_obj->posts as $membership ):

				if( $select ):

					$memberships[ $membership->ID ] = $membership->post_title;

				else:

					$membership_data = get_post_meta( $membership->ID, "membership_data", true);

					$memberships[ $membership->ID ] = array(
						'name' => $membership->post_title,
					);

					foreach( $membership_data as $key => $value ):

						$memberships[ $membership->ID ][$key] = $value;

					endforeach;

				endif;

			endforeach;

			return $memberships;

		else:

			return false;

		endif;

	}

	public static function get_membership( $membership_ids = array() ){

		$args = array(
			'post_type' => 'membership',
			'orderby' => 'post_title',
			'order' => 'ASC',
			'post__in' => $membership_ids,
		);

		$memberships_obj = new WP_Query( $args );

		if($memberships_obj->have_posts()):

			$memberships = array();

			foreach( $memberships_obj->posts as $membership ):

				$membership_data = get_post_meta( $membership->ID, "membership_data", true);

				$memberships[ $membership->ID ] = array(
					'name' => $membership->post_title
				);

				foreach( $membership_data as $key => $value ):

					$memberships[ $membership->ID ][$key] = $value;

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
	 * @param Bool  $none_members Set to true to return none members.
	 * @param Mixed $search Set to string to search for a specific member.
	 */
	public static function get_members( $fields = 'ID', $none_members = false, $search = false ) {

		if ( $search ) :
			$search = '*' . esc_attr( $search ) . '*';
		endif;

		if ( $none_members ) :

			if ( $search ) :

				$args = array(
					'meta_query' => array(
						array(
							'key' => 'fitpress_membership_status',
							'value' => 'active',
							'compare' => '!=',
						),
					),
					'search' => $search,
					'fields' => $fields,
				);

			else :

				$args = array(
					'meta_query' => array(
						array(
							'key' => 'fitpress_membership_status',
							'value' => 'active',
							'compare' => '!=',
						),
					),
					'fields' => $fields,
				);

			endif;

		else :

			if ( $search ) :

				$args = array(
					'meta_query' => array(
						array(
							'key' => 'fitpress_membership_status',
							'value' => 'active',
							'compare' => '=',
						),
					),
					'search' => $search,
					'fields' => $fields,
				);

			else :

				$args = array(
					'meta_query' => array(
						array(
							'key' => 'fitpress_membership_status',
							'value' => 'active',
							'compare' => '=',
						),
					),
					'fields' => $fields,
				);

			endif;

		endif;

		$member_query = new WP_User_Query( $args );

		return $member_query->get_results();

	}

	public static function get_user_membership( $user_id ) {
		$membership_id = get_user_meta( $user_id, 'fitpress_membership_id', true );

		if ( ! $membership_id ) :
			return 'None';
		else :
			$membership = FP_Membership::get_membership( array( $membership_id ) );
			return $membership[ $membership_id ];
		endif;
	}

}

/**
 * Extension main function
 */
function __fp_membership_main() {
	FP_Membership::get_instance();
}

// Initialize plugin when plugins are loaded
add_action( 'plugins_loaded', '__fp_membership_main' );
