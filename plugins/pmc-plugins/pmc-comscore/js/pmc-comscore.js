/**
 * @version 2020-09-22 Set the cache flag from `false` to `true` since its a static XML file which does not change. It was initiated by a ticket from VIP over performance concerns - https://wordpressvip.zendesk.com/hc/en-us/requests/116315
 * @version 2022-02-03 set cache flag to true as comscore expects this to not be browser cached. throttling comscore reporting on galleries and lists separately.
 */

var pmc_comscore = pmc_comscore || {
	pageview: function( callback ) {
		try {
			if ( typeof jQuery !== 'undefined' && typeof pmc_comscore_options.pageview_candidate_url !== 'undefined' ) {
				jQuery.ajax({
					url: pmc_comscore_options.pageview_candidate_url,
					type: 'GET',
					cache: false
				});
			}
			if ( typeof callback === 'function' ) {
				callback();
			}
		} catch (e) {
		}
	}
};
