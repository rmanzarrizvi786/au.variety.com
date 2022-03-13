<?php
/**
 * This file contains the Endpoints\Gallery class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Objects\Article_Object;
use PMC\Mobile_API\Endpoints\Objects\Gallery_Object;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Entitlements;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Has_Definitions;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Image;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Usable_Definitions;
use WP_Error;
use WP_Post;
use WP_REST_Request;

/**
 * Gallery endpoint class.
 */
class Gallery extends Public_Endpoint implements Has_Definitions, Has_Pagination {

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
	 * @var Gallery_Object
	 */
	protected $gallery_object;

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
		$this->gallery_object = new Gallery_Object( $gallery );

		return parent::rest_response( $request );
	}

	/**
	 * Get post headline.
	 *
	 * @return string
	 */
	protected function get_headline() {
		return html_entity_decode( \get_the_title( $this->article_object->post ) );
	}

	/**
	 * Get post published date.
	 *
	 * @return string
	 */
	protected function get_published_at(): string {
		return $this->article_object->published_at();
	}

	/**
	 * Get entitlements.
	 *
	 * @return array
	 */
	protected function get_entitlements(): array {
		return $this->article_object->entitlements();
	}

	/**
	 * Get gallery category.
	 *
	 * @return array
	 */
	protected function get_category(): array {
		return $this->article_object->category();
	}

	/**
	 * Get gallery subcategory.
	 *
	 * @return array
	 */
	protected function get_subcategory(): array {
		return $this->article_object->subcategory();
	}

	/**
	 * Get gallery byline.
	 *
	 * @return string
	 */
	protected function get_byline(): string {
		return $this->article_object->byline();
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
				'per_page' => $request->get_param( 'per_page' ),
				'page'     => $request->get_param( 'page' ),
			],
			$request->get_default_params()
		);

		$this->total_items = $this->gallery_object->get_items_count();

		return $this->gallery_object->items(
			$params['per_page'],
			$params['page']
		);
	}

	/**
	 * Retrieves the route schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_response_schema(): array {
		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Mobile App Gallery', 'pmc-mobile-api' ),
			'type'       => 'object',
			'properties' => [
				'headline'     => [
					'type' => 'string',
				],
				'published-at' => [
					'type'        => 'string',
					'format'      => 'date-time',
					'description' => __( 'The date and time the item was last published, in ISO 8601.', 'pmc-mobile-api' ),
				],
				'entitlements' => $this->add_definition( new Entitlements() ),
				'category'     => [
					'type' => 'array',
				],
				'subcategory'  => [
					'type' => 'array',
				],
				'byline'       => [
					'type' => 'string',
				],
				'items'        => [
					'type'  => 'array',
					'items' => $this->add_definition( new Image() ),
				],
			],
		];

		$definitions = $this->get_definitions();
		if ( ! empty( $definitions ) ) {
			$schema['definitions'] = $definitions;
		}

		return $schema;
	}

	/**
	 * Get the request params for the endpoint.
	 *
	 * @return array
	 */
	public function get_request_params(): array {
		return [
			'id' => [
				'description' => __( 'Gallery ID.', 'pmc-mobile-api' ),
				'type'        => 'integer',
				'required'    => true,
			],
		];
	}
}
