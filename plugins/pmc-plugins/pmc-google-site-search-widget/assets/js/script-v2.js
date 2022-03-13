function pmc_render_cse_v2() {
	//Search form on header

	jQuery(".cse-search-form").each(
		function () {
			google.search.cse.element.render(
				{
					div: jQuery(this).get(0),
					tag: 'searchbox-only',
					attributes: {
						resultsUrl: _pmc_google_site_search_url, queryParameterName: "q"
					}
				});
		}
	)

	jQuery(".cse-results").each(
		function () {
			//Search result div
			google.search.cse.element.render(
				{
					div: jQuery(this).get(0),
					tag: 'searchresults-only'
				});
		}
	)
	//Provide callback function to manipulate searchbox onload
	try {

		if (typeof pmc_google_custom_search_onload === 'function') {
			pmc_google_custom_search_onload();
		}

	} catch (er) {
	}
}

/*
 explicit: Components are rendered only with explicit calls to google.search.cse.element.render() or google.search.cse.element.go(). Used together with the callback parameter.

 onload (default): All CSE components inside the page's body tag are automatically rendered after the page loads.
 */
window.__gcse = {
	parsetags: 'explicit',
	callback: function () {
		if (document.readyState == 'complete') {
			// Document is ready when CSE element is initialized.
			// Render an element with both search box and search results in div with id 'test'.
			pmc_render_cse_v2();

		} else {
			// Document is not ready yet, when CSE element is initialized.
			jQuery(document).ready(function () {
			// google.setOnLoadCallback(function () {
				// Render an element with both search box and search results in div with id 'test'.
				pmc_render_cse_v2();
			// }, true);
			});
		}
	}
};

(function () {
	var cx = "";
	if ("undefined" !== typeof _pmc_google_site_search_id) {
		var cx = _pmc_google_site_search_id;
	}
	var gcse = document.createElement('script');
	gcse.type = 'text/javascript';
	gcse.async = true;
	gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') +
		'//www.google.com/cse/cse.js?cx=' + cx;
	var s = document.getElementsByTagName('script')[0];
	s.parentNode.insertBefore(gcse, s);
})();