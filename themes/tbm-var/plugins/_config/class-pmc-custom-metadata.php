<?php
/**
 * Config for Custom Metadata plugin
 *
 * @author  Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @since   2018-02-13 READS-994
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Custom_Metadata {

	use Singleton;

	/**
	 * Construct Method
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup listeners to WP hooks
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		/**
		 * Filters
		 */
		add_filter( 'sanitize_post_meta__pmc_featured_video_override_data', 'variety_filter_youtube_url' );

	}

} // end class

//EOF
