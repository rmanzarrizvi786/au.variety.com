<?php

namespace PMC\Header_Bidding\Vendors;

class Sovrn extends Base {

	const VENDOR_NAME = 'sovrn';

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
	 * @param string $param_value         The value of the vendor paramater.
	 * @param string $param_name          The name of the vendor paramater.
	 * @param array  $vendor_bidding_data The vendor's bidding data.
	 * @param array  $ad                  The current ad.
	 *
	 * @return string The value of the current param.
	 */
	public function filter_param( $param_value = '', $param_name = '', $bidding_data = array(), $ad = array() ) {

		if ( empty( $param_name ) ) {
			return $param_value;
		}

		if ( empty( $bidding_data ) || ! is_array( $bidding_data ) ) {
			return $param_value;
		}

		if ( empty( $ad['targeting_data'] ) || ! is_array( $ad['targeting_data'] ) ) {
			return $param_value;
		}

		$ad_size_mapping_array = $this->get_ad_size_mapping();
		$allowed_ad_sizes = $this->allowed_ad_sizes();

		if ( empty( $ad_size_mapping_array ) || ! is_array( $ad_size_mapping_array ) ) {
			$ad_size_mapping_array = array();
		}

		if ( 'tagid' !== $param_name || empty( $ad['ad-widths'] ) || ! is_array( $ad['ad-widths'] ) ) {
			return $param_value;
		}

		foreach ( $ad['ad-widths'] as $k => $v ) {
			if ( is_array( $v ) && count( $v ) > 1 ) {
				$ad_size = $v[0] . 'x' . $v[1];
				if ( array_key_exists( $ad_size, $ad_size_mapping_array ) ) {
					$ad_size = $ad_size_mapping_array[ $ad_size ];
				}

				if ( in_array( $ad_size, $allowed_ad_sizes, true ) ) {
					if ( ! empty( $bidding_data['atf'] ) && ! empty( $bidding_data['btf'] ) ) {
						$ad_position = $this->get_ad_position( $ad );
						$key_position = ( 'top' === $ad_position || 'adhesion' === $ad_position ) ? 'atf': 'btf';
						if ( ! empty( $bidding_data[ $key_position ][ $ad_size ] ) ) {
							return $bidding_data[ $key_position ][ $ad_size ];
						}
					} else {
						// Old way - If site configs doesn't have id's by atf and btf.
						if ( ! empty( $bidding_data[ $ad_size ] ) ) {
							return $bidding_data[ $ad_size ];
						}
					}
				}
			}
		}
		return $param_value;
	}
}

Sovrn::get_instance();
