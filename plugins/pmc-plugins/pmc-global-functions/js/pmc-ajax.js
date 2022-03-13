var pmc_ajax = pmc_ajax || {
	// cacheable get
	get: function( options ) {
		options.type = 'GET';
		return this.submit( options );
	},
	// non-cache post
	post: function( options ) {
		options.type = 'POST';
		return this.submit( options );
	},
	submit: function( options ) {
		var ajax_args = '/';

		// checking required fields
		if ( typeof pmc_ajax_options === 'undefined'
				|| typeof jQuery === 'undefined'
				|| typeof options.action == 'undefined'
				|| options.action == ''
				|| typeof options.type == 'undefined' ) {
			return false;
		}

		options.data = {};
		if ( typeof options.args !== 'undefined' ) {
			switch ( options.type ) {
				case 'GET':
					// force cacheable
					options.cache = true;
					// build cache friendly url part to avoid query string
					for (name in options.args) {
						// we're using dash (-) instead of equal (=) and slash (/) instead of ampersand (&)
						ajax_args += name + '-' + encodeURIComponent( options.args[name] ) + '/';
					}
					break;
				case 'POST':
					options.data = options.args;
					break;
				default:
					return false;
			}
		}

		// default ajax url
		var ajax_url = pmc_ajax_options.ajax_url;

		if ( typeof options.scheme !== 'undefined' ) {
			// scheme override for ajax url
			switch ( options.scheme ) {
				case 'https':
					ajax_url = pmc_ajax_options.ajax_url_https;
					break;
				case 'http':
					ajax_url = pmc_ajax_options.ajax_url_http;
					break;
			}
			delete options.scheme;
		}

		// piece together the ajax end point and sanitize action string
		options.url = ajax_url + options.action.replace(/\//g,'-') + ajax_args;
		delete options.action;

		if ( typeof options.cache !== 'undefined' && false === options.cache ) {
			// no cache detected, append _=[timestamp] for cache buster
			try {
				var timestamp = new Date().getTime();
				options.url += '?_=' + timestamp;
			} catch (e) {}
		}

		jQuery.ajax( options );

		// return true to indicate ajax call is submitted
		return true;
	}
};
