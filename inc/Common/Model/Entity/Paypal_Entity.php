<?php

namespace GGMP\Common\Model\Entity;

use WP_Error;
use WP_Post;

class Paypal_Entity {

	/**
	 * The paypal account ID
	 */
	public $ID = 0;

	/**
	 * Declare the default properties in WP_Post as we can't extend it
	 * Anything we've declared above has been removed.
	 */
	public $post_author = 0;
	public $post_date = '0000-00-00 00:00:00';
	public $post_date_gmt = '0000-00-00 00:00:00';
	public $post_content = '';
	public $post_title = '';
	public $post_excerpt = '';
	public $post_status = 'publish';
	public $comment_status = 'open';
	public $ping_status = 'open';
	public $post_password = '';
	public $post_name = '';
	public $to_ping = '';
	public $pinged = '';
	public $post_modified = '0000-00-00 00:00:00';
	public $post_modified_gmt = '0000-00-00 00:00:00';
	public $post_content_filtered = '';
	public $post_parent = 0;
	public $guid = '';
	public $menu_order = 0;
	public $post_mime_type = '';
	public $comment_count = 0;
	public $filter;

	public function __construct( $_id ) {

		$paypal    = WP_Post::get_instance( $_id );
		$this->ID  = $_id;
		$this->map = $this->get_meta( 'map' );

		return $this->setup( $paypal );
	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private paypal
	 *
	 * @since 1.0
	 */
	public function __get( $key ) {
		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( [ $this, 'get_' . $key ] );
		} else {
			return new WP_Error( 'ggmp-invalid-paypal', sprintf( esc_html__( 'Can\'t get paypal %s', 'ggmp' ), $key ) );
		}
	}

	/**
	 * Creates a paypal
	 *
	 * @param array $data Array of attributes for a paypal
	 * @return mixed  false if data isn't passed and class not instantiated for creation, or New Download ID
	 * @since 1.0
	 */
	public function create( $data = [] ) {

		if ( $this->id != 0 ) {
			return false;
		}

		$defaults = [
			'post_type'   => 'ggmp_paypal',
			'post_status' => 'draft',
			'post_title'  => esc_html__( 'New Paypal', 'ggmp' ),
		];

		$args = wp_parse_args( $data, $defaults );

		/**
		 * Fired before a paypal is created
		 *
		 * @param array $args The post object arguments used for creation.
		 */
		do_action( 'ggmp_pre_create', $args );

		$id = wp_insert_post( $args, true );

		$paypal = WP_Post::get_instance( $id );

		/**
		 * Fired after a paypal is created
		 *
		 * @param int   $id   The post ID of the created item.
		 * @param array $args The post object arguments used for creation.
		 */
		do_action( 'ggmp_post_create', $id, $args );

		return $this->setup( $paypal );

	}

	/**
	 * Given the paypal data, let's set the variables
	 *
	 * @param WP_Post $paypal The WP_Post object for paypal.
	 * @return bool         If the setup was successful or not
	 * @since  1.0
	 */
	private function setup( $paypal ) {

		if ( ! is_object( $paypal ) ) {
			return false;
		}

		if ( ! $paypal instanceof WP_Post ) {
			return false;
		}

		if ( 'ggmp_paypal' !== $paypal->post_type ) {
			return false;
		}

		foreach ( $paypal as $key => $value ) {
			$this->$key = $value;
		}

		return true;
	}

	/**
	 * Get Job Permerlink
	 *
	 *    return link to detail of the paypal.
	 *
	 * @return string
	 * @since 1.0
	 *
	 */
	public function get_link() {
		return get_permalink( $this->ID );
	}

	/**
	 * Posted Date
	 *
	 * Return create post with format by args,it support type: ago, date
	 *
	 * @return string
	 * @since 1.0
	 *
	 */
	public function get_post_date( $d = '' ) {
		$get_date = $this->post_date;
		if ( '' == $d ) {
			$get_date = mysql2date( get_option( 'date_format' ), $get_date, true );
		} else {
			$get_date = mysql2date( $d, $get_date, true );
		}

		return $get_date;
	}

	/**
	 * Updated Date
	 *
	 * Return create post with format by args,it support type: ago, date
	 *
	 * @return string
	 * @since 1.0
	 *
	 */
	public function get_updated_date( $d = '' ) {
		$get_date = $this->post_modified;
		if ( '' == $d ) {
			$get_date = mysql2date( get_option( 'date_format' ), $get_date, true );
		} else {
			$get_date = mysql2date( $d, $get_date, true );
		}

		return $get_date;
	}


	/**
	 * Gets meta box value
	 *
	 * Return create post with format by args,it support type: ago, date
	 *
	 * @access public
	 * @param $key
	 * @param $single
	 * @return string
	 */
	public function get_meta( $key, $single = true ) {
		return get_post_meta( $this->ID, GGMP_METABOX_PREFIX . $key, $single );
	}

	public function get_status_code() {
		return $this->post_status;
	}

	public function get_limit_per_day() {
		return $this->get_meta( 'limit_money_per_day' );
	}

	/**
	 *    Display Sidebar on left side and next is main content
	 *
	 * @return string
	 * @since 1.0
	 *
	 */
	public function get_posted_ago() {
		return human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) . " " . esc_html__( 'ago', 'ggmp' );
	}

	public function get_sandbox_api_username() {
		return $this->get_meta( 'sandbox_api_username' );
	}

	public function get_sandbox_api_password() {
		return $this->get_meta( 'sandbox_api_password' );
	}

	public function get_sandbox_api_signature() {
		return $this->get_meta( 'sandbox_api_signature' );
	}
}
