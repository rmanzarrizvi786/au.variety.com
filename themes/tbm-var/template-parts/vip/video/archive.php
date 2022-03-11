<?php
/**
 * VIP Archive Template.
 *
 * @package pmc-variety
 */

?>

<?php
\PMC::render_template(
	sprintf( '%s/template-parts/vip/video/archive-video-header.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);
?>

<?php
\PMC::render_template(
	sprintf( '%s/template-parts/vip/video/more-from.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);
?>

<?php
\PMC::render_template(
	sprintf( '%s/template-parts/vip/video/grid.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);
?>

<?php
\PMC::render_template(
	sprintf( '%s/template-parts/vip/video/pagination.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);
?>

<?php
\PMC::render_template(
	sprintf( '%s/template-parts/vip/module/explore-all-events.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[],
	true
);
