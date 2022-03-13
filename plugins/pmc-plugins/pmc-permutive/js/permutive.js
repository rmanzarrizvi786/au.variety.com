/**
 * - To create minified version of permutive.min.js:
 * - npm install uglify -g (to install uglify)
 * - uglify -s permutive.js -o permutive.min.js
 */
/* global jQuery, pmc_permutive_data */

( function ( $ ) {
	'use strict';
	$( document ).ready( function () {
		var trackers = ( 'object' === typeof pmc_permutive_data ) ? pmc_permutive_data : {};

		if( trackers.length > 0 ) {
			trackers.forEach( function ( event ) {
				if ( 'buy-now' === event.tracker.type ) {
					buy_now_tracker( event.tracker );
				} else if( 'inline-link' === event.tracker.type ) {
					inline_link_tracker(event.tracker);
				} else if( 'widget' === event.tracker.type ) {
					widget_tracker(event.tracker);
				}
			});
		}

		function buy_now_tracker( tracker ) {

			if( 'object' !== typeof tracker ||
				'string' !== typeof tracker.container ||
				'object' !== typeof tracker.data_points ||
				'string' !== typeof tracker.data_points.name ||
				'string' !== typeof tracker.data_points.price
			) {
				return false;
			}

			$( tracker.container ).click(function ( e ) {
				var target = jQuery(e.currentTarget),
					title = ( target.find(tracker.data_points.name).length > 0 ) ? target.find( tracker.data_points.name )[0].innerText : '',
					price = ( target.find(tracker.data_points.price).length > 0 ) ? target.find( tracker.data_points.price )[0].innerText : '',
					link = ( target.find( tracker.data_points.link ).length > 0 ) ? target.find( tracker.data_points.link)[0].href : target[0].href;

				price = parseFloat( price.split( ' ' )[0].replace( '$', '' ) );

				if ( title && link && price ) {
					var data = {
						title: title,
						link: link,
						price: price,
						type: tracker.event_type_label
					};
					fire_event(data);
				}
			});
		}

		function inline_link_tracker( tracker ) {

			if( 'object' !== typeof tracker || 'string' !== typeof tracker.container ) {
				return false;
			}

			$( tracker.container ).click( function ( e ) {
				var target = jQuery( e.currentTarget ),
					title = ( target.length > 0 ) ? target[0].innerText : '',
					link = ( target.length > 0 ) ? target[0].href : '';
				if ( title && link ) {
					var data = {
						title: title,
						link: link,
						type: tracker.event_type_label
					};
					fire_event( data );
				}
			});
		};

		function widget_tracker( tracker ) {

			if( 'object' !== typeof tracker || 'string' !== typeof tracker.container ) {
				return false;
			}

			$( tracker.container ).click(function ( e ) {
				var target = jQuery( e.currentTarget ),
					title = ( target.length > 0 ) ? target[0].innerText : '',
					link = ( target.length > 0 ) ? target[0].href : '';
				if ( link ) {
					var data = {
						title: title,
						type: tracker.event_type_label,
						link: link
					};
					fire_event( data );
				}
			});
		};

		function fire_event( data ) {
			if( 'undefined' === typeof data ) {
				return;
			}
			var partner = ( 'string' === typeof data.link ) ? domain_name( data.link ) : '',
				type    = ( 'string' === typeof data.type ) ? data.type : '',
				title   = ( 'string' === typeof data.title ) ? data.title : '',
				price   = ( 'number' === typeof data.price ) ? data.price : '';

			var amazon_aliases = [ 'amazon', 'amzn' ];
			partner = amazon_aliases.includes( partner.toLowerCase() ) ? 'Amazon' : partner;
			permutive.track( 'WidgetLinkClick', {
				'widget': {
					'type': type
				},
				'partner': partner,
				'product': {
					'price': price,
					'currency': 'US',
					'category': '',
					'name': title
				}
			})
		};

		function domain_name( domain ) {
			var a = document.createElement( 'a' );
			a.href = domain;
			var hostname = a.hostname;
			var host_split = hostname.split( '.' );
			host_split.pop();
			if ( host_split.length > 1 ) {
				host_split.shift();
			}
			return host_split.join();
		}
	});
} )( jQuery );
