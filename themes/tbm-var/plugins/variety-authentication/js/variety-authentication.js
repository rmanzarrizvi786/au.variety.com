var Variety_Authentication = {

	logout: function( callback_on_done ) {

		uls.api.logout( {
			success: function( data ) {
				if ( typeof callback_on_done == 'function' ) {
					callback_on_done(true);
				}
			},
			error: function( data ) {
				if ( typeof callback_on_done == 'function' ) {
					callback_on_done(false);
				}
			}
		} );

	} // logout

	,authorized: function() {
		return uls.session.can_access( 'vy-digital' );
	} // authorized

	,get_protected_data: function( options ) {
		var return_data = { status: false };
		var data_type = typeof options.data_type != 'undefined' ? options.data_type : '';
		var data_args = typeof options.data_args != 'undefined' ? options.data_args : '';
		var async = typeof options.async != 'undefined' ? options.async : false;
		var success = typeof options.success != 'undefined' ? options.success : false;
		var error = typeof options.error != 'undefined' ? options.error : false;

		jQuery.ajax({
			async: async,
			type: "POST",
			url: variety_authentication_object.ajax_url,
			xhrFields: {
				withCredentials: true
			},
			data: {
				action: 'variety_authentication',
				cmd: 'get-protected-data',
				data_type: data_type,
				data_args: data_args,
				security: variety_authentication_object.ajax_nonce
			},
			success: function(data, textStatus, jqXHR) {
				if ( typeof data.status != 'undefined' ) {
					return_data = data;
				}
				if ( typeof success === 'function' ) {
					success( data, textStatus, jqXHR );
				}
			},
			error: function(data, textStatus, jqXHR) {
				if ( typeof error === 'function' ) {
					error( data, textStatus, jqXHR );
				}
			}
		});
		return return_data;
	} // get_credential

	// redirect user to a secured url path
	,goto_secured_path: function( path ) {
		if ( typeof path == 'undefined' || !path ) {
			if ( window.location.protocol !== 'https:' ) {
				var location = 'https://' + window.location.host + window.location.pathname + window.location.hash;
				window.location = location;
			}
			return;
		}
		if ( window.location.pathname != path ) {
			var location = 'https://' + window.location.host + path + window.location.hash;
			window.location = location;
		}
	}

	// make all pages secured that are matching the secure pattern
	,make_secure: function() {
		this.goto_secured_path();
	}

};

