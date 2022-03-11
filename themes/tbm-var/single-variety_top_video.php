<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase post type has an underscore
/**
 * Video Single Template.
 *
 * @package pmc-variety
 */

get_header();

\PMC::render_template(
	sprintf( '%s/template-parts/video/single.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

get_footer();
