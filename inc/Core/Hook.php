<?php
namespace GGMP\Core;

// Exit if accessed directly.

use GGMP\Common\Model\Query\Paypal_Query;
use GGMP\Helper\Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract class to define/implement base methods for all controller classes
 *
 * @since      1.0.0
 * @package    GGMP
 * @subpackage GGMP/controllers
 */
class Hook {
	/**
	 * Hook constructor.
	 */
	public function __construct() {
		add_filter( 'option_woocommerce_ppec_paypal_settings', [ $this, 'woocommerce_ppec_paypal_settings', ], 10, 1 );
		add_action( 'woocommerce_cart_updated', [ $this, 'woocommerce_cart_updated' ], 10 );
	}

	public function woocommerce_cart_updated() {
		$cart = WC()->cart;

		if ( ! $cart ) {
			return;
		}

		$ip = Client::get_real_ip_addr();

		$client = get_option( 'ggmp_client', [] );
		$totals = $cart->get_totals();

		$accounts = Paypal_Query::get_paypal_accounts();

		$a = 0;
		foreach ( $accounts as $account ) {
			$a = $account->ID;
			break;
		}

		$client[ $ip ] = $a;

		update_option( 'ggmp_client', $client );
	}

	/**
	 * Change paypal settings.
	 *
	 * @param $value
	 * @return array
	 */
	public function woocommerce_ppec_paypal_settings( $value ) {
		if ( is_admin() ) {
			return $value;
		}

		$ip     = Client::get_real_ip_addr();
		$client = get_option( 'ggmp_client', [] );

		if ( isset( $client[ $ip ] ) ) {
			$account                  = ggmp_paypal( $client[ $ip ] );
			$value['api_username']    = '';
			$value['api_password']    = '';
			$value['api_signature']   = '';
			$value['api_certificate'] = '';
			$value['api_subject']     = '';

			$value['sandbox_api_username']  = $account->get_sandbox_api_username();
			$value['sandbox_api_password']  = $account->get_sandbox_api_password();
			$value['sandbox_api_signature'] = $account->get_sandbox_api_signature();

			// $value['sandbox_api_username']    = 'sb-rekv52535458_api1.business.example.com';
			// $value['sandbox_api_password']    = 'QUE94LPBNRQ24XA2';
			// $value['sandbox_api_signature']   = 'ATbs90-YxFSTTqSObnL1GMPybaigAqCzsltLAbgnVjZKLEt0yOKJpFma';
			$value['sandbox_api_certificate'] = '';
			$value['sandbox_api_subject']     = '';
		}

		return $value;
	}
}


