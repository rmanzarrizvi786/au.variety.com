<?php
/**
 * @author 2015-06-12 Hau Vong
 * @version 2015-06-12 Hau Vong Initial version, PPT-4976
 * @version 2017-08-22 Divyaraj Masani Refactored version no major functionality changes have been introduced
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Page_Meta {

	use Singleton;

	/**
	 * Class initialization.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Registers listeners to actions/fiters.
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		add_filter( 'pmc_page_meta', array( $this, 'filter_pmc_page_meta' ) );
		add_filter( 'pmc_krux_allowed_data_attributes', array( $this, 'filter_pmc_krux_allowed_data_attributes' ) );
		add_filter( 'pmc_google_analytics_get_custom_dimensions', array( $this, 'filter_paywall_logged_in_status' ) );

	}

	/**
	 * Called by 'pmc_page_meta' filter, this function adds logged-in status
	 * and subscriber type to the page meta array.
	 * Ignoring this function for now - will add tests ASAP
	 *
	 * @param  array $meta PMC page meta array.
	 * @return array
	 */
	public function filter_pmc_page_meta( $meta ) {
		$meta['logged-in']       = 'no';
		$meta['subscriber-type'] = 'free';

		if ( ! empty( pmc_subscription_get_user_entitlements() ) ) {
			$meta['logged-in']       = 'yes';
			$meta['subscriber-type'] = implode( ',', pmc_subscription_get_user_entitlements() );
		}

		return $meta;
	}

	/**
	 * Whitelist Variety-specific data attributes from PMC_Page_Meta for Krux.
	 *
	 * @since 2015-07-09 Hau Vong PPT-4976
	 *
	 * @version 2015-07-09 Hau Vong PPT-4976
	 *
	 * @param array $allowed_data_attributes List of key names from PMC_Page_Meta.
	 * @return array List of key names from PMC_Page_Meta that Krux can extract.
	 */
	function filter_pmc_krux_allowed_data_attributes( $allowed_data_attributes = array() ) {

		$allowed_data_attributes[] = 'logged-in';

		return $allowed_data_attributes;
	}

	/**
	 * filter_paywall_logged_in_status
	 *
	 * Adds the logged in/out status as a custom dimension for global google dimensions
	 *
	 * @param mixed $dimensions
	 *
	 * @return array
	 */
	function filter_paywall_logged_in_status( $dimensions ) {

		$meta = \PMC_Page_Meta::get_page_meta();

		if ( ! empty( $meta['logged-in'] ) ) {
			$dimensions['paywall-logged-in'] = $meta['logged-in'];
		}

		return $dimensions;
	}

}

// EOF
