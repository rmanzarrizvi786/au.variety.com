<?php

/**
 * Single Post Template.
 *
 * @package pmc-variety-2020
 */

while (have_posts()) :

	the_post();
?>

	<section class="article-with-sidebar // ">
		<div class="lrv-a-wrapper lrv-u-flex@tablet lrv-u-margin-t-1">

			<article class="lrv-u-width-100p u-max-width-830 u-border-r-1@tablet u-border-color-brand-secondary-40 u-padding-r-125@tablet u-margin-r-125@tablet">

				<div class="u-margin-l-6@desktop-xl">
					<?php
					\PMC::render_template(
						sprintf('%s/template-parts/article/article-header.php', untrailingslashit(CHILD_THEME_PATH)),
						[],
						true
					);
					?>
				</div>

				<div class="a-content a-content--logo-end u-font-family-body lrv-u-line-height-normal lrv-u-font-size-18 u-max-width-600@tablet u-max-width-640@desktop-xl lrv-u-margin-l-auto lrv-u-margin-b-1">

					<?php
					\PMC::render_template(
						sprintf('%s/template-parts/article/takeaways.php', untrailingslashit(CHILD_THEME_PATH)),
						[],
						true
					);
					?>

					<div class="vy-cx-page-content ">
						<?php the_content(); ?>

						<?php
						\PMC::render_template(
							sprintf('%s/template-parts/article/post-tags.php', untrailingslashit(CHILD_THEME_PATH)),
							[],
							true
						);
						?>

						<?php
						/* \PMC::render_template(
							sprintf('%s/template-parts/article/comments-button.php', untrailingslashit(CHILD_THEME_PATH)),
							[],
							true
						); */
						?>

					</div>

					<?php
					// if (\Variety\Inc\Article::get_instance()->is_article_vip(get_the_ID())) {
					if (0) {
					?>
						<div id="cx-paywall" class="u-max-width-618 lrv-u-margin-lr-auto"></div>
					<?php } ?>

					<?php
					/* \PMC::render_template(
						sprintf('%s/template-parts/article/review.php', untrailingslashit(CHILD_THEME_PATH)),
						[],
						true
					); */
					?>

					<?php do_action('variety_single_after_content'); ?>

					<?php
					/* \PMC::render_template(
						sprintf('%s/template-parts/article/cta-subscribe.php', untrailingslashit(CHILD_THEME_PATH)),
						[],
						true
					); */
					?>

				</div>

				<?php
				/* the_widget(
					'\Variety\Inc\Widgets\Cxense',
					[
						'id'        => 'cx-article-bottom',
						'widget_id' => 'c5aae127e65423297fb1cb64413be1085f4b456a',
					]
				); */
				?>

				<?php
				/* \PMC::render_template(
					sprintf('%s/template-parts/ads/outbrain.php', untrailingslashit(CHILD_THEME_PATH)),
					[],
					true
				); */
				?>

				<?php
				\PMC::render_template(
					sprintf('%s/template-parts/widgets/brag-jobs.php', untrailingslashit(CHILD_THEME_PATH)),
					['size' => 4, 'pos' => 'article-bottom'],
					true
				);
				?>

				<?php
				if (!\PMC::is_mobile()) {
					get_template_part('template-parts/ads/article-page-bottom');
				}
				?>
				<?php
				/* \PMC::render_template(
					sprintf('%s/template-parts/article/comments.php', untrailingslashit(CHILD_THEME_PATH)),
					[],
					true
				); */
				?>

			</article>

			<aside class="u-width-320@tablet lrv-u-flex-shrink-0 lrv-u-flex@tablet lrv-u-flex-direction-column lrv-a-space-children-vertical lrv-a-space-children--1">
				<?php if (is_active_sidebar('global-sidebar')) : ?>
					<?php dynamic_sidebar('global-sidebar'); ?>
				<?php endif; ?>
			</aside>

		</div>
	</section>

<?php

endwhile;
