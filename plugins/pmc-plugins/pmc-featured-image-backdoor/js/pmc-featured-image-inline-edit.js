(
	function ( $ ) {
		// global variable. nonce generated here is used in multipost-thumbnail-inline-edit.js
		pmc_featured_image_inline_edit = {

			init: function () {

				// add events
				$( document.getElementById( 'the-list' ) ).on( 'click', '.editinline', function ( e ) {
					e.preventDefault();
					pmc_featured_image_inline_edit.choose( this );
				} );
			},

			maybeDisableInsertButton: function () {
				wp.media.view.AttachmentCompat.prototype.on( 'ready', function ( e ) {

					var insertMediaButton = $( '#__wp-uploader-id-0 .media-toolbar button.media-button' );
					var imageRestrictedType = $( '#__wp-uploader-id-0  form.compat-item .compat-field-restricted_image_type input' );
					var singleUsePost = imageRestrictedType.filter( ':checked' ).data( 'singleusepost' );
					if ( singleUsePost ) {
						insertMediaButton.hide();
					} else {
						insertMediaButton.show();
					}
				});
			},

			choose: function ( id ) {
				var t = pmc_featured_image_inline_edit;

				if ( typeof id === 'object' ) {
					t.currentId = t.getId( id );
				}

				t.currentThumb = $( document.getElementById( t.currentId + '-featured-image' ) );

				// If the media frame doesn't exist, create it.
				if ( ! t.file_frame ) {

					// Create the media frame.
					t.backDoorFrame = wp.media.frames.backDoorFrame = wp.media( {
						title: 'Set Featured Image',
						button: {
							text: 'Set Featured Image'
						},
						multiple: false  // Set to true to allow multiple files to be selected
					} );

					// When we open the modal, make sure the correct image is selected
					t.backDoorFrame.on( 'open', t.selectCorrectThumb );

					// When an image is selected, run a callback.
					t.backDoorFrame.on( 'select', t.handleSelection );
				}

				t.file_frame =  true; //Not to load multiple media windows.
				pmc_featured_image_inline_edit.maybeDisableInsertButton();

				// Finally, open the modal
				t.backDoorFrame.open();
			},

			selectCorrectThumb: function () {
				var t = pmc_featured_image_inline_edit;
				var attachment, selection;

				t.currentAttachmentId = t.currentThumb.data( 'image-id' );
				if ( 0 < t.currentAttachmentId ) {
					selection = t.backDoorFrame.state().get( 'selection' );
					attachment = wp.media.attachment( t.currentAttachmentId );
					attachment.fetch();
					selection.add( attachment );
				}
			},

			handleSelection: function () {
				var t = pmc_featured_image_inline_edit;
				var attachment, post_id, params;

				// We set multiple to false so only get one image from the uploader
				attachment = t.backDoorFrame.state().get( 'selection' ).first().toJSON();

				t.currentAttachmentId = attachment.id;
				params = {
					action: 'pmc-featured-image-inline-save',
					backdoor_nonce: pmc_featured_image_inline_edit_l10n.nonce,
					attachment_id: t.currentAttachmentId,
					post_id: t.currentId
				};

				t.currentThumb.animate( {'opacity': '0.5'} );


				// make ajax request
				$.post( ajaxurl, params, t.handleResponse );

				return false;
			},

			handleResponse: function ( r ) {
				var t = pmc_featured_image_inline_edit;

				if ( false === r.error ) {
					t.currentThumb.animate( {'opacity': '0'}, 200, function () {
						t.currentThumb.html( r.markup );
						t.currentThumb.data( 'image-id', t.currentAttachmentId );
						t.currentThumb.animate( {'opacity': '1'} );
					} );
				} else {
					// @TODO custom popup
					alert( r.message );
					t.currentThumb.animate( {'opacity': '1'} );
				}
			},

			getId: function ( o ) {
				var id = $( o ).closest( 'tr' ).attr( 'id' ),
					parts = id.split( '-' );
				return parts[parts.length - 1];
			}
		};

		// Show/hide locks on featured image backdoors
		$( document ).on( 'heartbeat-tick.wp-check-locked-featured-image-backdoor', function ( e, data ) {
			var locked = data['wp-check-locked-posts'] || {};

			$( '#the-list tr' ).each( function ( i, el ) {
				var key = el.id, row = $( el ), lock_data, avatar;

				if ( locked.hasOwnProperty( key ) ) {
					if ( ! row.hasClass( 'wp-locked' ) ) {
						lock_data = locked[key];
						row.find( '.column-title .locked-text' ).text( lock_data.text );
						row.find( '.check-column checkbox' ).prop( 'checked', false );

						if ( lock_data.avatar_src ) {
							avatar = $( '<img class="avatar avatar-18 photo" width="18" height="18" alt="" />' ).attr( 'src', lock_data.avatar_src.replace( /&amp;/g, '&' ) );
							row.find( '.column-title .locked-avatar' ).empty().append( avatar );
						}
						row.addClass( 'wp-locked' );
					}
				} else if ( row.hasClass( 'wp-locked' ) ) {
					// Make room for the CSS animation
					row.removeClass( 'wp-locked' ).delay( 1000 ).find( '.locked-info span' ).empty();
				}
			} );

		} ).on( 'heartbeat-send.wp-check-locked-featured-image-backdoor', function ( e, data ) {
			var check = [];

			$( '#the-list tr' ).each( function ( i, el ) {
				if ( el.id ) {
					check.push( el.id );
				}
			} );

			if ( check.length ) {
				data['wp-check-locked-posts'] = check;
			}
		} ).ready( function () {
			// Set the heartbeat interval to 15 sec.
			if ( typeof wp !== 'undefined' && wp.heartbeat ) {
				wp.heartbeat.interval( 15 );
			}
		} );

		// Start the party
		pmc_featured_image_inline_edit.init();
		try {
			if ( 'undefined' !== typeof pmc_multipost_thumbnail_image_inline_edit ) {
				pmc_multipost_thumbnail_image_inline_edit.init();
			}
		} catch ( e ) {
		}
	}
)( jQuery );

// EOF
