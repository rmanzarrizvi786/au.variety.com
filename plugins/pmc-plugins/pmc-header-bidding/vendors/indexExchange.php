<?php

namespace PMC\Header_Bidding\Vendors;

/*
 * indexExchange Header Bidding Vendor
 * @see http://prebid.org/dev-docs/bidders.html#indexExchange
 *
 * See the example config in classes/example.config.php
 *
 * In your theme ensure you filter pmc_header_bidder_filter_vendors
 * and add the indexExchange params. e.g.
 *
 * 'indexExchange' => array(
 *     'id' => '',
 *     'siteID' => ''
 * )
 *
 * Filter pmc_header_bidder_filter_bidder_params and add mapping
 * data for indexExchange, e.g.
 *
 * 'indexExchange' => array(
 *      'desktop' => array(
 *          ...
 *      ),
 *      'mobile' => array(
 *          ...
 *      ),
 * )
 *
 * For a working example, see HollywoodLife:
 * pmc-hollywoodlife/plugins/config/pmc-header-bidder.php
 */
class IndexExchange extends Base {

	const VENDOR_NAME = 'indexExchange';

	/**
	 * Filter each param.
	 *
	 * @param string $param_value         The value of the vendor paramater.
	 *                                    Empty by default.
	 *
	 * @param string $param_name          The name of the vendor paramater.
	 *                                    e.g. 'id' or 'siteID'
	 *
	 * @param array  $vendor_bidding_data The vendor's bidding data. E.g.
	 *                                    array(
	 *                                       'desktop' => array(
	 *                                           'top' => array(
	 *                                               array(
	 *                                                   'id' => 10,
	 *                                                   'siteID' => '190051',
	 *                                                   'ad-sizes' => array(
	 *                                                       '970x250',
	 *                                                   ),
	 *                                               ),
	 *                                               array(
	 *                                                   'id' => 11,
	 *                                                   'siteID' => '190052',
	 *                                                   'ad-sizes' => array(
	 *                                                       '300x600',
	 *                                                       '300x250',
	 *                                                   ),
	 *                                               ),
	 *                                           ),
	 *                                           'mid' => array(
	 *                                               array(
	 *                                                   'id' => 12,
	 *                                                   'siteID' => '190053',
	 *                                                   'ad-sizes' => array(
	 *                                                       '300x250',
	 *                                                   ),
	 *                                               ),
	 *                                           ),
	 *                                       ),
	 *                                       'mobile' => array(
	 *                                           ...
	 *                                       ),
	 *                                   )
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

		// Bail if the ad is missing targeting and dimmensions
		if ( empty( $ad['targeting_data'] ) || ! is_array( $ad['targeting_data'] ) ) {
			return $param_value;
		}

		if ( empty( $ad['ad-widths'] ) || ! is_array( $ad['ad-widths'] ) ) {
			return $param_value;
		}

		$device = 'desktop';

		if ( \PMC::is_mobile() ) {
			$device = 'mobile';
		}

		// Bail if there is no bidding data for the current device
		if ( empty( $bidding_data[ $device ] ) || ! is_array( $bidding_data[ $device ] ) ) {
			return $param_value;
		}

		$bidding_ad_positions = $bidding_data[ $device ];

		$ad_position = $this->get_ad_position( $ad );

		// Bail if there is no bidding data for the current ad's position
		if ( empty( $bidding_ad_positions[ $ad_position ] ) ) {
			return $param_value;
		}

		// Loop through the vendor's device/ad position bidding data
		foreach ( $bidding_ad_positions[ $ad_position ] as $key => $ad_bidding_datum ) {

			// Bail if the bidding data doesn't contain a value for the current param
			if ( empty( $ad_bidding_datum[ $param_name ] ) ) {
				continue;
			}

			// Bail if the bidding data doesn't contain ad sizes
			if ( empty( $ad_bidding_datum['ad-sizes'] ) || ! is_array( $ad_bidding_datum['ad-sizes'] ) ) {
				continue;
			}

			// Loop through the bidding data's proposed ad sizes
			foreach ( $ad['ad-widths'] as $key => $ad_dimmensions ) {

				// Loop through the ad's actual sizes
				foreach ( $ad_bidding_datum['ad-sizes'] as $key => $vendor_ad_size ) {

					// Convert the string ad size, e.g. '300x250' to an array of integers, i.e. array( 300, 250 )
					$vendor_ad_size_array = $this->get_ad_size_array_from_string( $vendor_ad_size );

					// Skip this bidder's proposed ad size if it doesn't fit within the current ad
					if ( $ad_dimmensions[0] < $vendor_ad_size_array[0] ) {
						continue;
					}
					if ( $ad_dimmensions[1] < $vendor_ad_size_array[1] ) {
						continue;
					}

					// OK, cool, this bidding datum is for the current device, it matches
					// the ad's position targeting, and the current proposed width/height
					// fit within the ad's actual width/height—now we know which param
					// to send in the bidding data.
					$param_value = $ad_bidding_datum[ $param_name ];

					// We've got bidding info for this ad,
					// let's stop looking through the bidding data.
					break 3;
				}
			}
		}

		return $param_value;
	}
}

IndexExchange::get_instance();


// EOF