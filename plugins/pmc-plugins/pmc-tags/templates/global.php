<!-- START callback function for onhashchange(when the hash tag in the url gets updated) -->
<?php
$option['values']['sitepcode'] = ( ! empty ( $option['values']['sitepcode'] ) ) ? $option['values']['sitepcode'] : '';
?>
<script type="text/javascript">
window.pmc = window.pmc || {};
window.pmc.analytics = window.pmc.analytics || [];

function global_urlhashchanged() {
	/**
	 * Track pageview
	 */
	// Build the current domain name
	var tmp_td = document.domain.split('.');
	var tracking_domain = tmp_td[tmp_td.length-2] + "." + tmp_td[tmp_td.length-1];

	// Build the current image
	var imgname = location.hash.replace('#','');
	try {
        window.pmc.analytics.push(function() {
            window.pmc.analytics.track_pageview( location.pathname + location.hash );
		});
	} catch(err) {}

	try {
		if ( typeof window.snowplowKW == 'function' ) {
			console.log('sending tracking code');
			window.snowplowKW( 'trackPageView' );
		}
	} catch ( err ) {
	}

	// Track Quantcast
	try {

		// need to reset the array containing the options previously sent
		// as quantcast does not send the same key twice.
		if (__qc && __qc.qpixelsent && __qc.qpixelsent.length > 0) {
			__qc.qpixelsent.length = 0;
		}
		// First Quantcast Tag
		_qoptions={
			qacct:"<?php echo esc_js( $option['values']['sitepcode'] ); ?>"
		};

		quantserve();

	} catch(err) {}

	// Track Comscore unless it's a gallery and has been tracked
	if ( window.pmc.comscoreTracked && -1 === window.pmc.comscoreTracked.indexOf( document.location.href ) ) {
		try {
			setTimeout(function(){var url = "http" + (/^https:/.test(document.location.href) ? "s" : "") + "://beacon.scorecardresearch.com/scripts/beacon.dll" + "?c1=2&amp;c2=6035310&amp;c3=&amp;c4=&amp;c5=&amp;c6=&amp;c7=" + escape(document.location.href) + "&amp;c8=" + escape(document.title) + "&amp;c9=" + escape(document.referrer) + "&amp;c10=" + escape(screen.width + 'x' + screen.height) + "&amp;rn=" + (new Date()).getTime();var i = new Image();i.src = url;}, 1);

			COMSCORE.beacon({c1:2,c2:"6035310",c3:"6035310",c4:"",c5:"",c6:"",c15:""});

		} catch(err) {}
	}

	<?php do_action( 'pmc_tags_global_urlhashchanged_template' ); ?>

	/**
	* Track pageview end
	*/
}
</script>
