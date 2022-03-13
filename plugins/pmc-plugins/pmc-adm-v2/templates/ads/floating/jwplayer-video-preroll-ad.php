<?php

if ( ! function_exists( 'jwplayer_get_content_mask' ) || empty( $player_id ) ) {
	return;
}

$content_mask = jwplayer_get_content_mask();
$js_lib       = "https://$content_mask/libraries/$player_id.js";
$jw_style_v2  = (int) \PMC_Cheezcap::get_instance()->get_option( 'pmc-floating-video-jwplayer_style_v2' );

?>
<!-- Placeholder for Floating Video Ad -->
<div class="floating-preroll-ad <?php echo ! empty( $jw_style_v2 ) ? 'floating-preroll-ad-v2' : ''; ?>">
	<div class="floating-preroll-ad-container">
		<?php if ( $jw_style_v2 ) : ?>
			<div class="floating-preroll-ad-title"></div>
		<?php endif; ?>
		<span class="floating-preroll-ad-close">&times;</span>
		<div id="jwplayer_floating_preroll_ad"></div>
	</div>
</div>

<script id="pmcPrerollAd">
	(function(){
		function pmcLoadPrerollAd() {
			var container     = document.getElementById( 'pmcPrerollAd' ).parentElement;
			var prerollScript = document.createElement('script');

			prerollScript.setAttribute('onload', 'pmc_jwplayer.add()');
			prerollScript.setAttribute('type', 'text/javascript');
			prerollScript.setAttribute('src', '<?php echo esc_url( $js_lib ); ?>');

			container.appendChild( prerollScript );
		}

		if ( ! ( -1 < document.cookie.indexOf( 'scroll0=' ) ) ) {
			pmcLoadPrerollAd();
		}
	})();
</script>
<!-- End Placeholder Floating Video Ad -->
