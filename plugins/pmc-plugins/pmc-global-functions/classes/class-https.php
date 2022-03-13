<?php
/**
 * Enable specific requirements for HTTPS
 */

namespace PMC\Global_Functions;

use PMC\Global_Functions\Traits\Singleton;

class HTTPS {
	use Singleton;

	/**
	 * Load the filter
	 */
	protected function __construct() {
		if ( \PMC::is_https() ) {
			if ( function_exists( 'wpcom_vip_enable_https_canonical' ) ) {
				wpcom_vip_enable_https_canonical(); // Used to alert WP that we've moved to HTTPS.
			}
		}
	}

}

