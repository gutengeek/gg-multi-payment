<?php

use GGMP\Common\Query\Model\Paypal_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var Data to sanitize.
 * @return string|array
 */
function ggmp_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'ggmp_clean', $var );
	}

	return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
}

/**
 * Get Options Value by Key
 *
 * @return mixed
 *
 */
function ggmp_get_option( $key, $default = '' ) {
	global $ggmp_options;

	$value = isset( $ggmp_options[ $key ] ) ? $ggmp_options[ $key ] : $default;
	$value = apply_filters( 'ggmp_option_', $value, $key, $default );

	return apply_filters( 'ggmp_option_' . $key, $value, $key, $default );
}

/**
 * Create a new Paypal object.
 *
 * @param $id
 * @return \GGMP\Common\Model\Entity\Paypal_Entity
 */
function ggmp_paypal( $id ) {
	return new \GGMP\Common\Model\Entity\Paypal_Entity( $id );
}

/**
 * Create a new Paypal object.
 *
 * @param $id
 * @return \GGMP\Common\Model\Entity\Stripe_Entity
 */
function ggmp_stripe( $id ) {
	return new \GGMP\Common\Model\Entity\Stripe_Entity( $id );
}

if ( ! function_exists( 'ggmp_write_log' ) ) {

	/**
	 * Write log.
	 *
	 * @param $log
	 */
	function ggmp_write_log( $log ) {
		if ( true === WP_DEBUG ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}
}
