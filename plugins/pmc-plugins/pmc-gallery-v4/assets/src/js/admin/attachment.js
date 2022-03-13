/* eslint no-magic-numbers: [ "error", { "ignore": [1] } ]*/
( function ( $, media ) {
	'use strict';
	$( document ).ready( function () {
		var pmc = window.pmc || {},
			Attachment = media.view.Attachment;
		pmc.view = pmc.view || {};

		/**
		 * Extends wp.media.view.Attachment.
		 */
		pmc.view.Attachment = Attachment.extend( {
			/**
			 * Initialize function for pmc.view.Attachment
			 *
			 * @returns {void}
			 */
			initialize: function () {
				var mode = this.controller.content.mode();
				switch ( mode ) {
					case 'gallery_edit':
						this.buttons = {
							close: true,
							check: true
						};
						break;
					default:
						this.buttons = {
							check: true
						};
						break;
				}
				Attachment.prototype.initialize.apply( this, arguments );
			},

			/**
			 * Extends the events variable of parent class.
			 *
			 * @returns {object} List of events.
			 */
			events: function () {
				return _.extend( {}, Attachment.prototype.events, {
					'dblclick .js--select-attachment': 'openModal',
					'click .close': 'removeFromLibrary'
				} );
			},

			/**
			 * Renders individual element for attachment.
			 *
			 * @returns {void}
			 */
			render: function () {
				Attachment.prototype.render.apply( this, arguments );
			},

			/**
			 * Callback function for double click event of individual elements
			 * to open it in modal for update.
			 *
			 * @returns {object} instance of pmc.view.EditAttachments
			 */
			openModal: function () {
				var mode = this.controller.content.mode(),
					state = this.controller.state(),
					modal, selection;
				if ( 'gallery_edit' === mode ) {
					state = this.controller.state( 'gallery-edit' );
					selection = state.get( 'selection' );
				}
				if ( undefined !== selection && 1 < selection.length ) {
					modal = new pmc.view.BulkEditAttachments( {
						frame: 'edit-attachments',
						controller: this.controller,
						library: state.get( 'library' ),
						selection: state.get( 'selection' ),
						model: this.model
					} );
				} else {
					modal = new pmc.view.EditAttachments( {
						frame: 'edit-attachments',
						controller: this.controller,
						library: state.get( 'library' ),
						model: this.model
					} );
				}

				return modal;
			},

			/**
			 * Function to use remove model from collection.
			 * In parent function it remove from library collection.
			 * In child(this) function, we remove model from selection.
			 *
			 * @returns {void}
			 */
			removeFromLibrary: function () {
				Attachment.prototype.removeFromLibrary.apply( this, arguments );
				var controller = this.controller,
					state = controller.state( 'gallery-edit' ),
					selection = state.get( 'selection' ),
					id = this.model.get( 'attachment_id' ),	// Id of removed attachments.
					library = controller.state( 'gallery-library' ).get( 'library' ),
					model = media.model.Attachment.get( id );
				selection.remove( this.model );
				//	Fetch original data for attachment from database.
				model.fetch();
				//	Add removed attachment back to Media Library.
				if ( 'undefined' !== typeof     model.get( 'id' ) ) {
					library.add( model );
				}
				//	trigger Bulk remove event
				state.get( 'library' ).trigger( 'bulk:remove', [ model ] );
			},
			/**
			 * Update setings and rerender tinyMCE
			 *
			 * @return {void}
			 */
			updateSetting: function () {
				Attachment.prototype.updateSetting.apply( this, arguments );
				window.PmcGalleryAdminScript.BindMetaBoxCaptionTinyMce();
			},

			/**
			 * Function is used to save changed data in to database.
			 *
			 * @param {string} key Key field which was changed.
			 * @param {string} value Value of that key that was changed.
			 * @returns {void}
			 */
			save: function ( key, value ) {
				var mode = this.controller.content.mode();
				/**
				 * If mode is gallery_edit then only change value of model
				 * But do not save in database.
				 */
				if ( 'gallery_edit' === mode ) {
					this.model.set( key, value );
				} else {
					Attachment.prototype.save.apply( this, arguments );
				}
			}
		} );

		_.extend( window.pmc, pmc );
	} );
} )( jQuery, wp.media );
