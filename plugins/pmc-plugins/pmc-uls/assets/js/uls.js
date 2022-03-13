/* global pmc */

// workaround for IE not supporting String.includes
if (typeof String.prototype.includes === 'undefined') {
	String.prototype.includes = function(it) { return this.indexOf(it) != -1; };
}

var uls = uls || {
	ping_back_grace_minute: 5,
	fast: 0,
	url: "https://uls." + document.location.hostname.match(/([^.]+\.[^.]+$)/)[0],
	cookie_prefix: 'uls3',
	is_go: 0,
}; // uls

uls.session = uls.session || {

	get: function( key, default_value ) {
		var value = pmc.cookie.get( uls.cookie_prefix + '_' + key );
		if ( value ) {
			return value;
		}
		if ( typeof default_value !== 'undefined' ) {
			return default_value;
		}
		return false;
	},
	can_access: function( code ) {
		if ( ! uls.session.get( 'user_id' ) ) {
			return false;
		}
		var entitlement = uls.session.get( 'entitlement' );
		if ( ! entitlement ) {
			return false;
		}
		return entitlement.includes( code );
	},
	is_bypass: function() {
		return uls.session.get( 'bypass' );
	},
	is_valid: function() {

		// Check group_id first to catch site license user/ip access
		if ( 0 < this.get( 'group_id' )  ) {
			return true;
		}

		// If no group is present, check for a user_id which indicates a CDS/ESP user
		if ( 0 < this.get( 'user_id' ) ) {
			return true;
		}

		return false;
	}

}; // uls.session

uls.caching = uls.caching || {

	login: function() {
		var self = this;

		self.call();
	},

	update: function() {
		var self = this;

		self.call();
	},

	logout: function() {
		var self = this;

		self.call( { doing: 'logout' } );
	},

	call: function( data ) {

		if ( "undefined" == typeof uls.is_go || "1" !== uls.is_go ) {
			return;
		}

		jQuery.ajax({
			async: false,
			type: 'GET',
			url: ajaxurl + '?action=pmc_uls_do_cache_segment_ajax_callback',
			cache: false,
			xhrFields: {
				withCredentials: true
			},
			data: data
		});
	}
}; // uls.caching

