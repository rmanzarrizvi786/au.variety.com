<?php
/*
Plugin Name: PMC Amazon Auto Tagger
Plugin URI: http://www.pmc.com/
Author: PMC, Javier Martinez
Description: Add amazon auto tagger script to the footer
Version: 1.0
License: PMC Proprietary. All rights reserved.
*/

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
pmc_load_plugin( 'cheezcap' );

PMC\Amazon_Auto_Tagger\Tagger::get_instance();
//EOF
