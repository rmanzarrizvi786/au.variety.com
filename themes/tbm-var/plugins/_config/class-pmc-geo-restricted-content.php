<?php
/**
 * Configuration for pmc-tags plugin.
 *
 * @author Jignesh Nakrani <jignesh.nakrani@rtcamp.com>
 *
 * @since 2020-03-06
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;

class PMC_Geo_Restricted_Content {

	use Singleton;

	/**
	 * Class Constructor.
	 *
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 *
	 * Add filters/actions
	 *
	 * @return void
	 */
	protected function _setup_hooks() {
		add_filter( 'pmc_replace_image_in_feed', [ $this, 'set_replacement_image_for_feed' ] );
	}

	/**
	 *
	 * To set replacement image which are not allowed in Feed.
	 *
	 * @return string replacement Image URL for feed.
	 */
	public function set_replacement_image_for_feed(): string {

		return \PMC::esc_url_ssl_friendly( VARIETY_THEME_URL . '/assets/build/images/variety-placement.png' );

	}

}
