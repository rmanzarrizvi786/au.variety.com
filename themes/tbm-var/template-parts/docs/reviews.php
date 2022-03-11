<?php
/**
 * Reviews Template.
 *
 * @package pmc-variety-2020
 */

// Get default docs stories row data.
$reviews = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/docs-stories-row.prototype' );

$reviews_data = \Variety\Inc\Carousels::get_carousel_posts(
	'vy-docs-reviews',
	5,
);

if ( empty( $reviews_data ) || count( $reviews_data ) < 5 ) {
	return;
}

$reviews['c_span']                = false;
$reviews['large_story']['c_span'] = false;

$o_card_prototype  = $reviews['stories_row_items'][0];
$stories_row_items = [];

foreach ( $reviews_data as $key => $review ) {
	// If first review, set to large top story.
	if ( array_key_first( $reviews_data ) === $key ) {
		$reviews['large_story']['c_title']['c_title_text'] = $review->post_title;
		$reviews['large_story']['c_title']['c_title_url']  = isset( $review->url ) ? $review->url : get_the_permalink( $review );

		$reviews['large_story']['c_dek']['c_dek_text'] = isset( $review->custom_excerpt ) ? $review->custom_excerpt : wp_strip_all_tags( \PMC\Core\Inc\Helper::get_the_excerpt( $review->ID ) );

		$author_data = PMC\Core\Inc\Author::get_instance()->authors_data( $review->ID );
		$author_url  = get_author_posts_url( $author_data['single_author']['author']->ID, $author_data['single_author']['author']->user_nicename );

		$reviews['large_story']['c_tagline']['c_tagline_text']   = '';
		$reviews['large_story']['c_tagline']['c_tagline_markup'] = "By {$author_data['byline']}";

		$image_id = isset( $review->image_id ) ? $review->image_id : get_post_thumbnail_id( $review->ID );

		$reviews['large_story']['c_lazy_image']['c_lazy_image_link_url']           = isset( $review->url ) ? $review->url : get_the_permalink( $review );
		$reviews['large_story']['c_lazy_image']['c_lazy_image_placeholder_url']    = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$reviews['large_story']['c_lazy_image']['c_lazy_image_src_url']            = \wp_get_attachment_image_url( $image_id, 'landscape-xlarge' );
		$reviews['large_story']['c_lazy_image']['c_lazy_image_screen_reader_text'] = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		$reviews['large_story']['c_lazy_image']['c_lazy_image_alt_attr']           = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		$reviews['large_story']['c_lazy_image']['c_lazy_image_srcset_attr']        = false;
		$reviews['large_story']['c_lazy_image']['c_lazy_image_sizes_attr']         = false;

		continue;
	}

	// Otherwise, set to bottom row card.
	$o_card = $o_card_prototype;

	$o_card['c_title']['c_title_text'] = $review->post_title;
	$o_card['c_title']['c_title_url']  = isset( $review->url ) ? $review->url : get_the_permalink( $review );

	$o_card['c_dek']['c_dek_text'] = isset( $review->custom_excerpt ) ? $review->custom_excerpt : wp_strip_all_tags( \PMC\Core\Inc\Helper::get_the_excerpt( $review->ID ) );

	$image_id = isset( $review->image_id ) ? $review->image_id : get_post_thumbnail_id( $review->ID );

	$o_card['c_lazy_image']['c_lazy_image_link_url']           = isset( $review->url ) ? $review->url : get_the_permalink( $review );
	$o_card['c_lazy_image']['c_lazy_image_placeholder_url']    = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$o_card['c_lazy_image']['c_lazy_image_src_url']            = \wp_get_attachment_image_url( $image_id, 'landscape-xlarge' );
	$o_card['c_lazy_image']['c_lazy_image_screen_reader_text'] = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$o_card['c_lazy_image']['c_lazy_image_alt_attr']           = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$o_card['c_lazy_image']['c_lazy_image_srcset_attr']        = false;
	$o_card['c_lazy_image']['c_lazy_image_sizes_attr']         = false;

	array_push( $stories_row_items, $o_card );
}

$reviews['stories_row_items'] = $stories_row_items;

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/docs-stories-row.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$reviews,
	true
);
