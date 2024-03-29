/**
 * Copyright 2013 Michael Countis
 *
 * Cleaned up by Mike Auteri - PMCRS-121 - 01-31-2017
 *
 * To Generate a Minified Version:
 * npm install uglify -g
 * cd pmc-adm/assets/js/dfp-events
 * uglify -s dfp.gpt.logger.override.js -o dfp.gpt.logger.override.min.js
 *
 * MIT License: https://opensource.org/licenses/MIT
 */

( function() {
	"use strict";

	window.googletag = window.googletag || {};
	window.googletag.cmd = window.googletag.cmd || [];

	googletag.cmd.push( function() {

		if ( googletag.hasOwnProperty("on") || googletag.hasOwnProperty("off") || googletag.hasOwnProperty("trigger") || googletag.hasOwnProperty("events") ) {
			return;
		}

		var old_log = googletag.debug_log.log,
			events = [],
			addEvent = function( name, id, match ) {
				events.push( {
					"name": name,
					"id" : id,
					"match" : match
				} );
			};

		addEvent( "gpt-google_js_loaded",                    8, /Google service JS loaded/ig );
		addEvent( "gpt-gpt_fetch",                           46, /Fetching GPT implementation/ig );
		addEvent( "gpt-gpt_fetched",                         48, /GPT implementation fetched\./ig );
		addEvent( "gpt-page_load_complete",                  1, /Page load complete/ig );
		addEvent( "gpt-queue_start",                         31, /^Invoked queued function/ig );

		addEvent( "gpt-service_add_slot",                    40, /Associated ([\w]*) service with slot ([\/\w]*)/ig );
		addEvent( "gpt-service_add_targeting",               88, /Setting targeting attribute ([\w]*) with value ([\w\W]*) for service ([\w]*)/ig );
		addEvent( "gpt-service_collapse_containers_enable",  78, /Enabling collapsing of containers when there is no ad content/ig );
		addEvent( "gpt-service_create",                      35, /Created service: ([\w]*)/ig );
		addEvent( "gpt-service_single_request_mode_enable",  63, /Using single request mode to fetch ads/ig );

		addEvent( "gpt-slot_create",                         2, /Created slot: ([\/\w]*)/ig );
		addEvent( "gpt-slot_add_targeting",                  17, /Setting targeting attribute ([\w]*) with value ([\w\W]*) for slot ([\/\w]*)/ig );
		addEvent( "gpt-slot_fill",                           50, /Calling fillslot/ig );
		addEvent( "gpt-slot_fetch",                          3, /Fetching ad for slot ([\/\w]*)/ig );
		addEvent( "gpt-slot_receiving",                      4, /Receiving ad for slot ([\/\w]*)/ig );
		addEvent( "gpt-slot_render_delay",                   53, /Delaying rendering of ad slot ([\/\w]*) pending loading of the GPT implementation/ig );
		addEvent( "gpt-slot_rendering",                      5, /^Rendering ad for slot ([\/\w]*)/ig );
		addEvent( "gpt-slot_rendered",                       6, /Completed rendering ad for slot ([\/\w]*)/ig );

		googletag.events = googletag.events || {};

		/**
		 * googletag.on
		 *
		 * @param events
		 * @param op_arg0 (data)
		 * @param op_arg1 (callback)
		 * @returns {googletag}
		 */
		googletag.on = function( events, op_arg0, op_arg1 ) {
			if ( ! op_arg0 ) {
				return this;
			}

			events = events.split(" ");

			var	data = op_arg1 ? op_arg0 : undefined,
				callback = op_arg1 || op_arg0,
				ei = 0,
				e = '';

			callback.data = data;

			for( e = events[ei = 0]; ei < events.length; e = events[++ei] ) {
				( this.events[e] = this.events[e] || [] ).push( callback );
			}

			return this;
		};

		/**
		 * googletag.off
		 *
		 * @param events
		 * @param handler
		 * @returns {googletag}
		 */
		googletag.off = function( events, handler ) {
			events = events.split(" ");

			var	ei = 0,
				e = "",
				fi = 0,
				f = function(){};

			for ( e = events[ei]; ei < events.length; e = events[++ei] ) {
				if ( ! this.events.hasOwnProperty( e ) ) {
					continue;
				}

				if ( ! handler) {
					delete this.events[e];
					continue;
				}

				fi = this.events[e].length - 1;

				for ( f = this.events[e][fi]; fi >= 0; f = this.events[e][--fi] ) {
					if( f === handler ) {
						this.events[e].splice( fi, 1 );
					}
				}

				if ( 0 === this.events[e].length ) {
					delete this.events[e];
				}
			}

			return this;
		};

		/**
		 * googletag.trigger
		 *
		 * @param event
		 * @param parameters
		 * @returns {googletag}
		 */
		googletag.trigger = function( event, parameters ) {

			if( ! this.events[event] || this.events[event].length === 0 ) {
				return this;
			}

			var	parameters = parameters || [],
				fi = 0,
				f = this.events[event][fi];

			for ( fi, f; fi < this.events[event].length; f = this.events[event][++fi] ) {
				if ( false === f.apply( this, [{ data:f.data }].concat( parameters ) ) ) {
					break;
				}
			}

			return this;
		};

		/**
		 * googletag.debug_log.log
		 *
		 * @param level
		 * @param message
		 * @param service
		 * @param slot
		 * @param reference
		 * @returns {*}
		 */
		googletag.debug_log.log = function( level, message, service, slot, reference ) {

			if ( message && message.getMessageId && 'number' === typeof ( message.getMessageId() ) ) {
				var	args = Array.prototype.slice.call( arguments ),
					e = 0;

				for(e; e < events.length; e++)
					if( events[e].id === message.getMessageId() ) {
						googletag.trigger( events[e].name, args );
					}
			}

			return old_log.apply(this,arguments);
		};

	} );

} )();

// EOF
