<?php

/**
 * Single Post Template.
 *
 * Note: this template corresponds exactly with the Larva template
 * http://localhost:3000/project/__tests__/featured-article?vertical=true.
 * Make changes there first and then update here.
 *
 * @package pmc-variety-2020
 */

$is_vertical_image = \Variety\Inc\Featured_Article::get_instance()->is_vertical_image();

$module_context = $is_vertical_image ? 'featured-article.vertical' : 'featured-article';
$header_classes = $is_vertical_image ? 'u-order-n1@tablet' : '';

while (have_posts()) :

	the_post();
?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<?php if ($is_vertical_image) : ?>
			<div class="lrv-a-grid a-cols2@tablet lrv-u-align-items-center u-background-color-picked-bluewood@tablet lrv-a-glue-parent lrv-u-margin-b-1 u-color-white@tablet">
			<?php endif; ?>

			<?php
			\PMC::render_template(
				sprintf('%s/template-parts/article/featured-image.php', untrailingslashit(CHILD_THEME_PATH)),
				[
					'variant' => $module_context,
				],
				true
			);
			?>

			<header class="a-featured-article-grid__header lrv-u-flex lrv-u-flex-direction-column lrv-u-justify-content-center lrv-u-text-align-center <?php echo esc_attr($header_classes); ?>">

				<?php
				\PMC::render_template(
					sprintf('%s/template-parts/article/breadcrumbs.php', untrailingslashit(CHILD_THEME_PATH)),
					[
						'variant' => $module_context,
					],
					true
				);
				?>

				<?php
				\PMC::render_template(
					sprintf('%s/template-parts/article/article-title.php', untrailingslashit(CHILD_THEME_PATH)),
					[
						'variant' => $module_context,
					],
					true
				);
				?>

				<div class="lrv-u-justify-content-center lrv-u-flex lrv-u-padding-b-1 lrv-u-justify-content-center">
					<?php
					\PMC::render_template(
						sprintf('%s/template-parts/article/author-social.php', untrailingslashit(CHILD_THEME_PATH)),
						[],
						true
					);
					?>
				</div>

			</header>

			<?php if ($is_vertical_image) : ?>
			</div>
		<?php endif; ?>

		<div class="a-featured-article-grid lrv-a-wrapper">

			<div class="a-featured-article-grid__content">
				<div class="a-content a-content--blockquote-featured-article a-dropcap a-featured-article-image-offsets u-font-family-body lrv-u-font-size-18 lrv-u-border-t-1 lrv-u-border-color-grey-light">
					<?php the_content(); ?>
				</div>

				<div id="cx-paywall" class="u-max-width-618 lrv-u-margin-lr-auto"></div>

				<?php
				// After article content here.
				?>
				<?php
				\PMC::render_template(
					sprintf('%s/template-parts/article/cta-subscribe.php', untrailingslashit(CHILD_THEME_PATH)),
					[],
					true
				);
				?>

				<?php
				\PMC::render_template(
					sprintf('%s/template-parts/article/comments.php', untrailingslashit(CHILD_THEME_PATH)),
					[],
					true
				);
				?>

				<?php
				/* the_widget(
					'Variety\Inc\Widgets\Cxense',
					[
						'id'        => 'cx-article-bottom',
						'widget_id' => 'c5aae127e65423297fb1cb64413be1085f4b456a',
					]
				); */
				?>

				<?php
				\PMC::render_template(
					sprintf('%s/template-parts/ads/outbrain.php', untrailingslashit(CHILD_THEME_PATH)),
					[],
					true
				);
				?>
			</div>
		</div>

	</article>
<?php
endwhile;
