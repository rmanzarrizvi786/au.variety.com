<?php
/**
 * The 404 page for our theme.
 *
 * @package pmc-variety
 */

get_header();

$page_404 = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/page-404.prototype' );

$page_404['c_button']['c_button_url'] = '/';

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/page-404.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$page_404,
	true
);

get_footer();
