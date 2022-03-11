<?php
/**
 * VIP Special Report Archive Template.
 *
 * @package pmc-variety
 */

\PMC::render_template(
	sprintf( '%s/template-parts/vip/special-report/top-stories-vip.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

\PMC::render_template(
	sprintf( '%s/template-parts/vip/special-report/heading.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

\PMC::render_template(
	sprintf( '%s/template-parts/vip/special-report/grid.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);

\PMC::render_template(
	sprintf( '%s/template-parts/vip/special-report/pagination.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);
