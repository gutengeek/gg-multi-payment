<?php

namespace GGMP\Common\Model\Entity;

use WP_Error;
use WP_Post;

class Stripe_Entity {

	/**
	 * The stripe account ID
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
		$stripe    = WP_Post::get_instance( $_id );
		$this->ID  = $_id;

		return $this->setup( $stripe );
	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private stripe
	 */
	public function __get( $key ) {
		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( [ $this, 'get_' . $key ] );
		} else {
			return new WP_Error( 'ggmp-invalid-stripe', sprintf( esc_html__( 'Can\'t get stripe %s', 'ggmp' ), $key ) );
		}
	}

	/**
	 * Creates a stripe
	 *
	 * @param array $data Array of attributes for a stripe
	 * @return mixed  false if data isn't passed and class not instantiated for creation, or New Download ID
	 * @since 1.0
	 */
	public function create( $data = [] ) {
		if ( $this->id != 0 ) {
			return false;
		}

		$defaults = [
			'post_type'   => 'ggmp_stripe',
			'post_status' => 'draft',
			'post_title'  => esc_html__( 'New Stripe', 'ggmp' ),
		];

		$args = wp_parse_args( $data, $defaults );

		/**
		 * Fired before a stripe is created
		 *
		 * @param array $args The post object arguments used for creation.
		 */
		do_action( 'ggmp_pre_create', $args );

		$id = wp_insert_post( $args, true );

		$stripe = WP_Post::get_instance( $id );

		/**
		 * Fired after a stripe is created
		 *
		 * @param int   $id   The post ID of the created item.
		 * @param array $args The post object arguments used for creation.
		 */
		do_action( 'ggmp_post_create', $id, $args );

		return $this->setup( $stripe );

	}

	/**
	 * Given the stripe data, let's set the variables
	 *
	 * @param WP_Post $stripe The WP_Post object for stripe.
	 * @return bool         If the setup was successful or not
	 */
	private function setup( $stripe ) {
		if ( ! is_object( $stripe ) ) {
			return false;
		}

		if ( ! $stripe instanceof WP_Post ) {
			return false;
		}

		if ( 'ggmp_stripe' !== $stripe->post_type ) {
			return false;
		}

		foreach ( $stripe as $key => $value ) {
			$this->$key = $value;
		}

		return true;
	}

	public function get_id() {
		return $this->ID;
	}

	public function get_name() {
		return $this->post_title;
	}

	/**
	 * Get Permerlink
	 *
	 * @return string
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

	public function is_valid() {
		// TODO:...
		return true;
	}

	public function get_status_code() {
		return $this->post_status;
	}

	public function get_limit_per_day() {
		$limit = $this->get_meta( 'limit_money_per_day' );

		return $limit ? (float) $limit : (float) ggmp_get_option( 'limit_money_per_day', 300 );
	}

	public function get_publishable_key() {
		return $this->get_meta( 'publishable_key' );
	}

	public function get_secret_key() {
		return $this->get_meta( 'secret_key' );
	}

	public function get_webhook_secret() {
		return $this->get_meta( 'webhook_secret' );
	}

	public function get_test_publishable_key() {
		return $this->get_meta( 'test_publishable_key' );
	}

	public function get_test_secret_key() {
		return $this->get_meta( 'test_secret_key' );
	}

	public function get_test_webhook_secret() {
		return $this->get_meta( 'test_webhook_secret' );
	}

	public function get_truncated_pk() {
		return mb_substr( $this->get_publishable_key(), 0, 12 ) . '...' . substr( $this->get_publishable_key(), -4 );
	}

	public function get_truncated_test_pk() {
		return mb_substr( $this->get_test_publishable_key(), 0, 12 ) . '...' . substr( $this->get_test_publishable_key(), -4 );
	}

	public function get_stats() {
		$stats = $this->get_meta( 'stats' );

		return $stats ? $stats : [];
	}

	public function get_current_date_stats() {
		return date( 'Y-m-d', time() );
	}

	public function get_deposit( $date = '' ) {
		if ( ! $date ) {
			$date = $this->get_current_date_stats();
		}

		$stats = $this->get_stats();
		if ( ! isset( $stats[ $date ] ) ) {
			return 0;
		}

		return $stats[ $date ]['deposit'] ? (float) $stats[ $date ]['deposit'] : 0;
	}

	public function get_count_order( $date = '' ) {
		if ( ! $date ) {
			$date = $this->get_current_date_stats();
		}

		$stats = $this->get_stats();
		if ( ! isset( $stats[ $date ] ) ) {
			return 0;
		}

		return $stats[ $date ]['count_order'] ? absint( $stats[ $date ]['count_order'] ) : 0;
	}

	public function get_orders( $date = '' ) {
		if ( ! $date ) {
			$date = $this->get_current_date_stats();
		}

		$stats = $this->get_stats();
		if ( ! isset( $stats[ $date ] ) ) {
			return [];
		}

		return $stats[ $date ]['orders'] ? $stats[ $date ]['orders'] : [];
	}
}
