<?php

/**
 * Most Popular Widget.
 *
 * Used as a stand alone widget, and included in top-stories widget.
 *
 * @package pmc-variety
 */

if (empty($data['articles'])) {
	return;
}

$variant = is_home() ? 'homepage' : 'prototype';

$most_viewed = PMC\Core\Inc\Larva::get_instance()->get_json('modules/most-popular-sidebar.' . $variant);

// Popular posts.
$count    = !empty($data['popular_count']) ? (int) $data['popular_count'] : 8;
$template = $most_viewed['o_tease_list']['o_tease_list_items'][0];

$most_viewed['o_tease_list']['o_tease_list_items'] = [];

$i = 0;

foreach ($data['articles'] as $_post) {
	$populate = new \Variety\Inc\Populate(
		$_post['post_id'],
		$template,
		[
			'image_size'           => 'variety-popular',
			'image_srcset_enabled' => false,
		]
	);

	$item = $populate->get();

	$most_viewed['o_tease_list']['o_tease_list_items'][] = $item;

	if (2 === $i++) {
		$most_viewed['o_tease_list']['o_tease_list_items'][] = [
			'sponsored_most_popular_ad_action' => empty($data['sponsored_most_popular_ad_action']) ? 'sponsored-most-popular' : $data['sponsored_most_popular_ad_action'],
		];
	}
}

// Remove banner by default
$most_viewed['cxense_subscribe_widget'] = false;

if (PMC::is_mobile()) {

	// Add banner if is mobile homepage
	if (is_home()) {
		$most_viewed['cxense_subscribe_widget']['cxense_id_attr'] = 'cx-module-300x250-mobile';
	}

	if (is_single()) {
		$most_viewed['o_tease_list']['o_tease_list_items'][] = [
			'o_tease_primary_classes'          => 'lrv-u-padding-tb-1@mobile-max',
		];
	}
}


\PMC::render_template(
	sprintf('%s/template-parts/patterns/modules/most-popular-sidebar.php', untrailingslashit(CHILD_THEME_PATH)),
	$most_viewed,
	true
);
