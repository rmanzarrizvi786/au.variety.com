<?php
/**
 * Article Video Header Template.
 *
 * @package pmc-variety
 */

use \Variety\Plugins\Variety_VIP\Content;

$article_video_header = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/article-video-header.variety-vip' );

// Video card.
$thumbnail = \PMC\Core\Inc\Media::get_instance()->get_image_data_by_post( get_the_ID(), 'landscape-large' );

if ( ! empty( $thumbnail['src'] ) ) {
	$article_video_header['o_video_card']['o_video_card_alt_attr']       = $thumbnail['image_alt'];
	$article_video_header['o_video_card']['o_video_card_image_url']      = $thumbnail['src'];
	$article_video_header['o_video_card']['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$article_video_header['o_video_card']['o_video_card_caption_text']   = $thumbnail['image_caption'];
} else {
	$article_video_header['o_video_card']['o_video_card_alt_attr']       = '';
	$article_video_header['o_video_card']['o_video_card_image_url']      = '';
	$article_video_header['o_video_card']['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$article_video_header['o_video_card']['o_video_card_caption_text']   = '';
}

// Fetch the video source.
$video_meta = get_post_meta( get_the_ID(), 'variety_top_video_source', true );

if ( ! empty( $video_meta ) ) {
	if ( \Variety\Inc\Video::is_jw_player( $video_meta ) ) {
		$article_video_header['o_video_card']['o_video_card_link_showcase_trigger_data_attr'] = \Variety\Inc\Video::get_jw_id( $video_meta );
		$article_video_header['o_video_card']['o_video_card_link_showcase_type_data_attr']    = 'jwplayer';
	} else {
		$video_source = \Variety\Inc\Video::get_instance()->get_video_source();

		$article_video_header['o_video_card']['o_video_card_link_showcase_trigger_data_attr'] = $video_source;
	}
} else {
	$article_video_header['o_video_card']['o_video_card_link_showcase_trigger_data_attr'] = '';
}

// Set video duration. 1) initial time 2) data attribute (used in play lists to dynamically update DOM for different videos)
$video_time = get_post_meta( get_the_ID(), 'variety_top_video_duration', true ) ?? '';
$article_video_header['o_video_card']['c_span']['c_span_text']                     = $video_time;
$article_video_header['o_video_card']['o_video_card_link_showcase_time_data_attr'] = $video_time;

// Indicator.
$playlist = get_the_terms( get_the_ID(), Content::VIP_PLAYLIST_TAXONOMY );

if ( ! empty( $playlist[0] ) ) {
	$article_video_header['o_indicator']['c_span']['c_span_text']                 = $playlist[0]->name;
	$article_video_header['o_video_card']['o_indicator']['c_span']['c_span_text'] = $playlist[0]->name;
	$article_video_header['o_indicator']['c_span']['c_span_url']                  = get_term_link( $playlist[0] );
	$article_video_header['o_video_card']['o_indicator']['c_span']['c_span_url']  = get_term_link( $playlist[0] );
} else {
	$article_video_header['o_indicator']['c_span']['c_span_text']                 = '';
	$article_video_header['o_video_card']['o_indicator']['c_span']['c_span_text'] = '';
	$article_video_header['o_indicator']['c_span']['c_span_url']                  = '';
	$article_video_header['o_video_card']['o_indicator']['c_span']['c_span_url']  = '';
}

// Heading.
$article_video_header['c_heading']['c_heading_text']                 = get_the_title();
$article_video_header['o_video_card']['c_heading']['c_heading_text'] = get_the_title();
$article_video_header['o_video_card']['c_heading']['c_heading_url']  = '';

// Timestamp.
$article_video_header['c_timestamp']['c_timestamp_text'] = get_the_time( 'F j, Y g:ia' ) . ' PT';

// Social.
$social_share_data = \PMC\Core\Inc\Sharing::get_instance()->get_icons();

if ( \PMC\Core\Inc\Sharing::has_icons( $social_share_data ) ) {

	$social_icon_prototype = $article_video_header['social_share']['primary'][0];

	$primary_share_items   = [];
	$secondary_share_items = [];

	if ( ! empty( $social_share_data['primary'] ) && is_array( $social_share_data['primary'] ) ) {

		foreach ( $social_share_data['primary'] as $icon_name => $icon_data ) {

			$share_icon = $social_icon_prototype;

			$share_icon['c_icon_url']          = $icon_data->url;
			$share_icon['c_icon_name']         = $icon_name;
			$share_icon['c_icon_rel_name']     = $icon_name;
			$share_icon['c_icon_link_classes'] = sprintf( '%1$s u-color-%2$s:hover', $share_icon['c_icon_link_classes'], $icon_name );

			$primary_share_items[] = $share_icon;
		}
	}

	if ( ! empty( $social_share_data['secondary'] ) && is_array( $social_share_data['secondary'] ) ) {

		foreach ( $social_share_data['secondary'] as $icon_name => $icon_data ) {

			$share_icon = $social_icon_prototype;

			$share_icon['c_icon_url']          = $icon_data->url;
			$share_icon['c_icon_name']         = $icon_name;
			$share_icon['c_icon_rel_name']     = $icon_name;
			$share_icon['c_icon_link_classes'] = sprintf( '%1$s u-color-%2$s:hover', $share_icon['c_icon_link_classes'], $icon_name );

			$secondary_share_items[] = $share_icon;
		}
	}

	$article_video_header['social_share']['primary']   = $primary_share_items;
	$article_video_header['social_share']['secondary'] = $secondary_share_items;

} else {

	$article_video_header['social_share']['primary']   = [];
	$article_video_header['social_share']['secondary'] = [];

}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/article-video-header.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$article_video_header,
	true
);
