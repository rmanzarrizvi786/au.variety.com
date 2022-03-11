<?php
/**
 * Single VIP Video Template.
 *
 * @package pmc-variety
 */

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>

		<div id="post-<?php the_ID(); ?>">

			<?php
			\PMC::render_template(
				sprintf( '%s/template-parts/vip/video/article-video-header.php', untrailingslashit( CHILD_THEME_PATH ) ),
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

					<div class="a-content a-dropcap u-font-family-body lrv-u-line-height-normal lrv-u-font-size-18 lrv-a-glue-parent">

						<?php the_content(); ?>

					</div>
				</div>
			</div>

			<?php
			\PMC::render_template(
				sprintf( '%s/template-parts/vip/video/link.php', untrailingslashit( CHILD_THEME_PATH ) ),
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

		</div>

		<?php
	endwhile;
endif;
