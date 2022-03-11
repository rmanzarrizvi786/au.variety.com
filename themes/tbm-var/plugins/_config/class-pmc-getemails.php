<?php
/**
 * Configuration for pmc-getemails plugin
 *
 * @package pmc-variety
 */
namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

class PMC_GetEmails {

	use Singleton;

	/**
	 * PMC_GetEmails constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks() : void {
		add_filter( 'pmc_getemails_id', [ $this, 'get_id' ] );
	}

	/**
	 * GetEmails ID for script configuration.
	 *
	 * @return string
	 */
	public function get_id() : string {
		return '150HYDN';
	}

}
