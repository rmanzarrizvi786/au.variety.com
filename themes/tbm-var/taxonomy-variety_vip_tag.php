<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase post type has an underscore
/**
 * VIP Tag Template.
 *
 * @package pmc-variety
 */

get_header( 'vip' );

\PMC::render_template(
	sprintf( '%s/template-parts/vip/archive/archive.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

get_footer();
