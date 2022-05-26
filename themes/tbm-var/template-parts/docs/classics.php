<?php

/**
 * Classica Template.
 *
 * @package pmc-variety
 */

// Get default docs stories row data.
$classics = PMC\Core\Inc\Larva::get_instance()->get_json('modules/docs-classics.prototype');

// Get global curation settings.
// $settings = get_option( 'global_curation', [] );
// $settings = $settings['tab_variety_documentaries'];

$classics['c_heading']['c_heading_text'] = !empty($settings['variety_classics_heading_text']) ? $settings['variety_classics_heading_text'] : $classics['c_heading']['c_heading_text'];

$classics_data = \Variety\Inc\Carousels::get_carousel_posts(
	'vy-docs-classics',
	4,
);

if (empty($classics_data) || count($classics_data) < 4) {
	return;
}

$o_card_prototype   = $classics['classics_row_items'][0];
$classics_row_items = [];

foreach ($classics_data as $key => $classic) {
	// Otherwise, set to bottom row card.
	$o_card = $o_card_prototype;

	$o_card['c_title']['c_title_text'] = $classic->post_title;
	$o_card['c_title']['c_title_url']  = isset($classic->url) ? $classic->url : get_the_permalink($classic);

	$o_card['c_dek']['c_dek_text'] = isset($classic->custom_excerpt) ? $classic->custom_excerpt : wp_strip_all_tags(\PMC\Core\Inc\Helper::get_the_excerpt($classic->ID));

	$image_id = isset($classic->image_id) ? $classic->image_id : get_post_thumbnail_id($classic->ID);

	$o_card['c_lazy_image']['c_lazy_image_link_url']           = isset($classic->url) ? $classic->url : get_the_permalink($classic);
	$o_card['c_lazy_image']['c_lazy_image_placeholder_url']    = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$o_card['c_lazy_image']['c_lazy_image_src_url']            = \wp_get_attachment_image_url($image_id, 'landscape-xlarge');
	$o_card['c_lazy_image']['c_lazy_image_screen_reader_text'] = get_post_meta($image_id, '_wp_attachment_image_alt', true);
	$o_card['c_lazy_image']['c_lazy_image_alt_attr']           = get_post_meta($image_id, '_wp_attachment_image_alt', true);
	$o_card['c_lazy_image']['c_lazy_image_srcset_attr']        = false;
	$o_card['c_lazy_image']['c_lazy_image_sizes_attr']         = false;

	array_push($classics_row_items, $o_card);
}

$classics['classics_row_items'] = $classics_row_items;

\PMC::render_template(
	sprintf('%s/template-parts/patterns/modules/docs-classics.php', untrailingslashit(CHILD_THEME_PATH)),
	$classics,
	true
);
