<?php

/**
 * Latest News River
 *
 * Used on Homepage, Homepage 2+ and on Archives.
 *
 * @package pmc-variety
 */

$river = PMC\Core\Inc\Larva::get_instance()->get_json('modules/latest-news-river.homepage');

$template = $river['o_tease_news_list_primary']['o_tease_list_items'][0];

$river['o_tease_news_list_primary']['o_tease_list_items']   = [];
$river['o_tease_news_list_secondary']['o_tease_list_items'] = [];

if (is_post_type_archive('vy-thought-leaders')) {
	$river['o_more_from_heading']['c_heading']['c_heading_text'] = __('Thought Leaders', 'pmc-variety');
} elseif (is_post_type_archive('hollywood_exec')) {
	$river['o_more_from_heading']['c_heading']['c_heading_text'] = __('Industry Executives', 'pmc-variety');
} elseif (true === is_paged()) {
	$river['o_more_from_heading']['c_heading']['c_heading_text'] = __('More News', 'pmc-variety');
} elseif (is_archive()) {
	$current_term = get_queried_object();

	if (!empty($current_term->labels->archives)) {
		$river['o_more_from_heading']['c_heading']['c_heading_text'] = $current_term->labels->archives;
	}
}

if (is_archive()) {
	$river['o_more_from_heading']['c_heading']['c_heading_is_primary_heading'] = true;
}

$count = 1;

if (is_home()) {
	$item_ad = [
		'o_tease_primary_classes'            => $template['o_tease_primary_classes'],
		'o_tease_classes'                    => $template['o_tease_classes'],
		'sponsored_homepage_river_ad_action' => 'sponsored-homepage-river',
	];
}

