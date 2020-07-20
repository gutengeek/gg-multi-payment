<?php
namespace GGMP\Common\Module\Stripe;

use GGMP\Common\Model\Query\Stripe_Query;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract class to define/implement base methods for all controller classes
 *
 * @package    GGMP
 * @subpackage GGMP/controllers
 */
class Stripe_Hook {
	/**
	 * Enabled.
	 *
	 * @var
	 */
	public $stripe_settings;

	/**
	 * Total label
	 *
	 * @var
	 */
	public $total_label;

	/**
	 * Key
	 *
	 * @var
	 */
	public $publishable_key;

	/**
	 * Key
	 *
	 * @var
	 */
	public $secret_key;

	/**
	 * Is test mode active?
	 *
	 * @var bool
	 */
	public $testmode;

	/**
	 * Hook constructor.
	 */
	public function __construct() {
		if ( 'on' !== ggmp_get_option( 'enable_stripe', 'on' ) ) {
			return;
		}

		$this->stripe_settings = get_option( 'woocommerce_stripe_settings', [] );
		$this->testmode        = ( ! empty( $this->stripe_settings['testmode'] ) && 'yes' === $this->stripe_settings['testmode'] ) ? true : false;

		add_filter( 'option_woocommerce_stripe_settings', [ $this, 'woocommerce_stripe_settings', ], 10, 1 );
		// add_filter( 'option_woocommerce_stripe_settings', [ $this, 'woocommerce_stripe_settings_in_cart', ], 10, 1 );
		add_filter( 'woocommerce_checkout_posted_data', [ $this, 'woocommerce_checkout_posted_data', ], 10, 1 );
		add_action( 'woocommerce_checkout_order_processed', [ $this, 'woocommerce_checkout_order_processed' ], 10, 3 );
		add_action( 'woocommerce_review_order_after_submit', [ $this, 'woocommerce_review_order_after_submit' ], 10, 1 );

		add_filter( 'wc_stripe_payment_request_params', [ $this, 'wc_stripe_payment_request_params_function' ], 10, 3 );
		add_filter( 'wc_stripe_params', [ $this, 'wc_stripe_params_function' ], 10, 3 );
		add_filter( 'woocommerce_stripe_request_headers', [ $this, 'woocommerce_stripe_request_headers_function' ], 10, 3 );
	}

	public function wc_stripe_payment_request_params_function( $params ) {
		$cart = WC()->cart;
		if ( $cart ) {
			$cart_totals = WC()->cart->get_totals();
			if ( $cart_totals ) {
				$total    = $cart_totals['total'];
				$accounts = Stripe_Query::get_stripe_accounts();

				if ( $accounts ) {
					foreach ( $accounts as $account ) {
						$account       = ggmp_stripe( $account->ID );
						$deposit       = $account->get_deposit();
						$limit_per_day = $account->get_limit_per_day();

						if ( $account->is_valid() && ( $deposit + $total ) <= $limit_per_day ) {
							if ( $this->testmode ) {
								$params['stripe']['key'] = $account->get_test_publishable_key();
							} else {
								$params['stripe']['key'] = $account->get_publishable_key();
							}
							break;
						}
					}
				}
			}
		}

		return $params;
	}

	/**
	 * Change stripe settings.
	 *
	 * @param $value
	 * @return array
	 */
	public function woocommerce_stripe_settings_in_cart( $value ) {
		if ( ! function_exists( 'woocommerce_gateway_stripe_init' ) || is_admin() ) {
			return $value;
		}

		$session        = new \WC_Session_Handler();
		$session_cookie = $session->get_session_cookie();
		if ( $session_cookie ) {
			if ( isset( $session_cookie[0] ) && $session_cookie[0] ) {
				$session_data = $session->get_session( $session_cookie[0] );

				if ( isset( $session_data['cart_totals'] ) && $session_data['cart_totals'] ) {
					$cart_total = maybe_unserialize( $session_data['cart_totals'] );
					if ( isset( $cart_total['total'] ) ) {
						$total = $cart_total['total'];
						if ( $total ) {
							$accounts = Stripe_Query::get_stripe_accounts();
							if ( $accounts ) {
								foreach ( $accounts as $account ) {
									$account       = ggmp_stripe( $account->ID );
									$deposit       = $account->get_deposit();
									$limit_per_day = $account->get_limit_per_day();

									if ( $account->is_valid() && ( $deposit + $total ) <= $limit_per_day ) {
										$value['test_publishable_key'] = $account->get_test_publishable_key();
										$value['publishable_key']      = $account->get_publishable_key();
										$value['test_secret_key']      = $account->get_test_secret_key();
										$value['secret_key']           = $account->get_secret_key();
										$value['test_webhook_secret']  = $account->get_test_webhook_secret();
										$value['webhook_secret']       = $account->get_webhook_secret();
										break;
									}
								}
							}
						}
					}
				}
			}
		}

		return $value;
	}

	/**
	 * Localized JS key/value pair.
	 *
	 * @param $params
	 *
	 * @return mixed
	 */
	public function wc_stripe_params_function( $params ) {
		$cart = WC()->cart;
		if ( $cart ) {
			$cart_totals = WC()->cart->get_totals();
			if ( $cart_totals ) {
				$total    = $cart_totals['total'];
				$accounts = Stripe_Query::get_stripe_accounts();

				if ( $accounts ) {
					foreach ( $accounts as $account ) {
						$account       = ggmp_stripe( $account->ID );
						$deposit       = $account->get_deposit();
						$limit_per_day = $account->get_limit_per_day();

						if ( $account->is_valid() && ( $deposit + $total ) <= $limit_per_day ) {
							if ( $this->testmode ) {
								$params['key'] = $account->get_test_publishable_key();
							} else {
								$params['key'] = $account->get_publishable_key();
							}
							break;
						}
					}
				}
			}
		}

		return $params;
	}

