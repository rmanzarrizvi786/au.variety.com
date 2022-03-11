<?php
/**
 * Stories Row Widget.
 *
 * @package pmc-variety
 */

if ( empty( $data['articles'] ) ) {
	return;
}

// Helper to update values based on WP_Post objects.
$larva_populate = \Variety\Inc\Larva_Populate::get_instance();

// Get default module data.
$row = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/stories-row.prototype' );

$row_template      = $row;
$c_span            = $row['c_span'];
$large_story       = $row['large_story'];
$row_item_template = $row['stories_row_items'][0];


$row_template['stories_row_items'] = [];

$i = 1;
foreach ( $data['articles'] as $key => $story_data_item ) {

	if ( 1 === $i ) {

		$large_story['c_title']      = $larva_populate->c_title( $story_data_item, $large_story );
		$large_story['c_lazy_image'] = $larva_populate->c_lazy_image( $story_data_item, $large_story, [ 'image_size' => 'landscape-medium' ] );
		$large_story['c_dek']        = $larva_populate->c_dek( $story_data_item->ID, $large_story, $story_data_item );
		$large_story['c_link']       = $larva_populate->c_link_author( $story_data_item->ID, $large_story );
		$row_template['large_story'] = $large_story;

		$i++;
		continue;
	}

	$row_item_template['c_title']        = $larva_populate->c_title( $story_data_item, $row_item_template );
	$row_item_template['c_lazy_image']   = $larva_populate->c_lazy_image( $story_data_item, $row_item_template, [ 'image_size' => 'landscape-medium' ] );
	$row_item_template['c_link']         = $larva_populate->c_link_author( $story_data_item->ID, $row_item_template );
	$row_template['stories_row_items'][] = $row_item_template;
}

// Render.
\PMC::render_template(
	sprintf( '%s/template-parts/editorial-hub/hub-stories.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[ 'row' => $row_template ],
	true
);

// Reset template.
$row_template = $row;
