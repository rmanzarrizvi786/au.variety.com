<?php
/**
 * Template Name: Education Discounts
 *
 * CDWE-580 -- Copied from pmc-variety-2014 theme.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-08-21
 */

get_header();
?>

	<div class="l-page__content">
		<div class="l-wrap">
			<div class="l-wrap__main">
				<?php if ( have_posts() ) { ?>
					<?php
					while ( have_posts() ) {
						the_post();
						?>
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'clearfix' ); ?> role="article"
							itemscope itemtype="http://schema.org/BlogPosting">
							<header class="article-header">
								<h1 class="page-title" itemprop="headline">Education Discounts</h1>
							</header> <!-- end article header -->

							<section class="entry-content">
								<div class="book-wrapper">
									<div class="book-block">
										<img src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/ed-discount-student.png' ); ?>"/>
										<h2>Students</h2>
										<a class="subscribe-now" href="https://www.pubservice.com/variety/?PC=VY&PK=SSPUB58">
											Subscribe Now
										</a>
									</div>
									<div class="book-block">
										<img src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/ed-discount-professor.png' ); ?>"/>
										<h2>Professors</h2>
										<a class="subscribe-now" href="https://www.pubservice.com/variety/?PC=VY&PK=SPPUB58">
											Subscribe Now
										</a>
										<br/>
										<span class="share-offer">
											<a href="mailto:?subject=Check%20out%20these%20Education%20Discounts%20from%20Variety&body=Hi,%20I%20thought%20you%20might%20be%20interested%20in%20the%20discounted%20education%20rates%20that%20Variety%20is%20offering!%20You%20can%20view%20them%20here:%20https://variety.com/education-discounts/%3Fsrc%3Dshare-info&nm=11&nx=165&ny=-41&mb=2&clkt=55">
												SHARE THIS OFFER
											</a>
										</span>
										<span class="share-offer-sub-title">with your students and colleagues</span>
									</div>
								</div>
							</section> <!-- end article section -->

						</article> <!-- end article -->

					<?php } ?>

				<?php } else { ?>

					<article id="post-not-found" class="hentry clearfix">
						<header class="article-header">
							<h1><?php esc_html_e( 'Page Not Found!', 'pmc-variety' ); ?></h1>
						</header>
					</article>

				<?php } ?>

			</div> <!-- end #l-wrap__main -->

			<?php get_sidebar(); ?>
		</div> <!-- end #l-wrap -->
	</div> <!-- end #l-page__content -->

<?php
get_footer();
