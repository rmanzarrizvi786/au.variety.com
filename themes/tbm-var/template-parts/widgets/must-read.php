<?php
/**
 * Must Read module.
 *
 * @package pmc-variety
 */

if ( empty( $data['articles'] ) ) {
	return;
}

$variant = is_single() ? 'prototype' : 'section-front';

$must_read = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/must-read-widget.' . $variant );

$primary_template   = $must_read['o_tease_list_primary']['o_tease_list_items'][0];
$secondary_template = $must_read['o_tease_list_secondary']['o_tease_list_items'][0];
$sponsor_template   = $must_read['o_tease_list_secondary']['o_tease_list_items'][3];

$must_read['o_tease_list_primary']['o_tease_list_items']   = [];
$must_read['o_tease_list_secondary']['o_tease_list_items'] = [];

$count = 1;

foreach ( $data['articles'] as $_post ) {
	if ( 1 === $count ) {
		$populate = new \Variety\Inc\Populate(
			$_post,
			$primary_template,
			[
				'image_size'           => 'landscape-small',
				'image_srcset_enabled' => false,
			]
		);
	} else {
		$populate = new \Variety\Inc\Populate(
			$_post,
			$secondary_template,
			[
				'image_size'           => 'variety-popular',
				'image_srcset_enabled' => false,
			]
		);
	}

	$item = $populate->get();

	if ( 1 === $count ) {
		$must_read['o_tease_list_primary']['o_tease_list_items'][] = $item;
	} else {
		$must_read['o_tease_list_secondary']['o_tease_list_items'][] = $item;
	}

	$count ++;
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/must-read-widget.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$must_read,
	true
);
