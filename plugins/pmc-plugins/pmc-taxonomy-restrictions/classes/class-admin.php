<?php
/**
 * Admin related functionality
 *
 * @package pmc-taxonomy-restrictions
 */

namespace PMC\Taxonomy_Restrictions;

use \PMC\Global_Functions\Traits\Singleton;
use \CheezCapGroup;


class Admin {

	use Singleton;

	/**
	 * Register various hooks
	 */
	protected function __construct() {

		add_filter( 'pmc_cheezcap_groups', array( $this, 'filter_pmc_cheezcap_groups' ) );

	}

	/**
	 * Add new cheezcap group and options.
	 *
	 * @internal pmc_cheezcap_groups
	 *
	 * @param array $cheezcap_groups An array of cheezcap groups.
	 *
	 * @return array
	 */
	public function filter_pmc_cheezcap_groups( $cheezcap_groups = array() ) {

		if ( empty( $cheezcap_groups ) || ! is_array( $cheezcap_groups ) ) {
			$cheezcap_groups = array();
		}

		$cheezcap_options = apply_filters( 'pmc_taxonomy_restrictions_cheezcap_options', array() );

		if ( ! empty( $cheezcap_options ) ) {
			$cheezcap_groups[] = new CheezCapGroup( 'Taxonomy', 'pmc_taxonomy_group', $cheezcap_options );
		}

		return $cheezcap_groups;

	}

}
