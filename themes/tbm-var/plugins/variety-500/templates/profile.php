<?php
/**
 * V500 Profile Template
 *
 * Displays the single profile page.
 *
 * @package pmc-variety-2017
 * @since 1.0
 */

$executive      = \Variety\Plugins\Variety_500\Profile::get_instance();
$instagram_feed = $executive->get_instagram_feed();
$country_abbrs  = $executive->get_country_abbreviations();
$current_year   = get_option( 'variety_500_year', date( 'Y' ) );
$sharing_icons  = \Variety\Plugins\Variety_500\Sharing::get_instance()->get_icons();

\Variety\Plugins\Variety_500\Templates::header();
\Variety\Plugins\Variety_500\Templates::site_header();

/*
 * Implement the WordPress loop to display the bio information. We really only
 * use the `post_content` and `the_title` fields to pull bio data, the rest of
 * the data is pulled from meta fields.
 */
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>

<nav class="c-profile-nav" data-trigger="profile-nav-manager">
	<ol class="c-profile-nav__list">
		<li class="c-profile-nav__item">
			<a href="#biography" class="c-profile-nav__link"><?php esc_html_e( 'Biography', 'pmc-variety' ); ?></a>
		</li>
		<?php if ( $executive->has_career_highlights() ) : ?>
			<li class="c-profile-nav__item">
				<a href="#career" class="c-profile-nav__link"><?php esc_html_e( 'Career', 'pmc-variety' ); ?></a>
			</li>
		<?php endif; ?>
		<?php if ( $executive->has_education() ) : ?>
			<li class="c-profile-nav__item">
				<a href="#education" class="c-profile-nav__link"><?php esc_html_e( 'Education', 'pmc-variety' ); ?></a>
			</li>
		<?php endif; ?>
		<?php if ( ! empty( $executive->get_honors() ) ) : ?>
			<li class="c-profile-nav__item">
				<a href="#honors" class="c-profile-nav__link"><?php esc_html_e( 'Variety Honors', 'pmc-variety' ); ?></a>
			</li>
		<?php endif; ?>
		<?php if ( $executive->has_philanthropy() ) : ?>
			<li class="c-profile-nav__item">
				<a href="#philanthropy" class="c-profile-nav__link"><?php esc_html_e( 'Philanthropy', 'pmc-variety' ); ?></a>
			</li>
		<?php endif; ?>
		<?php if ( $executive->has_news() ) : ?>
			<li class="c-profile-nav__item">
				<a href="#news" class="c-profile-nav__link"><?php esc_html_e( 'News', 'pmc-variety' ); ?></a>
			</li>
		<?php endif; ?>
		<?php if ( ! empty( $instagram_feed ) ) : ?>
			<li class="c-profile-nav__item">
				<a href="#social-glimpse" class="c-profile-nav__link"><?php esc_html_e( 'Social Glimpse', 'pmc-variety' ); ?></a>
			</li>
		<?php endif; ?>
		<?php if ( $executive->has_survey() ) : ?>
			<li class="c-profile-nav__item">
				<a href="#qa" class="c-profile-nav__link"><?php esc_html_e( 'Q&amp;A', 'pmc-variety' ); ?></a>
			</li>
		<?php endif; ?>
		<?php if ( $executive->has_related_profiles() ) : ?>
			<li class="c-profile-nav__item">
				<a href="#related-people" class="c-profile-nav__link"><?php esc_html_e( 'Related People', 'pmc-variety' ); ?></a>
			</li>
		<?php endif; ?>
	</ol>
</nav><!-- .c-profile-nav -->

