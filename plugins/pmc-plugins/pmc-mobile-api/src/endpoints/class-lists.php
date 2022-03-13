<?php
/**
 * This file contains the Endpoints\Lists class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Objects\Article_Object;
use PMC\Mobile_API\Endpoints\Objects\List_Object;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Image;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Usable_Definitions;
use WP_Error;
use WP_Post;
use WP_REST_Request;

/**
 * Lists endpoint class.
 */
class Lists extends Article {

	use Usable_Definitions;

	/**
	 * Article model.
	 *
	 * @var Article_Object
	 */
	protected $article_object;

	/**
	 * Gallery model.
	 *
	 * @var List_Object
	 */
	protected $list_object;

	/**
	 * Send the API response for the REST endpoint.
	 *
	 * @param WP_REST_Request $request REST request data.
	 * @return \WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function rest_response( WP_REST_Request $request ) {

		// Get gallery.
		$gallery = \get_post( $request['id'] );

		if ( ! $gallery instanceof WP_Post ) {
			return new WP_Error(
				'rest_gallery_invalid_id',
				__( 'Invalid gallery ID.', 'pmc-mobile-api' ),
				[
					'status' => 404,
				]
			);
		}

		$this->article_object = new Article_Object( $gallery );
		$this->list_object    = new List_Object( $gallery );

		return parent::rest_response( $request );
	}

	/**
	 * Get gallery items.
	 *
	 * @param WP_REST_Request $request REST request data.
	 * @return array
	 */
	protected function get_items( WP_REST_Request $request ): array {

		$params = wp_parse_args(
			[
				'page'     => $request->get_param( 'page' ),
				'per_page' => $request->get_param( 'per_page' ),
			],
			$request->get_default_params()
		);

		return $this->list_object->items( $params );
	}

	/**
	 * Get list template.
	 *
	 * @return string
	 */
	public function get_list_template(): string {
		return get_post_meta( $this->article_object->post->ID, 'pmc_list_template', true );
	}

	/**
	 * Get list numbering.
	 *
	 * @return string
	 */
	public function get_list_numbering(): string {
		return get_post_meta( $this->article_object->post->ID, 'pmc_list_numbering', true );
	}
	/**
	 * Get list numbering.
	 *
	 * @return string
	 */
	public function get_list_item_count(): string {
		$list_items_count = '';

		$list_relation = get_term_by( 'slug', $this->article_object->post->ID, 'pmc_list_relation' );

		if ( ! empty( $list_relation->count ) ) {
			$list_items_count = $list_relation->count;
		}

		return $list_items_count;
	}

	/**
	 * Retrieves the route schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_response_schema(): array {
		$schema = parent::get_response_schema();

		$schema['properties']['items'] = [
			'type'  => 'array',
			'items' => $this->add_definition( new Image() ),
		];

		$schema['properties']['list-item-count'] = [ 'type' => 'string' ];
		$schema['properties']['list-template']   = [ 'type' => 'string' ];
		$schema['properties']['list-numbering']  = [ 'type' => 'string' ];

		return $schema;
	}

	/**
	 * Get the request params for the endpoint.
	 *
	 * @return array
	 */
	public function get_request_params(): array {
		return [
			'id'       => [
				'description' => __( 'List ID.', 'pmc-mobile-api' ),
				'type'        => 'integer',
				'required'    => true,
			],
			'page'     => [
				'description' => __( 'List page number.', 'pmc-mobile-api' ),
				'type'        => 'integer',
				'required'    => false,
			],
			'per_page' => [
				'description' => __( 'List items per page.', 'pmc-mobile-api' ),
				'type'        => 'integer',
				'required'    => false,
			],
		];
	}
}
