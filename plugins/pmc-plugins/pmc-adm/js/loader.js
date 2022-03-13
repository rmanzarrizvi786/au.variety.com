/**
 * To create minified version of loader.min.js:
 * - npm install uglify -g (to install uglify)
 * - uglify -s loader.js -o loader.min.js
 *
 * Version: 5.8
 *
 * To Generate a Minified Version:
 * npm install uglify -g
 * cd pmc-adm/js/
 * uglify -s loader.js -o loader.min.js
 */
/* eslint linebreak-style: 0 */
/* global jQuery, pmc, blogherads, pmc_meta */

var pmc_adm_boomerang = {},
	pmc_adm_cmp_interval,
	pmc_adm_cmp_interval_tries = 1;

(function(window, $) {

	window.AdLoader = function(id, map) {
		var self = this,
		wrapper = $(id);

		/**
		 * Bind all events and prepare the class.
		 */
		self.initialize = function() {

		}
	};

})(window, jQuery);

/**
 * Doubleclick Ad functions.
 * @type {{load_ad: Function, rotateAd: Function}}
 */
var pmc_adm_doubleclick = {

	load_ad: function () {
		ad_Iframe_div = jQuery(".pmc-adm-iframe-div").eq(0);

		if (ad_Iframe_div.length > 0) {

			var doc_width = jQuery(document).width();
			var ordnum = Math.random() * 1000000000000000000;

			adIframe = jQuery('<iframe class="pmc-adm-iframe ' + ad_Iframe_div.data('adclass') + '" src="'
				+ ad_Iframe_div.data('adurl') + ';ord=' + ordnum + '" width="'
				+ ad_Iframe_div.data('adwidth') + 'px" height="'
				+ ad_Iframe_div.data('adheight')
				+ 'px"	marginwidth="0" marginheight="0"  frameborder="0" scrolling="no"></ifr'
				+ 'ame>');

			ad_Iframe_div.replaceWith(adIframe);

			if (ad_Iframe_div.data('adrender') > 0) {
				setTimeout(pmc_admanager.load_ad(), ad_Iframe_div.data('adrender'));
			}
			else if (ad_Iframe_div.data('adrender') == 0) {
				adIframe.load(function () {
					this.load_ad();
				});
			} else {
				this.load_ad();
			}
		} // if
	},
	rotateAd: function (cls) {

		var rotateClass = cls ? cls : 'pmc-adm-iframe';
		rotateClass = '.' + rotateClass;
		var ordnum = Math.random() * 1000000000000000000;

		jQuery(rotateClass).each(function () {
			sourceUrl = jQuery(this).attr("src");
			sourceUrl = sourceUrl.replace(/ord=[\.0-9]+/i, "ord=" + ordnum);
			jQuery(this).attr({
				src: sourceUrl
			});
		});
	}
};

/**
 * Google Publisher Ad Tags.
 */
var googletag = googletag || {};
googletag.cmd = googletag.cmd || [];

/**
 * Google Publisher Tags ad functions
 *
 * @since 2013-11-08 Amit Gupta
 * @version 2013-11-12 Amit Gupta
 * @version 2014-02-18 Amit Gupta - added set_location()
 * @version 2016-11-10 Brandon Camenisch - removed set_location()
 */
