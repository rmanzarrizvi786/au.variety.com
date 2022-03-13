<?php
/**
 * PMC Admantx
 *
 * @author Vinod Tella <vtella@pmc.com>
 *
 * @group pmc-admantx
 */

namespace PMC\Admantx;

use PMC\Global_Functions\Traits\Singleton;

class Plugin {

	use Singleton;

	const DEFAULT_CACHE_DURATION         = 24 * HOUR_IN_SECONDS;
	const CHEEZCAP_OPTION_CACHE_DURATION = 'pmc_admantx_cache_duration';
	const CHEEZCAP_OPTION_ENABLE         = 'pmc_admantx_enable';
	const TARGETING_KEY                  = 'admants';

	private $_api;

	protected function __construct() {
		$this->_api = Api::get_instance();
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		add_filter( 'pmc_adm_prepare_boomerang_global_settings', [ $this, 'filter_pmc_adm_prepare_boomerang_global_settings' ] );
		add_filter( 'pmc_google_amp_json_config', [ $this, 'filter_pmc_google_amp_json_config' ] );
		add_filter( 'pmc_cheezcap_groups', [ $this, 'filter_cheezcap_options' ] );
	}

	public function filter_cheezcap_options( $cheezcap_groups = [] ) {
		if ( empty( $cheezcap_groups ) || ! is_array( $cheezcap_groups ) ) {
			$cheezcap_groups = [];
		}

		$cheezcap_options = [
			new \CheezCapDropdownOption(
				wp_strip_all_tags( __( 'Enable ADmantX', 'pmc-admantx' ), true ),
				wp_strip_all_tags( __( 'Enabling the ADmantX service', 'pmc-admantx' ), true ),
				self::CHEEZCAP_OPTION_ENABLE,
				[ 'no', 'yes' ],
				1, // first option => No, 2nd option = Yes
				[ wp_strip_all_tags( __( 'No', 'pmc-admantx' ), true ), wp_strip_all_tags( __( 'Yes', 'pmc-admantx' ), true ) ]
			),

			new \CheezCapTextOption(
				wp_strip_all_tags( __( 'Cache duration in seconds', 'pmc-admantx' ), true ),
				wp_strip_all_tags( __( 'Enter the cache duration in seconds', 'pmc-admantx' ), true ),
				self::CHEEZCAP_OPTION_CACHE_DURATION,
				self::DEFAULT_CACHE_DURATION
			),

		];

		$cheezcap_groups[] = new \CheezCapGroup( 'ADmantX', 'pmc_admantx_group', $cheezcap_options );

		return $cheezcap_groups;
	}

	/**
	 * Add ADmantX targeting to ad calls.
	 *
	 * @param array $data
	 * @return array
	 */
	public function filter_pmc_adm_prepare_boomerang_global_settings( $data ) {

		$data['targeting_data'][ self::TARGETING_KEY ] = $this->_api->get();

		return $data;

	}

	/**
	 * Add ADmantX targeting to AMP ad calls if it hasn't already been added
	 * previously by the pmc_adm_prepare_boomerang_global_settings filter.
	 *
	 * @param array $data
	 * @return array
	 */
	public function filter_pmc_google_amp_json_config( $data ) {

		if ( empty( $data['targeting'][ self::TARGETING_KEY ] ) ) {
			$data['targeting'][ self::TARGETING_KEY ] = $this->_api->get();
		}

		return $data;

	}

}
