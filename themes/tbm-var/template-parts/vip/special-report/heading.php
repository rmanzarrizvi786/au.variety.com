<?php
/**
 * Special Report Heading Template.
 *
 * @package pmc-variety
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/special-report-landing-heading.prototype' );

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/special-report-landing-heading.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
