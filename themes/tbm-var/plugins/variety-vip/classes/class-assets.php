<?php
/**
 * Assets
 *
 * Responsible for loading VIP related assets.
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Variety_VIP;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC;

/**
 * Class Assets
 */
class Assets {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup hooks.
	 *
	 */
	protected function _setup_hooks() {

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 11 );

	}

	/**
	 * Enqueue Assets.
	 */
	public function enqueue_assets() {

		if ( Content::is_vip_page() ) {

			\PMC\Core\Inc\Assets::get_instance()->inline_style( 'variety-vip.inline', CHILD_THEME_PATH );

			$fmtime = filemtime( CHILD_THEME_PATH . '/assets/build/css/variety-vip.async.css' );

			wp_register_style(
				'variety-vip.async',
				PMC::get_asset_path( '/assets/build/css/variety-vip.async.css' ),
				[],
				$fmtime,
				'all'
			);

			wp_enqueue_style( 'variety-vip.async' );

		}

		// Load this on all pages as we want to check if user is logged in on VIP or not
		// on whole site to be able to display appropriate menu in header
		wp_enqueue_script(
			'variety-vip-js',
			PMC::get_asset_path( '/assets/build/js/variety_vip.js' ),
			[],
			false,
			true
		);

	}

}

// EOF.
