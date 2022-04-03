<?php

/**
 * Article Header Template.
 *
 * @package pmc-variety
 */

$has_linked_dirt_gallery = get_post_meta(get_the_ID(), 'dirt_permalink', true);

if (PMC_Featured_Video_Override::get_instance()->has_featured_video(get_the_ID())) {
	$data = PMC\Core\Inc\Larva::get_instance()->get_json('modules/article-header.featured-video');
} elseif (PMC::has_linked_gallery(get_the_ID()) && !$has_linked_dirt_gallery) {
	$data = PMC\Core\Inc\Larva::get_instance()->get_json('modules/article-header.linked-gallery');
} else {
	$data = PMC\Core\Inc\Larva::get_instance()->get_json('modules/article-header.prototype');
}

// Post data.
$data['o_title']['c_heading']['c_heading_text']          = get_the_title();
$data['article_meta']['c_timestamp']['c_timestamp_text'] = get_the_time('M j, Y g:ia'); // . ' PT';

$sub_heading = get_post_meta(get_the_ID(), '_variety-sub-heading', true);

if (!empty($sub_heading)) {
	$data['o_custom_paragraph']['o_custom_paragraph_text'] = $sub_heading;
} else {
	$data['o_custom_paragraph']['o_custom_paragraph_text'] = '';
}

// Breadcrumb.
$breadcrumb = \PMC\Core\Inc\Theme::get_instance()->get_breadcrumb();

$data['article_meta']['breadcrumbs']['o_nav']['o_nav_list_items']                  = [$data['article_meta']['breadcrumbs']['o_nav']['o_nav_list_items'][0]];
$data['article_meta']['breadcrumbs']['o_nav']['o_nav_list_items'][0]['c_link_url'] = '/';

if (!empty($breadcrumb)) {
	foreach ($breadcrumb as $crumb) {
		$breadcrumb_item                = $data['article_meta']['breadcrumbs']['o_nav']['o_nav_list_items'][0];
		$breadcrumb_item['c_link_text'] = $crumb->name;
		$breadcrumb_item['c_link_url']  = get_term_link($crumb);

		$data['article_meta']['breadcrumbs']['o_nav']['o_nav_list_items'][] = $breadcrumb_item;
	}
}

// Author.
$custom_author = get_post_meta(get_the_ID(), 'author', true);

$author_data = PMC\Core\Inc\Author::get_instance()->authors_data();
if (!$custom_author && !empty($author_data['single_author'])) {

	$author_url = get_author_posts_url($author_data['single_author']['author']->ID, $author_data['single_author']['author']->user_nicename);

	if (!empty($author_data['single_author']['picture']['image'])) {
		$data['author_social']['author']['c_lazy_image']['c_lazy_image_src_url']     = $author_data['single_author']['picture']['image'];
		$data['author_social']['author']['c_lazy_image']['c_lazy_image_alt_attr']    = $author_data['single_author']['picture']['name'] ?? '';
		$data['author_social']['author']['c_lazy_image']['c_lazy_image_srcset_attr'] = \wp_get_attachment_image_srcset(get_post_thumbnail_id());
		$data['author_social']['author']['c_lazy_image']['c_lazy_image_sizes_attr']  = \wp_get_attachment_image_sizes(get_post_thumbnail_id());
	} else {
		$data['author_social']['author']['c_lazy_image'] = false;
	}

	$data['author_social']['author']['author_details']['c_tagline']['c_tagline_text'] = ''; // $author_data['single_author']['more_info']['author_role'];

	if (!empty($author_data['single_author']['more_info']['twitter'])) {
		$data['author_social']['author']['author_details']['c_link_twitter_profile']['c_link_text'] = $author_data['single_author']['more_info']['twitter']['handle'];
		$data['author_social']['author']['author_details']['c_link_twitter_profile']['c_link_url']  = $author_data['single_author']['more_info']['twitter']['link'];
	} else {
		$data['author_social']['author']['author_details']['c_link_twitter_profile'] = false;
	}

	$data['author_social']['author']['c_link']['c_link_text'] = $author_data['single_author']['author']->display_name;
	$data['author_social']['author']['c_link']['c_link_url']  = $author_url;

	$data['author_social']['author']['author_details']['c_title']['c_title_url']    = $author_url;
	$data['author_social']['author']['author_details']['c_title']['c_title_markup'] = $author_data['single_author']['author']->display_name;

	$author_article_ids       = PMC\Core\Inc\Author::get_instance()->get_author_posts($author_data['single_author']['author']->user_nicename);

	// var_dump($author_article_ids);
	// exit;

	$author_article_prototype = $data['author_social']['author']['author_details']['stories'][0];
	$author_article_list      = [];

	if (!empty($author_article_ids) && is_array($author_article_ids)) {

		foreach ($author_article_ids as $article_id) {

			$article_item = $author_article_prototype;

			$article_item['c_link']['c_link_text']           = pmc_get_title($article_id);
			$article_item['c_link']['c_link_url']            = get_the_permalink($article_id);
			$article_item['c_timestamp']['c_timestamp_text'] = sprintf('%1$s %2$s', human_time_diff(get_the_time('U', $article_id), current_time('timestamp')), __('ago', 'pmc-variety'));

			$author_article_list[] = $article_item;
		}
	}

	$data['author_social']['author']['author_details']['stories'] = $author_article_list;

	$data['author_social']['author']['author_details']['c_link_view_all']['c_link_url'] = $author_url;
} else {
	if ($custom_author) {
		$data['author_social']['author']['c_tagline']['c_tagline_markup'] = $custom_author;
	} else {
		$data['author_social']['author']['c_tagline']['c_tagline_markup'] = $author_data['byline'];
	}
	$data['author_social']['author']['is_byline_only']                = true;
}

