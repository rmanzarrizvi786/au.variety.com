<?php
/**
 * This file contains the Route_Register class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API;

use PMC\Mobile_API\Endpoints\Endpoint;

/**
 * Route Registrar class.
 */
class Route_Registrar {

	/**
	 * Namespace for the REST endpoints.
	 */
	const NAMESPACE = 'mobile-apps/v1';

	/**
	 * Array of Endpoints.
	 *
	 * @var array
	 */
	protected $endpoints;

	/**
	 * Register an endpoint.
	 *
	 * @param string   $path     Path at which to register the endpoint.
	 * @param Endpoint $endpoint Endpoint to register.
	 */
	public function add_endpoint( string $path, Endpoint $endpoint ) {
		$this->endpoints[ $path ] = $endpoint;
	}

	/**
	 * Deregister an endpoint.
	 *
	 * @param string $path Path at which the endpoint is registered.
	 */
	public function remove_endpoint( string $path ) {
		if ( isset( $this->endpoints[ $path ] ) ) {
			unset( $this->endpoints[ $path ] );
		}
	}

	/**
	 * Register REST routes with WordPress.
	 */
	public function register_routes() {
		foreach ( $this->endpoints as $path => $endpoint ) {
			/**
			 * Filters the route arguments passed to register_rest_route().
			 *
			 * @param array    $route_args REST Route arguments. {@see register_rest_route()}.
			 * @param Endpoint $endpoint   Endpoint providing the arguments.
			 * @param string   $path       Route path.
			 */
			$route_args = apply_filters(
				'pmc_mobile_api_register_route_arguments',
				$endpoint->get_route_args(),
				$endpoint,
				$path
			);

			register_rest_route( self::NAMESPACE, $path, $route_args );
		}
	}
}
