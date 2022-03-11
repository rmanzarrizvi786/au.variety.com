<?php
/**
 * Config file foe PMC Groups plugin
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since   2018-09-03
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;

class PMC_Groups {

	use Singleton;

	/**
	 * Contruct Method.
	 */
	protected function __construct() {
		add_filter( 'pmc_groups_register_group', [ $this, 'add_pmc_groups' ] );
	}

	/**
	 * To add Group in PMC Groups.
	 *
	 * @param array $groups List of PMC Groups.
	 *
	 * @return array List of PMC Groups.
	 */
	public function add_pmc_groups( $groups = [] ) {

		if ( empty( $groups ) || ! is_array( $groups ) ) {
			$groups = [];
		}

		$groups_details = array(
			array(
				'slug'        => 'pmc-variety-pmceed-494-A-B-testing',
				'description' => 'To Perform A/B Testing to display exposed thumbnail strip on Variety gallery pages',
			),
			array(
				'slug'        => 'pmc-variety-pmceed-678-A-B-testing', // @todo Remove this group once PMCEED-678 experiment done.
				'description' => 'To Perform A/B Testing to display single page gallery on Variety gallery pages',
			),
			array(
				'slug'        => 'pmc-variety-sade-68-A-B-testing', // @todo Remove this group once SADE-68 experiment done.
				'description' => 'To perform A/B testing to measure impact of page load speed on Variety articles/section-fronts pages',
			),
		);

		foreach ( $groups_details as $group_detail ) {
			$groups[ $group_detail['slug'] ] = [
				'slug'        => $group_detail['slug'],
				'description' => $group_detail['description'],
				'ticket'      => '',
				'users'       => [],
			];
		}

		return $groups;
	}
}
