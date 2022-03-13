<?php
?>
<!--DFP Prestitial ad call -->

<div id="prestitial-ad-section" class="hide">
	<div id="prestitial-ad-outer-container">
		<div id="prestitial-ad-overlay"></div>
		<div id="prestitial-ad-close">
			<?php if ( PMC::is_mobile() ) : ?>
				X
			<?php else : ?>
				Click to Skip Ad
			<?php endif; ?>
		</div>
		<div id="prestitial-ad-duration-counter"><em>Closing in...</em></div>
		<div id="prestitial-ad-container"></div>
		<div id="prestitial-ad-third-party-content-view-tracker"></div>
	</div>
	<div id="prestitial-ad-inject-container" class="hide">
		<?php pmc_adm_render_ads( 'dfp-prestitial' ) ?>
	</div>
</div>

<!-- end DFP Prestitial ad call -->
