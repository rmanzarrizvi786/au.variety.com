<?php
/**
 * Hollywood Executive Profile Single Template.
 *
 * @package pmc-variety-2020
 */

while ( have_posts() ) :
	the_post();

	$executive = \Variety\Inc\Executive::get_instance();
	$executive->set_executive( get_the_ID() );

	$executive_photo = get_post_meta( get_the_ID(), 'photo_url', true );

	?>

	<section class="article-with-sidebar // ">
		<div class="lrv-a-wrapper lrv-u-flex@tablet lrv-u-justify-content-center u-justify-content-flex-end@desktop">
			<div class="article-with-sidebar__inner lrv-u-flex@tablet lrv-u-padding-t-050 u-max-width-1160 u-padding-t-150@tablet lrv-u-width-100p">
				<article id="post-<?php the_ID(); ?>" class="article-with-sidebar__article u-max-width-830 u-padding-r-125@tablet u-margin-r-125@tablet u-padding-r-375@desktop-xl u-margin-r-375@desktop-xl u-border-r-1@tablet u-border-color-brand-secondary-40 lrv-u-flex-grow-1">
					<div class="lrv-u-line-height-normal lrv-u-font-size-18">
						<div class="profile-header lrv-u-margin-b-2 lrv-u-display-flex">
							<?php
							if ( $executive->get_photo_metadata() && $executive_photo ) {
									$profile_image_credit = $executive->get_photo_metadata();
								?>
								<div class="profile-image lrv-u-display-inline-block u-vertical-align-top lrv-u-text-align-center">
									<img
										class="u-width-150 lrv-u-margin-lr-auto"
										src="<?php echo esc_url( $executive_photo ); ?>"
										alt="<?php echo esc_attr( sprintf( '%s - Entertainment Executive', get_the_title() ) ); ?>"
									/>
								</div>
								<?php
							} 
							?>

							<header class="lrv-u-padding-tb-1@mobile-max lrv-u-display-inline-block lrv-u-margin-l-1@desktop">
								<h1 class="lrv-u-font-family-primary lrv-u-font-weight-normal u-font-size-35 "><?php the_title(); ?></h1>
								<h2 class="u-font-family-basic lrv-u-font-size-16 lrv-u-text-transform-uppercase u-font-weight-medium">
									<?php if ( ! empty( $executive->get_job_title() ) ) { ?>
									<div><?php echo esc_html( $executive->get_job_title() ); ?></div>
									<?php } ?>

									<?php if ( ! empty( $executive->get_company_name() ) ) { ?>
										<div><?php echo esc_html( $executive->get_company_name() ); ?></div>
									<?php } ?>

									<?php 
									if ( ! empty( $profile_image_credit ) ) {
										printf( '<div class="a-font-basic-m u-color-pale-sky-2 lrv-u-text-transform-initial lrv-u-margin-t-050">Photo: %s</div>', esc_html( $profile_image_credit ) );
									}
									?>
								</h2>
							</header>
						</div>

						<div class="u-background-color-accent-c-100 lrv-u-padding-a-2 lrv-u-text-align-center u-padding-a-1@mobile-max lrv-u-margin-b-2">
							<img
								class="u-width-142 lrv-u-margin-lr-auto lrv-u-padding-b-050"
								src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/assets/build/svg/variety-insight-logo-color.svg"
							/>

							<div class="lrv-u-font-size-12@mobile-max lrv-u-font-family-secondary lrv-u-font-size-16">
								<?php
								echo esc_html(
									sprintf(
										// translators: %s replaced with Exec name
										__(
											'Access contact info, org charts, active projects and more for %s and 80,000+ other executives and producers.',
											'pmc-variety'
										),
										get_the_title()
									)
								);
								?>
							</div>

							<a
								class="lrv-u-text-transform-uppercase lrv-u-font-weight-bold lrv-u-font-size-12 u-color-brand-secondary-50 lrv-u-font-family-secondary u-letter-spacing-012 lrv-a-icon-after a-icon-long-right-arrow-blue u-color-picked-bluewood:hover lrv-u-padding-t-050 a-content-ignore"
								href="https://www.varietyinsight.com/#utm_source=varietyexec&utm_medium=insightlink&utm_campaign=clicks"
							>
								<?php esc_html_e( 'Visit Variety Insight', 'pmc-variety' ); ?>
							</a>
						</div>

						<section>
							<?php
							$film_credits = $executive->get_film_credits();

							if ( ! empty( $film_credits ) ) {
								?>
							<h2 class="lrv-u-margin-t-2 lrv-u-font-family-primary lrv-u-font-weight-normal u-border-b-6 u-border-color-pale-sky-2 lrv-u-margin-b-050 lrv-u-padding-b-050"><?php esc_html_e( 'Selected Film Credits', 'pmc-variety' ); ?></h2>
							<ul class="lrv-a-unstyle-list lrv-a-grid a-cols2@tablet u-align-items-stretch">
								<?php foreach ( $film_credits as $credit ) { ?>
								<li class="lrv-u-border-b-1 lrv-u-border-color-grey-light">
									<div class="lrv-u-flex lrv-u-align-items-center">
										<?php if ( ! empty( $credit['title'] ) ) { ?>
											<h3 class="lrv-u-padding-r-1"><?php echo esc_html( $credit['title'] ); ?></h3>
										<?php } ?>

										<?php
										if ( ! empty( $credit['production_status'] ) ) {
											$production_status = sanitize_title( $credit['production_status'] );
											$production_class  = ( isset( $credit_status_badges[ $production_status ] ) ) ? $credit_status_badges[ $production_status ] : '';
											?>
										<span class="lrv-u-text-transform-uppercase u-font-family-basic lrv-u-font-size-12 lrv-u-color-grey-dark lrv-u-font-weight-bold"><?php echo esc_html( $credit['production_status'] ); ?></span>
										<?php } ?>
									</div>
									<dl class="lrv-u-flex lrv-u-flex-direction-column lrv-u-font-size-14 lrv-u-padding-b-1 lrv-u-margin-b-1">
										<?php
										if ( ! empty( $credit['companies'] ) ) {
											$companies = array_map(
												function( $company ) {
														return $company['company_name'];
												},
												(array) $credit['companies']
											);
											?>
										<dt class="lrv-u-padding-r-1 lrv-u-font-weight-bold"><?php esc_html_e( 'Companies', 'pmc-variety' ); ?></dt>
										<dd class="lrv-u-margin-b-1 u-padding-r-4@tablet u-padding-r-2" style="margin-inline-start: 0;"><?php echo esc_html( implode( ',', $companies ) ); ?></dd>
										<?php } ?>

										<?php if ( ! empty( $credit['job_title'] ) ) { ?>
										<dt class="lrv-u-padding-r-1 lrv-u-font-weight-bold"><?php esc_html_e( 'Title', 'pmc-variety' ); ?></dt>
										<dd class="lrv-u-margin-b-1 u-padding-r-4@tablet u-padding-r-2" style="margin-inline-start: 0;"><?php echo esc_html( $credit['job_title'] ); ?></dd>
										<?php } ?>

										<?php
										if ( ! empty( $credit['release_date'] ) ) {
											$release_date = is_numeric( $credit['release_date'] ) ? intval( is_numeric( $credit['release_date'] ) ) : strtotime( $credit['release_date'] );
											?>
										<dt class="lrv-u-padding-r-1 lrv-u-font-weight-bold"><?php esc_html_e( 'Release Date', 'pmc-variety' ); ?></dt>
										<dd class="lrv-u-margin-b-1 u-padding-r-4@tablet u-padding-r-2" style="margin-inline-start: 0;"><?php echo esc_html( date( 'F d, Y', $release_date ) ); ?></dd>
										<?php } ?>
									</dl>
								</li>
								<?php }    // end foreach loop for Film credits ?>
							</ul>
							<?php } ?>

							<?php
							$tv_credits = $executive->get_tv_credits();

							if ( ! empty( $tv_credits ) ) {
								?>
							<h2 class="lrv-u-margin-t-2 lrv-u-font-family-primary lrv-u-font-weight-normal u-border-b-6 u-border-color-pale-sky-2 lrv-u-margin-b-1 lrv-u-padding-b-050"><?php esc_html_e( 'Selected TV Credits', 'pmc-variety' ); ?></h2>
							<ul class="lrv-a-unstyle-list lrv-a-grid a-cols2@tablet u-align-items-stretch">
								<?php foreach ( $tv_credits as $credit ) { ?>
									<li class="lrv-u-border-b-1 lrv-u-border-color-grey-light">
										<div class="lrv-u-flex lrv-u-align-items-center">
											<?php if ( ! empty( $credit['title'] ) ) { ?>
												<h3 class="lrv-u-padding-r-1"><?php echo esc_html( $credit['title'] ); ?></h3>
											<?php } ?>

											<?php
											if ( ! empty( $credit['production_status'] ) ) {
												$production_status = sanitize_title( $credit['production_status'] );
												$production_class  = ( isset( $credit_status_badges[ $production_status ] ) ) ? $credit_status_badges[ $production_status ] : '';
												?>
												<span class="lrv-u-text-transform-uppercase u-font-family-basic lrv-u-font-size-12 lrv-u-color-grey-dark lrv-u-font-weight-bold"><?php echo esc_html( $credit['production_status'] ); ?></span>
											<?php } ?>
										</div>
										<dl class="lrv-u-flex lrv-u-flex-direction-column lrv-u-font-size-14 lrv-u-padding-b-1 lrv-u-margin-b-1">
											<?php
											if ( ! empty( $credit['companies'] ) ) {
												$companies = array_map(
													function( $company ) {
															return $company['company_name'];
													},
													(array) $credit['companies']
												);
												?>
												<dt class="lrv-u-padding-r-1 lrv-u-font-weight-bold"><?php esc_html_e( 'Companies', 'pmc-variety' ); ?></dt>
												<dd class="lrv-u-margin-b-1 u-padding-r-4@tablet u-padding-r-2" style="margin-inline-start: 0;"><?php echo esc_html( implode( ',', $companies ) ); ?></dd>
											<?php } ?>

											<?php if ( ! empty( $credit['job_title'] ) ) { ?>
												<dt class="lrv-u-padding-r-1 lrv-u-font-weight-bold"><?php esc_html_e( 'Title', 'pmc-variety' ); ?></dt>
												<dd class="lrv-u-margin-b-1 u-padding-r-4@tablet u-padding-r-2" style="margin-inline-start: 0;"><?php echo esc_html( $credit['job_title'] ); ?></dd>
											<?php } ?>

											<?php
											if ( ! empty( $credit['season_year'] ) && '0000-0000' !== $credit['season_year'] ) {
												?>
												<dt class="lrv-u-padding-r-1 lrv-u-font-weight-bold"><?php esc_html_e( 'Release Date', 'pmc-variety' ); ?></dt>
												<dd class="lrv-u-margin-b-1 u-padding-r-4@tablet u-padding-r-2" style="margin-inline-start: 0;"><?php echo esc_html( $credit['season_year'] ); ?></dd>
											<?php } ?>
										</dl>
									</li>
								<?php }    // end foreach loop for TV credits ?>
							</ul>
							<?php } ?>

							<?php
							$digital_credits = $executive->get_digital_credits();

							if ( ! empty( $digital_credits ) ) {
								?>
							<h2 class="lrv-u-margin-t-2 lrv-u-font-family-primary lrv-u-font-weight-normal u-border-b-6 u-border-color-pale-sky-2 lrv-u-margin-b-050 lrv-u-padding-b-050"><?php esc_html_e( 'Selected Digital Credits', 'pmc-variety' ); ?></h2>
							<ul class="lrv-a-unstyle-list lrv-a-grid a-cols2@tablet u-align-items-stretch">
								<?php foreach ( $digital_credits as $credit ) { ?>
									<li class="lrv-u-border-b-1 lrv-u-border-color-grey-light">
										<div class="lrv-u-flex lrv-u-align-items-center">
											<?php if ( ! empty( $credit['title'] ) ) { ?>
												<h3 class="lrv-u-padding-r-1"><?php echo esc_html( $credit['title'] ); ?></h3>
											<?php } ?>

											<?php
											if ( ! empty( $credit['production_status'] ) ) {
												$production_status = sanitize_title( $credit['production_status'] );
												$production_class  = ( isset( $credit_status_badges[ $production_status ] ) ) ? $credit_status_badges[ $production_status ] : '';
												?>
												<span class="lrv-u-text-transform-uppercase u-font-family-basic lrv-u-font-size-12 lrv-u-color-grey-dark lrv-u-font-weight-bold"><?php echo esc_html( $credit['production_status'] ); ?></span>
											<?php } ?>
										</div>
										<dl class="lrv-u-flex lrv-u-flex-direction-column lrv-u-font-size-14 lrv-u-padding-b-1 lrv-u-margin-b-1">
											<?php if ( ! empty( $credit['networks'] ) ) { ?>
												<dt class="lrv-u-padding-r-1 lrv-u-font-weight-bold"><?php esc_html_e( 'Platform', 'pmc-variety' ); ?></dt>
												<dd class="lrv-u-margin-b-1 u-padding-r-4@tablet u-padding-r-2" style="margin-inline-start: 0;"><?php echo esc_html( implode( ',', (array) $credit['networks'] ) ); ?></dd>
											<?php } ?>

											<?php if ( ! empty( $credit['job_title'] ) ) { ?>
												<dt class="lrv-u-padding-r-1 lrv-u-font-weight-bold"><?php esc_html_e( 'Title', 'pmc-variety' ); ?></dt>
												<dd class="lrv-u-margin-b-1 u-padding-r-4@tablet u-padding-r-2" style="margin-inline-start: 0;"><?php echo esc_html( $credit['job_title'] ); ?></dd>
											<?php } ?>

											<?php
											if (
												! empty( $credit['air_date'] ) || ! empty( $credit['season_premiere_date'] )
												|| ( ! empty( $credit['season_year'] ) && '0000-0000' !== $credit['season_year'] )
											) {
												$release_date = ( ! empty( $credit['air_date'] ) ) ? date( 'F d, Y', strtotime( $credit['air_date'] ) ) : '';
												$release_date = ( empty( $release_date ) && ! empty( $credit['season_premiere_date'] ) ) ? date( 'F d, Y', strtotime( $credit['season_premiere_date'] ) ) : '';
												$release_date = ( empty( $release_date ) ) ? $credit['season_year'] : $release_date;
												?>
												<dt class="lrv-u-padding-r-1 lrv-u-font-weight-bold"><?php esc_html_e( 'First Launched', 'pmc-variety' ); ?></dt>
												<dd class="lrv-u-margin-b-1 u-padding-r-4@tablet u-padding-r-2" style="margin-inline-start: 0;"><?php echo esc_html( $release_date ); ?></dd>
											<?php } ?>
										</dl>
									</li>
								<?php }    // end foreach loop for Digital credits ?>
							</ul>
							<?php } ?>



							<div class="lrv-u-flex lrv-u-flex-direction-column@mobile-max lrv-u-align-items-center u-background-color-accent-c-100 lrv-u-padding-a-2 u-padding-a-1@mobile-max lrv-u-text-align-center@mobile-max lrv-u-margin-t-2">
								<div class="u-width-132 lrv-u-flex-shrink-0 lrv-u-border-r-1@desktop lrv-u-border-color-grey lrv-u-padding-t-050 lrv-u-padding-r-1 lrv-u-padding-r-00@mobile-max lrv-u-padding-b-050 lrv-u-margin-r-1 lrv-u-margin-r-00@mobile-max">
									<div class="lrv-u-font-size-12 lrv-u-padding-b-025">
										<?php esc_html_e( 'Data Provided By', 'pmc-variety' ); ?>
									</div>
									<img
										class=""
										src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/assets/build/svg/variety-bi-logo.png"
									/>
								</div>
								<div class="lrv-u-font-size-12@mobile-max lrv-u-font-family-secondary lrv-u-font-size-16">
									<?php
									echo esc_html(
										sprintf(
											// translators: %s replaced with Exec name
											__(
												'Want to contact %s directly? Get phone numbers, email addresses, org charts and more from virtually everyone working in entertainment!',
												'pmc-variety'
											),
											get_the_title()
										)
									);
									?>
									<a
									class="lrv-u-flex u-justify-content-center@mobile-max lrv-u-text-transform-uppercase lrv-u-font-weight-bold lrv-u-font-size-12 u-color-brand-secondary-50 lrv-u-font-family-secondary u-letter-spacing-012 lrv-a-icon-after a-icon-long-right-arrow-blue u-color-picked-bluewood:hover lrv-u-padding-t-050 a-content-ignore"
									href="https://www.varietyinsight.com/#utm_source=varietyexec&utm_medium=insightlink&utm_campaign=clicks"
									>
										<?php esc_html_e( 'Visit Variety Insight', 'pmc-variety' ); ?>
									</a>
								</div>
							</div>
						</section>

					</div>
				</article>
				<aside class="article-with-content__sidebar lrv-u-height-100p u-width-300@tablet lrv-u-flex-shrink-0 ">
					<?php if ( is_active_sidebar( 'global-sidebar' ) ) : ?>
						<?php dynamic_sidebar( 'global-sidebar' ); ?>
					<?php endif; ?>
				</aside>
			</div>
		</div>
	</section>

	<?php

endwhile;
