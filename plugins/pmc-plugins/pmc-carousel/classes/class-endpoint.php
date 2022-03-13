<?php
/**
 * REST API endpoint for Gutenberg.
 *
 * @package pmc-carousel
 */
namespace PMC\Carousel;

use PMC\Global_Functions\WP_REST_API\Endpoint as Base;
use PMC\Global_Functions\WP_REST_API\Utilities;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class Endpoint.
 */
class Endpoint extends Base {
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
	protected function _get_namespace_slug(): string {
		return 'carousel';
	}

	/**
	 * Return endpoint's route, including any URL parameters (dynamic parts).
	 *
	 * @return string
	 */
	protected function _get_route(): string {
		return 'carousel/(?P<id>[\d]+)';
	}

	/**
	 * Return endpoint's arguments to be passed to `register_rest_route()`.
	 *
	 * @return array
	 */
	protected function _get_args(): array {
		return [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_carousel' ],
			'permission_callback' => Utilities\Permissions::current_user_can(
				'publish_posts'
			),
			'args'                => [
				'id'       => [
					'required'          => true,
					'type'              => 'integer',
					'validate_callback' => [ Utilities\Validation::class, 'is_numeric' ],
				],
				'per_page' => [
					'required'          => false,
					'type'              => 'integer',
					'validate_callback' => [ Utilities\Validation::class, 'is_numeric' ],
					'default'           => 5,
				],
			],
		];
	}

	/**
	 * Get carousel data.
	 */
	public function get_carousel( WP_REST_Request $request ): WP_REST_Response {
		$term_obj = get_term( $request->get_param( 'id' ) );

		$carousel = pmc_render_carousel(
			'pmc_carousel_modules',
			$term_obj->slug,
			$request->get_param( 'per_page' )
		);

		return rest_ensure_response( (array) $carousel );
	}
}
