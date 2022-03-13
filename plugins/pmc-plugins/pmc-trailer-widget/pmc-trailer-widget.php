<?php
/**
 * Plugin Name: PMC Trailer Video Widget
 * Description: Creates a widget for Showing Videos
 * Version: 1.0
 * Author: PMC, Amit Sannad, 10up
 * License: PMC Proprietary.  All rights reserved.
 */

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );
require_once( __DIR__ . '/class-pmc-trailer.php' );
require_once( __DIR__ . '/class-pmc-trailer-widget.php' );

$pmc_trailer = PMC_Trailer::get_instance();
//EOF
