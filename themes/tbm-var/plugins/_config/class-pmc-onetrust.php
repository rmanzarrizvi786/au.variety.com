<?php
/**
 * Configuration for pmc-onetrust plugin.
 *
 * @author Reef Fanous <rfanous@pmc.com>
 *
 * @since 2020-06-05
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;

class PMC_Onetrust {

	use Singleton;

	/**
	 * Class Constructor.
	 */
	protected function __construct() {
		add_filter( 'pmc_onetrust_config', [ $this, 'filter_pmc_onetrust_config' ] );
	}

	/**
	 * Filter for onetrust configsv
	 *
	 * @param array $options list of configs.
	 *
	 * @return array
	 */
	public function filter_pmc_onetrust_config( $options ) : array {
		$site_id = \PMC::is_production() ? 'cc6bc008-2b11-4a0f-bcad-b834f9eb865d' : '0618e9a0-571e-4d57-a43a-35e267c51f2f-test';

		$options['site_id']                 = $site_id;
		$options['ccpa_css_selector']       = "a[href='https://www.pmc.com/opt-out']";
		$options['gdpr_css_selector']       = 'nav.o-nav a.c-link.privacy-consent';
		$options['ca_privacy_css_selector'] = "a[href='https://pmc.com/privacy-policy/#california']";

		return $options;
	}

}
