<?php

/**
 * Top Stories Grid for Section Front
 */

use PMC\Core\Inc\Carousels;

if ($is_paged) {
	return;
}

if (!is_array($menu_items) || empty($menu_items['root'])) {
	return;
}

$larva_populate = \Variety\Inc\Larva_Populate::get_instance();

$current_term = get_queried_object();

$featured_story_data = PMC\Core\Inc\Carousels::get_instance()->get_posts(
	$current_term->slug,
	5,
	'landscape-large',
	$current_term->taxonomy,
	false,
	false
);


if (!is_array($featured_story_data) || empty($featured_story_data) || 5 > count($featured_story_data)) {
	return;
}

$top_stories = PMC\Core\Inc\Larva::get_instance()->get_json('modules/top-stories.section-front');

$json_keys = [
	0 => 'o_story_first',
	1 => 'o_story_second',
	2 => 'o_story_third',
	3 => 'o_story_fourth',
	4 => 'o_story_fifth',
];

$i = 0;
foreach ($featured_story_data as $story_data_item) {

	$featured_story_item = $top_stories[$json_keys[$i]];

	$featured_story_item['c_title']['c_title_url']    = $story_data_item['url'];
	$featured_story_item['c_title']['c_title_markup'] = $story_data_item['title'];

	$featured_story_item['c_dek'] = false;

	$featured_story_item['c_lazy_image']['c_lazy_image_permalink_url']      = $story_data_item['url'];
	$featured_story_item['c_lazy_image']['c_lazy_image_placeholder_url']    = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$featured_story_item['c_lazy_image']['c_lazy_image_src_url']            = $story_data_item['image'] ?? '';
	$featured_story_item['c_lazy_image']['c_lazy_image_screen_reader_text'] = $story_data_item['image_alt'] ?? '';
	$featured_story_item['c_lazy_image']['c_lazy_image_alt_attr']           = $story_data_item['image_alt'] ?? '';
	$featured_story_item['c_lazy_image']['c_lazy_image_srcset_attr']        = \wp_get_attachment_image_srcset($story_data_item['image_id']) ?? false;
	$featured_story_item['c_lazy_image']['c_lazy_image_sizes_attr']         = \wp_get_attachment_image_sizes($story_data_item['image_id']) ?? false;

	$primary_category = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy(
		$story_data_item['ID'],
		'category'
	);

	$subcategory = get_post_meta($story_data_item['ID'], 'subcategories', true);

	if (empty($subcategory)) {
		$post_categories = get_the_category($story_data_item['ID']);
		$subcategory     = $post_categories[1]->term_id ?? '';
	}

	if (false !== $primary_category) {
		$featured_story_item['c_span']['c_span_url']  = get_term_link($primary_category->term_id);
		$featured_story_item['c_span']['c_span_text'] = $primary_category->name;
	} else {
		$featured_story_item['c_span'] = false;
	}

	$author = \PMC\Core\Inc\Author::get_instance()->authors_data($story_data_item['ID']);

	if (!empty($author['byline'])) {
		$featured_story_item['c_link']['c_link_text'] = wp_strip_all_tags(sprintf('By %1$s', $author['byline']));

		if (!empty($author['single_author'])) {
			$featured_story_item['c_link']['c_link_url'] = get_author_posts_url(
				$author['single_author']['author']->ID,
				$author['single_author']['author']->user_nicename
			);
		}
	} else {
		$featured_story_item['c_link']['c_link_text'] = '';
		$featured_story_item['c_link']['c_link_url']  = '';
	}

	$featured_story_item['c_timestamp']['c_timestamp_text'] = variety_human_time_diff($story_data_item['ID']);


	// TODO: Add this to Larva_Populate
	$featured_story_item['video_permalink_url'] = false;

	if (PMC_Featured_Video_Override::get_instance()->has_featured_video($story_data_item['ID']) || \Variety_Top_Videos::POST_TYPE_NAME === get_post_type($story_data_item['ID'])) {
		$featured_story_item['video_permalink_url'] = get_permalink($story_data_item['ID']);
	}

	$top_stories[$json_keys[$i]] = $featured_story_item;
	$i++;
}

\PMC::render_template(
	sprintf(
		'%s/template-parts/patterns/modules/top-stories.php',
		untrailingslashit(CHILD_THEME_PATH)
	),
	$top_stories,
	true
);
