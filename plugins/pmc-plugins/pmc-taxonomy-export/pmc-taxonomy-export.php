<?php
/*
Plugin Name: PMC Taxonomy Export
Plugin URI: http://pmc.com/
Description: Plugin to export SEO data for Taxonomy
Version: 1.0
Author: Archana Mandhare, PMC
License: PMC Proprietary.  All rights reserved.
*/

define( 'PMC_TAXONOMY_EXPORT_DIR', __DIR__ );

function pmc_taxonomy_export_loader() {

	if ( is_admin() ) {
		wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
		PMC\Taxonomy_Export\Admin::get_instance();
	}
}

pmc_taxonomy_export_loader();