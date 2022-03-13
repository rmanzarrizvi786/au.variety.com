<?php
// Template: show-interrrupts

?>
<script type="text/javascript" id="show-interrupts">
	var ck = '<?php echo esc_js( $cookie_name ) ;?>';

	var cookie_check = pmc.cookie.get( ck );
	var show_interrupt_hash = location.hash;

	if( show_interrupt_hash.indexOf('showinterrupt') > -1 ){
		// we want to force the interrupt to be shown for 200 seconds without a cookie written or any other check.
		pmc_admanager.settings.interrupt_counter = 200;
		pmc_admanager.show_interrupt_ads();

	}else if( cookie_check == null || typeof cookie_check === 'undefined' || cookie_check == ''){
		pmc_adm_has_interrupts = true; // global variabl telling us that there is an interstitial or prestitial on this page.
		// we got to this point so we need to set the cookie so that this overlay doesn't show up again.

		if (
			( 'undefined' !== typeof pmc.hooks && 'object' === typeof pmc_meta && 'undefined' !== typeof pmc_meta.is_eu && true === pmc_meta.is_eu ) ||
			-1 !== window.location.search.indexOf( 'region=eu' )
		) {
			pmc.hooks.add_action( 'pmc_adm_consent_data_ready', function( consent_data ) {
				if ( 'object' === typeof consent_data && 'undefined' !== typeof consent_data.returnValue ) {
					pmc.cookie.set(ck, 1, <?php echo intval( $time_gap ); ?>, '/');
					pmc_admanager.show_interrupt_ads();
				}
			});
		} else {
			pmc.cookie.set(ck, 1, <?php echo intval( $time_gap ); ?>, '/');
			pmc_admanager.show_interrupt_ads();
		}

	}

</script>
