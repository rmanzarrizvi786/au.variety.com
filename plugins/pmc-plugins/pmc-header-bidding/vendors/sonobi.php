<?php

namespace PMC\Header_Bidding\Vendors;

class Sonobi extends Base {

	const VENDOR_NAME = 'sonobi';

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

		if ( 'ad_unit' === $param_name && ! empty( $ad['key'] ) && ! empty( $ad['sitename'] ) && ! empty( $ad['zone'] ) ) {
			$param_value = apply_filters( 'pmc_adm_google_publisher_slot', sprintf( '/%s/%s/%s', $ad['key'], $ad['sitename'], $ad['zone'] ), $ad );
		}
		return $param_value;
	}
}

Sonobi::get_instance();

// EOF
