<?php
namespace GGMP\Frontend\Controller;

use GGMP\Core\Controller;

class Checkout extends Controller {

	/**
	 * Process Save Data Post Profile
	 *
	 *    Display Sidebar on left side and next is main content
	 *
	 * @return string
	 * @since 1.0
	 *
	 */
	public function register_ajax_hook_callbacks() {

	}

	/**
	 * Process Save Data Post Profile
	 *
	 *    Display Sidebar on left side and next is main content
	 *
	 * @return string
	 * @since 1.0
	 *
	 */
	public function register_hook_callbacks() {
		// add_filter( 'option_woocommerce_ppec_paypal_settings', [ $this, 'woocommerce_ppec_paypal_settings', ], 10, 1 );
	}

	public function woocommerce_ppec_paypal_settings( $value ) {
		var_dump(5555); die;
		return [];
	}
}
