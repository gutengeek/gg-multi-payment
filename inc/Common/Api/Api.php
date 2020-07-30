<?php
namespace GGMP\Common\Api;

class Api extends \WC_API_Resource {
	/** @var string $base the route base */
	protected $base = '/ggmp';

	/**
	 * Register the routes for this class
	 *
	 * GET /reports
	 * GET /reports/sales
	 *
	 * @param array $routes
	 * @return array
	 * @since 2.1
	 */
	public function register_routes( $routes ) {

		# GET/PUT/DELETE /ggmp/<id>
		$routes[ $this->base . '/(?P<id>\d+)' ] = [
			[ [ $this, 'update_tracking' ], \WC_API_Server::EDITABLE | \WC_API_Server::ACCEPT_DATA ],
		];

		return $routes;
	}

	/**
	 * Edit an order
	 *
	 * @param int $id the order ID
	 * @param array $data
	 *
	 * @return array|\WP_Error
	 */
	public function update_tracking( $id, $data ) {
		try {
			if ( ! isset( $data['order'] ) ) {
				throw new \WC_API_Exception( 'woocommerce_api_missing_order_data', sprintf( __( 'No %1$s data specified to edit %1$s', 'woocommerce' ), 'order' ), 400 );
			}

			$data = $data['order'];

			$update_totals = false;

			$id = $this->validate_request( $id, $this->post_type, 'edit' );

			if ( is_wp_error( $id ) ) {
				return $id;
			}

			$data  = apply_filters( 'woocommerce_api_edit_order_data', $data, $id, $this );
			$order = wc_get_order( $id );

			if ( empty( $order ) ) {
				throw new \WC_API_Exception( 'woocommerce_api_invalid_order_id', __( 'Order ID is invalid', 'woocommerce' ), 400 );
			}

			$order_args = array( 'order_id' => $order->get_id() );

			// Customer note.
			if ( isset( $data['note'] ) ) {
				$order_args['customer_note'] = $data['note'];
			}

			// Customer ID.
			if ( isset( $data['customer_id'] ) && $data['customer_id'] != $order->get_user_id() ) {
				// Make sure customer exists.
				if ( false === get_user_by( 'id', $data['customer_id'] ) ) {
					throw new WC_API_Exception( 'woocommerce_api_invalid_customer_id', __( 'Customer ID is invalid.', 'woocommerce' ), 400 );
				}

				update_post_meta( $order->get_id(), '_customer_user', $data['customer_id'] );
			}

			// Billing/shipping address.
			$this->set_order_addresses( $order, $data );

			$lines = array(
				'line_item' => 'line_items',
				'shipping'  => 'shipping_lines',
				'fee'       => 'fee_lines',
				'coupon'    => 'coupon_lines',
			);

			foreach ( $lines as $line_type => $line ) {

				if ( isset( $data[ $line ] ) && is_array( $data[ $line ] ) ) {

					$update_totals = true;

					foreach ( $data[ $line ] as $item ) {

						// Item ID is always required.
						if ( ! array_key_exists( 'id', $item ) ) {
							$item['id'] = null;
						}

						// Create item.
						if ( is_null( $item['id'] ) ) {
							$this->set_item( $order, $line_type, $item, 'create' );
						} elseif ( $this->item_is_null( $item ) ) {
							// Delete item.
							wc_delete_order_item( $item['id'] );
						} else {
							// Update item.
							$this->set_item( $order, $line_type, $item, 'update' );
						}
					}
				}
			}

			// Payment method (and payment_complete() if `paid` == true and order needs payment).
			if ( isset( $data['payment_details'] ) && is_array( $data['payment_details'] ) ) {

				// Method ID.
				if ( isset( $data['payment_details']['method_id'] ) ) {
					update_post_meta( $order->get_id(), '_payment_method', $data['payment_details']['method_id'] );
				}

				// Method title.
				if ( isset( $data['payment_details']['method_title'] ) ) {
					update_post_meta( $order->get_id(), '_payment_method_title', sanitize_text_field( $data['payment_details']['method_title'] ) );
				}

				// Mark as paid if set.
				if ( $order->needs_payment() && isset( $data['payment_details']['paid'] ) && true === $data['payment_details']['paid'] ) {
					$order->payment_complete( isset( $data['payment_details']['transaction_id'] ) ? $data['payment_details']['transaction_id'] : '' );
				}
			}

			// Set order currency.
			if ( isset( $data['currency'] ) ) {
				if ( ! array_key_exists( $data['currency'], get_woocommerce_currencies() ) ) {
					throw new WC_API_Exception( 'woocommerce_invalid_order_currency', __( 'Provided order currency is invalid.', 'woocommerce' ), 400 );
				}

				update_post_meta( $order->get_id(), '_order_currency', $data['currency'] );
			}

			// If items have changed, recalculate order totals.
			if ( $update_totals ) {
				$order->calculate_totals();
			}

			// Update order meta.
			if ( isset( $data['order_meta'] ) && is_array( $data['order_meta'] ) ) {
				$this->set_order_meta( $order->get_id(), $data['order_meta'] );
			}

			// Update the order post to set customer note/modified date.
			wc_update_order( $order_args );

			// Order status.
			if ( ! empty( $data['status'] ) ) {
				// Refresh the order instance.
				$order = wc_get_order( $order->get_id() );
				$order->update_status( $data['status'], isset( $data['status_note'] ) ? $data['status_note'] : '', true );
			}

			wc_delete_shop_order_transients( $order );

			do_action( 'woocommerce_api_edit_order', $order->get_id(), $data, $this );
			do_action( 'woocommerce_update_order', $order->get_id() );

			return $this->get_order( $id );

		} catch ( WC_Data_Exception $e ) {
			return new WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => 400 ) );
		} catch ( WC_API_Exception $e ) {
			return new WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ) );
		}
	}
}
