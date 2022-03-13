/*jslint browser: true, devel: true, ass: true, eqeq: true, forin: true, newcap: true, nomen: true, plusplus: true, regexp: true, unparam: true, sloppy: true, todo: true, vars: true, white: true */
/**
 * PMC Utility library
 *
 * @version 0.4d
 * @author Amit Gupta
 *
 * @since 2012-04-09
 * @lastModified 2013-03-14 Amit Gupta
 *
 * npm install uglify-js -g
 * uglifyjs pmc-utils.js -cm -o pmc-utils.min.js
 *
 */

// In case pmc-hooks.js or any pmc extension script load before pmc-utils.js
var pmc = pmc || {};

Object.assign(pmc, {

	/**
	 * Generate the GUID/UUID
	 * @returns {string}
	 */
	uuid: function() {
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
			var r = Math.random() * 16 | 0, v = c === 'x' ? r : ((r & 0x3) | 0x8);
			return v.toString(16);
		});
	},

	/**
	 * Generate PVUUID if needed
     * @param {boolean} force
	 */
	generate_pvuuid: function (force) {

		if (force) {
			window._skmPageViewId = pmc.uuid();
		} else {
			// Assign & re-use existing value if defined elsewhere; otherwise auto generate
			window._skmPageViewId = window._skmPageViewId || pmc.uuid();
		}

		return window._skmPageViewId;
	},

	/**
	 * rot13 encode
	 */
	rot13: function (s) {
		return s.replace( /[A-Za-z]/g , function(c) {
			return String.fromCharCode( c.charCodeAt(0) + ( c.toUpperCase() <= "M" ? 13 : -13 ) );
		} );
	},

	/**
	 * Return true if we detect site is access via ezproxy by checking expected hostname vs window.location.hostname
	 */
	is_proxied: function() {
		if ( typeof window.pmc_site_config !== 'object' || ! window.pmc_site_config.hasOwnProperty('is_proxied') || ! window.pmc_site_config.hasOwnProperty('rot13_hostname') ) {
			return false;
		}
		// first time run?
		if ( ! window.pmc_site_config.is_proxied && window.pmc_site_config.is_proxied === null ) {
			// we need to use rot13 to encode the hostname to prevent ezproxy from domain translation
			window.pmc_site_config.is_proxied = this.rot13(window.pmc_site_config.rot13_hostname).toLowerCase() !== window.location.hostname.toLowerCase();
		}
		return window.pmc_site_config.is_proxied;
	},

	/**
	 * Replace the url if detected proxied and the url domain match
	 * @param  {string} url    The url to be translate
	 * @param  {boolean} force Force proxy translate even if domain not match
	 * @return {string}        The translated url
	 */
	proxy_url: function( url, force ) {
		var self = this;
		// note: is_proxied ensured rot13_hostname variable exist
		if ( this.is_proxied() ) {
			return url.replace(/^(https?:\/\/)([^\/]+)/, function(replacing,protocol,hostname){
				if ( force || self.rot13(window.pmc_site_config.rot13_hostname).toLowerCase() === hostname.toLowerCase() ) {
					if( window.location.port && window.location.port != '' ) {
						return protocol + window.location.hostname + ':' + window.location.port;
					} else {
						return protocol + window.location.hostname;
					}
				}
				return replacing;
			});
		}
		return url;
	},

	/**
	 * Replace the url domain with site domain if ezproxy detected
	 * @param  string url    The url to be translate
	 * @return string        The translated url
	 */
	reverse_proxy_url: function( url ) {
		var self = this;
		// note: is_proxied ensured rot13_hostname variable exist
		if ( this.is_proxied() ) {
			return url.replace(/^(https?:\/\/)([^\/]+)/, function(replacing,protocol,hostname){
				// need to use rot13 value, ezproxy may translate window.pmc_site_config.hostname
				return protocol + self.rot13(window.pmc_site_config.rot13_hostname);
			});
		}
		return url;
	},

	/**
	 * Get the property of an object, return default if not define / empty
	 */
	get_object_property: function ( the_object, property_name, default_value ) {
		if ( typeof the_object !== 'object' || ! the_object.hasOwnProperty( property_name ) ) {
			return default_value;
		}
		return the_object[ property_name ];
	},

	/**
	 * pass a var to it to check if its empty/null/undefined or not. If empty/null/undefined then it returns TRUE else FALSE
	 */
	is_empty: function(var_value) {
		if( ! var_value || var_value === null || var_value === undefined ) {
			return true;
		}
		var_value = var_value + "";	//convert to string to be able to do remove spaces from both ends
		var_value = var_value.replace(/^\s+|\s+$/g, "");	//scrub spaces from both ends
		if( var_value === "" ) {
			return true;
		}
		return false;
	},
	/**
	 * Check if JSON object is available or not
	 * @since 2014-02-18 Amit Gupta
	 */
	has_json: function() {
		if ( typeof JSON == 'undefined' || ! JSON ) {
			return false;
		}

		return true;
	},
	/**
	 * rounds `num` to number of decimal places specified in `dec`. If `dec` is empty then default is 2
	 */
	_round: function(num, dec) {
		dec = ( this.is_empty(dec) ) ? 2 : dec;
		return Math.round( num * Math.pow( 10, dec ) ) / Math.pow( 10, dec );
	},
	/**
	 * replace all instances of `needle` in `haystack` with `replace_with`
	 */
	_replace_all: function(needle, replace_with, haystack) {
		var pattern = new RegExp(needle, 'g');		//create RegEx pattern to replace all
		return haystack.replace(pattern, replace_with);
	},
	/**
	 * return a unix style timestamp
	 */
	timestamp: function() {
		return Math.round( +new Date() / 1000 );
	},
	/**
	 * Test all properties of an object in dot notation.
	 * @param string obj_str An object in dot notation, passed as a string.
	 * @return object|undefined
	 *
	 * @example if( typeof pmc.deeptest('PMC_Example.utils.foo') === 'function' ) { PMC_Example.utils.foo.call(); }
	 *
	 * @since 2013-08-26 Corey Gilmore
	 * @author kennebec@stackoverflow
	 * @see http://stackoverflow.com/questions/2631001/javascript-test-for-existence-of-nested-object-key comment by @kennebec
	 *
	 */
	deeptest: function(obj_str){
		obj_str = obj_str.split('.');
		var obj = window[obj_str.shift()];
		while(obj && obj_str.length) {
			obj = obj[obj_str.shift()];
		}
		return obj;
	},
	/**
	 * This function strips out HTML tags from a string and decodes all HTML
	 * entities to text equivalent
	 */
	decode_entities: function( text ) {
		var element = document.createElement('div');

		if( text && typeof text === 'string' ) {
			//strip out html tags
			text = text.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '').replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
			element.innerHTML = text;		//put the leftover html in DOM element
			text = element.textContent;		//grab the plain text version of our HTML
			element.textContent = '';		//nullify the DOM element
		}

		return text;
	},
	/**
	 * A function to strip out all special chars (except hyphens & underscores)
	 * and replace all spaces with hyphens. This takes in a string and makes it
	 * suitable for use in a URL.
	 */
	sanitize_title: function( text ) {
		if( ! text || this.is_empty( text ) || typeof text !== 'string' ) {
			return '';
		}
		var original_text = text;
		text = this.decode_entities( text );
		text = text.replace(/[^a-zA-Z0-9 \-_]/g,'');	//strip out undesired chars
		text = text.replace(/\s\-/g,'-').replace(/\-\s/g,'-').replace(/\s/g,'-');	//strip out spaces preceding/succeeding hyphens and convert spaces to hyphens
		text = text.replace(/\-_/g,'-').replace(/_\-/g,'-');	//convert all instances of _- and -_ into single hyphens
		text = text.replace(/\-{2,}/g,'-');	//convert all instances of multiple successive hyphens into single hyphens
		text = text.replace(/^\-/g,'').replace(/\-$/g,'');	//strip out any hyphens from beginning/end of text
		return text.toLowerCase();	//return lowercase string
	},
	/**
	 * A function to strip out all special chars (except hyphens & underscores)
	 * and removes all spaces and changes all alphabets to lowercase. This
	 * takes in a string and makes it suitable for use as a key name. This
	 * function mimics sanitize_key() in WordPress.
	 *
	 * @since 2015-03-03 Amit Gupta
	 */
	sanitize_key: function( text ) {
		if( ! text || this.is_empty( text ) || typeof text !== 'string' ) {
			return '';
		}

		var original_text = text;

		text = this.decode_entities( text );
		text = text.replace(/[^a-zA-Z0-9\-_]/g,'');	//strip out undesired chars
		text = text.replace(/^\-/g,'').replace(/\-$/g,'');	//strip out any hyphens from beginning/end of text

		return text.toLowerCase();	//return lowercase string
	},
	/**
	 * A function to open a pop-up window
	 *
	 * @since 2015-06-29 Amit Gupta
	 */
	popup: function( url, name, width, height ) {
		if ( typeof url === 'undefined' || this.is_empty( url ) ) {
			return false;
		}

		if ( typeof name === 'undefined' || this.is_empty( name ) ) {
			name = '_blank';
		}

		if ( typeof width === 'undefined' || this.is_empty( width ) ) {
			width = 550;
		}

		if ( typeof height === 'undefined' || this.is_empty( height ) ) {
			height = 600;
		}

		window.open( url, name, 'width=' + parseInt( width ) + ', height=' + parseInt( height ) );

		return true;
	},
	/**
	 * cookie sub-class
	 */
	cookie: {
		/**
		 * Retrieve a cookie by name.
		 *
		 * @TODO: implement the substr approach (under testing - it randomly snips out a char on either boundary), remove usage of loop
		 *
		 * @param string cookie_name Name of a cookie to retrieve a value for
		 *
		 * @return string|null Cookie value on success, null on failure.
		 */
		get: function(cookie_name) {
				var x, y, i;
				var arr_cookie = document.cookie.split(";");
				var arr_cookie_len = arr_cookie.length;
				for ( i = 0; i < arr_cookie_len; i++ ) {
					x = arr_cookie[i].substr( 0, arr_cookie[i].indexOf("=") );	//get cookie name
					y = arr_cookie[i].substr( arr_cookie[i].indexOf("=") + 1 );	//get cookie value
					x = x.replace(/^\s+|\s+$/g, "");	//scrub spaces from both ends of cookie name
					if( x == cookie_name ) {
						//cookie name matches the one we want, so return the value
						return unescape(y);
					}
				}
				return null;
		},

		/**
		 * Create a cookie
		 *
		 * @TODO - put in domain & secure parameters as well, so it'll be full-fledged & generic
		 *
		 * @param string cookie_name  The name of the cookie you're creating.
		 * @param string cookie_value The value of the cookie you're creating.
		 * @param int    expiry_secs  The number of seconds (from now) until the cookie expires.
		 *                            Passing 0 will expire the cookie when the browser session is complete.
		 * @param string cookie_path  A path to isolate the cookie to.
		 *                            Passing '/' will allow the cookie to function site-wide.
		 *
		 * @return null
		 */
		set: function(cookie_name, cookie_value, expiry_secs, cookie_path) {
			expiry_secs = ( typeof expiry_secs !== 'undefined' ) ? parseInt( expiry_secs, 10 ) * 1000 : 0;	//convert to milliseconds
			cookie_path = ( typeof cookie_path !== 'undefined' ) ? cookie_path : '';

			var l_date = new Date();
			var expiry_date = new Date( l_date.getTime() + expiry_secs );

			cookie_value = ( typeof cookie_value !== 'undefined' ) ? escape( cookie_value ) : '';
			cookie_value += ( pmc.is_empty( expiry_secs ) ) ? "" : "; expires=" + expiry_date.toUTCString();
			cookie_value += ( pmc.is_empty( cookie_path ) ) ? "; path=/" : "; path=" + cookie_path;

			document.cookie = cookie_name + "=" + cookie_value;
		},
		/**
		 * pass cookie name to delete the cookie
		 */
		expire: function(cookie_name, cookie_path) {
			cookie_path = ( pmc.is_empty(cookie_path) ) ? '' : cookie_path;
			this.set(cookie_name, "", 1, cookie_path);
		}
	},

	/*
	 * A function to load a script and does a callback after script is loaded
	 */
	load_script: function( src, callback, script_id ) {
		if ( typeof src === 'undefined' ) {
			return;
		}
		var loaded = false;
		var new_script = document.createElement('script');
		new_script.type = 'text/javascript';
		if ( typeof script_id !== 'undefined' ) {
			new_script.id = script_id;
		}
		new_script.async = true;
		new_script.src = src;
		if ( typeof callback === 'function' ) {
			new_script.onreadystatechange = new_script.onload = function() {
				if (!loaded) {
					loaded = true;
					callback();
				}
				loaded = true;
			};
		}
		var script = document.getElementsByTagName('script')[0];
		script.parentNode.insertBefore( new_script, script );
	},

	mobile_width: function( ignore_orientation ) {
		var width = 0;
		try {
			if ( typeof ignore_orientation === 'undefined' ) {
				ignore_orientation = false;
			}
			// determine device screen size and width base on device orientation
			if (ignore_orientation || typeof window.orientation === 'undefined' || window.orientation == 0 || window.orientation == 180 ) {
				width = window.screen.width;
			}
			else {
				width = window.screen.height;
			}
			// Some device such as Android have a default viewport width set to 320, while its physical screen width is 480.
			// We need to adjust the width accordingly to current viewport width.
			// First ensure it's a mobile useragent.
			var user_agent = "";
			if ( typeof navigator === "object" && typeof navigator.userAgent === "string" ) {
				user_agent = navigator.userAgent.toLowerCase();
			}
			var is_mobile = ( ( user_agent.indexOf("mobile") > -1 ) && ( user_agent.indexOf("ipad") === -1 ) );
			if ( is_mobile && typeof window.devicePixelRatio != 'undefined' && window.devicePixelRatio > 1.0 ) {
				width = width / window.devicePixelRatio;
			}
		} catch ( ignore ) {
			// do nothing
			// http://stackoverflow.com/questions/16613790/jslint-error-expected-ignore-and-instead-saw-ex
		}
		return width;
	},

	// Tracking sub class
	tracking: {
		// function to call back on removed
		callback_on_removed: function( tracking_object ) {

			if (
				'object' === typeof tracking_object &&
				'function' === typeof tracking_object.get_properties_string
			) {
				//Appending utm params to amphtml meta tag
				var amp_url = '';
				var ga_tracking_string = tracking_object.get_properties_string();
				if (
					0 < ga_tracking_string.length &&
					document.querySelector('link[rel="amphtml"]')
				) {
					amp_url = document.querySelector("link[rel='amphtml']").href;
					amp_url += '?' + ga_tracking_string;
					document.querySelector("link[rel='amphtml']").href = amp_url;
				}
			}
		},

		// variable use for storing the tracking tokens that are removed
		_tokens: [],

		// a function to extract the tracking tokens
		_extract_tokens: function() {
			// always extract tracking value once regardless if function remove_from_browser_url has been called
			if ( typeof this._extraced !== 'undefined' ) {
				return;
			}
			this._extraced = true;
			this.remove( window.location.hash, true );
			this.remove( window.location.search, true );
		},

		// a function to return tokens property
		get_properties: function() {
			this._extract_tokens();
			var name, arr = [];
			for( name in this._tokens ) {
				arr.push( { name: name, value: this._tokens[name] } );
			}
			return arr;
		},

		// a function to return tracking tokens in querystring format
		get_properties_string: function() {
			this._extract_tokens();
			var name, arr = [];
			for( name in this._tokens ) {
				arr.push( name + '=' + this._tokens[name] );
			}
			return arr.join('&');
		},

		get_property:  function (name, default_value) {
			this._extract_tokens();
			return ( typeof name === 'undefined' || typeof this._tokens[name] === 'undefined' ) ? default_value : this._tokens[name];
		},

		/*
		 * A function to strip campaign tracking code
		 */
		remove: function( bufs, extract_tokens ) {
			var tracking = this;

			// https://regex101.com/r/x7BFP6/1
			var re_tokens = new RegExp( '^(utm_[a-z]+|token)=' );
			return bufs.replace(/(\?|#)([^#]*)/g, function(_, delim, search) {
				search = search.split( /&amp;|&/ ).map( function( v ) {
						if ( extract_tokens && re_tokens.test( v ) && v ) {
							var pair = v.split('=',2);
							tracking._tokens[pair[0]] = pair[1];
						}
						return ! re_tokens.test( v ) && v;
					}).filter(Boolean).join('&');
				return search ? delim + search : '';
			});
		}, // remove

		/*
		 * A function to strip campaign tracking code from browser url
		 */
		remove_from_browser_url: function() {
			if ( typeof this._removed !== 'undefined' ) {
				return;
			}
			this._removed = true;

			var scrollV, scrollH, loc = window.location;

			if ("replaceState" in history) {
				history.replaceState({}, document.title, loc.pathname + this.remove( loc.search, true ) + this.remove( loc.hash, true ) );   // HTML5, change URL display without reload
			} else {
				// Prevent scrolling by storing the page's current scroll offset
				scrollV = document.body.scrollTop;
				scrollH = document.body.scrollLeft;

				loc.hash = this.remove( loc.hash, true );   // older browser can only change hash value without reload

				// Restore the scroll offset, should be flicker free
				document.body.scrollTop = scrollV;
				document.body.scrollLeft = scrollH;
			}

			if ( 'function' === typeof this.callback_on_removed ) {
				this.callback_on_removed( this );
			}

		}, // remove_from_browser_url

		/*
		 * function to call other events after GA tracking fired
		 */
		do_call_events: function() {

			this.remove_from_browser_url();

		}
	},	// Tracking sub class

	/**
	 * Copies all the properties of config to the specified object.
	 * From ExtJS
	 * @param {Object} object The receiver of the properties
	 * @param {Object} config The source of the properties
	 * @param {Object} defaults A different object that will also be applied for default values
	 * @return {Object} returns obj
	 *
	 * @since 2014-07-07 Corey Gilmore
	 */
	apply: function(object, config, defaults) {
		if (defaults) {
			pmc.apply(object, defaults);
		}

		if (object && config && typeof config === 'object') {
			var i;

			for (i in config) {
				object[i] = config[i];
			}
		}

		return object;
	},

	/**
	 *
	 *
	 * @since 2014-07-07 Corey Gilmore
	 */
	flash: function( el, opts ) {
		el = jQuery(el);

		if( !el.length ) {
			return;
		}

		opts = pmc.apply( {}, opts, {
			highlight: '#FFFF99',
			in_ms: 700,
			out_ms: 1200
		});

		el.animate({
			backgroundColor: opts.highlight
		}, opts.in_ms, 'linear', function() {
			el.animate({
				backgroundColor: 'transparent'
			}, opts.out_ms, 'linear', function() {
				el.css({'background':'none', backgroundColor : ''});
			});
		});
	},

	/**
	 * PHP trim() recreated in Javascript, sourced from php.js project
	 * @see http://phpjs.org/functions/trim/
	 *
	 * @since 2016-02-12 Amit Gupta
	 */
	trim: function( str, charlist ) {

		var whitespace, l = 0,
		i = 0;
		str += '';

		if ( ! charlist ) {
			// default list
			whitespace = ' \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000';
		} else {
			// preg_quote custom list
			charlist += '';
			whitespace = charlist.replace( /([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1' );
		}

		l = str.length;
		for ( i = 0; i < l; i++ ) {

			if ( whitespace.indexOf( str.charAt( i ) ) === -1 ) {
				str = str.substring( i );
				break;
			}

		}

		l = str.length;
		for ( i = l - 1; i >= 0; i-- ) {

			if ( whitespace.indexOf( str.charAt( i ) ) === -1 ) {
				str = str.substring( 0, i + 1 );
				break;
			}

		}

		return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';

	}

}); // pmc


/**
 * String formatting using an numeric parameters.
 *
 * Usage: 'Fezzik, {0} there {1} ahead? If there {0}, we all be {2}.'.format( 'are', 'rocks', 'dead' )
 * Yields: 'Fezzik, are there rocks ahead? If there are, we all be dead.'
 *
 * @since 2014-07-03 Corey Gilmore
 *
 */
if( typeof String.prototype.format !== 'function' ) {
	String.prototype.format = function() {
		var args = arguments;
		return this.replace(/\{(\d+)\}/g, function(match, number) {
			return (typeof args[number] !== 'undefined' ? args[number] : match);
		});
	};
}

/**
 * String formatting using an named parameters. From Stack Overflow.
 *
 * Usage: 'Fezzik, are there {what} ahead? If there are, we all be {status}.'.formatUnicorn( { what: 'rocks', status: 'dead' } )
 * Yields: 'Fezzik, are there rocks ahead? If there are, we all be dead.'
 *
 * http://stackoverflow.com/a/18234317
 * http://meta.stackexchange.com/questions/207128/what-is-formatunicorn-for-strings
 * @since 2014-07-03 Corey Gilmore
 *
 */
if( typeof String.prototype.formatUnicorn !== 'function' ) {
	String.prototype.formatUnicorn = function() {
		var n, i, t, e = this.toString();

		if( !arguments.length ) {
			return e;
		}

		t = typeof arguments[0];
		n = 'string' == t || 'number' == t ? Array.prototype.slice.call(arguments) : arguments[0];

		for( i in n )  {
			e = e.replace(new RegExp( '\\{' + i + '\\}', 'gi' ), n[i]);
		}
		return e;
	};
}

pmc.analytics = pmc.analytics || [];
pmc.generate_pvuuid();
