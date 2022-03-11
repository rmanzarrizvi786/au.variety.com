<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="lrv-u-text-align-center">

	<div class="u-background-color-accent-b">
		<h2 class="lrv-u-font-family-secondary lrv-u-color-white lrv-u-font-weight-bold lrv-u-font-size-32 lrv-u-text-transform-uppercase lrv-u-padding-tb-2 lrv-u-line-height-small">
			Corporate Subscriptions
		</h2>
	</div>

	<div class="u-background-brand-secondary-top-half u-padding-lr-3@tablet">

		<div class="lrv-a-wrapper lrv-u-background-color-white u-border-t-6@tablet u-border-color-vip-brand-primary lrv-u-padding-tb-2 lrv-u-margin-b-2 u-box-shadow-small-medium@tablet">

			<?php if ( ! empty( $vip_corporate_subscriptions_submission_text ) ) { ?>

				<div class="lrv-u-padding-b-2">
					<?php echo esc_html( $vip_corporate_subscriptions_submission_text ?? '' ); ?>
				</div>

			<?php } else { ?>

				<div class="lrv-a-grid lrv-a-cols3@desktop lrv-u-align-items-center u-margin-lr-075@mobile-max u-margin-lr-3">

					<div class="lrv-a-span2@tablet">
						<img
							class="lrv-u-display-block lrv-u-margin-lr-auto"
							src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/vip-corporate-subscriptions.png' ); ?>"
						/>
						<div class="lrv-u-text-transform-uppercase lrv-u-margin-tb-1 lrv-u-font-weight-bold lrv-u-font-size-18">
							Consider a Corporate Subscription to
								<?php if ( ! empty( $c_icon ) ) { ?>
									<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $c_icon, true ); ?>
								<?php } ?>
						</div>

						<div class="lrv-a-grid a-cols2@tablet u-grid-gap-2 lrv-u-font-size-15">
							<div>
								<div class="u-color-brand-vip-primary lrv-u-font-weight-bold lrv-u-text-transform-uppercase">
									Group Rate
								</div>
								Subscription prices go down as the number of subscriptions increase.
							</div>

							<div>
								<div class="u-color-brand-vip-primary lrv-u-font-weight-bold lrv-u-text-transform-uppercase">
									Fewer Notices
								</div>
								All subscriptions are grouped together on renewals and invoices, to cut down on the amount of mail you receive from us
							</div>
						</div>
					</div>

					<div class="lrv-u-font-size-15 u-border-l-1@desktop lrv-u-border-color-grey-light u-padding-l-3@desktop u-margin-l-3@desktop lrv-u-margin-t-2@mobile-max lrv-u-text-align-left">
						Enter your info below to receive discounted pricing for corporate customers!

						<div class="lrv-u-border-b-1 lrv-u-border-color u-border-r-1 lrv-u-border-color-grey-light lrv-u-margin-t-2">
							<input
								class="a-reset-input a-placeholder-color-pale-sky-2 lrv-u-border-a-0 lrv-u-width-100p lrv-u-padding-lr-050 lrv-u-padding-tb-025 lrv-u-font-size-16"
								type="text"
								required="required"
								name="vcsFirstName"
								placeholder="First Name"
							>
						</div>

						<div class="lrv-u-border-b-1 lrv-u-border-color u-border-r-1 lrv-u-border-color-grey-light lrv-u-margin-t-2">
							<input
								class="a-reset-input a-placeholder-color-pale-sky-2 lrv-u-border-a-0 lrv-u-width-100p lrv-u-padding-lr-050 lrv-u-padding-tb-025 lrv-u-font-size-16"
								type="text"
								required="required"
								name="vcsLastName"
								placeholder="Last Name"
							>
						</div>

						<div class="lrv-u-border-b-1 lrv-u-border-color u-border-r-1 lrv-u-border-color-grey-light lrv-u-margin-t-2">
							<input
								class="a-reset-input a-placeholder-color-pale-sky-2 lrv-u-border-a-0 lrv-u-width-100p lrv-u-padding-lr-050 lrv-u-padding-tb-025 lrv-u-font-size-16"
								type="text"
								required="required"
								name="vcsCompanyName"
								placeholder="Company Name"
							>
						</div>

						<div class="lrv-u-border-b-1 lrv-u-border-color u-border-r-1 lrv-u-border-color-grey-light lrv-u-margin-t-2">
							<input
								class="a-reset-input a-placeholder-color-pale-sky-2 lrv-u-border-a-0 lrv-u-width-100p lrv-u-padding-lr-050 lrv-u-padding-tb-025 lrv-u-font-size-16"
								type="text"
								required="required"
								name="vcsEmailAddress"
								placeholder="Email Address"
								pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"
							>
						</div>

						<input  type="test" name="vip-corporate-subscription-check" class="lrv-u-display-none" autocomplete="off" />

						<input
							class="lrv-u-border-a-0 lrv-u-color-white lrv-u-cursor-pointer lrv-u-font-size-18 u-background-color-brand-primary-vip u-color-black:hover u-font-family-accent lrv-u-margin-t-2 lrv-u-padding-a-050 lrv-u-width-100p "
							type="submit"
							name="vcsSubmit"
							value="Submit"
						>

					</div>
				</div>

			<?php } ?>

		</div>
	</div>

</section>