<section class="l-profile">
	<div class="l-profile__container">
		<div class="l-profile__image-area">
			<div class="c-profile-card c-profile-main">
				<figure class="c-profile-card__media">
					<?php
					// Display featured image if it's exists else get image from meta.
					if ( has_post_thumbnail() ) {

						$profile_image_url    = get_the_post_thumbnail_url( null, 'full' );
						$profile_image_credit = get_post_meta( get_post_thumbnail_id(), '_image_credit', true );

					} else {
						$profile_image_url = $executive->get_honoree_image();
					}

					if ( ! empty( $profile_image_url ) ) {

						printf( '<img src="%1$s" alt="%2$s" />',
							esc_url( $profile_image_url ),
							the_title_attribute(
								[
									'echo' => false,
								]
							)
						);

					}
					?>
				</figure>

				<?php
				// Display photo credit.
				if ( ! empty( $profile_image_credit ) ) {
					printf( '<span class="c-profile-card__media-credit">%s</span>', esc_html( $profile_image_credit ) );
				}

				$countries = array_shift( $executive->get_country_of_citizenship() );

				if ( false !== $countries && is_string( $countries ) ) : ?>
					<div class="c-profile-card__country">
						<?php
						// Explode countries into an array to handle with a foreach loop.
						$countries = explode( '|', $countries );

						if ( ! empty( $countries ) && is_array( $countries ) ) {
							foreach ( $countries as $citizenship ) {
								// Clean up the country name.
								$citizenship  = trim( $citizenship );
								$country_slug = \Variety\Plugins\Variety_500\Countries::get_country_slug( $citizenship );

								// Ensure we have an abbreviation for this country.
								if ( empty( $country_slug ) ) {
									continue;
								}

								// Check if the file exists.
								$file = untrailingslashit( VARIETY_500_ROOT ) . '/assets/images/flags/' . $country_slug . '.png';
								if ( ! file_exists( $file ) ) {
									continue;
								}

								// Use the plugin URL instead of the directory.
								$url        = untrailingslashit( VARIETY_500_PLUGIN_URL ) . '/assets/images/flags/' . $country_slug . '.png';
								$search_url = \Variety\Plugins\Variety_500\Templates::get_search_url( array( 'country_of_citizenship' => $citizenship ) );
								?>
								<a href="<?php echo esc_url( $search_url ); ?>" class="c-profile-card__country-flag"><img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_html( $citizenship ); ?>" /></a>
								<?php
							}
						}
						// moved the badge inside the div.c-profile-card__country to position badge in relation to the last flag to display
						// the working assumption is that the Honoree will always have at least one country flag (confirmed with Product)
						$exec_year = $executive->honoree_current_year();
						// if honoree is part of this year's group, display badge with year from settings
						if ( ! empty( $exec_year ) && ( 2017 < absint( $current_year ) ) ) {
							$honor_year = $exec_year;

							PMC::render_template(
								sprintf( '%s/plugins/variety-500/templates/partials/honor-badge.php', untrailingslashit( CHILD_THEME_PATH ) ),
								[ 'year' => $honor_year ],
								true
							);
						}
						?>
					</div>
				<?php endif; ?>
				</div><!-- .c-profile-card -->

		</div><!-- .l-profile__image-area -->
		<div class="l-profile__copy-area" id="biography">

			<article class="c-profile-bio">
				<header>
					<?php if ( ! empty( $executive->get_companies() ) ) : ?>
						<p class="c-profile-bio__company"><?php echo esc_html( $executive->get_companies() ); ?></p>
					<?php endif; ?>
					<h1 class="c-profile-bio__heading" data-trigger="fit-text-to-container"><?php echo wp_kses_post( str_replace( ' ', '<br>', get_the_title() ) ); ?></h1>
					<?php if ( ! empty( $executive->get_job_title() ) ) : ?>
						<h3 class="c-profile-bio__job-title"><?php echo esc_html( $executive->get_job_title() ); ?></h3>
					<?php endif; ?>
					<?php if ( $executive->has_social() ) : ?>
						<ul class="l-list l-list--inline">
							<?php if ( ! empty( $executive->get_twitter_url() ) ) : ?>
								<li class="l-list__item l-list__item--inline">
									<a href="<?php echo esc_url( $executive->get_twitter_url() ); ?>" target="_blank" class="c-profile-bio__social-link">
										<span class="c-social-icon c-social-icon__twitter">
											<span class="screen-reader-text"><?php esc_html_e( 'Twitter', 'pmc-variety' ); ?>: </span>
										</span>
										<?php echo esc_html( $executive->get_twitter_handle() ); ?>
									</a>
								</li>
							<?php endif; ?>
							<?php if ( ! empty( $executive->get_instagram_url() ) ) : ?>
								<li class="l-list__item l-list__item--inline">
									<a href="<?php echo esc_url( $executive->get_instagram_url() ); ?>" target="_blank" class="c-profile-bio__social-link">
										<span class="c-social-icon c-social-icon__instagram">
											<span class="screen-reader-text"><?php esc_html_e( 'Instagram', 'pmc-variety' ); ?></span>
										</span>
										<?php echo esc_html( $executive->get_instagram_username() ); ?>
									</a>
								</li>
							<?php endif; ?>
						</ul>
					<?php endif; ?>
				</header>

				<?php if ( ! empty( $sharing_icons ) && is_array( $sharing_icons ) ) : ?>
					<div class="c-profile-bio__social-bar">
						<nav class="c-social-bar">
							<h4 class="c-social-bar__heading"><?php esc_html_e( 'Share', 'pmc-variety' ); ?></h4>

								<div class="c-social-bar__list">
									<ul class="l-list l-list--inline l-list--condensed" data-trigger="share-links-manager">

										<?php // This is validated as an array above. ?>
										<?php foreach ( $sharing_icons as $id => $share_icon ) : ?>
											<?php
											if ( \PMC\Social_Share_Bar\Config::CM === $id ) {
												continue;
											}
											?>
											<li class="l-list__item l-list__item--inline l-list__item--condensed">
												<?php echo wp_kses_post( \Variety\Plugins\Variety_500\Sharing::get_instance()->assemble_link( $share_icon, $id ) ); ?>
											</li>
										<?php endforeach; ?>
									</ul><!-- .l-list l-list--inline l-list--condensed -->
								</div>

						</nav><!-- .c-social-bar -->
					</div><!-- .c-profile-bio__social-bar -->
				<?php endif; ?>

				<div class="c-profile-bio__text">
					<?php the_content(); ?>
				</div>
			</article><!-- .c-profile-bio -->

		</div><!-- .l-profile__copy-area -->
		<div class="l-profile__meta-area">

			<?php if ( $executive->has_career_highlights() ) : ?>
				<div class="l-profile__meta-block">

					<div class="c-profile-meta" id="career">
						<span class="c-ordinal"><!-- 02 --></span>
						<h4 class="c-profile-meta__caption"><?php esc_html_e( 'Career', 'pmc-variety' ); ?></h4>

						<ul class="l-list">
							<?php
							// This is verified as an array in has_career_highlights() above.
							$highlights = $executive->get_career_highlights();
							foreach ( $highlights as $highlight ) :
								if ( ! empty( $highlight ) && is_array( $highlight ) ) :
									// The variable $company can represent a company, track, or album array.
									foreach ( $highlight as $company ) :
										if ( ! empty( $company ) && is_array( $company ) ) :
											// Locate the strings we need.
											$img_url = $executive::find_career_highlight_image_url( $company );
											$title   = $executive::find_career_highlight_title( $company );
											// Must have at least a title in order to render.
											if ( empty( $title ) ) {
												continue;
											}
											?>
											<li class="l-list__item">
												<?php if ( ! empty( $img_url ) ) : ?>
													<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="c-profile-meta__logo" />
												<?php endif; ?>
												<?php echo esc_html( $title ); ?>
											</li>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul><!-- .l-list -->
					</div><!-- .c-profile-meta -->
				</div><!-- .l-profile__meta-block -->
			<?php endif; ?>
			<?php
			// in the loop, get the ID here to pass as argument to the class methods
			$honored_years = $executive->honored_years( get_the_ID() );
			if ( $executive->has_education() || ! empty( $executive->get_honors() ) || $executive->has_philanthropy() || ! empty( $honored_years ) ) :
				?>
				<div class="l-profile__meta-block">

					<?php if ( $executive->has_education() ) : ?>
						<div class="c-profile-meta" id="education">
							<span class="c-ordinal"><!-- 04 --></span>
							<h4 class="c-profile-meta__caption"><?php esc_html_e( 'Education', 'pmc-variety' ); ?></h4>

							<ul class="l-list">
								<?php if ( $executive->get_education1() ) : ?>
									<li class="l-list__item">
										<?php echo esc_html( $executive->get_education1() ); ?>
									</li>
								<?php endif; ?>
								<?php if ( $executive->get_education2() ) : ?>
									<li class="l-list__item">
										<?php echo esc_html( $executive->get_education2() ); ?>
									</li>
								<?php endif; ?>
								<?php if ( $executive->get_education3() ) : ?>
									<li class="l-list__item">
										<?php echo esc_html( $executive->get_education3() ); ?>
									</li>
								<?php endif; ?>
							</ul><!-- .l-list -->

						</div><!-- .c-profile-meta -->
					<?php endif; ?>
					<?php if ( ! empty( $executive->get_honors() ) || ! empty( $honored_years ) ) : ?>
						<div class="c-profile-meta" id="honors">
							<span class="c-ordinal"><!-- 03 --></span>
							<h4 class="c-profile-meta__caption c-profile-meta__caption--honors"><?php esc_html_e( 'Variety Honors', 'pmc-variety' ); ?></h4>

							<ul class="l-list">
							<?php
								// display previous years nominated, exclude current year
							if ( ! empty( $honored_years ) && is_array( $honored_years ) ) :
								foreach ( $honored_years as $year ) :
									if ( $year < $current_year ) {
										?>
										<li class="l-list__item">
										<?php echo esc_html( $year, 'pmc-variety' ) . ' Variety500 Honoree'; // nothing to translate here -- the "Honors" and $year are a Name ?>
										</li>
									<?php } ?>
								<?php endforeach; ?>
							<?php endif; ?>

							<?php
								// display honors
								$honors = explode( ',', $executive->get_honors() );
								if ( ! empty( $honors ) && is_array( $honors ) ) :
									foreach ( $honors as $honor ) :
										// Sanity check.
										if ( empty( $honor ) ) {
											continue;
										}
										// Clean up the string.
										$honor = trim( $honor );
										?>
										<li class="l-list__item">
											<?php echo esc_html( $honor ); ?>
										</li>
									<?php endforeach; ?>
								<?php endif; ?>
								</ul><!-- .l-list -->

						</div><!-- .c-profile-meta -->
					<?php endif; ?>
					<?php if ( $executive->has_philanthropy() ) : ?>
						<div class="c-profile-meta" id="philanthropy">
							<span class="c-ordinal"><!-- 05 --></span>
							<h4 class="c-profile-meta__caption  c-profile-meta__caption--philanthropy"><?php esc_html_e( 'Philanthropy', 'pmc-variety' ); ?></h4>

							<ul class="l-list">
								<?php
								// Method has_philanthropy() above checks for a non-empty array.
								$items = $executive->get_philanthropy();
								foreach ( $items as $title => $url ) : ?>
									<?php
									if ( empty( $title ) ) {
										continue;
									} ?>
									<li class="l-list__item">

										<?php if ( ! empty( $url ) ) : ?>
											<a href="<?php echo esc_url( $url ); ?>" target="_blank" title="<?php echo esc_attr( $title ); ?>">
										<?php endif; ?>
											<?php echo esc_html( $title ); ?>
										<?php if ( ! empty( $url ) ) : ?>
											</a>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul><!-- .l-list -->

						</div><!-- .c-profile-meta -->
					<?php endif; ?>

				</div><!-- .l-profile__meta-block -->
			<?php endif; ?>
				<div class="c-profile-insight-promo">
				<?php
					$insight_url = get_option( 'variety_500_vi_cta_link' ); // get url for cta below

					PMC::render_template(
						sprintf( '%s/plugins/variety-500/templates/partials/varietyinsight-svglogo.php', untrailingslashit( CHILD_THEME_PATH ) ),
						[
							'width'  => '175',
							'height' => '35',
						],
						true
					);
				?>
				<?php // translators: %1$s replaced with first name and %2$s with last name ?>
					<div class="c-profile-insight-promo__text"><?php echo wp_kses_post( sprintf( __( 'Interested in org charts, contact info and more for %1$s %2$s', 'pmc-variety' ), $executive->get_first_name(), $executive->get_last_name() ) ); ?></div>
					<?php if ( ! empty( $insight_url ) ) : ?>
					<div class="c-profile-insight-promo__cta">
						<a href="<?php echo esc_url( $insight_url ); ?>" target="_blank">
						<?php esc_html_e( 'Visit Variety Insight', 'pmc-variety' ); ?>
						</a>
					</div>
					<?php endif; ?>
				</div><!-- .c-profile-insight-promo -->
		</div><!-- .l-profile__meta-area -->
		<?php if ( $executive->has_jobs() ) : ?>
			<div class="l-profile__jobs-area">

				<div class="c-profile-jobs">
					<ul class="l-list">
						<?php
						$jobs_types = array();
						$line_of_work = $executive->get_line_of_work();
						$media_category = $executive->get_media_category();

						if ( $executive->has_line_of_work() ) {
							$jobs_types['line_of_work'] = $line_of_work;
						}
						if ( $executive->has_media_category() ) {
							$jobs_types['media_category'] = $media_category;
						}

						foreach ( $jobs_types as $job_type => $jobs ) :
							foreach ( $jobs as $job ) :
								$job = trim( $job );
								$icon_class = $executive->get_job_icon_class( $job );
								$search_url = \Variety\Plugins\Variety_500\Templates::get_search_url( array( $job_type => $job ) );

								// Modify Labels.
								if ( 'Live Entertainment' === $job ) {
									$job = __( 'Live Ent.', 'pmc-variety' );
								} elseif ( 'Technology' === $job ) {
									$job = __( 'Tech', 'pmc-variety' );
								}
								?>
								<li class="l-list__item l-list__item--condensed">
									<a href="<?php echo esc_url( $search_url ); ?>">
										<span class="c-profile-jobs__icon c-profile-jobs__icon--<?php echo esc_attr( $icon_class ); ?>"><?php echo esc_html( $job ); ?></span>
									</a>
								</li>
							<?php
							endforeach;
						endforeach; ?>
					</ul>
				</div><!-- .c-profile-jobs -->

			</div><!-- .l-profile__jobs-area -->
		<?php endif; ?>
	</div><!-- .l-profile__container -->
