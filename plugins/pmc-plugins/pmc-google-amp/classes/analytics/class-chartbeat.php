<?php
/**
 * Class to add Chartbeat analytics to Google Amp
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2020-04-13
 */

namespace PMC\Google_Amp\Analytics;

use \PMC\Global_Functions\Traits\Singleton;

class Chartbeat {

	use Singleton;

	/**
	 * Class constructor
	 *
	 * @codeCoverageIgnore Class constructor does not need code coverage, because it just calls other methods which should have their own code coverage.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Method to set up listeners to hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() : void {

		/*
		 * Filters
		 */
		add_filter( 'amp_post_template_analytics', [ $this, 'get_setup_data' ] );

	}

	/**
	 * Method to return chartbeat setup data.
	 * This is hooked to 'amp_post_template_analytics' filter.
	 *
	 * @param array $analytics
	 *
	 * @return array
	 */
	public function get_setup_data( $analytics = [] ) : array {

		$analytics = ( ! is_array( $analytics ) ) ? [] : $analytics;

		$data = apply_filters( 'pmc_chartbeat_setup_data', [] );
		$data = ( ! is_array( $data ) ) ? [] : $data;

		if ( empty( $data ) || empty( $data['config_data'] ) ) {
			return $analytics;
		}

		$attributes = ( ! empty( $data['attributes'] ) ) ? wp_json_encode( $data['attributes'] ) : [];

		$analytics['chartbeat'] = [
			'type'        => 'chartbeat',
			'attributes'  => $attributes,
			'config_data' => $data['config_data'],    // this will be json encoded by AMP plugin automatically
		];

		return $analytics;

	}

}    // end class

//EOF
