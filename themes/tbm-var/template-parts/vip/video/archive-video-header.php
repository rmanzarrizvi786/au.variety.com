<?php
/**
 * Archive Video Header Template.
 *
 * @package pmc-variety
 */

use Variety\Plugins\Variety_VIP\Content;
use Variety\Plugins\Variety_VIP\Video;
use Variety\Inc\Carousels;

global $wp_query;

$header = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/archive-video-header.variety-vip' );

if ( is_tax() ) {
	$term = get_queried_object();

	if ( is_a( $term, 'WP_Term' ) ) {
		$featured = Carousels::get_carousel_posts( $term->slug, 1, $term->taxonomy );
	}
} else {
	$featured = Carousels::get_carousel_posts( Video::ARCHIVE_CAROUSEL, 1 );
}

if ( empty( $featured ) ) {
	if ( ! empty( $term ) ) {
		$featured = Video::get_latest_video( 1, $term );
	} else {
		$featured = Video::get_latest_video( 1 );
	}

	$featured = $featured->posts;
}

if ( empty( $featured ) ) {
	return;
}

$featured = $featured[0];

$header['o_video_card']['o_video_card_permalink_url']  = get_permalink( $featured );
$header['o_video_card']['c_heading']['c_heading_text'] = variety_get_card_title( $featured );
$header['o_video_card']['c_heading']['c_heading_url']  = get_permalink( $featured );

if ( ! empty( $featured->image_id ) ) {
	$thumbnail = $featured->image_id;
} else {
	$thumbnail = get_post_thumbnail_id( $featured );
}

if ( ! empty( $thumbnail ) ) {
	$image = \PMC\Core\Inc\Media::get_instance()->get_image_data( $thumbnail, 'landscape-large' );

	$header['o_video_card']['o_video_card_alt_attr']       = $image['image_alt'];
	$header['o_video_card']['o_video_card_image_url']      = $image['src'];
	$header['o_video_card']['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$header['o_video_card']['o_video_card_caption_text']   = $image['image_caption'];
} else {
	$header['o_video_card']['o_video_card_alt_attr']       = '';
	$header['o_video_card']['o_video_card_image_url']      = '';
	$header['o_video_card']['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$header['o_video_card']['o_video_card_caption_text']   = '';
}

$category = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $featured->ID, Content::VIP_PLAYLIST_TAXONOMY );

if ( ! empty( $category ) ) {
	$header['o_video_card']['o_indicator']['c_span']['c_span_text'] = $category->name;
	$header['o_video_card']['o_indicator']['c_span']['c_span_url']  = get_term_link( $category );
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/archive-video-header.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$header,
	true
);
