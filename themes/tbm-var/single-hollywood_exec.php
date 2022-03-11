<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase post type has an underscore
/**
 * Hollywood Executive Profile Single Template.
 *
 * @package pmc-variety-2020
 */

get_header();

\PMC::render_template(
	sprintf( '%s/template-parts/hollywood-exec/single.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

get_footer();
