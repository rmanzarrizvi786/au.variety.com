<?php

/**
 * Video Single Template.
 *
 * @package pmc-variety
 */

\PMC::render_template(
	sprintf('%s/template-parts/video/video-header.php', untrailingslashit(CHILD_THEME_PATH)),
	[],
	true
);

?>

<section class="article-with-sidebar // ">
	<div class="lrv-a-wrapper lrv-u-flex@tablet lrv-u-justify-content-center u-justify-content-flex-end@desktop">
		<div class="article-with-sidebar__inner lrv-u-flex@tablet lrv-u-padding-t-050 u-max-width-1160 u-padding-t-150@tablet lrv-u-width-100p">
			<article id="post-<?php the_ID(); ?>" class="article-with-sidebar__article u-max-width-830 u-padding-r-125@tablet u-margin-r-125@tablet u-padding-r-375@desktop-xl u-margin-r-375@desktop-xl u-border-r-1@tablet u-border-color-brand-secondary-40 lrv-u-flex-grow-1">
				<?php
				\PMC::render_template(
					sprintf('%s/template-parts/video/social.php', untrailingslashit(CHILD_THEME_PATH)),
					[],
					true
				);
				?>

				<div class="a-content a-content--logo-end u-font-family-body lrv-u-line-height-normal lrv-u-font-size-18 u-max-width-600@tablet u-max-width-640@desktop-xl lrv-u-margin-l-auto">

					<?php the_content(); ?>

					<?php
					\PMC::render_template(
						sprintf('%s/template-parts/article/comments-button.php', untrailingslashit(CHILD_THEME_PATH)),
						[],
						true
					);
					?>

					<?php do_action('variety_single_after_content'); ?>

					<?php
					/* \PMC::render_template(
						sprintf( '%s/template-parts/article/cta-subscribe.php', untrailingslashit( CHILD_THEME_PATH ) ),
						[],
						true
					); */
					?>

					<?php
					/* \PMC::render_template(
						sprintf('%s/template-parts/article/comments.php', untrailingslashit(CHILD_THEME_PATH)),
						[],
						true
					); */
					?>

				</div>

			</article>

			<aside class="article-with-content__sidebar lrv-u-height-100p lrv-u-width-300 lrv-u-flex-shrink-0">
				<?php if (is_active_sidebar('video-sidebar')) : ?>
					<?php dynamic_sidebar('video-sidebar'); ?>
				<?php endif; ?>
			</aside>
		</div>
	</div>
</section>