<?php
/**
 * Reviews module.
 *
 * @package pmc-variety
 */

if ( empty( $data['articles'] ) ) {
	return;
}

$reviews = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/homepage-reviews.prototype' );

// Title.
$reviews['o_more_from_heading']['c_heading']['c_heading_text'] = $data['title'];

$r_obj           = new \Variety\Inc\Widgets\Reviews();
$verticals       = $r_obj->verticals;
$review_template = $reviews['reviews_lists'][1];
$item_template   = $reviews['reviews_lists'][1]['o_tease_list_items'][0];

$reviews['reviews_lists']              = [];
$review_template['o_tease_list_items'] = [];

$count = 1;

foreach ( $verticals as $slug ) {
	$term          = get_term_by( 'slug', $slug, 'vertical' );
	$reviews_array = [];
	$review_item   = $review_template;

	$review_item['o_tease_list_id_attr'] = $term->slug;

	if ( 1 === $count ) {
		$review_item['o_tease_list_classes'] .= ' is-active';
	}

	if ( ! empty( $term->name ) && ! empty( $data['articles'][ $term->slug ] ) && 'none' !== $data[ 'carousel_' . $term->slug ] ) {
		foreach ( $data['articles'][ $term->slug ] as $_post ) {
			$item_template['c_link']      = false;
			$item_template['c_timestamp'] = false;

			$populate = new \Variety\Inc\Populate( $_post, $item_template );

			$item = $populate->get();

			// Critic's Pick.
			$badge = \Variety\Inc\Badges\Critics_Pick::get_instance();

			if ( ! $badge->exists_on_post( $_post->ID ) ) {
				$item['c_span'] = null;
			} else {
				$item['c_span'] = $item_template['c_span'];
			}

			$reviews_array[] = $item;
		}
	} elseif ( 'none' === $data[ 'carousel_' . $term->slug ] ) {
		switch ( $term->slug ) {
			case 'tv':
				unset( $reviews['o_nav']['o_nav_list_items'][0] );
				break;
			case 'film':
				unset( $reviews['o_nav']['o_nav_list_items'][1] );
				break;
			case 'music':
				unset( $reviews['o_nav']['o_nav_list_items'][2] );
				break;
			case 'legit':
				unset( $reviews['o_nav']['o_nav_list_items'][3] );
				break;
		}
	}

	$review_item['o_tease_list_items'] = $reviews_array;
	$reviews['reviews_lists'][]        = $review_item;

	$count ++;
}

// More link.
$reviews['o_more_link']['c_link']['c_link_text'] = $data['more_text'];
$reviews['o_more_link']['c_link']['c_link_url']  = $data['more_link'];

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/homepage-reviews.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$reviews,
	true
);
