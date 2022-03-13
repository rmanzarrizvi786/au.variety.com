<?php

/*
Plugin Name: PMC Content Publishing
Description: Article Checklist to help the editors complete all the items required for a post
Version: 1.0
Authors: PMC, Corey Gilmore, Hau Vong, Javier Martinez
License: PMC Proprietary.  All rights reserved.
Fork: vip/bgr/plugins/bgr-post-checklist
Javascript Fork: vip/pmc-plugins/pmc-post-checklist/js/pmc-post-checklist.js
Dependency: pmc_load_plugin( 'publishing-checklist' )
Since: 2015-10-20 Archana Mandhare PMCVIP-339
*/


define( 'PMC_CONTENT_PUBLISHING_ROOT', __DIR__ );
define( 'PMC_CONTENT_PUBLISHING_VERSION', '1.0' );
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

function pmc_content_publishing_checklist_loader() {

	if ( is_admin() ) {

		//load dependencies
		pmc_load_plugin( 'publishing-checklist', 'pmc-plugins' ); //VIP deprecated plugin now in our pmc-plugins repo

		//initialize plugin class
		PMC\Content_Publishing\Checklist::get_instance();
	}
}


pmc_content_publishing_checklist_loader();
//EOF
