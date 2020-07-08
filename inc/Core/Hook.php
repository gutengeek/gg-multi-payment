<?php
namespace GGMP\Core;

// Exit if accessed directly.
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
	}

	/**
	 * Change paypal settings.
	 *
	 * @param $value
	 * @return array
	 */
	public function woocommerce_ppec_paypal_settings( $value ) {
		$value['api_username']    = '';
		$value['api_password']    = '';
		$value['api_signature']   = '';
		$value['api_certificate'] = '';
		$value['api_subject']     = '';

		$value['sandbox_api_username']    = 'sb-rekv52535458_api1.business.example.com';
		$value['sandbox_api_password']    = 'QUE94LPBNRQ24XA2';
		$value['sandbox_api_signature']   = 'ATbs90-YxFSTTqSObnL1GMPybaigAqCzsltLAbgnVjZKLEt0yOKJpFma';
		$value['sandbox_api_certificate'] = '';
		$value['sandbox_api_subject']     = '';

		return $value;
	}
}


