<?php

/**
 * Special Coverage module.
 *
 * @package pmc-variety
 */

use Variety\Plugins\Variety_VIP\Content;

if (empty($data['articles'])) {
	return;
}

$special = PMC\Core\Inc\Larva::get_instance()->get_json('modules/homepage-vertical-list.special');

// Title.
$special['o_more_from_heading']['c_heading']['c_heading_text'] = $data['title'];

$primary_template_1 = $special['o_tease_list_primary']['o_tease_list_items'][0];
$primary_template_2 = $special['o_tease_list_primary']['o_tease_list_items'][1];
$secondary_template = $special['o_tease_list_secondary']['o_tease_list_items'][0];

$special['o_tease_list_primary']['o_tease_list_items']   = [];
$special['o_tease_list_secondary']['o_tease_list_items'] = [];

$count = 1;

foreach ($data['articles'] as $_post) {
	if (1 === $count) {
		$populate = new \Variety\Inc\Populate($_post, $primary_template_1);
	} elseif (2 === $count) {
		$populate = new \Variety\Inc\Populate($_post, $primary_template_2);
	} else {
		$populate = new \Variety\Inc\Populate($_post, $secondary_template);
	}

	$item = $populate->get();

	if ($count <= 2) {
		$special['o_tease_list_primary']['o_tease_list_items'][] = $item;
	} else {
		$special['o_tease_list_secondary']['o_tease_list_items'][] = $item;
	}

	$count++;
}

// More link.
$special['o_more_link']['c_link']['c_link_text'] = $data['more_text'];
$special['o_more_link']['c_link']['c_link_url']  = $data['more_link'];

\PMC::render_template(
	sprintf('%s/template-parts/patterns/modules/homepage-vertical-list.php', untrailingslashit(CHILD_THEME_PATH)),
	$special,
	true
);