var pmc_adm_gpt = {
	skin_targeting_kw: 0,
	ad_slots: {},
	settings: {},
	rendered: {},
	render_ad_type: 'default',
 	should_refresh_ads: true,
	pmc_lazy_ads_observer: {},
	auto_refresh_time_limit: 30000,// milliseconds.
	auto_ad_refresh_inactive_time: 0,
	auto_ad_refresh_timers: [],
	auto_ad_refresh_interval_id: 0,
	auto_ad_refresh_status: false,

	init: function( settings ) {

		var io_config = {
			root: null, // avoiding 'root' or setting it to 'null' sets it to default value: viewport
			rootMargin: '500px 0px 500px 0px',
			threshold: 0
		};

		this.process_ccpa_check();

		this.pmc_lazy_ads_observer = new IntersectionObserver( this.render_lazy_ads, io_config );

		// Optionally send a page-level 'referrer' targeting key/value
		if ( 'enable' === pmc_adm_config.page_level_referrer_targeting ) {
			this.set_referrer_targeting_keyword();
		}

		this.settings = this.apply_filters( 'pmc_adm_gpt_settings', settings );
		var self = this;

		googletag.cmd.push( function() {
			var slot_element_id;

			for ( var t in self.settings.ad_list ) {
				var ads = self.settings.ad_list[t];
				if ( "undefined" !== typeof pmc_adm_has_time_gap_ads && false === pmc_adm_has_time_gap_ads && 'time_gap_ads' === t ) {
					continue;
				}

				for ( var i in ads ) {
					var ad = ads[i];
					var slot;
					// is ad restricted by devices?
					if ( typeof ad['devices'] != 'undefined' && typeof ad['devices'].length == 'number' ) {
						if ( !self.is_device( ad['devices'] ) ) {
							continue;
						}
					}
					ad['slot'] = pmc_adm_gpt.apply_filters( 'pmc-adm-gpt-ad-slot', ad['slot'] );
					if ( typeof ad['oop'] != 'undefined' && ad['oop'] ) {
						slot = googletag.defineOutOfPageSlot( self.apply_slot_filter( ad['slot'] ), ad['id'] );
					} else {
						slot = googletag.defineSlot( self.apply_slot_filter( ad['slot'] ), ad['width'], ad['id'] );
					}

					slot_element_id = slot.getSlotElementId(); // Get slot element Id.

					if ( 'undefined' !== typeof slot_element_id && -1 < slot_element_id.indexOf( 'gallery' ) ) {
						ad['can_refresh'] = true; // Set refresh true to load ad initially.

						/**
						 * Add slotRenderEnded eventlistener on gallery ads.
						 * Event listener will be called when ad slot render ended while we display slot or refresh slot.
						 * Adding Timeout to let ad slot to be on screen for around 1 seconds to get impression.
						 */
						googletag.pubads( slot ).addEventListener( 'slotRenderEnded', function( event ) {
							if ( false === event.isEmpty ) {
								setTimeout( function() { // Wait for atlease 1 second to count impression.

									/**
									 * This is to reset can_refresh for gallery ads because when gallery ads in refresh slot list the value of can_refresh is false.
									 * because of it we don't end up continuously refreshing ads without getting response of previous slot.
									 * This will reset the ability to refresh the gallery ad slots after they gets rendered correctly.
									 * Above can_refresh is to set refresh true initially as we first load ads by refresing slots on page.
									 * Above value won't be inherited after first request because this part of code is added to initially setup slot before rendering it on page.
									 */
									if (
										'object' === typeof event.slot &&
										'function' === typeof event.slot.getSlotElementId
									) {
										for ( var ad_index in pmc_adm_gpt.settings.ad_list.default ) {
											if ( event.slot.getSlotElementId() === pmc_adm_gpt.settings.ad_list.default[ ad_index ].id ) {
												pmc_adm_gpt.settings.ad_list.default[ ad_index ]['can_refresh'] = true;
											}
										}
									}
								}, 1250 );
							}
						});
					}

					if ( slot ) {
						slot.addService( googletag.pubads() );
						for ( var j in ad['targeting'] ) {
							slot.setTargeting( j, ad['targeting'][j] );
						}
						self.ad_slots[ ad['id'] ] = slot;
					}
				} // for
			} //for


			self.set_targeting_keywords( self.settings['ad_targetings'] );
			self.set_skin_targetting_kw();

			/*
			 * Need single request for performance and DFP ads sync
			 * Important: Single request must be called before enableServices()
			 *
			 * Call single request only if it hasn't been disabled explicitly
			 */
			googletag.pubads().enableSingleRequest();

			googletag.pubads().collapseEmptyDivs();
			googletag.pubads().disableInitialLoad();	// we do not want to render the ads yet

            googletag.enableServices();

			// need to loop through all valid ads to trigger ad display
			for ( var t in self.settings.ad_list ) {
				var ads = self.settings.ad_list[t];
				for ( i in ads ) {
					if ( typeof self.ad_slots[ads[i]['id']] == 'undefined' ) {
						continue;
					}
					googletag.display( ads[i]['id'] );
				}
			}

			//Binding to gpt event `slotRequested`
			googletag.pubads().addEventListener( 'slotRequested', function( event ) {
				pmc.hooks.do_action( 'pmc_adm_gpt_slot_requested_event', pmc_adm_gpt.reset_auto_refresh_timer );
			});

		}); // googletag.cmd.push ..

 		if ( 'object' === typeof pmc_header_bidder && '1' === pmc_header_bidder.active ) {
 		    this.should_refresh_ads = false;
 		}
 		//check for Amazon Apstag
		if ( 'object' === typeof apstag || 'undefined' !== typeof pmc_adm_ias) {
			this.should_refresh_ads = false;
		}
		//check for ias on page
		if ( 'undefined' !== typeof pmc_adm_ias ) {
			this.should_refresh_ads = false;
		}

 		if ( this.render_ad_type && this.should_refresh_ads ) {
 		    pmc_adm_gpt.render_ads();
 		}

		if (
			'object' === typeof pmc_adm_config &&
			'string' === typeof pmc_adm_config.auto_refresh &&
			'enable' === pmc_adm_config.auto_refresh
		) {
			this.init_auto_refreshing_ads();
		}

		self.bind_events();

	},

	render_ad: function( ad, targeting ) {
		var self = this;

		if ( typeof ad === 'undefined' || ! ad ) {
			return;
		}

		// is ad restricted by devices?
		if ( typeof ad['devices'] != 'undefined' && typeof ad['devices'].length == 'number' ) {
			if ( !this.is_device( ad['devices'] ) ) {
				return;
			}
		}

		if ( typeof self.ad_slots[ ad['id'] ] !== 'undefined' ) {
			googletag.cmd.push( function() {
				googletag.pubads().refresh( [ self.ad_slots[ ad['id'] ] ] );
			} );
		}

	},

	// return true if there is ads for ad type
	has_ads: function( ad_type ) {

		if ( typeof ad_type == 'undefined' || '' == ad_type ) {
			ad_type = this.render_ad_type;
		}

		if ( ! ad_type || typeof this.settings.ad_list == 'undefined' ) {
			return false;
		}

		if ( typeof this.settings.ad_list[ad_type] == 'undefined' ) {
			return false;
		}

		var list = this.settings.ad_list[ad_type];

		for ( var i in list ) {
			var ad = list[i];
			// is ad restricted by devices?
			if ( typeof ad['devices'] != 'undefined' && typeof ad['devices'].length == 'number' ) {
				if ( !this.is_device( ad['devices'] ) ) {
					continue;
				}
			}
			return true;
		}

		return false;
	},

	// render ads by ad type: default, interrupt-ads, interrupt-ads-gallery
	render_ads: function( ad_type ) {
		if ( typeof ad_type == 'undefined' || '' == ad_type ) {
			ad_type = this.render_ad_type;
		}

		if ( ! ad_type || typeof this.settings.ad_list == 'undefined' ) {
			return;
		}

		if ( typeof this.settings.ad_list[ad_type] == 'undefined' ) {
			return;
		}

		var self = this;

		googletag.cmd.push( function() {
			var ads = self.settings.ad_list[ad_type];
			var slots = [];

			for ( var i in ads ) {
				var ad = ads[i];
				if ( 'undefined' !== typeof self.ad_slots[ ad.id ] ) {
					if (
						'undefined' !== typeof ad.is_lazy_load &&
						'enable' === pmc_adm_config.lazy_load_override &&
						'yes' === ad.is_lazy_load
					) {
						var $id = jQuery( '#' + ad.id );

						// Add lazy load class.
						$id.addClass( 'pmc-lazyload-ad' );

					} else if ( 'undefined' !== typeof ad.id && -1 < ad.id.indexOf( 'gallery' ) ) {
						if ( 'undefined' !== typeof ad.can_refresh && true === ad.can_refresh ) { // Don't push gallery ad slot to refresh if 'can_refresh' is set to false.
							slots.push( self.ad_slots[ad.id] ); // eslint-disable-line space-in-parens
							ad.can_refresh = false; // Gallery Ad is in refresh list so set 'can_refresh' to false.
						}
					} else {
						slots.push( self.ad_slots[ad['id']] );
					}

					if (
						'string' === typeof ad.is_ad_rotatable &&
						'YES' === ad.is_ad_rotatable &&
						'object' === typeof pmc_adm_config &&
						'string' === typeof pmc_adm_config.auto_refresh &&
						'enable' === pmc_adm_config.auto_refresh
					) {
						pmc_adm_gpt.auto_ad_refresh_timers[ad.id] = {
							timer: null,
							ad_inview: false,
							last_view_started: null,
							total_time_inview: null,
							refresh_count: 0,
							refresh_time_limit: ad.ad_refresh_time
						};
					}
				}
			}

			if ( slots.length > 0 ) {
				googletag.pubads().refresh( slots );
			}

			if ( jQuery( '.pmc-lazyload-ad' ).length ) {
				self.load_lazy_ads( true );
			}

		} );
		self.rendered[ad_type] = true;

	},

	render_lazy_ads: function ( entries ) {
		entries.forEach( function ( entry ) {
			if ( entry.isIntersecting && 'function' === typeof googletag.pubads ) {
				googletag.pubads().refresh( [ pmc_adm_gpt.ad_slots[ entry.target.getAttribute('id') ] ] );
				pmc_adm_gpt.pmc_lazy_ads_observer.unobserve( entry.target );
			}
		} );
	},

	// Loading lazy ads
	load_lazy_ads: function( lazy_load ) {
		var self = this,
			$lazy_ads = jQuery( '.pmc-lazyload-ad' );

		if ( 0 === $lazy_ads.length &&
			'settings' in self &&
			'ad_list' in self.settings &&
			'default' in self.settings.ad_list &&
			'object' === typeof self.settings.ad_list.default
		) {

			self.settings.ad_list.default.forEach( function(ad) {
				if ( 'undefined' !== typeof ad.is_lazy_load && 'yes' === ad.is_lazy_load ) {
					if( 0 < jQuery( '#' + ad.id ).length ) {
						$lazy_ads.push( jQuery( '#' + ad.id )[0] );
					}
				}
			} );
		}

		$lazy_ads.each( function () {
			var $this = jQuery( this );
			if( true === lazy_load && 'IntersectionObserver' in window ) {
				pmc_adm_gpt.pmc_lazy_ads_observer.observe( this );
			} else {
				googletag.pubads().refresh( [ self.ad_slots[ $this.attr( 'id' ) ] ] );
			}
		} );
	},

	// render ads by ad type: default, interrupt-ads, interrupt-ads-gallery
	rotate_ads: function( ad_type ) {
		this.render_ads( ad_type );
	},

	remove_ads: function( ad_type ) {
		if ( typeof ad_type == 'undefined' || '' == ad_type ) {
			ad_type = this.render_ad_type;
		}

		if ( ! ad_type || typeof this.settings.ad_list == 'undefined' ) {
			return;
		}

		if ( typeof this.settings.ad_list[ad_type] == 'undefined' ) {
			return;
		}

		if ( typeof this.rendered[ad_type] == 'undefined' ) {
			return;
		}

		var ads = this.settings.ad_list[ad_type];
		var slots = [];

		for ( var i in ads ) {
			var ad = ads[i];
			if ( typeof this.ad_slots[ ad['id'] ] !== 'undefined' ) {
				if ( 'undefined' !== typeof ad.id && -1 < ad.id.indexOf( 'gallery' ) ) {
					if ( 'undefined' !== typeof ad.can_refresh && true === ad.can_refresh ) { // Remove Gallery Ad if Its about to Refresh.
						jQuery( '#' + ad.id ).empty();
					}
				} else {
					jQuery( '#' + ad.id ).empty();
				}
			}
		}
	},

	is_mobile: function() {
		if ( typeof this._is_mobile == 'undefined' ) {
			this._is_mobile = null != navigator.userAgent.match(/mobile|iPhone|Blackberry|Android|MIDP|AvantGo|BlackBerry|J2ME|Opera Mini|DoCoMo|NetFront|Nokia|PalmOS|PalmSource|portalmmm|Plucker|ReqwirelessWeb|SonyEricsson|Symbian|UP\.Browser|Windows CE|Xiino/i);
		}
		return this._is_mobile;
	},
	is_desktop: function() {
		return ! this.is_mobile();
	},
	is_device: function( device ) {
		if ( typeof this._device_string == 'undefined' ) {
			if ( this.is_mobile() ) {
				this._device_string = 'mobile';
			} else {
				this._device_string = 'desktop';
			}
		}
		if ( typeof device == 'string' ) {
			return device == this._device_string;
		}
		if ( typeof device.indexOf == 'function' ) {
			return device.indexOf( this._device_string ) > -1;
		}
		return false;
	},

	apply_filters: function ( filter, value, data1 ) {
		if ( typeof pmc == 'undefined' || typeof pmc.hooks == 'undefined' || typeof pmc.hooks.apply_filters == 'undefined') {
			return value;
		}
		return pmc.hooks.apply_filters( filter, value, data1 );
	},

	apply_slot_filter: function( slot ) {
		return this.apply_filters( 'pmc_adm_gpt_slot', slot );
	},

	apply_ad_width_filter: function( ad_width, slot  ) {
		return this.apply_filters( 'pmc_adm_gpt_ad_width', ad_width, slot );
	},

	// refresh ads by div class name
	refresh_ads: function( cls ) {
		if ( typeof cls == 'undefined' ) {
			cls = 'ad-rotatable';
		}

		var self = this;
		var slots_to_refresh = [];

		jQuery( '.pmc-adm-goog-pub-div .' + cls ).each( function() {
			var div_id = jQuery( this ).attr( 'id' );

			if ( typeof self.ad_slots[ div_id ] !== 'undefined' ) {
				slots_to_refresh.push( self.ad_slots[ div_id ] );
			}
		} ).promise().done( function() {
			if ( slots_to_refresh && slots_to_refresh.length > 0 ) {
				//Clearing previous skin ads just before refreshing ads
				jQuery('#skin-ad-left-rail-container').removeAttr('style');
				jQuery('#skin-ad-right-rail-container').removeAttr('style');
				if ( typeof pmc_header_bidder != 'undefined' && typeof pbjs != 'undefined' && pmc_header_bidder.active == 1 ) {
					//This is needed to request new bids when ads are refreshed with out page loading
					//check if it is gallery page and HB(prebidjs) is active
					if( typeof pmc_meta != 'undefined' && pmc_meta['page-type'] != 'undefined' && pmc_meta['page-type'] == 'gallery' ) {

						//if Apstag is enabled then we need to re fetch bids
						if( 'undefined' !== typeof pmc_apstag && 'is_gallery' in pmc_apstag && 'enabled' === pmc_apstag.is_gallery ) {
							apstag_refresh_bids( slots_to_refresh );
						}
							//now check if HB is allowed on gallery pages

						if( typeof pmc_header_bidder_script_object == 'undefined'
							|| typeof pmc_header_bidder_script_object.pmc_header_bidding_on_gallery == 'undefined'
							|| pmc_header_bidder_script_object.pmc_header_bidding_on_gallery != 'yes' ){
							pbjs = null;
							googletag.pubads().refresh( slots_to_refresh );
							return;
						}
					}
					//check and call apstag
					pbjs.adserverRequestSent = false;
					pbjs.requestBids({
						bidsBackHandler: function () {
							pbjs.sendAdserverRequest( slots_to_refresh );
						}
					});
				} else if ( 'undefined' !== typeof apstag
					&& 'undefined' !== typeof pmc_apstag
					&& 'is_gallery' in pmc_apstag
					&& 'enabled' === pmc_apstag.is_gallery ) {

					apstag_refresh_bids( slots_to_refresh );
				} else {
					googletag.pubads().refresh( slots_to_refresh );
				}
			}
		} );
	},
	display_ad: function (div_id) {
		googletag.display(div_id);
	},

	/**
	 * Set ad targeting key 'skin'.
	 * Updating random number generation from [1-10] to [1-12].
	 * @since 04-17-2018 Vinod Tella - READS-1136
	 */
	set_skin_targetting_kw: function () {
		var self = this;

		if ( 0 === self.skin_targeting_kw  ) {
			self.skin_targeting_kw = Math.floor( ( Math.random() * 12 ) + 1 );
		}

		googletag.pubads().setTargeting( 'skin', self.skin_targeting_kw.toString() );
	},

	/**
	 * Set the page-level 'referer' targeting.
	 */
	set_referrer_targeting_keyword: function() {

		try {

			// Inform Sourcebuster that we're ready to capture the
			// user's referrer information.
			// @see https://github.com/alexfedoseev/sourcebuster-js
			sbjs.init({

				// Kill Sourcebuster's cookies when the session stops
				lifetime: 0,

				// Update the direct traffic referrer value, from
				// `(direct)` to `direct` just so it's easier to read.
				typein_attributes: {
					source: 'direct',
					medium: 'none',
				},

				// Define referral alias'
				// There are a number of these baked into SB
				// @see https://github.com/alexfedoseev/sourcebuster-js#organics
				referrals: [
					{
						host: 'l.facebook.com',
						medium: 'social',
						display: 'facebook'
					},
					{
						host: 'www.facebook.com',
						medium: 'social',
						display: 'facebook'
					}
				],

				/**
				 * Tell ADM to use the referrer when sending targeting keywords.
				 *
				 * @param object sb The Sourcebuster object
				 */
				callback: function (sb) {

					/**
					 * Filter the page-level targeting keywords.
					 *
					 * @param array $args An array of the existing key/value targeting items.
					 *
					 * @return array An array of key/value targeting including a referrer key/value.
					 */
					pmc.hooks.add_filter('pmc-adm-set-targeting-keywords', function (args) {
						try {
							if (sbjs.get.current.src) {
								args['referrer'] = sbjs.get.current.src;
							}
							if (sbjs.get.session.pgs) {
								args['pageview'] = sbjs.get.session.pgs;
							}
						} catch (e) {
							// do nothing
						}
						return args;
					});
				}
			});
		} catch ( e ) {
			// do nothing
		}
	},

	set_targeting_keywords: function( args ) {
		if ( typeof googletag.pubads == 'undefined' ) {
			return;
		}

		args = this.apply_filters( 'pmc-adm-set-targeting-keywords', args );
		for (key in args) {
			var value = args[key];
			value = this.apply_filters( 'pmc-adm-set-targeting-keywords-' + key , value );
			if ( typeof value != 'undefined' && '' != value ) {
				googletag.pubads().setTargeting( key, value );
			}
		}
	},

	/**
	 * Removes the uid added to ad ID when rendering DIV element and returns the actual ad ID
	 *
	 * @param {String} ad_id
	 * @returns {String}
	 */
	get_generic_id: function( ad_id ) {

		if ( typeof ad_id === 'undefined' || ! ad_id ) {
			return '';
		}

		var parts = ad_id.split( '-' );

		return parts.slice( 0, parts.length - 1 ).join( '-' );

	},

	/**
	 * Tracking ad units that are in view and making then ready to refresh.
	 *
	 */
	handle_ad_refresh_interval: function () {
		var total_time_in_view = 0,
			current_time,
			last_view_started,
			current_view_state,
			ad_unit_timer_obj,
			inactive_time = 0,
			temp_auto_refresh_time_limit = pmc_adm_gpt.auto_refresh_time_limit,
			ads_to_refresh = [];

		if ( 'undefined' !== typeof pmc_adm_gpt.is_direct_sold && true === pmc_adm_gpt.is_direct_sold ) {
			return;
		}

		current_time = performance.now();

		if ( 'undefined' !== typeof pmc_adm_gpt.auto_ad_refresh_inactive_time &&
			0 !== pmc_adm_gpt.auto_ad_refresh_inactive_time
			) {

			inactive_time = current_time - pmc_adm_gpt.auto_ad_refresh_inactive_time;
		}

		for ( var ad in pmc_adm_gpt.auto_ad_refresh_timers ) {

			ad_unit_timer_obj            = pmc_adm_gpt.auto_ad_refresh_timers[ ad ];
			temp_auto_refresh_time_limit = ad_unit_timer_obj.refresh_time_limit;

			if ( true === pmc_adm_gpt.is_ad_inview( ad, 50 ) ) {
				ad_unit_timer_obj.ad_inview = true;
			}else{
				ad_unit_timer_obj.ad_inview = false;
			}

			// Check if this ad unit was inview previously for some time then accumulate that time as well.
			current_view_state = ad_unit_timer_obj.ad_inview;

			if( true === current_view_state && null !== ad_unit_timer_obj.last_view_started ) {
				last_view_started = ad_unit_timer_obj.last_view_started;

				total_time_in_view = current_time - last_view_started;

				//If adunit has inview time recorded before
				if ( ad_unit_timer_obj.total_time_inview ) {
					total_time_in_view = total_time_in_view + ad_unit_timer_obj.total_time_inview;
				}

				ad_unit_timer_obj.total_time_inview = total_time_in_view - inactive_time;
			}

			if ( ad_unit_timer_obj.total_time_inview ) {
				total_time_in_view = ad_unit_timer_obj.total_time_inview;
				total_time_in_view = total_time_in_view - inactive_time;
				temp_auto_refresh_time_limit = ( ad_unit_timer_obj.refresh_time_limit - total_time_in_view );
			}

			ad_unit_timer_obj.last_view_started = current_time;

			//If total time to refresh is less then or equal to 0 then refresh.
			if ( temp_auto_refresh_time_limit <= 0 &&
				true === ad_unit_timer_obj.ad_inview &&
				null !== ad_unit_timer_obj.total_time_inview ) {

				// Checking if the ad is inview one last time before refresh
				// Ex: if the user switches tab just before refresh;
				if ( false === pmc_adm_gpt.is_ad_inview( ad, 50 ) ) {
					ad_unit_timer_obj.ad_inview = false;
					continue;
				}
				ad_unit_timer_obj.last_view_started = current_time; //Store current time
				ad_unit_timer_obj.total_time_inview = null; //Reset total inview time
				pmc_adm_gpt.auto_ad_refresh_timers[ ad ] = ad_unit_timer_obj;
				ads_to_refresh.push( ad );

			}
		}

		if( ads_to_refresh.length > 0 ) {
			pmc_adm_gpt.refresh_inview_ad( ads_to_refresh );
		}

		//recursive timer
		pmc_adm_gpt.auto_ad_refresh_inactive_time = 0; //reset inactive time;
		if ( true === pmc_adm_gpt.auto_ad_refresh_status ) {
			pmc_adm_gpt.auto_ad_refresh_interval_id = window.setTimeout( pmc_adm_gpt.handle_ad_refresh_interval, 500 );
		}
	},

	/**
	 * Check if ad is in view or not.
	 *
	 * @param {String} id  Ad div id.
	 * @param {int} percent  Percentage of viewable pixels od adunit.
	 * @returns {boolean}
	 */
	is_ad_inview: function ( id, percent ) {

		var ad, bounding, inview_pxl, total_pxl;
		ad = document.querySelector( '#' + id );

		if ( null === ad ) {
			return false;
		}

		bounding = ad.getBoundingClientRect();

		if (
			bounding.height > 0 &&
			bounding.width > 0 &&
			bounding.top >= 0 &&
			bounding.left >= 0 &&
			bounding.right <= ( window.innerWidth || document.documentElement.clientWidth ) &&
			bounding.bottom <= ( window.innerHeight || document.documentElement.clientHeight )
		) {
			return true;
		} else {
			var oovw = pmc_adm_gpt.get_out_of_view_pixels( bounding.left, bounding.right, window.innerWidth ),
				oovh = pmc_adm_gpt.get_out_of_view_pixels( bounding.top, bounding.bottom, window.innerHeight );

			inview_pxl = ( bounding.height - oovh ) * ( bounding.width - oovw );
			total_pxl = bounding.width * bounding.height;
			return ( ( inview_pxl / total_pxl ) >= ( percent / 100 ) );

		}
	},

	/**
	 * Find the portion of pixels that are out of view on a single axis.
	 *
	 * @param  {int} min            The minimum boundary of the element (ie left/top).
	 * @param  {int} max            The max boundary of the element (ie right/bottom).
	 * @param  {int} view_port_size The viewport size on the same axis.
	 * @return {int}                The number of out-of-view pixels.
	 */
	get_out_of_view_pixels: function( min, max, view_port_size ) {
		var oov = 0;

		if ( min < 0 ) {
			oov += ( 0 - min );
		}

		if ( max > view_port_size ) {
			oov += ( max - view_port_size );
		}

		return oov;
	},

	/**
	 * Refresh  Ad unit when it is in view.
	 *
	 * @param {Array} ad_ids  Ad div ids.
	 */
	refresh_inview_ad: function ( ad_ids ) {

		var slot_refresh_count = 1,
			self = this,
			ad_unit_timer_obj = {},
			ad_slots = [];

		//Make sure existing ads are not direct sold

		if ( 'undefined' === typeof pmc_adm_gpt.is_direct_sold || false === pmc_adm_gpt.is_direct_sold ) {

			ad_ids.forEach( function ( ad_id ) {

				ad_unit_timer_obj = pmc_adm_gpt.auto_ad_refresh_timers[ ad_id ];

				//set refresh key value in slot targeting
				if (ad_unit_timer_obj.refresh_count) {

					slot_refresh_count = ( 1 + ad_unit_timer_obj.refresh_count );
				}

				ad_unit_timer_obj.refresh_count = slot_refresh_count;

				self.ad_slots[ad_id].setTargeting( 'mivr', ad_unit_timer_obj.refresh_count );
				self.ad_slots[ad_id].setTargeting( 'refresh', 1 );
				ad_slots.push( pmc_adm_gpt.ad_slots[ ad_id ] );
			} );

			if( ad_slots.length > 0 ) {
				self.prepare_ad_refresh(ad_slots);
			}

		}

	},

	/**
	 * Initialize auto refreshing ads.
	 *
	 */
	init_auto_refreshing_ads: function () {
		document.addEventListener( 'visibilitychange', this.handle_visibility_change, false);
		this.handle_visibility_change(); //this handle should start auto refreshing ads.
		pmc.hooks.add_action( 'pmc_adm_gpt_slot_requested_event', this.reset_auto_refresh_timer );
	},

	/**
	 * Start auto refreshing ads.
	 *
	 */
	start_auto_refreshing_ads: function () {
		if ( 'undefined' === typeof this.is_direct_sold || false === this.is_direct_sold ) {
			this.auto_ad_refresh_status = true;
			this.auto_ad_refresh_interval_id = window.setTimeout( this.handle_ad_refresh_interval, 500 );
		}
	},

	/**
	 * Pause auto refreshing ads.
	 *
	 */
	pause_auto_refreshing_ads: function () {
		this.auto_ad_refresh_status = false;
		this.auto_ad_refresh_inactive_time = performance.now();
		clearInterval( this.auto_ad_refresh_interval_id );
	},

	/**
	 * Stop auto refreshing ads when user is not in active tab.
	 *
	 */
	handle_visibility_change: function () {
		var status = 'visible';

		if ( 'undefined' !== typeof document.hidden ) {
			status = document.hidden ? 'hidden' : 'visible';
		}

		if( 'visible' === status ) {
			pmc_adm_gpt.start_auto_refreshing_ads();
		} else {
			pmc_adm_gpt.pause_auto_refreshing_ads();
		}
	},

	/**
	 * Reset refresh timer per ad slot on ad request.
	 *
	 */
	reset_auto_refresh_timer: function ( event ) {

		if ( 'object' === typeof event &&
			'object' === typeof event.slot &&
			'function' === typeof event.slot.getSlotElementId &&
			'object' === typeof pmc_adm_gpt.auto_ad_refresh_timers
		) {

			var ad_div_id = event.slot.getSlotElementId();

			if ( ad_div_id in pmc_adm_gpt.auto_ad_refresh_timers ) {
				pmc_adm_gpt.auto_ad_refresh_timers[ ad_div_id ].total_time_inview = null;
			}
		}
	},

	/**
	 * Prepare adsolts for refresh.
	 *
	 */
	prepare_ad_refresh: function( slots ) {

		//if Apstag is enabled then we need to re fetch bids
		if ('object' === typeof apstag || 'undefined' !== typeof pmc_adm_ias) {

			if ('function' === typeof apstag_refresh_bids) {
				apstag_refresh_bids(slots);
			}

		} else {
			googletag.pubads().refresh(slots); //Refresh Ad now
		}
	},

	/**
	 * Bind Events
	 *
	 */
	bind_events: function() {

		this.direct_sold_ad_event();

	},

	/**
	 * Bind on window messge event to determine direct sold ad flag
	 *
	 */
	direct_sold_ad_event: function () {

		var self = this;

		if ( 'undefined' !== typeof pmc && 'undefined' !== typeof pmc.hooks ) {

			jQuery( window ).on( 'message', function ( wrappedEvent ) {
				var event = wrappedEvent.originalEvent,
					message_pattern = 'pmcadm:dfp:isdirect=true';

				if ( 'string' === typeof event.data ) {

					/*
					 Only process the message event, if it is pmcadm:dfp:isdirect=true
					 */
					if ( event.data.substring( 0, message_pattern.length ) === message_pattern ) {

						//Direct sold ads are running and run hooks that are needed.
						pmc.hooks.do_action( 'pmc_adm_dfp_direct_sold', event );
						self.is_direct_sold = true;

					}
				}
			});
		}
	},

	/**
	 * Set gpt targeting key ooos=y if CCPA signal is 1YYY
	 *
	 */
	process_ccpa_check: function () {

		if ( 'function' === typeof __uspapi ) {
			__uspapi('getUSPData', 1, function( data, success ) {
				if (
					'undefined' !== typeof success &&
					'object' === typeof data &&
					'string' === typeof data.uspString &&
					'1YYY' === data.uspString &&
					'undefined' !== typeof window.googletag
				) {
					googletag.cmd.push( function() {
						googletag.pubads().setTargeting('ooos', 'y');
					});
				}
			});
		}
	}

};

