<?php
/**
 * Video Carousel VIP Template.
 *
 * @package pmc-variety
 */

use Variety\Plugins\Variety_VIP\Content;
use Variety\Inc\Carousels;

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/video-carousel.variety-vip' );

if ( ! empty( $video_carousel_classes ) ) {
	$data['video_carousel_classes'] = $video_carousel_classes;
}

$_posts = \Variety\Plugins\Variety_VIP\Carousel::get_vip_carousel_posts( 'vip-home-featured-video', 4, false, [ Content::VIP_VIDEO_POST_TYPE ] );

if ( empty( $_posts ) ) {
	return;
}

$template            = $data['video_items'][0];
$data['video_items'] = [];

foreach ( $_posts as $index => $_post ) {
	$item = $template;

	$item['o_video_card_permalink_url']  = get_permalink( $_post );
	$item['c_heading']['c_heading_text'] = variety_get_card_title( $_post );
	$item['c_heading']['c_heading_url']  = get_permalink( $_post );

	if ( ! empty( $_post->image_id ) ) {
		$thumbnail = $_post->image_id;
	} else {
		$thumbnail = get_post_thumbnail_id( $_post );
	}

	if ( ! empty( $thumbnail ) ) {
		$image = \PMC\Core\Inc\Media::get_instance()->get_image_data( $thumbnail, 'landscape-large' );

		$item['o_video_card_alt_attr']       = $image['image_alt'];
		$item['o_video_card_image_url']      = $image['src'];
		$item['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$item['o_video_card_caption_text']   = $image['image_caption'];
	} else {
		$item['o_video_card_alt_attr']       = '';
		$item['o_video_card_image_url']      = '';
		$item['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$item['o_video_card_caption_text']   = '';
	}

	$category = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $_post->ID, Content::VIP_PLAYLIST_TAXONOMY );

	if ( ! empty( $category ) ) {
		$item['o_indicator']['c_span']['c_span_text'] = $category->name;
		$item['o_indicator']['c_span']['c_span_url']  = get_term_link( $category );
	}

	$data['video_items'][] = $item;
}

// Move the last item to the first position due to the second item being initially selected.
$data['video_items'] = array_merge( array_splice( $data['video_items'], -1 ), $data['video_items'] );

$data['view_all_link']['c_link']['c_link_url'] = '/vip-video/';

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/video-carousel.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