uls.api = uls.api || {
	endpoint: '/api/v3/',
	endpoint_v2: '/api/v2/',
	overlay_count: 0,

	set_overlay_processing: function ( active ) {
		var self = this;
		var overlay = jQuery('#uls_overlay_ajax_loading');
		if ( active ) {
			self.overlay_count += 1;
			if ( ! overlay.length ) {
				overlay = jQuery('<div></div>').attr('id','uls_overlay_ajax_loading');
				jQuery('body').append( overlay );
			}
			jQuery( overlay ).show();
		}
		else if ( overlay.length ) {
			self.overlay_count -= 1;
			if ( self.overlay_count <= 0 ) {
				self.overlay_count = 0;
				jQuery( overlay ).hide();
			}
		}
	},

	ping: function( options ) {
		var self = this;
		var fast = '';

		if ( typeof options == 'object' && typeof options.fast !== 'undefined' && options.fast ) {
			fast = '/fast';
		}

		self.call( 'GET', self.endpoint + 'session/ping' + fast, {
			show_busy: false,
			data: typeof options == 'object' && typeof options.data === 'object' ? options.data : null,
			error: function() {
				if ( typeof options === 'object' && typeof options.error === 'function' ) {
					try {
						options.error.apply( self, [] );
					} catch ( e ) {}
				}
			},
			success: function(data) {

				if ( typeof data == 'object' && typeof data.refresh != 'undefined' && data.refresh ) {
					uls.caching.update();
				}

				if ( typeof options === 'object' && typeof options.success === 'function' ) {
					try {
						options.success.apply( self, [ data ] );
					} catch ( e ) {}
					return;
				}

				if ( typeof data == 'object' && typeof data.refresh != 'undefined' && data.refresh ) {
					window.location.reload();
				}
			}
		});
	},

	otll: function( username, options ) {
		var self = this;
		self.call( 'GET', self.endpoint_v2 + 'otll/user/' + username, {
			success: function( data ) {
				try {
					options.success.apply( self, [ data ]);
				} catch ( e ) {
					window.console.log( e );

					// continue regardless of error

				}
			},
			error: function( data ) {
				try {
					options.error.apply( self, [ data ]);
				} catch ( e ) {
					window.console.log( e );

					// continue regardless of error

				}
			}
		});
	},

	logout: function( options ) {
		var self = this;
		self.call( 'GET', self.endpoint + 'session/destroy', {
			success: function() {

				uls.caching.logout();

				if ( typeof options === 'object' && typeof options.success === 'function' ) {
					try {
						options.success.apply( self );
					} catch ( e ) {}
					return;
				}

				window.location.reload();
			}
		});
	},

	login: function ( username, password, rememberme, options ) {
		var self = this;
		if ( ! username || ! password ) {
			return;
		}
		self.call( 'POST', self.endpoint + 'session/authenticate', {
			data: {
				username      : username,
				password      : password,
				rememberme    : rememberme,
				delete_session: pmc.get_object_property( options, 'delete_session', 0 )
			},
			error: function() {
				if ( typeof options === 'object' && typeof options.error === 'function' ) {
					try {
						options.error.apply( self, [] );
					} catch ( e ) {}
				}
			},
			success: function( data ) {

				try {
					if ( ! data.error && data.user_id ) {

						uls.caching.login();

						if ( typeof options === 'object' && typeof options.success === 'function' ) {
							try {
								options.success.apply( self, [ data ] );
							} catch ( e ) {}
							return;
						}

						if ( window.location.pathname.search(/\/login/) > -1 ) {
							window.location = window.location.pathname.replace(/login.*/,'');
							return;
						}
						window.location.reload();

					}
				} catch( e ) {}

				// error
				if ( typeof options === 'object' && typeof options.error === 'function' ) {
					try {
						options.error.apply( self, [ data ] );
					} catch ( e ) {}
				}

			}
		});
	},

	get_provider: function( email, callback ) {
		var self = this;

		if ( ! email ) {
			return;
		}

		self.call( 'GET', self.endpoint + 'subscription/provider/' + email, {
			error: function( response ) {
				try {
					callback.apply( null, [ response ] );
				} catch ( e ) {}
			},
			success: function( response ) {
				try {
					callback.apply( null, [ response ] );
				} catch ( e ) {}
			}
		} );
	},

	call: function( method, endpoint, options ) {

		// if uls's url not configured, ULS is disabled.
		if ( ! uls.url ) {
			return false;
		}

		var self = this;
		var data = typeof options === 'object' && typeof options.data !== 'undefined' ? options.data : null;
		var contentType = typeof options == 'object' && typeof options.contentType !== 'undefined' ? options.contentType : 'application/x-www-form-urlencoded';
		if ( 'application/json' === contentType ) {
			data = JSON.stringify( data );
		}
		jQuery.ajax({
			async: true,
			type: method,
			url: uls.url + endpoint,
			cache: false,
			xhrFields: {
				// We need to send uls session cookies back to uls domain for validation
				withCredentials: true
			},
			data: data,
			contentType: contentType,
			beforeSend: function() {
				if ( typeof options !== 'object' || typeof options.show_busy === 'undefined' || options.show_busy ) {
					self.set_overlay_processing( true );
				}
			},
			complete: function() {
				if ( typeof options !== 'object' || typeof options.show_busy === 'undefined' || options.show_busy ) {
					self.set_overlay_processing( false );
				}
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				if ( typeof options === 'object' && typeof options.error === 'function' ) {
					try {
						options.error.apply( self, [ jqXHR, textStatus, errorThrown ] );
					} catch ( e ) {}
				}
			},
			success: function(data, textStatus, jqXHR ) {
				if ( typeof options === 'object' && typeof options.success === 'function' ) {
					try {
						options.success.apply( self, [ data, textStatus, jqXHR ] );
					} catch ( e ) {}
				}
			}
		});
	}

}; // uls.api

uls.bluetoad = uls.bluetoad || {

	/**
	 * Helper function to transfer user access request to bluetoad
	 */
	access_issue: function( issue_id, publication_id, publisher_id, target_domain ) {
		if ( typeof issue_id === 'undefined' || !issue_id ) {
			issue_id = 'l';
		}

		if ( typeof publication_id === 'undefined' || ! publication_id ) {
			return;
		}

		if ( typeof publisher_id === 'undefined' || ! publisher_id ) {
			publisher_id = 5153;
		}

		if ( typeof target_domain === 'undefined' || ! target_domain ) {
			target_domain = "bluetoad.com";
		}

		uls.api.ping( {
			data: { token: true },
			success: function( data ) {
				try {
					if ( data && data.valid && data.username && data.token ) {

						// Bluetoad doesn't only support custom authentication via a login form
						// To prevent exposing user's password on bluetoad, we relying on ULS
						// to generate a temp token to act as a password so we can pass to bluetoad
						// for validation using bluetoad custom authentication implementation
						// without prompting the user for their credential again.
						var form = jQuery('<form />', { id: "bluetoad-form", action: 'https://www.bluetoad.com/publication/logincheck.php', method: 'POST' }).append(
									jQuery('<input />', { name: 'registration', type: 'hidden', value: 0 }),
									jQuery('<input />', { name: 'pub_id', type: 'hidden', value: publisher_id }),
									jQuery('<input />', { name: 'm', type: 'hidden', value: publication_id }),
									jQuery('<input />', { name: 'l', type: 'hidden', value: issue_id === 'l' ? 1 : 0 }),
									jQuery('<input />', { name: 'i', type: 'hidden', value: issue_id != 'l' ? issue_id : 0 }),
									jQuery('<input />', { name: 'reader_login', type: 'hidden', value: data.username }),
									jQuery('<input />', { name: 'reader_password', type: 'hidden', value: data.token }),
									jQuery('<input />', { name: 'targ_dom', type: 'hidden', value: target_domain })
							);
						// need this for firefox to work
						jQuery('body').append(form);
						jQuery(form).submit();
						// sensitive info, need to remove
						jQuery(form).remove();

					}
				}
				catch(e) {}

			}
		} );

		return;
	} // access_digital

};

