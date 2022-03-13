<?php

use PMC\Core\Inc\Helper;

$profile_landing_river = PMC\Larva\Json::get_instance()->get_json_data( 'modules/profile-landing-river.prototype' );

$terms = get_the_terms( get_the_ID(), 'post_tag' );

if ( is_wp_error( $terms ) || false === $terms ) {
	return;
}

$term_posts = [];

foreach ( $terms as $term_item ) {

	$term_id    = $term_item->term_id ?? '';
	$term_posts = array_merge( $term_posts, Helper::get_term_posts_cache( $term_id, 'post_tag', 3 ) );

	if ( count( $term_posts ) >= 3 ) {
		break;
	}
}

if ( empty( $term_posts ) ) {
	return;
}

$profile_landing_river['profile_landing_river_id_attr'] = 'latest-news';

$profile_landing_river['c_heading']['c_heading_text'] = esc_html__( 'Latest News', 'pmc-profiles' );

$river_item_prototype = $profile_landing_river['profile_landing_river_stories'][0];

$river_item_array = [];

foreach ( array_slice( $term_posts, 0, 3 ) as $term_post ) {
	$river_item = $river_item_prototype;

	$river_item['c_title']['c_title_text'] = $term_post->post_title;
	$river_item['c_title']['c_title_url']  = get_permalink( $term_post->ID );

	$author_data = PMC\Core\Inc\Author::get_instance()->authors_data( $term_post->ID );

	$river_item['c_tagline_author']['c_tagline_markup'] = $author_data['byline'] ?? '';

	$river_item['c_timestamp']['c_timestamp_text'] = \pmc_human_time( get_the_time( 'U' ) );

	$thumbnail_id = get_post_thumbnail_id( $term_post->ID );

	$river_item['c_dek']['c_dek_markup'] = pmc_get_excerpt( $term_post->ID );

	$primary_category = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $term_post->ID, 'category' );

	$river_item['c_span']['c_span_text'] = $primary_category->name ?? '';
	$river_item['c_span']['c_span_url']  = get_tag_link( $primary_category->term_id ?? '' );

	$river_item['c_lazy_image']['c_lazy_image_src_url']         = wp_get_attachment_image_url( $thumbnail_id, 'medium', false );
	$river_item['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( $thumbnail_id );
	$river_item['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes( $thumbnail_id );
	$river_item['c_lazy_image']['c_lazy_image_alt_attr']        = \PMC::get_attachment_image_alt_text( $thumbnail_id );
	$river_item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$river_item['c_lazy_image']['c_lazy_image_link_url']        = get_permalink( $term_post->ID );

	$river_item_array[] = $river_item;
}

$profile_landing_river['profile_landing_river_stories'] = $river_item_array;

\PMC::render_template(
	sprintf( '%s/build/patterns/modules/profile-landing-river.php', \PMC\Larva\Config::get_instance()->get( 'core_directory' ) ),
	$profile_landing_river,
	true
);
