/*!
 * Socialite Plugin
 * Copyright (c) 2013 PMC
 * Author: PMC, Hau Vong
 */

jQuery(function(){
	if ( typeof Socialite === 'undefined' || typeof jQuery.fn.waypoint !== 'function' ) {
		return;
	}

	if ( typeof window._socialite_settings === 'object' ) {
		Socialite.setup( window._socialite_settings );
	}


	jQuery(".social").waypoint(function() {
			Socialite.load(this);
		},
		{
			offset: '150%',
			triggerOnce: true
		});
});

if ( typeof Socialite === 'object' ) {

	Socialite.network('pinterest',{
		script: {
			src: '//assets.pinterest.com/js/pinit.js',
			id:  'pinterest-js'
		}
	});


	Socialite.widget('pinterest','pin-it',{
		init: function(instance) {
			var params = '';
			if ( jQuery(instance.el).attr('data-media') ) {
				params += "&media=" + encodeURIComponent(jQuery(instance.el).attr('data-media'));
			}
			if ( jQuery(instance.el).attr('data-description') ) {
				params += "&description=" + encodeURIComponent(jQuery(instance.el).attr('data-description'));
			}
			var el = jQuery('<a class="pin-it-button"><img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>')
				.attr('href','http://pinterest.com/pin/create/button/?url='+ encodeURIComponent(jQuery(instance.el).attr('data-href')) + params);

			jQuery(jQuery(instance.el).prop('attributes')).each(function(){
				if ( /^data-pin/.test(this.name) ) {
					jQuery(el).attr(this.name,this.value);
				}
			});
			jQuery(instance.el).append(el);

			if ( typeof Socialite.settings.pinterest != 'undefined' ) {
				var settings = Socialite.settings.pinterest;
				if ( typeof settings.onclick == 'function' ) {
					jQuery(el).on('click', settings.onclick );
					jQuery(jQuery(instance.el)).on('click','a',settings.onclick);
				}
			}
		}

	});

}