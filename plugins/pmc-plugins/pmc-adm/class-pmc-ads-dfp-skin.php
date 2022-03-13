<?php


/**
 * This class handles the DFP Skin ad that needs to go out-of-page
 *
 * @author Archana Mandhare <amandhare@pmc.com>
 * @since 2016-04-28
 * @version 2016-04-28 Archana Mandhare PMCVIP-1095
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Ads_Dfp_Skin {

	use Singleton;
	/**
	 * This function fires off when the object of this class is created.
	 * Hook up stuff in here
	 *
	 * @since 2016-04-28
	 * @version 2016-04-28 Archana Mandhare PMCVIP-1095
	 *
	 * @return void
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'action_init' ), 20);
	}

	public function action_init() {
		add_action( 'pmc-tags-top', array( $this, 'action_add_dfp_skin_markup') );
	}

	/**
	 * Render the template that should be included in the body section at the top
	 *
	 * @since 2016-04-28
 	 * @version 2016-04-28 Archana Mandhare PMCVIP-1095
 	 *
	 * @return void
	 */
	public function action_add_dfp_skin_markup(){

		$dfp_skin_enabled = apply_filters( 'pmc_adm_dfp_skin_enabled', true);

		if ( ! $dfp_skin_enabled ) {
			return;
		}

		$dfp_skin_template = PMC_ADM_DIR . '/templates/ads/skin/dfp-skin.php';
		PMC::render_template( $dfp_skin_template , array(), true );

	}
}

