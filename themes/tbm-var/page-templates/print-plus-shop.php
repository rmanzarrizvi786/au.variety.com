<?php

/**
 * Print Plus Shop Template.
 *
 * Template Name: Print Plus - Shop
 *
 * @package pmc-variety
 */

get_header();

\PMC::render_template(
	sprintf( '%s/template-parts/print-plus/print-plus-shop.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

get_footer( 'simplified' );
