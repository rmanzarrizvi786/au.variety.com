<?php
/**
 * Comments Template.
 *
 * Copied from pmc-variety-2017. For writing CSS, there is a static
 * representation of this markup in the Larva server in
 * src/patterns/one-offs/DEV-comments.
 *
 * @codeCoverageIgnore
 *
 * @package pmc-variety-2020
 */

\PMC::render_template(
	sprintf( '%s/template-parts/comments/comments.php', untrailingslashit( CHILD_THEME_PATH ) ),
	get_defined_vars(),
	true
);
