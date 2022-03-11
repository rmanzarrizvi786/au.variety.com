<?php
/**
 * Section module.
 *
 * @package pmc-variety
 */

$section = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/homepage-vip-tv-film.exposure' );

// Exposure.
$section['vip_curated']['o_more_from_heading']['c_heading']['c_heading_text'] = __( 'Events & Parties', 'pmc-variety' );
$primary_template = $section['vip_curated']['o_tease_primary'];
$list_template    = $section['vip_curated']['o_tease_list']['o_tease_list_items'][0];

$section['vip_curated']['o_tease_primary']                    = [];
$section['vip_curated']['o_tease_list']['o_tease_list_items'] = [];

if ( ! empty( $data['articles']['exposure'] ) ) {
	$count = 1;

	foreach ( $data['articles']['exposure'] as $_post ) {
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
			$section['vip_curated']['o_tease_primary'] = $item;
		} else {
			$section['vip_curated']['o_tease_list']['o_tease_list_items'][] = $item;
		}

		$count ++;
	}
}

$section['vip_curated']['o_more_link']['c_link']['c_link_text'] = __( 'More Events & Parties', 'pmc-variety' );
$section['vip_curated']['o_more_link']['c_link']['c_link_url']  = get_term_link( 'scene', 'vertical' );

// Streaming.
$section['homepage_vertical_list']['o_more_from_heading']['c_heading']['c_heading_text'] = __( 'Global', 'pmc-variety' );

$primary_template   = $section['homepage_vertical_list']['o_tease_list_primary']['o_tease_list_items'][0];
$secondary_template = $section['homepage_vertical_list']['o_tease_list_secondary']['o_tease_list_items'][0];

$section['homepage_vertical_list']['o_tease_list_primary']['o_tease_list_items']   = [];
$section['homepage_vertical_list']['o_tease_list_secondary']['o_tease_list_items'] = [];

if ( ! empty( $data['articles']['global'] ) ) {
	$count = 1;

	foreach ( $data['articles']['global'] as $_post ) {
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
			$section['homepage_vertical_list']['o_tease_list_primary']['o_tease_list_items'][] = $item;
		} else {
			$section['homepage_vertical_list']['o_tease_list_secondary']['o_tease_list_items'][] = $item;
		}

		$count ++;
	}
}

$section['homepage_vertical_list']['o_more_link']['c_link']['c_link_text'] = __( 'More Global', 'pmc-variety' );
$section['homepage_vertical_list']['o_more_link']['c_link']['c_link_url']  = get_term_link( 'global', 'category' );

// Politics.
$section['homepage_vertical_list_horizontal']['o_more_from_heading']['c_heading']['c_heading_text'] = __( 'Politics', 'pmc-variety' );

$primary_template   = $section['homepage_vertical_list_horizontal']['o_tease_list_primary']['o_tease_list_items'][0];
$secondary_template = $section['homepage_vertical_list_horizontal']['o_tease_list_secondary']['o_tease_list_items'][0];
$tertiary_template  = $section['homepage_vertical_list_horizontal']['o_tease_list_secondary']['o_tease_list_items'][1];

$section['homepage_vertical_list_horizontal']['o_tease_list_primary']['o_tease_list_items']   = [];
$section['homepage_vertical_list_horizontal']['o_tease_list_secondary']['o_tease_list_items'] = [];

if ( ! empty( $data['articles']['politics'] ) ) {
	$count = 1;

	foreach ( $data['articles']['politics'] as $_post ) {
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
			$section['homepage_vertical_list_horizontal']['o_tease_list_primary']['o_tease_list_items'][] = $item;
		} else {
			$section['homepage_vertical_list_horizontal']['o_tease_list_secondary']['o_tease_list_items'][] = $item;
		}

		$count ++;
	}
}

$section['homepage_vertical_list_horizontal']['o_more_link']['c_link']['c_link_text'] = __( 'More Politics', 'pmc-variety' );
$section['homepage_vertical_list_horizontal']['o_more_link']['c_link']['c_link_url']  = get_term_link( 'politics', 'vertical' );

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/homepage-vip-tv-film.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$section,
	true
);
