<?php
/**
 * V500 Home Page Template
 *
 * Displays the V500 Home page.
 *
 * @package pmc-variety-2017
 * @since 1.0
 */

$profiles = \Variety\Plugins\Variety_500\Spotlight::get_profiles();
$video_id = get_option( 'variety_500_home_video_id' );

$sponsor_logo = get_option( 'variety_500_sponsor_hero_logo' );

\Variety\Plugins\Variety_500\Templates::header();
?>

<section class="l-hero-video">
	<div class="l-hero-video__container">
		<div class="c-hero-video">
			<div class="c-hero-video__background">
				<picture class="c-hero-video__background-image">
					<source srcset="<?php echo esc_url( untrailingslashit( VARIETY_500_PLUGIN_URL ) ); ?>/assets/images/hero-image.png" media="(min-width: 320px) and (max-width: 415px)">
					<source srcset="<?php echo esc_url( untrailingslashit( VARIETY_500_PLUGIN_URL ) ); ?>/assets/images/hero-image-large.png" media="(min-width: 416px)">
					<img src="<?php echo esc_url( untrailingslashit( VARIETY_500_PLUGIN_URL ) ); ?>/assets/images/hero-image-large.png" alt="Variety500 Entertainment Leaders and Icons" />
				</picture>
			</div>
			<div class="c-hero-video__player">
				<?php
				if ( ! empty( $video_id ) ) {
					printf( '<div class="c-hero-video__video" data-video-id="%s"></div>', esc_attr( $video_id ) );
				}
				?>
				<div class="c-hero-video__content">
					<?php if ( ! empty( $video_id ) ) { ?>
						<div class="c-hero-video__content-button">
							<button class="c-button c-button--inverted c-button--hero-video"><?php esc_html_e( 'Watch the Video', 'pmc-variety' ); ?></button>
						</div>
					<?php } ?>
					<?php
					if ( ! empty( $sponsor_logo ) ) {
						$sponsor_link = get_option( 'variety_500_sponsor_link' );
						$presented_by = get_option( 'variety_500_presented_by' );
						$presented_by = ( ! empty( $presented_by ) ) ? $presented_by : __( 'presented by', 'pmc-variety' );
						?>
						<div class="c-hero-video__content-sponsor">
							<?php
							if ( ! empty( $sponsor_link ) ) {
								printf( '<a href="%s" target="_blank">', esc_url( $sponsor_link ) );
							}
							?>
							<span class="c-hero-video__presented-by" ><?php echo esc_html( $presented_by ); ?></span>
							<span class="c-hero-video__sponsor-logo">
									<img src="<?php echo esc_url( $sponsor_logo ); ?>" alt="<?php esc_attr_e( 'Variety 500 Sponsor', 'pmc-variety' ); ?>"  />
								</span>
							<?php
							if ( ! empty( $sponsor_link ) ) {
								echo '</a>';
							}
							?>
						</div>
					<?php } ?>
				</div>
			</div>
			<?php if ( ! \PMC::is_mobile() ) { ?>
				<div class="c-scroll__cta">
					<a href="#" class="c-scroll__cta-link">
						<span></span>
						<span></span>
						<span></span>
						<span class="c-scroll__cta-text"><?php esc_html_e( 'Scroll', 'pmc-variety' ); ?></span>
					</a>
				</div>
			<?php } ?>
		</div>
	</div>
</section>

