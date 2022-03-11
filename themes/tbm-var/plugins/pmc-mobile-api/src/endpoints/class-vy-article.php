<?php
/**
 * This file contains the PMC\VY\Mobile_API\Endpoints\VY_Article class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\VY\Mobile_API\Endpoints;

use PMC\Mobile_API\Endpoints\Article;
use PMC\Mobile_API\Endpoints\Objects\Ad_Object;
use PMC\Mobile_API\Endpoints\Objects\Video_Object;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Ad;
use PMC\Mobile_API\Endpoints\Schema_Definitions\Term;
use PMC\Mobile_API\Route_Registrar;
use stdClass;

/**
 * VY Article endpoint class.
 */
class VY_Article extends Article {

	/**
	 * Get post category term.
	 *
	 * @return array|stdClass
	 */
	protected function get_category() {
		$is_video = $this->is_video_post_type();

		// Get category based on post type.
		if ( $is_video ) {
			$category = $this->article_object->get_term_from_post( 'vcategory' );
		} else {
			$category = $this->article_object->category();
		}

		// Empty object if no category.
		if ( empty( $category ) ) {
			return new stdClass();
		}

		// Hacky way to change the link of the playlist taxonomy.
		if ( $is_video && ! empty( $category['slug'] ) ) {
			$category['link'] = \rest_url( '/' . Route_Registrar::NAMESPACE . sprintf( '/video/latest/%s', $category['slug'] ) );
		}

		return $category;
	}

	/**
	 * Get post vertical term.
	 *
	 * @return array|stdClass
	 */
	protected function get_vertical(): array {
		return $this->article_object->category( 'vertical' );
	}

	/**
	 * Get post vip category.
	 *
	 * @return array|stdClass
	 */
	protected function get_vip_category(): array {
		return $this->article_object->category( 'variety_vip_category' );
	}

	/**
	 * Get post vip tag.
	 *
	 * @return array|stdClass
	 */
	protected function get_vip_tag(): array {
		return $this->article_object->category( 'variety_vip_tag' );
	}

	/**
	 * Get post byline.
	 *
	 * @return string
	 */
	public function get_byline(): string {

		// If not video post type.
		if ( false === $this->is_video_post_type() ) {
			return $this->article_object->byline();
		}

		global $coauthors_plus;

		if ( ! is_a( $coauthors_plus, 'coauthors_plus' ) ) {
			return '';
		}

		// Get byline/author.
		$author = $this->article_object->get_term_from_post( 'author' );
		if ( empty( $author['slug'] ) ) {
			return '';
		}

		$byline = $coauthors_plus->get_coauthor_by( 'user_login', $author['slug'] );

		if ( empty( $byline ) ) {
			return '';
		}

		return $byline->display_name ?? '';
	}

	/**
	 * Get post, featured, video based on post type (post or video).
	 *
	 * @return string
	 */
	protected function get_featured_video(): string {

		$video_url = $this->article_object->featured_video();

		if ( empty( $video_url ) ) {
			// Get meta key based on post type.
			$meta_key = $this->is_video_post_type() ? 'variety_top_video_source' : 'video_url';

			$video_url = (string) trim( \get_post_meta( $this->article_object->post->ID, $meta_key, true ) );
		}

		if ( empty( $video_url ) ) {
			return '';
		}

		return $this->article_object->get_video_output( $video_url );
	}

	/**
	 * Get, video, post related content/articles.
	 *
	 * @return array
	 */
	protected function get_related_content(): array {

		// If not video post type, return regular related content.
		if ( false === $this->is_video_post_type() ) {
			return $this->article_object->related_content( [ 'max_articles' => 3 ] );
		}

		// Check if PMC function exists.
		if ( ! function_exists( 'pmc_related_articles' ) ) {
			return [];
		}

		// Get related video posts.
		$related_posts = \pmc_related_articles(
			$this->article_object->post->ID,
			[
				'post_type' => 'variety_top_video',
			]
		);

		// Check if we have any items.
		if ( empty( $related_posts ) ) {
			return [];
		}

		// An array of video objects.
		return array_map(
			function( $post_id ) {
				return ( new Video_Object( \get_post( $post_id ) ) )->get_video();
			},
			array_values( \wp_list_pluck( $related_posts, 'post_id' ) )
		);
	}

	/**
	 * Get the ad.
	 *
	 * @return array
	 */
	public function get_ad_size(): array {

		// Setting the ad size.
		$data = [
			'height' => 320,
			'width'  => 50,
		];

		return ( new Ad_Object( $data ) )->get_ad();
	}

	/**
	 * Check if it is a video post type.
	 *
	 * @return bool
	 */
	protected function is_video_post_type(): bool {
		return ( 'variety_top_video' === $this->article_object->post->post_type || 'variety_vip_video' === $this->article_object->post->post_type );
	}

	/**
	 * Get review_meta.
	 *
	 * @return array
	 */
	public function get_review_meta(): array {
		$post_id = $this->article_object->post->ID;

		$review_meta = [];

		$review_meta_values = [
			'director'       => 'pmc-director',
			'canonical'      => 'pmc-review-canonical-link',
			'origin'         => 'variety-review-origin',
			'mpaa-rating'    => 'variety-review-mpaa-rating',
			'running-time'   => 'variety-review-running-time',
			'production'     => 'variety-primary-credit',
			'cast'           => 'variety-primary-cast',
			'secondary-cast' => 'variety-secondary-cast',
			'crew'           => 'variety-secondary-credit',
			'music-by'       => 'variety-music-by',
		];

		foreach ( $review_meta_values as $review_meta_key => $review_meta_value ) {
			$review_meta[ $review_meta_key ] = get_post_meta( $post_id, $review_meta_value, true );
		}

		return $review_meta;
	}

	/**
	 * Get is_review.
	 *
	 * @return bool
	 */
	public function get_is_review() {
		return variety_is_review( $this->article_object->post );
	}

	/**
	 * Get is_featured.
	 *
	 * @return bool
	 */
	public function get_is_featured() {
		return has_term( 'variety-featured-article', '_post-options', $this->article_object->post );
	}

	/**
	 * Updating schema to add the ad size obejct.
	 *
	 * @return array
	 */
	public function get_response_schema(): array {
		$schema = parent::get_response_schema();

		if ( $this->is_video_post_type() ) {
			$schema['properties']['ad-size'] = $this->add_definition( new Ad() );
		}

		$schema['properties']['vertical']     = $this->add_definition( new Term() );
		$schema['properties']['vip_category'] = $this->add_definition( new Term() );
		$schema['properties']['vip_tag']      = $this->add_definition( new Term() );
		$schema['properties']['is_review']    = [ 'type' => 'bool' ];
		$schema['properties']['review_meta']  = [ 'type' => 'object' ];
		$schema['properties']['is_featured']  = [ 'type' => 'bool' ];

		return $schema;
	}
}
