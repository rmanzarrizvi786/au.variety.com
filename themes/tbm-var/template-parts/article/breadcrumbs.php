<?php

/**
 * Single Article Breadcrumbs.
 *
 * Copied from Artnews theme.
 *
 * @package pmc-variety
 */

$items = \PMC\Core\Inc\Theme::get_instance()->get_breadcrumb();

if ( empty( $items ) || ! is_array( $items ) ) {
	return;
}

$variant = ! empty( $variant ) ? $variant : 'prototype';

$breadcrumbs = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/breadcrumbs.' . $variant );

$o_nav_list_item                          = $breadcrumbs['o_nav']['o_nav_list_items'][0];
$breadcrumbs['o_nav']['o_nav_list_items'] = [];

$o_nav_list_item['c_link_text'] = esc_html__( 'Home', 'pmc-variety' );
$o_nav_list_item['c_link_url']  = '/';

$breadcrumbs['o_nav']['o_nav_list_items'][] = $o_nav_list_item;

foreach ( $items as $item ) {

	$term_link = get_term_link( $item );

	if ( empty( $item->name ) || empty( $term_link ) || is_wp_error( $term_link ) ) {
		continue;
	}
	$o_nav_list_item['c_link_text']             = $item->name;
	$o_nav_list_item['c_link_url']              = $term_link;
	$breadcrumbs['o_nav']['o_nav_list_items'][] = $o_nav_list_item;

}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/breadcrumbs.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$breadcrumbs,
	true
);
