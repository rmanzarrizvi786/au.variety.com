<?php
/**
 * Recommended For You module.
 *
 * @package pmc-variety
 */

if ( empty( $data['articles'] ) ) {
	return;
}

$voices = PMC\Larva\Pattern::get_instance()->get_json_data( 'modules/homepage-voices.prototype', true );

$template = $voices['o_tease_list']['o_tease_list_items'][0];

$voices['o_tease_list']['o_tease_list_items'] = [];

$voices['o_more_from_heading']['c_heading']['c_heading_text'] = __( 'What To Buy', 'pmc-variety' );

foreach ( $data['articles'] as $_post ) {
	$populate = new \Variety\Inc\Populate( $_post, $template );
	$item     = $populate->get();

	$item['c_span']['c_span_text'] = false;
	$item['c_span']['c_span_url']  = false;

	$voices['o_tease_list']['o_tease_list_items'][] = $item;
}

$voices['o_more_link']['c_link']['c_link_url']  = '/v/shopping/';
$voices['o_more_link']['c_link']['c_link_text'] = __( 'More', 'pmc-variety' );

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/homepage-voices.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$voices,
	true
);
