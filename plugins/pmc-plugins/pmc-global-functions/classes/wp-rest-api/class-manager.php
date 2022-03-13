<?php
/**
 * Manage WP REST API additions.
 *
 * @package pmc-global-functions
 */

namespace PMC\Global_Functions\WP_REST_API;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Manager.
 */
class Manager {
	use Singleton;

	/**
	 * Registered endpoint classes to be instantiated at `rest_api_init`.
	 *
	 * @var array
	 */
	protected $_registered = [];

	/**
	 * Manager constructor.
	 */
	public function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'rest_api_init', [ $this, 'init_endpoints' ] );
	}

	/**
	 * Load registered endpoints.
	 */
	public function init_endpoints(): void {
		if ( ! doing_action( 'rest_api_init' ) ) {
			return;
		}

		foreach ( $this->_registered as $class ) {
			$class::get_instance();
		}
	}

	/**
	 * Register endpoint class.
	 *
	 * @param string $class Class name.
	 * @return bool
	 */
	public function register_endpoint( string $class ): bool {
		if ( did_action( 'rest_api_init' ) ) {
			/**
			 * Endpoint tests often call `rest_api_init` or `rest_get_server()`
			 * and can trigger this condition unintentionally. Without an
			 * `expectedIncorrectUsage` annotation, those tests will fail.
			 */
			// Untestable due to constant.
			// @codeCoverageIgnoreStart
			if ( ! defined( 'IS_UNIT_TESTING' ) || ! IS_UNIT_TESTING ) {
				_doing_it_wrong(
					__METHOD__,
					sprintf(
						/* translators: 1. Name of action before which this must be called. */
						esc_html__(
							'Endpoints cannot be registered after the `%1$s` hook fires.',
							'pmc-global-functions'
						),
						esc_html( 'rest_api_init' )
					),
					1
				);
			}
			// @codeCoverageIgnoreEnd

			return false;
		}

		if ( ! is_subclass_of( $class, Endpoint::class, true ) ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf(
					/* translators: 1. Name of abstract class that endpoint must extend. */
					esc_html__(
						'Endpoint class must extend the `%1$s` class.',
						'pmc-global-functions'
					),
					esc_html( Endpoint::class )
				),
				1
			);

			return false;
		}

		$this->_registered[] = $class;
		return true;
	}
}
