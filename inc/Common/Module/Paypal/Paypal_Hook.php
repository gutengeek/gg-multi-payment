<?php
namespace GGMP\Common\Module\Paypal;

use GGMP\Common\Model\Query\Paypal_Query;

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
class Paypal_Hook {
	/**
	 * Hook constructor.
	 */
	public function __construct() {
		if ( 'on' !== ggmp_get_option( 'enable_paypal', 'on' ) ) {
			return;
		}

		add_filter( 'option_woocommerce_ppec_paypal_settings', [ $this, 'woocommerce_ppec_paypal_settings', ], 10, 1 );
		add_filter( 'woocommerce_checkout_posted_data', [ $this, 'woocommerce_checkout_posted_data', ], 10, 1 );
		add_action( 'woocommerce_checkout_order_processed', [ $this, 'woocommerce_checkout_order_processed' ], 10, 3 );
		add_action( 'woocommerce_review_order_after_submit', [ $this, 'woocommerce_review_order_after_submit' ], 10, 1 );
	}

	/**
	 * Change paypal settings.
	 *
	 * @param $value
	 * @return array
	 */
	public function woocommerce_ppec_paypal_settings( $value ) {
		if ( ! function_exists( 'wc_gateway_ppec' ) || is_admin() ) {
			return $value;
		}

		if ( 'yes' !== $value['enabled'] ) {
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
							$accounts = Paypal_Query::get_paypal_accounts();
							if ( $accounts ) {
								foreach ( $accounts as $account ) {
									$account       = ggmp_paypal( $account->ID );
									$deposit       = $account->get_deposit();
									$limit_per_day = $account->get_limit_per_day();

									if ( $account->is_valid() && ( $deposit + $total ) <= $limit_per_day ) {
										$value['api_username']            = $account->get_api_username();
										$value['api_password']            = $account->get_api_password();
										$value['api_signature']           = $account->get_api_signature();
										$value['api_certificate']         = $account->get_api_certificate();
										$value['sandbox_api_username']    = $account->get_sandbox_api_username();
										$value['sandbox_api_password']    = $account->get_sandbox_api_password();
										$value['sandbox_api_signature']   = $account->get_sandbox_api_signature();
										$value['sandbox_api_certificate'] = $account->get_sandbox_api_certificate();
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
	 * @param $order_id
	 * @param $posted_data
	 * @param $order \WC_Order
	 */
	public function woocommerce_checkout_order_processed( $order_id, $posted_data, $order ) {
		if ( ! isset( $posted_data['payment_method'] ) || ( 'ppec_paypal' !== $posted_data['payment_method'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		$account_id = isset( $posted_data['ggmp_paypal_account'] ) ? absint( $posted_data['ggmp_paypal_account'] ) : 0;

		if ( ! $account_id ) {
			return;
		}

		$account = ggmp_paypal( $account_id );

		if ( $account ) {
			update_post_meta( $order_id, '_paypal_account_id', $account->get_id() );

			$order->add_order_note( sprintf(
					__( 'PayPal account assigned:  %s. API Username: %s. Sandbox API Username: %s', 'ggmp' ),
					$account->get_name(),
					$account->get_api_username() ? $account->get_api_username() : '---',
					$account->get_sandbox_api_username() ? $account->get_sandbox_api_username() : '---'
				)
			);

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

			update_post_meta( $account->get_id(), GGMP_METABOX_PREFIX . 'stats', $stats );
		}
	}

	public function woocommerce_review_order_after_submit() {
		$option = get_option( 'woocommerce_ppec_paypal_settings', [] );
		if ( 'yes' !== $option['enabled'] ) {
			return;
		}

		$totals     = WC()->cart->get_totals();
		$total      = $totals['total'];
		$account_id = 0;
		if ( $total ) {
			$accounts = Paypal_Query::get_paypal_accounts();
			if ( $accounts ) {
				foreach ( $accounts as $account ) {
					$account = ggmp_paypal( $account->ID );
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
        <input type="hidden" name="ggmp_paypal_account" value="<?php echo esc_attr( $account_id ); ?>">
		<?php
	}

	public function woocommerce_checkout_posted_data( $data ) {
		$option = get_option( 'woocommerce_ppec_paypal_settings', [] );
		if ( 'yes' !== $option['enabled'] ) {
			return $data;
		}

		$accounts = Paypal_Query::get_paypal_accounts();
		if ( $accounts ) {
			$data['ggmp_paypal_account'] = absint( $_POST['ggmp_paypal_account'] );
		}

		return $data;
	}
}


