<?php

/**
 * This class handles the Ads that trigger on time gap set in Ad Manager
 *
 * @author Vinod Tella <vtella@pmc.com>
 * @since 2017-09-26
 * @version 2017-09-26 Vinod Tella PMCRS-848
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Ads_Time_Gap_Trigger {

	use Singleton;

	/**
	 * Initialising Time Gap Trigger Ads
	 */
	protected function __construct() {

		add_filter( 'pmc-adm-ad-groups', array( $this, 'filter_pmc_adm_ad_groups' ) );
		add_action( 'pmc-tags-top', array( $this, 'action_pmc_ads_time_gap_tags') );
		add_filter( 'pmc_global_cheezcap_options', array( $this, 'filter_pmc_global_cheezcap_options' ) );
	}

	/**
	 * Filter hook to add ad type "Time Gap Trigger".
	 * @param array $ad_groups
	 *
	 * @return array
	 */
	public function filter_pmc_adm_ad_groups( $ad_groups = array() ){
		$ad_groups['time_gap_ads'] = 'Time Gap Trigger';
		return $ad_groups;
	}

	/**
	 * Action hook to render trigger ads script.
	 */
	public function action_pmc_ads_time_gap_tags() {
		$cookie_name =  'pmc-adi-' . md5( 'pmc-adi-time-gap-ads' );
		$time_gap = (int) \PMC_Cheezcap::get_instance()->get_option( 'pmc_ads_time_gap_trigger_ads_value' );
		if ( ! empty( $time_gap ) && $time_gap > 0 ) {
			$time_gap = ( $time_gap * 3600 ); // converting to seconds.
			PMC::render_template( PMC_ADM_DIR . '/templates/pmc_ads_time_gap_tags.php', array(
				'cookie_name' => $cookie_name,
				'time_gap'    => $time_gap,
			), true );
		}
	}

	/**
	 * Adding cheezcap option for Time gap value to trigger Time gap ads
	 *
	 * @param array $cheezcap_options List of cheezcap options.
	 *
	 * @return array $cheezcap_options
	 */
	public function filter_pmc_global_cheezcap_options( array $cheezcap_options = array() ) {
		$range = range( 0, 24 );
		if ( empty( $cheezcap_options ) || ! is_array( $cheezcap_options ) ) {
			$cheezcap_options = array();
		}

		$cheezcap_options[] = new \CheezCapDropdownOption(
			'PMC ADS Time Gap Trigger Ads',
			'Select hours to trigger Time gap ads',
			'pmc_ads_time_gap_trigger_ads_value',
			$range,
			0, // first option => No.
			$range
		);

		return $cheezcap_options;
	}
}

