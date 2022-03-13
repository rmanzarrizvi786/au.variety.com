<?php
/**
 * Template for rendering bottom sticky ad unit on desktop view
 *
 * @author Brian Van <Brian.VanNieuwenhoven@ey.com>
 * @since 2017-06-13
 */

if( ! isset( $ad_slot ) ) {
	return false;
} else {
?>
<!-- margin / padding set to 0 so layout can't override -->
<div class="desktop-bottom-sticky-ad hidden" style="margin: 0; padding: 0;">

	<div class="sticky-ad">

		<div class="btn-close-ad">
			<?php do_action( 'pmc_sticky_ads_desktop_close_button' ); ?>
		</div>

		<div class="ad-unit">
			<?php pmc_adm_render_ads( $ad_slot ); ?>
		</div>

	</div>

</div>

<?php }
//omit linebreak