<?php
/**
 * Membership Statuses
 *
 * Set and get membership status
 *
 * @class     FP_Membership_Status
 * @version   2.5.0
 * @package   FitPress/Classes/FP_Membership_Status
 * @category  Class
 * @author    Digital Leap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FP_Post_Types Class.
 */
class FP_Membership_Status {

    /* We only want a single instance of this class. */
    private static $instance = null;

    protected $status = null;

    protected $member_id = null;

    /*
    * Creates or returns an instance of this class.
    *
    * @return  FP_Membership A single instance of this class.
    */
    public static function get_instance( ) {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    } // end get_instance;

	/**
	 * Hook in methods.
	 */
    public function __construct( $member_id = null ){

    	if ( $member_id ) :
    		$this->set_member_id( $member_id );
    	endif;

		add_action( 'fitpress_after_membership_profile_fields', array( $this, 'show_membership_statuses' ), 2 );

		add_action( 'personal_options_update', array( $this, 'save_membership_status_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_membership_status_fields' ) );

	}

	public function get_statuses( ){

		$statuses = array(
			'active'    => 'Active',
			'suspended' => 'Suspended',
			'cancelled' => 'Cancelled',
			'on-hold'   => 'On-hold',
			'no-membership'   => 'No Membership',
		);

		return $statuses;

	}

	public function get_status( ){

		if ( ! $this->status ) :
			$this->status = get_user_meta( $this->member_id, 'fitpress_membership_status', true );
		endif;

		return $this->status;

	}

	public function set_status( $status ){

		update_user_meta( $this->member_id, 'fitpress_membership_status', $status, $this->get_status( ) );

		$this->status = $status;

	}

	public function get_member_id( ){
		return $this->member_id;
	}

	public function set_member_id( $member_id ){
		$this->member_id = $member_id;
	}

	function show_membership_statuses( $user_id ) {

		$this->set_member_id( $user_id );
		$this->get_status( );

		if ( ! $this->status ) :
			$this->set_status( 'no-membership' );
		endif;

		?>

		<tr>
		<th><label for="membership_status">Membership Status</label></th>

		<td>
			<select name="membership_status" id="membership_status">
			<?php foreach( $this->get_statuses() as $key => $status ):?>
				<option value="<?php echo $key;?>" <?php echo selected( $key, $this->status );?>><?php echo $status;?></option>
			<?php endforeach;?>
			</select>
		</td>
		</tr>

	<?php

	}

	function save_membership_status_fields( $member_id ) {

		if ( ! current_user_can( 'edit_user', $member_id ) ) :
			return false;
		endif;

		if ( isset( $_POST['membership_status'] ) ) :

			$this->set_member_id( $member_id );

			$this->set_status( $_POST['membership_status'] );

		endif;

	}

}

/**
 * Extension main function
 */
function __fp_membership_status_main() {
    FP_Membership_Status::get_instance();
}

// Initialize plugin when plugins are loaded
add_action( 'plugins_loaded', '__fp_membership_status_main' );
