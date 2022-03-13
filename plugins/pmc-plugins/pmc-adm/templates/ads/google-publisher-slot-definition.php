<?php

/**
 * Template for defining and rendering ad slots for Google Publisher
 *
 * @since 2013-10-17 Amit Gupta
 * @version 2013-10-30 Amit Gupta
 * @version 2015-03-11 Hau Vong
 */
if( is_preview() ){
	return ;
}

if( ! $pmc_ad_settings = $provider->prepare_ad_settings( $ads ) ) {
	return;
}

?>
<script type='text/javascript' class="script-mobile">
	if ( 'undefined' !== typeof pmc_adm_gpt ) {
		pmc_adm_gpt.init(<?php echo wp_json_encode( $pmc_ad_settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ); ?>);
	}
</script>

