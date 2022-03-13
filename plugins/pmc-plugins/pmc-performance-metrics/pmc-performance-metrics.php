<?php
/**
 * Plugin Name: PMC Performance Metrics
 * Plugin URI: https://pmc.com
 * Description: Record performance metrics in analytics platforms.
 * Version: 1.0
 * License: PMC Proprietary. All rights reserved.
 * Author: PMC
*/

namespace PMC\Performance_Metrics;

define( 'PMC\\Performance_Metrics\\PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

Web_Vitals::get_instance();
