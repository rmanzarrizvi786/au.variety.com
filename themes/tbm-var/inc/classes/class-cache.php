<?php
/**
 * Cache
 *
 * Handles Cache related functionality.
 *
 * @package pmc-variety
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Cache
 *
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Cache {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 * Initializes the filters and actions.
	 */
	protected function __construct() {
		add_action( 'transition_post_status', [ $this, 'clear_widget_cache' ], 10, 3 );
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	/**
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 */
	public function clear_widget_cache( $new_status, $old_status, $post ) {

		if ( is_admin() ) {
			$post_type_list = [ 'pmc_featured' ];

			if ( 'publish' === $new_status && in_array( $post->post_type, (array) $post_type_list, true ) ) {
				$this->set_widget_cache_group();

			}
		}
	}

	/**
	 *
	 * @return string
	 */
	public function set_widget_cache_group() {

		$cache_key = \Variety\Inc\Widgets\Variety_Base_Widget::CACHE_GROUP_KEY;

		$value = $cache_key . time();

		wp_cache_set( $cache_key, $value );

		\pmc_update_option( $cache_key, $value, $cache_key );

	}

	/**
	 */
	public function admin_init() {

		if ( is_admin() && is_user_logged_in() && isset( $_GET['post_type'] ) && 'pmc_featured' === $_GET['post_type'] ) { // phpcs:ignore
			// clear carousel cache if requested
			if ( isset( $_GET['clear_carousel_cache'] ) && 'Clear Cache' === $_GET['clear_carousel_cache'] ) { // phpcs:ignore
				$this->set_widget_cache_group();
			}
		}
	}

}
