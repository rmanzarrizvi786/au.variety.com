<?php
/**
 * Config file for pmc-global-functions
 *
 * @since 2017-09-19 CDWE-583
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Global_Functions {

	use Singleton;

	/**
	 * Construct method for current class
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Register actions and filters
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		/**
		 * Filters
		 */

		/**
		 * Copied from old theme
		 *
		 * Also below notes is from old theme :
		 * Disabling old Amazon ads code. Once new plugin is activated and working on all sites
		 * this filter can be removed along with the old code in pmc-plugin.
		 */
		add_filter( 'pmc_amazon_ads_enabled', '__return_false' );

		add_filter( 'pmc_http_status_410_urls', array( $this, 'get_410_status_urls' ) );

	}

	/**
	 * Return list of URLs for those need to be set 410 status
	 *
	 * @see variety_redirect_legacy_urls() in pmc-variety-2014/functions.php
	 *
	 * @param  array $removed_urls List of URLs.
	 *
	 * @return array
	 */
	public function get_410_status_urls( $removed_urls ) {

		if ( empty( $removed_urls ) || ! is_array( $removed_urls ) ) {
			$removed_urls = array();
		}

		$urls = require( sprintf( '%s/templates/pmc-global-functions/410-urls.php', untrailingslashit( dirname( __DIR__ ) ) ) );

		$removed_urls = array_merge( $removed_urls, $urls );

		return $removed_urls;

	}

}

//EOF
