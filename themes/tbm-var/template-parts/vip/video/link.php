<?php
/**
 * Article Video More Link Template.
 *
 * @package pmc-variety
 */

use \Variety\Plugins\Variety_VIP\Content;

$data     = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/article-link.prototype' );
$playlist = get_the_terms( get_the_ID(), Content::VIP_PLAYLIST_TAXONOMY );

if ( ! empty( $playlist[0] ) ) {
	$data['c_link']['c_link_text'] = __( 'More Video', 'pmc-variety' );
	$data['c_link']['c_link_url']  = get_term_link( $playlist[0] );
} else {
	$data['c_link']['c_link_text'] = '';
	$data['c_link']['c_link_url']  = '';
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/article-link.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
