<?php
/**
 * Hub Header Template.
 *
 * @package pmc-variety
 */

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/hub-header.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
