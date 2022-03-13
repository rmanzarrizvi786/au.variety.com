jQuery(document).ready(function() {
	if ( typeof _gaq === 'undefined' && typeof pmc === 'object' && typeof pmc.tracking === 'object' && typeof pmc.tracking.remove_from_browser_url === 'function' ) {
		pmc.tracking.remove_from_browser_url();
	}
});
