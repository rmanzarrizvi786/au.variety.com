<?php

namespace PMC\Header_Bidding\Vendors;

/*
 * triplelift Header Bidding Vendor
 * @see http://prebid.org/dev-docs/bidders.html#triplelift
 *
 * See the example config in classes/example.config.php
 *
 * In your theme ensure you filter pmc_header_bidder_filter_vendors
 * and add the triplelift params. e.g.
 *
 * 'triplelift' => array(
 *     'inventoryCode' => ''
 * )
 *
 * Filter pmc_header_bidder_filter_bidder_params and add mapping
 * data for triplelift, e.g.
 *
 *
 * For a working example, see HollywoodLife:
 * pmc-hollywoodlife/plugins/config/pmc-header-bidder.php
 */
class Triplelift extends Base {

	const VENDOR_NAME = 'triplelift';

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

		if ( empty( $ad_size_mapping_array ) || ! is_array( $ad_size_mapping_array ) ) {
			$ad_size_mapping_array = array();
		}

		if ( 'inventoryCode' !== $param_name || empty( $ad['ad-widths'] ) || ! is_array( $ad['ad-widths'] ) ) {
			return $param_value;
		}

		if ( empty( $ad['targeting_data'] ) || ! is_array( $ad['targeting_data'] ) ) {
			return $param_value;
		}

		$pos           = $this->get_ad_position( $ad );
		$device_prefix = ( \PMC::is_mobile() ) ? 'mobile_' : 'desktop_';
		$site_prefix   = defined( 'PMC_SITE_NAME' ) ? PMC_SITE_NAME . '_' : '';

		//overwrite if it is passed in $bidding_data
		if ( ! empty( $bidding_data ) && ! empty( $bidding_data['site_prefix'] ) ) {
			$site_prefix = $bidding_data['site_prefix'] . '_';
		}

		$ad_location = ( is_array( $ad ) && isset( $ad['location'] ) ) ? $ad['location'] : '';
		if ( false !== strpos( $ad_location, 'inline-article-ad-' ) ) {
			$pos = 'mid-article';
			return $site_prefix . $device_prefix . $pos;
		}

		foreach ( $ad['ad-widths'] as $k => $v ) {

			$ad_size = $v[0] . 'x' . $v[1];
			if ( array_key_exists( $ad_size, $ad_size_mapping_array ) ) {
				$ad_size = $ad_size_mapping_array[ $ad_size ];
			}

			$param_value = $site_prefix . $device_prefix . $pos . '_' . $ad_size;
			break;

		}

		return $param_value;
	}
}

Triplelift::get_instance();
// EOF
