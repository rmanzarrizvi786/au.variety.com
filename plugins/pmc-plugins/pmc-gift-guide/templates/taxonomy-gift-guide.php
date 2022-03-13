<?php echo PMC::render_template( PMC_GIFT_GUIDE_DIR . '/templates/header.php' ); ?>

<div id="gift-guide-container">
	<section id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			<div id="gift-guide">
				<?php if ( have_posts() ) : ?>
					<?php
					$thumbnail_id = \PMC\Gift_Guide\Common::get_instance()->get_data( get_queried_object()->term_id, 'featured_image' );
					$img_url      = wp_get_attachment_image_url( $thumbnail_id, "full" );
					?>
					<header class="archive-header gift-guide-header" style="background-image:url(<?php echo esc_url( $img_url ); ?>)">
						<h1 class="archive-title">
							<?php echo esc_html( single_term_title( "", false ) ); ?>
						</h1>
					</header><!-- .archive-header -->

					<div class="gift-guide-container">
					<div class="gift-guide-grid">
						<!-- Gift Guide Ad -->
						<div class="gift-guide-leaderboard">
							<?php dynamic_sidebar( 'pmc-gift-guide' ) ?>
						</div>
					<div class="gift-guide-description">
						<hr>
						<?php echo wp_kses_post( term_description() ); ?>
						<hr>
					</div>
					<?php while ( have_posts() ) : the_post(); ?>
						<?php
						global $post;
						$featured = \PMC\Gift_Guide\Common::get_instance()->get_data( get_the_ID(), 'featured' );
						$giftLink = \PMC\Gift_Guide\Common::get_instance()->get_data( get_the_ID(), 'link' );
						$price    = \PMC\Gift_Guide\Common::get_instance()->get_data( get_the_ID(), 'price' );
						$retailer = \PMC\Gift_Guide\Common::get_instance()->get_data( get_the_ID(), 'retailer' );

						?>
						<article id="<?php echo esc_attr( $post->post_name ); ?>" <?php post_class(); ?>>
								<div class="gift-guide-post--image" style="background-image:url('<?php echo esc_url( get_the_post_thumbnail_url( $post, 'pmc-gift-large' ) ); ?>')"></div>

							<div class="gift-guide-post--content"><!-- Post Content -->
								<header class="entry-header">
									<?php echo wp_kses_post( get_the_title() ); ?>
								</header><!-- .entry-header -->

								<div class="entry-content">
									<?php
									/* translators: %s: Name of current post */
									the_content();
									echo wp_kses_post( wp_link_pages( array(
										'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:',
												'pmc-plugins' ) . '</span>',
										'after'       => '</div>',
										'link_before' => '<span>',
										'link_after'  => '</span>',
										'echo'        => 0
									) ) );
									?>
								</div><!-- .entry-content -->
								<?php if ( ! empty( $featured )) {
									echo PMC::render_template( PMC_GIFT_GUIDE_DIR . '/templates/meta-info.php' );
								} ?>
							</div>
							<?php if ( empty( $featured ) ) {
								echo PMC::render_template( PMC_GIFT_GUIDE_DIR . '/templates/meta-info.php' );
							} ?>
								<?php echo wp_kses_post( get_the_tag_list( '<footer class="entry-meta"><span class="tag-links">', '', '</span></footer>' ) ); ?>
						</article>
						<?php
					endwhile;
				else :
					?>
					</div> <!-- #gift-guide-grid -->
					</div> <!-- #gift-guide-container -->
					<header class="page-header">
						<h1 class="page-title">Nothing Found</h1>
					</header>

					<div class="page-content">
						<p>It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.</p>
					</div><!-- .page-content -->

					<?php
				endif;
				?>
			</div><!-- #gift-guide -->
			<?php echo PMC::render_template( PMC_GIFT_GUIDE_DIR . '/templates/footer.php' ); ?>
		</div><!-- #content -->
	</section><!-- #primary -->
