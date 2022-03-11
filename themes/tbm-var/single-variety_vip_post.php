<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase post type has an underscore
/**
 * Single VIP Post Template.
 *
 * @package pmc-variety
 */

get_header( 'vip' );

\PMC::render_template(
	sprintf( '%s/template-parts/vip/article/single.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

get_footer();
