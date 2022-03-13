<?php
/**
 * Apply Gutenberg-specific modifications to WP's REST API.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg\REST_API;

use PMC;
use PMC\Digital_Daily;
use PMC\Global_Functions\Traits\Singleton;
use WP_Post;
use WP_REST_Response;
use WP_REST_Request;

/**
 * Class Modifications.
 */
class Modifications {
	use Singleton;

	/**
	 * Modifications constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		/**
		 * Post types are registered at `init`, using `wp_loaded` ensures all
		 * exist before calling `get_post_types()`.
		 */
		add_action( 'wp_loaded', [ $this, 'hook_post_responses' ] );
	}

	/**
	 * Hook into all post types' REST responses.
	 */
	public function hook_post_responses(): void {
		$types = get_post_types(
			[
				'show_in_rest' => true,
			]
		);

		foreach ( $types as $type ) {
			add_filter(
				'rest_prepare_' . $type,
				[ $this, 'add_truncated_content' ],
				10,
				3
			);
		}

		if ( class_exists( Digital_Daily\Table_Of_Contents::class, false ) ) {
			add_filter(
				'rest_prepare_' . Digital_Daily\POST_TYPE,
				[ $this, 'add_dd_toc_to_rest_response' ],
				10,
				3
			);
		}
	}

	/**
	 * Add truncated content to REST responses, with HTML tags preserved.
	 *
	 * @param WP_REST_Response $response REST response object.
	 * @param WP_Post          $post     Post object.
	 * @param WP_REST_Request  $request  REST request object.
	 * @return WP_REST_Response
	 */
	public function add_truncated_content(
		WP_REST_Response $response,
		WP_Post $post,
		WP_REST_Request $request
	): WP_REST_Response {
		if ( 'edit' !== $request->get_param( 'context' ) ) {
			return $response;
		}

		$response_data = $response->get_data();

		if ( ! isset( $response_data['content']['rendered'] ) ) {
			return $response;
		}

		$response_data['content']['truncated'] = PMC::truncate(
			$response_data['content']['rendered'],
			300
		);

		$response->set_data( $response_data );

		return $response;
	}

	/**
	 * Add Digital Daily's Table of Contents data to REST response for use in
	 * the Fullscreen Cover block.
	 *
	 * @param WP_REST_Response $response REST response object.
	 * @param WP_Post          $post     Post object.
	 * @param WP_REST_Request  $request  REST request object.
	 * @return WP_REST_Response
	 */
	public function add_dd_toc_to_rest_response(
		WP_REST_Response $response,
		WP_Post $post,
		WP_REST_Request $request
	): WP_REST_Response {
		if ( 'edit' !== $request->get_param( 'context' ) ) {
			return $response;
		}

		$response_data = $response->get_data();

		if ( ! isset( $response_data['blockData'] ) ) {
			$response_data['blockData'] = [];
		}

		$block_name                                = 'pmc/fullscreen-cover';
		$response_data['blockData'][ $block_name ] = [
			[
				'label' => null,
				'value' => null,
			],
		];

		// Disallowed only because they have little use in Core (https://wp.me/p2AvED-gCU).
		// phpcs:disable WordPress.PHP.DisallowShortTernary
		$data = get_post_meta(
			$post->ID,
			Digital_Daily\Table_Of_Contents::META_KEY,
			true
		) ?: [];

		foreach ( (array) $data as $datum ) {
			$response_data['blockData'][ $block_name ][] = [
				'label' => $datum['title'] ?? get_the_title( $datum['ID'] ),
				'value' => (int) $datum['ID'],
			];
		}

		$response->set_data( $response_data );

		return $response;
	}
}
