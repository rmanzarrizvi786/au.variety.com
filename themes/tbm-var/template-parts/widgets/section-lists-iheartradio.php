<?php
/**
 * Section module.
 *
 * @package pmc-variety
 */

$section = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/homepage-lists-iheartradio.prototype' );

if ( ! empty( $data['articles']['lists'] ) ) {
	$template = $section['homepage_lists'];
	$featured = $data['articles']['lists'][0];
	$populate = new \Variety\Inc\Populate( $featured, $template );

	$section['homepage_lists'] = $populate->get();
} else {
	$section['homepage_lists']['o_more_from_heading'] = [];
	$section['homepage_lists']['c_title']             = [];
	$section['homepage_lists']['c_lazy_image']        = [];
}

$section['homepage_lists']['o_more_link']['c_link']['c_link_text'] = __( 'More Lists', 'pmc-variety' );
$section['homepage_lists']['o_more_link']['c_link']['c_link_url']  = '/lists/';

if ( ! empty( $data['list_more_text'] ) ) {
	$section['homepage_lists']['o_more_link']['c_link']['c_link_text'] = $data['list_more_text'];
}

if ( ! empty( $data['list_more_link'] ) ) {
	$section['homepage_lists']['o_more_link']['c_link']['c_link_url'] = $data['list_more_link'];
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/homepage-lists-iheartradio.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$section,
	true
);
