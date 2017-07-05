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
 * FP_Membership_Status Class.
 */
class FP_Membership_Status {

	/* We only want a single instance of this class. */
	private static $instance = null;

	protected $status = null;

	protected $membership_id = null;

	/*
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
	public function __construct( $membership_id = null ) {

		if ( $membership_id ) :
			$this->set_membership_id( $membership_id );
		endif;

		add_action( 'fitpress_after_membership_fields', array( $this, 'show_membership_statuses' ), 2 );
		add_action( 'fitpress_after_membership_save', array( $this, 'save_membership_status' ), 2 );

	}

	public function get_statuses() {

		$statuses = array(
			'active'    => 'Active',
			'suspended' => 'Suspended',
			'cancelled' => 'Cancelled',
			'expired'   => 'Expired',
			'on-hold'   => 'On-hold',
		);

		return $statuses;

	}

	public function get_status() {

		if ( ! $this->status ) :
			$this->status = get_post_meta( $this->membership_id, '_fp_membership_status', true );
		endif;

		return $this->status;

	}

	public function set_status( $status ) {

		update_post_meta( $this->membership_id, '_fp_membership_status', $status, $this->get_status() );

		$this->status = $status;

	}

	public function get_membership_id() {
		return $this->membership_id;
	}

	public function set_membership_id( $membership_id ) {
		$this->membership_id = $membership_id;
	}

	function show_membership_statuses( $membership_id ) {

		$this->set_membership_id( $membership_id );
		$this->get_status();

		?>

        <p>
            <label for="membership_status">Membership Status</label>
            <select name="membership_status" id="membership_status">
				<?php foreach ( $this->get_statuses() as $key => $status ) : ?>
                    <option value="<?php echo $key; ?>" <?php echo selected( $key, $this->status ); ?>><?php echo $status; ?></option>
				<?php endforeach; ?>
            </select>
        </p>

		<?php

	}

	function save_membership_status( $membership ) {

		if ( ! current_user_can( 'manage_options', $membership['membership_id'] ) ) :
			return false;
		endif;

		$this->set_membership_id( $membership['membership_id'] );

		if ( isset( $_POST['membership_status'] ) ) :

			$this->set_status( $_POST['membership_status'] );

		else :

			$this->set_status( 'on-hold' );

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
