var variety500Settings = ( function ( $, _ ) {
	var v500js = window.v500js || {},
		self = {};

	self.init = function () {
		$( document ).ready( function () {

			// For sponser logo.
			var sponsor_logo = new self.imageField().init( $('.sponsor-logo-img'), $('.upload-sponsor-logo'), $('.remove-sponsor-logo'), $('.sponsor-logo') );

			// For Hero section sponser logo.
			var sponsor_hero_logo = new self.imageField().init( $('.sponsor-hero-logo-img'), $('.upload-sponsor-hero-logo'), $('.remove-sponsor-hero-logo'), $('.sponsor-hero-logo') );

			self.sortablePage();
		});

		self.imageField = function( ) {

			this.frame        = false;
			this.imgContainer = false;
			this.uploadButton = false;
			this.removeButton = false;
			this.inputField   = false;

			/**
			 * To initilize new image field.
			 * which is allow to select, remove, and update image.
			 *
			 * @param {Object} imgContainer jQuery Object which have img tag
			 *                              as child to show selected image.
			 * @param {Object} uploadButton jQuery Object of upload button.
			 * @param {Object} removeButton jQuery Object of remove button.
			 * @param {Object} inputField   jQuery Object of input field
			 *                              where url of seleted image will store.
			 *
			 * @returns {void}
			 */
			this.init = function ( imgContainer, uploadButton, removeButton, inputField ) {

				this.imgContainer = imgContainer;
				this.uploadButton = uploadButton;
				this.removeButton = removeButton;
				this.inputField   = inputField;

				this.events();

			},

			/**
			 * To bind event to objects.
			 *
			 * @returns {void}
			 */
			this.events = function () {
				this.uploadButton.on( 'click', this.onClickUploadButton.bind( this ) );
				this.removeButton.on( 'click', this.onClickRemoveButton.bind( this ) );
			},

			/**
			 * Callback function of Upload button.
			 *
			 * @param {Object} event Event data.
			 *
			 * @returns {void}
			 */
			this.onClickUploadButton = function ( event ) {

				event.preventDefault();

				if ( this.frame ) {
					this.frame.open();
					return;
				}

				this.frame = wp.media( {
					title: 'Select or Upload Logo',
					button: {
						text: 'Select Logo'
					},
					multiple: false
				} );

				this.frame.on( 'select', this.onFrameSelect.bind( this ) );

				this.frame.open();
			},

			/**
			 * Callback function when user will select image.
			 *
			 * @returns {void}
			 */
			this.onFrameSelect = function () {
				// Get media attachment details from the frame state.
				var attachment = this.frame.state().get( 'selection' ).first().toJSON(),
					image = $( '<img/>' );

				// Send the attachment URL to our custom image input field.
				image.attr( 'src', attachment.url ).css({
					'max-width': '300px'
				});

				this.imgContainer.append( image );

				// Send the attachment url to our hidden input.
				this.inputField.val( attachment.url );

				// Hide the add image link.
				this.uploadButton.addClass( 'hidden' );

				// Unhide the remove image link.
				this.removeButton.removeClass( 'hidden' );
			},

			/**
			 * Callback function of remove button.
			 *
			 * @param {type} event Event data.
			 *
			 * @returns {void}
			 */
			this.onClickRemoveButton = function ( event ) {

				event.preventDefault();

				// Clear out the preview image.
				this.imgContainer.html( '' );

				// Un-hide the add image link.
				this.uploadButton.removeClass( 'hidden' );

				// Hide the delete image link.
				this.removeButton.addClass( 'hidden' );

				// Delete the image url from the hidden input.
				this.inputField.val( '' );
			}

		},

		/*
		 * Sortable functionality for Profiles and Instagram.
		 */
		self.sortablePage = function () {
			/*
			 * Two inputs on the settings page need Sortable applied to them.
			 * The Profile selection and Instagram section.
			 */
			var sortSettings = [
				{
					'selector'     : $( '#spotlight-profiles' ),
					'input'        : $( '#spotlight-profiles-input' ),
					'autocomplete' : $( '#profile-suggest' ),
					'itemCount'    : 10
				},
				{
					'selector'     : $( '#instagram-profiles' ),
					'input'        : $( '#instagram-profiles-input' ),
					'autocomplete' : $( '#instagram-suggest' ),
					'itemCount'    : 20
				}
			];

			_.each( sortSettings, function( sortSetting ) {
				if ( 0 < sortSetting.selector.length ) {
					self.doSortable( sortSetting.selector, sortSetting.input );
					self.autoComplete( sortSetting.selector, sortSetting.input, sortSetting.autocomplete, sortSetting.itemCount );

					// Modify the input value if an element is removed.
					sortSetting.selector.on( 'click', '.remove', function ( event ) {
						event.preventDefault();
						$( this ).closest( '.element' ).remove();
						sortSetting.selector.sortable( 'refresh' );
						self.updateSortable( sortSetting.selector, sortSetting.input );
					});
				}
			} );
		};

		/*
		 * Initialize the Sortable instance.
		 */
		self.doSortable = function ( sortSelector, sortInput ) {
			sortSelector.sortable({
				axis: 'y',
				handle: '.dashicons-menu',
				placeholder: 'ui-state-highlight',
				forcePlaceholderSize: true,
				update: function () {
					self.updateSortable( sortSelector, sortInput );
				}
			}).disableSelection();
		};

		/*
		 * Update the hidden input field.
		 */
		self.updateSortable = function ( sortSelector, sortInput ) {
			var order = sortSelector.sortable( 'toArray', { attribute: 'data-post-id' } );
			sortInput.val( order.join( ',' ) );
		};

		/*
		 * Autocomplete
		 *
		 * Based on the WP "add existing user" interface.
		 */
		self.autoComplete = function ( sortSelector, sortInput, sortAutocomplete, itemCount ) {
			var hiddenElement = sortSelector.find( '.hidden-element' );

			sortAutocomplete.autocomplete({
				source:    ajaxurl + '?action=v500-profile-query&_ajax_nonce=' + v500js.nonce,
				delay:     500,
				minLength: 2,
				open: function() {
					$( this ).addClass( 'open' );
				},
				close: function() {
					$( this ).removeClass( 'open' );
					$( this ).val( '' );
				},
				select: function( event, ui ) {
					var clone, count = $( '.visible', sortSelector ).length;

					if ( ( 'undefined' === typeof ui.item.id ) || ( 'undefined' === typeof ui.item.label ) ) {
						return false;
					}

					// Limit the number of elements allowed.
					if ( itemCount <= count ) {
						return false;
					}

					// Clone the hidden element and append it to the visible elements.
					clone = hiddenElement.clone();
					sortSelector.append( clone );
					clone.show().removeClass( 'hidden-element' ).addClass( 'visible' );

					// Add data to the element.
					clone.closest( '.element' ).attr( 'data-post-id', ui.item.id );
					$( '.element-title', clone ).html( ui.item.label );

					// Update sortable.
					sortSelector.sortable( 'refresh' );
					self.updateSortable( sortSelector, sortInput );
				}
			});
		};

	};

	return self;

} )( jQuery, _ );

variety500Settings.init();
