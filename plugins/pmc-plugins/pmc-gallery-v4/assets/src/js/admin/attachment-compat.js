( function ( $, media ) {
	'use strict';
	$( document ).ready( function () {
		var pmc = window.pmc || {},
			AttachmentCompat = media.view.AttachmentCompat;
		pmc.view = pmc.view || {};
		pmc.view.AttachmentCompat = AttachmentCompat.extend( {

			/**
			 * Extend render() function of parent class.
			 * To add [data-setting] attribute in image credit input field.
			 *
			 * @returns {pmc.view.AttachmentCompat} return itseld to allow chaining.
			 */
			render: function () {
				AttachmentCompat.prototype.render.apply( this, arguments );
				$( '.compat-field-image_credit input', this.$el ).attr( 'data-setting', 'image_credit' ).val( this.model.get( 'image_credit' ) );
				$( '.compat-field-image_source_url input', this.$el ).attr( 'data-setting', 'image_source_url' ).val( this.model.get( 'image_source_url' ) );
				return this;
			},
			/**
			 * Function is used to save custom field's changed data in to database.
			 * Since, it will be handle by `pmc.view.AttachmentDetailsTwoColumn.save()`
			 * (only for gallery's `image_credit` field) because code that's
			 * written in `render()`.
			 * Parent function will make ajax callback of custom field. we need to prevent
			 * therefore, extending parent's function and doing nothing will make sure
			 * that, not any action being act.
			 *
			 * @returns {void}
			 */
			save: function () {
				//	Keep this function blank
			}
		} );
		_.extend( window.pmc, pmc );
	} );
} )( jQuery, wp.media );
