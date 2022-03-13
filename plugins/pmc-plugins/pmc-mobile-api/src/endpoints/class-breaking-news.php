<?php
/**
 * This file contains the PMC\Mobile_API\Endpoints\Breaking_News class.
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Schema_Definitions\Has_Definitions;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Image;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Usable_Definitions;
use stdClass;
use WP_REST_Request;

/**
 * Breaking_News endpoint class.
 */
class Breaking_News extends Public_Endpoint implements Has_Definitions {

	use Usable_Definitions;

	/**
	 * Breaking News Data.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Send the API response for the REST endpoint.
	 *
	 * @param WP_REST_Request $request REST request data.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function rest_response( WP_REST_Request $request ) {
		$this->data = $this->get_widget_data();

		return parent::rest_response( $request );
	}

	/**
	 * Get title.
	 *
	 * @return string
	 */
	protected function get_title(): string {
		$title = $this->data['title'] ?? '';

		if ( ! empty( $title ) ) {
			return html_entity_decode( $title );
		}

		$post_id = $this->data['post_id'] ?? 0;

		if ( empty( $post_id ) ) {
			return '';
		}

		return html_entity_decode( get_the_title( $post_id ) );
	}

	/**
	 * Get widget status.
	 *
	 * @return string
	 */
	protected function get_status(): string {
		return $this->data['active'] ?? 'off';
	}

	/**
	 * Get link.
	 *
	 * @return string
	 */
	protected function get_link(): string {
		$post_id = $this->data['post_id'] ?? 0;

		if ( empty( $post_id ) ) {
			return $this->data['link'] ?? '';
		}

		return get_permalink( $post_id );
	}

	/**
	 * Get custom image.
	 *
	 * @return array|stdClass
	 */
	protected function get_image() {
		$post_id = absint( $this->data['post_id'] ?? 0 );

		if ( empty( $post_id ) ) {
			return Image::get_image( absint( $this->data['image_id'] ?? 0 ) );
		}

		return Image::get_image_from_post( $post_id );
	}

	/**
	 * Get post-id.
	 *
	 * @return int
	 */
	protected function get_post_id(): int {
		return intval( $this->data['post_id'] );
	}

	/**
	 * Get post-type.
	 *
	 * @return string
	 */
	protected function get_post_type(): string {

		$post_id = absint( $this->data['post_id'] ?? 0 );

		if ( empty( $post_id ) ) {
			return 'Not set.';
		}

		return get_post_type( $this->data['post_id'] );
	}

	/**
	 * Get widget data.
	 *
	 * @return array
	 */
	protected function get_widget_data(): array {

		// Check if class is active first.
		if ( ! \class_exists( 'PMC_Breaking_News' ) ) {
			return [];
		}

		return (array) \PMC_Breaking_News::get_instance()->get_data();
	}

	/**
	 * Retrieves the route schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_response_schema(): array {
		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Mobile App Breaking News', 'pmc-mobile-api' ),
			'type'       => 'object',
			'properties' => [
				'status'    => [ 'type' => 'string' ],
				'title'     => [ 'type' => 'string' ],
				'post-id'   => [ 'type' => 'string' ],
				'post-type' => [ 'type' => 'string' ],
				'image'     => $this->add_definition( new Image() ),
				'link'      => [
					'type'   => 'string',
					'format' => 'uri',
				],
			],
		];

		$definitions = $this->get_definitions();
		if ( ! empty( $definitions ) ) {
			$schema['definitions'] = $definitions;
		}

		return $schema;
	}
}