</section><!-- .l-profile -->
<?php if ( $executive->has_news() ) :
	// The above checked that $articles is an array.
	$articles = $executive->get_related_news();
	// Get up to 4 items.
	$articles = array_slice( $articles, 0, 4 );
		?>
	<section class="l-profile-news" id="news">
		<div class="l-profile-news__container">
			<header class="l-profile-news__heading">
				<span class="c-ordinal"><!-- 06 --></span>
				<h3 class="c-heading c-heading--profile-news">
					<?php echo wp_kses_post( sprintf( __( 'News %1$sfrom Variety%2$s', 'pmc-variety' ), '<span>', '</span>' ) ); ?>
				</h3>
			</header><!-- .l-profile-news__heading -->

			<?php if ( ! empty( $articles[0] ) && is_array( $articles[0] ) ) : ?>
				<?php // Ensure the 'title' value is at least set, even if it's empty. ?>
				<?php $title = ( ! empty( $articles[0]['title'] ) ) ? $articles[0]['title'] : ''; ?>
				<article>
					<figure class="l-profile-news__image">
						<?php if ( ! empty( $articles[0]['image'] ) ) :
								// Remove any query params to get the largest image size.
								$image_array = explode( '?', $articles[0]['image'] );
								$image_url = $image_array[0];
							?>
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
						<?php endif; ?>
					</figure><!-- .l-profile-news__image -->

					<div class="l-profile-news__post">
						<div class="l-profile-news__post-wrapper">
							<h4 class="c-profile-news__post-heading">
								<?php if ( ! empty( $articles[0]['url'] ) ) : ?>
									<a href="<?php echo esc_url( $articles[0]['url'] ); ?>">
								<?php endif; ?>
								<?php echo esc_html( $title ); ?>
								<?php if ( ! empty( $articles[0]['url'] ) ) : ?>
									</a>
								<?php endif; ?>
							</h4>
							<?php if ( ! empty( $articles[0]['contents'] ) ) :
								// Show only 450 words.
								$content = wp_trim_words( $articles[0]['contents'], 80, '...' );
								// Convert breaking tags to paragraph tags.
								$content = preg_replace( '/(<br\s*\/?>)/', '</p><p>', $content );

								// Clean up the paragraph tags.
								$content = wpautop( $content );

								// Get up to two complete paragraphs of content.
								$delimiter = '</p>';
								$pos = strpos( $content, $delimiter );

								if ( false !== $pos ) {
									$offset   = $pos + strlen( $delimiter );
									$next_pos = strpos( $content, $delimiter, $offset );

									// Find the second paragraph.
									$next_offset = $next_pos + strlen( $delimiter );
									if ( false !== $next_pos ) {
										$content = substr( $content, 0, $next_offset );
									} else {
										$content = substr( $content, 0, $offset );
									}
								}
								?>
								<div class="c-profile-news__post-content">
									<?php echo wp_kses_post( $content ); ?>
								</div>
							<?php endif; ?>
						</div><!-- .l-profile-news__post-wrapper -->
					</div><!-- .l-profile-news__post -->
				</article>
				<?php unset( $articles[0] ); ?>
			<?php endif; ?>
			<?php if ( ! empty( $articles ) && is_array( $articles ) ) : ?>
				<div class="l-profile-news__more">
					<h4><?php esc_html_e( 'MORE NEWS', 'pmc-variety' ); ?></h4>
				</div>
				<ul class="l-profile-news__list">
					<?php foreach ( $articles as $article ) :
						if ( empty( $article['url'] ) || empty( $article['title'] ) ) {
							continue;
						}
						?>
						<li class="l-profile-news__list-item">
							<a href="<?php echo esc_url( $article['url'] ); ?>" class="c-profile-news__related-news">
								<?php echo esc_html( ucwords( str_replace( '-', ' ', $article['title'] ) ) ); ?>
							</a>
						</li><!-- .l-profile-news__list-item -->
					<?php endforeach; ?>
				</ul><!-- .l-profile-news__list -->
			<?php endif; ?>
		</div><!-- .l-profile-news__container -->
	</section><!-- .l-profile-news -->
