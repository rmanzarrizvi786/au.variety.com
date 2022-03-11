<?php
/**
 * Section module.
 *
 * @package pmc-variety
 */

$section = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/homepage-music-theater-awards.prototype' );

// Music.
$primary_template   = $section['music_vertical_list']['o_tease_list_primary']['o_tease_list_items'][0];
$secondary_template = $section['music_vertical_list']['o_tease_list_secondary']['o_tease_list_items'][0];

$section['music_vertical_list']['o_tease_list_primary']['o_tease_list_items']   = [];
$section['music_vertical_list']['o_tease_list_secondary']['o_tease_list_items'] = [];

if ( ! empty( $data['articles']['music'] ) ) {
	$count = 1;

	foreach ( $data['articles']['music'] as $_post ) {
		if ( $count <= 2 ) {
			$primary_template['c_span']      = false;
			$primary_template['c_link']      = false;
			$primary_template['c_timestamp'] = false;
			$populate                        = new \Variety\Inc\Populate( $_post, $primary_template );
		} else {
			$secondary_template['c_span']       = false;
			$secondary_template['c_link']       = false;
			$secondary_template['c_timestamp']  = false;
			$secondary_template['c_lazy_image'] = false;
			$populate                           = new \Variety\Inc\Populate( $_post, $secondary_template );
		}

		$item = $populate->get();

		if ( $count <= 2 ) {
			$section['music_vertical_list']['o_tease_list_primary']['o_tease_list_items'][] = $item;
		} else {
			$section['music_vertical_list']['o_tease_list_secondary']['o_tease_list_items'][] = $item;
		}

		$count ++;
	}
}

$section['music_vertical_list']['o_more_link']['c_link']['c_link_text'] = __( 'More Music', 'pmc-variety' );
$section['music_vertical_list']['o_more_link']['c_link']['c_link_url']  = get_term_link( 'music', 'vertical' );

// Theater.
$primary_template   = $section['theater_vertical_list']['o_tease_list_primary']['o_tease_list_items'][0];
$secondary_template = $section['theater_vertical_list']['o_tease_list_secondary']['o_tease_list_items'][0];

$section['theater_vertical_list']['o_tease_list_primary']['o_tease_list_items']   = [];
$section['theater_vertical_list']['o_tease_list_secondary']['o_tease_list_items'] = [];

if ( ! empty( $data['articles']['legit'] ) ) {
	$count = 1;

	foreach ( $data['articles']['legit'] as $_post ) {
		if ( $count <= 2 ) {
			$primary_template['c_span']      = false;
			$primary_template['c_link']      = false;
			$primary_template['c_timestamp'] = false;
			$populate                        = new \Variety\Inc\Populate( $_post, $primary_template );
		} else {
			$secondary_template['c_span']       = false;
			$secondary_template['c_link']       = false;
			$secondary_template['c_timestamp']  = false;
			$secondary_template['c_lazy_image'] = false;
			$populate                           = new \Variety\Inc\Populate( $_post, $secondary_template );
		}

		$item = $populate->get();

		if ( $count <= 2 ) {
			$section['theater_vertical_list']['o_tease_list_primary']['o_tease_list_items'][] = $item;
		} else {
			$section['theater_vertical_list']['o_tease_list_secondary']['o_tease_list_items'][] = $item;
		}

		$count ++;
	}
}

$section['theater_vertical_list']['o_more_link']['c_link']['c_link_text'] = __( 'More Theater', 'pmc-variety' );
$section['theater_vertical_list']['o_more_link']['c_link']['c_link_url']  = get_term_link( 'legit', 'vertical' );

// Awards.
$primary_template = $section['awards_curated']['o_tease_primary'];
$list_template    = $section['awards_curated']['o_tease_list']['o_tease_list_items'][0];

$section['awards_curated']['o_tease_primary']                    = [];
$section['awards_curated']['o_tease_list']['o_tease_list_items'] = [];

if ( ! empty( $data['articles']['awards'] ) ) {
	$count = 1;

	foreach ( $data['articles']['awards'] as $_post ) {
		if ( 1 === $count ) {
			$primary_template['c_span']      = false;
			$primary_template['c_link']      = false;
			$primary_template['c_timestamp'] = false;
			$populate                        = new \Variety\Inc\Populate( $_post, $primary_template );
		} else {
			$list_template['c_span']      = false;
			$list_template['c_link']      = false;
			$list_template['c_timestamp'] = false;
			$populate                     = new \Variety\Inc\Populate( $_post, $list_template );
		}

		$item = $populate->get();

		if ( 1 === $count ) {
			$section['awards_curated']['o_tease_primary'] = $item;
		} else {
			$section['awards_curated']['o_tease_list']['o_tease_list_items'][] = $item;
		}

		$count ++;
	}
}

$section['awards_curated']['o_more_link']['c_link']['c_link_text'] = __( 'More Awards', 'pmc-variety' );
$section['awards_curated']['o_more_link']['c_link']['c_link_url']  = '/e/contenders/';

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/homepage-music-theater-awards.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$section,
	true
);
