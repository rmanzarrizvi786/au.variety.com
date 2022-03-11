<?php
/**
 * Videos Template.
 *
 * @package pmc-variety-2020
 */

// Get default docs video prototype.
$videos = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/docs-video.prototype' );

$video_data = \Variety\Inc\Carousels::get_carousel_posts(
	'vy-docs-video',
	5,
);


if ( empty( $video_data ) || count( $video_data ) < 5 ) {
	return;
}

$o_video_card_prototype  = $videos['o_video_card_list']['o_video_card_list_items'][0];
$o_video_card_list_items = [];

foreach ( $video_data as $key => $video ) {

	// If first video, set to large top video.
	if ( array_key_first( $video_data ) === $key ) {
		$videos['o_video_card_top']['o_video_card_permalink_url']  = get_permalink( $video );
		$videos['o_video_card_top']['c_heading']['c_heading_text'] = variety_get_card_title( $video );
		$videos['o_video_card_top']['c_heading']['c_heading_url']  = get_permalink( $video );
		$videos['o_video_card_top']['c_span']['c_span_text']       = get_post_meta( $video->ID, 'variety_top_video_duration', true );

		$vcategory = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $video->ID, 'vcategory' );

		if ( ! empty( $vcategory ) ) {
			$videos['o_video_card_top']['o_indicator']['c_span']['c_span_text'] = $vcategory->name;
			$videos['o_video_card_top']['o_indicator']['c_span']['c_span_url']  = get_term_link( $vcategory );
		} else {
			$videos['o_video_card_top']['o_indicator'] = false;
		}

		$videos['o_video_card_top']['o_video_card_link_showcase_title_data_attr']    = variety_get_card_title( $video );
		$videos['o_video_card_top']['o_video_card_link_showcase_time_data_attr']     = get_post_meta( $video->ID, 'variety_top_video_duration', true );
		$videos['o_video_card_top']['o_video_card_link_showcase_permalink_data_url'] = get_permalink( $video );

		if ( ! empty( $video->image_id ) ) {
			$thumbnail = $video->image_id;
		} else {
			$thumbnail = get_post_thumbnail_id( $video );
		}

		if ( ! empty( $thumbnail ) ) {
			$image = \PMC\Core\Inc\Media::get_instance()->get_image_data( $thumbnail, 'landscape-large' );

			$videos['o_video_card_top']['o_video_card_alt_attr']       = $image['image_alt'];
			$videos['o_video_card_top']['o_video_card_image_url']      = $image['src'];
			$videos['o_video_card_top']['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
			$videos['o_video_card_top']['o_video_card_caption_text']   = $image['image_caption'];
		} else {
			$videos['o_video_card_top']['o_video_card_alt_attr']       = '';
			$videos['o_video_card_top']['o_video_card_image_url']      = '';
			$videos['o_video_card_top']['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
			$videos['o_video_card_top']['o_video_card_caption_text']   = '';
		}

		$video_meta = get_post_meta( $video->ID, 'variety_top_video_source', true );

		if ( ! empty( $video_meta ) ) {
			if ( \Variety\Inc\Video::is_jw_player( $video_meta ) ) {
				$videos['o_video_card_top']['o_video_card_link_showcase_trigger_data_attr'] = \Variety\Inc\Video::get_jw_id( $video_meta );
				$videos['o_video_card_top']['o_video_card_link_showcase_type_data_attr']    = 'jwplayer';
			} else {
				$video_source = \Variety\Inc\Video::get_instance()->get_video_source( $video->ID );

				$videos['o_video_card_top']['o_video_card_link_showcase_trigger_data_attr'] = $video_source;
			}
		} else {
			$videos['o_video_card_top']['o_video_card_link_showcase_trigger_data_attr'] = '';
		}
		continue;
	}

	// Otherwise, set to bottom row card.
	$o_video_card = $o_video_card_prototype;

	$o_video_card['o_video_card_permalink_url']  = get_permalink( $video );
	$o_video_card['c_heading']['c_heading_text'] = variety_get_card_title( $video );
	$o_video_card['c_heading']['c_heading_url']  = get_permalink( $video );

	$o_video_card['c_play_icon']['c_play_badge_classes'] .= ' a-glue--t-60p@mobile-max';

	if ( ! empty( $video->image_id ) ) {
		$thumbnail = $video->image_id;
	} else {
		$thumbnail = get_post_thumbnail_id( $video );
	}

	if ( ! empty( $thumbnail ) ) {
		$image = \PMC\Core\Inc\Media::get_instance()->get_image_data( $thumbnail, 'landscape-large' );

		$o_video_card['o_video_card_alt_attr']       = $image['image_alt'];
		$o_video_card['o_video_card_image_url']      = $image['src'];
		$o_video_card['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$o_video_card['o_video_card_caption_text']   = $image['image_caption'];
	} else {
		$o_video_card['o_video_card_alt_attr']       = '';
		$o_video_card['o_video_card_image_url']      = '';
		$o_video_card['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$o_video_card['o_video_card_caption_text']   = '';
	}

	array_push( $o_video_card_list_items, $o_video_card );
}

$videos['o_video_card_list']['o_video_card_list_items'] = $o_video_card_list_items;

// Get URL for 'More Video' button.
$settings = get_option( 'global_curation', [] );
$settings = $settings['tab_variety_documentaries'];

if ( ! empty( $settings['variety_video_btn_link'] ) ) {
	$videos['o_more_link']['c_link']['c_link_text'] = ! empty( $settings['variety_video_btn_txt'] ) ? $settings['variety_video_btn_txt'] : $videos['o_more_link']['c_link']['c_link_text'];
	$videos['o_more_link']['c_link']['c_link_url']  = $settings['variety_video_btn_link'];
} else {
	$videos['o_more_link'] = false;
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/docs-video.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$videos,
	true
);
