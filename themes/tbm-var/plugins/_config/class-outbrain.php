<?php
/**
 * Class PMC_Outbrain
 *
 * Configures/Customizes the PMC Outbrain
 * plugin for the needs of the present theme.
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;

class Outbrain {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 *
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Initialize actions and filters.
	 *
	 *
	 */
	protected function _setup_hooks() {

		add_filter( 'pmc_outbrain_data_ob_template', array( $this, 'get_template_name' ) );

	}

	/**
	 * PMC Outbrain Template
	 *
	 * @since 2017.1.0
	 *
	 * @return string 'Variety' or 'VarietyLatino'
	 */
	public function get_template_name() {

		return 'Variety';

	}

}
