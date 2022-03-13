<?php
/*
Plugin Name: PMC Sonobi
Description: Prints sonobi code into header before gpt.js
Version: 1.0
Author: PMC, Brandon Camenisch <bcamenisch@pmc.com>
License: PMC Proprietary.  All rights reserved
*/

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

function pmc_sonobi_loader()
{
	\PMC\Sonobi\Loader::get_instance();
}

pmc_sonobi_loader();