<?php endif; ?>

<?php if ( ! empty( $instagram_feed ) && is_array( $instagram_feed ) ) : ?>
<section class="l-profile-social-glimpse" id="social-glimpse" data-trigger="social-glimpse-manager">
	<div class="l-profile-social-glimpse__container">
		<header class="l-profile-social-glimpse__header">

			<span class="c-ordinal c-ordinal--inverse"><!-- 07 --></span>
			<h3 class="c-heading c-heading--profile-social-glimpse">
				<?php esc_html_e( 'Social Glimpse', 'pmc-variety' ); ?>
			</h3>

		</header><!-- .l-profile-social-glimpse__header -->
		<div class="l-profile-social-glimpse__content">
			<ul class="l-profile-social-glimpse__list" data-slides>
				<?php foreach ( $instagram_feed as $image ) : ?>
				<li class="l-profile-social-glimpse__item">
					<a href="<?php echo esc_url( $image['link'] ); ?>" target="_blank">
						<img src="<?php echo esc_url( $image['src'] ); ?>" alt="<?php echo esc_url( $image['caption'] ); ?>" title="<?php echo esc_url( $image['caption'] ); ?>" />
					</a>
				</li>
				<?php endforeach; ?>
			</ul><!-- .l-profile-social-glimpse__list -->
		</div><!-- .l-profile-social-glimpse__content -->
		<nav>
			<button class="l-profile-social-glimpse__nav l-profile-social-glimpse__nav--prev" data-slider-trigger="prev">
				<span href="#" class="c-nav-arrow c-nav-arrow--prev">
					<span class="screen-reader-text"><?php esc_html_e( 'Previous items', 'pmc-variety' ); ?></span>
				</span>
			</button><!-- .l-profile-social-glimpse__nav.l-profile-social-glimpse__nav--prev -->
			<button class="l-profile-social-glimpse__nav l-profile-social-glimpse__nav--next" data-slider-trigger="next">
				<span class="c-nav-arrow c-nav-arrow--next">
					<span class="screen-reader-text"><?php esc_html_e( 'Next items', 'pmc-variety' ); ?></span>
				</span>
			</button><!-- .l-profile-social-glimpse__nav.l-profile-social-glimpse__nav--next -->
		</nav>
	</div><!-- .l-profile-social-glimpse__container -->
