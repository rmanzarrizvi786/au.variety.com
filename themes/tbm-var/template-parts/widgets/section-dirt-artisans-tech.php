<?php
/**
 * Section module.
 *
 * @package pmc-variety
 */

use PMC\Core\Inc\Larva;

$section = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/homepage-vip-tv-film.dirt' );

// Dirt.
$primary_template = $section['vip_curated']['o_tease_primary'];
$list_template    = $section['vip_curated']['o_tease_list']['o_tease_list_items'][0];

$section['vip_curated']['o_tease_primary']                    = [];
$section['vip_curated']['o_tease_list']['o_tease_list_items'] = [];

if ( ! empty( $data['articles']['dirt'] ) ) {
	$count = 1;

	foreach ( $data['articles']['dirt'] as $_post ) {
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

		// Dirt details.
		$location = get_post_meta( $_post->ID, 'dirt-meta_location', true );
		$price    = get_post_meta( $_post->ID, 'dirt-meta_price', true );

		if ( ! empty( $price ) ) {
			$item['o_span_group']['o_span_group_items'][0]['c_span_text'] = $price;
		} else {
			$item['o_span_group']['o_span_group_items'][0] = [];
		}

		if ( ! empty( $location ) ) {
			$item['o_span_group']['o_span_group_items'][1]['c_span_text'] = $location;
		} else {
			$item['o_span_group']['o_span_group_items'][1] = [];
		}

		if ( 1 === $count ) {
			$section['vip_curated']['o_tease_primary'] = $item;
		} else {
			$section['vip_curated']['o_tease_list']['o_tease_list_items'][] = $item;
		}

		$count ++;
	}
}

$section['vip_curated']['o_more_link']['c_link']['c_link_text'] = __( 'More Dirt', 'pmc-variety' );
$section['vip_curated']['o_more_link']['c_link']['c_link_url']  = get_term_link( 'dirt', 'vertical' );

if ( ! empty( $data['dirt_more_text'] ) ) {
	$section['vip_curated']['o_more_link']['c_link']['c_link_text'] = $data['dirt_more_text'];
}

if ( ! empty( $data['dirt_more_link'] ) ) {
	$section['vip_curated']['o_more_link']['c_link']['c_link_url'] = $data['dirt_more_link'];
}

// Artisans.
$section['homepage_vertical_list']['o_more_from_heading']['c_heading']['c_heading_text'] = __( 'Artisans', 'pmc-variety' );

$primary_template   = $section['homepage_vertical_list']['o_tease_list_primary']['o_tease_list_items'][0];
$secondary_template = $section['homepage_vertical_list']['o_tease_list_secondary']['o_tease_list_items'][0];

$section['homepage_vertical_list']['o_tease_list_primary']['o_tease_list_items']   = [];
$section['homepage_vertical_list']['o_tease_list_secondary']['o_tease_list_items'] = [];

if ( ! empty( $data['articles']['artisans'] ) ) {
	$count = 1;

	foreach ( $data['articles']['artisans'] as $_post ) {
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

$section['homepage_vertical_list']['o_more_link']['c_link']['c_link_text'] = __( 'More Artisans', 'pmc-variety' );
$section['homepage_vertical_list']['o_more_link']['c_link']['c_link_url']  = get_term_link( 'artisans', 'vertical' );

// Tech.
$section['homepage_vertical_list_horizontal']['o_more_from_heading']['c_heading']['c_heading_text'] = __( 'Tech', 'pmc-variety' );

$primary_template   = $section['homepage_vertical_list_horizontal']['o_tease_list_primary']['o_tease_list_items'][0];
$secondary_template = $section['homepage_vertical_list_horizontal']['o_tease_list_secondary']['o_tease_list_items'][0];
$tertiary_template  = $section['homepage_vertical_list_horizontal']['o_tease_list_secondary']['o_tease_list_items'][1];

$section['homepage_vertical_list_horizontal']['o_tease_list_primary']['o_tease_list_items']   = [];
$section['homepage_vertical_list_horizontal']['o_tease_list_secondary']['o_tease_list_items'] = [];

if ( ! empty( $data['articles']['tech'] ) ) {
	$count = 1;

	foreach ( $data['articles']['tech'] as $_post ) {
		if ( 1 === $count ) {
			$primary_template['c_span']      = false;
			$primary_template['c_link']      = false;
			$primary_template['c_timestamp'] = false;
			$populate                        = new \Variety\Inc\Populate( $_post, $primary_template );
		} elseif ( 2 === $count ) {
			$populate = new \Variety\Inc\Populate( $_post, $secondary_template );
		} else {
			$tertiary_template['c_span']       = false;
			$tertiary_template['c_link']       = false;
			$tertiary_template['c_timestamp']  = false;
			$tertiary_template['c_lazy_image'] = false;
			$populate                          = new \Variety\Inc\Populate( $_post, $tertiary_template );
		}

		$item = $populate->get();

		if ( 1 === $count ) {
			$section['homepage_vertical_list_horizontal']['o_tease_list_primary']['o_tease_list_items'][] = $item;
		} else {
			$section['homepage_vertical_list_horizontal']['o_tease_list_secondary']['o_tease_list_items'][] = $item;
		}

		$count ++;
	}
}

$section['homepage_vertical_list_horizontal']['o_more_link']['c_link']['c_link_text'] = __( 'More Tech', 'pmc-variety' );
$section['homepage_vertical_list_horizontal']['o_more_link']['c_link']['c_link_url']  = get_term_link( 'digital', 'vertical' );

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/homepage-vip-tv-film.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$section,
	true
);
