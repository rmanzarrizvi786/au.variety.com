<?php

namespace PMC\Header_Bidding\Vendors;

class Appnexus extends Base {

	const VENDOR_NAME = 'appnexus';

	/**
	 * Some LOBs might have different definitions for ad placements. Define above and below the fold.
	 *
	 * @var array $_atf_definitions
	 */
	public $_atf_definitions = array( 'atf', 'top' );

	/**
	 * Array with valid values for below the fold
	 */
	public $_btf_definitions = array( 'btf', 'bottom', 'bot', 'mid', 'gallery-btf' );

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

		$device = 'desktop';

		if ( \PMC::is_mobile() ) {
			$device = 'mobile';
		}

		foreach ( $ad['targeting_data'] as $data ) {
			if ( empty( $data['key'] ) || 'pos' !== $data['key'] || empty( $data['value'] ) ) {
				continue;
			}

			$ad_position = $data['value'];

			if ( empty( $bidding_data[ $device ] ) ) {
				continue;
			}

			if ( 'inline-article-ad-1' === $ad['location'] ) {
				//At this time we only need to track top mid article ad unit
				$ad_position = 'mid-article';
			}

			if ( in_array( $ad_position, $this->_atf_definitions, true ) && ! empty( $bidding_data[ $device ]['atf'] ) ) {
				$param_value = $bidding_data[ $device ]['atf'];
				break;
			}

			if ( in_array( $ad_position, $this->_btf_definitions, true ) && ! empty( $bidding_data[ $device ]['btf'] ) ) {
				$param_value = $bidding_data[ $device ]['btf'];
				break;
			}

			if ( ! empty( $bidding_data[ $device ][ $ad_position ] ) && 'placementId' === $param_name ) {
				$param_value = $bidding_data[ $device ][ $ad_position ];
				break;
			}
		}

		return $param_value;
	}

	/**
	 * Setting alias name.
	 * @param string $bidder_name
	 *
	 * @return string
	 */
	public function alias_name( $bidder_name = '' ) {
		return 'appnexus';
	}

	/**
	 * Setting Bidder outstream params
	 *
	 * @param array $bidder_params
	 *
	 * @return string
	 * @internal param string $bidder_name
	 *
	 */
	public function outstream_params( $bidder_params = [] ) {
		$bidder_params['video'] = [
			'skippable' => true,
			'playback_method' => [
				'auto_play_sound_off',
			],
		];
		//below param will be removed once testing is done
		$bidder_params['reserve'] = 10;

		return $bidder_params;
	}

}

Appnexus::get_instance();


// EOF