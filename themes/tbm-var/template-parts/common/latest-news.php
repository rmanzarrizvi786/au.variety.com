<?php

/**
 * Latest News Template.
 *
 * @package pmc-variety-2020
 */

// Get default latest news river data.
$river = PMC\Core\Inc\Larva::get_instance()->get_json($module);

// Current page.
$current_page = (get_query_var('paged')) ? get_query_var('paged') : 1;

$river['o_more_from_heading']['c_heading']['c_heading_text'] = $header_text;
$river['o_more_link']['c_link']['c_link_text']               = $more_button;

$template = $river['o_tease_news_list_primary']['o_tease_list_items'][0];

$river['o_tease_news_list_primary']['o_tease_list_items']   = [];
$river['o_tease_news_list_secondary']['o_tease_list_items'] = [];

$count = 1;

if (have_posts()) {
	while (have_posts()) {
		the_post();

		$list_item = $template;

		// Title.
		$list_item['c_title']['c_title_url']  = get_the_permalink();
		$list_item['c_title']['c_title_text'] = get_the_title();

		// Dek.
		$list_item['c_dek']['c_dek_text'] = wp_strip_all_tags(\PMC\Core\Inc\Helper::get_the_excerpt(get_the_ID()));

		// Image.
		$image = \PMC\Core\Inc\Media::get_instance()->get_image_data(get_post_thumbnail_id(), 'landscape-large');

		if (!empty($image['src'])) {
			$list_item['c_lazy_image']['c_lazy_image_link_url']        = get_the_permalink();
			$list_item['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
			$list_item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
			$list_item['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset(get_post_thumbnail_id());
			$list_item['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes(get_post_thumbnail_id());
			$list_item['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
			$list_item['c_figcaption']['c_figcaption_caption_markup']  = $image['image_caption'];
			$list_item['c_figcaption']['c_figcaption_credit_text']     = $image['image_credit'];
		} else {
			$list_item['c_lazy_image'] = [];
		}

		// Video.
		$list_item['is_video'] = false;

		if (PMC_Featured_Video_Override::get_instance()->has_featured_video(get_the_ID()) || \Variety_Top_Videos::POST_TYPE_NAME === get_post_type(get_the_ID())) {
			$list_item['is_video']            = true;
			$list_item['video_permalink_url'] = get_the_permalink();
		}

		// Taxonomy Term.
		$vertical = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy(get_the_ID(), 'vertical');

		if (!empty($vertical)) {
			$list_item['o_taxonomy_item']['c_span']['c_span_text'] = $vertical->name;
			$list_item['o_taxonomy_item']['c_span']['c_span_url']  = get_term_link($vertical);
		}

		if ('VIP' === $list_item['o_taxonomy_item']['c_span']['c_span_text']) {
			$list_item['o_taxonomy_item']['c_span']['c_span_text'] = __('VIP+', 'pmc-variety');
		}

		if ($count <= 4) {
			$river['o_tease_news_list_primary']['o_tease_list_items'][] = $list_item;
		} else {
			$river['o_tease_news_list_secondary']['o_tease_list_items'][] = $list_item;
		}

		$count++;
	}
}

// Previous / Next.
$next_post_link = get_next_posts_link() ? next_posts(0, false) : false;
$prev_post_link = get_previous_posts_link() ? previous_posts(false) : false;

if (!empty($next_post_link)) {
	if (1 === $current_page) {
		$river['o_more_link']['c_link']['c_link_text'] = $more_button;
	} else {
		$river['o_more_link']['c_link']['c_link_text'] = __('Next', 'pmc-variety');
	}
	$river['o_more_link']['c_link']['c_link_url'] = $next_post_link;
} else {
	$river['o_more_link']['c_link'] = false;
}

// Previous.
if (!empty($prev_post_link)) {
	$river['latest_news_river_is_paged']                    = true;
	$river['o_more_link_previous']['c_link']['c_link_url']  = $prev_post_link;
	$river['o_more_link_previous']['c_link']['c_link_text'] = __('Previous', 'pmc-variety');
} else {
	$river['o_more_link_previous']['c_link'] = false;
}

\PMC::render_template(
	sprintf('%s/template-parts/patterns/modules/latest-news-river.php', untrailingslashit(CHILD_THEME_PATH)),
	$river,
	true
);
