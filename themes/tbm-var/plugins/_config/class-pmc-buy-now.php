<?php
/**
 * Configuration file for PMC Buy Now plugin
 *
 * @author  Muhammad Muhsin <muhammad.muhsin@rtcamp.com>
 *
 * @since   2020-09-25
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;

class PMC_Buy_Now {

	use Singleton;

	/**
	 * PMC_Buy_Now constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup Hooks.
	 */
	protected function _setup_hooks() : void {

		add_filter( 'pmc_buy_now_data', [ $this, 'filter_pmc_buy_now_data' ] );

	}

	/**
	 * Filter to override buy now template
	 *
	 * @param array  $buy_now_data
	 *
	 * @return array
	 */
	public function filter_pmc_buy_now_data( array $buy_now_data ) : array {
		$buy_now_data['template'] = sprintf( '%s/template-parts/article/buy-now.php', CHILD_THEME_PATH );
		return $buy_now_data;
	}

}

//EOF
