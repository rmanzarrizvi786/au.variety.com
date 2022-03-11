<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase post type has an underscore
/**
 * Single VIP Video Template.
 *
 * @package pmc-variety
 */

get_header( 'vip' );

\PMC::render_template(
	sprintf( '%s/template-parts/vip/video/single.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

get_footer();
