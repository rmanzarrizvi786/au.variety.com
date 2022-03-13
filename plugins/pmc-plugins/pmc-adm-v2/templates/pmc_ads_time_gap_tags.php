<?php
// Template: show-time-gap-ads

?>
<script type="text/javascript" id="show-interrupts">
	var timeg_gap_ads_ck = <?php echo wp_json_encode( $cookie_name ) ;?>;
	var time_gap_ads_cookie_check = pmc.cookie.get( timeg_gap_ads_ck );
	var pmc_adm_has_time_gap_ads = false;

	if ( ! time_gap_ads_cookie_check ) {
		pmc_adm_has_time_gap_ads = true;
		pmc.cookie.set(timeg_gap_ads_ck, 1, <?php echo intval( $time_gap ); ?>, '/');
	}
</script>
