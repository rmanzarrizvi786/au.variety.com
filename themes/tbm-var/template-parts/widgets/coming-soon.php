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
$slider = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/awards-contenders.coming-soon' );

$slider_template            = $slider;
$slider_item_template_first = $slider['stories_slider']['stories_slider_items'][0];
$slider_item_template_rest  = $slider['stories_slider']['stories_slider_items'][1];

$slider_template['stories_slider']['stories_slider_items'] = [];

foreach ( $data['articles'] as $key => $story_data_item ) {

	// First slide does not require left margin so for that use different template.
	$slider_item_template = ( 0 === $key ) ? $slider_item_template_first : $slider_item_template_rest;

	$slider_item_template['c_title']      = $larva_populate->c_title( $story_data_item, $slider_item_template );
	$slider_item_template['c_lazy_image'] = $larva_populate->c_lazy_image( $story_data_item, $slider_item_template, [ 'image_size' => 'landscape-medium' ] );
	$watch_details                        = get_post_meta( $story_data_item->parent_id, 'variety_watch_link', true );

	$c_span_streamer_prototype = $slider_item_template['o_span_group']['o_span_group_items'][0];
	$c_span_date_prototype     = $slider_item_template['o_span_group']['o_span_group_items'][1];

	$slider_item_template['o_span_group']['o_span_group_items'] = [];

	if ( is_array( $watch_details ) && ! empty( $watch_details['variety_streamer'] ) ) {
		$c_span_streamer_prototype['c_span_text']                     = $watch_details['variety_streamer'];
		$slider_item_template['o_span_group']['o_span_group_items'][] = $c_span_streamer_prototype;
	}

	if ( is_array( $watch_details ) && ! empty( $watch_details['stream_date'] ) ) {
		$slider_item_template['external_link_url']['c_link_logo_url'] = get_permalink( $story_data_item->ID );

		$watch_date_timestamp = $watch_details['stream_date'] - ( (int) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
		$watch_date           = date_i18n( 'F j', $watch_date_timestamp );

		$c_span_date_prototype['c_span_text'] = sprintf( 'Streaming %1$s', $watch_date );

		$slider_item_template['o_span_group']['o_span_group_items'][] = $c_span_date_prototype;

		$slider_item_template['external_link_url']['data_title_attr']    = variety_get_card_title( $story_data_item );
		$slider_item_template['external_link_url']['data_location_attr'] = $watch_details['variety_streamer'] ? $watch_details['variety_streamer'] : '';
		$slider_item_template['external_link_url']['data_start_attr']    = gmdate( 'Y-m-j', $watch_date_timestamp );
	} else {
		$slider_item_template['external_link_url'] = false;
	}

	if ( empty( $slider_item_template['o_span_group']['o_span_group_items'] ) ) {
		$slider_item_template['o_span_group'] = false;
	}

	$slider_template['stories_slider']['stories_slider_items'][] = $slider_item_template;

	// Reset slider item template.
	$slider_item_template = $slider['stories_slider']['stories_slider_items'][1];

}

$slider_template['streamers_section_header']['c_heading']['c_heading_text'] = isset( $data['section_heading'] ) ? $data['section_heading'] : false;
$slider_template['streamers_section_header']['c_tagline']['c_tagline_text'] = isset( $data['section_logline'] ) ? $data['section_logline'] : false;

\PMC::render_template(
	sprintf( '%s/template-parts/editorial-hub/awards-contenders.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[ 'data' => $slider_template ],
	true
);
