<?php
/**
 * VIP Page Template.
 *
 * Template Name: VIP - Page
 *
 * @package pmc-variety
 */

get_header( 'vip' );

\PMC::render_template(
	sprintf( '%s/template-parts/page/page.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

get_footer();
