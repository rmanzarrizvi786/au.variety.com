<?php
/**
 * RubiconLite Vendor configuration.
 * This is actually a VOX partner using Rubiconlite config
 *
 * @author Vinod Tella <vtella@pmc.com>
 *
 * @since 2018-07-17 READS-1352
 */

namespace PMC\Header_Bidding\Vendors;

use \PMC;

class RubiconLite extends Base {

	const VENDOR_NAME = 'rubiconLite';

	/**
	 * Filter each param.
	 *
	 * @param string $param_value         The value of the vendor paramater.
	 * @param string $param_name          The name of the vendor paramater.
	 * @param array  $bidding_data        The vendor's bidding data.
	 * @param array  $ad                  The current ad.
	 *
	 * @return string The value of the current param.
	 */
	public function filter_param( $param_value = '', $param_name = '', $bidding_data = array(), $ad = array() ) {

		$device = ( PMC::is_mobile() ) ? 'mobile' : 'desktop';

		if (
			! empty( $ad['location'] ) &&
			! empty( $bidding_data[ $device ][ $ad['location'] ][ $param_name ] )
		) {

			$param_value = $bidding_data[ $device ][ $ad['location'] ][ $param_name ];

		}

		return $param_value;
	}

}