var pmc_dfp_skin = {

    properties: {
        dfpCreativeMarkup: undefined,
        dfpCreativeParameters: undefined,
        viewUrlTracked: false
    },

    init_skin: function(params) {
        var self = this;

        jQuery.extend(true, self, params);

        self.init_DOM();

        self.bind_GUI_events();
    },

    init_DOM: function() {
        var self = this;

        self.dom = {
            adSection: jQuery("#skin-ad-section"),
            leftRailContainer: jQuery("#skin-ad-left-rail-container"),
            rightRailContainer: jQuery("#skin-ad-right-rail-container")
        };
    },

    bind_GUI_events: function() {
        var self = this;

        jQuery(window).on("message", function(wrappedEvent) {
            var event = wrappedEvent.originalEvent;

            if ('string' === typeof event.data) {

                var markupMessagePattern = "pmcadm:dfp:skinad:markup";
                var parametersMessagePattern = "pmcadm:dfp:skinad:parameters";

                /*
                 Only process the message event, if it is for dfp > prestital ad > markup
                 */
                if (event.data.substring(0, markupMessagePattern.length)
                    === markupMessagePattern) {
                    self.properties.dfpCreativeMarkup =
                        event.data.substring(markupMessagePattern.length) || "<!-- NOOP -->";

                    self.run();
                } else if (event.data.substring(0, parametersMessagePattern.length)
                    === parametersMessagePattern) {
                    /*
                     Only process the message event, if it is for dfp > prestital ad > parameters
                     */
                    var serializedParameters = event.data.substring(parametersMessagePattern.length);

                    self.properties.dfpCreativeParameters = jQuery.parseJSON(serializedParameters);

                    self.run();
                }
            }
        });

        jQuery(window).on("resize", function() {
            self.refresh_skin_rails();
        });

        jQuery(document).ready(function() {
            self.refresh_skin_rails();
        });

        /*
         * This is needed because prestitials cause there to be no-scrollbars.
         * This throws off the skin rails calculation. Hence we need to refresh skin rails.
         * */
        jQuery("body").on("prestitial-ad:stopped", function() {
            self.refresh_skin_rails();
        });

        //Add events for when user clicks on the skins
        self.dom.leftRailContainer.on("click", function() {
            self.skin_clicked_EventHandler();
        });

        self.dom.rightRailContainer.on("click", function() {
            self.skin_clicked_EventHandler();
        });
    },

    skin_clicked_EventHandler: function() {
        var self = this;
        window.open(self.properties.dfpCreativeParameters.clickThroughURL, "_blank");
    },

    refresh_skin_rails: function() {
        var self = this;

        if (!self.properties.dfpCreativeParameters) {
            return;
        }

        var mainContentReferenceDOM = self.get_content_DOM();

        var availableRailWidth = self.get_available_rail_width(mainContentReferenceDOM);

        var browserWidth = jQuery(window).width();

        var RAIL_MAX_WIDTH_TO_SUPPLIED_RAIL_WIDTH = {
            LARGE: 1900,
            MEDIUM: 1350,
            SMALL: 1260
        };

        var suppliedRailWidthToBeUsed = 0;


        if (browserWidth < RAIL_MAX_WIDTH_TO_SUPPLIED_RAIL_WIDTH["MEDIUM"]) {
            suppliedRailWidthToBeUsed = RAIL_MAX_WIDTH_TO_SUPPLIED_RAIL_WIDTH["SMALL"];
        } else if (browserWidth < RAIL_MAX_WIDTH_TO_SUPPLIED_RAIL_WIDTH["LARGE"] && browserWidth >= RAIL_MAX_WIDTH_TO_SUPPLIED_RAIL_WIDTH["MEDIUM"]) {
            suppliedRailWidthToBeUsed = RAIL_MAX_WIDTH_TO_SUPPLIED_RAIL_WIDTH["MEDIUM"];
        } else {
            suppliedRailWidthToBeUsed = RAIL_MAX_WIDTH_TO_SUPPLIED_RAIL_WIDTH["LARGE"];
        }

        var imageToBeUsed = self.properties.dfpCreativeParameters.creative.image[suppliedRailWidthToBeUsed];

        var leftRailImageLink = self.properties.viewUrlTracked
            ? imageToBeUsed.left
            : self.properties.dfpCreativeParameters.viewURLPrefix + imageToBeUsed.left;

        var bodyBackgroundColor = self.properties.dfpCreativeParameters.bodyBackgroundColor
            ? (jQuery.trim(self.properties.dfpCreativeParameters.bodyBackgroundColor)
        || null)
            : null;

        jQuery("body").css("background-color", bodyBackgroundColor);

        self.properties.viewUrlTracked = true;

        self.dom.leftRailContainer.css("background-image", 'url("' + leftRailImageLink + '")')

        self.dom.rightRailContainer.css("background-image", 'url("' + imageToBeUsed.right + '")')

        var imgURL = imageToBeUsed.right;
        var img = jQuery('<img src="'+imgURL+'"/>').on('load', function(){
            var leftImageWidthToBeUsed = this.width;
            var mainContentReferenceDOM = pmc_dfp_skin.get_content_DOM();
            pmc_dfp_skin.dom.leftRailContainer
                .width(leftImageWidthToBeUsed)
                .offset({left: (mainContentReferenceDOM.offset().left - leftImageWidthToBeUsed)});

            pmc_dfp_skin.dom.rightRailContainer
                .width(leftImageWidthToBeUsed)
                .offset({left: (mainContentReferenceDOM.offset().left + mainContentReferenceDOM.outerWidth())});

        });
    },

    get_content_DOM: function() {
        var self = this;
        var orderedDomCandidates = ['main-wrapper'];
        if ( 'undefined' !== typeof pmc_adm_config && 'undefined' !== typeof pmc_adm_config.dfp_skin_main_content ) {
           orderedDomCandidates = pmc_adm_config.dfp_skin_main_content;
        }
        orderedDomCandidates = self.apply_filters( 'pmc-adm-dfp-skin-main-content', orderedDomCandidates );

        for (var ii=0; ii<orderedDomCandidates.length; ii++) {

            var element = jQuery("#" + orderedDomCandidates[ii]);
            if (element && element.width()) {
                return element;
            }
        }
        return jQuery('body');
    },

    get_available_rail_width: function(contentDOM) {
        if ( ! contentDOM ) {
           contentDOM = jQuery('body');
        }

        var documentWidth = 0;

        //Compute document width
        //IE
        if (!window.innerWidth) {
            if (!(document.documentElement.clientWidth === 0)) {
                documentWidth = document.documentElement.clientWidth; //strict mode
            } else {
                documentWidth = document.body.clientWidth; //quirks mode
            }

        } else {
            documentWidth = window.innerWidth; //w3c
        }

        return (documentWidth - contentDOM.outerWidth()) / 2;
    },

    run: function() {
        var self = this;

        /*
         Don't do anything if one of/both the markup & the parametes are absent.
         */
        if (!self.properties.dfpCreativeMarkup
            || !self.properties.dfpCreativeParameters) {
            return;
        }

        self.dom.adSection.removeClass("hide");
        self.dom.adSection.append(self.properties.dfpCreativeMarkup);

        self.refresh_skin_rails();
    },

    apply_filters: function ( filter, value, data1 ) {
        if ( typeof pmc == 'undefined' || typeof pmc.hooks == 'undefined' || typeof pmc.hooks.apply_filters == 'undefined') {
            return value;
        }
        return pmc.hooks.apply_filters( filter, value, data1 );
    }

}

