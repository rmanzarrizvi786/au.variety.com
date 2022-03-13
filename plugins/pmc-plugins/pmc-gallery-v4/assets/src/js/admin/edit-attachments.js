/* eslint no-magic-numbers: [ "error", { "ignore": [1,39,37] } ]*/
/* eslint complexity: ["error", 6] */
( function ( $, media ) {
	'use strict';
	$( document ).ready( function () {
		var pmc = window.pmc || {},
			EditAttachments = media.view.MediaFrame.EditAttachments;
		pmc.view = pmc.view || {};

		/**
		 * Overwrite the `wp.media.view.MediaFrame.EditAttachments`.
		 * Located in media-grid.js
		 * Must execute after media-grid.js
		 */
		pmc.view.EditAttachments = EditAttachments.extend( {

			/**
			 * Init function for window.pmc.view.EditAttachments
			 *
			 * @returns {void}
			 */
			initialize: function () {
				EditAttachments.prototype.initialize.apply( this, arguments );
			},

			/**
			 * Initialize tinyMCE when edit-attachment modal render.
			 *
			 * @return {void}
			 */
			createModal: function () {
				this.controller.trigger( 'edit-attachments:open', this );
				EditAttachments.prototype.createModal.apply( this, arguments );
				this.attachIndexToHeader();
				this.enableToolTips();
				window.PmcGalleryAdminScript.BindMetaBoxCaptionTinyMce();
				this.modal.on( 'close', _.bind( function () {
					this.modal.$el.remove();
					this.controller.trigger( 'edit-attachments:close', this );
				}, this ) );
			},
			/**
			 * To open image detail modal box.
			 * Removed the url state maintaining part from parent function.
			 *
			 * @param {object} contentRegion Instance of content region.
			 * @returns {void}
			 */
			editMetadataMode: function ( contentRegion ) {
				var mode = this.controller.content.mode();
				/**
				 * If `Edit Gallery` tab is open and click for edit attachment
				 * then open the custom metabox for custom actions.
				 */
				if ( 'gallery_edit' === mode ) {
					contentRegion.view = new pmc.view.AttachmentDetailsTwoColumn( {
						controller: this,
						model: this.model
					} );
				} else {
					contentRegion.view = new media.view.Attachment.Details.TwoColumn( {
						controller: this,
						model: this.model
					} );
				}

				/**
				 * Attach a subview to display fields added via the
				 * `attachment_fields_to_edit` filter.
				 *
				 * If `Edit Gallery` is open or in `Media Library`,
				 * and variant of attachment is being modify then also
				 * open custom `compect` view.
				 *
				 * Note : same thing we are not doing above (^) just because,
				 * `pmc.view.AttachmentDetailsTwoColumn` is used to provide
				 * *bluk edit* and this thing we are not supporting in `Media Library`
				 * because of in `Media library` there will mix of original attachment
				 * as well variant of attachment from gallery.
				 */
				if ( 'gallery_edit' === mode || ! isNaN( parseInt( this.model.get( 'gallery_id' ), 0 ) ) ) {
					contentRegion.view.views.set( '.attachment-compat', new pmc.view.AttachmentCompat( {
						controller: this,
						model: this.model
					} ) );
				} else {
					contentRegion.view.views.set( '.attachment-compat', new media.view.AttachmentCompat( {
						controller: this,
						model: this.model
					} ) );
				}
			},

			/**
			 * Re initialize tinyMce every time move forward-backward in
			 * edit-attachment modal.
			 *
			 * @return {void}
			 */
			rerender: function () {
				EditAttachments.prototype.rerender.apply( this, arguments );
				this.attachIndexToHeader();
				this.enableToolTips();
				window.PmcGalleryAdminScript.BindMetaBoxCaptionTinyMce();
			},

			/**
			 * Function was used to maintain url state for open attachment.
			 * Since, for custom post type, we are not maintaining the url state
			 * Keep it blank.
			 *
			 * @returns {void}
			 */
			resetRoute: function () {
				//	Put the blank function because,
				//	We are not going to reset url here since it is post page.
			},
			attachIndexToHeader: function () {

				var html = $( '<span class="current-attachment-number"></span>' ),
					indexEle = this.$( '.edit-media-header' ).find( '.current-attachment-number' ),
					current_indx = EditAttachments.prototype.getCurrentIndex.apply( this, arguments ) + 1,
					value = current_indx + '/' + this.library.length;

				// Checks if element already exist then update the value, else append the element
				if ( indexEle.length ) {
					$( indexEle ).text( value );
				} else {
					$( html ).text( value );
					$( html ).prependTo( '.edit-media-header' );
				}
			},
			/**
			 * enables tooltips for attachment frame
			 *
			 * @return {void}
			 */
			enableToolTips: function () {
				this.$( '.imgedit-help-toggle' ).tooltip( {
					position: {
						my: 'right bottom-20',
						at: 'right top',
						using: function ( position, feedback ) {
							$( this ).css( position );
							$( '<div>', {
								class: [ 'arrow', feedback.vertical, feedback.horizontal ].join( ' ' ),
							} ).appendTo( this );
						}
					}
				} );
			},
			/**
			 * Respond to the keyboard events: right arrow, left arrow, except when
			 * focus is in a textarea or input field.
			 *
			 * @param {object} event Event object.
			 * @return {void}
			 */
			keyEvent: function ( event ) {
				if ( ( 'INPUT' === event.target.nodeName || 'TEXTAREA' === event.target.nodeName ) && ! ( event.target.readOnly || event.target.disabled ) ) {
					return;
				}

				// The right arrow key
				if ( 39 === event.keyCode ) {
					this.nextMediaItem( event );
				}
				// The left arrow key
				if ( 37 === event.keyCode ) {
					this.previousMediaItem( event );
				}
			},

			/**
			 * Click handler to switch to the previous media item.
			 *
			 * @param {object} event Event object.
			 * @return {void}
			 */
			previousMediaItem: function ( event ) {
				EditAttachments.prototype.previousMediaItem.apply( this, arguments );
				this.library.trigger( 'edit:attachments:move:previous', event, this );
			},

			/**
			 * Click handler to switch to the next media item.
			 *
			 * @param {object} event Event object.
			 * @return {void}
			 */
			nextMediaItem: function ( event ) {
				EditAttachments.prototype.nextMediaItem.apply( this, arguments );
				this.library.trigger( 'edit:attachments:move:next', event, this );
			}
		} );
		pmc.view.BulkEditAttachments = pmc.view.EditAttachments.extend( {

			/**
			 * Init function for window.pmc.view.EditAttachments
			 *
			 * @returns {void}
			 */
			initialize: function () {
				pmc.view.EditAttachments.prototype.initialize.apply( this, arguments );
			},

			/**
			 * Initialize tinyMCE when edit-attachment modal render.
			 *
			 * @return {void}
			 */
			createModal: function () {
				pmc.view.EditAttachments.prototype.createModal.apply( this, arguments );
				this.$el.addClass( 'bulk-edit-frame' );
				this.removeHeader();
			},
			/**
			 * To open image detail modal box.
			 * Removed the url state maintaining part from parent function.
			 *
			 * @param {object} contentRegion Instance of content region.
			 * @returns {void}
			 */
			editMetadataMode: function ( contentRegion ) {
				var mode = this.controller.content.mode(),
					state = this.controller.state( 'gallery-edit' ),
					selection = state.get( 'selection' ),
					selectionIds = [];

				if ( false !== selection.multiple ) {
					selection.models.forEach( function ( m, i ) {
						selectionIds[ i ] = m.id;
					} );
					contentRegion.view = new pmc.view.AttachmentDetailsTwoColumn( {
						controller: this,
						model: this.model,
						bulkEdit: true,
						modelIds: selectionIds
					} );
				}

				/**
				 * Attach a subview to display fields added via the
				 * `attachment_fields_to_edit` filter.
				 *
				 * If `Edit Gallery` is open or in `Media Library`,
				 * and variant of attachment is being modify then also
				 * open custom `compect` view.
				 *
				 * Note : same thing we are not doing above (^) just because,
				 * `pmc.view.AttachmentDetailsTwoColumn` is used to provide
				 * *bluk edit* and this thing we are not supporting in `Media Library`
				 * because of in `Media library` there will mix of original attachment
				 * as well variant of attachment from gallery.
				 */
				if ( 'gallery_edit' === mode || ! isNaN( parseInt( this.model.get( 'gallery_id' ), 0 ) ) ) {

					contentRegion.view.views.set( '.attachment-compat', new pmc.view.AttachmentCompat( {
						controller: this,
						model: this.model
					} ) );
				} else {
					contentRegion.view.views.set( '.attachment-compat', new media.view.AttachmentCompat( {
						controller: this,
						model: this.model
					} ) );
				}

			},

			/**
			 * Re initialize tinyMce every time move forward-backward in
			 * edit-attachment modal.
			 *
			 * @return {void}
			 */
			rerender: function () {
				pmc.view.EditAttachments.prototype.rerender.apply( this, arguments );
				this.removeHeader();
			},

			/**
			 * Function was used to maintain url state for open attachment.
			 * Since, for custom post type, we are not maintaining the url state
			 * Keep it blank.
			 *
			 * @returns {void}
			 */
			resetRoute: function () {
				//	Put the blank function because,
				//	We are not going to reset url here since it is post page.
			},
			isBulkEdit: function () {
				var state = this.controller.state( 'gallery-edit' ),
					selection = state.get( 'selection' );
				if ( false !== selection.multiple ) {
					return true;
				} else {
					return selection.multiple;
				}
			},
			removeHeader: function () {
				if ( this.isBulkEdit() ) {
					this.$( '.edit-media-header' ).remove();
				}
			},
			previousMediaItem: function () {
			},
			nextMediaItem: function () {
			}

		} );
		_.extend( window.pmc, pmc );
	} );
} )( jQuery, wp.media );
