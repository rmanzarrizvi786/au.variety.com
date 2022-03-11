<?php
/**
 * Template Name: Editorial Hub Page
 *
 * @since   2020-05-11
 *
 * @package pmc-variety-2020
 */

global $page_template;
$page_template = 'page-editorial-hub';
get_header();

\PMC::render_template(
	sprintf( '%s/template-parts/editorial-hub/main.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

get_footer();
