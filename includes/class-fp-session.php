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

	public static $book_ahead = 7;

	public static $holidays = array();

	/**
	 * Hook in methods.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register_post_types' ), 5 );

		if ( is_admin() ) {
			add_action( 'load-post.php', array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
		}

		if ( ! wp_get_schedule( 'add_sessions_hook' ) ):
			$start = strtotime( 'tomorrow' );
			wp_schedule_event( $start, 'daily', 'add_sessions_hook' );
		endif;

		add_action( 'add_sessions_hook', __CLASS__ . '::add_sessions' );

	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {
		if ( post_type_exists( 'fp_session' ) ) {
			return;
		}

		do_action( 'action_register_post_type' );

		register_post_type( 'fp_session',
			array(
				'labels'              => array(
					'name'                  => __( 'Sessions', 'fitpress' ),
					'singular_name'         => __( 'Session', 'fitpress' ),
					'menu_name'             => _x( 'Sessions', 'Admin menu name', 'fitpress' ),
					'add_new'               => __( 'Add Session', 'fitpress' ),
					'add_new_item'          => __( 'Add New Session', 'fitpress' ),
					'edit'                  => __( 'Edit', 'fitpress' ),
					'edit_item'             => __( 'Edit Session', 'fitpress' ),
					'new_item'              => __( 'New Session', 'fitpress' ),
					'view'                  => __( 'View Session', 'fitpress' ),
					'view_item'             => __( 'View Session', 'fitpress' ),
					'search_items'          => __( 'Search Products', 'fitpress' ),
					'not_found'             => __( 'No Sessions found', 'fitpress' ),
					'not_found_in_trash'    => __( 'No Sessions found in trash', 'fitpress' ),
					'parent'                => __( 'Parent Session', 'fitpress' ),
					'featured_image'        => __( 'Session Image', 'fitpress' ),
					'set_featured_image'    => __( 'Set session image', 'fitpress' ),
					'remove_featured_image' => __( 'Remove session image', 'fitpress' ),
					'use_featured_image'    => __( 'Use as session image', 'fitpress' ),
				),
				'description'         => __( 'This is where you can add new products to your store.', 'fitpress' ),
				'public'              => false,
				'show_ui'             => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => false,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'has_archive'         => false,
				'show_in_nav_menus'   => true,
				'supports'            => array( 'title' ),
				'show_in_menu'        => 'fitpress',
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
			'session-info',
			__( 'Session Information', 'fitpress' ),
			array( $this, 'render_session_metabox' ),
			'fp_session',
			'advanced',
			'default'
		);

	}

	/**
	 * Renders the meta box.
	 *
	 * @param $post
	 */
	public function render_session_metabox( $post ) {
		// Add nonce for security and authentication.
		wp_nonce_field( FP_PLUGIN_FILE, 'session_nonce' );

		$start_time = get_post_meta( $post->ID, "_fp_start_time", true );
		$date       = ( $start_time ) ? date( 'l, j F Y', $start_time ) : date( 'l, j F Y' );
		$end_time   = get_post_meta( $post->ID, "_fp_end_time", true );
		$class_id   = get_post_meta( $post->ID, "_fp_class_id", true );

		$args = array(
			'post_type' => 'fp_class',
			'orderby'   => 'post_title',
			'order'     => 'ASC'
		);

		$classes = new WP_Query( $args );

		$bookings = FP_Booking::get_booked_sessions( array( 'session_id' => $post->ID ) );

		?>
        <p>
            <label for="class"></label>
            <select name="class_id">
				<?php foreach ( $classes->posts as $class ): ?>
                    <option value="<?php echo $class->ID; ?>" <?php selected( $class->ID, $class_id ); ?>><?php echo $class->post_title; ?></option>
				<?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="date">Date</label>
            <input type="text" name="date" value="<?php echo ( $date ) ? $date : ''; ?>"/>
        </p>
        <p>
            <label for="start-time">Start Time</label>
            <input placeholder="00:00" name="start_time" type="text"
                   value="<?php echo ( $start_time ) ? date( 'H:i', $start_time ) : ''; ?>" class="small-text">
        </p>
        <p>
            <label for="end-time">End Time</label>
            <input placeholder="00:00" name="end_time" type="text"
                   value="<?php echo ( $end_time ) ? date( 'H:i', $end_time ) : ''; ?>" class="small-text">
        </p>
        <h3>Members Booked</h3>
		<?php if ( ! empty( $bookings ) ): ?>

            <ol>
				<?php foreach ( $bookings as $booking ): ?>
                    <li>
                        <a href="<?php echo get_edit_user_link( $booking['user']->ID ); ?>"><?php echo $booking['user']->display_name; ?></a>
						<?php echo $booking['action']; ?>
                    </li>
				<?php endforeach; ?>
            </ol>

		<?php else: ?>
            <p>No members have booked yet.</p>
		<?php endif; ?>
        <p>
            <label for="add_member">Add Member</label>
            <select class="find-member-search" name="add_member[]" multiple="multiple">
                <option></option>
            </select>
        </p>
		<?php
	}

	/**
	 * Handles saving the meta box.
	 *
	 * @param int $post_id Post ID.
	 * @param WP_Post $post Post object.
	 *
	 * @return null
	 */
	public function save_session_metabox( $post_id, $post ) {

		// Check if nonce is set.
		if ( $post->post_type != 'fp_session' ) {
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

		$start_time = get_post_meta( $post->ID, "_fp_start_time", true );
		$end_time   = get_post_meta( $post->ID, "_fp_end_time", true );
		$class_id   = get_post_meta( $post->ID, "_fp_class_id", true );

		if ( isset( $_POST["start_time"] ) ) {
			$start_time = strtotime( $_POST["date"] . ' ' . $_POST["start_time"] );
		}

		if ( isset( $_POST["end_time"] ) ) {
			$end_time = strtotime( $_POST["date"] . ' ' . $_POST["end_time"] );
		}

		if ( isset( $_POST["class_id"] ) ) {
			$class_id = $_POST["class_id"];
		}

		if ( isset( $_POST["add_member"] ) && ! empty( $_POST["add_member"] ) ):

			//$member_ids = array_map( 'trim', explode( ',', $_POST["add_member"] ) );
			$member_ids = $_POST["add_member"];

			FP_Booking::add_booking( $post_id, $member_ids );

		endif;

		$args = array(
			'ID'         => $post_id,
			'post_title' => get_the_title( $class_id ) . ': ' . $_POST['date'] . ' (' . date( 'H:i', $start_time ) . ') - (' . date( 'H:i', $end_time ) . ')',
		);

		remove_action( 'save_post', array( $this, 'save_session_metabox' ), 10 );
		wp_update_post( $args );
		remove_action( 'save_post', array( $this, 'save_session_metabox' ), 10 );

		update_post_meta( $post_id, "_fp_start_time", $start_time );
		update_post_meta( $post_id, "_fp_end_time", $end_time );
		update_post_meta( $post_id, "_fp_class_id", $class_id );

	}

	public static function add_sessions( $start_day = null, $class_id = null ) {

		if ( ! $start_day ) :
			$start_day = strtotime( '+' . self::$book_ahead . ' days' );
		endif;

		$end_day = strtotime( '+1 second', strtotime( '+' . self::$book_ahead . ' days' ) );

		self::$holidays = get_option( 'fitpress_holidays' );

		if ( $class_id ):

			$args = array(
				'post_type' => 'fp_class',
				'p'         => $class_id
			);

		else:

			$args = array(
				'post_type' => 'fp_class'
			);

		endif;

		$classes = new WP_Query( $args );

		if ( ! empty( $classes->posts ) ):

			foreach ( $classes->posts as $class ):

				$class_id = $class->ID;

				$current_day = $start_day;

				while ( $current_day < $end_day ):

					$day_of_week = strtolower( date( 'l', $current_day ) );
					$short_date  = date( 'j F', $current_day );
					$year        = date( 'Y', $current_day );

					date_default_timezone_set( 'UTC' );

					$easter_object = new DateTime( '@' . easter_date( $year ) );
					$easter_object->setTimezone( new DateTimeZone( wp_get_timezone_string() ) );

					$easter = $easter_object->format( 'j F' );

					$easter_object->add( DateInterval::createFromDateString( '+1 days' ) );
					$family_day = $easter_object->format( 'j F' );

					$easter_object->add( DateInterval::createFromDateString( '-3 days' ) );
					$good_friday = $easter_object->format( 'j F' );

					$sunday_public_holiday = $short_date;

					if ( $day_of_week == 'monday' ) :
						$sunday_public_holiday = date( 'j F', strtotime( '-1 day', $current_day ) );
					endif;

					if (
						! array_key_exists( $short_date, self::$holidays )
						&& ! array_key_exists( $sunday_public_holiday, self::$holidays )
						&& $short_date != $easter
						&& $short_date != $easter
						&& $short_date != $family_day
						&& $short_date != $good_friday
					):

						$args = array(
							'post_type'  => 'fp_class_time',
							'meta_query' => array(
								array(
									'key'   => 'fp_class_id',
									'value' => $class_id,
									'type'  => 'NUMERIC'
								)
							),
							'tax_query'  => array(
								array(
									'taxonomy' => 'fp_day',
									'field'    => 'slug',
									'terms'    => array( $day_of_week )
								)
							),
						);

						$class_times = new WP_Query( $args );

						if ( ! empty( $class_times->posts ) ) {
							self::create_session_posts( $class_id, $class_times->posts, $current_day );
						}

					endif;

					// moves the counter 1 day
					$current_day = strtotime( 'midnight tomorrow', $current_day );

				endwhile;

			endforeach;

		endif;

	}

	public static function create_session_posts( $class_id, $class_times, $current_day ) {

		foreach ( $class_times as $class_time ):

			$date = date( 'l, j F Y', $current_day );

			$class_time_info = get_post_meta( $class_time->ID, 'fp_class_time_info', true );

			if ( isset ( $class_time_info['blocks'] ) && $class_time_info['blocks'] != 'none' ):

				$session_start = $class_time_info['start_time'];
				$session_end   = date( 'H:i', strtotime( $class_time_info['blocks'], strtotime( $session_start ) ) );

				while ( $session_end <= $class_time_info['end_time'] ) :

					$session_data = array(
						'class_id'      => $class_id,
						'start_time'    => $session_start,
						'end_time'      => $session_end,
						'date'          => $date,
						'class_time_id' => $class_time->ID,
					);

					self::create_session_post( $session_data );

					$session_start = date( 'H:i', strtotime( $class_time_info['blocks'], strtotime( $session_start ) ) );
					$session_end   = date( 'H:i', strtotime( $class_time_info['blocks'], strtotime( $session_end ) ) );

				endwhile;

			else:

				$session_data = array(
					'class_id'      => $class_id,
					'start_time'    => $class_time_info['start_time'],
					'end_time'      => $class_time_info['end_time'],
					'date'          => $date,
					'class_time_id' => $class_time->ID,
				);

				self::create_session_post( $session_data );

			endif;

		endforeach;

	}

	public static function create_session_post( $session_data ) {

		$fitness_session_post = array(
			'post_title'     => get_the_title( $session_data['class_id'] ) . ': ' . $session_data['date'] . ' (' . $session_data['start_time'] . ' - ' . $session_data['end_time'] . ')',
			'post_status'    => 'publish',
			'ping_status'    => 'closed',
			'comment_status' => 'closed',
			'post_type'      => 'fp_session',
		);

		remove_action( 'save_post', 'FP_Class::save_session_metabox' );
		$fitness_session_post_id = wp_insert_post( $fitness_session_post );
		add_action( 'save_post', 'FP_Class::save_session_metabox' );

		if ( $fitness_session_post_id ):;

			update_post_meta( $fitness_session_post_id, '_fp_class_id', $session_data['class_id'] );
			update_post_meta( $fitness_session_post_id, '_fp_class_time_id', $session_data['class_time_id'] );
			update_post_meta( $fitness_session_post_id, '_fp_start_time', strtotime( $session_data['date'] . ' ' . $session_data['start_time'] ) );
			update_post_meta( $fitness_session_post_id, '_fp_end_time', strtotime( $session_data['date'] . ' ' . $session_data['end_time'] ) );

		endif;

	}

	public static function get_session() {

		$args = array(
			'post_type'      => 'fp_session',
			'meta_query'     => array(
				array(
					'key'     => '_fp_start_time',
					'value'   => current_time( 'timestamp' ),
					'compare' => '>',
					'type'    => 'numeric',
				),
			),
			'posts_per_page' => - 1,
			'order'          => 'ASC',
			'orderby'        => 'meta_value_num',
			'meta_key'       => '_fp_start_time',
		);

		return new WP_Query( $args );

	}

	public static function get_sessions( $start_time = null, $end_time = null, $fields = 'all' ) {

		if ( ! $start_time ) {
			$start_time = strtotime( 'today midnight' );
		}

		if ( ! $end_time ) {
			$end_time = strtotime( 'tomorrow midnight', $start_time );
		}

		$args = array(
			'post_type'      => 'fp_session',
			'meta_query'     => array(
				array(
					'key'     => '_fp_start_time',
					'value'   => array(
						$start_time,
						$end_time
					),
					'compare' => 'BETWEEN',
					'type'    => 'numeric',
				),
			),
			'posts_per_page' => - 1,
			'order'          => 'ASC',
			'orderby'        => 'meta_value_num',
			'meta_key'       => '_fp_start_time',
			'fields'         => $fields,
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