// Comment count.
$data['author_social']['o_comments_link']['c_link']['c_link_text'] = '';

// Social.
$social_share_data = \PMC\Core\Inc\Sharing::get_instance()->get_icons();

if (\PMC\Core\Inc\Sharing::has_icons($social_share_data)) {

	$social_icon_prototype = $data['author_social']['social_share']['primary'][0];

	$primary_share_items   = [];
	$secondary_share_items = [];

	if (!empty($social_share_data['primary']) && is_array($social_share_data['primary'])) {

		foreach ($social_share_data['primary'] as $icon_name => $icon_data) {

			$share_icon = $social_icon_prototype;

			$share_icon['c_icon_url']          = $icon_data->url;
			$share_icon['c_icon_name']         = $icon_name;
			$share_icon['c_icon_rel_name']     = $icon_name;
			$share_icon['c_icon_link_classes'] = sprintf('%1$s u-color-%2$s:hover', $share_icon['c_icon_link_classes'], $icon_name);

			$primary_share_items[] = $share_icon;
		}
	}

	if (!empty($social_share_data['secondary']) && is_array($social_share_data['secondary'])) {

		foreach ($social_share_data['secondary'] as $icon_name => $icon_data) {

			$share_icon = $social_icon_prototype;

			$share_icon['c_icon_url']          = $icon_data->url;
			$share_icon['c_icon_name']         = $icon_name;
			$share_icon['c_icon_rel_name']     = $icon_name;
			$share_icon['c_icon_link_classes'] = sprintf('%1$s u-color-%2$s:hover', $share_icon['c_icon_link_classes'], $icon_name);

			$secondary_share_items[] = $share_icon;
		}
	}

	$data['author_social']['social_share']['primary']   = $primary_share_items;
	$data['author_social']['social_share']['secondary'] = $secondary_share_items;
} else {

	$data['author_social']['social_share']['primary']   = [];
	$data['author_social']['social_share']['secondary'] = [];
}

// Featured Image/Video.
$image_size = 'landscape-large';
$image      = \PMC\Core\Inc\Media::get_instance()->get_image_data_by_post(get_the_ID(), $image_size);