</section>
<?php endif; ?>

<?php if ( ! empty( $executive->get_survey_advice() ) && ! empty( $executive->get_survey_inspiration() ) ) : ?>
<section class="l-profile-qa" id="qa">
	<header class="l-profile-qa__header">

		<span class="c-ordinal"><!-- 08 --></span>
		<h3 class="c-heading c-heading--profile-qa">
			Q<span>&amp;</span>A
		</h3>

	</header><!-- .l-profile-qa__header -->
	<dl class="l-profile-qa__content">
		<?php if ( ! empty( $executive->get_survey_advice() ) ) : ?>
			<dt class="c-profile-qa__question">
				<p><?php esc_html_e( 'What’s the best piece of advice you’ve been given in your career?', 'pmc-variety' ); ?></p>
			</dt>
			<dd class="c-profile-qa__answer">
				<p>&ldquo;<?php echo esc_html( $executive->get_survey_advice() ); ?>&rdquo;</p>
			</dd>
		<?php endif; ?>
		<?php if ( ! empty( $executive->get_survey_inspiration() ) ) :  ?>
			<dt class="c-profile-qa__question">
				<p><?php esc_html_e( 'What or who inspires you?', 'pmc-variety' ); ?></p>
			</dt>
			<dd class="c-profile-qa__answer">
				<p>&ldquo;<?php echo esc_html( $executive->get_survey_inspiration() ); ?>&rdquo;</p>
			</dd>
		<?php endif; ?>
	</dl><!-- .l-profile-qa__content -->
