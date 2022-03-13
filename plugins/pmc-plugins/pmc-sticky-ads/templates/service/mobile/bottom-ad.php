<?php
/**
 * Template for rendering bottom sticky ad unit on mobile devices
 *
 * @author Amit Gupta <agupta@pmc.com>
 * @since 2016-11-08
 */
?>
<!-- margin / padding set to 0 so layout can't override -->
<div class="mobile-bottom-sticky-ad hidden" style="margin: 0; padding: 0;">

	<div class="sticky-ad">

		<div class="btn-close-ad">
			<?php do_action( 'pmc_sticky_ads_close_button' ); ?>
		</div>

		<div class="ad-unit">
			<?php pmc_adm_render_ads( $ad_slot ); ?>
		</div>

	</div>

</div>
