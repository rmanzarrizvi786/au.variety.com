<?php

if ( empty( $data['section_heading'] ) ) {
	return;
}

$streamers_section_header = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/streamers-section-header.prototype' );

$streamers_section_header['c_heading']['c_heading_text'] = $data['section_heading'] ?? '';
$streamers_section_header['c_tagline']['c_tagline_text'] = $data['section_tagline'] ?? '';

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/streamers-section-header.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$streamers_section_header,
	true
);
