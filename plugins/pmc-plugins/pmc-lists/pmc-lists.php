<?php
/**
 * Plugin Name: PMC Lists
 * Plugin URI: http://www.pmc.com
 * Version: 1.0
 * Author: XWP, PMC
 * Author URI: https://xwp.co
 * Author Email: engage@xwp.co
 * License: PMC Proprietary. All rights reserved.
 */

// Includes autoloader.
wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

// Simple Page Ordering for dragging and dropping lists.
pmc_load_plugin( 'simple-page-ordering', 'pmc-plugins' );

// Make sure cheezcap is active.
pmc_load_plugin( 'cheezcap' );

define( 'PMC_LISTS_PATH', __DIR__ );
define( 'PMC_LISTS_URL', plugins_url( '', __FILE__ ) );


$list_opt = PMC_Cheezcap::get_instance()->get_option( 'pmc_gallery_list_enabled' );

if ( 'yes' === $list_opt ) {
	return false;
}

\PMC\Lists\Lists::get_instance();
\PMC\Lists\List_Post::get_instance();
