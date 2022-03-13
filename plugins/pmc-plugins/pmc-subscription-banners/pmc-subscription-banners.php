<?php
/*
Plugin Name: PMC Subscription Banners
Plugin URI: https://pmc.com
Description: Provide subscription Banners on the frontend and Global Curation option to configure those from wp-admin
Version: 1.0
Author: Archana Mandhare
License: PMC Proprietary. All rights reserved.
*/

define( 'PMC_SUBSCRIPTION_BANNERS_ROOT', __DIR__ );
define( 'PMC_SUBSCRIPTION_BANNERS_URI', plugin_dir_url( __FILE__ ) );
define( 'PMC_SUBSCRIPTION_BANNERS_VERSION', '1.0' );

require_once __DIR__ . '/dependencies.php';

PMC\Subscription_Banners\Admin::get_instance();

//EOF
