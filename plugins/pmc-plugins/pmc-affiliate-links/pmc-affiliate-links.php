<?php
/*
Plugin Name: PMC Affiliate Links
Plugin URI: http://www.pmc.com
Description: Adds affiliate links if a relevant site link exists in content
Version: 1.0
Author: PMC, Javier Martinez
License: PMC Proprietary. All rights reserved.
*/

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

function pmc_affiliate_links_loader() {

	PMC\Affiliate_Links\Bootstrap::get_instance();
	PMC\Affiliate_Links\Tagger::get_instance();

}

pmc_affiliate_links_loader();

//EOF