var Variety_Digital = {
	options: {
		login_path: '/digital-subscriber-access/',
		secure_pattern: 'digital-subscriber-access|access-digital'
	}, // options

	// access digital edition where:
	// issue_id = 'l' to access latest issue
	// issue_id = number access a specific issue
	access_digital: function( issue_id ) {
		var query = '';

		if ( issue_id ) {
			query = '?eid=' + issue_id;
		}

		if ( ! uls.session.can_access( 'vy-digital' ) ) {
			this.goto_login_page( query );
			return;
		}

		window.location.href   = variety_digital.ereader_url + query;
		return;
	}, // access_digital

	goto_login_page: function ( query ) {
		if ( 'undefined' !== typeof query ) {
			window.location.search = query;
		}

		Variety_Authentication.goto_secured_path( this.options.login_path );
	},

	goto_home_page: function() {
		window.location = '/';
	},

	// function to return a query request from ?querystring or #hash
	get_query: function ( name ) {
		name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");

		// Find the query you are looking for either in a query string or hash.
		// ref: https://regex101.com/r/7HYblz/1
		var regX = new RegExp( "[\\?&]"+name+"=([^&#]*)" ),
			querystring = window.location.search + '&' + window.location.hash.replace('#', ''),
			results = regX.exec( querystring );

		if ( results ) {
			return decodeURIComponent(results[1].replace(/\+/g, " "));
		}

		return false;
	} // get_query

};

(function($) {
	$(function() {
		// Securing the page before we do anything else
		Variety_Authentication.make_secure();
		// process action request
		switch ( Variety_Digital.get_query('action') ) {
			case 'logout':
				Variety_Authentication.logout( function( status ) {
					$('#login-error').html('You are now logged out of Variety.com').fadeIn();
					Variety_Digital.goto_home_page();
				});
				break;
			default:
				var issue_id = Variety_Digital.get_query('eid');
				if ( issue_id ) {
					Variety_Digital.access_digital( Variety_Digital.get_query('eid') );
				}
			break;
		}

		// login form click
		$('#loginform #login-submit').on('click', function(e) {
			e.preventDefault();

			var username       = $('#loginform #user_login').val(),
				password       = $('#loginform #user_pass').val(),
				persist        = ( $('#loginform #rememberme').prop('checked') ? 1 : 0 ),
				delete_session = $('#loginform #delete_session').val();

			if ( ! username || ! password ) {
				alert('User name and password are required.')
				return;
			}

			if ( 'undefined' !== typeof VarietyEvent ) {
				try {
					VarietyEvent.track( 'subscribe-page', 'already-button', 'click', 0, 0 );
				} catch(err) { }
			}

			// Reset login error
			$('#login-error').hide();
			$('#login-submit').attr('value', 'Sign in');

			uls.api.login( username, password, persist, {
				delete_session: delete_session,
				success: function( data ) {
					var issue_id = Variety_Digital.get_query('eid');

					if ( issue_id ) {
						Variety_Digital.access_digital( issue_id );
					} else {
						// `ret` is short for return.
						var ret = Variety_Digital.get_query('r');

						if ( ! ret ) {
							ret = '/';
						}

						window.location.search = '';
						Variety_Authentication.goto_secured_path(ret);
					}
				},
				error: function( data ) {
					var reason = pmc.get_object_property( data, 'reason', false ),
						message = uls.message.build_text( data );

					switch( reason ) {
						case 'has_session':
							$('#login-submit').attr('value','Clear Session');
							$('#delete_session').attr('value', '1');
							break;
					}

					$('#login-error').text( message ).fadeIn();
				}
			} );

			return false;
		}); // login form submit button click

		// ga tracking
		$('.variety-digital-subscriber-access .tracking-button').on('click', function(e) {
			var category  = $(this).data('category'),
				action    = $(this).data('action'),
				label     = $(this).data('label');

			if ( 'undefined' !== typeof VarietyEvent ) {
				try {
					VarietyEvent.track(category, action, label, 0, 0 );
				} catch(err) { }
			}
			return true;
		}); // ga tracking
	});
})(jQuery);
