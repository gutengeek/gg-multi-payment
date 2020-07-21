<?php
namespace GGMP\Common\Model\Query;

class Paypal_Query {
	/**
	 * Get accounts.
	 *
	 * @param array $post
	 * @return \WP_Query
	 */
	public static function get_paypal_accounts_query( $args = [] ) {
		$args = wp_parse_args( $args, [
			'post_per_page' => -1,
			'order_by'      => 'title',
			'order'         => 'DESC',
			'post_status'   => 'publish',
		] );

		$query_args = array_merge( [ 'post_type' => 'ggmp_paypal' ], $args );

		return new \WP_Query( $query_args );
	}

	/**
	 * @param array $args
	 * @return int[]|\WP_Post[]
	 */
	public static function get_paypal_accounts( $args = [] ) {
		$args = wp_parse_args( $args, [
			'numberposts' => -1,
			'post_status' => 'publish',
			'order'       => 'DESC',
			'orderby'     => 'meta_value_num',
			'meta_key'    => GGMP_METABOX_PREFIX . 'priority',
		] );

		$query_args = array_merge( [ 'post_type' => 'ggmp_paypal' ], $args );
		$accounts   = get_posts( $query_args );

		return $accounts;
	}
}
