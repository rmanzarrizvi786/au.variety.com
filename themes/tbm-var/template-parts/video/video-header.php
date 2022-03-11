<?php
/**
 * Video Header.
 *
 * @package pmc-variety
 */

if ( is_single() ) {
	$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/video-header.article' );

	$data['video_menu_mobile']['c_span']['c_span_text'] = __( 'More Playlists', 'pmc-variety' );
} elseif ( is_tax( 'vcategory' ) ) {
	$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/video-header.playlist' );

	$data['video_menu']['o_nav']['o_nav_list_items'][0]['c_link_text'] = get_queried_object()->name;
	$data['video_menu']['o_nav']['o_nav_list_items'][0]['c_link_url']  = get_term_link( get_queried_object()->term_id );
	$data['video_menu_mobile']['c_span']['c_span_text']                = get_queried_object()->name;
} else {
	$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/video-header.prototype' );
}

if ( is_archive() ) {
	$data['c_heading']['c_heading_is_primary_heading'] = true;
}

$mobile_template = $data['video_menu_mobile']['o_nav']['o_nav_list_items'][0];

// Reset mobile menu for all templates.
$data['video_menu_mobile']['o_nav']['o_nav_list_items'] = [];

if ( ! is_single() && ! is_tax( 'vcategory' ) ) {
	// Menu.
	$_menu    = PMC\Core\Inc\Menu::get_instance()->get_menu_data( 'variety-top-video-menu' );
	$template = $data['video_menu']['o_nav']['o_nav_list_items'][1];

	$data['video_menu']['o_nav']['o_nav_list_items'] = [];

	if ( is_array( $_menu ) && isset( $_menu['root'] ) ) {
		foreach ( $_menu['root'] as $key => $val ) {
			$item = $template;

			$item['c_link_text'] = $val['c_nav_link_text'];
			$item['c_link_url']  = $val['c_nav_link_url'];

			$data['video_menu']['o_nav']['o_nav_list_items'][] = $item;

			// Mobile menu.
			$item = $mobile_template;

			$item['c_link_text'] = $val['c_nav_link_text'];
			$item['c_link_url']  = $val['c_nav_link_url'];

			$data['video_menu_mobile']['o_nav']['o_nav_list_items'][] = $item;
		}

		// Mobile menu title.
		$first = array_shift( $_menu['root'] );

		if ( ! empty( $first['c_nav_link_text'] ) ) {
			$data['video_menu_mobile']['c_span']['c_span_text'] = $first['c_nav_link_text'];
		}
	}
}

// Drop Down.
$_menu = PMC\Core\Inc\Menu::get_instance()->get_menu_data( 'variety-top-video-dropdown' );

$_template = $data['video_menu']['o_drop_menu']['o_nav']['o_nav_list_items'][0];

$data['video_menu']['o_drop_menu']['o_nav']['o_nav_list_items'] = [];

if ( is_array( $_menu ) && isset( $_menu['root'] ) ) {
	foreach ( $_menu['root'] as $key => $val ) {
		$item = $_template;

		$item['c_link_text'] = $val['c_nav_link_text'];
		$item['c_link_url']  = $val['c_nav_link_url'];

		$data['video_menu']['o_drop_menu']['o_nav']['o_nav_list_items'][] = $item;

		// Mobile menu.
		$item = $mobile_template;

		$item['c_link_text'] = $val['c_nav_link_text'];
		$item['c_link_url']  = $val['c_nav_link_url'];

		$data['video_menu_mobile']['o_nav']['o_nav_list_items'][] = $item;
	}
}

// Showcase.
if ( is_single() ) {
	$_posts = variety_get_playlist_videos_for_single_page( get_queried_object(), 0, 8 );

	// Add the current post to the top of the queue.
	$featured = get_queried_object();
} elseif ( is_tax( 'vcategory' ) ) {
	$_term = get_queried_object();

	if ( is_a( $_term, 'WP_Term' ) ) {
		$_posts   = \Variety\Inc\Carousels::get_video_carousel_posts( $_term->slug, 1, $_term->taxonomy, 'post' );
		$featured = $_posts[0];
	}
} else {
	$_posts = \Variety\Inc\Carousels::get_video_carousel_posts( 'featured-video', 8, false, 'post' );

	// First video should be featured.
	$featured = $_posts[0];
}

$data['video_showcase']['o_video_card']['o_video_card_permalink_url']  = get_permalink( $featured );
$data['video_showcase']['o_video_card']['c_heading']['c_heading_text'] = variety_get_card_title( $featured );
$data['video_showcase']['o_video_card']['c_heading']['c_heading_url']  = get_permalink( $featured );

if ( is_single() ) {
	$data['video_showcase']['o_video_card']['c_heading']['c_heading_is_primary_heading'] = get_permalink( $featured );
}

$data['video_showcase']['o_video_card']['c_span']['c_span_text'] = get_post_meta( $featured->ID, 'variety_top_video_duration', true );

