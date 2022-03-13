<?php

if ( ! function_exists( 'jwplayer_get_content_mask' ) || empty( $player_id ) ) {
	return;
}

$content_mask = jwplayer_get_content_mask();
$js_lib       = "https://$content_mask/libraries/$player_id.js";

?>
<!-- Placeholder for Floating Video Ad -->
<div class="floating-preroll-ad">
	<div class="floating-preroll-ad-container">
		<span class="floating-preroll-ad-close">&times;</span>
		<div id="jwplayer_floating_preroll_ad"></div>
	</div>
</div>

<script onload="pmc_jwplayer.add();" type='text/javascript' src="<?php echo esc_url( $js_lib ); ?>"></script>

<!-- End Placeholder Floating Video Ad -->
