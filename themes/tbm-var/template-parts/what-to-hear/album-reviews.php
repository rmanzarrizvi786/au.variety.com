<?php
/**
 * Album Reviews Template.
 *
 * @package pmc-variety-2020
 */

// Get data structure.
$wth_reviews = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/wth-reviews.prototype' );

$wth_reviews_data = \Variety\Inc\Carousels::get_carousel_posts(
	'vy-wth-albums',
	4,
);

if ( empty( $wth_reviews_data ) || count( $wth_reviews_data ) < 4 ) {
	return;
}

// Get global curation settings.
$settings = get_option( 'global_curation', [] );
$settings = isset( $settings['tab_variety_what_to_hear'] ) ? $settings['tab_variety_what_to_hear'] : false;

$wth_reviews['o_sub_heading']['c_heading']['c_heading_text'] = isset( $settings['variety_album_header_copy'] ) ? $settings['variety_album_header_copy'] : 'Album Reviews';

$wth_reviews['o_sub_heading']['c_dek']['c_dek_text'] = isset( $settings['variety_album_logline_copy'] ) ? $settings['variety_album_logline_copy'] : 'Who doesn\'t love new music, our critics pick the bets new albums of the moment.';

$wth_review_tease_prototype      = $wth_reviews['o_tease_list']['o_tease_list_items'][0];
$wth_review_tease_prototype_last = $wth_reviews['o_tease_list']['o_tease_list_items'][ count( $wth_reviews['o_tease_list']['o_tease_list_items'] ) - 1 ];
$wth_review_tease_items     = [];

foreach ( $wth_reviews_data as $key => $wth_review ) {
	$wth_review_tease = $wth_review_tease_prototype;
	if ( array_key_last( $wth_reviews_data ) === $key ) {
		$wth_review_tease = $wth_review_tease_prototype_last;
	}

	$wth_review_tease['c_title']['c_title_text'] = $wth_review->post_title;
	$wth_review_tease['c_title']['c_title_url']  = isset( $wth_review->url ) ? $wth_review->url : get_the_permalink( $wth_review );

	$genre = get_post_meta( $wth_review->parent_id, 'variety_hear_details', true );

	$wth_review_tease['c_span']['c_span_text'] = ! empty( $genre['variety_podcast_genre'] ) ? $genre['variety_podcast_genre'] : '';
	$wth_review_tease['c_span']['c_span_url']  = isset( $wth_review->url ) ? $wth_review->url : get_the_permalink( $wth_review );

	if ( ! empty( $genre['variety_watch_url'] ) ) {
		$wth_review_tease['c_link']['c_link_url'] = $genre['variety_watch_url'];
	} else {
		$wth_review_tease['c_link'] = false;
	}


	$wth_review_tease['c_dek']['c_dek_text'] = isset( $wth_review->custom_excerpt ) ? $wth_review->custom_excerpt : wp_strip_all_tags( \PMC\Core\Inc\Helper::get_the_excerpt( $wth_review->ID ) );

	$image_id = isset( $wth_review->image_id ) ? $wth_review->image_id : get_post_thumbnail_id( $wth_review->ID );

	$wth_review_tease['c_lazy_image']['c_lazy_image_link_url']           = isset( $wth_review->url ) ? $wth_review->url : get_the_permalink( $wth_review );
	$wth_review_tease['c_lazy_image']['c_lazy_image_placeholder_url']    = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$wth_review_tease['c_lazy_image']['c_lazy_image_src_url']            = \wp_get_attachment_image_url( $image_id, 'square-medium' );
	$wth_review_tease['c_lazy_image']['c_lazy_image_screen_reader_text'] = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$wth_review_tease['c_lazy_image']['c_lazy_image_alt_attr']           = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$wth_review_tease['c_lazy_image']['c_lazy_image_srcset_attr']        = false;
	$wth_review_tease['c_lazy_image']['c_lazy_image_sizes_attr']         = false;

	array_push( $wth_review_tease_items, $wth_review_tease );
}
$wth_reviews['o_tease_list']['o_tease_list_items'] = $wth_review_tease_items;

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/wth-reviews.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$wth_reviews,
	true
);
