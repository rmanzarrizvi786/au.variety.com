<?php
/**
 * This file contains the Menus class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API;

/**
 * Menus class.
 */
class Menus {

	/**
	 * Register the menus.
	 */
	public function register_menus() {
		$default_menus = [
			'main_menu_mobile_app' => __( 'Main Menu Mobile App', 'pmc-mobile-api' ),
		];

		$default_menus = apply_filters( 'pmc_mobile_api_menus', $default_menus );

		register_nav_menus( $default_menus );
	}
}
