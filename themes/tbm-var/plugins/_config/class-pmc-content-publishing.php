<?php
/**
 * Configuration file for PMC Content Publishing plugins
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since   2018-07-16
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;

class PMC_Content_Publishing {

	use Singleton;

	/**
	 * PMC_Content_Publishing constructor.
	 *
	 *
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * To setup actions and filter.
	 *
	 *
	 *
	 * @return void
	 */
	protected function _setup_hooks() {
		add_filter( 'pmc_content_publishing_checklist_default_tasks', [ $this, 'update_checklist' ] );
	}

	/**
	 * To modify task List.
	 *
	 * @param  array $tasks Task list
	 *
	 * @return array Task list
	 */
	public function update_checklist( $tasks ) {

		if ( empty( $tasks ) || ! is_array( $tasks ) ) {
			return [];
		}

		if ( ! empty( $tasks['sub_category'] ) ) {
			unset( $tasks['sub_category'] );
		}

		return $tasks;
	}
}