// Common ad functions
var pmc_admanager = {
	settings: {
		interrupts_hide_container: '#container',
		redirect_interval: 0,
		interrupt_counter: 0
	},
	load_ad: function() {
		pmc_adm_doubleclick.load_ad();
	},
	rotateAd: function( cls ) {
		pmc_adm_doubleclick.rotateAd( cls );
	},
	show_interrupt_ads: function( force ) {
		/**
		 * User has an ad blocker, so let's not bother with any of this.
		 */
		if ( pmc_is_adblocked ) {
			return;
		}

		// If user agent is googlebot then don't display ad.
		if ( typeof navigator.userAgent !== 'undefined' )  {

			var bot_pattern = /googlebot|googlebot-news/i;

			if ( bot_pattern.test( navigator.userAgent ) )  {
				return;
			}

		}

		//Do not want to show interrupt ads for pages with referrer `flipboard.com`.
		var referrer_url = document.referrer;
		var referrer = '';
		if ( 'undefined' !== typeof referrer_url && '' !== referrer_url ) {
			referrer = referrer_url.match(/:\/\/(.[^/]+)/)[1];
			if( 'flipboard.com' === referrer ) {
				return;
			}
		}


		pmc_adm_gpt.render_ad_type = '';
		var self = this;
		if ( document.readyState === 'complete' || ( typeof force !== 'undefined' && force ) ) {
			if ( !pmc_adm_gpt.has_ads('interrupt-ads') ) {
				this.hide_interrupt_ads();
				return;
			}

			if ( typeof this.settings.interrupts_hide_container != 'undefined' && jQuery(this.settings.interrupts_hide_container).length ) {
				jQuery( this.settings.interrupts_hide_container ).hide();
			}
			if ( self.settings.redirect_interval ) {
				clearInterval( this.settings.redirect_interval );
			}
			self.settings.redirect_interval = setInterval( "pmc_admanager.interrupt_timer()", 1000 );
			jQuery('body').addClass('interrupt-ads');
			jQuery('#pmc-adm-interrupts-container').show();
			pmc_adm_gpt.rotate_ads('interrupt-ads');
		} else {
			jQuery(document).ready(function(){
				self.show_interrupt_ads( true );
			});
		}
	},
	hide_interrupt_ads: function() {
		if ( typeof this.settings.interrupts_hide_container != 'undefined' && jQuery(this.settings.interrupts_hide_container).length ) {
			jQuery( this.settings.interrupts_hide_container ).show();
		}
		clearInterval( this.settings.redirect_interval );
		this.settings.redirect_interval = 0;
		jQuery('body').removeClass('interrupt-ads');
		jQuery(window).trigger('resize'); // MA: fixes bug where videos do not show after interstitial runs.
		jQuery('#pmc-adm-interrupts-container').hide();
		pmc_adm_gpt.remove_ads('interrupt-ads');
		pmc_adm_gpt.render_ad_type = 'default';
		pmc_adm_gpt.rotate_ads();
		var event = new CustomEvent( "pmc-hide-interrupt-ads", { "detail": "Fires when the interrupt ads are done."});
		document.dispatchEvent( event );
	},
	hide_interrupt: function() {
		this.hide_interrupt_ads();
	},
	interrupt_timer : function(){
		if( this.settings.interrupt_counter == 0 ){
			this.hide_interrupt_ads();
		}else{
			if ( this.settings.redirect_interval ) {
				this.settings.interrupt_counter --;
			}
			if ( document.getElementById( "pmc_ads_interrupts_timer" ) ) {
				document.getElementById( "pmc_ads_interrupts_timer" ).innerHTML = this.settings.interrupt_counter;
			}
		}
	}

};

