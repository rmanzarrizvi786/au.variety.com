<?php
/**
 * Section module.
 *
 * @package pmc-variety
 */

$section = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/homepage-artisans-tech-exposure-politics.prototype' );

$fields = [
	'artisans' => [
		'column' => 'vertical_list_first',
		'text'   => __( 'Artisans', 'pmc-variety' ),
	],
	'tech'     => [
		'column' => 'vertical_list_second',
		'text'   => __( 'Tech', 'pmc-variety' ),
	],
	'exposure' => [
		'column' => 'vertical_list_third',
		'text'   => __( 'Exposure', 'pmc-variety' ),
	],
	'politics' => [
		'column' => 'vertical_list_fourth',
		'text'   => __( 'Politics', 'pmc-variety' ),
	],
];

foreach ( $fields as $key => $value ) {
	$column     = $value['column'];
	$title_text = $value['text'];

	$section['horizontal_row'][ $column ]['o_more_from_heading']['c_heading']['c_heading_text'] = $title_text;

	$primary_template = $section['horizontal_row'][ $column ]['o_tease_list_primary']['o_tease_list_items'][0];
	$list_template    = $section['horizontal_row'][ $column ]['o_tease_list_secondary']['o_tease_list_items'][0];

	$section['horizontal_row'][ $column ]['o_tease_list_primary']['o_tease_list_items']   = [];
	$section['horizontal_row'][ $column ]['o_tease_list_secondary']['o_tease_list_items'] = [];

	if ( ! empty( $data['articles'][ $key ] ) ) {
		$count = 1;

		foreach ( $data['articles'][ $key ] as $_post ) {
			if ( 1 === $count ) {
				$primary_template['c_span']      = false;
				$primary_template['c_link']      = false;
				$primary_template['c_timestamp'] = false;
				$populate                        = new \Variety\Inc\Populate( $_post, $primary_template );
			} else {
				$list_template['c_span']       = false;
				$list_template['c_link']       = false;
				$list_template['c_timestamp']  = false;
				$list_template['c_lazy_image'] = false;
				$populate                      = new \Variety\Inc\Populate( $_post, $list_template );
			}

			$item = $populate->get();

			if ( 1 === $count ) {
				$section['horizontal_row'][ $column ]['o_tease_list_primary']['o_tease_list_items'][] = $item;
			} else {
				$section['horizontal_row'][ $column ]['o_tease_list_secondary']['o_tease_list_items'][] = $item;
			}

			$count++;
		}
	}
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/homepage-artisans-tech-exposure-politics.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$section,
	true
);