if (PMC_Featured_Video_Override::get_instance()->has_featured_video(get_the_ID())) {
	if (!empty($image['src'])) {
		$data['featured_video']['o_player']['o_player_alt_attr']  = $image['image_alt'];
		$data['featured_video']['o_player']['o_player_image_url'] = $image['src'];

		$data['featured_video']['o_figcaption']['c_figcaption']['c_figcaption_caption_markup'] = $image['image_caption'];
		$data['featured_video']['o_figcaption']['c_figcaption']['c_figcaption_credit_text']    = $image['image_credit'];
	} else {
		$data['featured_video']['o_player']['o_player_alt_attr']  = '';
		$data['featured_video']['o_player']['o_player_image_url'] = '';

		$data['featured_video']['o_figcaption']['c_figcaption']['c_figcaption_caption_markup'] = '';
		$data['featured_video']['o_figcaption']['c_figcaption']['c_figcaption_credit_text']    = '';
	}

	$width = 739;

	if (PMC::is_mobile()) {
		$width = 300;
	}

	$featured_video_arg = [
		'width'         => $width,
		'trackinggroup' => 91211,
		'ratio'         => '16:9',
	];

	// Featured Video.
	$video_meta = get_post_meta(get_the_ID(), PMC_Featured_Video_Override::META_KEY, true);

	if (\Variety\Inc\Video::is_jw_player($video_meta)) {
		$data['featured_video']['o_player']['o_player_trigger_data_attr']  = \Variety\Inc\Video::get_jw_id($video_meta);
		$data['featured_video']['o_player']['o_player_type_data_attr']     = 'jwplayer';
		$data['featured_video']['o_player']['o_player_autoplay_data_attr'] = true;
	} else {
		$escaped_video_html = \PMC_Featured_Video_Override::get_video_html(get_queried_object_id(), $featured_video_arg);
		$escaped_video_html = \Variety\Inc\Video::force_youtube_autoplay($escaped_video_html);
		$escaped_video_html = str_replace('"', "'", $escaped_video_html);

		$data['featured_video']['o_player']['o_player_trigger_data_attr'] = $escaped_video_html;
	}
} elseif (PMC::has_linked_gallery(get_the_ID()) && !$has_linked_dirt_gallery) {

	// Linked gallery.

	$linked_gallery_data = \PMC\Gallery\View::get_linked_gallery_data(get_the_ID());

	if (empty($linked_gallery_data)) {
		return;
	}

	/**
	 * - get image ids
	 */

	$images                        = get_post_meta($linked_gallery_data['id'], \PMC\Gallery\Defaults::NAME, true) ?: [];
	$linked_gallery_data['images'] = array_values($images);
	$image_count                   = count($linked_gallery_data['images']);

	$last_item_index = $image_count - 1;
	$last_item_index = ($last_item_index > 4) ? 4 : $last_item_index;

	if (empty($linked_gallery_data['images']) || !is_array($linked_gallery_data['images']) || $image_count < 3) {
		return;
	}

	$data['linked_gallery']['linked_gallery_url']        = $linked_gallery_data['url'];
	$data['linked_gallery']['linked_gallery_title_text'] = $linked_gallery_data['title'];

	$gallery_linked_figure_id = $linked_gallery_data['images'][0];

	$figure_data = \PMC\Core\Inc\Media::get_instance()->get_image_data($gallery_linked_figure_id, $image_size);

	$data['linked_gallery']['c_lazy_image_primary']['c_lazy_image_src_url']         = $figure_data['src'];
	$data['linked_gallery']['c_lazy_image_primary']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$data['linked_gallery']['c_lazy_image_primary']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset($gallery_linked_figure_id, $image_size);

	$data['linked_gallery']['c_lazy_image_primary']['c_lazy_image_alt_attr']   = $figure_data['img_alt'] ?? '';
	$data['linked_gallery']['c_lazy_image_primary']['c_lazy_image_sizes_attr'] = null;
	$data['linked_gallery']['c_lazy_image_primary']['c_lazy_image_crop_class'] = 'lrv-a-crop-16x9';

	$linked_gallery_item_prototype = $data['linked_gallery']['linked_gallery_items'][0];
	$linked_gallery_items          = [];

	foreach (array_slice($linked_gallery_data['images'], 1, $last_item_index - 1) as $image_id) {

		$img_data    = \PMC\Core\Inc\Media::get_instance()->get_image_data($image_id, 'landscape-small');
		$figure_item = $linked_gallery_item_prototype;

		$figure_item['c_lazy_image_src_url']         = $img_data['src'];
		$figure_item['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$figure_item['c_lazy_image_srcset_attr']     = null;
		$figure_item['c_lazy_image_alt_attr']        = $img_data['img_alt'] ?? '';
		$figure_item['c_lazy_image_sizes_attr']      = null;
		$figure_item['c_lazy_image_crop_class']      = 'lrv-a-crop-16x9';

		$linked_gallery_items[] = $figure_item;
	}

	$last_image_id = $linked_gallery_data['images'][$last_item_index];

	$img_data          = \PMC\Core\Inc\Media::get_instance()->get_image_data($last_image_id, 'landscape-small');
	$last_gallery_item = $data['linked_gallery']['linked_gallery_last_item'];

	$last_gallery_item['c_lazy_image_src_url']         = $img_data['src'];
	$last_gallery_item['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$last_gallery_item['c_lazy_image_srcset_attr']     = null;
	$last_gallery_item['c_lazy_image_alt_attr']        = $img_data['img_alt'] ?? '';
	$last_gallery_item['c_lazy_image_sizes_attr']      = null;
	$last_gallery_item['c_lazy_image_crop_class']      = 'lrv-a-crop-16x9';

	$data['linked_gallery']['linked_gallery_last_item'] = $last_gallery_item;

	$data['linked_gallery']['linked_gallery_items'] = $linked_gallery_items;
} else {

	if (!empty($image['src'])) {
		$data['o_figure']['o_figure_link_classes']                        = 'u-flex-order-n1@mobile-max';
		$data['o_figure']['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
		$data['o_figure']['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$data['o_figure']['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset(get_post_thumbnail_id(), $image_size);
		$data['o_figure']['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes(get_post_thumbnail_id(), $image_size);
		$data['o_figure']['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
		$data['o_figure']['c_figcaption']['c_figcaption_caption_markup']  = $image['image_caption'];
		$data['o_figure']['c_figcaption']['c_figcaption_credit_text']     = $image['image_credit'];
	} else {
		$data['o_figure'] = [];
	}
}

if ($has_linked_dirt_gallery) {

	$linked_gallery_url = \get_post_meta(get_the_ID(), 'dirt_permalink', true);
	$linked_gallery     = \PMC\Gallery\View::get_linked_gallery_data(get_the_ID());
	if (!empty($linked_gallery['url'])) {
		$linked_gallery_url = $linked_gallery['url'];
	}

	$data['o_figure']['o_figure_link_url'] = $linked_gallery_url;

	$data['dirt_details']['dirt_details_view_gallery_label_text'] = __('View Gallery', 'pmc-variety');
	$data['dirt_details']['dirt_details_view_gallery_value_text'] = \get_post_meta(get_the_ID(), 'dirt_syn_gallery_count', true);
	$data['dirt_details']['dirt_details_view_gallery_url']        = $linked_gallery_url;
	$data['dirt_details']['dirt_details_seller_label_text']       = __('Seller', 'pmc-variety');
	$data['dirt_details']['dirt_details_seller_value_text']       = \get_post_meta(get_the_ID(), 'dirt-meta_seller', true);
	$data['dirt_details']['dirt_details_location_label_text']     = __('Location', 'pmc-variety');
	$data['dirt_details']['dirt_details_location_value_text']     = \get_post_meta(get_the_ID(), 'dirt-meta_location', true);
	$data['dirt_details']['dirt_details_price_label_text']        = __('Price', 'pmc-variety');
	$data['dirt_details']['dirt_details_price_value_text']        = \get_post_meta(get_the_ID(), 'dirt-meta_price', true);
	$data['dirt_details']['dirt_details_size_label_text']         = __('Size', 'pmc-variety');
	$data['dirt_details']['dirt_details_size_value_text']         = \get_post_meta(get_the_ID(), 'dirt-meta_size', true);
} else {
	$data['dirt_details'] = false;
}

// Sponsored.
$is_sponsored = get_post_meta(get_the_ID(), 'vy-sponsored-content', true);

if ($is_sponsored) {
	$sponsor_data        = PMC\Core\Inc\Larva::get_instance()->get_json('modules/article-header.sponsored');
	$data['c_sponsored'] = $sponsor_data['c_sponsored'];
}

// Review.
if (variety_is_review()) {
	$badge = \Variety\Inc\Badges\Critics_Pick::get_instance();

	if ($badge->exists_on_post(get_the_ID())) {
		$editorial_data = PMC\Core\Inc\Larva::get_instance()->get_json('modules/article-header.taxonomy-highlight');

		$data['c_taxonomy_highlight']                             = $editorial_data['c_taxonomy_highlight'];
		$data['c_taxonomy_highlight']['c_taxonomy_highlight_url'] = $badge->get_link();
	}
}

\PMC::render_template(
	sprintf('%s/template-parts/patterns/modules/article-header.php', untrailingslashit(CHILD_THEME_PATH)),
	$data,
	true
);
