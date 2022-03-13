<?php
/**
 * Enable jQuery Migrate
 *
 * ALOT of this is from https://github.com/WordPress/jquery-migrate-helper/blob/trunk/class-jquery-migrate-helper.php
 */

namespace PMC\Global_Functions;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Add jQuery Migrate back to WordPress :(
 *
 * @codeCoverageIgnore becouse this is temporary code while we wait for SADE to have the capacity to fix bugs
 */
class Jquery_Migrate {
	use Singleton;


	/*
	 * Setup filters and actions
	 *
	 * @return void
	 */
	protected function _init() : void {
		// To be able to replace the src, scripts should not be concatenated.
		if ( ! defined( 'CONCATENATE_SCRIPTS' ) ) {
			define( 'CONCATENATE_SCRIPTS', false );
		}

		// This is directly taken from the plugin
		// https://lobby.vip.wordpress.com/2020/08/06/call-for-testing-wordpress-5-5-and-the-end-of-jquery-migrate/ says to use this plugin
		//
		$GLOBALS['concatenate_scripts'] = false; // phpcs:ignore

		add_action( 'wp_default_scripts', [ __CLASS__, 'replace_scripts' ], -1 );
	}

	// Pre-register scripts on 'wp_default_scripts' action, they won't be overwritten by $wp_scripts->add().
	private static function set_script( $scripts, $handle, $src, $deps = array(), $ver = false, $in_footer = false ) {
		$script = $scripts->query( $handle, 'registered' );

		if ( $script ) {
			// If already added
			$script->src  = $src;
			$script->deps = $deps;
			$script->ver  = $ver;
			$script->args = $in_footer;

			unset( $script->extra['group'] );

			if ( $in_footer ) {
				$script->add_data( 'group', 1 );
			}
		} else {
			// Add the script
			if ( $in_footer ) {
				$scripts->add( $handle, $src, $deps, $ver, 1 );
			} else {
				$scripts->add( $handle, $src, $deps, $ver );
			}
		}
	}

	/*
	 * Enqueue jQuery migrate, and force it to be the development version.
	 *
	 * This will ensure that console errors are generated, and we can surface these to the
	 * end user in a responsible manner so that they can update their plugins and theme,
	 * or make a decision to switch to other plugin/theme if no updates are available.
	 */
	public static function replace_scripts( $scripts ) : void {
		$jq = $scripts->query( 'jquery', 'registered' );

		// on old version of WP, don't add jQuery-migrate again.
		// PHPCS seems to have a problem with channing the is_array and in_array
		if ( $jq && is_array( $jq->deps ) && in_array( 'jquery-migrate', $jq->deps, true ) ) { // phpcs:ignore
			return;
		}

		$assets_url = plugins_url( 'js/', dirname( __FILE__ ) );

		self::set_script( $scripts, 'jquery-migrate', $assets_url . 'jquery-migrate.min.js', array(), '1.4.1-wp' );
		self::set_script( $scripts, 'jquery', false, [ 'jquery-core', 'jquery-migrate' ], '1.12.4-wp' );
	}

}
