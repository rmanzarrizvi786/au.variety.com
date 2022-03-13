/* global tb_remove, pmc_gallery_admin_options */
/* eslint no-magic-numbers: [ "error", { "ignore": [-1, 0,1,2] } ]*/
/* eslint complexity: ["error", 7] */
( function ( $ ) {
	'use strict';
	$( document ).ready( function () {
		// workaround wp 4.0 bug where set feature image window cannot close on gallery admin page
		$( this ).on( 'click', '#TB_closeWindowButton', function () {
			if ( typeof tb_remove === 'function' ) {
				tb_remove();
			}
		} );

		wp.media.pmc_gallery = {
			editGalleryLibreryCache: {},
			frame: function () {
				var self = this,
					editLibrary,
					mediaLibrary;
				if ( this._frame ) {
					return this._frame;
				}

				/**
				 * Create new instance of `window.pmc.Post()`
				 * to open gallery editor.
				 */
				this._frame = new window.pmc.Post( {
					id: 'pmc-gallery',
					frame: 'post',
					state: 'gallery-library',
					title: wp.media.view.l10n.editGalleryTitle,
					editing: true,
					multiple: true,
					selection: this.select()
				} );
				editLibrary = this._frame.state( 'gallery-edit' ).get( 'library' );
				mediaLibrary = this._frame.state( 'gallery-library' ).get( 'library' );

				this._frame.on( 'open', function () {
					self.Analytic = new window.pmc.GoogleAnalyticEvents( {
						el: '#pmc-gallery',
						mediaFrame: self._frame
					} );
				} );

				this._frame.on( 'edit-attachments:open', function ( _this ) {
					// Validate image credit on initial Modal open.
					setTimeout( function () {
						self.validateImageCredit( _this.model );
						self.validateImageSourceUrl( _this.model );
						self.validateImageRestriction( _this.model );
					}, 500 ); // eslint-disable-line
				} );

				this._frame.on( 'update', function () {
					wp.media.pmc_gallery.save();
				} );
				editLibrary.on( 'bulk:add', function ( models, isPrepend ) {
					self.bulkAddAttachment( models, isPrepend );
				} );
				editLibrary.on( 'bulk:remove', function ( models ) {
					self.bulkRemoveAttachment( models );
				} );
				editLibrary.on( 'reset:order', function ( models ) {
					self.reorderAttachment( models );
				} );
				this._frame.on( 'edit-attachments:close', this.edit_attachment_close, this );

				editLibrary.on( 'change', this.onChangeAttachment );
				mediaLibrary.on( 'change', this.onChangeAttachmentFromMediaLibrary );
				mediaLibrary.on( 'remove', this.onRemoveAttachmentFromMediaLibrary );

				/**
				 * To handle the Issue :
				 * If a link tooltip is showing when you advance to the next
				 * image to Edit using the arrows in the top right corner,
				 * the tooltip will not disappear.
				 */
				mediaLibrary.on( 'edit:attachments:move:next edit:attachments:move:previous', function ( event, _this ) {
					$( '.mce-toolbar-grp' ).css( 'display', 'none' );

					// Validate image credit on media library image change.
					// Set Image credit in model attributes.
					if ( 'undefined' !== typeof _this.model.attributes.compat.item ) {
						_this.model.set( 'image_credit', $( '.compat-field-image_credit input', _this.model.attributes.compat.item ).val() );
						_this.model.set( 'image_source_url', $( '.compat-field-image_source_url input', _this.model.attributes.compat.item ).val() );
						_this.model.set( 'image_restriction', $( '.compat-field-restricted_image_type input:checked', _this.model.attributes.compat.item ).val() );
						self.validateImageCredit( _this.model );
						self.validateImageSourceUrl( _this.model );
						self.validateImageRestriction( _this.model );
					}
				} );
				editLibrary.on( 'edit:attachments:move:next edit:attachments:move:previous', function ( event, _this ) {
					$( '.mce-toolbar-grp' ).css( 'display', 'none' );
					// Validate image credit on edit library image change.
					self.validateImageCredit( _this.model );
					self.validateImageSourceUrl( _this.model );
					self.validateImageRestriction( _this.model );
				} );

				this._frame.on( 'content:activate', function () {
					var mode = self._frame.content.mode();
					$( '#pmc-gallery .media-frame-content .media-toolbar-secondary .media-selection' ).prependTo( '#pmc-gallery > .media-frame-toolbar .media-toolbar-secondary' );
					/**
					 * When tab change save `Gallery Edit` tab data.
					 * Data which is modified by user.
					 * It is already saved in database but to maintaine state in
					 * front end we need maintain content of `Gallery Edit`.
					 */
					if ( 'gallery_edit' !== mode ) {
						self.editGalleryLibreryCache = self._frame.state( 'gallery-edit' ).get( 'library' ).toJSON();
					}
				} );

				return this._frame;
			},
			/**
			 * Trigger when edit attachment modal close.
			 * it will remove selection when edit attachment close if user on
			 * edit gallery tab.
			 *
			 * @ticket  CDWE-265
			 * @returns {void}
			 */
			edit_attachment_close: function () {
				var mode = this._frame.content.mode(),
					state = this._frame.state( 'gallery-edit' ),
					selection = state.get( 'selection' );
				if ( 'gallery_edit' === mode ) {
					selection.reset();
				}
			},
			bulkAddAttachment: function ( models, is_prepend ) {
				var request = {
						sub_action: 'add',
						is_prepend: is_prepend ? 1 : 0
					},
					model,
					data = {},
					i;
				for ( i in models ) {
					models[ i ].set( 'gallery_id', wp.media.view.settings.post.id, { silent: true } );
					if ( 'undefined' !== typeof models[ i ].attributes.compat.item ) {
						models[ i ].set( 'image_credit', $( '.compat-field-image_credit input', models[ i ].attributes.compat.item ).val(), { silent: true } );
						models[ i ].set( 'image_source_url', $( '.compat-field-image_source_url input', models[ i ].attributes.compat.item ).val(), { silent: true } );
						models[ i ].set( 'image_restriction', $( '.compat-field-restricted_image_type input:checked', models[ i ].attributes.compat.item ).val(), { silent: true } );
					}
					model = models[ i ].toJSON();
					data[ i ] = {
						id: model.attachment_id,
						author: model.author,
						title: model.title,
						description: model.description,
						caption: model.caption,
						alt: model.alt,
						pinterest_description: model.pinterest_description || '',
						image_credit: '',
						image_source_url:''
					};
				}
				request.data = data;
				this._save( request );
			},
			bulkRemoveAttachment: function ( models ) {
				var ids = {},
					request = {
						sub_action: 'remove'
					},
					i;
				for ( i in models ) {
					ids[ i ] = models[ i ].get( 'attachment_id' );
				}
				request.ids = ids;
				this._save( request, this.addAttachmentVariantInMedia );
			},
			reorderAttachment: function ( models ) {
				var request = {
					sub_action: 'reorder',
					ids: models.pluck( 'attachment_id' )
				};
				this._save( request );
			},
			onChangeAttachment: function ( model ) {
				var _this = wp.media.pmc_gallery,
					controller = wp.media.pmc_gallery._frame.states.get( 'gallery-edit' ),
					library,
					libraryCache = _this.editGalleryLibreryCache,
					selection = controller.get( 'selection' ),
					isPrepend = typeof pmc_gallery_admin_options !== 'undefined' && 'prepend' === pmc_gallery_admin_options.add_gallery,
					ids = 1 < selection.length ? selection.pluck( 'attachment_id' ) : [ model.get( 'attachment_id' ) ],
					cache = {},
					key,
					id,
					request,
					mode = _this._frame.content.mode(),
					edit_model = $( '.attachment-details .settings-save-status' ),
					spinner = $( '.spinner', edit_model ),
					saved_text = $( '.saved', edit_model );
				// If File is uploading then return.
				if ( 'undefined' !== typeof model.get( 'uploading' ) && true === model.get( 'uploading' ) ) {
					return false;
				}
				/**
				 * Restore local cache when content of `Media Library` load
				 * and modify the Gallery attachment's data.
				 *
				 * CASE : When page load and user is in `Gallery Edit` tab.
				 * then after he/she go to `Media Library` tab and load other
				 * attachment in media library.
				 * In case it load attachment which is in Gallery, It change
				 * data of attachment (but it won't show in Media Library because it is Gallery tab).
				 * For that we make cache copy when user come to `Media Library`
				 * (see this.frame() `content:activate` event)
				 * and restore here.
				 * we will change data silently because data is already stored in database.
				 */
				if ( 'undefined' !== typeof model.changed.id || 'gallery_edit' !== mode ) {
					library = _this._frame.state( 'gallery-edit' ).get( 'library' );
					for ( key in libraryCache ) {
						id = libraryCache[ key ].id;
						cache[ id ] = {
							title: libraryCache[ key ].title,
							description: libraryCache[ key ].description,
							caption: libraryCache[ key ].caption,
							alt: libraryCache[ key ].alt,
							pinterest_description: libraryCache[ key ].pinterest_description || '',
							image_credit: libraryCache[ key ].image_credit,
							image_source_url: libraryCache[ key ].image_source_url,
							image_restriction: libraryCache[ key ].image_restriction || 'none'
						};
					}
					library.each( function ( gallery_model ) {
						id = gallery_model.get( 'id' );
						for ( key in cache[ id ] ) {
							gallery_model.set( key, cache[ id ][ key ], { silent: true } );
						}
					} );
					// If File modified and not previously added then, it should be add in gallery.
					if ( -1 === _.indexOf( _.pluck( libraryCache, 'attachment_id' ), parseInt( model.get( 'attachment_id' ), 0 ) ) ) {
						library.trigger( 'bulk:add', [ model ], isPrepend );
					}
					return false;
				}

				model = _this.updateImageRestriction( model );

				_this.validateImageCredit( model );
				_this.validateImageSourceUrl( model );
				_this.validateImageRestriction( model );

				request = {
					sub_action: 'edit',
					ids: ids,
					changes: model.changed
				};
				spinner.css( {
					visibility: 'visible'
				} );
				_this._save( request, function () {
					spinner.css( {
						visibility: 'hidden'
					} );
					saved_text.show();
					setTimeout( function () {
						saved_text.hide();
					}, 2000 );
				} );
				return true;
			},

			/**
			 * Function is used when attachment is deleted from gallery.
			 * It will added variant from other gallery in Media Library.
			 *
			 * @param {Object} data Response data of remove attachment.
			 * @returns {Void} void
			 */
			addAttachmentVariantInMedia: function ( data ) {
				var _this = wp.media.pmc_gallery,
					state = _this._frame.state( 'gallery-library' ),
					library = state.get( 'library' ),
					index,
					model;

				for ( index in data ) {
					model = new Backbone.Model( data[ index ] );
					library.add( model );
				}
				state.reset();
			},

			/**
			 * When and attachment (it's model) is removed from media library
			 * mean when it added to Gallery Edit tab then,
			 * Remove itself along with all veriant of that perticuler attachment
			 * from media library.
			 *
			 * @param {Object} model Current model which is removed from media library
			 * @returns {Void} void
			 */
			onRemoveAttachmentFromMediaLibrary: function ( model ) {
				var _this = wp.media.pmc_gallery,
					state = _this._frame.state( 'gallery-library' ),
					library = state.get( 'library' ),
					attachment_id = parseInt( model.get( 'attachment_id' ), 0 );
				library.each( function ( current_model ) {
					if ( 'undefined' !== typeof current_model ) {
						if ( parseInt( current_model.get( 'attachment_id' ), 0 ) === attachment_id ) {
							library.remove( current_model, { silent: true } );
						}
					}
				} );
			},
			/**
			 * Callback function when any attachment's data change by
			 * double clicking of attachment from Media Library tab.
			 * This modify request data that is will being send to save attachment
			 * For gallery it add extra param `gallery_id` in request.
			 * So, backend part can identify that for this request it need to
			 * update in that perticuler gallery no in original attachment.
			 *
			 * @param {Object} model Current model that was changed.
			 * @param {Object} event Event there's data object will containe request data
			 * @returns {Void} void
			 */
			onChangeAttachmentFromMediaLibrary: function ( model, event ) {
				var gallery_id = model.get( 'gallery_id' ),
					_this = wp.media.pmc_gallery;
				if ( gallery_id ) {
					event.data = _.extend( event.data || {}, {
						attachment_id: model.get( 'attachment_id' ),
						gallery_id: model.get( 'gallery_id' )
					} );
				} else {
					event.data = _.extend( event.data || {}, {
						attachment_id: model.get( 'attachment_id' )
					} );
				}

				// Validate image credit on media library image change.
				// Set Image credit in model attributes.
				if ( 'undefined' !== typeof model.attributes.compat && 'undefined' !== typeof model.attributes.compat.item ) {
					model.set( 'image_credit', $( '.compat-field-image_credit input', model.attributes.compat.item ).val() );
					model.set( 'image_source_url', $( '.compat-field-image_source_url input', model.attributes.compat.item ).val() );
					model.set( 'image_restriction', $( '.compat-field-restricted_image_type input:checked', model.attributes.compat.item ).val() );
					_this.validateImageCredit( model );
					_this.validateImageSourceUrl( model );
					_this.validateImageRestriction( model );
				}
			},
			/**
			 * It will remove all attachment or there veriant (model) from
			 * Media Library tab which is already in Gallery Tab.
			 *
			 * @returns {Void} void
			 */
			removeGalleryFromLibrary: function () {
				var state = this._frame.state( 'gallery-edit' ),
					library = state.get( 'library' ),
					mediaLibrary = this._frame.state( 'gallery-library' ).get( 'library' ),
					attachment_ids = library.pluck( 'attachment_id' );
				mediaLibrary.each( function ( current_model ) {
					if ( 'undefined' !== typeof current_model ) {
						if ( -1 !== _.indexOf( attachment_ids, current_model.get( 'attachment_id' ) ) ) {
							mediaLibrary.remove( current_model, { silent: true } );
						}
					}
				} );
			},
			save: function () {
				var controller = wp.media.pmc_gallery._frame.states.get( 'gallery-edit' ),
					library = controller.get( 'library' ),
					ids = library.pluck( 'attachment_id' ),
					gallery_attachments = library.toJSON(),
					data = {},
					request,
					i;
				for ( i in gallery_attachments ) {
					data[ i ] = {
						id: gallery_attachments[ i ].attachment_id,
						title: gallery_attachments[ i ].title,
						description: gallery_attachments[ i ].description,
						caption: gallery_attachments[ i ].caption,
						alt: gallery_attachments[ i ].alt,
						pinterest_description: gallery_attachments[ i ].pinterest_description || '',
						image_credit: gallery_attachments[ i ].image_credit,
						image_source_url: gallery_attachments[ i ].image_source_url
					};
				}
				request = {
					sub_action: 'update',
					data: data,
					ids: ids
				};
				this._save( request );
			},
			_save: function ( request, callback ) {
				var spinner = $( '.media-modal .spinner' ),
					data = _.extend( request || {}, {
						nonce: wp.media.view.settings.post.nonce,
						html: wp.media.pmc_gallery.link,
						post_id: wp.media.view.settings.post.id,
						security: pmc_gallery_admin_options.pmc_gallery_update
					} );
				spinner.css( 'visibility', 'visible' );
				//Send ID's to hidden field
				wp.media.post( 'pmc_gallery_update', data ).done( function ( response ) {
					spinner.css( 'visibility', 'hidden' );
					if ( 'function' === typeof callback ) {
						callback( response );
					}
				} ).fail( function () {
					spinner.css( 'visibility', 'hidden' );
				} );
			},
			createToolBar: function ( browser ) {

				//not render this button when on media library tab
				if ( browser.options.search ) {
					return;
				}

				wp.media.pmc_gallery.renderSortButton( browser, 'sortNumerically', wp.media.view.l10n.sortNumerically, 1 );
				wp.media.pmc_gallery.renderSortButton( browser, 'sortAlphabetically', wp.media.view.l10n.sortAlphabetically, 2 );

			},
			init: function () {
				var title = $( '#title' );
				//create html
				this.frame().open();
				//shift html
				$( '#__wp-uploader-id-1' ).prependTo( '#pmc-gallery-images' );
				$( '#pmc-gallery .media-frame-content .media-toolbar-secondary .media-selection' ).prependTo( '#pmc-gallery > .media-frame-toolbar .media-toolbar-secondary' );
				$( 'body' ).removeClass( 'modal-open' );

				// Make 'Media Library' tab selected by default.
				this._frame.content.mode( 'browse' );

				// PPT-3824 Bring Scroll to top of page as after opening media frame the scroll goes down.
				// Also set focus to title
				$( 'html, body' ).animate( { scrollTop: 0 }, 'fast' );
				if ( title.val().length === 0 ) {
					title.focus();
				}

			},
			select: function () {
				var self = this,
					shortcode,
					post_id,
					attachments,
					selection,
					request = {
						sub_action: 'get',
						nonce: wp.media.view.settings.post.nonce,
						post_id: wp.media.view.settings.post.id,
						security: pmc_gallery_admin_options.pmc_gallery_update
					};
				try {
					post_id = wp.media.gallery.defaults.id;
					shortcode = wp.shortcode.next( 'gallery', wp.media.view.settings.pmc_gallery.shortcode );
				} catch ( e ) {
					shortcode = false;
				}

				if ( ! shortcode ) {
					return false;
				}

				shortcode = shortcode.shortcode;

				if ( _.isUndefined( shortcode.get( 'id' ) ) && ! _.isUndefined( post_id ) ) {
					shortcode.set( 'id', post_id );
				}

				attachments = wp.media.gallery.attachments( shortcode );
				selection = new wp.media.model.Selection( attachments.models, {
					props: attachments.props.toJSON(),
					multiple: true
				} );

				selection.gallery = attachments.gallery;
				selection.more().done( function () {
					selection.props.set( { query: false } );
					selection.unmirror();
					selection.props.unset( 'orderby' );
					selection.on( 'add', self.addAttachment );

					/**
					 * If attachment available in edit gallery,
					 * make 'Edit Gallery' tab selected.
					 */
					if ( 0 < selection.length ) {
						self._frame.content.mode( 'gallery_edit' );
					}

					wp.media.post( 'pmc_gallery_update', request ).done( function ( response ) {
						var response_clone = {},
							index,
							key,
							id,
							selection_json = selection.toJSON();
						if ( 0 === response.length ) {
							// If no response found, need to migrate data.
							self.save();
							/**
							 * On time of data migration,
							 * we still need to handle image_credit manually,
							 */
							for ( index in selection_json ) {
								selection.models[ index ].set( 'image_credit', $( '.compat-field-image_credit input', selection.models[ index ].attributes.compat.item ).val(), { silent: true } );
								selection.models[ index ].set( 'image_source_url', $( '.compat-field-image_source_url input', selection.models[ index ].attributes.compat.item ).val(), { silent: true } );
								selection.models[ index ].set( 'image_restriction', $( '.compat-field-restricted_image_type input:checked', selection.models[ index ].attributes.compat.item ).val(), { silent: true } );
							}
							return false;
						}
						for ( index in response ) {
							response_clone[ response[ index ].id ] = response[ index ];
						}
						//	Unbind the change event from collection.
						selection.off( 'change', self.onChangeAttachment );
						for ( index in selection_json ) {
							id = selection_json[ index ].id;
							for ( key in response_clone[ id ] ) {
								//	Update Collection according to custome gallery data.
								selection.models[ index ].set( key, response_clone[ id ][ key ], { silent: true } );
							}
						}
						//	Bind the change event from collection.
						selection.on( 'change', self.onChangeAttachment );
					} ).fail( function () {
					} );
				} );
				return selection;
			},

			/**
			 *  Validate Image Credit filed on media attachment edit page.
			 * If image credit field is empty then it will highlight it with red border.
			 * Marked as a required field so will show alert if not filled.
			 *
			 * @param {object} model current selected attachment model object.
			 *
			 * @returns {void} return void
			 */
			validateImageCredit: function ( model ) {
				var imageCreditField = $( 'form.compat-item .compat-field-image_credit input' ),
					editModel = $( '.attachment-details .settings-save-status' ),
					requiredText = $( '.required', editModel ),
					imageCreditValue;

				if ( 'undefined' === typeof imageCreditField || 0 >= imageCreditField.length || 'undefined' === typeof model ) {
					return;
				}

				imageCreditValue = model.get( 'image_credit' );
				imageCreditValue = 'undefined' !== typeof imageCreditValue ? imageCreditValue : '';
				imageCreditField.removeClass( 'error' );

				if ( 0 >= imageCreditValue.length ) {
					imageCreditField.addClass( 'error' );
					requiredText.show();
				} else {
					imageCreditField.removeClass( 'error' );
					requiredText.hide();
				}
			},

			/**
			 *  Validate Image Source Url field on media attachment edit page.
			 * If image credit field is empty then it will highlight it with red border.
			 * Marked as a required field so will show alert if not filled.
			 *
			 * @param {object} model current selected attachment model object.
			 *
			 * @returns {void} return void
			 */
			validateImageSourceUrl: function ( model ) {
				var imageCreditField = $( 'form.compat-item .compat-field-image_source_url input' );

				model.get( 'image_source_url' );

				if ( 'undefined' === typeof imageCreditField || 0 >= imageCreditField.length || 'undefined' === typeof model ) {
					return;
				}
			},

			/**
			 * Validate Image restricted filed on media attachment edit page.
			 * If image restricted field is set to 'single_use' OR 'site_restricted' then it will display warning massages accordingly.
			 *
			 * @param {object} model current selected attachment model object.
			 *
			 * @returns {void} return void
			 */
			validateImageRestriction: function ( model ) {
				var imageRestricted = $( 'form.compat-item .compat-field-restricted_image_type input' ),
					editModel = $( '.attachment-details .attachment-info' ),
					restrictedNoticeSingleUse = $( '.restricted-single-use-notice', editModel ),
					restrictedNoticeSiteWise = $( '.site-restricted-notice', editModel ),
					imageRestrictedUse;

				if ( 'undefined' === typeof imageRestricted || 'undefined' === typeof model ) {
					return;
				}

				imageRestrictedUse = imageRestricted.filter( ':checked' ).val();

				//check the value from modal as html markup is cached and not updated.
				imageRestrictedUse = model.changed.image_restriction || imageRestrictedUse;

				if ( 'single_use' === imageRestrictedUse ) {
					restrictedNoticeSingleUse.show();
					imageRestricted[2].checked = true;
				} else {
					restrictedNoticeSingleUse.hide();
				}

				if ( 'site_restricted' === imageRestrictedUse ) {
					restrictedNoticeSiteWise.show();
					imageRestricted[1].checked = true;
				} else {
					restrictedNoticeSiteWise.hide();
				}

				if ( 'none' === imageRestrictedUse ) {
					imageRestricted[0].checked = true;
				}

				// add event handler for radio button checked change event
				$( imageRestricted ).on( 'change', function () {

					var imageRestrictedTypeVal = $( this ).val();

					// show-hide warning based on selected item
					if ( 'undefined' !== typeof imageRestrictedTypeVal && 'single_use' === imageRestrictedTypeVal ) {
						restrictedNoticeSingleUse.show();
					} else {
						restrictedNoticeSingleUse.hide();
					}

					// show-hide warning based on selected item
					if ( 'undefined' !== typeof imageRestrictedTypeVal && 'site_restricted' === imageRestrictedTypeVal ) {
						restrictedNoticeSiteWise.show();
					} else {
						restrictedNoticeSiteWise.hide();
					}
				} );
			},

			/**
			 * Update Image restriction type as single use if the image credit is Associated Press
			 */
			updateImageRestriction: function ( model ) {

				var regex = /\bap\b|\bassociated press\b/i;

				if ( ( regex.exec(model.attributes.image_credit ) ) !== null ) {
					$( '.compat-field-restricted_image_type input' )[2].checked = true;
					model.changed.image_restriction = 'single_use';
				}

				return model;
			}
		};
		wp.media.pmc_gallery.init();

		// Get the instance of `Featured image popup box`.
		var featuredImageModal = wp.media.featuredImage.frame();

		// Bind its open event.
		featuredImageModal.on( 'open', function () {
			var mode = wp.media.pmc_gallery._frame.content.mode(),
				editLibrary = wp.media.pmc_gallery._frame.state( 'gallery-edit' ).get( 'library' );
			/**
			 * If currently `Edit gallery` tab is open
			 * then do not observe for new uploaded file for Edit gallery.
			 * Because that will cause when upload image for feature image
			 * it will added in gallery.
			 */
			if ( 'gallery_edit' === mode ) {
				featuredImageModal.content.mode( 'browse' );
				editLibrary.unobserve( wp.Uploader.queue );
			}
		} );
		featuredImageModal.on( 'close', function () {
			var mode = wp.media.pmc_gallery._frame.content.mode(),
				editLibrary = wp.media.pmc_gallery._frame.state( 'gallery-edit' ).get( 'library' );
			/**
			 * If currently `Edit gallery` tab is open
			 * and close featured image box.
			 * then reset `wp.Uploader.queue` to gallery list.
			 */
			if ( 'gallery_edit' === mode ) {
				editLibrary.observe( wp.Uploader.queue );
			}
		} );
	} );
} )( jQuery );
