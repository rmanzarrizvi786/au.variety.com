<?php
/**
 * Base class for REST API endpoints for Gutenberg.
 *
 * @package pmc-exacttarget
 */
namespace PMC\Exacttarget\WP_REST_API;

use PMC\Global_Functions\Traits\Singleton;
use WP_REST_Request;

/**
 * Class Endpoint.
 */
abstract class Endpoint {
	use Singleton;

	/**
	 * Namespace for our endpoints.
	 */
	public const NAMESPACE = 'pmc/exacttarget/v1';

	/**
	 * Regex to capture an ID in an endpoint route.
	 */
	protected const ENDPOINT_ID_REGEX = '/(?P<id>[\d]+)';

	/**
	 * WP_REST_API constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'rest_api_init', [ $this, 'add_endpoints' ] );
	}

	/**
	 * Register endpoints.
	 */
	abstract public function add_endpoints(): void;

	/**
	 * Validate input arguments.
	 *
	 * @param mixed $param Value to validate.
	 * @return bool
	 */
	public function validate_numeric( $param ): bool {
		return is_numeric( $param );
	}

	/**
	 * Restrict endpoint read access.
	 *
	 * @return bool
	 */
	public function check_endpoint_read_permissions(): bool {
		return current_user_can( 'publish_posts' );
	}

	/**
	 * Restrict endpoint write access.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool
	 */
	abstract public function check_endpoint_update_permissions(
		WP_REST_Request $request
	): bool;
}
