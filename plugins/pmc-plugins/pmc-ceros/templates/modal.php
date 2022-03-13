<script>
	var PMC_CEROS_EDITOR = {
		buttonTitle: <?php echo wp_json_encode( esc_html__( 'Insert Ceros shortcode', 'pmc-ceros' ) ); ?>,
		cerosImageUrl: decodeURIComponent( '<?php echo rawurlencode( esc_url( PMC_CEROS_PLUGIN_URL . 'assets/images/ceros.png' ) ); ?>' ),
	};
</script>
<style>
.pmc-ceros-dialog{max-width:800px;z-index:999999!important}.pmc-ceros-dialog form{padding:10px}.pmc-ceros-dialog .ui-dialog-titlebar.ui-widget-header.ui-corner-all.ui-helper-clearfix{display:none}.pmc-ceros-dialog form{display:block;padding-top:5px;padding-bottom:5px}.pmc-ceros-dialog form>span{display:block;padding-bottom:5px;}.pmc-ceros-dialog .button-primary{margin-top:15px;display:block;}.pmc-ceros-dialog .pmc-ceros-close{display:block;position:absolute;right:7px;text-decoration:none;top:5px}
</style>
<div id="pmc-ceros-dialog" class="hidden">
	<a class="pmc-ceros-close" href="#">&times;</a>
	<form>
		<span><?php esc_html_e( 'Ceros Embed Code', 'pmc-ceros' ); ?></span>
		<textarea rows="5" cols="50" class="regular-text" name="embed_code" /></textarea>
		<button class="button button-primary"><?php esc_html_e( 'Insert Code', 'pmc-ceros' ); ?></button>
	</form>
</div>
