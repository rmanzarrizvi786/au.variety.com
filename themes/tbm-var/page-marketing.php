<?php
/**
 * Template Name: Marketing Page
 *
 * @since   2019-03-29
 *
 * @package pmc-variety-2017
 */

?>
<!DOCTYPE html>

<!--[if lt IE 7]>
<html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if (IE 7)&!(IEMobile)]>
<html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if (IE 8)&!(IEMobile)]>
<html <?php language_attributes(); ?> class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]>
<html <?php language_attributes(); ?> class="no-js"><![endif]-->

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

	<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>

	<?php wp_head(); ?>
	<?php do_action( 'pmc_tags_head' ); ?>
</head>

<body <?php body_class(); ?>>
<?php do_action( 'pmc-tags-top' ); // phpcs:ignore ?>
<div id="site-wrapper">
	<div class="l-page l-page--marketing">

		<div class="l-page__section l-page__section--background">
			<div class="marketing-landing">
				<div class="marketing-landing__back-to-buttom">
					<a class="c-icon c-icon--chevron-left" href="<?php echo esc_url( '/' ); ?>">
						<?php
						printf(
							'%s <strong>%s</strong>',
							esc_html__( 'Back to', 'pmc-variety' ),
							esc_html__( 'Variety', 'pmc-variety' )
						);
						?>
					</a>
				</div>
				<div class="marketing-landing__logo">
					<img src="
					<?php
					echo esc_url(
						sprintf(
							'%s/assets/build/images/marketing/variety-vip-logo.svg',
							untrailingslashit( VARIETY_THEME_URL )
						)
					);
					?>
					">
				</div>

				<div class="marketing-landing__header">
					<h1 class="marketing-landing__heading">
						<?php
						esc_html_e(
							'Variety Intelligence Platform',
							'pmc-variety'
						);
						?>
					</h1>
					<h3 class="marketing-landing__sub-heading">
						<?php
						esc_html_e(
							'an upcoming offering from the editors of Variety',
							'pmc-variety'
						);
						?>
					</h3>
				</div>

				<div class="marketing-landing__subscription">
					<h3 class="marketing-landing__subscription-heading">
						<?php
						esc_html_e(
							'Sign up to enjoy a free sample of VIP special report',
							'pmc-variety'
						);
						?>
					</h3>

					<div class="c-newsletter c-newsletter--marketing-landing">
						<form method="post" action="https://pages.email.variety.com/varietyvip/api" name="newsletter-module-form" class="c-newsletter__form">
							<input name="EmailAddress" required class="c-newsletter__email" id="EmailAddress" placeholder="<?php esc_attr_e( 'Email address', 'pmc-variety' ); ?>" type="email">
							<span class="c-newsletter__message hide">
							<?php
							esc_html_e(
								'Thank you for your interest. Check your inbox for your free VIP Special Report.',
								'pmc-variety'
							);
							?>
							</span>
							<button type="submit" class="c-newsletter__button">
								<?php
								esc_html_e(
									'Free Sample',
									'pmc-variety'
								);
								?>
							</button>
							<input type="hidden" name="__contextName" id="__contextName" value="NewsletterFormPost"/>
							<input type="hidden" name="__executionContext" id="__executionContext" value="Post"/>
						</form>
					</div>

				</div>
				<div class="marketing-landing__image">
					<img src="
					<?php
					echo esc_url(
						sprintf(
							'%s/assets/build/images/marketing/report-example.png',
							untrailingslashit( VARIETY_THEME_URL )
						)
					);
					?>
					">
				</div>
			</div>
		</div>

		<div class="l-page__section">
			<div class="c-marketing-section">
				<h2 class="c-marketing-section__heading">
					<?php
					esc_html_e(
						'Learn what VIP can offer',
						'pmc-variety'
					);
					?>
				</h2>
				<div class="c-marketing-section__excerpt">
					<p>
						<?php
						esc_html_e(
							'Variety Intelligence Platform (just call it "VIP" for short) will be available later this year. Sign up for updates in the coming months, including the launch-date announcement, and much more about what you can expect if you subscribe. VIP features include:',
							'pmc-variety'
						);
						?>
					</p>
				</div>
				<div class="c-marketing-section__features">

					<div class="c-card c-card--marketing-feature">
						<img class="c-card__image" src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/marketing/special-reports.svg' ); ?>" title="<?php esc_attr_e( 'Special Reports', 'pmc-variety' ); ?>"/>
						<h3 class="c-card__heading"><?php esc_html_e( 'Special Reports', 'pmc-variety' ); ?></h3>
						<p class="c-card__detail">
							<?php
							esc_html_e(
								'A dedicated team of experts will release at least one "deep dive" analysis per month on a wide range of need-to-know media trends.',
								'pmc-variety'
							);
							?>
						</p>
					</div>

					<div class="c-card c-card--marketing-feature">
						<img class="c-card__image" src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/marketing/in-depth-briefings.svg' ); ?>" title="<?php esc_attr_e( 'In-Depth Briefings', 'pmc-variety' ); ?>"/>
						<h3 class="c-card__heading"><?php esc_html_e( 'In-Depth Briefings', 'pmc-variety' ); ?></h3>
						<p class="c-card__detail">
							<?php
							esc_html_e(
								'Timely commentaries on the biggest stories impacting the industry in your inbox every week.',
								'pmc-variety'
							);
							?>
						</p>
					</div>

					<div class="c-card c-card--marketing-feature">
						<img class="c-card__image" src="
								<?php
								echo esc_url(
									sprintf(
										'%s/assets/build/images/marketing/exclusive-data.svg',
										untrailingslashit( VARIETY_THEME_URL )
									)
								);
								?>
								" title="<?php esc_attr_e( 'Exclusive Data', 'pmc-variety' ); ?>"/>
						<h3 class="c-card__heading"><?php esc_html_e( 'Exclusive Data', 'pmc-variety' ); ?></h3>
						<p class="c-card__detail">
							<?php
							esc_html_e(
								'Customizable charts on every kind of stat you want at your fingertips, updated regularly.',
								'pmc-variety'
							);
							?>
						</p>
					</div>

				</div>
			</div>
		</div>

		<div class="l-page__section l-page__section--background">
			<div class="c-marketing-section c-marketing-section--newsletter">
				<h2 class="c-marketing-section__heading">
					<?php
					esc_html_e(
						'Don\'t miss the opportunity',
						'pmc-variety'
					);
					?>
				</h2>
				<h3 class="c-marketing-section__sub-heading">
					<?php
					esc_html_e(
						'Sign up TODAY and you\'ll receive a sample special report from VIP',
						'pmc-variety'
					);
					?>
				</h3>

				<div class="c-newsletter c-newsletter--marketing-landing c-newsletter--marketing-landing-footer">
					<form method="post" action="https://pages.email.variety.com/varietyvip/api/" name="newsletter-module-form" class="c-newsletter__form">
						<input name="EmailAddress" required class="c-newsletter__email js-newsletter-email" placeholder="<?php esc_attr_e( 'Email address', 'pmc-variety' ); ?>" type="email">
						<span class="c-newsletter__message hide">
						<?php
						esc_html_e(
							'Thank you for your interest. Check your inbox for your free VIP Special Report.',
							'pmc-variety'
						);
						?>
						</span>
						<button type="submit" class="c-newsletter__button">
							<?php
							esc_html_e(
								'Sign me up',
								'pmc-variety'
							);
							?>
						</button>
						<input type="hidden" name="__contextName" id="__contextName" value="NewsletterFormPost"/>
						<input type="hidden" name="__executionContext" id="__executionContext" value="Post"/>
					</form>
				</div>

			</div>
		</div>

		<div class="l-page__section l-page__section--copy-right">
			<p>
				&copy;
				<?php
				// translators: %s current year.
				printf( esc_html_x( 'Variety is a part of Penske Media Corporation. &copy; %s Variety Media, LLC. All Rights Reserved. Variety and the Flying V logos are trademarks of Variety Media, LLC. Powered by WordPress.com VIP', 'footer copyright text', 'pmc-variety' ), esc_html( gmdate( 'Y' ) ) );
				?>
			</p>
		</div>

	</div>
</div>

<?php wp_footer(); ?>
<?php do_action( 'pmc-tags-footer' ); // phpcs:ignore ?>
</body>
</html>

