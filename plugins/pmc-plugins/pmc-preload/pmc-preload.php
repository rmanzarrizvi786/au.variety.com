<?php
/**
 * Plugin Name: PMC Preload
 * Plugin URI: https://pmc.com
 * Description: Preload assets to improve performance.
 * Version: 1.0
 * License: PMC Proprietary. All rights reserved.
 * Author: PMC
*/

namespace PMC\Preload;

define( 'PMC\\Preload\\PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

Manager::get_instance();
