<?php
/**
 * Video Category Template.
 *
 * @package pmc-variety
 */

get_header();

\PMC::render_template(
	sprintf( '%s/template-parts/video/category.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

get_footer();
