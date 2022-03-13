<?php
/**
 * Plugin Name: PMC Dateless Link
 * Description: Adds functionlity to add dateless urls.
 * Version: 1.0
 * Author: PMC
 * License: PMC Proprietary.  All rights reserved.
 * Text Domain: pmc-dateless-link
 *
 * @package pmc-shop
 */

define( 'PMC_SHOP_ROOT', __DIR__ );
define( 'PMC_SHOP_URL', plugin_dir_url( __FILE__ ) );

\PMC\Dateless_Link\Plugin::get_instance();
