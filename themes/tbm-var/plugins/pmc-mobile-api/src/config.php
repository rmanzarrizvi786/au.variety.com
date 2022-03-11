<?php
/**
 * This file contains assorted configurations of the core PMC_Mobile_API plugin.
 *
 * @package VY_Mobile_API
 */

namespace PMC\VY\Mobile_API;

use PMC\Core\Inc\Meta\Byline;

/**
 * Adding VY image crop sizes.
 *
 * @param array $crop_sizes An array of crop sizes.
 *
 * @return array
 */
function image_crop_sizes( array $crop_sizes ): array {
	return array_merge(
		$crop_sizes,
		[
			'full',
			'landscape-large',
			'landscape-large',
			'landscape-medium',
			'square-small',
		]
	);
}
add_filter( 'pmc_mobile_api_image_crop_sizes', __NAMESPACE__ . '\image_crop_sizes' );

/**
 * Adding VY taxonomies to post card.
 *
 * @param array $taxonomies An array of taxonomies.
 *
 * @return array
 */
function add_vy_taxonomies( array $taxonomies ): array {

	$vy_taxonomies = [ 'vertical', 'variety_vip_category' ];

	return array_merge( $taxonomies, $vy_taxonomies );
}

add_filter( 'article_post_card_taxonomies', __NAMESPACE__ . '\add_vy_taxonomies' );

/**
 * Adding VY Personalization taxonomy.
 *
 * @return string
 */
function personalization_taxonomy(): string {
	return 'vertical';
}

add_filter( 'mobile_api_personalization_taxonomy', __NAMESPACE__ . '\personalization_taxonomy' );

/**
 * Adding VY Personalization taxonomy.
 *
 * @return string
 */
function personalization_query(): string {
	return 'verticals';
}

add_filter( 'mobile_api_personalization_query', __NAMESPACE__ . '\personalization_query' );

/**
 * Returns byline.
 *
 * @param string $byline  Byline.
 * @param int    $post_id Post id.
 *
 * @return string
 */
function custom_byline( $byline, $post_id ): string {
	$authors_byline = Byline::get_instance();
	$authors        = $authors_byline->get_authors( $post_id );

	if ( empty( $authors ) ) {
		return false;
	}

	return get_byline_from_authors( $authors );
}

/**
 * Get post byline from authors.
 *
 * @param array $authors Authors.
 *
 * @return string
 */
function get_byline_from_authors( array $authors ): string {
	$byline = '';
	foreach ( $authors as $index => $author ) {
		$byline .= $author->display_name;

		if ( 2 === count( $authors ) && 0 === $index ) {
			$byline .= ' and ';
		} elseif ( count( $authors ) > 2 && ( count( $authors ) - 2 ) === $index ) {
			$byline .= ', and ';
		} elseif ( count( $authors ) > 2 && $index < count( $authors ) - 2 ) {
			$byline .= ', ';
		}
	}

	return $byline;
}

add_filter( 'pmc_mobile_api_byline', __NAMESPACE__ . '\custom_byline', 10, 2 );
