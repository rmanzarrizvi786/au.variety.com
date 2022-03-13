<?php
// Template: show-interrupts

?>
<script type="text/javascript" id="show-interrupts">
	window.pmc = window.pmc || {};
	pmc.pmc_adm_interstitial_ck = '<?php echo esc_js( $cookie_name ); ?>';
	pmc.pmc_adm_interstitial_interval = '<?php echo intval( $time_gap ); ?>';
</script>
