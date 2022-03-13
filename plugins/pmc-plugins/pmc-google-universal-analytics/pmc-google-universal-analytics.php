<?php
/**
* Plugin Name: PMC Google Universal Analytics
* Description: Adds universal analytics to PMC sites
* Version: 1.0
* Author: PMC
* License: PMC Proprietary.  All rights reserved
* Text Domain: pmc-google-universal-analytics
* Domain Path: /languages
*/

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

define( 'PMC_GAUA_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

require_once( __DIR__ . '/classes/class-pmc-google-universal-analytics.php' );
require_once( __DIR__ . '/classes/class-pmc-admin-google-analytics.php' );

\PMC\Google_Universal_Analytics\Dimension_Mapping::get_instance();
