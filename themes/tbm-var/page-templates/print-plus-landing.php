<?php

/**
 * Print Plus Landing Template.
 *
 * Template Name: Print Plus - Landing
 *
 * @package pmc-variety
 */

get_header();

\PMC::render_template(
	sprintf( '%s/template-parts/print-plus/print-plus-landing.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

get_footer( 'simplified' );
