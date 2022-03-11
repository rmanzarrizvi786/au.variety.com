<?php
/**
 * What To Watch Widget.
 *
 * @package pmc-variety
 */

if ( empty( $data['articles'] ) ) {
	return;
}

// Helper to update values based on WP_Post objects.
$larva_populate = \Variety\Inc\Larva_Populate::get_instance();

// Get default module data.
$slider = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/awards-contenders.prototype' );

$slider_template            = $slider;
$slider_item_template_first = $slider['stories_slider']['stories_slider_items'][0];
$slider_item_template_rest  = $slider['stories_slider']['stories_slider_items'][1];

$slider_template['stories_slider']['stories_slider_items'] = [];

foreach ( $data['articles'] as $key => $story_data_item ) {

	// First slide does not required left margin so for that use different template.
	$slider_item_template = ( 0 === $key ) ? $slider_item_template_first : $slider_item_template_rest;

	$slider_item_template['c_title']      = $larva_populate->c_title( $story_data_item, $slider_item_template );
	$slider_item_template['c_lazy_image'] = $larva_populate->c_lazy_image( $story_data_item, $slider_item_template, [ 'image_size' => 'landscape-medium' ] );
	$watch_link                           = get_post_meta( $story_data_item->parent_id, 'variety_watch_link', true );

	if ( is_array( $watch_link ) && ! empty( $watch_link['variety_watch_url'] ) ) {
		$slider_item_template['external_link_url']['c_link_logo_url']         = $watch_link['variety_watch_url'];
		$slider_item_template['external_link_url']['c_link_logo_target_attr'] = '_blank';
	} else {
		$slider_item_template['external_link_url'] = false;
	}
	if ( is_array( $watch_link ) && ! empty( $watch_link['variety_streamer'] ) ) {
		$slider_item_template['c_span']['c_span_text'] = $watch_link['variety_streamer'];
	} else {
		$slider_item_template['c_span'] = false;
	}

	$slider_template['stories_slider']['stories_slider_items'][] = $slider_item_template;
}

$slider_template['streamers_section_header']['c_heading']['c_heading_text'] = isset( $data['awards_heading'] ) ? $data['awards_heading'] : false;
$slider_template['streamers_section_header']['c_tagline']['c_tagline_text'] = isset( $data['awards_logline'] ) ? $data['awards_logline'] : false;

\PMC::render_template(
	sprintf( '%s/template-parts/editorial-hub/awards-contenders.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[ 'data' => $slider_template ],
	true
);
