<?php
namespace GGMP\Helper;

class Client {
		/**
	 * @return mixed
	 */
	public static function get_real_ip_addr() {
		$ip = \WC_Geolocation::get_ip_address();

		if ( ! $ip ) {
			$ip = 'ggmp';
		}

		return $ip;
	}
}
