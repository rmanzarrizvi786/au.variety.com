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
$slider = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/stories-slider.prototype' );

$slider_template            = $slider;
$slider_item_template_first = $slider['stories_slider_items'][0];
$slider_item_template       = $slider['stories_slider_items'][1];

$slider_template['stories_slider_items'] = [];

foreach ( $data['articles'] as $key => $story_data_item ) {

	if ( 0 === $key ) {
		$slider_item_template = $slider_item_template_first;
	}

	$new_arrival_badge_prototype = $slider_item_template['o_span_group']['o_span_group_items'][0];
	$critic_pick_badge_prototype = $slider_item_template['o_span_group']['o_span_group_items'][1];

	$slider_item_template['o_span_group']['o_span_group_items'] = [];

	$slider_item_template['c_title']      = $larva_populate->c_title( $story_data_item, $slider_item_template );
	$slider_item_template['c_lazy_image'] = $larva_populate->c_lazy_image( $story_data_item, $slider_item_template, [ 'image_size' => 'landscape-medium' ] );
	$watch_link                           = get_post_meta( $story_data_item->parent_id, 'variety_watch_link', true );

	if ( is_array( $watch_link ) && ! empty( $watch_link['variety_watch_url'] ) ) {
		$slider_item_template['external_link_url']['c_link_logo_url']         = $watch_link['variety_watch_url'];
		$slider_item_template['external_link_url']['c_link_logo_target_attr'] = '_blank';
	} else {
		$slider_item_template['external_link_url'] = false;
	}

	if ( is_array( $watch_link ) && ! empty( $watch_link['new_arrival'] ) ) {
		$slider_item_template['o_span_group']['o_span_group_items'][] = $new_arrival_badge_prototype;
	}

	// Check if Critic's Pick badge should be applied.
	$badge = \Variety\Inc\Badges\Critics_Pick::get_instance();

	if ( $badge->exists_on_post( $story_data_item->ID ) ) {
		$slider_item_template['o_span_group']['o_span_group_items'][] = $critic_pick_badge_prototype;
	}

	if ( empty( $slider_item_template['o_span_group']['o_span_group_items'] ) ) {
		$slider_item_template['o_span_group'] = false;
	}

	$slider_template['stories_slider_items'][] = $slider_item_template;

	// Reset slider item template.
	$slider_item_template = $slider['stories_slider_items'][1];

}

if ( ! empty( $data['stream_heading'] ) ) {

	$slider_template['heading']['c_heading_text'] = $data['stream_heading'];
} else {
	$slider_template['heading'] = false;
}

$slider_template['stories_slider_id_attr'] = isset( $data['stream_heading'] ) ? sanitize_title_with_dashes( $data['stream_heading'] ) : '';

\PMC::render_template(
	sprintf( '%s/template-parts/editorial-hub/hub-section.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[ 'slider' => $slider_template ],
	true
);
