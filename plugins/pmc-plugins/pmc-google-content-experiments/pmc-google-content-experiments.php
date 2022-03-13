<?php
/*
Plugin Name: PMC Google Content Experiments A/B Testing
Description: This plugin implements A/B testing with Google's Content Experiments (CX)
             https://developers.google.com/analytics/solutions/experiments

             See the README for detailed usage.

Version: 1.5
Author: PMC, James Mehorter <james.mehorter@pmc.com>
*/

use PMC\Google_Content_Experiments as CX;

define( 'PMC_GOOGLE_CX_ROOT', trailingslashit( __DIR__ ) );
define( 'PMC_GOOGLE_CX_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

require_once( __DIR__ . '/inc/helpers.php' );
require_once( __DIR__ . '/inc/admin.php' );

// These classes are autoloaded
CX\API::get_instance();
CX\Loader::get_instance();

// EOF