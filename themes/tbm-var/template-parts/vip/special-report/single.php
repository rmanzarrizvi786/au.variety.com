<?php
/**
 * Single VIP Post Template.
 *
 * @package pmc-variety
 */

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();

		// Logic for showing only on free articles is in the cta-banner.php template.
		\PMC::render_template(
			sprintf( '%s/template-parts/article/cta-banner.php', untrailingslashit( CHILD_THEME_PATH ) ),
			[],
			true
		);

		?>

		<div id="post-<?php the_ID(); ?>">

				<?php
				\PMC::render_template(
					sprintf( '%s/template-parts/vip/article/article-header.php', untrailingslashit( CHILD_THEME_PATH ) ),
					[],
					true
				);
				?>

				<div class="lrv-u-background-color-body">
					<div class="lrv-a-wrapper u-max-width-618">

						<?php
						\PMC::render_template(
							sprintf( '%s/template-parts/vip/article/takeaways.php', untrailingslashit( CHILD_THEME_PATH ) ),
							[],
							true
						);
						?>

						<div class="a-content a-dropcap u-font-family-body lrv-u-line-height-normal lrv-u-font-size-18">

							<?php the_content(); ?>

						</div>
					</div>
				</div>

				<?php
				\PMC::render_template(
					sprintf( '%s/template-parts/vip/special-report/locked.php', untrailingslashit( CHILD_THEME_PATH ) ),
					[],
					true
				);
				?>

				<?php
				\PMC::render_template(
					sprintf( '%s/template-parts/vip/module/more-from-vip-article.php', untrailingslashit( CHILD_THEME_PATH ) ),
					[],
					true
				);
				?>

		</div><!-- /#single -->

		<?php
	endwhile;
endif;
