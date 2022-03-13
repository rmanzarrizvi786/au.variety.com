<?php if ( 'top' === $position ) : ?>
	<script data-cfasync='true' type='text/javascript' src='//c.amazon-adsystem.com/aax2/amzn_ads.js'></script>
	<script data-cfasync='true' type='text/javascript'>
		try {
			amznads.getAds('3157');
		} catch(e) { /*ignore*/}
	</script>
<?php endif; ?>

<?php if ( 'footer' === $position ) : ?>
	<script data-cfasync='true' type='text/javascript'>
		try {
			amznads.setTargetingForGPTAsync('amznslots');
		} catch(e) { /*ignore*/}
	</script>
<?php endif; ?>
