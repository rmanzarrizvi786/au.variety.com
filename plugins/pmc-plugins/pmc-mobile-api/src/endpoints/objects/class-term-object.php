<?php
/**
 * This file contains the PMC\Mobile_API\Endpoints\Objects\Term_Object class
 *
 * @package PMC_Mobile_API
 */

namespace PMC\Mobile_API\Endpoints\Objects;

use PMC\Mobile_API\Route_Registrar;
use WP_Term;

/**
 * Term model.
 */
class Term_Object {

	/**
	 * Term object.
	 *
	 * @var WP_Term
	 */
	protected $term;

	/**
	 * Term object constructor.
	 *
	 * @param WP_Term $term Section term.
	 */
	public function __construct( WP_Term $term ) {
		$this->term = $term;
	}

	/**
	 * Get term data.
	 *
	 * @return array
	 */
	public function get_term(): array {

		$section_slug = ( 'post_tag' === $this->term->taxonomy )
			? 'tag'
			: $this->term->taxonomy;

		$featured_image_id = get_term_meta(
			$this->term->term_id,
			/**
			 * Filter the meta key used to find the featured image.
			 *
			 * @param string $meta_key Default key.
			 * @param string $taxonomy Taxonomy slug.
			 * @param int    $term_id  Term id.
			 */
			apply_filters( 'pmc_term_featured_image_key', 'featured_image', $this->term->taxonomy, $this->term->term_id ),
			true
		);

		if ( ! empty( $featured_image_id ) ) {
			$featured_image_url = wp_get_attachment_image_url( $featured_image_id, 'full' );
		}

		return [
			'id'       => $this->term->term_id,
			'slug'     => $this->term->slug ?? '',
			'name'     => $this->term->name ?? '',
			'count'    => $this->term->count ?? '',
			'image'    => $featured_image_url ?? '',
			'taxonomy' => $this->term->taxonomy ?? '',
			'link'     => \rest_url( '/' . Route_Registrar::NAMESPACE . sprintf( '/section/%s/%d', $section_slug, $this->term->term_id ) ),
		];
	}
}
