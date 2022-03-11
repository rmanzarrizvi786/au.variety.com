<?php
/**
 * Configuration file for pmc-facebook-instant-articles plugin.
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since   2017-09-20 - CDWE-660
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Extend 'pmc-facebook-instant-articles' functionality.
 */
class PMC_Facebook_Instant_Articles {

	use Singleton;

	/**
	 * Construct Method.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * To setup actions/filters.
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		/**
		 * Filters
		 */
		add_filter( 'pmc_fbia_recirculation_ad_placement_id', [ $this, 'filter_recirculation_ad_placement_id' ] );

	}

	/**
	 * Return recirculation Ad placement ID
	 *
	 * @return string
	 */
	public function filter_recirculation_ad_placement_id() {

		return '1279468935412340_1929926067033287';
	}

}