$data['video_showcase']['o_video_card']['o_video_card_link_showcase_title_data_attr']    = variety_get_card_title( $featured );
$data['video_showcase']['o_video_card']['o_video_card_link_showcase_time_data_attr']     = get_post_meta( $featured->ID, 'variety_top_video_duration', true );
$data['video_showcase']['o_video_card']['o_video_card_link_showcase_permalink_data_url'] = get_permalink( $featured );
$data['video_showcase']['o_video_card']['o_video_card_link_showcase_autoplay_data_attr'] = true;

if ( ! empty( $featured->custom_excerpt ) ) {
	$data['video_showcase']['o_video_card']['c_dek']['c_dek_text'] = wp_strip_all_tags( $featured->custom_excerpt );
} else {
	$data['video_showcase']['o_video_card']['c_dek']['c_dek_text'] = wp_strip_all_tags( \PMC\Core\Inc\Helper::get_the_excerpt( $featured->ID ) );
}

if ( ! empty( $featured->image_id ) ) {
	$thumbnail = $featured->image_id;
} else {
	$thumbnail = get_post_thumbnail_id( $featured );
}

if ( ! empty( $thumbnail ) ) {
	$image = \PMC\Core\Inc\Media::get_instance()->get_image_data( $thumbnail, 'landscape-large' );

	$data['video_showcase']['o_video_card']['o_video_card_alt_attr']       = $image['image_alt'];
	$data['video_showcase']['o_video_card']['o_video_card_image_url']      = $image['src'];
	$data['video_showcase']['o_video_card']['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$data['video_showcase']['o_video_card']['o_video_card_caption_text']   = $image['image_caption'];
} else {
	$data['video_showcase']['o_video_card']['o_video_card_alt_attr']       = '';
	$data['video_showcase']['o_video_card']['o_video_card_image_url']      = '';
	$data['video_showcase']['o_video_card']['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$data['video_showcase']['o_video_card']['o_video_card_caption_text']   = '';
}

$video_meta = get_post_meta( $featured->ID, 'variety_top_video_source', true );

if ( ! empty( $video_meta ) ) {
	if ( \Variety\Inc\Video::is_jw_player( $video_meta ) ) {
		$data['video_showcase']['o_video_card']['o_video_card_link_showcase_trigger_data_attr'] = \Variety\Inc\Video::get_jw_id( $video_meta );
		$data['video_showcase']['o_video_card']['o_video_card_link_showcase_type_data_attr']    = 'jwplayer';
	} else {
		$video_source = \Variety\Inc\Video::get_instance()->get_video_source( $featured->ID );

		$data['video_showcase']['o_video_card']['o_video_card_link_showcase_trigger_data_attr'] = $video_source;
	}
} else {
	$data['video_showcase']['o_video_card']['o_video_card_link_showcase_trigger_data_attr'] = '';
}

$category = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $featured->ID, 'vcategory' );

if ( ! empty( $category ) ) {
	$data['video_showcase']['o_video_card']['o_indicator']['c_span']['c_span_text'] = $category->name;
	$data['video_showcase']['o_video_card']['o_indicator']['c_span']['c_span_url']  = ! is_single() ? get_term_link( $category ) : '';
}

// Related videos.
if ( ! is_tax( 'vcategory' ) ) {
	$_template_1 = $data['video_showcase']['related_videos']['o_video_card_list']['o_video_card_list_items'][0];
	$_template_2 = $data['video_showcase']['related_videos']['o_video_card_list']['o_video_card_list_items'][1];

	$data['video_showcase']['related_videos']['o_video_card_list']['o_video_card_list_items'] = [];

	$count = 1;

	foreach ( $_posts as $index => $_post ) {
		if ( 1 === $count ) {
			$item = $_template_1;
		} else {
			$item = $_template_2;
		}

		$item['o_video_card_permalink_url']  = get_permalink( $_post );
		$item['c_heading']['c_heading_text'] = pmc_get_title( $_post );
		$item['c_heading']['c_heading_url']  = get_permalink( $_post );
		$item['c_span']['c_span_text']       = get_post_meta( $_post->ID, 'variety_top_video_duration', true );

		$item['o_video_card_link_showcase_title_data_attr']    = variety_get_card_title( $_post );
		$item['o_video_card_link_showcase_time_data_attr']     = get_post_meta( $_post->ID, 'variety_top_video_duration', true );
		$item['o_video_card_link_showcase_permalink_data_url'] = get_permalink( $_post );

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

		$video_meta = get_post_meta( $_post->ID, 'variety_top_video_source', true );

		if ( ! empty( $video_meta ) ) {
			if ( \Variety\Inc\Video::is_jw_player( $video_meta ) ) {
				$item['o_video_card_link_showcase_trigger_data_attr'] = \Variety\Inc\Video::get_jw_id( $video_meta );
				$item['o_video_card_link_showcase_type_data_attr']    = 'jwplayer';
			} else {
				$video_source = \Variety\Inc\Video::get_instance()->get_video_source( $_post->ID );

				$item['o_video_card_link_showcase_trigger_data_attr'] = $video_source;
			}
		} else {
			$item['o_video_card_link_showcase_trigger_data_attr'] = '';
		}

		$data['video_showcase']['related_videos']['o_video_card_list']['o_video_card_list_items'][] = $item;

		$count ++;
	}
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/video-header.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
