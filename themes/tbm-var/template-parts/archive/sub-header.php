<?php
/**
 * Sub Header
 *
 * Used on Section Front, contains a term name and a dropdown menu.
 */

if ( ! is_array( $menu_items ) || empty( $menu_items['root'] ) ) {
	return;
}
$current_term = get_queried_object();

$sub_header = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/sub-header.prototype' );

$sub_header_nav_item_prototype = $sub_header['o_nav']['o_nav_list_items'][0] ?? [];

$sub_header['c_heading']['c_heading_text'] = $current_term->name;

$term_link = get_term_link( $current_term );

$sub_menu_array = [];

$sub_menu_all_link = $sub_header_nav_item_prototype;

$sub_menu_all_link['c_link_text'] = esc_html__( 'All', 'pmc-variety' );
$sub_menu_all_link['c_link_url']  = ( ( is_wp_error( $term_link ) ) ? '#' : $term_link );

$sub_menu_array[] = $sub_menu_all_link;

foreach ( $menu_items['root'] as $menu_item ) {
	$sub_menu_nav_item = $sub_header_nav_item_prototype;

	$sub_menu_nav_item['c_link_text'] = $menu_item['c_nav_link_text'];
	$sub_menu_nav_item['c_link_url']  = $menu_item['c_nav_link_url'];

	$sub_menu_array[] = $sub_menu_nav_item;
}

$sub_header['o_nav']['o_nav_list_items'] = $sub_menu_array;

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/sub-header.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$sub_header,
	true
);
