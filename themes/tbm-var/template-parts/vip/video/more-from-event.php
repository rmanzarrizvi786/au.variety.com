<?php
/**
 * Archive Video More From Template.
 *
 * @package pmc-variety
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/more-from-video-landing.event' );

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/more-from-video-landing.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
