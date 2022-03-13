<?php
/**
 * Registry for custom "Stale While Revalidate" component.
 *
 * @package pmc-pwa
 */

namespace PMC\PWA\Components;

use WP_Service_Worker_Registry;

/**
 * Class Service_Worker_Registry.
 */
class Service_Worker_Registry implements WP_Service_Worker_Registry {
	/**
	 * Registered routes to cache.
	 *
	 * @var array
	 */
	protected $_routes = [];

	/**
	 * Register a URL to be cached using "Stale While Revalidate" strategy.
	 *
	 * @param string $url  URL to cache.
	 * @param array  $args Caching arguments.
	 */
	public function register( $url, $args = array() ): void {
		$this->_routes[] = compact( 'url', 'args' );
	}

	/**
	 * Retrieve all registered URLs.
	 *
	 * @return array
	 */
	public function get_all() {
		return $this->_routes;
	}
}
