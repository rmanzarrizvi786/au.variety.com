<?php
/**
 * Component to force items to be added to cache.
 *
 * @package pmc-pwa
 */

namespace PMC\PWA\Components;

use PMC\PWA\Service_Worker_Cache;
use WP_Service_Worker_Component;
use WP_Service_Worker_Registry_Aware;
use WP_Service_Worker_Scripts;

/**
 * Class Service_Worker_Caching_Forced_Component.
 */
class Service_Worker_Caching_Forced_Component implements WP_Service_Worker_Component, WP_Service_Worker_Registry_Aware {
	/**
	 * URL registry.
	 *
	 * @var Service_Worker_Registry
	 */
	protected $_registry;

	/**
	 * Service_Worker_Caching_Forced_Component constructor.
	 */
	public function __construct() {
		$this->_registry = new Service_Worker_Registry();
	}

	/**
	 * Register component for output in service worker.
	 *
	 * @param WP_Service_Worker_Scripts $scripts Instance of service-worker scripts manager.
	 */
	public function serve( WP_Service_Worker_Scripts $scripts ) {
		$scripts->register(
			'wp-caching-routes-forced-cache',
			[
				'src'  => [ $this, 'get_script' ],
				'deps' => [ 'wp-base-config' ],
			]
		);
	}

	/**
	 * Get output priority.
	 *
	 * Priority 91 is used to place this after the built-in precaching
	 * support and our Stale-While-Revalidate component, but before
	 * navigation preloading configuration.
	 *
	 * @return int
	 */
	public function get_priority(): int {
		return 91;
	}

	/**
	 * Retrieve component's registry.
	 *
	 * @codeCoverageIgnore Enforced by return type.
	 *
	 * @return Service_Worker_Registry
	 */
	public function get_registry(): Service_Worker_Registry {
		return $this->_registry;
	}

	/**
	 * Build script output.
	 *
	 * @return string
	 */
	public function get_script(): string {
		$urls = array_filter(
			$this->_registry->get_all(),
			static function( $item ): bool {
				return Service_Worker_Cache::CACHE_NAME_NAVIGATION === $item['args']['cache'];
			}
		);

		if ( empty( $urls ) ) {
			return '';
		}

		$urls = wp_list_pluck( $urls, 'url' );

		return sprintf(
			'caches.open(`${wp.serviceWorker.core.cacheNames.prefix}-%1$s`).then((caches)=> {caches.addAll(%2$s);});',
			Service_Worker_Cache::CACHE_NAME_NAVIGATION,
			wp_service_worker_json_encode( $urls )
		);
	}
}
