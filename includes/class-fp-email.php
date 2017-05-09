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
class FP_Email {

	public $template_html = 'email/default.php';

	public $headers = array('Content-Type: text/html; charset=UTF-8');

	protected $email_settings = array();

	public function __construct( $setup = array() ){

		if ( isset( $setup['template'] ) ) :
			$this->template_html = $setup['template'];
		else :
			$this->template_html = 'email/default.php';
		endif;

		if ( isset( $setup['headers'] ) ) :
			$this->headers = array_merge( $this->headers, $setup['headers'] );
		endif;

		$this->email_settings = get_option( 'fitpress_email_settings' );

		add_filter( 'wp_mail_from', array( $this, 'email_from' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'email_from_name' ) );

	}

	public function send_email( $to, $subject = 'Test', $data = array(), $attachments = null){

		$data = array_merge( $data, array( 'email_settings' => $this->email_settings ) );

		$content = $this->get_content_html( $data );

		$admin_email = get_bloginfo( 'admin_email' );

		if ( $to != $admin_email ) :
			$this->headers = array_merge( $this->headers, array( 'Cc: ' . $admin_email, 'Bcc: admin@flickerleap.com' ) );
		else :
			$this->headers = array_merge( $this->headers, array( 'Bcc: admin@flickerleap.com' ) );
		endif;

		wp_mail( $to, $subject, $content, $this->headers, $attachments );

	}

	function email_from( $old ) {

		if ( ! isset( $this->email_settings['from_address'] ) ) {
			$this->email_settings['from_address'] = 'info@fitpress.co.za';
		}

		return $this->email_settings['from_address'];

	}

	function email_from_name( $old ) {

		if ( ! isset( $this->email_settings['from_name'] ) ) {
			$this->email_settings['from_name'] = 'FitPress';
		}

		return $this->email_settings['from_name'];

	}

	/**
	* get_content_html function.
	*
	* @since 0.1
	* @return string
	*/
	public function get_content_html( $data ) {
		ob_start();
			fp_get_template( $this->template_html, $data );
		return ob_get_clean();
	}

}
