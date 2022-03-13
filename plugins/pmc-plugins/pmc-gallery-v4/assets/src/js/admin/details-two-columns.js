( function ( $, media ) {
	'use strict';
	$( document ).ready( function () {
		var pmc = window.pmc || {},
			AttachmentDetailsTwoColumn = media.view.Attachment.Details.TwoColumn;
		pmc.view = pmc.view || {};
		pmc.view.AttachmentDetailsTwoColumn = AttachmentDetailsTwoColumn.extend( {
			/**
			 * Function is used to save changed data in to database.
			 *
			 * @param {string} key Key field which was changed.
			 * @param {string} value Value of that key that was changed.
			 * @returns {void}
			 */
			save: function ( key, value ) {
				// Get UTC timestamp for current moment
				var dateFormate = this.getUTCStamp( new Date() );
				var data = {
					modified_gmt: dateFormate // Time stamp this will help in updating 'last save' time-stamp.
				};
				data[ key ] = value; // Current model's changed value.

				// Checks if in bulkEdit mode
				if ( this.options.bulkEdit ) {
					// Get current selection of attachments for bulk edit
					var models = this.controller.state().frame.options.selection.models,
						selected = this.model.get( 'id' );

					// This is to make sure each attachment gets updated on the front
					models.forEach( function ( current ) {
						if ( selected !== current.get( 'id' ) ) {
							current.set( data, { silent: true } );
						}
					} );
				}
				this.model.set( data );
			},
			getUTCStamp: function ( dateObj ) {
				var curr_date = dateObj.getUTCDate(),
					curr_month = dateObj.getUTCMonth(),
					curr_year = dateObj.getUTCFullYear(),
					curr_min = dateObj.getUTCMinutes(),
					curr_hr = dateObj.getUTCHours(),
					curr_sc = dateObj.getUTCSeconds();
				curr_month = curr_month + 1;
				if ( curr_month.toString().length === 1 ) {
					curr_month = '0' + curr_month;
				}
				if ( curr_date.toString().length === 1 ) {
					curr_date = '0' + curr_date;
				}
				if ( curr_hr.toString().length === 1 ) {
					curr_hr = '0' + curr_hr;
				}
				if ( curr_min.toString().length === 1 ) {
					curr_min = '0' + curr_min;
				}
				if ( curr_sc.toString().length === 1 ) {
					curr_sc = '0' + curr_sc;
				}
				return curr_year + '-' + curr_month + '-' + curr_date + ' ' + curr_hr + ':' + curr_min + ':' + curr_sc;
			}
		} );
		_.extend( window.pmc, pmc );
	} );
} )( jQuery, wp.media );