var pmc_dfp_prestitial = {
   properties: {
        isAdSkippedManually: false,
        dfpCreativeMarkup: undefined,
        dfpCreativeParameters: undefined
    },

    constants: {
        tabletMaxWidth: 768
    },

    init_prestitial: function(params) {
        var self = this;

        jQuery.extend(true, self, params);

        self.init_DOM();

        self.bind_GUI_Events();
    },

    init_DOM: function() {
        var self = this;

        self.dom = {

            adSection: jQuery("#prestitial-ad-section"),

            outerContainer: jQuery("#prestitial-ad-outer-container"),

            overlay: jQuery("#prestitial-ad-overlay"),
            container: jQuery("#prestitial-ad-container"),

            logo: jQuery("#prestitial-ad-logo"),

            thirdPartyContentContainer: jQuery("#prestitial-ad-inject-container"),
            thirdPartyContentViewTrackerContainer: jQuery("#prestitial-ad-third-party-content-view-tracker"),

            skipAd: {
                button: jQuery("#prestitial-ad-close"),
                durationCounter: jQuery("#prestitial-ad-duration-counter")
            }
        };

        self.dom.thirdPartyContentContainer.addClass("hide");
    },

    bind_GUI_Events: function() {
        var self = this;

        self.dom.skipAd.button.on("click", function() {
            self.properties.isAdSkippedManually = true;
            self.destroy();
        });

        jQuery(window).on("message", function(wrappedEvent) {
            var event = wrappedEvent.originalEvent;

            if ('string' === typeof event.data) {

                var markupMessagePattern = "pmcadm:dfp:prestitialad:markup";
                var parametersMessagePattern = "pmcadm:dfp:prestitialad:parameters";

                /*
                 Only process the message event, if it is for dfp > prestital ad > markup
                 */
                if (event.data.substring(0, markupMessagePattern.length)
                    === markupMessagePattern) {
                    self.properties.dfpCreativeMarkup =
                        event.data.substring(markupMessagePattern.length) || "<!-- NOOP -->";

                    self.run();
                } else if (event.data.substring(0, parametersMessagePattern.length)
                    === parametersMessagePattern) {
                    /*
                     Only process the message event, if it is for dfp > prestital ad > parameters
                     */
                    var serializedParameters = event.data.substring(parametersMessagePattern.length);

                    self.properties.dfpCreativeParameters = jQuery.parseJSON(serializedParameters);

                    self.run();
                }
            }
        });

        jQuery(window).on("resize", function() {
            self.refresh_container_position();
        });

    },

    destroy: function() {
        var self = this;
        jQuery("body").removeClass("no-scroll prestitial-ad-running");
        jQuery("body").trigger("prestitial-ad:stopped");
        self.dom.adSection.remove();
    },

    start_countdown: function() {
        var self = this;

        var timeLeft = self.properties.dfpCreativeParameters.duration;
        var countdownInterval = window.setInterval(function() {

            //If user has clicked on skip ad button, just clear interval & return
            if (self.properties.isAdSkippedManually) {
                window.clearInterval(countdownInterval);
                return;
            }

            //If the auto-close has time left then update counter text
            if (timeLeft) {
                self.dom.skipAd.durationCounter.text("Closing in " + timeLeft
                + " sec" + (timeLeft > 1 ? "s" : ""));
            } else {
                window.clearInterval(countdownInterval);
                self.destroy();
                return;
            }

            timeLeft--;
        }, 1000);
    },

    refresh_container_position: function() {
        var self = this;

        if (!self.properties.dfpCreativeParameters) {
            return;
        }

        var documentWidth = 0;
        var documentHeight = 0;

        //Compute document width
        //IE
        if (!window.innerWidth) {
            if (!(document.documentElement.clientWidth === 0)) {
                documentWidth = document.documentElement.clientWidth; //strict mode
            } else {
                documentWidth = document.body.clientWidth; //quirks mode
            }

        } else {
            documentWidth = window.innerWidth; //w3c
        }

        var adContainerLeftPosition = (documentWidth - self.properties.dfpCreativeParameters.width) / 2;

        //Compute document height
        //IE
        if (!window.innerHeight) {
            if (!(document.documentElement.clientHeight === 0)) {
                documentHeight = document.documentElement.clientHeight; //strict mode
            } else {
                documentHeight = document.body.clientHeight; //quirks mode
            }

        } else {
            documentHeight = window.innerHeight; //w3c
        }

        var adContainerTopPosition = (documentHeight - self.properties.dfpCreativeParameters.height) / 2;

        self.dom.overlay.css("background-color", self.properties.dfpCreativeParameters.overlayBackgroundColor);

        var containerToBePositioned = self.dom.container;

        if (self.properties.dfpCreativeParameters.isThirdPartyContent) {
            containerToBePositioned = self.dom.thirdPartyContentContainer;
        }

        if (documentWidth < self.constants.tabletMaxWidth) {

            self.dom.logo.offset({
                left: (documentWidth - self.dom.logo.innerWidth()) / 2
            });

            self.dom.skipAd.button.css({
                "right": adContainerLeftPosition + "px"
            });

            adContainerTopPosition = self.dom.logo.innerHeight() + self.dom.skipAd.button.innerHeight() + 7;
        }

        containerToBePositioned.width(self.properties.dfpCreativeParameters.width);
        containerToBePositioned.offset({
            top: adContainerTopPosition,
            left: adContainerLeftPosition
        });
    },

    run: function() {
        var self = this;

        /*
         Don't do anything if one of/both the markup & the parametes are absent.
         */
        if (!self.properties.dfpCreativeMarkup
            || !self.properties.dfpCreativeParameters) {
            return;
        }

        window.scrollTo(0, 0);
        jQuery("body").addClass("no-scroll prestitial-ad-running");
        self.dom.adSection.removeClass("hide");

        if (self.properties.dfpCreativeParameters.isThirdPartyContent) {

            var thirdPartyContainerIframe = self.dom.thirdPartyContentContainer
                .find("iframe");

            thirdPartyContainerIframe
                .width(self.properties.dfpCreativeParameters.width)
                .height(self.properties.dfpCreativeParameters.height);

            var img = jQuery('<img>');
            img.attr('src',self.properties.dfpCreativeParameters.viewURLPrefix + window.location.origin + "/sprites/transparent-1x1.png" );
            self.dom.thirdPartyContentViewTrackerContainer.append(img);

            self.dom.thirdPartyContentContainer.removeClass("hide");
        }

        self.dom.container.append(self.properties.dfpCreativeMarkup);

        self.refresh_container_position();
        self.start_countdown();

        window.setTimeout(function() {
            self.dom.adSection.addClass("shown");
        }, 400);
    }
}

