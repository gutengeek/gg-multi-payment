<?php
namespace GGMP\Common\Api;

/**
 * REST API Data Currencies controller class.
 *
 * @package Automattic/WooCommerce/RestApi
 */
class Shipping_Carriers extends \WC_REST_Data_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'ggmp_shipping_carriers';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace, '/' . $this->rest_base, [
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * Return the list of currencies.
	 *
	 * @param \WP_REST_Request $request Request data.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		return rest_ensure_response( \VI_WOO_ORDERS_TRACKING_DATA::shipping_carriers() );
	}
}
