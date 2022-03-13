<?php
/**
 * Base class for WP REST API endpoints.
 *
 * @package pmc-global-functions
 */

namespace PMC\Global_Functions\WP_REST_API;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Endpoint.
 */
abstract class Endpoint {
	use Singleton;

	/**
	 * Endpoint constructor.
	 */
	final protected function __construct() {
		if ( ! doing_action( 'rest_api_init' ) ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf(
					/* translators: 1. Name of action at which this can be called. */
					esc_html__(
						'Class cannot be instantiated before the `%1$s` hook.',
						'pmc-global-functions'
					),
					esc_html( 'rest_api_init' )
				),
				1
			);

			return;
		}

		$this->_register();
	}

	/**
	 * Register endpoint.
	 */
	final protected function _register(): void {
		if ( ! $this->_validate_args() ) {
			return;
		}

		register_rest_route(
			$this->_build_namespace(),
			$this->_get_route(),
			$this->_get_args()
		);
	}

	/**
	 * Build endpoint's namespace.
	 *
	 * @return string
	 */
	final protected function _build_namespace(): string {
		$slug    = $this->_get_namespace_slug();
		$version = $this->_get_namespace_version();

		if ( 0 === strpos( $slug, 'pmc/' ) ) {
			$slug = substr( $slug, 4 );
		}

		return sprintf(
			'pmc/%1$s/v%2$d',
			$slug,
			$version
		);
	}

	/**
	 * Validate endpoint arguments.
	 *
	 * @return bool
	 */
	final protected function _validate_args(): bool {
		$args = $this->_get_args();

		$required_keys = [
			'methods',
			'callback',
			'permission_callback',
		];

		foreach ( $required_keys as $required_key ) {
			if ( ! isset( $args[ $required_key ] ) ) {
				_doing_it_wrong(
					__METHOD__,
					sprintf(
						/* translators: 1. Name of missing key. */
						esc_html__(
							'Missing required key: %1$s',
							'pmc-global-functions'
						),
						esc_html( $required_key )
					),
					1
				);

				return false;
			}
		}

		return true;
	}

	/**
	 * Return endpoint's slug for use within the `pmc` namespace. Slug will be
	 * prefixed with `pmc/` and have version appended automatically.
	 *
	 * Often, this will be the name of the implementing plugin, with the leading
	 * `pmc-` removed; for example, `pmc-carousel` endpoints would set this to
	 * `carousel`.
	 *
	 * @return string
	 */
	abstract protected function _get_namespace_slug(): string;

	/**
	 * Return endpoint's numeric version. Version will be prefixed with `v`
	 * automatically.
	 *
	 * @return int
	 */
	protected function _get_namespace_version(): int {
		return 1;
	}

	/**
	 * Return endpoint's route, including any URL parameters (dynamic parts).
	 *
	 * @return string
	 */
	abstract protected function _get_route(): string;

	/**
	 * Return endpoint's arguments to be passed to `register_rest_route()`.
	 *
	 * @return array
	 */
	abstract protected function _get_args(): array;
}
