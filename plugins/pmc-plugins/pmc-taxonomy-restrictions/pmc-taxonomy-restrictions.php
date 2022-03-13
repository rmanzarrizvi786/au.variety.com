<?php
/**
 * Plugin Name: PMC Taxonomy Restrictions
 * Plugin URI: http://pmc.com/
 * Description: Plugin to restrict taxonomy for specific user roles.
 * Version: 1.0
 * Author: PMC, Chandra Patel <chandrakumar.patel@rtcamp.com>
 * License: PMC Proprietary.  All rights reserved.
 *
 * @package pmc-taxonomy-restrictions
 */

/**
 * Initialize plugin
 */
function pmc_taxonomy_restrictions_loader() {

	if ( ! is_admin() ) {
		return;
	}

	require_once __DIR__ . '/dependencies.php';

	\PMC\Taxonomy_Restrictions\Admin::get_instance();

	\PMC\Taxonomy_Restrictions\Post_Tag::get_instance();

}

pmc_taxonomy_restrictions_loader();
