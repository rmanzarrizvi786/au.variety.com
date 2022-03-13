<?php
/*
Plugin Name: PMC Geo Restricted Content
Plugin URI: http://www.pmc.com
Description: Adds a post option to mark post as geo restricted.
Version: 1.0
Author: PMC, Jignesh Nakrani
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_GEO_RESTRICTED_CONTENT_ROOT', __DIR__ );
define( 'PMC_GEO_RESTRICTED_CONTENT_URL', trailingslashit( plugins_url( null, __FILE__ ) ) );

define( 'PMC_GEO_RESTRICTED_CONTENT_VERSION', '2021.10' );


function pmc_geo_restricted_content_loader() {

	/*
	 * Load dependencies
	 */
	require_once PMC_GEO_RESTRICTED_CONTENT_ROOT . '/dependencies.php';

	/*
	 * Initialize PMC Post Options Taxonomy
	 */
	\PMC\Geo_Restricted_Content\Restrict_Image_Uses::get_instance();
	\PMC\Geo_Restricted_Content\Restricted_Content::get_instance();

}

pmc_geo_restricted_content_loader();



//EOF
