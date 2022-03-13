/**
 * Disable selecting getty images from adding to post content or set as featured image.
 * Script look for image credits data to determine if the image is getty image or not.
 */

/*global jQuery, wp*/
/*eslint no-undef: "error"*/
jQuery( document ).ready( function( $ ) {

	if ( wp && wp.media ) {

		wp.media.view.Modal.prototype.on( 'open', function() {
			$( document ).on( 'click', 'li.attachment.save-ready', function() {
				var is_checked = $( this ).attr( 'aria-checked' ),
					image_id = $( this ).data( 'id' ),
					credits = '';

				if ( image_id &&
					is_checked &&
					'function' === typeof wp.media.attachment &&
					'object' === typeof wp.media.attachment().collection &&
					'object' === typeof wp.media.attachment().collection._byId &&
					'object' === typeof wp.media.attachment().collection._byId[ image_id ] &&
					'object' === typeof wp.media.attachment().collection._byId[ image_id ].attributes &&
					'object' === typeof wp.media.attachment().collection._byId[ image_id ].attributes.compat &&
					'string' === typeof wp.media.attachment().collection._byId[ image_id ].attributes.compat.item ) {

					credits = $( '.compat-field-image_credit input', wp.media.attachment().collection._byId[ image_id ].attributes.compat.item ).val();

					if ( credits.toLowerCase().includes( 'getty' ) && 'true' === is_checked ) {

						$( '.media-button-insert' ).prop( 'disabled', true );
						$( '.button.media-button.button-primary.button-large' ).prop( 'disabled', true );
						$( this )
							.attr( 'aria-checked', false )
							.removeClass( 'selected' )
							.find( 'div.thumbnail' ).first().addClass( 'getty-content' )
							.find( 'div.centered' ).first().addClass( 'getty-image' );

					} else if ( 'true' === is_checked ) {

						$( '.button.media-button.button-primary.button-large' ).prop( 'disabled', false );
						$( 'div.getty-content' ).removeClass( 'getty-content' );
						$( 'div.getty-image' ).removeClass( 'getty-image' );

					}
				}
			});
		});
	}
});
