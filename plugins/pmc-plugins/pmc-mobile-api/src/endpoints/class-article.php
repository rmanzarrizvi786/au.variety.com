<?php
/**
 * This file contains the Endpoints\Article class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Objects\Article_Object;
use PMC\Mobile_API\Endpoints\Objects\Gallery_Object;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Entitlements;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Gallery_Card;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Has_Definitions;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Image;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Related_Articles;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Term;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Usable_Definitions;
use stdClass;
use WP_REST_Request;

/**
 * Article endpoint class.
 */
class Article extends Public_Endpoint implements Has_Definitions {

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
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function rest_response( WP_REST_Request $request ) {

		$post = \get_post( $request['id'] );

		if ( ! $post instanceof \WP_Post ) {
			return new \WP_Error(
				'rest_article_invalid_id',
				__( 'Invalid article ID.', 'pmc-mobile-api' ),
				[
					'status' => 404,
				]
			);
		}

		$this->article_object = new Article_Object( $post );
		$this->gallery_object = Gallery_Object::get_gallery_from_post( $post->ID );

		return parent::rest_response( $request );
	}

	/**
	 * Get post category term.
	 *
	 * @return array|stdClass
	 */
	protected function get_category() {
		$category = $this->article_object->category();

		// Empty object if no category.
		if ( empty( $category ) ) {
			return new stdClass();
		}

		return $category;
	}

	/**
	 * Get post category term.
	 *
	 * @return array|stdClass
	 */
	protected function get_subcategory() {
		$category = $this->article_object->subcategory();

		// Empty object if no category.
		if ( empty( $category ) ) {
			return new stdClass();
		}

		return $category;
	}

	/**
	 * Get post id.
	 *
	 * @return string
	 */
	protected function get_post_id() {
		return $this->article_object->post_id();
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
	 * Get post post_type.
	 *
	 * @return string
	 */
	protected function get_post_type() {
		return $this->article_object->post_type();
	}

	/**
	 * Get post dek/summary/excerpt/tagline.
	 *
	 * @return string
	 */
	protected function get_tagline() {
		return $this->article_object->tagline();
	}

	/**
	 * Get post byline.
	 *
	 * @return string
	 */
	protected function get_byline() {
		return $this->article_object->byline();
	}

	/**
	 * Get post excerpt.
	 *
	 * @return string
	 */
	protected function get_excerpt() {
		return $this->article_object->excerpt();
	}

	/**
	 * Get post featured image.
	 *
	 * @return array|stdClass
	 */
	protected function get_featured_image() {
		return $this->article_object->featured_image();
	}

	/**
	 * Get post featured gallery.
	 *
	 * @return array|stdClass
	 */
	protected function get_featured_gallery() {
		// Empty object if no featured gallery.
		if ( empty( $this->gallery_object ) || ! \method_exists( $this->gallery_object, 'get_gallery_card' ) ) {
			return new stdClass();
		}

		return $this->gallery_object->get_gallery_card();
	}

	/**
	 * Get post featured video.
	 *
	 * @return string
	 */
	protected function get_featured_video() {
		return $this->article_object->featured_video();
	}

	/**
	 * Get entitlements.
	 *
	 * @return array
	 */
	protected function get_entitlements() {
		return $this->article_object->entitlements();
	}

	/**
	 * Get permalink.
	 *
	 * @return string
	 */
	protected function get_permalink() {
		return $this->article_object->permalink();
	}

	/**
	 * Get post published date.
	 *
	 * @return string
	 */
	protected function get_published_at() {
		return $this->article_object->published_at();
	}

	/**
	 * Get post updated date.
	 *
	 * @return string
	 */
	protected function get_updated_at() {
		return $this->article_object->updated_at();
	}

	/**
	 * Get post body content.
	 *
	 * @return string
	 */
	protected function get_body() {
		return $this->article_object->body();
	}

	/**
	 * Get post body preview.
	 *
	 * @return string
	 */
	protected function get_body_preview() {
		return $this->article_object->body_preview();
	}

	/**
	 * Get post tag terms.
	 *
	 * @return array
	 */
	protected function get_tags() {
		return $this->article_object->tags();
	}

	/**
	 * Get post related content/articles.
	 *
	 * @return array
	 */
	protected function get_related_content() {
		return $this->article_object->related_content( [ 'max_articles' => 3 ] );
	}

	/**
	 * Get "Read Next" Post_Card.
	 *
	 * @return array
	 */
	protected function get_read_next() {
		return $this->article_object->read_next();
	}

	/**
	 * Get the request params for the endpoint.
	 *
	 * @return array
	 */
	public function get_request_params(): array {
		return [
			'id' => [
				'description' => __( 'Article ID.', 'pmc-mobile-api' ),
				'type'        => 'integer',
				'required'    => true,
			],
		];
	}

	/**
	 * Retrieves the route schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_response_schema(): array {
		$term_def = $this->add_definition( new Term() );

		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Mobile App Article', 'pmc-mobile-api' ),
			'type'       => 'object',
			'properties' => [
				'category'         => $term_def,
				'subcategory'      => $term_def,
				'headline'         => [
					'type' => 'string',
				],
				'tagline'          => [
					'type' => 'string',
				],
				'post-type'        => [
					'type' => 'string',
				],
				'byline'           => [
					'type' => 'string',
				],
				'excerpt'          => [
					'type' => 'string',
				],
				'featured-image'   => $this->add_definition( new Image() ),
				'featured-gallery' => $this->add_definition( new Gallery_Card() ),
				'featured-video'   => [
					'type' => 'string',
				],
				'entitlements'     => $this->add_definition( new Entitlements() ),
				'permalink'        => 'string',
				'post-id'          => 'string',
				'published-at'     => [
					'type'        => 'string',
					'format'      => 'date-time',
					'description' => __( 'The date and time the item was last published, in ISO 8601.', 'pmc-mobile-api' ),
				],
				'updated-at'       => [
					'type'        => 'string',
					'format'      => 'date-time',
					'description' => __( 'The date and time the item was last updated, in ISO 8601.', 'pmc-mobile-api' ),
				],
				'body'             => [
					'type' => 'string',
				],
				'body-preview'     => [
					'type' => 'string',
				],
				'tags'             => [
					'type'  => 'array',
					'items' => $term_def,
				],
				'related-content'  => $this->add_definition( new Related_Articles() ),
			],
		];

		$definitions = $this->get_definitions();
		if ( ! empty( $definitions ) ) {
			$schema['definitions'] = $definitions;
		}

		return $schema;
	}
}
