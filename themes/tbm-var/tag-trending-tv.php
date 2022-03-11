<?php
/**
 * Tag Template for Trending TV
 *
 * @since   2021-11-10
 *
 * @package pmc-variety-2020
 */

get_header();

\PMC::render_template(
	sprintf( '%s/template-parts/trending-tv/main.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

get_footer();
