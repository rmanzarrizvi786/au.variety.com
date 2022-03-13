<?php
/*
Plugin Name: PMC Subscription
Plugin URI: https://pmc.com
Description: Provide subscription and paywall functionality.
Version: 1.0
Author: Hau Vong, Mike Auteri, Archana Mandhare, James Mehorter
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_SUBSCRIPTION_ROOT', __DIR__ );
define( 'PMC_SUBSCRIPTION_URI', plugin_dir_url( __FILE__ ) );
define( 'PMC_SUBSCRIPTION_VERSION', '1.0' );
define( 'PMC_SUBSCRIPTION_CACHE_GROUP', 'pmc-subscription' );

require_once __DIR__ . '/dependencies.php';
require_once __DIR__ . '/inc/paywall-helpers.php';

PMC\Subscription\Plugin::get_instance();
PMC\Subscription\Paywall_Taxonomy::get_instance();
PMC\Subscription\Paywall_Posts::get_instance();

//EOF
