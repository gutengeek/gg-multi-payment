<?php

namespace GGMP\Common\Module\Report;

class Report {
	public function __construct() {
		add_filter( 'woocommerce_reports_charts', [ $this, 'woocommerce_reports_charts' ] );
	}

	public function woocommerce_reports_charts( $reports ) {
		$reports['ggmp'] = array(
			'title'   => __( 'Payment Routing', 'ggmp' ),
			'reports' => array(
				'paypal' => array(
					'title'       => __( 'Paypal', 'ggmp' ),
					'description' => '',
					'hide_title'  => true,
					'callback'    => array( __CLASS__, 'get_paypal_report' ),
				),
				'stripe' => array(
					'title'       => __( 'Stripe', 'ggmp' ),
					'description' => '',
					'hide_title'  => true,
					'callback'    => array( __CLASS__, 'get_stripe_report' ),
				),
			),
		);

		return $reports;
	}

	/**
	 * Get a report from our reports subfolder.
	 *
	 * @param string $name
	 */
	public static function get_paypal_report( $name ) {
		$report = new Paypal();
		$report->output_report();
	}

	/**
	 * Get a report from our reports subfolder.
	 *
	 * @param string $name
	 */
	public static function get_stripe_report( $name ) {
		$report = new Stripe();
		$report->output_report();
	}
}
