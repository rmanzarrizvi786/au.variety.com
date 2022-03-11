<?php

/**
 * Section module.
 *
 * @package pmc-variety
 */

$section = PMC\Core\Inc\Larva::get_instance()->get_json('modules/homepage-tv-film-music-theater.prototype');

$columns = [
	'tv'      => [
		'column'  => 'vertical_list_first',
		'display' => __('TV', 'pmc-variety'),
	],
	'film'    => [
		'column'  => 'vertical_list_second',
		'display' => __('Film', 'pmc-variety'),
	],
	'music'   => [
		'column'  => 'vertical_list_third',
		'display' => __('Music', 'pmc-variety'),
	],
	'digital' => [
		'column'  => 'vertical_list_fourth',
		'display' => __('Tech', 'pmc-variety'),
	],
	/* 'theater' => [
		'column'  => 'vertical_list_fourth',
		'display' => __( 'Theater', 'pmc-variety' ),
	], */
];

foreach ($columns as $key => $value) {
	$column = $value['column'];
	$text   = $value['display'];

	$section['horizontal_row_data'][$column]['o_more_from_heading']['c_heading']['c_heading_text'] = $text;

	$first_template     = $section['horizontal_row_data'][$column]['o_tease_list_primary']['o_tease_list_items'][0];
	$secondary_template = $section['horizontal_row_data'][$column]['o_tease_list_secondary']['o_tease_list_items'][0];

	$section['horizontal_row_data'][$column]['o_tease_list_primary']['o_tease_list_items']   = [];
	$section['horizontal_row_data'][$column]['o_tease_list_secondary']['o_tease_list_items'] = [];

	if (!empty($data['articles'][$key])) {
		$count = 1;

		foreach ($data['articles'][$key] as $_post) {
			if (1 === $count) {
				$first_template['c_span']      = false;
				$first_template['c_link']      = false;
				$first_template['c_timestamp'] = false;
				$populate                      = new \Variety\Inc\Populate($_post, $first_template);
			} else {
				$secondary_template['c_span']       = false;
				$secondary_template['c_link']       = false;
				$secondary_template['c_timestamp']  = false;
				$secondary_template['c_lazy_image'] = false;
				$populate                           = new \Variety\Inc\Populate($_post, $secondary_template);
			}

			$item = $populate->get();

			if (1 === $count) {
				$section['horizontal_row_data'][$column]['o_tease_list_primary']['o_tease_list_items'][] = $item;
			} else {
				$section['horizontal_row_data'][$column]['o_tease_list_secondary']['o_tease_list_items'][] = $item;
			}

			$count++;
		}
	}
}

\PMC::render_template(
	sprintf('%s/template-parts/patterns/modules/homepage-tv-film-music-theater.php', untrailingslashit(CHILD_THEME_PATH)),
	$section,
	true
);