/**
 * Boomerang ad functions.
 *
 * @type object
 */
pmc_adm_boomerang = {

	/**
	 * Init function.
	 */
	init: function() {
		this.bind_events();
	},

	/**
	 * To bind all events.
	 */
	bind_events: function() {

		if ( 'undefined' !== typeof pmc && 'undefined' !== typeof pmc.hooks && 'undefined' !== typeof pmc.hooks.add_action ) {
			pmc.hooks.add_action( 'pmc_gallery_rotate_ads', this.rotate_ads_for_gallery );
		}

	},

	/**
	 * Callback function when gallery slide change
	 * To refresh ads on page.
	 */
	rotate_ads_for_gallery: function() {

		var is_mobile = pmc_adm_gpt.is_mobile(),
			ad_dom_id = false,
			ad_slot = false,
			ads_need_to_reload = [];

		if ( 'undefined' === typeof blogherads || 'function' !== typeof blogherads.reloadAds ) {
			return;
		}

		if ( is_mobile ) {

			jQuery( '#adm-gallery-right-rail > .adma.boomerang > .pmc-adm-boomerang-pub-div div, #adm-header-leaderboard > .adma.boomerang > .pmc-adm-boomerang-pub-div div' ).each( function() {

				ad_dom_id = jQuery( this ).attr( 'id' );

				if ( ad_dom_id ) {
					ad_slot = blogherads.getSlotById( ad_dom_id );

					if ( 'object' === typeof ad_slot ) {
						ads_need_to_reload.push( ad_slot );
					}
				}

			});

			// Also rotate the adhesion slot.
			ad_slot = blogherads.getSlotsByType( 'bottom' )[0];

			if ( ad_slot ) {
				ads_need_to_reload.push( ad_slot );
			}


			if ( 0 < ads_need_to_reload.length ) {
				blogherads.reloadAds( ads_need_to_reload );
			}

		} else {

			blogherads.reloadAds();

		}

	}

};

