<!-- Newsletter Subscripton CTA -->
<div class="cta cta--newsletter">
	<h3 class="cta__heading">
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/svg/logo-pmc.php', [], true ); ?>
		<span class="cta__subheading"><?php esc_html_e( 'Free alerts & Newsletters', 'pmc-core' ); ?></span>
	</h3>

	<form method="post" action="<?php echo esc_url( cheezcap_get_option( 'pmc_core_signup_url', false ) ); ?>" id="newsletter-module-form" name="newsletter-module-form" class="cta-form" target="_blank">
		<input type="email" name="EmailAddress" class="toolkitEmail cta-form__email" placeholder="<?php esc_html_e( 'Enter your email address', 'pmc-core' ); ?>"/>
		<input type="hidden" name="pmc_core_morningreport_Opted_In" value="Yes"/>
		<input type="hidden" name="pmc_core_newsalert_Opted_In" value="Yes"/>
		<input type="hidden" name="pmc_core_promo_house_Opted_In" value="Yes"/>
		<input type="hidden" name="pmc_core_promo_summits_Opted_In" value="Yes"/>
		<input type="hidden" name="pmc_core_promo_partner_Opted_In" value="Yes"/>
		<input type="hidden" name="pmc_core_unsub_all" value="No"/>
		<input type="hidden" name="pmc_core_newsalert_source" value="onsite-simple"/>
		<input type="hidden" name="pmc_core_morningreport_source" value="onsite-simple"/>
		<input type="hidden" name="__contextName" id="__contextName" value="FormPost"/>
		<input type="hidden" name="__executionContext" id="__executionContext" value="Post"/>
		<input type="hidden" name="__successPage" id="__successPage" value=""/>
		<button class="cta-form__submit" type="submit"><?php esc_html_e( 'Sign Up', 'pmc-core' ); ?></button>
	</form>
	<script>
		/*
		 Add the success input field on both keyup and blur
		 this ensures the field is added when an email address
		 is typed in, pasted in, or selected from the user's browser
		 */
		(
			function ( $ ) {
				$( '.toolkitEmail' ).keyup( function () {
					pmc_core_add_success_page_input( this );
				} ).blur( function () {
					pmc_core_add_success_page_input( this );
				} );

				function pmc_core_add_success_page_input( that ) {
					var $this = $( that );
					var emailValue = encodeURIComponent( $this.val() );
					$this.parents('form').find('#__successPage').val('<?php echo esc_url( 'https://pages.email.' . apply_filters( 'pmc_core_site_domain', wp_parse_url( get_home_url(), PHP_URL_HOST ) ) . '/PreferenceCenter/' ); ?>?email='+emailValue+'&signup=success');
				}
			}
		)( jQuery );
	</script>
</div>