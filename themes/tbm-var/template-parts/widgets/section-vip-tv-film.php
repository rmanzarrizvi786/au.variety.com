<?php
/**
 * Section module.
 *
 * @package pmc-variety
 */

$section = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/homepage-vip-tv-film.prototype' );

// VIP.
$primary_template = $section['vip_curated']['o_tease_primary'];
$list_template    = $section['vip_curated']['o_tease_list']['o_tease_list_items'][0];

$section['vip_curated']['o_tease_primary']                    = [];
$section['vip_curated']['o_tease_list']['o_tease_list_items'] = [];

if ( ! empty( $data['articles']['vip'] ) ) {
	$count = 1;

	foreach ( $data['articles']['vip'] as $_post ) {
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

$section['vip_curated']['o_more_link']['c_link']['c_link_text'] = __( 'More VIP', 'pmc-variety' );
$section['vip_curated']['o_more_link']['c_link']['c_link_url']  = \Variety\Plugins\Variety_VIP\VIP::vip_url();

if ( ! empty( $data['vip_more_text'] ) ) {
	$section['vip_curated']['o_more_link']['c_link']['c_link_text'] = $data['vip_more_text'];
}

if ( ! empty( $data['vip_more_link'] ) ) {
	$section['vip_curated']['o_more_link']['c_link']['c_link_url'] = $data['vip_more_link'];
}

// TV.
$section['homepage_vertical_list']['o_more_from_heading']['c_heading']['c_heading_text'] = __( 'TV', 'pmc-variety' );

$primary_template   = $section['homepage_vertical_list']['o_tease_list_primary']['o_tease_list_items'][0];
$secondary_template = $section['homepage_vertical_list']['o_tease_list_secondary']['o_tease_list_items'][0];

$section['homepage_vertical_list']['o_tease_list_primary']['o_tease_list_items']   = [];
$section['homepage_vertical_list']['o_tease_list_secondary']['o_tease_list_items'] = [];

$tv_vertical = get_term_by( 'slug', 'tv', 'vertical' );
if ( ! is_wp_error( $tv_vertical ) && ! empty( $tv_vertical->term_id ) ) {
	$data['articles']['tv'] = \PMC\Core\Inc\Helper::get_term_posts_cache( $tv_vertical->term_id, 'vertical', 5 );
}

if ( ! empty( $data['articles']['tv'] ) ) {
	$count = 1;

	foreach ( $data['articles']['tv'] as $_post ) {
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

$section['homepage_vertical_list']['o_more_link']['c_link']['c_link_text'] = __( 'More TV', 'pmc-variety' );
$section['homepage_vertical_list']['o_more_link']['c_link']['c_link_url']  = get_term_link( 'tv', 'vertical' );

// Film.
$section['homepage_vertical_list_horizontal']['o_more_from_heading']['c_heading']['c_heading_text'] = __( 'Film', 'pmc-variety' );

$primary_template   = $section['homepage_vertical_list_horizontal']['o_tease_list_primary']['o_tease_list_items'][0];
$secondary_template = $section['homepage_vertical_list_horizontal']['o_tease_list_secondary']['o_tease_list_items'][0];
$tertiary_template  = $section['homepage_vertical_list_horizontal']['o_tease_list_secondary']['o_tease_list_items'][1];

$section['homepage_vertical_list_horizontal']['o_tease_list_primary']['o_tease_list_items']   = [];
$section['homepage_vertical_list_horizontal']['o_tease_list_secondary']['o_tease_list_items'] = [];

$film_vertical = get_term_by( 'slug', 'film', 'vertical' );
if ( ! is_wp_error( $film_vertical ) && ! empty( $film_vertical->term_id ) ) {
	$data['articles']['film'] = \PMC\Core\Inc\Helper::get_term_posts_cache( $film_vertical->term_id, 'vertical', 5 );
}

if ( ! empty( $data['articles']['film'] ) ) {
	$count = 1;

	foreach ( $data['articles']['film'] as $_post ) {
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

$section['homepage_vertical_list_horizontal']['o_more_link']['c_link']['c_link_text'] = __( 'More Film', 'pmc-variety' );
$section['homepage_vertical_list_horizontal']['o_more_link']['c_link']['c_link_url']  = get_term_link( 'film', 'vertical' );

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/homepage-vip-tv-film.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$section,
	true
);
