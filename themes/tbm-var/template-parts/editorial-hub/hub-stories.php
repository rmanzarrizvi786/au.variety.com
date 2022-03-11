<?php
/**
 * Stories Row Template.
 *
 * @package pmc-variety
 */

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/stories-row.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$row,
	true
);
