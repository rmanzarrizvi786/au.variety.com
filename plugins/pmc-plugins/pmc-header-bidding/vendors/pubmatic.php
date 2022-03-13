<?php

namespace PMC\Header_Bidding\Vendors;

class Pubmatic extends Base {

	const VENDOR_NAME = 'pubmatic';

	/**
	 * Returns ad_size mapping array
	 *
	 * @return array
	 */
	public function get_ad_size_mapping() {
		$ad_size_mapping_array = array(
			'544x250' => '300x250',
			'544x251' => '300x250',
			'620x250' => '300x250',
			'620x251' => '300x250',
			'300x251' => '300x250',
			'300x252' => '300x250',
			'300x600' => '300x250',
			'160x600' => '300x250',
			'728x91'  => '728x90',
			'970x250' => '728x90',
		);

		return $ad_size_mapping_array;
	}

	/**
	 * Returns allowed ad sizes that can be passed to for bidding
	 *
	 * @return array
	 */
	public function allowed_ad_sizes() {
		$allowed_ad_sizes = array(
			'728x90',
			'300x250',
			'320x50',
		);
		return $allowed_ad_sizes;
	}

	/**
	 * Filter each param.
	 *
	 * @param string $param_value         The value of the vendor parameter.
	 * @param string $param_name          The name of the vendor parameter.
	 * @param array  $bidding_data        The vendor's bidding data.
	 * @param array  $ad                  The current ad.
	 *
	 * @return string The value of the current param.
	 */
	public function filter_param( $param_value = '', $param_name = '', $bidding_data = array(), $ad = array() ) {

		if ( empty( $param_name ) ) {
			return $param_value;
		}

		$ad_size_mapping_array = $this->get_ad_size_mapping();
		$allowed_ad_sizes = $this->allowed_ad_sizes();

		if ( empty( $ad_size_mapping_array ) || ! is_array( $ad_size_mapping_array ) ) {
			$ad_size_mapping_array = array();
		}

		if ( 'adSlot' !== $param_name || empty( $ad['ad-widths'] ) || ! is_array( $ad['ad-widths'] ) ) {
			return $param_value;
		}

		if ( empty( $ad['targeting_data'] ) || ! is_array( $ad['targeting_data'] ) ) {
			return $param_value;
		}

		foreach ( $ad['ad-widths'] as $k => $v ) {

			$ad_size = $v[0] . 'x' . $v[1];
			if ( array_key_exists( $ad_size, $ad_size_mapping_array ) ) {
				$ad_size = $ad_size_mapping_array[ $ad_size ];
			}

			if ( in_array( $ad_size, $allowed_ad_sizes ) ) {

				$pos = '';
				$device_prefix = 'desktop_';
				foreach ( $ad['targeting_data'] as $data ) {
					if ( isset( $data['key'] ) && 'pos' === $data['key'] && isset( $data['value'] ) ) {
						$pos = $data['value'];
					}
				}
				$adSlot_prefix = defined( 'PMC_SITE_NAME' ) ? PMC_SITE_NAME . '_' : '';
				//overwrite if it is passed in $bidding_data
				if ( ! empty( $bidding_data ) && ! empty( $bidding_data['adSlot_prefix'] ) ) {

					$adSlot_prefix = $bidding_data['adSlot_prefix'] . '_';
				}

				if ( \PMC::is_mobile() ) {
					$device_prefix = 'mobile_';
				}
				$param_value = $device_prefix . $adSlot_prefix . $pos . '@' . $ad_size;
				break;
			}
		}

		return $param_value;
	}
}

Pubmatic::get_instance();

// EOF
