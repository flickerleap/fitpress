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
class FP_Admin {

	/**
	 * Hook in methods.
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'fitpress_dashboard' ), 5 );

		//Setting up columns.
		add_filter( 'manage_users_columns', array( $this, 'user_column_header' ), 10, 1 );
		add_action( 'manage_users_custom_column', array( $this, 'user_column_data' ), 15, 3 );
		add_filter( 'manage_users_sortable_columns', array( $this, 'user_column_sortable' ) );
		add_action( 'pre_get_users', array( $this, 'sort_by_membership' ) );

	}

	public function fitpress_dashboard() {

	    //add an item to the menu
	    add_menu_page (
	        'FitPress Dashboard',
	        'FitPress',
	        'manage_options',
	        'fitpress',
	        array( $this, 'fitpress_dashboard_render' ),
	        'dashicons-heart',
	        '55.77'
	    );

	    //add an item to the menu
	    add_submenu_page (
	    	'fitpress',
	        'FitPress Settings',
	        'Settings',
	        'manage_options',
	        'fp_settings',
	        array( $this, 'fitpress_settings_render' )
	    );

	}

	public function fitpress_dashboard_render(){

		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'todays-bookings';

	    ?>
	    <div class="wrap">
	        <h2>FitPress Dashboard</h2>
	    	<h2 class="nav-tab-wrapper">
			    <a href="?page=fitpress&amp;tab=todays-bookings" class="nav-tab <?php echo $active_tab == 'todays-bookings' ? 'nav-tab-active' : ''; ?>">Today's Bookings</a>
			    <a href="?page=fitpress&amp;tab=tomorrows-bookings" class="nav-tab <?php echo $active_tab == 'tomorrows-bookings' ? 'nav-tab-active' : ''; ?>">Tomorrow's Bookings</a>
			</h2>
			<?php switch( $active_tab ):
				case 'tomorrows-bookings':
					$this->render_day_bookings( strtotime( 'tomorrow midnight' ) );
					break;
				case 'todays-bookings':
				case 'default':
					$this->render_day_bookings( strtotime( 'today midnight' ) );
					break;

			endswitch;?>
	    </div>
	    <?php

	}

	public function render_day_bookings( $start_time ){

		$day_bookings = FP_Booking::get_day_bookings( $start_time );

		if( !empty( $day_bookings ) ):

			foreach( $day_bookings as $session => $bookings ):

				echo '<h3>' . $session . '</h3>';

				if( !empty( $bookings ) ):

					?><ol>
						<?php foreach( $bookings as $booking ):?>
						<li>
							<a href="<?php echo get_edit_user_link( $booking['user']->ID ); ?>"><?php echo $booking['user']->display_name;?></a>
						</li>
						<?php endforeach;?>
					</ol><?php

				else:
					echo '<p>No bookings for this session.</p>';
				endif;

			endforeach;

		else:

			echo '<p>There are no sessions on this day.</p>';

		endif;

	}

	public function fitpress_settings_render(){

	    ?>
	    <div class="wrap">
	        <h2>FitPress Settings</h2>
			<p>Booking and Cancellation Limits</p>
			<?php do_action( '' );?>
	    </div>
	    <?php

	}

	/*
	* Setup Column and data for users page with sortable
	*/
	public static function user_column_header( $column ) {

		$column['membership'] = __( 'Membership', 'fitpress' );

		$column['membership_status'] = __( 'Membership Status', 'fitpress' );

		return $column;

	}

	public function user_column_data( $value, $column_name, $user_id ) {

		if ( 'membership' == $column_name ) :
			return $this->get_membership( $user_id );
		endif;

		if ( 'membership_status' == $column_name ) :
			$membership_status = new FP_Membership_Status();
			$membership_status->set_member_id( $user_id );
			return $membership_status->get_status( );
		endif;

		return $value;

	}

	public function sort_by_membership( $query ) {

		if ( 'membership' == $query->get( 'orderby' ) ) {

			$query->set( 'meta_query', array(
				'relation' => 'OR',
				array(
					'key' => 'fitpress_membership_id',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key' => 'fitpress_membership_id',
				),
			));

			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'meta_key', 'fitpress_membership_id' );

		}

		if ( 'membership_status' == $query->get( 'orderby' ) ) {

			$query->set( 'meta_query', array(
				'relation' => 'OR',
				array(
					'key' => 'fitpress_membership_status',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key' => 'fitpress_membership_status',
				),
			));

			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', 'fitpress_membership_status' );

		}

	}

	public function user_column_sortable( $columns ) {

		$columns['membership'] = 'membership';
		$columns['membership_status'] = 'membership_status';

		return $columns;

	}

	public function get_membership( $user_id ) {
		$membership_id = get_user_meta( $user_id, 'fitpress_membership_id', true );

		if ( ! $membership_id ) :
			return 'None';
		else :
			$membership = FP_Membership::get_membership( array( $membership_id ) );
			return $membership[ $membership_id ]['name'];
		endif;
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
