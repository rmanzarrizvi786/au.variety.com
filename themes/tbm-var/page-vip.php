<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase post type has an underscore
/**
 * VIP Home Template.
 *
 * Template Name: VIP - Home
 *
 * @package pmc-variety
 */

get_header( 'vip' );

\PMC::render_template(
	sprintf( '%s/template-parts/vip/home/home.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

get_footer();
