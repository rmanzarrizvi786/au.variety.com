<?php

namespace PMC\Header_Bidding\Vendors;

class AudienceNetwork extends Base {

	const VENDOR_NAME = 'audienceNetwork';

	/**
	 * Filter each param.
	 *
	 * @param string $param_value         The value of the vendor paramater.
	 * @param string $param_name          The name of the vendor paramater.
	 * @param array  $bidding_data The vendor's bidding data.
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

		//This bidder is only works for mobile ad units.
		if ( \PMC::is_mobile() && $param_name === 'placementId' ) {
			$ad_position = $this->get_ad_position( $ad );
			$ad_position = ( 'mid' === $ad_position ) ? 'middle' : $ad_position;
			if ( ! empty( $ad_position )
			     && ! empty( $bidding_data['mobile'] )
			     && ! empty( $bidding_data['mobile'][ $ad_position ] ) ) {

				return $bidding_data['mobile'][ $ad_position ];
			}
		}

		return $param_value;
	}
}

AudienceNetwork::get_instance();

// EOF