</section><!-- .l-profile-qa -->
<?php endif; ?>


<?php if ( $executive->has_related_profiles() ) : ?>
<section class="l-profile-related" id="related-people">
	<div class="l-profile-related__container">
		<header class="l-profile-related__header">

			<span class="c-ordinal"><!-- 09 --></span>
			<h3 class="c-heading c-heading--profile-related"><?php esc_html_e( 'Related People', 'pmc-variety' ); ?></h3>

		</header><!-- .l-profile-related__header -->
		<ul class="l-profile-related__content u-bleed--25 u-padding--l-10">
			<?php
			// Get a maximum of four profiles.  has_related_profiles() checks for an array.
			$profiles = array_slice( $executive->get_related_profiles(), 0, 4 );
			foreach ( $profiles as $profile ) :
				// Note that key names match what is coming from the API, and not the post meta.
						$name        = ( ! empty( $profile['first_name'] ) ) ? $profile['first_name'] : '';
						$name       .= ( ! empty( $profile['last_name'] ) ) ? ' ' . $profile['last_name'] : '';
						$variety_id  = ! empty( $profile['variety_id'] ) ? $profile['variety_id'] : '';
						$profile_url = $executive::find_related_profile_url( $variety_id );
						$profile_id  = $executive::get_exec_profile_id( $variety_id );
						$new_name    = $executive->new_profile( $profile_id );
				?>

				<li class="l-profile-related__item">
					<div class="c-profile-card c-profile-card--search-result u-max-width--300@until-tablet">

						<figure class="c-profile-card__media">
							<?php if ( ! empty( $profile_url ) && ! empty( $profile['honoree_image'] ) ) : ?>
								<a href="<?php echo esc_url( $profile_url ); ?>">
									<img src="<?php echo esc_url( $profile['honoree_image'] ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
								</a>
							<?php endif; ?>
						</figure>

						<?php if ( ! empty( $profile['country_of_citizenship'] ) ) :
							// Explode countries into an array to handle with a foreach loop.
							$countries = explode( '|', $profile['country_of_citizenship'] );

							// Only use the first country's flag because of space restrictions.
							if ( ! empty( $countries[0] ) ) :
								// Clean up the country name.
								$citizenship = trim( $countries[0] );
								$country_slug = \Variety\Plugins\Variety_500\Countries::get_country_slug( $citizenship );

								// Ensure we have an abbreviation for this country.
								if ( empty( $country_slug ) ) {
									continue;
								}

								// Check if the file exists.
								$file = untrailingslashit( VARIETY_500_ROOT ) . '/assets/images/flags/' . $country_slug . '.png';
								if ( ! file_exists( $file ) ) {
									continue;
								}

								// Use the plugin URL instead of the directory.
								$url = untrailingslashit( VARIETY_500_PLUGIN_URL ) . '/assets/images/flags/' . $country_slug . '.png';
								?>
								<div class="c-profile-card__country">
									<a href="#flag" class="c-profile-card__country-flag"><img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_html( $citizenship ); ?>" /></a>
								</div>
							<?php endif;
						endif;?>
						<?php
								// assign class value if new, otherwise, empty string
								$new_profile_class = '';
								if ( $new_name ) {
									$new_profile_class = 'c-profile-new';
								}
								?>
						<div class="c-profile-card__body <?php echo esc_html( $new_profile_class ); ?>">
							<?php if ( ! empty( $profile_url ) ) : ?>
								<a href="<?php echo esc_url( $profile_url ); ?>">
							<?php endif; ?>
							<?php if ( ! empty( $profile['companies'] ) && is_array( $profile['companies'] ) ) :
								$companies = wp_list_pluck( $profile['companies'] , 'company_name' );
								$companies = implode( ', ', $companies );
								if ( is_string( $companies ) ) :
									?>
									<span class="c-profile-card__caption"><?php echo esc_html( $companies ); ?></span>
								<?php endif; ?>
							<?php endif; ?>
								<h4 class="c-profile-card__heading"><?php echo esc_html( $name ); ?></h4>
									<?php if ( ! empty( $profile['job_title'] ) ) : ?>
								<span class="c-profile-card__caption c-profile-card__caption--alt"><?php echo esc_html( $profile['job_title'] ); ?></span>
									<?php endif; ?>
								<?php
										if ( ! empty( $profile['brief_synopsis'] ) ) :
											?>
										<span class="c-profile-card__synopsis c-profile-card__synopsis--alt">
											<?php echo esc_html( $profile['brief_synopsis'] ); ?>
										</span>
								<?php endif; ?>
							<?php if ( ! empty( $profile_url ) ) : ?>
								</a>
							<?php endif; ?>
						</div>

					</div><!-- .c-profile-card.c-profile-card--search-result -->
				</li>
			<?php endforeach; ?>
		</ul><!-- .l-profile-related__content -->
	</div><!-- .l-profile-related__container -->
