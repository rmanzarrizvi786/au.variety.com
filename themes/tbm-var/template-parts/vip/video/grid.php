<?php
/**
 * Archive Video Grid Template.
 *
 * @package pmc-variety
 */

use Variety\Plugins\Variety_VIP\Content;

$grid = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/video-grid.variety-vip' );

global $wp_query, $paged;

$latest   = $wp_query->posts;
$template = $grid['video_items'][0];

$grid['video_items'] = [];

if ( ! empty( $latest ) ) {
	foreach ( $latest as $_post ) {
		$item = $template;

		$item['o_video_card_permalink_url']  = get_permalink( $_post );
		$item['c_heading']['c_heading_text'] = pmc_get_title( $_post );
		$item['c_heading']['c_heading_url']  = get_permalink( $_post );

		$thumbnail = get_post_thumbnail_id( $_post );

		if ( ! empty( $thumbnail ) ) {
			$item['o_video_card_alt_attr']       = get_post_meta( $thumbnail, '_wp_attachment_image_alt', true );
			$item['o_video_card_image_url']      = get_the_post_thumbnail_url( $_post );
			$item['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
			$item['o_video_card_caption_text']   = wp_get_attachment_caption( $thumbnail );
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

		$grid['video_items'][] = $item;
	}
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/video-grid.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$grid,
	true
);
