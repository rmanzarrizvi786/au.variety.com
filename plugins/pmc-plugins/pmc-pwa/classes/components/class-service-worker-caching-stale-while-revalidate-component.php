<?php
/**
 * Component to implement "Stale While Revalidate" caching strategy.
 *
 * @package pmc-pwa
 */

namespace PMC\PWA\Components;

use PMC\PWA\Service_Worker_Cache;
use WP_Service_Worker_Caching_Routes;
use WP_Service_Worker_Component;
use WP_Service_Worker_Registry_Aware;
use WP_Service_Worker_Scripts;

class Service_Worker_Caching_Stale_While_Revalidate_Component implements WP_Service_Worker_Component, WP_Service_Worker_Registry_Aware {
	/**
	 * URL registry.
	 *
	 * @var Service_Worker_Registry
	 */
	protected $_registry;

	/**
	 * Service_Worker_Caching_Stale_While_Revalidate_Component constructor.
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
			'wp-caching-routes-stale-while-revalidate',
			[
				'src'  => [ $this, 'get_script' ],
				'deps' => [ 'wp-base-config' ],
			]
		);
	}

	/**
	 * Get output priority.
	 *
	 * Priority 90 is used to place this after the built-in precaching
	 * support but before our Stale-While-Revalidate component.
	 *
	 * @return int
	 */
	public function get_priority(): int {
		return 90;
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
		$items  = $this->_registry->get_all();
		$script = '';

		foreach ( $items as $item ) {
			$script .= sprintf(
				'wp.serviceWorker.routing.registerRoute( new RegExp( %s ), new wp.serviceWorker.strategies[ %s ]( %s ) );',
				wp_service_worker_json_encode( $item['url'] ),
				wp_service_worker_json_encode( WP_Service_Worker_Caching_Routes::STRATEGY_STALE_WHILE_REVALIDATE ),
				WP_Service_Worker_Caching_Routes::prepare_strategy_args_for_js_export(
					Service_Worker_Cache::get_instance()->modify_navigation_caching_args(
						$item['args']
					)
				)
			);
		}

		return $script;
	}
}
