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
    public function __construct(){

    	add_action( 'admin_menu', array( $this, 'fitpress_dashboard' ), 5 );

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
	        <p>Coming soon!</p>
	    </div>
	    <?php

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
