<?php

namespace PMC\Header_Bidding\Vendors;

use PMC\Global_Functions\Traits\Singleton;
/**
 * Base class meant to be extended by individual vendor classes.
 *
 * Includes functionality/methods shared by all vendors.
 */
abstract class Base {

	use Singleton;

	/**
	 * Class instantiation.
	 */
	protected function _init() {
		add_filter( sprintf( 'pmc_header_bidder_filter_%s_params', static::VENDOR_NAME ), array( $this, 'filter_param' ), 10, 4 );
		add_filter( sprintf( 'pmc_header_bidder_filter_%s_alias_name', static::VENDOR_NAME ), array( $this, 'alias_name' ) );
		add_filter( sprintf( 'pmc_header_bidding_%s_outstream_params', static::VENDOR_NAME ), array( $this, 'outstream_params' ) );
	}

	/**
	 * This method is meant to be overloaded per child class
	 */
	public function filter_param() {}

	/**
	 * This method is meant to be overloaded per child class if any alias name is there
	 *
	 * @param String $bidder_name Bidder name.
	 *
	 * @return string   return bidder name if not alias is set.
	 */
	public function alias_name( $bidder_name = '' ) {
		return $bidder_name;
	}

	/**
	 * This method is meant to be overloaded per child class if any Outsream Video bidder params to add
	 *
	 * @param array $bidder_params Bidder parameters.
	 * @return string   return bidder params.
	 */
	public function outstream_params( $bidder_params = array() ) {
		return $bidder_params;
	}

	/**
	 * Get the 'pos' targeting value for a given ad.
	 *
	 * @param array $ad An array of ad data.
	 *
	 * @return string   The string position, e.g. 'top', 'bottom', etc.
	 */
	protected function get_ad_position( $ad = array() ) {

		if ( empty( $ad ) || ! is_array( $ad ) ) {
			return '';
		}

		if ( empty( $ad['targeting_data'] ) || ! is_array( $ad['targeting_data'] ) ) {
			return '';
		}

		foreach ( $ad['targeting_data'] as $key => $targeting_datum ) {

			if ( empty( $targeting_datum['key'] ) || empty( $targeting_datum['value'] ) ) {
				continue;
			}

			if ( 'pos' !== $targeting_datum['key'] ) {
				continue;
			}

			return strtolower( $targeting_datum['value'] );
		}
	}

	/**
	 * Convert a string of dimmensions to an array.
	 *
	 * E.g. '250x300' becomes array( 250, 300 )
	 *
	 * @param string $ad_size_string A string of width/height dimmensions, e.g. '250x300'.
	 *
	 * @return array An array containing elements for the dimmension width and height as integers.
	 */
	protected function get_ad_size_array_from_string( $ad_size_string = '' ) {

		if ( empty( $ad_size_string ) || ! is_string( $ad_size_string ) ) {
			return array();
		}

		$ad_size_string = strtolower( $ad_size_string );

		if ( false === strpos( $ad_size_string, 'x' ) ) {
			return array();
		}

		$ad_size_array = explode( 'x', $ad_size_string );

		$ad_size_array = array_map( 'intval', $ad_size_array );

		return $ad_size_array;
	}
}