/**
 * JWPlayer floating preroll ad functions.
 *
 * @type object
 */
var pmc_floating_preroll_ads = {

	// player elements and utility vars
	player_instance: {},
	pmc_floating_ad_main_div: '.floating-preroll-ad',
	is_floating_ad_showed: false,
	time_gap: 0,
	cookie: '',
	interval_id: '',

	/**
	 * Init function.
	 */
	init: function () {

		var _self = this;

		// First check for direct-sold ads flag is set or not
		if ( 'undefined' !== typeof pmc_adm_gpt && 'undefined' !== pmc_adm_gpt.is_direct_sold && true === pmc_adm_gpt.is_direct_sold ) {
			return false;
		}

		// Skip floating pre-roll ads if Localized data not available
		if ( 'undefined' === typeof pmcadm_floating_preroll_data || ! pmcadm_floating_preroll_data || 'undefined' === typeof pmcadm_floating_preroll_data.time_gap ) {
			return false;
		}

		// Fetch cookie name for floating pre-roll ad, Only proceed if name available
		_self.cookie = pmcadm_floating_preroll_data.cookie_name;

		if ( '' === _self.cookie || !_self.cookie ) {
			return false;
		}

		// Get the cookie value
		var floating_preroll_cookie = pmc.cookie.get( _self.cookie );

		// If cookie not set then init the floating player OR ELSE remove the container
		if ( floating_preroll_cookie == null || typeof floating_preroll_cookie === 'undefined' || '' === floating_preroll_cookie || 0 === parseInt( pmcadm_floating_preroll_data.time_gap ) ) {

			if ( ( 'undefined' !== typeof pmc_adm_has_interrupts && true === pmc_adm_has_interrupts )
				&& ( 'undefined' !== typeof pmc_admanager
					 && 'undefined' !== pmc_admanager.settings
					 && 'undefined' !== pmc_admanager.redirect_interval
					 && 0 !== pmc_admanager.settings.redirect_interval )
			) {

				_self.interval_id = setInterval( function () {

						if ( 0 === pmc_admanager.settings.redirect_interval ) {
							_self.show_floating_preroll_ad();
						}
					}, 1000
				);

			} else {

				_self.show_floating_preroll_ad();

			}

		} else {

			_self.remove_floating_player();
			return false;

		}
	},

	/**
	 * Loads preroll ad and bind it's events.
	 */
	show_floating_preroll_ad: function () {

		var jwplayers_divs = jQuery( '[id ^=jwplayer_][id $=_div]' ),
			is_desktop = pmc_adm_gpt.is_desktop(),
			_self = this,
			media_id = pmcadm_floating_preroll_data.media_id,
			time_gap = pmcadm_floating_preroll_data.time_gap,
			related_videos = jQuery('.l-pvm-video [id ^=jwplayer_][id $=_div]'),
			player_width = 400,
			player_height = 225;

		// Serve this ads only for desktop.
		// Proceed if Media ID is set.
		// bail out if direct-sold ads flag is set.
		if (
			! is_desktop
			|| ! media_id || '' === media_id || 'undefined' === typeof media_id
			||( 'undefined' !== typeof pmc_adm_gpt && 'undefined' !== pmc_adm_gpt.is_direct_sold && true === pmc_adm_gpt.is_direct_sold )
		) {
			return;
		}

		// Use default time (1 day) if no value passed for it.
		if ( '' !== time_gap ) {
			_self.timegap = time_gap;
		}

		clearInterval( _self.interval_id );

		if ( 1200 > jQuery( window ).width() ) {
			player_height = 190;
			player_width = 300;
			jQuery( '.floating-preroll-ad-container' ).css( 'width', '314px' );
			jQuery( '.floating-preroll-ad-container' ).css( 'height', '204px' );
		}

		if ( 0 === ( jwplayers_divs.length - related_videos.length ) && ( 'function' === typeof window.jwplayer || 'function' === typeof window.pmc_jwplayer ) ) {

			var jwConfig = {
				playlist: 'https://cdn.jwplayer.com/v2/media/' + media_id,
				autostart: true,
				mute: true,
				'height': player_height,
				'width': player_width,
				'pmc_position': 'floating'
			};

			// player setup

			if ( 'function' === typeof window.pmc_jwplayer ) {
				_self.player_instance = window.pmc_jwplayer( 'jwplayer_floating_preroll_ad' ).setup( jwConfig ).instance();
			} else {
				_self.player_instance = window.jwplayer( 'jwplayer_floating_preroll_ad' ).setup( jwConfig );
			}

			_self.player_instance.on( 'firstFrame', function () {
				_self.show_floating_player();
			} );

			_self.player_instance.on( 'adImpression', function () {

				pmc.cookie.set( _self.cookie, 1, _self.time_gap, '/' );
				_self.is_floating_ad_showed = true;
				_self.show_floating_player();

			} );

			_self.player_instance.on( 'adError', function () {

				if ( false === _self.is_floating_ad_showed ) {
					_self.remove_floating_player();
				}
			} );

			jQuery( document ).on( 'click', '.floating-preroll-ad-close', function() {

				video_name = _self.get_concatenated_label();
				_self.send_ga_event( 'click', video_name, false );
				_self.remove_floating_player();
			} );
		} else {
			jQuery( this.pmc_floating_ad_main_div ).remove();
		}

	},

	/**
	 * To show Floating player pre-roll ads DOM. (and shows close button after 5 second)
	 */
	show_floating_player: function () {
		setTimeout( function () {
			jQuery( '.floating-preroll-ad-close' ).show();
		}, 5000 );
		jQuery( this.pmc_floating_ad_main_div ).show();
	},

	/**
	 * To Remove the floating player DOM and jwplayer.
	 */
	remove_floating_player: function () {
		if ( 'function' === typeof this.player_instance.remove ) {
			this.player_instance.remove();
		}
		jQuery( this.pmc_floating_ad_main_div ).remove();
	},

	/**
	 * To get current Player Video Name.
	 *
	 * @returns {string/boolean}
	 */
	get_player_video_name: function() {

		var video_name = this.player_instance.getConfig().playlistItem.title;
		var video_id   = this.player_instance.getConfig().playlistItem.mediaid;

		video_name = video_name.replace( /[\W_]+/g, '-' );

		if ( video_name ) {
			video_name = video_name.toLowerCase();
		}

		if ( ! ( video_name && video_id ) ) {
			return false;

		}

		return video_name + '_' + video_id + '_';

	},

	/**
	 * To get concatenated label.
	 *
	 * @returns {string/boolean}
	 */
	get_concatenated_label: function() {

		var video_name = this.get_player_video_name();
		var seconds    = Math.floor( this.player_instance.getPosition() );

		video_name = 'floating-video-ad_' + video_name + seconds;

		return video_name;
	},

	/**
	 * To send GA events.
	 *
	 * @param {string}  event_action event action name.
	 * @param {string}  event_label event lable.
	 * @param {boolean} non_interaction is the event non interaction.
	 */
	send_ga_event: function( event_action, event_label, non_interaction ) {

		if ( window.pmc && window.pmc.event_tracking ) {
			window.pmc.event_tracking( '', event_action, 'video', event_label, false, false, non_interaction );
		}
	}

};

