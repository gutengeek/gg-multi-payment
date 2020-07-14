<?php
namespace GGMP\Helper;

class Client {
	protected $id;

	protected $record;

	public function __construct( $id, $record = 8 ) {

		$this->id = $id;

		$this->record = $record;

		// $this->count_page_stats();
	}

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

	/**
	 * Count page views.
	 */
	public function count_page_stats() {
		// Get IPs.
		$ips = $this->get_ips_viewed();

		$current_ip = static::get_real_ip_addr();

		if ( ! in_array( $current_ip, $ips ) ) {
			// Update IPS.
			array_push( $ips, $current_ip );
			update_post_meta( $this->id, 'opalestate_ips_viewed', $ips );

			// Count and update total views.
			$total_views = intval( get_post_meta( $this->id, 'opalestate_total_views', true ) );
			if ( $total_views == '' ) {
				$total_views = 1;
			} else {
				$total_views++;
			}

			update_post_meta( $this->id, 'opalestate_total_views', $total_views );

			// Update detailed views.
			$today          = date( 'm-d-Y', time() );
			$detailed_views = get_post_meta( $this->id, 'opalestate_detailed_views', true );

			if ( $detailed_views == '' || ! is_array( $detailed_views ) ) {
				$detailed_views           = [];
				$detailed_views[ $today ] = 1;
			} else {
				if ( ! isset( $detailed_views[ $today ] ) ) {
					if ( count( $detailed_views ) > 15 ) {
						array_shift( $detailed_views );
					}

					$detailed_views[ $today ] = 1;
				} else {
					$detailed_views[ $today ] = intval( $detailed_views[ $today ] ) + 1;
				}
			}

			$detailed_views = update_post_meta( $this->id, 'opalestate_detailed_views', $detailed_views );
		}
	}


	public function get_traffic_labels() {
		$detailed_views = get_post_meta( $this->id, 'opalestate_detailed_views', true );

		if ( ! is_array( $detailed_views ) ) {
			$detailed_views = [];
		}

		$array_label = array_keys( $detailed_views );
		$array_label = array_slice( $array_label, -1 * $this->record, $this->record, false );

		return $array_label;
	}


	public function get_traffic_data() {
		$detailed_views = get_post_meta( $this->id, 'opalestate_detailed_views', true );
		if ( ! is_array( $detailed_views ) ) {
			$detailed_views = [];
		}
		$array_values = array_values( $detailed_views );
		$array_values = array_slice( $array_values, -1 * $this->record, $this->record, false );

		return $array_values;
	}


	public function get_traffic_data_accordion() {
		$detailed_views = get_post_meta( $this->id, 'opalestate_detailed_views', true );
		if ( ! is_array( $detailed_views ) ) {
			$detailed_views = [];
		}

		// since this runs before we increment the visits - on acc page style
		$today = date( 'm-d-Y', time() );

		if ( isset( $detailed_views[ $today ] ) ) {
			$detailed_views[ $today ] = intval( $detailed_views[ $today ] ) + 1;
		}

		$array_values = array_values( $detailed_views );
		$array_values = array_slice( $array_values, -1 * $this->record, $this->record, false );

		return $array_values;
	}

	/**
	 * Get IPs viewed.
	 *
	 * @return array
	 */
	public function get_ips_viewed() {
		$ips = get_post_meta( $this->id, 'opalestate_ips_viewed', true );
		if ( ! $ips ) {
			$ips = [];
		}

		return $ips;
	}
}
