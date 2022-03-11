<?php

/**
 * Static Toaster Widget
 *
 * Intended to be in Homepage Top. Hidden below desktop.
 */

if (empty($carousel_data) || count($carousel_data) < 3) {
	return;
}

$static_toaster = PMC\Core\Inc\Larva::get_instance()->get_json('modules/static-toaster.prototype');

$static_toaster['o_tease_list']['o_tease_list_classes'] = 'lrv-a-unstyle-list lrv-a-grid lrv-a-cols3@tablet a-separator-spacing--r-050 a-separator-spacing--r-0@desktop-xl u-grid-gap-1 lrv-u-flex-grow-1';

$static_toaster['o_tease_list']['o_tease_list_item_classes'] .= ' lrv-u-border-r-1@desktop';

$template = $static_toaster['o_tease_list']['o_tease_list_items'][0];

$static_toaster['o_tease_list']['o_tease_list_items'] = [];

foreach ($carousel_data as $list_item) {
	$item = $template;

	$item['c_title']['c_title_text'] = \PMC::truncate($list_item['title'], 40, '', true);
	$item['c_title']['c_title_url']  = $list_item['url'];

	$excerpt = $list_item['excerpt'];

	if (empty($excerpt) && !empty($list_item['parent_ID'])) {
		$current_post = get_post($list_item['parent_ID']);
		if (!empty($current_post->post_excerpt)) {
			$excerpt = $current_post->post_excerpt;
		}
	}

	$item['c_tagline']['c_tagline_text'] = \PMC::truncate($excerpt, 60, '', true);

	$item['c_lazy_image']['c_lazy_image_link_url']           = $list_item['url'];
	$item['c_lazy_image']['c_lazy_image_placeholder_url']    = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$item['c_lazy_image']['c_lazy_image_src_url']            = $list_item['image'] ?? '';
	$item['c_lazy_image']['c_lazy_image_screen_reader_text'] = $list_item['image_alt'] ?? '';
	$item['c_lazy_image']['c_lazy_image_alt_attr']           = $list_item['image_alt'] ?? '';
	$item['c_lazy_image']['c_lazy_image_srcset_attr']        = false;
	$item['c_lazy_image']['c_lazy_image_sizes_attr']         = false;

	$static_toaster['o_tease_list']['o_tease_list_items'][] = $item;
}


$static_toaster['cxense_static_toaster_widget']['cxense_widget_classes'] .= ' lrv-u-padding-l-1';

\PMC::render_template(
	sprintf('%s/template-parts/patterns/modules/static-toaster.php', untrailingslashit(CHILD_THEME_PATH)),
	$static_toaster,
	true
);
