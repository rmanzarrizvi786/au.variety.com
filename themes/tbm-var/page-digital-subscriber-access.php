<?php
/*
 * Template Name: Variety Digital Subscriber Access
 */
add_filter(
	'body_class',
	function( $classes ) {
		return array_merge( $classes, array( 'page-login' ) );
	}
);

/**
 * PMCRS-1167 - disable all ads on login page
 */
add_filter(
	'pmc-adm-fetch-ads',
	function() {
		return [];
	}
);

// we want to add this filter as late as possible, priority > 10
add_filter( 'pmc_header_bidder_active', '__return_false', 100 );

get_header();
?>
<div id="loading"></div>

<div class="variety-digital-subscriber-access lrv-a-wrapper lrv-u-margin-tb-2">
	<div class="lrv-a-grid a-cols4@tablet lrv-u-align-items-center lrv-u-font-family-secondary">

		<article class="a-span3@tablet">

			<h2 class="c-heading lrv-u-margin-b-2 lrv-u-text-align-center lrv-u-font-size-28 lrv-u-font-size-32@desktop"><?php esc_html_e( 'Variety Print Plus subscribers have access to the digital edition of Variety, special issues, and 15 years of archives.', 'pmc-variety' ); ?></h2>

			<div id="login-error" style="display:none;">
				<?php esc_html_e( 'Login failed: You have entered an incorrect Username or password, please try again.', 'pmc-variety' ); ?>
			</div>

			<div class="lrv-a-grid lrv-a-cols3@tablet lrv-u-align-items-center">
				<section class="variety-digital-subscriber-access-section lrv-u-border-r-1@desktop lrv-u-border-color-grey-light lrv-u-padding-r-1 lrv-u-height-100p lrv-u-padding-tb-2">
					<h3 class="c-title  lrv-u-font-size-24 lrv-u-font-size-32@desktop lrv-u-font-weight-bold lrv-u-text-align-center"><?php esc_html_e( 'Already a subscriber?', 'pmc-variety' ); ?></h3>
					<form name="loginform" id="loginform" action="#" method="post">
						<input type="hidden" id="delete_session" name="delete_session" value="0">
						<p class="login-username">
							<label class="u-font-family-basic lrv-u-font-size-12 lrv-u-color-grey-dark lrv-u-text-transform-uppercase lrv-u-display-block lrv-u-margin-b-050" for="user_login"><?php esc_html_e( 'Username', 'pmc-variety' ); ?></label>
							<input type="text" name="username" id="user_login" class="input lrv-u-padding-a-050 lrv-u-font-size-16 lrv-u-border-a-1 lrv-u-border-color-grey lrv-u-width-100p lrv-u-max-width-300" value="" size="20">
						</p>
						<p class="login-password">
							<label class="u-font-family-basic lrv-u-font-size-12 lrv-u-color-grey-dark lrv-u-text-transform-uppercase lrv-u-display-block lrv-u-margin-b-050" for="user_pass"><?php esc_html_e( 'Password', 'pmc-variety' ); ?></label>
							<input type="password" name="password" id="user_pass" class="input lrv-u-padding-a-050 lrv-u-font-size-16 lrv-u-border-a-1 lrv-u-border-color-grey lrv-u-width-100p lrv-u-max-width-300" value="" size="20">
						</p>
						<p class="login-remember">
							<label class="u-font-family-basic lrv-u-font-size-12 lrv-u-color-grey-dark lrv-u-text-transform-uppercase"><input name="persist" type="checkbox" id="rememberme"><?php esc_html_e( 'Remember Me', 'pmc-variety' ); ?></label>
						</p>
						<p class="login-submit">
							<input class="lrv-a-unstyle-button lrv-u-cursor-pointer lrv-u-border-radius-5 lrv-u-background-color-black lrv-u-color-white lrv-u-padding-lr-1 lrv-u-padding-tb-050" id="login-submit" type="submit" name="login-submit" class="button-primary" value="<?php esc_html_e( 'Sign in', 'pmc-variety' ); ?>">
							<input type="hidden" name="cmd" value="verify-credential"/>
							<input type="hidden" name="action" value="variety_digital_subscriber"/>
						</p>
						<div class="lrv-u-color-grey lrv-u-font-size-14">
							<div>
								<a href="https://penske.dragonforms.com/VY_ForgotPass" class="tracking-button" id="variety_forgot_password" data-category="subscribe-page" data-action="forgot-button" data-label="click" >
									<?php esc_html_e( 'Forgot your Username or Password?', 'pmc-variety' ); ?>
								</a>
							</div>
							<div>
								<a href="<?php echo esc_url( '/static-pages/contact-us/' ); ?>"><?php esc_html_e( 'Contact Customer Service.', 'pmc-variety' ); ?></a>
							</div>
						</div>
					</form>
				</section>

				<section class="variety-subscribe lrv-u-border-r-1@desktop lrv-u-border-color-grey-light lrv-u-padding-r-1 lrv-u-height-100p lrv-u-text-align-center lrv-u-padding-tb-2">
					<h3 class="c-title  lrv-u-font-size-24 lrv-u-font-size-32@desktop lrv-u-font-weight-bold"><?php esc_html_e( 'Not yet a subscriber?', 'pmc-variety' ); ?></h3>
					<p class="c-tagline u-font-family-body lrv-u-margin-b-2 lrv-u-font-size-18">
						<?php
						echo sprintf(
							'%s <span class="variety">%s</span> %s',
							esc_html( 'Join us at a great, low rate and get', 'pmc-variety' ),
							esc_html( 'Variety', 'pmc-variety' ),
							esc_html( 'in print and immediate digital access.', 'pmc-variety' )
						);
						?>
					</p>

					<a href="<?php echo esc_url( '/subscribe-us/?utm_source=site&utm_medium=VAR_Login&utm_campaign=DualShop' ); ?>" target="_blank" class="button-primary tracking-button lrv-a-unstyle-button lrv-u-border-radius-5 lrv-u-background-color-black lrv-u-color-white lrv-u-padding-lr-1 lrv-u-padding-tb-050" id="variety-subscribe" data-category="subscribe-page" data-action="not-yet-button" data-label="click" ><?php esc_html_e( 'Subscribe', 'pmc-variety' ); ?></a>
				</section>

				<section class="variety-register lrv-u-height-100p lrv-u-text-align-center lrv-u-padding-tb-2">
					<h3 class="c-title  lrv-u-font-size-24 lrv-u-font-size-32@desktop lrv-u-font-weight-bold"><?php esc_html_e( 'Don\'t have a username?', 'pmc-variety' ); ?></h3>

					<p class="c-tagline u-font-family-body lrv-u-margin-b-2 lrv-u-font-size-18"><?php esc_html_e( 'Log in to Online Account Services and click Update Subscription from the menu.', 'pmc-variety' ); ?></p>

					<a href="https://www.pubservice.com/subinfo.aspx?PC=VY&AN=&Zp=&PK=" target="_blank"  class="button-primary tracking-button lrv-a-unstyle-button lrv-u-border-radius-5 lrv-u-background-color-black lrv-u-color-white lrv-u-padding-lr-1 lrv-u-padding-tb-050" id="variety-register" data-category="subscribe-page" data-action="don't-have-button" data-label="click" ><?php esc_html_e( 'Register', 'pmc-variety' ); ?></a>
				</section>
			</div>

			<div class="u-font-family-body lrv-u-font-size-16 lrv-u-margin-t-2 u-max-width-618">
					<?php
					echo sprintf(
						'<span class="variety">%s</span> %s',
						esc_html( 'Variety\'s', 'pmc-variety' ),
						esc_html( 'digital edition is a replica of the print version including every article, feature, photo and chart. The digital magazine is available each Tuesday at 5AM (PST).', 'pmc-variety' )
					);
					?>
				</p>
			</div>

		</article>

		<aside class="u-align-self-start">
			<?php
			$issue = Variety_Digital_Feed::get_instance()->get_latest_variety_issue();
			if ( ! empty( $issue ) ) {
				printf( '<a href="%s" class=""><img src="%s" /></a>', esc_url( $issue['url'] ), esc_url( $issue['img320'] ) );
			}
			?>
		</aside>

	</div>
</div>

<?php
get_footer();
