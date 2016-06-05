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
class FP_Email{

	public $template_html = 'email/default.php';

	public $headers = array('Content-Type: text/html; charset=UTF-8');

	public function __construct( $setup = array() ){

		if( isset( $setup['template'] ) )
			$this->template_html = $setup['template'];

		if( isset( $setup['headers'] ) )
			$this->headers = array_merge( $this->headers, $setup['headers'] );

		add_filter('wp_mail_from', array( $this, 'email_from' ) );
		add_filter('wp_mail_from_name', array( $this, 'email_from_name' ) );

	}

	public function send_email( $to, $subject = 'Test', $data = array(), $attachments = null){

		$content = $this->get_content_html( $data );

		wp_mail( $to, $subject, $content, $this->headers, $attachments );

	}

	function email_from( $old ) {

		return 'heartbeat@crossfitexanimo.co.za';

	}

	function email_from_name( $old ) {

		return get_bloginfo( 'name' );

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