/**
 * ULS message related
 */
uls.message = uls.message || {
	/**
	 * The default mapping of uls reason code into text message
	 */
	mapping: {
		default                   : 'You have entered an incorrect Username or password, please try again.',
		contact_support           : 'Please contact support.',
		inactive                  : 'Your account is inactive.',
		subscription_expired      : 'Your account is expired.',
		subscription_invalid      : 'There is some issue with your subscription.',
		has_session               : 'You are logged in to too many devices. To clear all your sessions, re-enter your credential and click on "Clear Session"',
		group_access_limit_reached: 'Your organization\'s concurrent seat limit has been reached. You won\'t have access to subscriber only content until someone else\'s session expires. Please try again in a few minutes.',
		group_subscription_expired: 'Your organization\'s subscription has expired or is inactive.',
		group_subscription_invalid: 'There is some issue with your organization\'s subscription.',
		contact_site_license_admin: 'Please contact your site license administrator at [support_email] for support.'
	},

	/**
	 * Helper function to replace [variable] in a text message string
	 * @param  {object} data    The ULS api result object
	 * @param  {string} message The template message string
	 * @return {string}         The translated message
	 */
	replace_variables: function( data, message ) {
		var keys = [ 'support_email' ];
		jQuery( keys ).each( function( i, k ) {
			var v = pmc.get_object_property( data, k, false );
			if ( v ) {
				var regx = new RegExp( '\\[' + k + '\\]' );
				message = message.replace( regx, v );
			}
		} );
		return message;
	},

	/**
	 * Build the text detail messages from ULS api result object
	 * @param  {object} data          The ULS api result object
	 * @param  {string} default_value The default message string
	 * @return {string}               The translated text message
	 */
	build_text: function( data, default_value ) {
		var reason = pmc.get_object_property( data, 'reason', false );
		var message = ! pmc.is_empty( default_value ) ? default_value : this.mapping.default;
		switch( reason ) {
			case 'inactive':
			case 'subscription_expired':
			case 'subscription_invalid':
				message = this.mapping[ reason ] + ' ' + this.mapping.contact_support;
				break;

			case 'has_session':
			case 'group_access_limit_reached':
				message = this.mapping[ reason ];
				break;

			case 'group_subscription_expired':
			case 'group_subscription_invalid':
				message = this.mapping[ reason ];
				if ( typeof data.support_email !== 'undefined' ) {
					message += ' ' + this.mapping.contact_site_license_admin;
				}
				break;

		}

		return this.replace_variables( data, message );

	},

	/**
	 * Wrap the DOM object around the text message, see build_text function
	 */
	build_dom_element: function( data, default_value ) {
		return jQuery('<div/>').append( this.build_text( data, default_value ) );
	}
};

// Do async auto ping back uls to keep session active
jQuery(document).ready(function(){
	var session_exp = uls.session.get( 'session_exp' );
	var autologin   = uls.session.get( 'autologin' );

	// no session cookie, don't do anything
	if ( ! session_exp && ! autologin ) {
		return;
	}

	try {
		var trigger_fast_api     = parseInt( uls.fast );
		var minute_until_expired = ! session_exp ? 0 : ( Date.parse( session_exp.replace(/\+/g,' ') ) - Date.now() ) /1000/60;
		var need_pingback        = minute_until_expired < uls.ping_back_grace_minute;

		// For security reason, we can't read the remember token
		// If we detect no entitlement cookie, it's mean session is invalid
		// But we have session_exp or remember_me cookie, this mean we need to re-new the session
		if ( ! uls.session.get('entitlement') ) {
			trigger_fast_api = false;	// To trigger session re-authorized, we need to pingback on the normal route
			need_pingback = true;
		}

		if ( need_pingback ) {

			uls.api.ping({
				fast: trigger_fast_api,
				success: function( data ) {
					try {
						if ( typeof data === 'undefined' ) {
							return;
						}
						if ( typeof data.error !== 'undefined' && data.error ) {
							pmc.hooks.do_action( 'uls.ping.error', data );
						}
						if ( typeof data.refresh !== 'undefined' && data.refresh ) {
							pmc.hooks.do_action( 'uls.ping.refresh', data );
						}
					} catch(e) {}
				}
			});
		}

	}
	catch(e) {}

});

