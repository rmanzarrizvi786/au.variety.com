<?php

namespace PMC\Uls;

use Automattic\VIP\Cache\Vary_Cache;
use PMC\Global_Functions\Traits\Singleton;

/**
 * This Caching class is responsible for managing full-page cache variants for subscribers on Vip Go.
 *
 * This class' functionality is only available on VIP Go using their Vary_Cache implementation.
 *
 * This plugin is used on Classic and Go sites, and our pipelines run in the VIP Classic context,
 * where go-only mu-plugins like Vary_Cache are not available there. Due to this, please note that
 * this class is only included when the current site is running on Go (see pmc-uls.php).
 * You'll also note that this class has been added to the <exclude> list in phpunit.xml to prevent it
 * from being inspected for code coverage (because the class won't be executed during Classic tests).
 *
 * @package PMC\Uls
 */
class Caching {

	use Singleton;

	const GROUP             = 'uls';
	const LOGGED_IN_SEGMENT = 'logged-in';
	const LOGGEDOUT_SEGMENT = 'logged-out';

	/**
	 * Initialization
	 */
	public function __construct() {

		if ( file_exists( WPMU_PLUGIN_DIR . '/cache/class-vary-cache.php' ) ) {
			require_once( WPMU_PLUGIN_DIR . '/cache/class-vary-cache.php' ); // phpcs:ignore
		}

		add_action( 'init', [ $this, 'action_init' ] );
		add_action( 'wp_ajax_nopriv_pmc_uls_do_cache_segment_ajax_callback', [ $this, 'do_cache_segment_ajax_callback' ] );
		add_action( 'wp_ajax_pmc_uls_do_cache_segment_ajax_callback', [ $this, 'do_cache_segment_ajax_callback' ] );
	}

	/**
	 * Perform functionality on WordPress' "init" action.
	 */
	public function action_init() {

		$this->_maybe_register_group();
	}

	/**
	 * Maybe register the cache group
	 */
	private function _maybe_register_group() {

		Vary_Cache::register_group( self::GROUP );
	}

	/**
	 * Given pieces, determine the appropriate name for use as a segment.
	 *
	 * @param array $pieces An array of strings. Ex:
	 *                           [ "A.X", "B.Y" ]
	 *
	 * @return string A string name representing the group. Ex.
	 *                "A-X_B-Y"
	 */
	private function _get_segment_name( array $pieces = [] ): string {

		sort( $pieces );
		$name = implode( '_', $pieces );
		$name = str_replace( '.', '-', $name );

		return $name;
	}

	/**
	 * Set a user to our cache group with an appropriate cache segment.
	 *
	 * This function is run after a subscriber has logged-in,
	 * logged-out, or after a subscriber has received new upstream data (ping).
	 *
	 * @param string $segment_override Force a user into a specific segment.
	 *
	 * @return string The name of the segment which the user was set to.
	 */
	private function _set_group_for_user( string $segment_override = '' ): string {

		$this->_maybe_register_group();

		$user_entitlements = (array) Session::get_instance()->entitlement();
		$entitlement_sets  = (array) apply_filters( 'pmc_uls_entitlement_sets', [] );

		// Determine which segment to assign the user to.

		// User's placed in the 'logged-in' segment are known/logged-in
		// but have no entitlements known to this site.
		$segment = $this->_get_segment_name( [ self::LOGGED_IN_SEGMENT ] );

		// User's with a known entitlement are set to the appropriate entitled segment.
		foreach ( $user_entitlements as $entitlement ) {
			foreach ( $entitlement_sets as $entitlement_set ) {
				if ( false !== array_search( $entitlement, (array) $entitlement_set, true ) ) {
					$segment = $this->_get_segment_name( $entitlement_set );
					break 2;
				}
			}
		}

		// Allow the chosen segment to be overridden.
		// This is primarily used when logging out a user.
		// When a user logs out, we need to remove them from the entitled segment.
		// Unfortunately, Vary_Cache does not support removing a user from a group.
		// See https://github.com/Automattic/vip-go-mu-plugins/issues/1163
		// Due to this, we place them in a 'logged-out' segment.
		$segment = empty( $segment_override ) ? $segment : $segment_override;

		Vary_Cache::set_group_for_user( self::GROUP, $segment );

		return $segment;
	}

	/**
	 * Update the user's segment cookie with any changes, or to bump it's expiration.
	 */
	public function do_cache_segment_ajax_callback() {

		$doing = \PMC::filter_input( INPUT_GET, 'doing', FILTER_SANITIZE_STRING );

		if ( 'logout' === $doing ) {
			$this->_set_group_for_user( self::LOGGEDOUT_SEGMENT );
		} else {
			$this->_set_group_for_user();
		}

		// Instruct Vary_Cache to emit the updated cache segmentation cookie
		Vary_Cache::send_headers();

		return wp_send_json_success();
	}
}
