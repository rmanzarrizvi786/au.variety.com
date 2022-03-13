<?php
namespace PMC\Google_Universal_Analytics;

use PMC\Global_Functions\Traits\Singleton;

class Dimension_Mapping {
	use Singleton;

	protected function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		add_filter( 'pmc_ga_mapped_ec_product_field', [ $this, 'filter_pmc_ga_mapped_ec_product_field' ] );
	}

	/**
	 * Return the custom dimension mapping for the ecommerce product fields
	 * @return int[]
	 *
	 * @see https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#enhanced-ecomm
	 */
	private function _get_product_dimension_map() : array {
		$dimension_map = [
			'product_url' => 1,
			'url'         => 1,
		];
		return $dimension_map;
	}

	/**
	 * Translate the $product into ga mapped product dimension
	 * Do not call this method directly, use apply_filter( 'pmc_ga_mapped_ec_product_field', $product );
	 *
	 * @param $product
	 * @return array
	 */
	public function filter_pmc_ga_mapped_ec_product_field( $product ) {
		$mapped_data           = [];
		$product_to_ga_mapping = $this->_get_product_dimension_map();
		foreach ( $product as $key => $value ) {
			if ( isset( $product_to_ga_mapping[ $key ] ) ) {
				$mapped_data[ 'dimension' . $product_to_ga_mapping[ $key ] ] = $value;
			} else {
				$mapped_data[ $key ] = $value;
			}

		}
		return $mapped_data;
	}

}
