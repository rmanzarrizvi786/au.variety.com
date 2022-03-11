<?php
/**
 * Awards Contenders Template.
 *
 * @package pmc-variety
 */

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/awards-contenders.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
