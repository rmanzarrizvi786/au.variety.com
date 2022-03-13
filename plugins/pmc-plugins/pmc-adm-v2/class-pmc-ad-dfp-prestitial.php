<?php


/**
 * This class handles the DFP Prestitial ad that needs to before page is shown
 *
 * @author Archana Mandhare <amandhare@pmc.com>
 * @since 2016-05-04
 * @version 2016-05-04 Archana Mandhare PMCVIP-1533
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Ads_Dfp_Prestitial {

	use Singleton;

	/**
	 * This function fires off when the object of this class is created.
	 * Hook up stuff in here
	 *
	 * @since 2016-05-04
	 * @version 2016-05-04 Archana Mandhare PMCVIP-1533
	 *
	 * @return void
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'action_init' ) );
	}

	public function action_init() {
		add_action( 'pmc-tags-top', array( $this, 'action_add_dfp_prestitial_markup') );
	}

	/**
	 * Render the template that should be included in the body section at the top
	 *
	 * @since 2016-05-04
	 * @version 2016-05-04 Archana Mandhare PMCVIP-1533
	 *
	 * @return void
	 */
	public function action_add_dfp_prestitial_markup(){

		$dfp_prestitial_enabled = apply_filters( 'pmc_adm_dfp_prestitial_enabled', true );

		if ( ! $dfp_prestitial_enabled ) {
			return;
		}

		$is_dfp_prestitial = PMC_Ads::get_instance()->get_ads_to_render( 'dfp-prestitial' );

		if ( ! empty( $is_dfp_prestitial ) ) {
			include PMC_ADM_DIR . '/templates/ads/interruptus/template-dfp-prestitial.php';
		}

	}
}
