<?php

/**
 * Video module.
 *
 * @package pmc-variety
 */

use Variety\Plugins\Variety_VIP\Content;

if (empty($data['videos'])) {
	return;
}

$videos = PMC\Core\Inc\Larva::get_instance()->get_json('modules/homepage-video.prototype');

// Title.
$videos['c_heading']['c_heading_text'] = $data['title'];

// Featured video.
$featured = $data['videos'][0];

$videos['video_showcase']['o_video_card']['o_video_card_permalink_url']  = get_permalink($featured);
$videos['video_showcase']['o_video_card']['c_heading']['c_heading_text'] = variety_get_card_title($featured);
$videos['video_showcase']['o_video_card']['c_heading']['c_heading_url']  = get_permalink($featured);
$videos['video_showcase']['o_video_card']['c_span']['c_span_text']       = get_post_meta($featured->ID, 'variety_top_video_duration', true);

$videos['video_showcase']['o_video_card']['o_video_card_link_showcase_title_data_attr']    = variety_get_card_title($featured);
$videos['video_showcase']['o_video_card']['o_video_card_link_showcase_time_data_attr']     = get_post_meta($featured->ID, 'variety_top_video_duration', true);
$videos['video_showcase']['o_video_card']['o_video_card_link_showcase_permalink_data_url'] = get_permalink($featured);

if (!empty($featured->image_id)) {
	$thumbnail = $featured->image_id;
} else {
	$thumbnail = get_post_thumbnail_id($featured);
}

if (!empty($thumbnail)) {
	$image = \PMC\Core\Inc\Media::get_instance()->get_image_data($thumbnail, 'landscape-large');

	$videos['video_showcase']['o_video_card']['o_video_card_alt_attr']       = $image['image_alt'];
	$videos['video_showcase']['o_video_card']['o_video_card_image_url']      = $image['src'];
	$videos['video_showcase']['o_video_card']['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$videos['video_showcase']['o_video_card']['o_video_card_caption_text']   = $image['image_caption'];
} else {
	$videos['video_showcase']['o_video_card']['o_video_card_alt_attr']       = '';
	$videos['video_showcase']['o_video_card']['o_video_card_image_url']      = '';
	$videos['video_showcase']['o_video_card']['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$videos['video_showcase']['o_video_card']['o_video_card_caption_text']   = '';
}

$video_meta = get_post_meta($featured->ID, 'variety_top_video_source', true);

if (!empty($video_meta)) {
	if (\Variety\Inc\Video::is_jw_player($video_meta)) {
		$videos['video_showcase']['o_video_card']['o_video_card_link_showcase_trigger_data_attr'] = \Variety\Inc\Video::get_jw_id($video_meta);
		$videos['video_showcase']['o_video_card']['o_video_card_link_showcase_type_data_attr']    = 'jwplayer';
	} else {
		$video_source = \Variety\Inc\Video::get_instance()->get_video_source($featured->ID);

		$videos['video_showcase']['o_video_card']['o_video_card_link_showcase_trigger_data_attr'] = $video_source;
	}
} else {
	$videos['video_showcase']['o_video_card']['o_video_card_link_showcase_trigger_data_attr'] = '';
}

// Related videos.
$_template = $videos['video_showcase']['related_videos']['o_video_card_list']['o_video_card_list_items'][0];

$videos['video_showcase']['related_videos']['o_video_card_list']['o_video_card_list_items'] = [];

foreach ($data['videos'] as $_post) {
	$item = $_template;

	$item['o_video_card_permalink_url']  = get_permalink($_post);
	$item['c_heading']['c_heading_text'] = variety_get_card_title($_post);
	$item['c_heading']['c_heading_url']  = get_permalink($_post);
	$item['c_span']['c_span_text']       = get_post_meta($_post->ID, 'variety_top_video_duration', true);

	if (!empty($_post->image_id)) {
		$thumbnail = $_post->image_id;
	} else {
		$thumbnail = get_post_thumbnail_id($_post);
	}

	if (!empty($thumbnail)) {
		$image = \PMC\Core\Inc\Media::get_instance()->get_image_data($thumbnail, 'landscape-large');

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

	$video_meta = get_post_meta($_post->ID, 'variety_top_video_source', true);

	if (!empty($video_meta)) {
		if (\Variety\Inc\Video::is_jw_player($video_meta)) {
			$item['o_video_card_link_showcase_trigger_data_attr'] = \Variety\Inc\Video::get_jw_id($video_meta);
			$item['o_video_card_link_showcase_type_data_attr']    = 'jwplayer';
		} else {
			$video_source = \Variety\Inc\Video::get_instance()->get_video_source($_post->ID);

			$item['o_video_card_link_showcase_trigger_data_attr'] = $video_source;
		}
	} else {
		$item['o_video_card_link_showcase_trigger_data_attr'] = '';
	}

	// Attributes.
	$item['o_video_card_link_showcase_title_data_attr']    = variety_get_card_title($_post);
	$item['o_video_card_link_showcase_time_data_attr']     = get_post_meta($_post->ID, 'variety_top_video_duration', true);
	$item['o_video_card_link_showcase_permalink_data_url'] = get_permalink($_post);

	$videos['video_showcase']['related_videos']['o_video_card_list']['o_video_card_list_items'][] = $item;
}

// More link.
$videos['o_more_link']['c_link']['c_link_text'] = $data['more_text'];
$videos['o_more_link']['c_link']['c_link_url']  = $data['more_link'];

\PMC::render_template(
	sprintf('%s/template-parts/patterns/modules/homepage-video.php', untrailingslashit(CHILD_THEME_PATH)),
	$videos,
	true
);
