<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase post type has an underscore
/**
 * Hollywood Executive Profiles Archive Template.
 *
 * @package pmc-variety
 */

get_header();

?>

<div class="u-padding-t-1@mobile-max lrv-u-margin-t-1">
	<div class="lrv-a-wrapper lrv-u-margin-t-1@tablet lrv-u-margin-b-2">
		<div class="lrv-a-grid lrv-a-cols4@tablet">
			<div class="lrv-a-span3@tablet lrv-a-space-children-vertical lrv-a-space-children--2">
				<?php get_template_part( 'template-parts/archive/latest-news-river' ); ?>

			</div>

			<aside class="lrv-a-space-children-vertical lrv-a-space-children--1">
				<?php
				if ( is_active_sidebar( 'global-sidebar' ) ) {
					dynamic_sidebar( 'global-sidebar' );
				}
				?>
			</aside>
		</div>
	</div>
</div>

<?php

get_footer();
