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
class FP_Classes {

	/**
	 * Hook in methods.
	 */
    public function __construct(){
		add_action( 'init', array( $this, 'register_post_types' ), 5 );
    	add_action( 'init', array( $this, 'register_taxonomy') );

        if ( is_admin() ) {
            add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
            add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
        }
	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {
		if ( post_type_exists('fp_class') ) {
			return;
		}

		do_action( 'action_register_post_type' );

		register_post_type( 'fp_class',
			array(
				'labels'             => array(
					'name'                  => __( 'Classes', 'fitpress' ),
					'singular_name'         => __( 'Class', 'fitpress' ),
					'menu_name'             => _x( 'Classes', 'Admin menu name', 'fitpress' ),
					'add_new'               => __( 'Add Class', 'fitpress' ),
					'add_new_item'          => __( 'Add New Class', 'fitpress' ),
					'edit'                  => __( 'Edit', 'fitpress' ),
					'edit_item'             => __( 'Edit Class', 'fitpress' ),
					'new_item'              => __( 'New Class', 'fitpress' ),
					'view'                  => __( 'View Class', 'fitpress' ),
					'view_item'             => __( 'View Class', 'fitpress' ),
					'search_items'          => __( 'Search Products', 'fitpress' ),
					'not_found'             => __( 'No Classes found', 'fitpress' ),
					'not_found_in_trash'    => __( 'No Classes found in trash', 'fitpress' ),
					'parent'                => __( 'Parent Class', 'fitpress' ),
					'featured_image'        => __( 'Class Image', 'fitpress' ),
					'set_featured_image'    => __( 'Set class image', 'fitpress' ),
					'remove_featured_image' => __( 'Remove class image', 'fitpress' ),
					'use_featured_image'    => __( 'Use as class image', 'fitpress' ),
				),
				'description'         => __( 'This is where you can add new classes to your website.', 'fitpress' ),
				'public'              => false,
				'show_ui'             => true,
				'publicly_queryable'  => false,
				'exclude_from_search' => false,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title' ),
				'has_archive'         => false,
				'show_in_nav_menus'   => true
			)
		);

		register_post_type( 'fp_class_time',
			array(
				'description'         => __( 'This is where you can add new products to your store.', 'fitpress' ),
				'public'              => false,
				'show_ui'             => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => false,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'has_archive'         => false,
				'show_in_nav_menus'   => false,
			)
		);

		register_post_type( 'fp_session',
			array(
				'labels'             => array(
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
			)
		);
	}

	/**
	 * Register a post type.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/register_post_type
	 */
	function register_taxonomy() {
		$labels = array(
			'name'              => _x( 'FitPress Days', 'taxonomy general name' ),
			'singular_name'     => _x( 'FitPress Day', 'taxonomy singular name' ),
			'search_items'      => __( 'Search FitPress Days' ),
			'all_items'         => __( 'All FitPress Days' ),
			'parent_item'       => __( 'Parent FitPress Day' ),
			'parent_item_colon' => __( 'Parent FitPress Day:' ),
			'edit_item'         => __( 'Edit FitPress Day' ),
			'update_item'       => __( 'Update FitPress Day' ),
			'add_new_item'      => __( 'Add New FitPress Day' ),
			'new_item_name'     => __( 'New FitPress Day Name' ),
			'menu_name'         => __( 'FitPress Day' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => false,
			'show_admin_column' => false,
			'query_var'         => true,
		);

		register_taxonomy( 'fp_day', array( 'fp_class', 'fp_class_time', 'fp_session' ), $args );

		if( !term_exists( 'Monday', 'fp_day' ) ):

			wp_insert_term( 'Monday', 'fp_day' );
			wp_insert_term( 'Tuesday', 'fp_day' );
			wp_insert_term( 'Wednesday', 'fp_day' );
			wp_insert_term( 'Thursday', 'fp_day' );
			wp_insert_term( 'Friday', 'fp_day' );
			wp_insert_term( 'Saturday', 'fp_day' );
			wp_insert_term( 'Sunday', 'fp_day' );

		endif;

	}
 
    /**
     * Meta box initialization.
     */
    public function init_metabox() {
        add_action( 'add_meta_boxes', array( $this, 'add_metabox'  )        );
        add_action( 'save_post',      array( $this, 'save_class_metabox' ), 10, 2 );
        add_action( 'save_post',      array( $this, 'save_session_metabox' ), 10, 2 );
    }
 
    /**
     * Adds the meta box.
     */
    public function add_metabox() {

        add_meta_box(
            'class-info',
            __( 'Class Information', 'fitpress' ),
            array( $this, 'render_class_metabox' ),
            'fp_class',
            'advanced',
            'default'
        );
    	
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
     */
    public function render_class_metabox( $post ) {
        // Add nonce for security and authentication.
        wp_nonce_field( FP_PLUGIN_FILE, 'class_nonce' );

        $class_info = get_post_meta( $post->ID, "fp_class_info", true );

    	?>
        <p>
            <label for="limit">Limit</label>
            <input name="limit" type="text" value="<?php echo isset( $class_info['limit'] ) ? $class_info['limit'] : ''; ?>" class="regular-text">
        </p>
   		<?php

   		$days = get_terms( array(
   			'taxonomy' => 'fp_day',
			'hide_empty' => false,
			'orderby' => 'term_id',
			'get' => 'all'
		) );

   		$args = array(
   			'post_type' => 'fp_class_time',
   			'meta_query' => array(
   				array(
   					'key' => 'fp_class_id',
   					'value' => $post->ID
   				),
   			),
   			'orderby' => 'post_title',
   			'order' => 'ASC'
   		);

   		$class_times = new WP_Query( $args );

   		if( $class_times->have_posts() ):

   			foreach( $class_times->posts as $class_time ):

   				$class_time_info = get_post_meta( $class_time->ID, 'fp_class_time_info', true );

   				$class_time_term_ids = wp_get_post_terms( $class_time->ID, 'fp_day', array( 'fields' => 'ids' ) );

   				?>

				<div class="class-time">
					<p>
					Start Time <input name="class_times[<?php echo $class_time->ID;?>][start_time]" type="text" value="<?php echo isset( $class_time_info['start_time'] ) ? $class_time_info['start_time'] : ''; ?>" class="class-start-time" placeholder="00:00" /> - 
					<input name="class_times[<?php echo $class_time->ID;?>][end_time]" type="text" value="<?php echo isset( $class_time_info['end_time'] ) ? $class_time_info['end_time'] : ''; ?>" class="class-end-time" placeholder="00:00" /> End Time (Delete? <input type="checkbox" name="class_times[<?php echo $class_time->ID;?>][delete]"  class="regular-check" value="1" />)
					</p>
					<p>
					<?php foreach($days as $day):?>
						<?php echo $day->name;?> <input type="checkbox" name="class_time_days[<?php echo $class_time->ID;?>][]" <?php if( in_array( $day->term_id, $class_time_term_ids ) ) echo 'checked="checked"';?> class="regular-check class-day" value="<?php echo $day->term_id;?>" /> 
					<?php endforeach;?>
					</p>
				</div>

   				<?php

   			endforeach;

   		else:

			?>

			<div class="class-time">
				<p>
				Start Time <input name="class_times[0][start_time]" type="text" value="" class="class-start-time" placeholder="00:00" /> - 
				<input name="class_times[0][end_time]" type="text" value="" class="class-start-time" placeholder="00:00" /> End Time (Delete? <input type="checkbox" name="class_times[0][delete]"  class="regular-check" value="1" />)
				</p>
				<p>
				<?php foreach($days as $day):?>
					<?php echo $day->name;?> <input type="checkbox" name="class_time_days[0][]" class="regular-check class-day" value="<?php echo $day->term_id;?>" /> 
				<?php endforeach;?>
				</p>
			</div>

			<?php

   		endif;

   		?>
   		<p><a class=" add-class-time">Add Class Time</a></p>
   		<?php

    }
 
    /**
     * Renders the meta box.
     */
    public function render_session_metabox( $post ) {
        // Add nonce for security and authentication.
        wp_nonce_field( FP_PLUGIN_FILE, 'session_nonce' );

        $start_time = get_post_meta( $post->ID, "_fp_start_time", true ); 
        $date = ($start_time) ? date( 'l, j F Y', $start_time ) : date( 'l, j F Y' );
        $end_time = get_post_meta( $post->ID, "_fp_end_time", true ); 
        $class_id = get_post_meta( $post->ID, "_fp_class_id", true ); 

		$args = array(
			'post_type'  => 'fp_class',
			'orderby' => 'post_title',
			'order' => 'ASC'
		);

		$classes = new WP_Query( $args );

		$bookings = FP_Booking::get_booked_sessions( array( 'session_id' => $post->ID ) );

    	?>
		<p>
			<label for="class"></label>
			<select name="class_id">
			<?php foreach( $classes->posts as $class):?>
				<option value="<?php echo $class->ID;?>" <?php selected($class->ID, $class_id);?>><?php echo $class->post_title;?></option>
			<?php endforeach;?>
			</select>
		</p>
		<p>
			<label for="date">Date</label>
			<input type="text" name="date" value="<?php echo ( $date ) ? $date : ''; ?>" />
		</p>
        <p>
            <label for="start-time">Start Time</label>
            <input placeholder="00:00" name="start_time" type="text" value="<?php echo ( $start_time ) ? date( 'H:i', $start_time ) : ''; ?>" class="small-text">
        </p>
        <p>
            <label for="end-time">End Time</label>
            <input placeholder="00:00" name="end_time" type="text" value="<?php echo ( $end_time ) ? date( 'H:i', $end_time ) : ''; ?>" class="small-text">
        </p>
        <h3>Members Booked</h3>
        <?php if( !empty( $bookings ) ):?>

        	<ol>
        		<?php foreach( $bookings as $booking ):?>
        		<li>
        			<?php echo $booking['user']->display_name;?>
        			<?php echo $booking['action'];?>
        		</li>
        		<?php endforeach;?>
        	</ol>

    	<?php else:?>
    		<p>No members have booked yet.</p>
    	<?php endif;?>
        <?php
    }
 
    /**
     * Handles saving the meta box.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     * @return null
     */
    public function save_class_metabox( $post_id, $post ) {
        // Add nonce for security and authentication.
        $nonce_name   = isset( $_POST['class_nonce'] ) ? $_POST['class_nonce'] : '';
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
	   	
	   	$class_info = get_post_meta( $post_id , "fp_class_info", true); 

	    if( isset( $_POST["class_times"] ) && isset( $_POST['class_time_days'] ) ){
		        
	        remove_action( 'save_post',      array( $this, 'save_class_metabox' ), 10, 2 );
	        remove_action( 'save_post',      array( $this, 'save_session_metabox' ), 10, 2 );

	    	foreach( $_POST['class_times'] as $class_time_id => $class_times ):

	    		if( $class_time_id > 0 && !isset( $_POST['class_times'][$class_time_id]['delete'] ) ):

	    			$class_time_info = get_post_meta( $class_time_id, 'fp_class_time_info', true); 
      				$class_time_term_ids = wp_get_post_terms( $class_time_id, 'fp_day', array( 'fields' => 'ids' ) );

	    			$post = array(
	    				'ID' => $class_time_id,
	    				'post_title' => 'FitPress Class Time ' . ( isset($_POST['class_times'][$class_time_id]["start_time"]) ? $_POST['class_times'][$class_time_id]["start_time"]: '' ) . ( isset($_POST['class_times'][$class_time_id]["end_time"]) ? ' - ' . $_POST['class_times'][$class_time_id]["end_time"] : '' ),
	    			);

				    if(isset($_POST['class_times'][$class_time_id]["start_time"])){
				        $class_time_info['start_time'] = $_POST['class_times'][$class_time_id]["start_time"];
				    }

				    if(isset($_POST['class_times'][$class_time_id]["end_time"])){
				        $class_time_info['end_time'] = $_POST['class_times'][$class_time_id]["end_time"];
				    }

	    			wp_update_post( $post );

	    			update_post_meta( $class_time_id, 'fp_class_time_info', $class_time_info );

	    			if( isset( $_POST['class_time_days'][$class_time_id] ) ):

	    				$days = array_map( 'intval', $_POST['class_time_days'][$class_time_id] );
						$days = array_unique( $days );

						wp_set_object_terms( $class_time_id, $days, 'fp_day' );

	    			endif;

	    		elseif( $class_time_id <= 0 && !isset( $_POST['class_times'][$class_time_id]['delete'] )  ):

	    			$post = array(
	    				'post_title' => 'FitPress Class Time ' . ( isset($_POST['class_times'][$class_time_id]["start_time"]) ? $_POST['class_times'][$class_time_id]["start_time"]: '' ) . ( isset($_POST['class_times'][$class_time_id]["end_time"]) ? ' - ' . $_POST['class_times'][$class_time_id]["end_time"] : '' ),
	    				'post_type' => 'fp_class_time',
	    				'post_status' => 'publish'
	    			);

				    if(isset($_POST['class_times'][$class_time_id]["start_time"])){
				        $class_time_info['start_time'] = $_POST['class_times'][$class_time_id]["start_time"];
				    }

				    if(isset($_POST['class_times'][$class_time_id]["end_time"])){
				        $class_time_info['end_time'] = $_POST['class_times'][$class_time_id]["end_time"];
				    }

	    			$new_post_id = wp_insert_post( $post );

	    			update_post_meta( $new_post_id, 'fp_class_time_info', $class_time_info );
	    			update_post_meta( $new_post_id, 'fp_class_id', $post_id );

	    			if( isset( $_POST['class_time_days'][$class_time_id] ) ):

	    				$days = array_map( 'intval', $_POST['class_time_days'][$class_time_id] );
						$days = array_unique( $days );

						wp_set_object_terms( $new_post_id, $days, 'fp_day' );

	    			endif;

	    		elseif($class_time_id > 0):

	    			wp_delete_post( $class_time_id, true );

	    		endif;

	    	endforeach;

	        add_action( 'save_post',      array( $this, 'save_class_metabox' ), 10, 2 );
	        add_action( 'save_post',      array( $this, 'save_session_metabox' ), 10, 2 );
	    }

	    if(isset($_POST["limit"])){
	        $class_info['limit'] = $_POST["limit"];
	    }

	    update_post_meta($post_id, "fp_class_info", $class_info);

    }
 
    /**
     * Handles saving the meta box.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     * @return null
     */
    public function save_session_metabox( $post_id, $post ) {
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
        $end_time = get_post_meta( $post->ID, "_fp_end_time", true ); 
        $class_id = get_post_meta( $post->ID, "_fp_class_id", true ); 

	    if(isset($_POST["start_time"])){
	        $start_time = strtotime( $_POST["date"] . ' ' . $_POST["start_time"] );
	    }

	    if(isset($_POST["end_time"])){
	        $end_time = strtotime( $_POST["date"] . ' ' . $_POST["end_time"] );
	    }

	    if(isset($_POST["class_id"])){
	        $class_id = $_POST["class_id"];
	    }

	    $args = array(
      		'ID' => $post_id,
      		'post_title' => get_the_title( $class_id ) . ': ' . $_POST['date'] . ' (' . date( 'H:i', $start_time ) . ') - (' . date( 'H:i', $end_time ) . ')',
	    );

	    remove_action( 'save_post', array( $this, 'save_session_metabox' ), 10, 2 );
	    wp_update_post( $args );
		remove_action( 'save_post', array( $this, 'save_session_metabox' ), 10, 2 );

	    update_post_meta( $post_id, "_fp_start_time", $start_time );
	    update_post_meta( $post_id, "_fp_end_time", $end_time );
	    update_post_meta( $post_id, "_fp_class_id", $class_id );

    }

}

/**
 * Extension main function
 */
function __fp_classes_main() {
    new FP_Classes();
}

// Initialize plugin when plugins are loaded
add_action( 'plugins_loaded', '__fp_classes_main' );
