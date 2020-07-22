<?php
namespace GGMP\Admin\Setting;

use GGMP\Core as Core;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 */
class General extends Core\Metabox {

	/**
	 * Register User Shortcodes
	 *
	 * Define and register list of user shortcodes such as register form, login form, dashboard shortcode
	 */
	public function get_tab() {
		return [ 'id' => 'general', 'heading' => esc_html__( 'General' ) ];
	}

	/**
	 * Register User Shortcodes
	 *
	 * Define and register list of user shortcodes such as register form, login form, dashboard shortcode
	 *
	 * @since    1.0.0
	 */
	public function get_settings() {
		$fields = [
			[
				'id'          => 'enable_paypal',
				'name'        => esc_html__( 'Enable PayPal Routing', 'ggmp' ),
				'type'        => 'switch',
				'default'     => 'on',
				'description' => sprintf( __( 'Please install and activate <a href="%s" target="_blank">WooCommerce PayPal Checkout Payment Gateway</a> to use this feature.', 'ggmp' ),
					esc_url( 'https://wordpress.org/plugins/woocommerce-gateway-paypal-express-checkout/' ) ),
			],
			[
				'id'          => 'enable_stripe',
				'name'        => esc_html__( 'Enable Stripe Routing', 'ggmp' ),
				'type'        => 'switch',
				'default'     => 'on',
				'description' => sprintf( __( 'Please install and activate <a href="%s" target="_blank">WooCommerce Stripe Payment Gateway plugin</a> to use this feature.', 'ggmp' ),
					esc_url( 'https://wordpress.org/plugins/woocommerce-gateway-stripe/' ) ),
			],
			[
				'id'          => 'limit_money_per_day',
				'name'        => esc_html__( 'Default limit money per day', 'ggmp' ),
				'type'        => 'text_number',
				'default'     => '300',
				'description' => esc_html__( 'Default limit money per day', 'ggmp' ),
			],
		];

		return apply_filters( 'ggmp_settings_general', $fields );
	}
}
