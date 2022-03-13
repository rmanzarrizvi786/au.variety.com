/* global pmc, ga, googletag */

/*
	To Generate a Minified Version:
	sudo npm install uglifyjs -g
	cd .../themes/vip/pmc-plugins/pmc-google-universal-analytics/js/
	uglifyjs event-tracking.js -o event-tracking.min.js --compress unused=false
 */

(function($) {

	window.blogherads = window.blogherads || {};
	window.blogherads.adq = window.blogherads.adq || [];

	window.pmc = window.pmc || {};
	window.pmc.analytics = window.pmc.analytics || [];

	window.pmc_ga_event_tracking = ('object' === typeof window.pmc_ga_event_tracking) ? window.pmc_ga_event_tracking : {};

	var device = window.pmc_ga_event_tracking.device || '[D]',
		events = window.pmc_ga_event_tracking.events || [];

	/**
	 * GA event tracking helper function.
	 *
	 * @param object  $element       DOM object from selector
	 * @param string  action         event action, default is `click`
	 * @param string  category       event category
	 * @param string  label          event label
	 * @param boolean details        if true includes nested text within DOM object for context
	 * @param boolean url            if true include href of DOM object for context
	 * @param boolean nonInteraction tells GA event if nonInteraction or not default is `false`
	 * @param object  pre_events     A list of arbitrary events to send before the main ga() event.
	 *                               Helpful in scenarios like sending GA Ecommerce Data prior to a click event.
	 *                               See WWD/inc/class-wwd-event-tracking.php:subscription_events()
	 * @param integer event_value    event value Specifies the event value. Values must be non-negative.
	 *
	 * @returns {{hitType: string, eventCategory: *, eventAction: *, eventLabel: string, nonInteraction: boolean}}
	 */
	window.pmc.event_tracking = function( $element, action, category, label, details, url, nonInteraction, pre_events, event_value ) {
		var custom_ga_data = '',
			href = '',
			labelDetails = '';

		pre_events = pre_events || [];

		try {
			custom_ga_data = $element.attr('custom-ga-data');

			// If the the current node has custom-ga-data attribute, include them in the tracking event

			if (custom_ga_data) {
				custom_ga_data = JSON.parse(custom_ga_data);
				Object.entries(custom_ga_data).forEach(function (entry) {
					var key   = entry[0];
					var value = entry[1];

					if (!value) {
						return;
					}

					value = window.pmc.replace_wild_card( $element, value );

					switch (key) {
						case 'category':
							if ('undefined' === typeof category || !category) {
								category = value;
							}
							break;
						case 'details':
							if ('undefined' === typeof details || !details) {
								details = value;
							}
							break;
						case 'label':
							if ('undefined' === typeof label || !label) {
								label = value;
							}
							break;
						case 'product':
							window.pmc.maybe_load_ga_plugin('ec');
							// IMPORTANT use indexed 1 to replace the product, if not, it will add to the list
							// and multiple product will be records from previous clicked event if page not refresh
							window.pmc.maybe_set_ga_pre_events( pre_events, [ 'ec:addProduct', value, 1 ] );
							window.pmc.maybe_set_ga_pre_events( pre_events, [ 'ec:setAction', 'click', {'list': 'article'} ] );
							break;
						case 'url':
							if ('undefined' === typeof url || !url) {
								url = value;
							}
							break;
						default:
							window.pmc.maybe_set_ga_property(key, value);
							break;
					} // switch

				}); // foreach
			} // if
		}
		catch (e) {
			console.log('Custom Tracking Event Error: ' + e);
		}

		if (details) {
			if ( 'string' === typeof details ) {
				labelDetails = details;
			} else {
				labelDetails = $.trim($element.text())
					.replace(/(\r?\n|\r)/gm, ' ') // remove line breaks.
					.replace(/\s{2,}/g, ' '); // remove multiple spaces.
			}
			if (labelDetails.length) {
				labelDetails = labelDetails.substring(0, 15);
				labelDetails = ' (' + labelDetails + ')';
			} else {
				labelDetails = '';
			}
		}

		if(  typeof label === 'undefined' || pmc.is_empty( label )){
			label = apply_filters( 'pmc-google-analytics-tracking-label', '', $element );
		} else {
			label = window.pmc.replace_wild_card( $element, label );
		}

		if (url) {
			if ('string' === typeof url && url.length) {
				href = ' ' + url;
			} else {
				href = $element.attr('href');
				if ('string' === typeof href && href.length) {
					href = href.split(/\/\/(.+)?/, 2); // split on only first // found

					if (2 === href.length) {
						href = ' ' + href[1];
					} else {
						href = ' ' + href[0];
					}
				} else {
					href = '';
				}
			}
		}

		category = window.pmc.replace_wild_card( $element, category );

		if ( pre_events ) {
			pre_events = window.pmc.replace_wild_card( $element, pre_events );
		}

		if ('click' === action) {
			var data = window.pmc.get_ga_mapped_data('page-type');
			if (data && data.value  === 'article') {
				window.pmc.maybe_set_ga_property('id',true);
				window.pmc.maybe_set_ga_property('location',true);
			}
		}

		var event = {
			hitType: 'event',
			eventCategory: category,
			eventAction: action,
			eventLabel: device + ' ' + label + labelDetails + href,
			nonInteraction: !!nonInteraction
		};

		if ( 'number' === typeof event_value ) {
			event.eventValue = event_value;
		}

		try {
			if ( pre_events && pre_events.length ) {
				for ( var i in pre_events ) {
					// There will be an unknown number of arguments needed for this ga() call
					// e.g. ec:addProduct has two args, whereas ec:setAction has three.. and so on.
					// Using .apply() allows us to call ga() and pass all given arguments.
					ga.apply( this, pre_events[i] );
				}
			}

			event = apply_filters( 'pmc-google-analytics-tracking-events', event, $element );

			ga('send', event);
		} catch (err) {
			console.log('Event Tracking Error: ' + err);
		}

		return event; // return makes it testable.
	};

	/**
	 * jQuery Event Tracking plugin.
	 * @param atts
	 */
	$.fn.eventTracking = function(atts) {
		if (this && 'object' === typeof atts) {

			var selector = this.selector || atts.selector,
				contentConsumed = false,
				pageOpenedTime = ( new Date() ).getTime();

			var action         = atts.action || 'click',
				category       = atts.category || '',
				details        = atts.details || false,
				iframe         = atts.iframe || false,
				label          = atts.label || '',
				nonInteraction = atts.nonInteraction || false,
				pre_events     = atts.pre_events || [],
				url            = atts.url || false;

			if (!iframe) {

				// Handle a custom 'inview' event.
				//
				// Determines if the given element is currently
				// 'in view'/able to be seen by the user.
				if ( 'inview' === action ) {
					$(selector).each(function () {
						var self = this;
						$(self).one('inview', function (event, isInView) {
							var inview_element = this;
							if (isInView) {
								window.pmc.event_tracking($(inview_element), action, category, label, details, url, nonInteraction, pre_events);
							}
						});
					});
				}

				// Handle a custom 'content consumed' event.
				//
				// Currently this only supports articles and
				// determines that the content has been consumed
				// when: after 3 seconds, the user has scrolled to
				// at least the middle of the article.
				else if ( 'content-consumed' === action ) {
					$( document ).scroll( _.debounce( function( e ) {
						// pmc-inview is a custom jQuery selector
						// located in pmc-js-libraries/js/pmc-jquery-extensions
						if ( $( selector ).is( ':pmc-inview' ) && ! contentConsumed ) {
							var currentTime = ( new Date() ).getTime();
							if ( currentTime - pageOpenedTime >= 3000 ) {
								e.stopPropagation();

								window.pmc.event_tracking( $(this), action, category, label, details, url, nonInteraction, pre_events );
								contentConsumed = true;
							}
						}
					}, 300 ) );
				}

				// Enable click tracking on DFP ad clicks
				else if ( 'dfp-ad-clicks' === action ) {
					if ( 'undefined' !== typeof googletag ) {
						googletag.cmd.push( function() {

							// Once each ad finishes rendering find the ad's iframe, and attach
							// a click event to the anchor tag within, which fires the GA event when clicked.
							googletag.pubads().addEventListener( 'slotRenderEnded', function( event ) {
								var slot_name = event.slot.getName(),
									$ad_iframe = $( 'iframe[id*="' + slot_name + '"]' ),
									$ad_iframe_container = $ad_iframe.parents( '.admz' ),
									_event = event;

								$ad_iframe.contents().find('body a').each(function() {
									$(this).click(function(){

										// Obtain the &adurl= query arg value for inclusion in the event label
										var ad_url = $(this).attr('href').split('?')[1].split('&');
										for ( var i = 0; i < ad_url.length; i++ ) {
											var kv = ad_url[ i ].split( '=' );
											if ( 'adurl' === kv[0] ) {
												ad_url = decodeURIComponent( kv[1] );
												break;
											}
										}

										action = 'click';
										category = 'DFP Ad Click';

										label = {
											'location': $ad_iframe_container.attr('id'),
											'oid': _event.campaignId,
											'liid': _event.lineItemId,
											'cid': _event.creativeId,
											'to_url': ad_url
										};

										if ( window.pmc && window.pmc.hooks && window.pmc.hooks.apply_filters ) {

											/**
											 * Filter the label parts before they're joined into a string.
											 *
											 * @param array  label      The individual parts of the event label.
											 * @param jQuery $ad_iframe The iFrame containing the ad.
											 * @param string slot_name  The DFP slot name for the current ad.
											 * @param object _event     googletag.events.SlotRenderEndedEvent object.
											 *                          See https://developers.google.com/doubleclick-gpt/reference#googletageventsslotrenderendedevent
											 */
											label = window.pmc.hooks.apply_filters(
												'pmc-google-universal-analytics-dfp-event-label-array',
												label,
												$ad_iframe.contents(),
												slot_name,
												_event
											);
										}

										label = window.JSON.stringify( label );

										window.pmc.event_tracking( $(this), action, category, label, details, url, nonInteraction, pre_events );

										if ( window.pmc && window.pmc.hooks && window.pmc.hooks.do_action ) {

											/**
											 * Allow theme to fire action on dfp-ad-clicks
											 *
											 * @param string  ad_url      the Destination ad url
											 * @param array  pre_events   the pre_events that are required
											 */
											window.pmc.hooks.do_action(
												'pmc-google-universal-analytics-dfp-ad-clicks-pre-events',
												pre_events,
												ad_url
											);
										}

									});
								});
							} );
						});
					}
				}

				// Handle default HTML event types
				// e.g. click, hover, etc.
				else {
					$(document).on(action, selector, function(e) {
						e.stopPropagation();

						window.pmc.event_tracking( $(this), action, category, label, details, url, nonInteraction, pre_events );
					});
				}

			} else if ('string' === typeof iframe) {
				$(iframe).load(function() {
					$(iframe).contents().on(action, selector, function(e) {
						e.stopPropagation();

						window.pmc.event_tracking( $(this), action, category, label, details, url, nonInteraction, pre_events );
					});
				});
			}
		}
	};

	/**
	 * Helper function load additional ga plugin once
	 * @param plugin
	 */
	window.pmc.maybe_load_ga_plugin = function ( plugin ) {
		window.pmc.ga_plugin_loaded = window.pmc.ga_plugin_loaded || {};
		if (!window.pmc.ga_plugin_loaded[plugin]) {
			ga('require', plugin);
			window.pmc.ga_plugin_loaded[plugin] = true;
		}
	};

	/**
	 * Helper function to populate the ga property if not exist
	 * @param key
	 * @param value
	 */
	window.pmc.maybe_set_ga_property = function( key, value ) {
		if ( ! value ) {
			return;
		}
		try {
			var data = window.pmc.get_ga_mapped_data(key);
			if ('boolean' === typeof value && value) {
				value = (data && data.value) ? data.value : false;
			}
			if (data && data.key && value) {
				var old_value = undefined;
				if (window.pmc.ga_tracker_instance) {
					old_value = window.pmc.ga_tracker_instance.get(data.key);
				}
				if (old_value != value && value) {
					ga('set', data.key, value);
				}
			}
		} catch (err) {
			console.log('maybe_set_ga_property Error: ' + err);
		}
	};

	/**
	 * Helper function to merge fields from merge_with into data if field doesn't exists
	 * @param data
	 * @param merge_with
	 * @return {*}
	 */
	window.pmc.maybe_merge_fields = function (data,merge_with) {
		try {
			if (merge_with && 'object' === typeof merge_with) {
				Object.entries(merge_with).forEach(function (entry) {
					var key = entry[0];
					var value = entry[1];
					if (!data[key] && value) {
						data[key] = value;
					} else if ('object' === typeof data[key] && 'object' === typeof value) {
						data[key] = window.pmc.maybe_merge_fields(data[key], value);
					}
				});
			}
		}
		catch(e) {
			console.log('Error: maybe_merge_fields - ' + e);
		}
		return data;
	};

	/**
	 * Helper function to merge data for ec:addProduct & ec:setAction, otherwise append to array
	 * @param array pre_events
	 * @param array data
	 * @return array
	 */
	window.pmc.maybe_set_ga_pre_events = function (pre_events, data) {
		try {
			for (var i in pre_events) {
				var event = pre_events[i];
				if (event[0] === data[0]) {
					switch (event[0]) {
						case 'ec:addProduct':
						case 'ec:setAction':
							pre_events[i][1] = window.pmc.maybe_merge_fields(pre_events[i][1], data[1]);
							return pre_events;
					}
				}
			}
			pre_events.push( data );
		}
		catch (e) {
			console.log('Error: maybe_set_ga_pre_events - ' + e);
		}
		return pre_events;
	};

	/**
	 * Lookup the mapped ga dimension key value pair
	 * @param key
	 * @return {{value: boolean, key}|{value: boolean, key: boolean}|boolean}
	 */
	window.pmc.get_ga_mapped_data = function (key) {
		var dimension = false;
		var value = false;

		if ('location' === key) {

			if ( pmc && pmc.tracking ) {
				var utms = pmc.tracking.get_properties_string();
				if ( utms !== '' ) {
					// the utm params are gone already so add them back
					value = window.location.href.split('#')[0] + ( window.location.search ? '&' : '?' ) + utms;
				}
			}

			if (!value) {
				query = document.querySelector("link[rel='canonical']");
				if (query) {
					value = query.href;
				}
				if (!value) {
					value = document.location.href;
				}
			}

			return {
				'key' : key,
				'value': value
			};
		}

		if (key.match(/^dimension\d+/)) {
			dimension = key;
		} else {
			if (window.pmc_ga_mapped_dimensions && window.pmc_ga_mapped_dimensions[key]) {
				dimension = 'dimension' + window.pmc_ga_mapped_dimensions[key];
			}
		}
		if (dimension) {
			if (window.pmc_ga_dimensions && window.pmc_ga_dimensions[dimension]) {
				value = window.pmc_ga_dimensions[dimension];
			}
			return {
				'key': dimension,
				'value': value
			};
		}
		return false;
	};

	/**
	 * Helper function to replace templated string %=name=% with attribute from the given $selector element
	 * If the attribute value is empty, do a ga mapping lookup for the matched properties the the ga mapping dimension
	 * @param $selector
	 * @param event_attr The string in the format %=name=%
	 * @return {string|*|{}|[]}
	 */
	window.pmc.replace_wild_card = function ($selector, event_attr) {

		// prevent null object falling through and cause fatal errors
		if ( null === event_attr ) {
			return event_attr;
		}

		if ( event_attr instanceof Array ) {
			var attributes = [];
			for ( var i in event_attr ) {
				attributes[i] = window.pmc.replace_wild_card( $selector, event_attr[i] );
			}
			return attributes;
		} else if( 'object' === typeof event_attr ) {
			var attributes = {};
			Object.keys(event_attr).forEach( function (key) {
				attributes[key]= window.pmc.replace_wild_card( $selector, event_attr[key] );
			});
			return attributes;
		} else if( 'string' === typeof event_attr ) {
			var regex = /%=(.*)=%/;
			if ( null !== ( data_attr = regex.exec(event_attr) ) ) {
				var attr = $selector.attr(data_attr[1]);
				if ( typeof attr !== 'undefined' && false !== attr && '' !== attr ) {
					return attr;
				}

				// get the mapped key/value from a given name from ga mapped fields/value
				var match = data_attr[1].match(/^ga-map-(key|value)-(.+)/);
				if (match) {
					attr = window.pmc.get_ga_mapped_data(match[2]);
					if (attr) {
						switch (match[1]) {
							case 'key':
								if (attr.key) {
									return attr.key;
								}
								break;
							case 'value':
								if (attr.value) {
									return attr.value;
								}
								break;
						}
					}
				} else {
					attr = window.pmc.get_ga_mapped_data(data_attr[1]);
					if (attr && attr.value) {
						return attr.value;
					}
				}
			}
		}
		return event_attr;
	};

	// Implement PMC Analytics functions
	var pmc_analytics = {

		/**
		 * Replaced the pmc.analytics array push function
		 *
		 * @param {function} cmd The command to execute
		 */
		push: function (cmd) {
			if (typeof cmd === 'function') {
				try {
					cmd();
				}
				catch (e) {
				}
			}
		},


		/**
		 * Helper function to track GA pageview.
		 * Notes: This call will trigger a new pvuuid and send pageviews event to Atlas 1PD.
		 *
		 * @param {string} url       The page view url to track
		 */
		track_pageview: function (url) {
			// Need to force new pvuuid for new pageview event
			var pvuuid = pmc.generate_pvuuid(true);

			// Updating the GA mapped dimension as well if it exists
			if (typeof pmc_ga_mapped_dimensions !== 'undefined' && typeof pmc_ga_dimensions !== 'undefined' && pmc_ga_mapped_dimensions['pageview-id']) {
				var dim_name = 'dimension' + pmc_ga_mapped_dimensions['pageview-id'].toString();

				pmc_ga_dimensions[dim_name] = pvuuid;

				// This field exists if GA is enabled for custom dimension tracking
				if (typeof pmcGaCustomDimensions !== 'undefined' && typeof ga !== 'undefined') {
					pmcGaCustomDimensions[dim_name] = pvuuid;
					ga('set', pmcGaCustomDimensions);
				}
			}

			if (typeof ga !== 'undefined') {
				ga( 'send', 'pageview', url );
			}

			blogherads.adq.push(function () {
				try {
					// Set the 1st party data if available
					blogherads.setPageMetaData( window.pmc_fpd || {} );

					// Tell Atlas to skip generating new pvuuid and re-use existing
					blogherads.trackPageView(true);
				}
				catch (e) {
				}
			});

		}
	};

	// add a filter for passing social button event data to pmc-social-share-bar
	pmc.hooks.add_filter('pmc_event_tracking_social_data', function (socialEvent, socialNetwork) {
		if (events.length <= 0 || null === socialNetwork) {
			return null;
		}

		var name = socialNetwork.toLowerCase();
		var selector = ('pinit' === name) ? '.btn-pinterest' : '.btn-' + name;
		var index = null;

		// if an event is not provided, try finding a match in the events array
		if (null === socialEvent) {
			for (var i = 0; i < events.length; i++) {
				if ( events[i].selector === selector ) {
					index = i;
					break;
				}
			}
		}

		if (null !== index) {
			var event = events[index];
			socialEvent = {
				hitType: 'event',
				eventCategory: event.category,
				eventAction: event.action,
				eventLabel: device + ' ' + event.label,
				nonInteraction: event.nonInteraction
			};
		}

		return socialEvent;
	});

	// Expose the ga tracker instance so we can determine certain field is set during click event
	if ( ! window.pmc.ga_tracker_instance ) {
		if ('function' === typeof ga) {
			ga(function (tracker) {
				window.pmc.ga_tracker_instance = tracker;
			});
		}
	}

	var pmc_analytics_queues = pmc.analytics;  // save the queues to process later

	// Assign pmc GA implementation
	pmc.analytics = pmc_analytics;

	// Process pending the analytics queues
	if (pmc_analytics_queues && Array.isArray(pmc_analytics_queues) && pmc_analytics_queues.length) {
		pmc_analytics_queues.forEach(function(cmd){
			pmc_analytics.push(cmd);
		});
	}

	// Start processing the list of events for tracking

	for (var i = 0; i < events.length; i++) {
		try {
			var e = events[i];
			$(e.selector).eventTracking(e);
		} catch (err) {
			console.log('Event Tracking Error: ' + err);
		}
	}

})(jQuery);

// EOF
