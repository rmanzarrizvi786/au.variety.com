<?php
/**
 * Tag Template for 'What to Hear'
 *
 * @since   2021-06-09
 *
 * @package pmc-variety-2020
 */

get_header();

\PMC::render_template(
	sprintf( '%s/template-parts/what-to-hear/main.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

get_footer();
