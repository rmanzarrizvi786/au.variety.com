<?php
/**
 * Trending Podcasts Template.
 *
 * @package pmc-variety-2020
 */

// Get data structure.
$wth_podcasts = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/stories-river.prototype' );

$wth_podcasts_data = \Variety\Inc\Carousels::get_carousel_posts(
	'vy-wth-trending-pods',
	5,
);

if ( empty( $wth_podcasts_data ) || count( $wth_podcasts_data ) < 5 ) {
	return;
}

$wth_podcasts['o_sub_heading']['c_heading']['c_heading_text'] = 'Trending';

$wth_podcasts['o_sub_heading']['c_dek']['c_dek_text'] = 'From daily news to true crime, find the next can\'t-miss podcasts that everyone is talking about and are sure to get you hooked.';

$wth_tease_prototype      = $wth_podcasts['o_tease_news_list']['o_tease_list_items'][0];
$wth_tease_prototype_last = $wth_podcasts['o_tease_news_list']['o_tease_list_items'][ count( $wth_podcasts['o_tease_news_list']['o_tease_list_items'] ) - 1 ];
$wth_tease_items          = [];

foreach ( $wth_podcasts_data as $key => $wth_podcast ) {
	$wth_tease = $wth_tease_prototype;
	if ( array_key_last( $wth_podcasts_data ) === $key ) {
		$wth_tease = $wth_tease_prototype_last;
	}

	$wth_tease['c_title']['c_title_text'] = $wth_podcast->post_title;
	$wth_tease['c_title']['c_title_url']  = isset( $wth_podcast->url ) ? $wth_podcast->url : get_the_permalink( $wth_podcast );

	$genre = get_post_meta( $wth_podcast->parent_id, 'variety_hear_details', true );

	$wth_tease['o_taxonomy_item']['c_span']['c_span_text'] = isset( $genre['variety_podcast_genre'] ) ? $genre['variety_podcast_genre'] : '';

	$wth_tease['c_dek']['c_dek_text'] = isset( $wth_podcast->custom_excerpt ) ? $wth_podcast->custom_excerpt : wp_strip_all_tags( \PMC\Core\Inc\Helper::get_the_excerpt( $wth_podcast->ID ) );

	$image_id = isset( $wth_podcast->image_id ) ? $wth_podcast->image_id : get_post_thumbnail_id( $wth_podcast->ID );

	$wth_tease['c_lazy_image']['c_lazy_image_link_url']           = isset( $wth_podcast->url ) ? $wth_podcast->url : get_the_permalink( $wth_podcast );
	$wth_tease['c_lazy_image']['c_lazy_image_placeholder_url']    = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$wth_tease['c_lazy_image']['c_lazy_image_src_url']            = \wp_get_attachment_image_url( $image_id, 'square-medium' );
	$wth_tease['c_lazy_image']['c_lazy_image_screen_reader_text'] = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$wth_tease['c_lazy_image']['c_lazy_image_alt_attr']           = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$wth_tease['c_lazy_image']['c_lazy_image_srcset_attr']        = false;
	$wth_tease['c_lazy_image']['c_lazy_image_sizes_attr']         = false;

	array_push( $wth_tease_items, $wth_tease );
}

$wth_podcasts['o_tease_news_list']['o_tease_list_items'] = $wth_tease_items;


\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/stories-river.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$wth_podcasts,
	true
);