<?php
\Variety\Plugins\Variety_500\Templates::site_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>

		<section class="l-home-intro" id="about">
			<div class="l-home-intro__container">
				<div class="c-page-intro c-page-intro--homepage">
					<span class="c-section-title"><?php esc_html_e( 'Intro', 'pmc-variety' ); ?></span>
					<div class="c-page-intro__background-text">
						<?php esc_html_e( 'Intro', 'pmc-variety' ); ?>
					</div>
					<h1 class="c-page-intro__text">
						<?php esc_html_e( 'What is Variety500?', 'pmc-variety' ); ?>
					</h1>
					<div class="c-page-intro__description">
						<?php the_content(); ?>
					</div>
				</div><!-- .c-page-intro -->
			</div>
		</section>

		<section class="l-spotlight" id="spotlight" data-trigger="spotlight-manager">
			<div class="l-spotlight__container">
				<header class="l-spotlight__header">

					<h3 class="c-spotlight__heading">
						<?php esc_html_e( 'Spotlight', 'pmc-variety' ); ?>
					</h3>

				</header><!-- .l-spotlight__header -->

				<div class="l-spotlight__copy-area">
					<?php foreach ( $profiles as $count => $profile ) : ?>
						<div class="l-spotlight__copy-slide<?php echo ( 1 === $count ) ? ' is-active' : ''; ?>" data-spotlight-slide="<?php echo esc_attr( $count ); ?>">
							<article class="c-profile-bio">
								<header>
									<?php if ( ! empty( $profile['companies'] ) ) : ?>
										<p class="c-profile-bio__company c-profile-bio__company--spotlight"><?php echo esc_html( $profile['companies'] ); ?></p>
									<?php endif; ?>
									<h2 class="c-profile-bio__heading c-profile-bio__heading--spotlight"><?php echo esc_html( $profile['name'] ); ?></h2>
									<?php if ( ! empty( $profile['job_title'] ) ) : ?>
										<h3 class="c-profile-bio__job-title c-profile-bio__job-title--spotlight"><?php echo esc_html( $profile['job_title'] ); ?></h3>
									<?php endif; ?>
								</header>
								<?php if ( ! empty( $profile['synopsis'] ) ) : ?>
									<div class="c-profile-bio__text c-profile-bio__text--spotlight">
										<p><?php echo wp_kses_post( $profile['synopsis'] ); ?></p>
									</div>
								<?php endif; ?>
								<?php if ( ! empty( $profile['link'] ) ) : ?>
									<div class="l-spotlight__cta l-spotlight__cta--desktop">
										<a href="<?php echo esc_url( $profile['link'] ); ?>" class="c-button"><?php esc_html_e( 'View Profile', 'pmc-variety' ); ?></a>
									</div>
								<?php endif; ?>
							</article><!-- .c-profile-bio -->
						</div><!-- .l-spotlight__copy-slide -->
					<?php endforeach; ?>
				</div><!-- .l-spotlight__copy-area -->

				<div class="l-spotlight__cta l-spotlight__cta--mobile">
					<?php foreach ( $profiles as $count => $profile ) : ?>
						<?php if ( ! empty( $profile['link'] ) ) : ?>
							<div class="l-spotlight__cta-slide<?php echo ( 1 === $count ) ? ' is-active' : ''; ?>" data-spotlight-slide="<?php echo esc_attr( $count ); ?>">
								<a href="<?php echo esc_url( $profile['link'] ); ?>" class="c-button c-button--inverted"><?php esc_html_e( 'View Profile', 'pmc-variety' ); ?></a>
							</div><!-- .l-spotlight__cta-slide -->
						<?php endif; ?>
					<?php endforeach; ?>
				</div><!-- .l-spotlight__cta.l-spotlight__cta--mobile -->

				<div class="l-spotlight__image-area">
					<?php foreach ( $profiles as $count => $profile ) : ?>
						<div class="l-spotlight__image-slide<?php echo ( 1 === $count ) ? ' is-active' : ''; ?>" data-spotlight-slide="<?php echo esc_attr( $count ); ?>">
							<div class="c-profile-card">
								<figure class="c-profile-card__media c-profile-card__media--spotlight">
									<?php if ( ! empty( $profile['image'] ) ) : ?>
										<img src="<?php echo esc_url( $profile['image'] ); ?>" alt="<?php echo esc_html( $profile['name'] ); ?>" />
									<?php endif; ?>
								</figure>
							</div><!-- .c-profile-card -->
						</div><!-- .l-spotlight__image-slide -->
					<?php endforeach; ?>
				</div><!-- .l-spotlight__image-area -->

				<nav class="l-spotlight__nav-container">
					<ul class="l-spotlight__nav">
						<li class="l-spotlight__nav-item l-spotlight__nav-item--prev">
							<a href="#" class="c-spotlight__nav c-spotlight__nav--prev" data-spotlight-trigger="prev">
								<span><?php esc_html_e( 'Prev', 'pmc-variety' ); ?></span>
							</a>
						</li>
						<li class="l-spotlight__nav-item l-spotlight__nav-item--next">
							<a href="#" class="c-spotlight__nav c-spotlight__nav--next" data-spotlight-trigger="next">
								<span><?php esc_html_e( 'Next', 'pmc-variety' ); ?></span>
							</a>
						</li>
					</ul><!-- .l-spotlight__nav -->

					<ul class="l-spotlight__slides-nav">
						<?php foreach ( $profiles as $count => $profile ) : ?>
							<li class="l-spotlight__slides-nav-item">
								<a href="#" class="c-spotlight__slide-indicator" data-spotlight-trigger="<?php echo esc_attr( $count ); ?>">
									<span class="screen-reader-text"><?php echo esc_html( $profile['name'] ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul><!-- .l-spotlight__slides-nav -->

				</nav><!-- .l-spotlight__nav-container -->
			</div><!-- .l-spotlight__container -->
		</section><!-- .l-spotlight -->


		<?php
		$stats = \Variety\Plugins\Variety_500\Stats::get_instance()->get_stats();

		if ( ! empty( $stats ) ) :
			?>
			<section class="l-stats" id="by-the-numbers" data-trigger="stats-manager">
				<div class="l-stats__container">
					<header class="l-stats__header">
						<h3 class="c-heading c-heading--by-the-numbers">
							<?php esc_html_e( 'Who makes up the Variety500?', 'pmc-variety' ); ?>
						</h3><!-- .c-heading.c-heading--by-the-numbers -->
					</header><!-- .l-stats__header -->

					<div class="l-stats__content">
						<?php if ( ! empty( $stats['country_of_residence'] ) ) : ?>
							<div class="l-stats__card is-active" data-stats-slide="country-of-residence">
								<div class="l-stats__caption">
									<h4 class="c-stats__caption"><?php esc_html_e( 'Country of citizenship', 'pmc-variety' ); ?></h4>
								</div><!-- .l-stats__caption -->
								<ul class="l-stats__list">
									<?php
									$count = 0;

									foreach ( $stats['country_of_residence'] as $country => $stat_count ) :
										$count++;
										$expanded = false;

										if ( $count <= 3 ) {
											$expanded = true;
										} elseif ( $count > 9 ) {
											break;
										}

										$country_slug = \Variety\Plugins\Variety_500\Countries::get_country_slug( $country );
										?>
										<li class="l-stats__list-item">
											<a href="<?php echo esc_url( \Variety\Plugins\Variety_500\Templates::get_search_url( [ 'country_of_residence' => $country ] ) ); ?>" class="c-stats__box
																<?php
																if ( ! empty( $expanded ) ) {
																	echo esc_attr( 'c-stats__box--expanded' ); }
																?>
											">
												<span class="c-stats__icon c-stats__icon--flag"><img src="<?php echo esc_url( untrailingslashit( VARIETY_500_PLUGIN_URL ) ); ?>/assets/images/flags/<?php echo esc_attr( $country_slug ); ?>.png" alt="<?php echo esc_attr( $country ); ?>" /></span>
												<span class="c-stats__name"><?php echo esc_html( $country ); ?></span>
												<span class="c-stats__count"><?php echo intval( $stat_count ); ?></span>
											</a><!-- .c-stats__box.c-stats__box--expanded -->
										</li><!-- .l-stats__list-item -->
									<?php endforeach; ?>
								</ul><!-- .l-stats__list -->
							</div><!-- .l-stats__card -->
						<?php endif; ?>

						<?php if ( ! empty( $stats['country_of_citizenship'] ) ) : ?>
							<div class="l-stats__card" data-stats-slide="citizenship">
								<div class="l-stats__caption">
									<h4 class="c-stats__caption"><?php esc_html_e( 'Citizenship', 'pmc-variety' ); ?></h4>
								</div><!-- .l-stats__caption -->
								<ul class="l-stats__list">
									<?php
									$count = 0;

									foreach ( $stats['country_of_citizenship'] as $country => $stat_count ) :
										$count++;
										$expanded = false;

										if ( $count <= 3 ) {
											$expanded = true;
										} elseif ( $count > 9 ) {
											break;
										}

										$country_slug = \Variety\Plugins\Variety_500\Countries::get_country_slug( $country );
										?>
										<li class="l-stats__list-item">
											<a href="<?php echo esc_url( \Variety\Plugins\Variety_500\Templates::get_search_url( [ 'country_of_citizenship' => $country ] ) ); ?>" class="c-stats__box
																<?php
																if ( ! empty( $expanded ) ) {
																	echo esc_attr( 'c-stats__box--expanded' ); }
																?>
											">
												<span class="c-stats__icon c-stats__icon--flag"><img src="<?php echo esc_url( untrailingslashit( VARIETY_500_PLUGIN_URL ) ); ?>/assets/images/flags/<?php echo esc_attr( $country_slug ); ?>.png" alt="<?php echo esc_attr( $country ); ?>" /></span>
												<span class="c-stats__name"><?php echo esc_html( $country ); ?></span>
												<span class="c-stats__count"><?php echo intval( $stat_count ); ?></span>
											</a><!-- .c-stats__box.c-stats__box--expanded -->
										</li><!-- .l-stats__list-item -->
									<?php endforeach; ?>
								</ul><!-- .l-stats__list -->
							</div><!-- .l-stats__card -->
						<?php endif; ?>

						<?php if ( ! empty( $stats['line_of_work'] ) ) : ?>
							<div class="l-stats__card" data-stats-slide="job-function">
								<div class="l-stats__caption">
									<h4 class="c-stats__caption"><?php esc_html_e( 'Job Function', 'pmc-variety' ); ?></h4>
								</div><!-- .l-stats__caption -->
								<ul class="l-stats__list">
									<?php
									$count = 0;

									foreach ( $stats['line_of_work'] as $line_of_work => $stat_count ) :
										$count++;
										$expanded = false;

										if ( $count <= 3 ) {
											$expanded = true;
										} elseif ( $count > 6 ) {
											break;
										}

										// Prepare the line_of_work file name.
										$file_name = sanitize_title( trim( $line_of_work ) );
										?>
										<li class="l-stats__list-item">
											<a href="<?php echo esc_url( \Variety\Plugins\Variety_500\Templates::get_search_url( [ 'line_of_work' => $line_of_work ] ) ); ?>" class="c-stats__box
																<?php
																if ( ! empty( $expanded ) ) {
																	echo esc_attr( 'c-stats__box--expanded' ); }
																?>
											">
												<?php

												/*
												 * DEV NOTE:
												 * Please note there is no `c-stats__icon--flag` class in here (it adds 'shadow' to both
												 * sides of the rectangular flag; it shouldn't be applied to the icons here).
												 *
												 * Known icons: backers.png, deal-makers.png, execs.png, artists.png, producers.png, moguls.png
												 */
												?>
												<span class="c-stats__icon"><img src="<?php echo esc_url( untrailingslashit( VARIETY_500_PLUGIN_URL ) ); ?>/assets/images/jobs/<?php echo esc_attr( $file_name ); ?>.png" alt="<?php echo esc_attr( $line_of_work ); ?>" /></span>
												<span class="c-stats__name"><?php echo esc_html( $line_of_work ); ?></span>
												<span class="c-stats__count"><?php echo intval( $stat_count ); ?></span>
											</a><!-- .c-stats__box.c-stats__box--expanded -->
										</li><!-- .l-stats__list-item -->
									<?php endforeach; ?>
								</ul><!-- .l-stats__list -->
							</div><!-- .l-stats__card -->
						<?php endif; ?>

						<?php if ( ! empty( $stats['media_category'] ) ) : ?>
							<div class="l-stats__card" data-stats-slide="media-category">
								<div class="l-stats__caption">
									<h4 class="c-stats__caption"><?php esc_html_e( 'Media Category', 'pmc-variety' ); ?></h4>
								</div><!-- .l-stats__caption -->
								<ul class="l-stats__list">
									<?php
									$count = 0;

									foreach ( $stats['media_category'] as $media_category => $stat_count ) :
										$count++;
										$expanded = false;

										if ( $count <= 3 ) {
											$expanded = true;
										} elseif ( $count > 6 ) {
											break;
										}

										// Prepare the $media_category file name.
										$file_name = sanitize_title( trim( $media_category ) );
										// Abbreviate this.
										if ( 'Live Entertainment' === $media_category ) {
											$media_category = __( 'Live Ent.', 'pmc-variety' );
										}
										?>
										<li class="l-stats__list-item">
											<a href="<?php echo esc_url( \Variety\Plugins\Variety_500\Templates::get_search_url( [ 'media_category' => $media_category ] ) ); ?>" class="c-stats__box
																<?php
																if ( ! empty( $expanded ) ) {
																	echo esc_attr( 'c-stats__box--expanded' ); }
																?>
											">
												<?php

												/*
												 * DEV NOTE:
												 * Please note there is no `c-stats__icon--flag` class in here (it adds 'shadow' to both
												 * sides of the rectangular flag; it shouldn't be applied to the icons here).
												 *
												 * Known icons: backers.png, deal-makers.png, execs.png, artists.png, producers.png, moguls.png
												 */
												?>
												<span class="c-stats__icon"><img src="<?php echo esc_url( untrailingslashit( VARIETY_500_PLUGIN_URL ) ); ?>/assets/images/jobs/<?php echo esc_attr( $file_name ); ?>.png" alt="<?php echo esc_attr( $media_category ); ?>" /></span>
												<span class="c-stats__name"><?php echo esc_html( $media_category ); ?></span>
												<span class="c-stats__count"><?php echo intval( $stat_count ); ?></span>
											</a><!-- .c-stats__box.c-stats__box--expanded -->
										</li><!-- .l-stats__list-item -->
									<?php endforeach; ?>
								</ul><!-- .l-stats__list -->
							</div><!-- .l-stats__card -->
						<?php endif; ?>
					</div><!-- .l-stats__content -->
				</div><!-- .l-stats__container -->

				<div class="l-stats__container l-stats__container--bare">
					<nav class="l-stats__nav-container">
						<div class="l-stats__nav-label">
							<span class="c-stats__nav-label"><?php esc_html_e( 'View By', 'pmc-variety' ); ?></span>
						</div><!-- .l-stats__nav-label -->

						<div class="l-stats__nav">
							<ul class="l-stats__nav-list l-stats__nav-list--textual">
								<?php if ( ! empty( $stats['country_of_residence'] ) ) : ?>
									<li class="l-stats__nav-item">
										<a href="#" class="c-stats__nav-item" data-stats-trigger="country-of-residence"><?php esc_html_e( 'Country of citizenship', 'pmc-variety' ); ?></a>
									</li><!-- .l-stats__nav-item -->
								<?php endif; ?>
								<?php if ( ! empty( $stats['country_of_citizenship'] ) ) : ?>
									<li class="l-stats__nav-item">
										<a href="#" class="c-stats__nav-item" data-stats-trigger="citizenship"><?php esc_html_e( 'Citizenship', 'pmc-variety' ); ?></a>
									</li><!-- .l-stats__nav-item -->
								<?php endif; ?>
								<?php if ( ! empty( $stats['line_of_work'] ) ) : ?>
									<li class="l-stats__nav-item">
										<a href="#" class="c-stats__nav-item" data-stats-trigger="job-function"><?php esc_html_e( 'Job Function', 'pmc-variety' ); ?></a>
									</li><!-- .l-stats__nav-item -->
								<?php endif; ?>
								<?php if ( ! empty( $stats['media_category'] ) ) : ?>
									<li class="l-stats__nav-item">
										<a href="#" class="c-stats__nav-item" data-stats-trigger="media-category"><?php esc_html_e( 'Media Category', 'pmc-variety' ); ?></a>
									</li><!-- .l-stats__nav-item -->
								<?php endif; ?>
							</ul><!-- .l_stats__nav-list.l-stats__nav-list--textual -->
						</div><!-- .l-stats__nav -->

						<ul class="l-stats__nav-list l-stats__nav-list--indicator">
							<?php if ( ! empty( $stats['country_of_residence'] ) ) : ?>
								<li class="l-stats__nav-item">
									<a href="#" class="c-stats__nav-indicator" data-stats-trigger="country-of-residence">
										<span class="screen-reader-text"><?php esc_html_e( 'Country of citizenship', 'pmc-variety' ); ?></span>
									</a>
								</li><!-- .l-stats__nav-item -->
							<?php endif; ?>
							<?php if ( ! empty( $stats['country_of_citizenship'] ) ) : ?>
								<li class="l-stats__nav-item">
									<a href="#" class="c-stats__nav-indicator" data-stats-trigger="citizenship">
										<span class="screen-reader-text"><?php esc_html_e( 'Citizenship', 'pmc-variety' ); ?></span>
									</a>
								</li><!-- .l-stats__nav-item -->
							<?php endif; ?>
							<?php if ( ! empty( $stats['line_of_work'] ) ) : ?>
								<li class="l-stats__nav-item">
									<a href="#" class="c-stats__nav-indicator" data-stats-trigger="job-function">
										<span class="screen-reader-text"><?php esc_html_e( 'Job Function', 'pmc-variety' ); ?></span>
									</a>
								</li><!-- .l-stats__nav-item -->
							<?php endif; ?>
							<?php if ( ! empty( $stats['media_category'] ) ) : ?>
								<li class="l-stats__nav-item">
									<a href="#" class="c-stats__nav-indicator" data-stats-trigger="media-category">
										<span class="screen-reader-text"><?php esc_html_e( 'Media Category', 'pmc-variety' ); ?></span>
									</a>
								</li><!-- .l-stats__nav-item -->
							<?php endif; ?>
						</ul><!-- .l_-stats__nav-list -->
					</nav><!-- .l-stats__nav-container.l-stats__nav-list--indicator -->
				</div><!-- .l-stats__container.l-stats__container--bare -->
			</section><!-- .l-stats -->

		<?php endif; ?>
		<?php

		$carousel_videos = \Variety\Plugins\Variety_500\Interviews::get_instance()->get_carousel_videos();

		if ( ! empty( $carousel_videos ) && is_array( $carousel_videos ) ) {

			$additional_classes = '';

			if ( count( $carousel_videos ) > 0 ) {

				$additional_classes = 'l-interview-video--has-header';

			}
			?>
			<section class="l-interview-video l-interview--carousel <?php echo esc_attr( $additional_classes ); ?>" data-video-carousel>
				<div class="l-interview-video__container">
					<header class="l-interview-video__header">

						<h3 class="l-interview-video__header__heading">
							<?php esc_html_e( 'Interviews', 'pmc-variety' ); ?>
						</h3>

					</header><!-- .l-interview-video__header -->

					<div class="l-interview-video__player">

						<nav class="l-interview-video__nav-container">
							<ul class="l-interview-video__nav__list">
								<li class="l-interview-video__nav-item l-interview-video__nav-item--prev">
									<a href="#" class="l-interview-video__nav l-interview-video__nav--prev" data-video-trigger="prev">
										<span><?php esc_html_e( 'Prev', 'pmc-variety' ); ?></span>
									</a>
								</li>
								<li class="l-interview-video__nav-item l-interview-video__nav-item--next">
									<a href="#" class="l-interview-video__nav l-interview-video__nav--next" data-video-trigger="next">
										<span><?php esc_html_e( 'Next', 'pmc-variety' ); ?></span>
									</a>
								</li>
							</ul><!-- .l-interview-video__nav__list -->
						</nav><!-- .l-interview-video__nav-container -->

						<ul class="l-interview-video__carousel" data-video-slider>
							<?php

							for ( $i = 0; $i < count( $carousel_videos ); $i++ ) {

								$modifiers = ( 0 === $i ) ? ' is-active' : '';

								?>
								<li class="l-interview-video__slide<?php echo esc_attr( $modifiers ); ?>" data-video="video-unique-slug-<?php echo intval( $carousel_videos[ $i ]->ID ); ?>">
									<?php
									if ( empty( $carousel_videos[ $i ]->image ) ) {
										$carousel_videos[ $i ]->image = variety_get_card_image_url( $carousel_videos[ $i ]->ID );
									}

									if ( empty( $carousel_videos[ $i ]->image_alt ) ) {
										$carousel_videos[ $i ]->image_alt = variety_get_card_image_alt( $carousel_videos[ $i ]->ID );
									}
									?>

									<div class="c-intreview-player c-player is-static">

										<div class="c-intreview-player__thumb">
											<a href="#" class="c-intreview-player__link c-player__link">
												<img src="<?php echo esc_url( $carousel_videos[ $i ]->image ); ?>" alt="<?php echo esc_attr( $carousel_videos[ $i ]->image_alt ); ?>" />
											</a>

											<?php
											// Fetch the video source.
											$video_source = get_post_meta( $carousel_videos[ $i ]->ID, 'variety_top_video_source', true );
											$video_source = variety_filter_youtube_url( $video_source );

											// For YouTube, apply an iFrame. Caters for youtu.be links.
											if ( strpos( $video_source, 'youtu' ) !== false ) {
												$video_source = str_replace( 'www.', '', $video_source );

												if ( strpos( $video_source, 'youtu.be' ) ) {
													$video_source = preg_replace( '~^https?://youtu\.be/([a-z-\d_]+)$~i', 'https://www.youtube.com/embed/$1', $video_source );
												} elseif ( strpos( $video_source, 'youtube.com/watch' ) ) {
													$video_source = preg_replace( '~^https?://youtube\.com\/watch\?v=([a-z-\d_]+)$~i', 'https://www.youtube.com/embed/$1', $video_source );
												}

												$video_source .= '?version=3&#038;rel=0&#038;fs=1&#038;autohide=2&#038;showsearch=0&#038;showinfo=0&#038;iv_load_policy=3&#038;wmode=transparent';

												printf( '<iframe id="interview-video-%1$s" class="interview-vidoes" type="text/html" width="670" height="407" data-src="%2$s" allowfullscreen="true" style="border:0;"></iframe>', intval( $carousel_videos[ $i ]->ID ), esc_url( $video_source ) );
											} else {
												// Run it through the_content filter to process any oEmbed or Shortcode
												// https://wordpressvip.zendesk.com/hc/en-us/requests/106493 removed apply_filters and added do_shortcode
												// We think only youtube and JWPlayer is being used here
												if ( 0 === strpos( $video_source, '[' ) ) {
													echo do_shortcode( wp_kses_post( $video_source ) ); // phpcs:ignore
												} elseif ( function_exists( 'wpcom_vip_wp_oembed_get' ) ) {
													echo wp_kses_post( wpcom_vip_wp_oembed_get( $video_source ) );
												} else {
													echo wp_kses_post( wp_oembed_get( $video_source ) );
												}
											}
											?>
										</div><!-- .c-intreview-player__thumb -->

										<header class="c-intreview-player__title">
											<h3 class="c-heading c-heading--video"><?php echo esc_html( $carousel_videos[ $i ]->post_title ); ?></h3>
										</header><!-- .c-intreview-player -->
									</div><!-- .c-intreview-player -->

								</li><!-- .l-interview-video -->
							<?php } // end foreach. ?>
						</ul><!-- .l-interview-video__carousel -->
					</div><!-- .l-intereview-video__player -->
				</div><!-- .l-interview-video__container-->
			</section><!-- .l-interview-video -->
		<?php } // end if. ?>

		<?php
		$feed = \Variety\Plugins\Variety_500\Social_Glimpse::get_home_feed();
		if ( ! empty( $feed ) && is_array( $feed ) ) :
			?>
			<section class="l-glimpse">
				<div class="l-glimpse__container">
					<header class="l-glimpse__header">
						<h3 class="c-heading c-heading--home-glimpse">
							<?php esc_html_e( 'A Glimpse Into the Lives of the 500', 'pmc-variety' ); ?>
						</h3><!-- .c-heading.c-heading--glimpse -->
					</header><!-- .l-glimpse__header -->
					<div class="l-glimpse__content">
						<div class="c-slider">
							<div class="c-slider__slick" data-trigger="glimpse-slick-slider">
								<?php foreach ( $feed as $image ) : ?>
									<div class="c-slider__slide">
										<a href="<?php echo esc_url( $image['link'] ); ?>" target="_blank">
											<img src="<?php echo esc_url( $image['src'] ); ?>" alt="<?php echo esc_attr( $image['caption'] ); ?>" title="<?php echo esc_attr( $image['caption'] ); ?>">
										</a>
									</div>
								<?php endforeach; ?>
							</div>
						</div><!-- .c-slider -->
					</div><!-- .l-glimpse__content -->
				</div><!-- .l-glimpse__container -->
			</section><!-- .l-glimpse -->
		<?php endif; ?>

		<?php
	endwhile;
endif;
?>

<?php \Variety\Plugins\Variety_500\Templates::footer(); ?>
