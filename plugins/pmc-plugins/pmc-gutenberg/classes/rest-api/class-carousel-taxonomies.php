<?php
/**
 * Endpoint for `pmc-carousel` supported taxonomies made available to Carousel
 * block.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg\REST_API;

use PMC_Carousel;
use PMC\Global_Functions\WP_REST_API\Endpoint;
use PMC\Global_Functions\WP_REST_API\Utilities;
use PMC\Top_Videos_V2\PMC_Top_Videos;
use WP_REST_Response;

/**
 * Class Carousel_Taxonomies.
 */
class Carousel_Taxonomies extends Endpoint {
	use Namespace_Slug;

	/**
	 * Return endpoint's route, including any URL parameters (dynamic parts).
	 *
	 * @return string
	 */
	protected function _get_route(): string {
		return 'carousel-taxonomies';
	}

	/**
	 * Return endpoint's arguments to be passed to `register_rest_route()`.
	 *
	 * @return array
	 */
	protected function _get_args(): array {
		return [
			'methods'             => 'GET',
			'callback'            => [ $this, 'callback' ],
			'permission_callback' => Utilities\Permissions::current_user_can(
				'edit_posts'
			),
		];
	}

	/**
	 * Build list of taxonomies that can be used as curation sources for the
	 * Carousel block.
	 *
	 * @return WP_REST_Response
	 */
	public function callback(): WP_REST_Response {
		$options = [
			[
				'value' => null,
				'label' => __( 'Select curation', 'pmc-gutenberg' ),
			],
		];

		if ( class_exists( PMC_Carousel::class, false ) ) {
			$carousel_taxonomies = PMC_Carousel::get_instance()
				->get_available_taxonomies();

			foreach ( $carousel_taxonomies as $taxonomy ) {
				$object = get_taxonomy( $taxonomy );

				if ( false === $object ) {
					continue;
				}

				$options[] = [
					'value' => $taxonomy,
					'label' => $object->labels->singular_name,
				];
			}
		}

		return rest_ensure_response( $options );
	}
}
