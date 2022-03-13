<?php
/*
Plugin Name: PMC Custom Menu Items
Description: Adds and implements ability to add anything to WP Nav Menu
Version: 1.0
Author: PMC, Amit Gupta
License: PMC Proprietary.  All rights reserved.
*/

define( 'PMC_CUSTOM_MENU_ITEMS_DIR', __DIR__ );

function pmc_custom_menu_items_plugin_loader() {
	/**
	 * load dependencies
	 */
	require_once PMC_CUSTOM_MENU_ITEMS_DIR . '/dependencies.php';


	/**
	 * load classes
	 */
	require_once PMC_CUSTOM_MENU_ITEMS_DIR . '/controllers/class-pmc-custom-menu-items.php';

	if ( is_admin() ) {
		require_once PMC_CUSTOM_MENU_ITEMS_DIR . '/controllers/class-pmc-custom-menu-items-admin.php';
	} else {
		require_once PMC_CUSTOM_MENU_ITEMS_DIR . '/controllers/class-pmc-custom-menu-items-maker.php';
	}

	/**
	 * Init classes
	 */
	if ( is_admin() ) {
		PMC_Custom_Menu_Items_Admin::get_instance();
	} else {
		PMC_Custom_Menu_Items_Maker::get_instance();
	}
}

pmc_custom_menu_items_plugin_loader();


//EOF