if (have_posts()) {
	while (have_posts()) {
		the_post();
		$the_permalink = get_permalink();

		if (is_post_type_archive('hollywood_exec')) {
			$executive = \Variety\Inc\Executive::get_instance();
			$executive->set_executive(get_the_ID());
		} elseif (is_post_type_archive('vy-thought-leaders')) {
			$thought_leader                   = \Variety\Inc\Thought_Leaders::get_instance();
			$the_permalink                    = $thought_leader->get_meta('_tl_pdf', get_the_ID());
			$thought_leader__publication_date = $thought_leader->get_meta('_publication_date', get_the_ID());
		}

		$item = $template;

		// Title.
		$item['c_title']['c_title_url'] = $the_permalink;

		if (isset($executive) && $executive instanceof Variety\Inc\Executive) {
			$job_title_and_company = implode(
				', ',
				array_filter( // removes blank array elements
					[
						$executive->get_job_title(),
						$executive->get_company_name(),
					]
				)
			);

			// TODO: These classes break parity with those in the pattern library and should be moved to a variant in the Larva latest-news-river module
			$item['c_title']['c_title_classes']        = 'lrv-u-font-size-16';
			$item['c_title']['c_title_text']           = $job_title_and_company;
			$item['c_title']['c_title_before_classes'] = 'lrv-u-display-block lrv-u-font-size-20';
			$item['c_title']['c_title_before_text']    = variety_get_card_title();
		} else {
			$item['c_title']['c_title_text'] = variety_get_card_title();
		}


		// Featured Image/Video.
		$image = \PMC\Core\Inc\Media::get_instance()->get_image_data(get_post_thumbnail_id(), 'landscape-large');

		if (!empty($image['src'])) {
			$item['c_lazy_image']['c_lazy_image_link_url']        = $the_permalink;
			$item['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
			$item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
			$item['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset(get_post_thumbnail_id());
			$item['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes(get_post_thumbnail_id());
			$item['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
			$item['c_figcaption']['c_figcaption_caption_markup']  = $image['image_caption'];
			$item['c_figcaption']['c_figcaption_credit_text']     = $image['image_credit'];
		} else {
			$item['c_lazy_image'] = [];
		}

		$item['is_video'] = false;

		if (PMC_Featured_Video_Override::get_instance()->has_featured_video(get_the_ID()) || \Variety_Top_Videos::POST_TYPE_NAME === get_post_type(get_the_ID())) {
			$item['is_video']            = true;
			$item['video_permalink_url'] = $the_permalink;
		}

		if (isset($executive)) {
			// Disable these as they are above the c_title
			// abusing c_title (above) to get job title/company name AFTER the c_title
			$item['o_taxonomy_item']['c_span']['c_span_text'] = false;
			$item['o_taxonomy_item']['c_span']['c_span_url']  = false;
			$item['c_timestamp']['c_timestamp_text']          = false;
		} elseif (is_post_type_archive('vy-thought-leaders')) {
			$item['o_tease_meta_classes']                     = '';
			$item['o_taxonomy_item']['c_span']['c_span_text'] = false;
			$item['o_taxonomy_item']['c_span']['c_span_url']  = false;
			$item['c_timestamp']['c_timestamp_text']          = $thought_leader__publication_date;
		} else {
			// Vertical.
			$vertical = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy(get_the_ID(), 'vertical');

			if (!empty($vertical)) {
				$item['o_taxonomy_item']['c_span']['c_span_text'] = $vertical->name;
				$item['o_taxonomy_item']['c_span']['c_span_url']  = get_term_link($vertical);
			}

			if ('VIP' === $item['o_taxonomy_item']['c_span']['c_span_text']) {
				$item['o_taxonomy_item']['c_span']['c_span_text'] = __('VIP+', 'pmc-variety');
			}

			if ('variety_vip_post' === get_post_type()) {
				$item['o_taxonomy_item']['c_span']['c_span_text']         = __('VIP+', 'pmc-variety');
				$item['o_taxonomy_item']['c_span']['c_span_url']          = \Variety\Plugins\Variety_VIP\VIP::vip_url();
				$item['o_taxonomy_item']['c_span']['c_span_link_classes'] = str_replace('u-color-pale-sky-2', 'u-color-brand-vip-primary', $item['o_taxonomy_item']['c_span']['c_span_link_classes']);
			}

			// Time.
			$item['c_timestamp']['c_timestamp_text'] = variety_human_time_diff(get_the_ID());
		}

		if (3 === $count && !empty($item_ad)) {
			$river['o_tease_news_list_primary']['o_tease_list_items'][] = $item_ad;
		}

		if ($count <= 4) {
			$river['o_tease_news_list_primary']['o_tease_list_items'][] = $item;
		} else {
			$river['o_tease_news_list_secondary']['o_tease_list_items'][] = $item;
		}

		$count++;
	}
}

// Subscribe.
$river['cxense_subscribe_module']['cxense_id_attr'] = 'cx-module-mid-river';

// Previous / Next.
$next_post_link = get_next_posts_link() ? next_posts(0, false) : false;
$prev_post_link = get_previous_posts_link() ? previous_posts(false) : false;

if (!empty($next_post_link)) {
	if (is_archive('hollywood_exec')) {
		$river['o_more_link']['c_link']['c_link_text'] = __('Next', 'pmc-variety');
	} else {
		$river['o_more_link']['c_link']['c_link_text'] = __('More News', 'pmc-variety');
	}
	$river['o_more_link']['c_link']['c_link_url'] = $next_post_link;
} else {
	// Hide link if there are no next posts
	$river['o_more_link']['c_link'] = false;
}

// Previous.
if (!empty($prev_post_link)) {
	$river['latest_news_river_is_paged']                    = true;
	$river['o_more_link_previous']['c_link']['c_link_url']  = $prev_post_link;
	$river['o_more_link_previous']['c_link']['c_link_text'] = __('Previous', 'pmc-variety');
} else {
	// Hide link if there are no next posts
	$river['o_more_link_previous']['c_link'] = false;
}

/* if (\PMC::is_mobile()) {
	$river['o_tease_news_list_secondary']['o_tease_list_items'][] = ['sponsored_homepage_river_ad_action' => 'sponsored-homepage-river'];
} */

$river = apply_filters('variety_river', $river, $template);

\PMC::render_template(
	sprintf(
		'%s/template-parts/patterns/modules/latest-news-river.php',
		untrailingslashit(CHILD_THEME_PATH)
	),
	$river,
	true
);