jQuery(document).ready(function () {
 	pmc_dfp_prestitial.init_prestitial();
	pmc_dfp_skin.init_skin();
	pmc_admanager.load_ad();
	pmc_adm_boomerang.init();
});

jQuery( window ).on( 'load', function() {
	setTimeout( function () {

		if (
			( 'object' === typeof pmc_meta && 'undefined' !== typeof pmc_meta.is_eu && true === pmc_meta.is_eu ) ||
			-1 !== window.location.search.indexOf( 'region=eu' )
		) {
			pmc.hooks.add_action( 'pmc_adm_consent_data_ready', function( consent_data ) {
				if ( 'object' === typeof consent_data && 'undefined' !== typeof consent_data.returnValue ) {
					pmc_floating_preroll_ads.init();
				}
			});
		} else {
			pmc_floating_preroll_ads.init();
		}
	}, 500);
});


if ( typeof rotateAd === 'function') {
	_pmc_other_rotateAd = rotateAd;
}
rotateAd = function ( ad_class ) {
	pmc_adm_gpt.refresh_ads( ad_class );
	if ( typeof _pmc_other_rotateAd === 'function' && _pmc_other_rotateAd !=  rotateAd ) {
		try {
			_pmc_other_rotateAd( ad_class );
		} catch(e){}
	}
};

if( 'undefined' !== typeof pmc && 'undefined' !== typeof pmc.hooks && 'undefined' !== typeof pmc.hooks.add_filter ) {

    pmc.hooks.add_filter( 'pmc-adm-set-targeting-keywords', function ( keywords ) {
      if ( typeof keywords['kw'] == 'undefined' ) {
        keywords[ 'kw' ] = [];
      }

      if ( 'undefined' === typeof keywords['pm'] ) {
        keywords['pm'] = [];
      }

      return keywords;
    } );

    pmc.hooks.add_filter( 'pmc-adm-set-targeting-keywords-pm', function ( keyword ) {
      var pmValue = null;

      if ( window.location.search.substring( 1 ) ) {
        var tokens = window.location.search.substring( 1 ).split( '&' );

        tokens.forEach( function( token ) {
          var pair = token.split( '=' );

          if ( 'pm' === pair[0] ) {
            pmValue = pair[1] || 0;
          }
        } );
      }

      if ( window.location.hash.substring( 1 ) ) {
        var hash = window.location.hash.substring( 1 );

        if ( '!' === hash.substring( 0, 1 ) ) {
          hash = hash.substring( 1 );
        }

        tokens = hash.split( '&' );

        tokens.forEach( function( token ) {
          var pair = token.split( '=' );

          if ( 'pm' === pair[0] ) {
            pmValue = pair[1] || 0;
          }
        } );
      }

      if ( null !== pmValue ) {
        if ( Array.isArray( keyword ) ) {
          keyword.push( 'y', pmValue );
        } else {
          keyword += ( keyword ? ',' : '' ) + [ 'y', pmValue ];
        }
      }

      return keyword;

    } );

    pmc.hooks.add_filter('pmc-adm-set-targeting-keywords-kw', function (keyword) {
        var kw = '', tokens, idx, pair;
        if (window.location.search.substring(1)) {
            tokens = window.location.search.substring(1).split('&');
            for (idx in tokens) {
                pair = tokens[idx].split('=', 2);
                if ('kw' == pair[0]) {
                    kw = pair[1];
                    break;
                }
            }
        }

        if (!kw && window.location.hash.substring(1)) {
            var hash = window.location.hash.substring(1);
            if ('!' == hash.substring(0, 1)) {
                hash = hash.substring(1);
            }
            tokens = hash.split('&');
            for (idx in tokens) {
                pair = tokens[idx].split('=', 2);
                if ('kw' == pair[0]) {
                    kw = pair[1];
                    break;
                }
            }
        }
		if( kw ){
			if (keyword instanceof Array) {
				keyword.push(kw);
			} else {
				keyword += ( keyword ? ',' : '' ) + kw;
			}
		}

        return keyword;
    });
}

//Delaying Ad calls until gdpr consent is made for eu users
function pmc_adm_check_cmp() {

	if ( 'function' === typeof window.__cmp ) {
		clearInterval( pmc_adm_cmp_interval );
		window.__cmp( 'getConsentData', null, function( result, success ) {
			var consent_data = {};

			// We got a consent response. either yes or no.
			// For US `result.gdprApplies` returns false.
			// Not worrying about consent data that is returned now.
			if ( 'object' === typeof result ) {
				consent_data = {
					'returnValue': result,
					'success': success
				};
				pmc.hooks.do_action( 'pmc_adm_consent_data_ready', consent_data );
			}
		});
	} else if ( 'function' === typeof window.__tcfapi ) {
		clearInterval( pmc_adm_cmp_interval );
	}

	// bail out after 5 seconds(50 tries).
	if( 50 < pmc_adm_cmp_interval_tries ) {
		clearInterval( pmc_adm_cmp_interval );
	}
	pmc_adm_cmp_interval_tries++;
}

// pmc_meta is on the top of the page before any other scripts.
if ( ('object' === typeof pmc_meta && 'undefined' !== typeof pmc_meta.is_eu && true === pmc_meta.is_eu ) || -1 !== window.location.search.indexOf( 'region=eu' ) ) {
	pmc_adm_cmp_interval = setInterval( pmc_adm_check_cmp, 100 );
}

//EOF
