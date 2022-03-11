<?php
/**
 * Menus
 *
 * Responsible for setting up VIP menus.
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Variety_VIP;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Menus
 */
class Menus {

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup hooks.
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() {

		add_action( 'init', [ $this, 'register_nav_menus' ], 21 );

	}

	/**
	 * Register nav menus for child theme.
	 */
	public function register_nav_menus() {

		$menus = [
			'pmc_variety_vip_header'        => __( 'Header - VIP', 'pmc-variety' ),
			'pmc_variety_vip_trending'      => __( 'Trending News - VIP', 'pmc-variety' ),
			'pmc_variety_vip_header_navbar' => __( 'Header Navbar - VIP', 'pmc-variety' ),
		];

		register_nav_menus( $menus );
	}

}

// EOF.
