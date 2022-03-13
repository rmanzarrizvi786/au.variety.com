/**
 * Script to handle admin media upload/edit frame events and validation
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @since 2018-08-16 READS-1409
 *
 * To create minified version of pmc-admin-media-validation.min.js:
 *
 * - npm install uglify -g (to install uglify)
 * - uglify -s pmc-admin-media-validation.js -o pmc-admin-media-validation.min.js
 */
( function( $ ) {
	$( document ).ready( function() {
		var adminMediaFrame = {

			/**
			 * Initialization.
			 */
			init: function() {
				var _frame,
					_addMediaFrame,
					_frameId,
					_addMediaFrameId,
					_self = this;
				if ( 'undefined' === typeof wp.media.featuredImage ) {
					return;
				}
				_frame = wp.media.featuredImage.frame();

				_frame.on( 'open', function() {

					// To not show highlight on initial modal open.
					if ( 'undefined' !== typeof _frameId ) {
						_self.validateImageCredit( _frameId );
					}
					_frameId = _frame.$el.attr( 'id' );
					_frameId = 'undefined' !== _frameId ? _frameId : '';
				});

				_frame.on( 'uploader:ready', function() {
					setTimeout( function() {
						_self.validateImageCredit( _frameId );
					}, 5000 );
				});

				_frame.on( 'selection:toggle', function() {
					_self.validateImageCredit( _frameId );
				});

				_frame.on( 'attachment:compat:ready', function() {
					_self.validateImageCredit( _frameId );
				});

				$( '#insert-media-button' ).on( 'click', function() {
					if ( 'undefined' === typeof _addMediaFrame ) {
						setTimeout( function() {

							if ( 'undefined' === wp.media.frame ) {
								return;
							}

							_addMediaFrame = wp.media.frame;
							_addMediaFrameId = _addMediaFrame.$el.attr( 'id' );
							_addMediaFrameId = 'undefined' !== _addMediaFrameId ? _addMediaFrameId : '';

							_addMediaFrame.on( 'selection:toggle', function() {
								_self.validateImageCredit( _addMediaFrameId );
							});

							_addMediaFrame.on( 'attachment:compat:ready', function() {
								_self.validateImageCredit( _addMediaFrameId );
							});

						}, 1000 );
					}
				});

			},

			/**
			 * Validate Image Credit filed on media attachment edit page.
			 * If image credit field is empty then it will highlight it with red border.
			 * Marked as a required field so will show alert if not filled.
			 *
			 * @param frameId current frame id.
			 */
			validateImageCredit: function( frameId ) {

				var imageCreditField,
					requiredText,
					imageCreditValue;

				if ( 'undefined' === typeof frameId || 0 >= frameId.length ) {
					return;
				}

				imageCreditField = $( '#' + frameId + ' form.compat-item .compat-field-image_credit input' );

				if ( 'undefined' === typeof imageCreditField || 0 >= imageCreditField.length ) {
					return;
				}

				requiredText = $( '#' + frameId + ' .attachment-details .required' );
				imageCreditValue = imageCreditField.val();

				if ( 'undefined' === typeof requiredText || 0 >= requiredText.length ) {

					requiredText = $( '<span>', {
						'class': 'required',
						'text': 'Please fill in the required fields.'
					});

					$( '#' + frameId + ' .attachment-details > h2' ).after( requiredText );
				}

				imageCreditValue = ( 'undefined' !== typeof imageCreditValue ) ? imageCreditValue : '';
				imageCreditField.removeClass( 'error' );

				if ( 'undefined' !== typeof imageCreditValue && 0 >= imageCreditValue.length ) {
					imageCreditField.addClass( 'error' );
					requiredText.show();
				}

				$( imageCreditField ).on( 'blur', function() {
					imageCreditValue = $( this ).val();
					if ( 'undefined' !== typeof imageCreditValue && 0 >= imageCreditValue.length ) {
						imageCreditField.addClass( 'error' );
						requiredText.show();
					} else {
						imageCreditField.removeClass( 'error' );
						requiredText.hide();
					}
				});
			}
		};

		adminMediaFrame.init();
	});
}( jQuery ) );