	/**
	 * Headers parameters for cURL requests.
	 *
	 * @see https://docs.woocommerce.com/document/stripe/
	 *
	 * @param $headers_args
	 *
	 * @return mixed
	 */
	public function woocommerce_stripe_request_headers_function( $headers_args ) {
		$cart = WC()->cart;
		if ( $cart ) {
			$cart_totals = WC()->cart->get_totals();
			if ( $cart_totals ) {
				$total    = $cart_totals['total'];
				$accounts = Stripe_Query::get_stripe_accounts();

				if ( $accounts ) {
					foreach ( $accounts as $account ) {
						$account       = ggmp_stripe( $account->ID );
						$deposit       = $account->get_deposit();
						$limit_per_day = $account->get_limit_per_day();

						if ( $account->is_valid() && ( $deposit + $total ) <= $limit_per_day ) {
							if ( $this->testmode ) {
								$headers_args['Authorization'] = 'Basic ' . base64_encode( $account->get_test_secret_key() . ':' );
							} else {
								$headers_args['Authorization'] = 'Basic ' . base64_encode( $account->get_secret_key() . ':' );
							}
							break;
						}
					}
				}
			}
		}

		return $headers_args;
	}

	/**
	 * Change stripe settings.
	 *
	 * @param $value
	 * @return array
	 */
	public function woocommerce_stripe_settings( $value ) {
		if ( ! function_exists( 'woocommerce_gateway_stripe_init' ) ) {
			return $value;
		}

		if ( ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) || ! isset( $_GET['wc-api'] ) || ( 'wc_stripe' !== $_GET['wc-api'] ) ) {
			return $value;
		}

		$request_body = file_get_contents( 'php://input' );
		if ( $request_body ) {
			$notification = json_decode( $request_body );

			if ( $notification->data && $notification->data->object && $notification->data->object->id ) {
				$order = \WC_Stripe_Helper::get_order_by_source_id( $notification->data->object->id );

				if ( $order ) {
					$order_id          = $order->get_id();
					$stripe_account_id = get_post_meta( $order_id, '_stripe_account_id', true );
					if ( $stripe_account_id ) {
						$account = ggmp_stripe( $stripe_account_id );
						if ( $this->testmode ) {
							$value['test_webhook_secret'] = $account->get_test_webhook_secret();
						} else {
							$value['webhook_secret'] = $account->get_webhook_secret();
						}
					}
				}
			}
		}

		return $value;
	}

	/**
	 * @param $order_id
	 * @param $posted_data
	 * @param $order \WC_Order
	 */
	public function woocommerce_checkout_order_processed( $order_id, $posted_data, $order ) {
		if ( ! isset( $posted_data['payment_method'] ) || ( 'stripe' !== $posted_data['payment_method'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		$account_id = isset( $posted_data['ggmp_stripe_account'] ) ? absint( $posted_data['ggmp_stripe_account'] ) : 0;

		if ( ! $account_id ) {
			return;
		}

		update_post_meta( $order_id, '_stripe_account_id', $account_id );

		$account       = ggmp_stripe( $account_id );
		$limit_per_day = $account->get_limit_per_day();
		$stats         = $account->get_stats();

		$today = $account->get_current_date_stats();

		if ( ! isset( $stats[ $today ] ) ) {
			$stats[ $today ] = [
				'count_order'   => 1,
				'deposit'       => $order->get_total(),
				'orders'        => [ $order_id ],
				'limit_per_day' => (float) $limit_per_day,
			];
		} else {
			$stats[ $today ]['count_order']   += 1;
			$stats[ $today ]['deposit']       += $order->get_total();
			$stats[ $today ]['orders'][]      = $order_id;
			$stats[ $today ]['limit_per_day'] = (float) $limit_per_day;
		}

		if ( count( $stats ) > 15 ) {
			array_shift( $stats );
		}

		update_post_meta( $account_id, GGMP_METABOX_PREFIX . 'stats', $stats );
	}

	public function woocommerce_review_order_after_submit() {
		$totals     = WC()->cart->get_totals();
		$total      = $totals['total'];
		$account_id = 0;
		if ( $total ) {
			$accounts = Stripe_Query::get_stripe_accounts();
			if ( $accounts ) {
				foreach ( $accounts as $account ) {
					$account = ggmp_stripe( $account->ID );
					$deposit = $account->get_deposit();

					$limit_per_day = $account->get_limit_per_day();
					if ( $account->is_valid() && ( $deposit + $total ) <= $limit_per_day ) {
						$account_id = $account->get_id();
						break;
					}
				}
			}
		}

		if ( ! $account_id ) {
			return;
		}
		?>
        <input type="hidden" name="ggmp_stripe_account" value="<?php echo esc_attr( $account_id ); ?>">
		<?php
	}

	public function woocommerce_checkout_posted_data( $data ) {
		$accounts = Stripe_Query::get_stripe_accounts();
		if ( $accounts ) {
			$data['ggmp_stripe_account'] = absint( $_POST['ggmp_stripe_account'] );
		}

		return $data;
	}
}


