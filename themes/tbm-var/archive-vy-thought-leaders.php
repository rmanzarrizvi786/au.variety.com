<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase post type has an underscore
/**
 * VIP Special Report Archive Template.
 *
 * @package pmc-variety
 */

get_header();

\PMC::render_template(
	sprintf( '%s/template-parts/archive/main.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

get_footer();