</section><!-- .l-profile-related -->
<?php endif; ?>


<section class="l-profile-deep-dive" id="deep-dive">
	<div class="l-profile-deep-dive__wrapper">
		<div class="c-profile-deep-dive__logo">
			<?php
				PMC::render_template(
					sprintf( '%s/plugins/variety-500/templates/partials/varietyinsight-svglogo.php', untrailingslashit( CHILD_THEME_PATH ) ),
					[
						'width'  => '319',
						'height' => '70',
					],
					true
				);
			?>
		</div><!-- .c-profile-deep-dive__logo -->
		<div class="l-profile-deep-dive__content">
			<div class="c-profile-deep-dive">
				<?php
				// translators: %1$s replaced with first name and %2$s with last name
				?>
				<p><?php echo wp_kses_post( sprintf( __( 'Want more information on %1$s %2$s?', 'pmc-variety' ), $executive->get_first_name(), $executive->get_last_name() ) ); ?></p>
				<?php /* translators: %1$s replaced with first name */ ?>
				<p><?php echo wp_kses_post( sprintf( __( 'Sign up for a free trial to view %1$s\'s contact info, company details, production deals, & more', 'pmc-variety' ), $executive->get_first_name() ) ); ?>  </p>
			</div>
			<div class="c-profile-deep-dive__more">
				<?php
				$url = get_option( 'variety_500_vi_cta_link' );
				if ( ! empty( $url ) ) :
					?>
					<a href="<?php echo esc_url( $url ); ?>" target="_blank">
					<?php esc_html_e( 'View More', 'pmc-variety' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div><!-- .l-profile-deep-dive__content -->
	</div><!-- .l-profile-deep-dive__wrapper -->
</section><!-- .l-profile-deep-dive -->


<?php if ( ! empty( $sharing_icons ) && is_array( $sharing_icons ) ) : ?>
	<nav class="c-social-bar c-social-bar--mobile">
		<h4 class="c-social-bar__heading"><?php esc_html_e( 'Share', 'pmc-variety' ); ?></h4>

			<div class="c-social-bar__list">
				<ul class="l-list l-list--inline l-list--condensed" data-trigger="share-links-manager">
					<?php // This is validated as an array above. ?>
					<?php foreach ( $sharing_icons as $id => $share_icon ) : ?>
						<?php
						if ( \PMC\Social_Share_Bar\Config::CM === $id ) {
							continue;
						}
						?>
						<li class="l-list__item l-list__item--inline l-list__item--condensed">
							<?php echo wp_kses_post( \Variety\Plugins\Variety_500\Sharing::get_instance()->assemble_link( $share_icon, $id ) ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

	</nav><!-- .c-social-bar -->
<?php endif; ?>

<?php
	endwhile;
endif;
?>

<?php \Variety\Plugins\Variety_500\Templates::footer(); ?>
