<?php
namespace GGMP\Common\Api;

/**
 * REST API Data Currencies controller class.
 *
 * @package Automattic/WooCommerce/RestApi
 */
class Tracking extends \WC_REST_CRUD_Controller {

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
	protected $rest_base = 'ggmp_tracking';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'shop_order';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			[
				'args'   => [
					'id' => [
						'description' => __( 'Unique identifier for the resource.', 'ggmp' ),
						'type'        => 'integer',
					],
				],
				[
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'update_item_permissions_check' ],
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * Get object. Return false if object is not of required type.
	 *
	 * @param int $id Object ID.
	 * @return \WC_Data|bool
	 */
	protected function get_object( $id ) {
		$order = wc_get_order( $id );
		// In case id is a refund's id (or it's not an order at all), don't expose it via /orders/ path.
		if ( ! $order || 'shop_order_refund' === $order->get_type() ) {
			return false;
		}

		return $order;
	}

	/**
	 * Update a single post.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return \WP_Error|\WP_REST_Response|array|mixed
	 */
	public function update_item( $request ) {
		$object = $this->get_object( (int) $request['id'] );

		if ( ! $object || 0 === $object->get_id() ) {
			return new \WP_Error( "woocommerce_rest_{$this->post_type}_invalid_id", __( 'Invalid ID.', 'ggmp' ), [ 'status' => 400 ] );
		}

		if ( ! $request->has_param( 'tracking_code' ) ) {
			return new \WP_Error( "woocommerce_rest_{$this->post_type}_missing_tracking_code", __( 'Missing Tracking ID.', 'ggmp' ), [ 'status' => 400 ] );
		}

		return $this->save_track_info_all_item( $request, $object );
	}

	/**
	 * @param \WP_REST_Request $request Full details about the request.
	 * @param \WC_Order        $order   order.
	 * @throws \Exception
	 */
	public function save_track_info_all_item( $request, $order ) {
		if ( ! class_exists( 'VI_WOO_ORDERS_TRACKING_DATA' ) ) {
			return new \WP_Error( "woocommerce_rest_{$this->post_type}_missing_tracking_plugin", __( 'Missing Tracking Plugin.', 'ggmp' ), [ 'status' => 400 ] );
		}

		$order_id                 = $order->get_id();
		$transition_id            = $order->get_transaction_id();
		$class_settings           = new \VI_WOO_ORDERS_TRACKING_DATA();
		$add_to_paypal            = isset( $request['add_to_paypal'] ) ? sanitize_text_field( $request['add_to_paypal'] ) : 1;
		$tracking_number          = isset( $request['tracking_code'] ) ? sanitize_text_field( $request['tracking_code'] ) : '';
		$transID                  = isset( $request['transID'] ) ? sanitize_text_field( $request['transID'] ) : $transition_id;
		$change_order_status      = isset( $request['change_order_status'] ) ? sanitize_text_field( $request['change_order_status'] ) : '';
		$send_mail                = isset( $request['send_mail'] ) ? sanitize_text_field( $request['send_mail'] ) : '';
		$paypal_method            = isset( $request['paypal_method'] ) ? sanitize_text_field( $request['paypal_method'] ) : 'ppec_paypal';
		$carrier_slug             = isset( $request['carrier_id'] ) ? sanitize_text_field( $request['carrier_id'] ) : '';
		$carrier_name             = isset( $request['carrier_name'] ) ? sanitize_text_field( $request['carrier_name'] ) : '';
		$add_new_carrier          = isset( $request['add_new_carrier'] ) ? sanitize_text_field( $request['add_new_carrier'] ) : '';
		$carrier_type             = '';
		$settings                 = $class_settings->get_params();
		$settings['order_status'] = $change_order_status;
		$response                 = [
			'status'                   => 'success',
			'paypal_status'            => 'success',
			'paypal_message'           => __( '', 'ggmp' ),
			'message'                  => __( 'Tracking saved', 'ggmp' ),
			'detail'                   => '',
			'tracking_code'            => $tracking_number,
			'tracking_url'             => '',
			'tracking_url_show'        => '',
			'carrier_name'             => $carrier_name,
			'carrier_id'               => $carrier_slug,
			'carrier_type'             => $carrier_type,
			'item_id'                  => '',
			'change_order_status'      => $change_order_status,
			'paypal_button_class'      => '',
			'paypal_button_title'      => '',
			'paypal_added_trackings'   => '',
			'tracking_service'         => '',
			'tracking_service_status'  => 'success',
			'tracking_service_message' => '',
			'digital_delivery'         => 0,
		];

		$settings['email_enable'] = $send_mail === 'yes' ? 1 : 0;
		$tracking_more_slug       = '';
		$digital_delivery         = 0;
		if ( $add_new_carrier ) {
			$carrier_name     = isset( $request['carrier_name'] ) ? sanitize_text_field( $request['carrier_name'] ) : '';
			$tracking_url     = isset( $request['tracking_url'] ) ? sanitize_text_field( $request['tracking_url'] ) : '';
			$shipping_country = isset( $request['shipping_country'] ) ? sanitize_text_field( $request['shipping_country'] ) : '';
			$carrier_url      = $tracking_url;
			if ( $carrier_name && $tracking_url && $shipping_country ) {
				$custom_carriers_list             = json_decode( $class_settings->get_params( 'custom_carriers_list' ), true );
				$custom_carrier                   = [
					'name'    => $carrier_name,
					'slug'    => 'custom_' . time(),
					'url'     => $tracking_url,
					'country' => $shipping_country,
					'type'    => 'custom',
				];
				$carrier_slug                     = $custom_carrier['slug'];
				$custom_carriers_list[]           = $custom_carrier;
				$settings['custom_carriers_list'] = json_encode( $custom_carriers_list );
				update_option( 'woo_orders_tracking_settings', $settings );
				$carrier_type = 'custom-carrier';
			} else {
				update_option( 'woo_orders_tracking_settings', $settings );
				wp_send_json(
					[
						'status'  => 'error',
						'message' => __( 'Not enough information', 'ggmp' ),
						'details' => [
							'carrier_name'     => $carrier_name,
							'tracking_url'     => $tracking_url,
							'shipping_country' => $shipping_country,
						],
					]
				);
			}
		} else {
			update_option( 'woo_orders_tracking_settings', $settings );
			$carrier = $class_settings->get_shipping_carrier_by_slug( $carrier_slug );
			if ( is_array( $carrier ) && count( $carrier ) ) {
				$carrier_url        = $carrier['url'];
				$carrier_name       = $carrier['name'];
				$carrier_type       = $carrier['carrier_type'];
				$tracking_more_slug = isset( $carrier['tracking_more_slug'] ) ? $carrier['tracking_more_slug'] : '';
				if ( isset( $carrier['digital_delivery'] ) ) {
					$digital_delivery             = $carrier['digital_delivery'];
					$response['digital_delivery'] = $digital_delivery;
					if ( $digital_delivery == 1 ) {
						$tracking_number = '';
					}
				}
			} else {
				$carrier_url = '';
			}
		}
		$response['carrier_id']   = $carrier_slug;
		$response['carrier_type'] = $carrier_type;
		$response['carrier_url']  = $carrier_url;
		if ( ! $order_id || ( ! $tracking_number && $digital_delivery != 1 ) || ! $carrier_slug || ! $carrier_type ) {
			wp_send_json(
				[
					'status'  => 'error',
					'message' => __( 'Not enough information', 'ggmp' ),
				]
			);
		}
		$paypal_added_trackings = get_post_meta( $order_id, 'vi_wot_paypal_added_tracking_numbers', true );
		if ( ! $paypal_added_trackings ) {
			$paypal_added_trackings = [];
		}
		if ( $add_to_paypal && $transID && $paypal_method && ! in_array( $tracking_number, $paypal_added_trackings ) ) {
			$send_paypal = [
				[
					'trans_id'        => $transID,
					'carrier_name'    => $carrier_name,
					'tracking_number' => $tracking_number,
				],
			];

			$result_add_paypal = $this->add_trackinfo_to_paypal( $send_paypal, $paypal_method, $order );

			if ( $result_add_paypal['status'] === 'error' ) {
				$response['paypal_status']  = 'error';
				$response['paypal_message'] = empty( $result_add_paypal['data'] ) ? __( 'Cannot add tracking to PayPal', 'ggmp' ) : $result_add_paypal['data'];
			} else {
				$paypal_added_trackings[] = $tracking_number;
				update_post_meta( $order_id, 'vi_wot_paypal_added_tracking_numbers', $paypal_added_trackings );
			}
		}

		$response['paypal_added_trackings'] = implode( ', ', array_filter( $paypal_added_trackings ) );
		if ( ! in_array( $tracking_number, $paypal_added_trackings ) ) {
			$response['paypal_button_class'] = 'active';
			$response['paypal_button_title'] = __( 'Add this tracking number to PayPal', 'ggmp' );
		} else {
			$response['paypal_button_class'] = 'inactive';
			$response['paypal_button_title'] = __( 'This tracking number was added to PayPal', 'ggmp' );
		}

		global $wpdb;

		$query       = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %s AND order_item_type='line_item'", $order_id );
		$line_items  = $wpdb->get_results( $query, ARRAY_A );
		$now         = time();
		$last_update = $now;
		if ( $line_items_count = count( $line_items ) ) {
			$send_mail_array         = [];
			$tracking_url_import     = $class_settings->get_url_tracking( $carrier_url, $tracking_number, $carrier_slug, $order->get_shipping_postcode() );
			$tracking_change_count   = 0;
			$status                  = '';
			$result_refresh_database = '';
			if ( $carrier_type === 'service-carrier' ) {
				$send_data               = [
					'carrier_id'            => $tracking_more_slug,
					'carrier_name'          => $carrier_name,
					'shipping_country_code' => $order->get_shipping_country(),
					'tracking_code'         => $tracking_number,
					'order_id'              => $order_id,
					'customer_phone'        => $order->get_billing_phone(),
					'customer_email'        => $order->get_billing_email(),
					'customer_name'         => $order->get_formatted_billing_full_name(),
				];
				$result_refresh_database = \VI_WOO_ORDERS_TRACKING_TABLE_TRACKING::refresh_track_info_database( $send_data, true );
			}
			for ( $i = 0; $i < $line_items_count; $i++ ) {
				$line_item       = $line_items[ $i ];
				$item_id         = isset( $line_item['order_item_id'] ) ? $line_item['order_item_id'] : '';
				$order_item_name = isset( $line_item['order_item_name'] ) ? $line_item['order_item_name'] : '';
				if ( $item_id ) {
					$item_tracking_data    = wc_get_order_item_meta( $item_id, '_vi_wot_order_item_tracking_data', true );
					$current_tracking_data = [
						'tracking_number' => '',
						'carrier_slug'    => '',
						'carrier_url'     => '',
						'carrier_name'    => '',
						'carrier_type'    => '',
						'time'            => $now,
					];
					$tracking_change       = true;
					if ( $item_tracking_data ) {
						$item_tracking_data = json_decode( $item_tracking_data, true );
						if ( $digital_delivery == 1 ) {
							$current_tracking_data = array_pop( $item_tracking_data );
							if ( ! empty( $current_tracking_data['tracking_number'] ) || empty( $current_tracking_data['carrier_slug'] ) || empty( $current_tracking_data['carrier_name'] ) || empty( $current_tracking_data['carrier_url'] ) ) {
								$item_tracking_data[] = $current_tracking_data;
							} elseif ( $current_tracking_data['carrier_url'] == $carrier_url ) {
								$tracking_change = false;
							}
						} else {
							foreach ( $item_tracking_data as $order_tracking_data_k => $order_tracking_data_v ) {
								$current_tracking_data = $order_tracking_data_v;
								if ( $current_tracking_data['tracking_number'] == $tracking_number ) {
									if ( $current_tracking_data['carrier_url'] == $carrier_url && $order_tracking_data_k === ( count( $item_tracking_data ) - 1 ) ) {
										$tracking_change = false;
									}
									unset( $item_tracking_data[ $order_tracking_data_k ] );
									break;
								}
							}
						}

						$item_tracking_data = array_values( $item_tracking_data );
					} else {
						$item_tracking_data = [];
					}

					$current_tracking_data['status']          = $status;
					$current_tracking_data['last_update']     = $last_update;
					$current_tracking_data['tracking_number'] = $tracking_number;
					$current_tracking_data['carrier_slug']    = $carrier_slug;
					$current_tracking_data['carrier_url']     = $carrier_url;
					$current_tracking_data['carrier_name']    = $carrier_name;
					$current_tracking_data['carrier_type']    = $carrier_type;
					$item_tracking_data[]                     = $current_tracking_data;
					wc_update_order_item_meta( $item_id, '_vi_wot_order_item_tracking_data', json_encode( $item_tracking_data ) );
					$send_mail_array[] = [
						'order_item_name' => $order_item_name,
						'tracking_number' => $tracking_number,
						'tracking_url'    => $tracking_url_import,
						'carrier_name'    => $carrier_name,
					];
					if ( $tracking_change ) {
						$tracking_change_count++;
					}
					do_action( 'vi_woo_orders_tracking_single_edit_tracking_change', $tracking_change, $current_tracking_data, $item_id, $order_id, $response );
				}
			}
			if ( 'yes' === $send_mail && count( $send_mail_array ) ) {
				\VI_WOO_ORDERS_TRACKING_ADMIN_IMPORT_CSV::send_mail( $order_id, $send_mail_array, true );
			}
			$response['tracking_url_show']   = $tracking_url_import;
			$response['change_order_status'] = $change_order_status;
			$response['detail_add_database'] = isset( $result_refresh_database['track_info'] ) ? $result_refresh_database['track_info'] : '';
		} else {
			$response['status']  = 'error';
			$response['message'] = __( 'Order items not matched', 'ggmp' );
		}
		if ( $change_order_status ) {
			$order->update_status( substr( $change_order_status, 3 ) );
			$order->save();
		}

		return $response;
	}

	/**
	 * @param           $send_paypal
	 * @param           $paypal_method
	 * @param \WC_Order $order
	 * @return array
	 */
	public function add_trackinfo_to_paypal( $send_paypal, $paypal_method, $order ) {
		$class_settings          = new \VI_WOO_ORDERS_TRACKING_DATA();
		$available_paypal_method = $class_settings->get_params( 'paypal_method' );
		$i                       = array_search( $paypal_method, $available_paypal_method );
		if ( is_numeric( $i ) ) {
			$sandbox           = $class_settings->get_params( 'paypal_sandbox_enable' )[ $i ] ? true : false;
			$paypal_account_id = get_post_meta( $order->get_id(), '_paypal_account_id', true );
			if ( $paypal_account_id ) {
				$account = ggmp_paypal( $paypal_account_id );
				if ( $sandbox ) {
					$client_id = $account->get_client_id_sandbox();
					$secret    = $account->get_secret_sandbox();
				} else {
					$client_id = $account->get_client_id_live();
					$secret    = $account->get_secret_live();
				}
			} else {
				if ( $sandbox ) {
					$client_id = $class_settings->get_params( 'paypal_client_id_sandbox' )[ $i ];
					$secret    = $class_settings->get_params( 'paypal_secret_sandbox' )[ $i ];
				} else {
					$client_id = $class_settings->get_params( 'paypal_client_id_live' )[ $i ];
					$secret    = $class_settings->get_params( 'paypal_secret_live' )[ $i ];
				}
			}

			$result = \VI_WOO_ORDERS_TRACKING_ADMIN_PAYPAL::add_tracking_number( $client_id, $secret, $send_paypal, $sandbox );
		} else {
			$result = [
				'status' => 'error',
				'data'   => __( 'PayPal method not found', 'ggmp' ),
			];
		}

		return $result;
	}
}
