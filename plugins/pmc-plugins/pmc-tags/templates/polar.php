<script data-cfasync='true' async src="//plugin.mediavoice.com/mediaconductor/mc.js"></script>
<script data-cfasync='true'>
	window.mediaconductor=window.mediaconductor||function(){(mediaconductor.q=mediaconductor.q||[]).push(arguments);}
	mediaconductor("init", "<?php echo esc_js( $option['values']['id'] ); ?>");
	mediaconductor("exec");
</script>
