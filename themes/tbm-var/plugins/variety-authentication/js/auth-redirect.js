/* eslint-disable */
var PMC_CORE_AuthRedirect = PMC_CORE_AuthRedirect || function() {
	var loaded;
	if ( typeof loaded != 'undefined' ) {
		return;
	}
	loaded = true;

	jQuery(document).ready(function(){
		var params = [];

		try {
			if (window.location.hash.startsWith('#login&')) {
				var tokens = window.location.hash.substring(7).split('&');
				for( idx in tokens ) {
					pair = tokens[idx].split('=',2);
					params[pair[0]] = pair[1];
				}
			}
		} catch(ignore) {
		}

		function message_handler( e ) {
			try {
				if ( 'reload-page' == e.data && e.origin.match('\.%%change_me%%\.com$|//%%change_me%%\.com$|//%%change_me%%\.vip\.local$') ) {
					if ( typeof(params['goto']) != 'undefined' ) {
						window.location = decodeURIComponent(params['goto']);
						return;
					}
					window.location.reload();
				}
			} catch(ignore) {
			}
		}

		try {
			if ( window.addEventListener ) {
				window.addEventListener('message',message_handler);
			} else if ( window.attachEvent ) {
				window.attachEvent('onmessage',message_handler);
			}

			if ( typeof params['goto'] != 'undefined' ) {
				if ( ( jQuery.cookie('uls_username') && jQuery.cookie('uls_token') )
					|| ( jQuery.cookie('uls2_username') && jQuery.cookie('uls2_token') )
					) {
					if ( typeof(params['goto']) != 'undefined' ) {
						window.location = decodeURIComponent(params['goto']);
					}
				} else {
					if ( jQuery('#login-form').length ) {
						jQuery('#login-form').slideToggle();
					} else {
						jQuery('.login-menu').fadeIn('fast');
					}
				}
			}
		} catch(ignore) {
		}

		// Prevent Zooming in mobile.
		(function($) {
			var $viewportMeta = $('meta[name="viewport"]');
			var $form = $('#login-form');

			var toggleMeta = function() {
				setTimeout( function() {
					if ($form.is(':visible')) {
						$viewportMeta.attr('content', 'width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1');
					} else {
						$viewportMeta.attr('content', 'width=device-width');
					}
				}, 500 );
			}

			$('a.login-link').click(function() {
				toggleMeta();
			});

			$('#login-form .icon-close-circle').click(function() {
				toggleMeta();
			});
		})(jQuery);

	});
}

PMC_CORE_AuthRedirect();
/* eslint-enable */
