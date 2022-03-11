<?php
/**
 * Archive Video More From Template.
 *
 * @package pmc-variety
 */

if ( is_tax( \Variety\Plugins\Variety_VIP\Content::VIP_PLAYLIST_TAXONOMY ) ) {
	$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/more-from-video-landing.event' );
} else {
	$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/more-from-video-landing.prototype' );
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/more-from-video-landing.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
