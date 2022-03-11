<?php
/**
 * Author page template.
 *
 * @package pmc-variety
 */

get_header();

?>
<div class="lrv-a-wrapper lrv-u-margin-b-2 lrv-u-margin-t-1 u-background-color-white">
	<div class="lrv-u-flex@tablet lrv-u-width-100p">
		<div class="lrv-a-space-children-vertical lrv-a-space-children--2">
			<?php
			\PMC::render_template(
				sprintf( '%s/template-parts/author/author-blurb.php', untrailingslashit( CHILD_THEME_PATH ) ),
				[],
				true
			);
			\PMC::render_template(
				sprintf( '%s/template-parts/author/recent-articles.php', untrailingslashit( CHILD_THEME_PATH ) ),
				[],
				true
			);
			?>
		</div>
		<aside class="lrv-a-space-children-vertical lrv-a-space-children--1 lrv-u-flex lrv-u-flex-direction-column lrv-u-height-100p u-width-320@tablet lrv-u-flex-shrink-0 u-padding-l-1@tablet">
			<?php
			if ( is_active_sidebar( 'global-sidebar' ) ) {

				dynamic_sidebar( 'global-sidebar' );

			}
			?>
		</aside>
	</div>
	<div class="lrv-a-wrapper lrv-u-margin-t-2">
		<?php
		\PMC::render_template(
			sprintf( '%s/template-parts/ads/home-river.php', untrailingslashit( CHILD_THEME_PATH ) ),
			[
				'ad_location' => 'after-article',
			],
			true
		);
		?>
	</div>
</div>
<?php

get_footer();
