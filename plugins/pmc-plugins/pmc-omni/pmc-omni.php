<?php
/*
Plugin Name: PMC Omni
Description: Add a visit cookie on user machine for each user per site
Version: 1.0
Author: PMC, Archana Mandhare <amandhare@pmc.com>
License: PMC Proprietary.  All rights reserved.
*/

define( 'PMC_OMNI_ROOT', __DIR__ );
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

function pmc_omni_loader(){
	\PMC\Omni\Visit_Cookie::get_instance();
}

pmc_omni_loader();
//EOF

