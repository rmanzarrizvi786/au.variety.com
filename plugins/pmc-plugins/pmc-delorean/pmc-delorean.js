/*jslint browser: true, devel: true, ass: true, eqeq: true, forin: true, newcap: true, nomen: true, plusplus: true, regexp: true, unparam: true, sloppy: true, todo: true, vars: true, white: true */
/*global jQuery: true, PMC_Delorean: true, pmc_delorean: true, pmc_site_time: true, pmc:true, moment: true */
PMC_Delorean = {
	Default: 'default',
	debug_mode: false // set to true to prevent all form submits
};

(function($,window){
	if( typeof pmc_delorean === 'undefined' || typeof pmc_site_time === 'undefined' ) {
		return;
	}

	var pmc_delorean_post, pmc_delorean_click_handler, pmc_delorean_user_tz, pmc_delorean_quickedit, pmc_delorean_clock, pmc_delorean_reset_ui;
	var server_time, local_time, clock_format, user_tz;

	PMC_Delorean.user_tz = pmc_delorean_user_tz = function() {
		var now = new Date().toString();
		var TZ = now.indexOf('(') > -1 ?
			now.match(/\([^\)]+\)/)[0].match(/[A-Z]/g).join('') :
			now.match(/[A-Z]{3,4}/)[0];
		if( TZ == "GMT" && /(GMT\W*\d{4})/.test(now) ) {
			TZ = RegExp.$1;
		}
		return TZ;
	};


	jQuery('#wpadminbar .pmc-delorean .ab-item').html('<span class="server"></span><span class="local"></span>');
	server_time = jQuery('#wpadminbar .pmc-delorean .ab-item .server');
	local_time = jQuery('#wpadminbar .pmc-delorean .ab-item .local');

	if( pmc_delorean.show_clock && server_time.length && local_time.length ) {
		clock_format = pmc_delorean.clock_seconds ? 'H:mm:ss ' : 'H:mm ';

		user_tz = PMC_Delorean.user_tz();

		if( user_tz != pmc_site_time.tz_abbrev ) {
			local_time.show();
		}

		PMC_Delorean.clock = pmc_delorean_clock = function() {
			if( typeof moment !== 'function' ) {
				if( window.console && window.console.log ) {
					console.log("ERROR: moment.js is not loaded, PMC_Delorean will not function");
				}
				return;
			}
			var server = moment().zone( pmc_site_time.gmt_num_offset );

			server_time.text( server.format(clock_format) + pmc_site_time.tz_abbrev );
			if( user_tz != pmc_site_time.tz_abbrev ) {
				var local = moment();
				local_time.text( local.format('H:mm ') + user_tz );
			}

		};
		PMC_Delorean.clock();
		window.setInterval(PMC_Delorean.clock, 1000);
	}

	/**
	 * Flash a warning on the Quick Edit screen if a publish was prevented
	 * @see PMC_Delorean::quickedit_publish_check
	 *
	 */
	if( pmc_delorean.screen_id == 'edit-post' ) {
		PMC_Delorean.quickedit = pmc_delorean_quickedit = function(event, xhr, settings) {
			var el, post_id, action, params = settings ? (settings.hasOwnProperty('data') ? jQuery.deparam( settings.data ) : 0) : 0;
			if( !params || !(action = params.hasOwnProperty('action') ? params.action : 0) ) {
				return;
			}

			if( action == 'inline-save' ) {
				if( !(post_id = params.hasOwnProperty('post_ID') ? params.post_ID : 0) ) {
					return;
				}
				el = jQuery('#edit-' + post_id + ' .inline-edit-save .error');
				if( typeof pmc !== 'undefined' && pmc.hasOwnProperty('flash') && el.length && (el.is(':hidden') == false || el.text().length ) ) {
					pmc.flash(el, {highlight:'yellow', out:1500 });
				}
			}
		};
		jQuery(document).ajaxSuccess( PMC_Delorean.quickedit );
	}

	if( pmc_delorean.screen_id == 'post' ) {

		PMC_Delorean.click_handler = pmc_delorean_click_handler = function(e) {
			var id = jQuery(this).data( 'pmc-delorean-id' ) || 0;

			// A known element triggered the click (or submit)
			if( !id || !id.length ) {
				id = 0;

				// This is the only way to track submitting the form via enter key (the same as clicking submit#publish)
				if( e.type === 'keydown' && e.keyCode === 13) {
					id = 'enter-key';
				}
			}

			if( id ) {
				jQuery('form#post').data( 'pmc-delorean-clicked', id );
				jQuery('form#post').data( 'pmc-delorean-trigger', e.type );

				if( PMC_Delorean.debug_mode ) {
					console.log( 'PMC_Delorean: Click Handler: Clicked: %s (type: %s)', id, e.type );
				}
			}
		};

		/**
		 * Reset the Publish Post metabox UI if a publish was aborted.
		 *  -- Remove the spinners and re-enable disabled buttons.
		 *
		 * @since 2014-07-14 Corey Gilmore
		 * @see PPT-2762, PPT-2872
		 *
		 */
		PMC_Delorean.reset_ui = pmc_delorean_reset_ui = function() {
			// Clean up the interface by hiding all the spinners
			jQuery('#publishing-action .spinner').hide();
			jQuery('#publish').prop('disabled', false).removeClass('button-primary-disabled');

			jQuery('#save-post').prop('disabled', false).removeClass('button-disabled');
			jQuery('#save-action .spinner').hide();
		};

		/**
		 * Intercept form submits (whenever possible) to prevent back publishing
		 *
		 * @since 2014-07-14 Corey Gilmore
		 * @see PPT-2762, PPT-2872
		 *
		 */
		PMC_Delorean.post = pmc_delorean_post = function(e) {
			if( typeof moment !== 'function' ) {
				if( window.console && window.console.log ) {
					console.log("ERROR: moment.js is not loaded, PMC_Delorean will not function");
				}
				return;
			}

			var is_future_publish = false, is_safe_status = true, is_primary_submit = false;
			var attempted_date, now, time_str, retval = true, event_detail;
			var orig_status = jQuery('#original_post_status').val();
			var new_status = jQuery('#post_status').val();
			var src_button = jQuery('form#post').data( 'pmc-delorean-clicked' ) || 0;
			var src_event = jQuery('form#post').data( 'pmc-delorean-trigger' ) || 0;

			// Check for a scheduled post with a date in the past (it will be published and not scheduled)
			time_str = '{0}-{1}-{2} {3}:{4} {5}'.format( jQuery('#aa').val(), jQuery('#mm').val(), jQuery('#jj').val(), jQuery('#hh').val(), jQuery('#mn').val(), pmc_site_time.gmt_offset);
			attempted_date = moment( time_str );
			now = moment();

			is_future_publish = attempted_date.isBefore(now);

			// Check for a submit via the primary submit button or an enter key
			if( src_button == 'publish' || src_button == 'enter-key' ) {
				is_primary_submit = true;
			}

			// // If our new status is not publish, or the post was already published, an action/transition is considered "safe"
			// If the post was already published, an action/transition is considered "safe"
			is_safe_status = orig_status == 'publish';

			if( PMC_Delorean.debug_mode ) {
				PMC_Delorean.debug_log = {};
				PMC_Delorean.debug_log.now = now;
				PMC_Delorean.debug_log.time_str = time_str;
				PMC_Delorean.debug_log.attempted_date = attempted_date;

				console.log("PMC_Delorean: SUBMIT: Clicked: %s | Trigger: %s | new_status: %s | orig_status: %s\nis_future_publish: %s | is_safe_status: %s | is_primary_submit: %s\ntime_str: %s | attempted_date: %s | now : %s",
					jQuery('form#post').data( 'pmc-delorean-clicked' ),
					jQuery('form#post').data( 'pmc-delorean-trigger' ),
					new_status, orig_status,
					is_future_publish, is_safe_status, is_primary_submit,
					time_str, attempted_date.toString(), now.toString()
				);
			}

			event_detail = {
				is_future_publish: is_future_publish,
				new_status: new_status,
				orig_status: orig_status,
				attempted_date: attempted_date,
				now: now,
				src_button: src_button,
				src_event: src_event,
				is_primary_submit: is_primary_submit
			};

			// Trigger an event allowing anyone to cancel the submit by returning false
			// triggerHandler returns undefined by default
			retval = jQuery(document).triggerHandler('pmc_delorean.submit', [ this, e, event_detail ] );

			// no action from the handler, or the default behavior
			if( typeof retval === 'undefined' || retval == PMC_Delorean.Default ) {
				if( src_button === 'preview' || src_button === 'save' ) {
					retval = true;
				} else if( (!is_future_publish || is_safe_status) && !is_primary_submit ) {
					// If we aren't accidentally back-pubbing, or it's a safe status,
					// and the primary submit action (publish) wasn't selected, allow the submit to continue
					retval = true;
				} else if( !is_future_publish && is_primary_submit ) { // scheduling a post -- check the text value of the button too?
					retval = true;
				} else {
					retval = confirm( "Post will likely be published immediately, proceed?\nPast Date: {0} | Button: {1} | Status: {2}".format( (is_future_publish ? 'Yes' : 'No'), src_button, new_status ) );
				}
			}

			if( !retval ) {
				PMC_Delorean.reset_ui();
			}

			if( PMC_Delorean.debug_mode ) {
				PMC_Delorean.reset_ui();
				console.log( 'PMC_Delorean: return: %s, event: %o', retval, event_detail );
				return false; // DEBUG
			}

			return retval;
		};

		jQuery(document).ready( function() {
			var events = 'click mousedown mouseup'; // keydown

			// Data values to track the source element and event type of the post form submit
			jQuery('form#post').data( 'pmc-delorean-trigger', '' );
			jQuery('form#post').data( 'pmc-delorean-clicked', '' );

			/**
			 * Brute force - track anything that can trigger a form submit
			 *  Track keydown (enter) on non-textarea form elements
			 *  Track click/mousedown/mouseup/keydown event for buttons and button-ish links
			 *
			 */
			jQuery('form#post input,button,submit,select').keydown( PMC_Delorean.click_handler );

			jQuery('#save-post').data( 'pmc-delorean-id', 'save' ).on( events, PMC_Delorean.click_handler );
			jQuery('.preview.button').data( 'pmc-delorean-id', 'preview' ).on( events, PMC_Delorean.click_handler );
			jQuery('.submitdelete.deletion').data( 'pmc-delorean-id', 'delete' ).on( events, PMC_Delorean.click_handler );
			jQuery('#publish').data( 'pmc-delorean-id', 'publish' ).on( events, PMC_Delorean.click_handler );


			// Intercept submit events so we can prevent back-publishing
			jQuery('form#post').on( 'submit', PMC_Delorean.post );
		});
	}


})(jQuery,this);