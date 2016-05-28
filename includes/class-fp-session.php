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
class FP_Session {

	public $book_ahead = 7;

	public $holidays = array();

	/**
	 * Hook in methods.
	 */
    public function __construct(){

		if( !wp_get_schedule('add_sessions_hook') ):
			$start = strtotime( 'tomorrow' );
			wp_schedule_event( $start, 'daily', 'add_sessions_hook' );
		endif;

		add_action('add_sessions_hook', array( $this, 'add_sessions'));

	}

	public function add_sessions( $current_day = null, $class_id = null ){	

		if( !$current_day )
			$current_day = strtotime( '+' . $this->book_ahead . ' days' );
		
		$end_day = strtotime( '+1 day', strtotime( '+' . $this->book_ahead . ' days' )  );

		$this->holidays = get_option( 'fitpress_holidays' );

		if( $class_id ):

			$args = array(
				'post_type'  => 'fp_class',
				'p' => $class_id
			);

		else:

			$args = array(
				'post_type'  => 'fp_class'
			);

		endif;

		$classes = new WP_Query( $args );

		if( !empty( $classes->posts ) ):

			foreach( $classes->posts as $class ):

				$class_id = $class->ID;

				while( $current_day < $end_day ):
					
					$day_of_week = strtolower( date( 'l', $current_day  ) );
					$short_date = date( 'j F', $current_day  );
					$year = date( 'Y', $current_day );
					$easter = date( 'j F', easter_date( $year ) );
					$family_day = date( 'j F', strtotime( '+1 day', easter_date( $year ) ) );
					$good_friday = date( 'j F', strtotime( '-2 days', easter_date( $year ) ) );

					$sunday_public_holiday = $short_date;

					if( $day_of_week == 'monday' )
						$sunday_public_holiday = date( 'j F', strtotime( '-1 day', $current_day ) );

					if(
						!in_array( $short_date, $this->holidays )
						&& !in_array( $sunday_public_holiday, $this->holidays )
						&& $short_date != $easter
						&& $short_date != $easter
						&& $short_date != $family_day
						&& $short_date != $good_friday ):

						$args = array(
							'post_type'  => 'fp_class_time',
							'meta_query' => array(
								array(
									'key' => 'fp_class_id',
									'value' => $class->ID,
									'type' => 'NUMERIC'
								)
							),
							'tax_query' => array(
								array(
									'taxonomy' => 'fp_day',
									'field' => 'slug',
									'terms' => array( $day_of_week )
								)
							),
						);

						$class_times = new WP_Query( $args );

						if( !empty($class_times->posts) )
							self::create_session_posts( $class_id, $class_times->posts, $current_day );

					endif;

					// moves the counter 1 day
					$current_day = strtotime( 'midnight tomorrow', $current_day );

				endwhile;

			endforeach;

		endif;

	}

	public static function create_session_posts( $class_id, $class_times, $current_day ){

		foreach( $class_times as $class_time):

			$date = date( 'l, j F Y', $current_day );

			$class_time_info = get_post_meta( $class_time->ID, 'fp_class_time_info', true); 

			$fitness_session_post = array(
				'post_title'     => get_the_title( $class_id ) . ': ' . $date . ' (' . $class_time_info['start_time'] . ' - ' . $class_time_info['end_time'] . ')',
				'post_status'    => 'publish',
				'ping_status'    => 'closed',
				'comment_status' => 'closed',
				'post_type'      => 'fp_session',
			);

			remove_action( 'save_post', 'FP_Class::save_session_metabox' );
			$fitness_session_post_id = wp_insert_post( $fitness_session_post );
			add_action( 'save_post', 'FP_Class::save_session_metabox' );

			if( $fitness_session_post_id ):;

				update_post_meta( $fitness_session_post_id, '_fp_class_id', $class_id );
				update_post_meta( $fitness_session_post_id, '_fp_class_time_id', $class_time->ID );
				update_post_meta( $fitness_session_post_id, '_fp_start_time', strtotime( $date . ' ' . $class_time_info['start_time']) );
				update_post_meta( $fitness_session_post_id, '_fp_end_time', strtotime( $date . ' ' . $class_time_info['end_time']) );

			endif;
			
		endforeach;

	}

	public static function get_session(){

		$args = array(
			'post_type'  => 'fp_session',
			'meta_query' => array(
				array(
					'key'    => '_fp_start_time',
					'value'  => current_time( 'timestamp' ),
					'compare'=> '>',
					'type'   => 'numeric',
				),
			),
			'posts_per_page' => -1,
			'order'     => 'ASC',
			'orderby'   => 'meta_value_num',
			'meta_key'  => '_fp_start_time',
		);

		return new WP_Query( $args );

	}

}

/**
 * Extension main function
 */
function __fp_session_main() {
    new FP_Session();
}

// Initialize plugin when plugins are loaded
add_action( 'plugins_loaded', '__fp_session_main' );
