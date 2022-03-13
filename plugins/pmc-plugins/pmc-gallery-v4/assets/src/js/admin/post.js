/* global jQuery, pmc_gallery_admin_options, _ */
/* eslint no-magic-numbers: [ "error", { "ignore": [-1,0,1,2] } ]*/
( function ( $, media ) {
	'use strict';
	$( document ).ready( function () {

		var pmc = window.pmc || {},
			l10n = media.view.l10n,
			Post = media.view.MediaFrame.Post;

		/**
		 * Extends wp.media.view.MediaFrame.Post
		 */
		pmc.Post = Post.extend( {
			/**
			 * Initialize pmc.Post
			 *
			 * @returns {void}
			 */
			initialize: function () {
				Post.prototype.initialize.apply( this, arguments );
				this.on( 'content:create:gallery_edit', this.galleryEditContent, this );
				this.on( 'content:deactivate:gallery_edit', this.galleryEditDeactivated, this );
				this.on( 'content:activate', this.galleryAddToolbar, this );	// referesh Toolbar when tab `mode` change
			},

			/**
			 * Creates menu item for side menu.
			 *
			 * @returns {void}
			 */
			galleryMenu: function () {
				//	blank function for remove cancle button and saperator
				//	from side menu
			},

			/**
			 * Render callback for the router region in the `browse` mode.
			 *
			 * @see wp.media.view.MediaFrame.Select
			 * @param {wp.media.view.Router} routerView View of Router UI
			 * @returns {void}
			 */
			browseRouter: function ( routerView ) {
				Post.prototype.browseRouter.apply( this, arguments );
				routerView.set( {
					gallery_edit: {
						text: l10n.editGalleryTitle,
						priority: 50
					}
				} );
			},

			/**
			 * Render callback for the content region in the `browse` mode.
			 *
			 * @param {object} contentRegion Instance of content region.
			 * @return {void}
			 */
			browseContent: function ( contentRegion ) {
				var state = this.state();
				state.set( 'AttachmentView', pmc.view.Attachment );
				this.$el.removeClass( 'hide-toolbar' );
				// Browse our library of attachments.
				contentRegion.view = new wp.media.view.AttachmentsBrowser( {
					controller: this,
					collection: state.get( 'library' ),
					selection: state.get( 'selection' ),
					model: state,
					sidebar: false,
					sortable: state.get( 'sortable' ),
					search: state.get( 'searchable' ),
					filters: state.get( 'filterable' ),
					date: state.get( 'date' ),
					display: state.has( 'display' ) ? state.get( 'display' ) : state.get( 'displaySettings' ),
					dragInfo: state.get( 'dragInfo' ),

					idealColumnWidth: state.get( 'idealColumnWidth' ),
					suggestedWidth: state.get( 'suggestedWidth' ),
					suggestedHeight: state.get( 'suggestedHeight' ),

					AttachmentView: state.get( 'AttachmentView' )
				} );
				this.selectionStatusToolbar( contentRegion.view.toolbar );
			},

			/**
			 * Render callback for the content region in the `gallery_edit` mode.
			 *
			 * @param {wp.media.controller.Region} contentRegion Instance of content region.
			 * @return {void}
			 */
			galleryEditContent: function () {
				var state = this.state( 'gallery-edit' ),
					library = state.get( 'library' ),
					selection = state.get( 'selection' ),
					view;

				this.$el.removeClass( 'hide-toolbar' );
				selection.multiple = 'add';
				this.$el.addClass( 'gallery-edit' );

				view = new wp.media.view.AttachmentsBrowser( {
					controller: this,
					collection: library,
					selection: selection,
					model: state,
					sidebar: false,
					display: true,
					dragInfo: state.get( 'dragInfo' ),
					search: state.get( 'searchable' ),
					filters: state.get( 'filterable' ),
					date: state.get( 'date' ),
					describe: true,
					AttachmentView: pmc.view.Attachment,
					sortable: {
						/**
						 * Callback function of jQuery sortable when sorting is
						 * being start.
						 * Function is used to collect current index of item,
						 * which will be use in update() to replace current
						 * index with new index in collection.
						 *
						 * @param {object} event Event object.
						 * @param {object} ui Object of UI Element which being sorting.
						 * @returns {void}
						 */
						start: function ( event, ui ) {
							ui.item.data( 'sortableIndexStart', ui.item.index() );
						},

						/**
						 * Callback function for helper of jQuery sortable.
						 * If single object is being sorting then do nothing and
						 * return current object as helper.
						 * If Multiple Object is being sorting then create clone
						 * object that contain all selected object for visual.
						 *
						 * @param {object} event Event object.
						 * @param {object} item Item that currentlly being sorting.
						 * @returns {object} cloned object.
						 */
						helper: function ( event, item ) {
							// If it is single item selection then,
							// return itself as helper.
							if ( ! ( selection.length && item.hasClass( 'selected' ) ) ) {
								return item;
							}

							// Clone the selected items into an array.
							var elements = item.parent().children( '.selected' ).clone(),
								// Create the helper object
								helper = $( '<li></li>' );

							// Add a property to `item` called 'multidrag` that contains the.
							// selected items, which can usefull in future.
							item.data( 'multidrag', elements ).siblings( '.selected' ).addClass( 'hidden' );
							// Now the selected items exist in memory, attached to the `item`,

							//	Set height, width to individual item same as
							//	other item, And add class helper to them. which
							//	are being sorted.
							elements.css( {
								width: item.outerWidth(),
								height: item.outerHeight()
							} ).addClass( 'helper' );

							//	Set helper object requvired height, width,
							//	for visual affect.
							helper.css( {
								width: item.outerWidth() * elements.length,
								height: item.outerHeight()
							} );
							//	Set every element to helper object.
							helper.append( elements );

							return helper;
						},

						/**
						 * Callback function when sorting is being stoped.
						 * We are hidding elements in helper() callback.
						 * So when we are done with sorting display those element
						 * which have been hidden.
						 *
						 * @param {object} event Event object.
						 * @param {object} ui UI element object.
						 * @returns {void}
						 */
						stop: function ( event, ui ) {
							ui.item.siblings( '.selected' ).removeClass( 'hidden' );
						},

						/**
						 * Callback Function for update the item that are sorted.
						 *
						 * @param {object} event Event object.
						 * @param {object} ui Object of item that currentlly being sorting
						 * @returns {void}
						 */
						update: function ( event, ui ) {
							var collection = library,
								model = collection.at( ui.item.data( 'sortableIndexStart' ) ),
								comparator = collection.comparator,
								current_index = ui.item.index();

							// Temporarily disable the comparator to prevent `add`
							// from re-sorting.
							delete collection.comparator;
							if ( selection.length && ui.item.hasClass( 'selected' ) ) {
								selection.each( function ( current_model ) {
									collection.remove( current_model );
								} );
								selection.each( function ( current_model, index ) {
									collection.add( current_model, {
										at: current_index + index
									} );
								} );
							} else {
								// Silently shift the model to its new index.
								collection.remove( model, {
									silent: true
								} );
								collection.add( model, {
									silent: true,
									at: ui.item.index()
								} );
							}
							// Restore the comparator.
							collection.comparator = comparator;

							// Fire the `reset` event to ensure other collections sync.
							collection.trigger( 'reset', collection );

							//	Fire the `orderreset` event to sync order of collection in sync
							collection.trigger( 'reset:order', collection );

							// If the collection is sorted by menu order,
							// update the menu order.
							collection.saveMenuOrder();
						}
					}
				} );

				view.toolbar.set( 'selectall', new pmc.view.selectionAll( {
					controller: this,
					state: state,
					priority: -40
				} ) );

				/**
				 * Set selection bar.
				 *
				 * Ref. wp.media.view.MediaFrame.Post.selectionStatusToolbar()
				 */
				view.toolbar.set( 'selection', new wp.media.view.Selection( {
					controller: this,
					collection: state.get( 'selection' ),
					priority: -40,
					// If the selection is editable, pass the callback to
					// switch the content mode.
					editable: state.get( 'editable' ) && function () {
						this.controller.content.mode( 'edit-selection' );
					}
				} ) );

				// Reverse sort button.
				view.toolbar.set( 'reverse', {
					text: l10n.reverseOrder,
					priority: 80,
					click: function () {
						library.reset( library.toArray().reverse() );
						//	Fire the `orderreset` event to sync order of collection in sync
						library.trigger( 'reset:order', library );
					}
				} );

				// Numeric sort button.
				view.toolbar.set( 'sortNumerically', {
					text: l10n.sortNumerically,
					priority: 80,
					click: function () {
						var sorted = library.toArray().sort( function ( a, b ) {
							return a.get( 'attachment_id' ) - b.get( 'attachment_id' );
						} );
						library.reset( sorted );
						//	Fire the `orderreset` event to sync order of collection in sync
						library.trigger( 'reset:order', library );
					}
				} );

				// Alphabetic sort button.
				view.toolbar.set( 'sortAlphabetically', {
					text: l10n.sortAlphabetically,
					priority: 80,
					click: function () {
						var sorted = library.toArray().sort( function ( a, b ) {
							var fileNameA = a.get( 'filename' ).replace( new RegExp( '-', 'g' ), '' ),
								fileNameB = b.get( 'filename' ).replace( new RegExp( '-', 'g' ), '' );
							return fileNameA > fileNameB;
						} );
						library.reset( sorted );
						//	Fire the `orderreset` event to sync order of collection in sync
						library.trigger( 'reset:order', library );
					}
				} );

				// Create Date sort button.
				view.toolbar.set( 'sortCreatedDate', {
					text: l10n.sortCreatedDate,
					priority: 80,
					click: function () {
						var sorted = library.toArray().sort( function ( a, b ) {
							var dateA = a.get( 'attachment_created_timestamp' ),
								dateB = b.get( 'attachment_created_timestamp' );
							// Oldest First.
							return dateA > dateB;
						} );
						library.reset( sorted );
						//	Fire the `orderreset` event to sync order of collection in sync
						library.trigger( 'reset:order', library );
					}
				} );

				// `Edit Metadata` button.
				view.toolbar.set( 'editmetadata', {
					text: l10n.editmetadata,
					priority: -70,
					click: function () {
						if ( 0 < selection.length ) {
							$( 'li.selected .js--select-attachment', view.attachments.$el ).first().trigger( 'dblclick' );
						}
					}
				} );

				//	`Send to Front` Button.
				view.toolbar.set( 'sendToFront', {
					text: 'Send to front',	//	@TODO Need to add text in l10n.
					priority: -60,
					click: function () {
						selection.each( function ( model, index ) {
							library.remove( model );
							library.add( model, { at: index } );
						} );
						//	Fire the `orderreset` event to sync order of collection in sync
						library.trigger( 'reset:order', library );
					}
				} );

				//	Add `Send to back` button in toolbar.
				view.toolbar.set( 'sendToBack', {
					text: 'Send to back',	//	@TODO Need to add text in l10n.
					priority: -60,
					click: function () {
						selection.each( function ( model ) {
							library.remove( model );
							library.add( model );
						} );
						//	Fire the `orderreset` event to sync order of collection in sync
						library.trigger( 'reset:order', library );
					}
				} );

				/**
				 * Add `Delete Selection` Button to remove selected item.
				 */
				view.toolbar.set( 'bulkRemove', {
					text: l10n.remove,
					priority: -70,
					click: function () {
						var removedModels = selection.models;
						selection.each( function ( model ) {
							library.remove( model );
						} );
						selection.reset();
						library.trigger( 'bulk:remove', removedModels );
					}
				} );

				// observe the uploader when edit_gallery queue is activate
				library.observe( wp.Uploader.queue );
				// Browse library of attachments.
				this.content.set( view );
			},

			/**
			 * Function is used to remove all selection from
			 * Edit gallery mode (Tab).
			 *
			 * @returns {void}
			 */
			removeGallerySelection: function () {
				var state = this.state( 'gallery-edit' ),
					selection = state.get( 'selection' );
				selection.reset();
			},

			/**
			 * Callback function for `gallery_edit` deactivation mode.
			 *
			 * @returns {void}
			 */
			galleryEditDeactivated: function () {
				var state = this.state( 'gallery-edit' ),
					library = state.get( 'library' );
				this.$el.removeClass( 'gallery-edit' );
				// remove observer the uploader when edit_gallery queue is activate
				library.unobserve( wp.Uploader.queue );
			},

			/**
			 * Callback function of activation of any mode.
			 * To update the bottom toolbar according to current mode.
			 *
			 * @returns {void}
			 */
			galleryAddToolbar: function () {
				var mode = this.content.mode(),
					text = l10n.addToGallery,
					requires = { selection: true };
				if ( 'gallery_edit' === mode ) {
					text = l10n.updateGallery;
					requires = {};
				}
				this.toolbar.set( new wp.media.view.Toolbar( {
					controller: this,
					items: {
						insert: {
							style: 'primary',
							text: text,
							priority: 80,
							requires: requires,

							/**
							 * Handles the click event for `Add to Gallery` and `Update Gallery` button.
							 * Adds attachment into gallery if mode is `browse`.
							 * Update the gallery if mode is `gallery_edit`.
							 *
							 * @fires wp.media.controller.State#reset
							 * @return {void}
							 */
							click: function () {
								var controller = this.controller,
									state,
									edit,
									models,
									selection,
									collected = [],
									isPrepend;
								if ( 'gallery_edit' === mode ) {
									state = controller.state( 'gallery-edit' );
									state.trigger( 'update', state.get( 'library' ) );
									// Restore and reset the default state.
									controller.setState( controller.options.state );
									controller.reset();
								} else {
									state = controller.state();
									edit = controller.state( 'gallery-edit' );
									selection = state.get( 'selection' );
									isPrepend = typeof pmc_gallery_admin_options !== 'undefined' && 'prepend' === pmc_gallery_admin_options.add_gallery;
									// Loop throught each selection attachment.
									selection.each( function ( model ) {
										/**
										 * Check attachment id whether it is already exist in added list
										 * If yes then prevent it to being re add in gallery.
										 */
										if ( -1 !== _.indexOf( collected, parseInt( model.get( 'attachment_id' ), 0 ) ) ) {
											selection.remove( model );
										} else if ('single_use' === $( '.compat-field-restricted_image_type input:checked', model.attributes.compat.item ).val() &&
												'' !== $( '.compat-field-restricted_image_type input:checked', model.attributes.compat.item ).data( 'singleusepost' ) &&
												$( '#post_ID' ).val() !== $( '.compat-field-restricted_image_type input:checked', model.attributes.compat.item ).data( 'singleusepost' )
											) {
											alert( model.get( 'filename' ) + ' is restricted to SINGLE USE only, it cannot be added to this gallery or post.' );
											selection.remove( model );
										} else {
											/**
											 * If is not and going to add then add it in added list.
											 * So, next time with same attachment won't get add.
											 */
											collected.push( parseInt( model.get( 'attachment_id' ), 0 ) );
										}
									} );
									// This will be final and unique list of model which can be add in gallery.
									models = selection.models;
									if ( isPrepend ) {
										edit.get( 'library' ).add( models, { at: 0 } );
									} else {
										edit.get( 'library' ).add( models );
									}
									state.trigger( 'reset' );
									edit.get( 'library' ).trigger( 'bulk:add', models, isPrepend );
									controller.content.mode( 'gallery_edit' );
								}
							}
						}
					}
				} ) );
			}

		} );
		_.extend( pmc.Post, Post );
		_.extend( window.pmc, pmc );
	} );
} )( jQuery, wp.media );
