<?php
/**
 * Manage custom service-worker components.
 *
 * @package pmc-pwa
 */

namespace PMC\PWA;

use PMC\Global_Functions\Traits\Singleton;
use WP_Service_Worker_Registry_Aware;

/**
 * Class Components.
 */
class Components {
	use Singleton;

	/**
	 * Instances of various custom components.
	 *
	 * @var array
	 */
	protected $_components = [];

	/**
	 * Components constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		$this->_load();
	}

	/**
	 * Load custom components.
	 */
	protected function _load(): void {
		$components = [
			'caching_stale_while_revalidate' =>
				Components\Service_Worker_Caching_Stale_While_Revalidate_Component::class,
			'caching_forced'                 =>
				Components\Service_Worker_Caching_Forced_Component::class,
		];

		foreach ( $components as $slug => $component ) {
			$instance = new $component();

			if ( $instance instanceof WP_Service_Worker_Registry_Aware ) {
				$this->_components[ $slug ] = $instance;

				add_action(
					'wp_front_service_worker',
					[ $instance, 'serve' ],
					$instance->get_priority()
				);
			}
		}
	}

	/**
	 * Add a URL to be cached using the "Stale While Revalidate" strategy.
	 *
	 * @param string $url URL to cache.
	 */
	public function cache_url_as_stale_while_revalidate( string $url ): void {
		$this->_components['caching_stale_while_revalidate']->get_registry()->register(
			$url,
			[]
		);
	}

	/**
	 * Add a single URL to a given cache.
	 *
	 * @param string      $url   URL to cache.
	 * @param string|null $cache Destination cache.
	 */
	public function add_url_to_cache( string $url, ?string $cache = null ): void {
		if ( empty( $cache ) ) {
			$cache = Service_Worker_Cache::CACHE_NAME_NAVIGATION;
		}

		$valid_caches = [
			Service_Worker_Cache::CACHE_NAME_NAVIGATION,
			Service_Worker_Cache::CACHE_NAME_THEME_ASSETS,
			Service_Worker_Cache::CACHE_NAME_PLUGIN_ASSETS,
			Service_Worker_Cache::CACHE_NAME_UPLOADS,
		];

		// Array is set immediately before this.
		// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
		if ( ! in_array( $cache, $valid_caches, true ) ) {
			return;
		}

		$this->_components['caching_forced']->get_registry()->register(
			$url,
			compact( 'cache' )
		);
	}
}
