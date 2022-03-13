<?php
/**
 * Rubicon Vendor configuration.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @since 2018-04-11 READS-801
 */

namespace PMC\Header_Bidding\Vendors;

class Rubicon extends Base {

	const VENDOR_NAME = 'rubicon';

	/**
	 * Array with valid values for above the fold.
	 *
	 * @var array $_atf_definitions
	 */
	protected $_atf_definitions = array( 'atf', 'top' );

	/**
	 * Array with valid values for below the fold
	 */
	protected $_btf_definitions = array( 'btf', 'bottom', 'bot', 'gallery-btf' );

	/**
	 * Array with valid values for mid articles.
	 */
	protected $_mid_definitions = array( 'mid', 'mid-article' );

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

		if ( empty( $bidding_data[ $device ] ) ) {
			return $param_value;
		}

		foreach ( $ad['targeting_data'] as $data ) {

			if ( empty( $data['key'] ) || 'pos' !== $data['key'] || empty( $data['value'] ) ) {
				continue;
			}

			$ad_position = $data['value'];

			if ( in_array( $ad_position, $this->_atf_definitions, true ) && ! empty( $bidding_data[ $device ]['atf'][ $param_name ] ) ) {
				$param_value = $bidding_data[ $device ]['atf'][ $param_name ];
				break;
			}

			if ( in_array( $ad_position, $this->_btf_definitions, true ) && ! empty( $bidding_data[ $device ]['btf'][ $param_name ] ) ) {
				$param_value = $bidding_data[ $device ]['btf'][ $param_name ];
				break;
			}

			if ( in_array( $ad_position, $this->_mid_definitions, true ) && ! empty( $bidding_data[ $device ]['mid-article'][ $param_name ] ) ) {
				$param_value = $bidding_data[ $device ]['mid-article'][ $param_name ];
				break;
			}
		}

		return $param_value;
	}

}

Rubicon::get_instance();
