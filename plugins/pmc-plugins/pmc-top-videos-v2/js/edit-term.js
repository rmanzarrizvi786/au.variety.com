/* globals jQuery, wp, _pmcVideoCategoryExports */
/* exported pmcVideoCategory */

var pmcVideoCategory = ( function( $ ) { // eslint-disable-line no-unused-vars
	'use strict';

	var self = {
		frame: false,
		modalTitle: 'Select or Upload an Image',
		buttonText: 'Insert Image',
		elements: {}
	};

	if ( 'undefined' !== typeof _pmcVideoCategoryExports ) {
		$.extend( self, _pmcVideoCategoryExports );
	}

	self.init = function() {

		var response = '';

		if ( 'object' !== typeof self.elements ) {
			return;
		}

		$( document ).ready( function() {

			$.each( self.elements, function() {
				var element = $( this );
				if ( 1 > element.length ) {
					return true;
				}
				$( element ).on( 'click', '.btn-add', function( e ) {
					e.preventDefault();
					self.uploadImage( element );
				});

				$( element ).on( 'click', '.btn-remove', function( e ) {
					e.preventDefault();
					self.removeImage( element );
				});
			});
		});

		// Remove the image preview when a new Term is added via AJAX.
		$( document ).ajaxComplete( function( event, xhr, settings ) {
			if ( 'undefined' !== typeof settings.data ) {
				var queryStringArr = settings.data.split( '&' );

				if ( ( 'object' === typeof queryStringArr ) && ( -1 !== $.inArray( 'action=add-tag', queryStringArr ) ) ) {
					response = $( xhr.responseXML ).find( 'term_id' ).text();
					if ( '' !== response ) {

						// This is the only image field on the "Add Term" screen.
						self.removeImage( '.vcat-image' );
					}
				}
			}
		});
	};

	self.uploadImage = function( element ) {
		if ( ! self.frame ) {
			self.frame = wp.media({
				frame: 'select',
				title: self.modalTitle,
				button: {
					text: self.buttonText
				},
				multiple: false
			});
		}

		self.frame.on( 'select', function() {
			var attachment = self.frame.state().get( 'selection' ).first().toJSON();
			$( '.img-wrapper', element ).empty().append( $( '<img>', { src: attachment.url }) );
			$( '.image-id', element ).val( attachment.id );
			$( '.btn-add', element ).addClass( 'hidden' );
			$( '.btn-remove', element ).removeClass( 'hidden' );
			self.frame = null;
		});

		self.frame.open();
	};

	self.removeImage = function( element ) {
		$( '.img-wrapper', element ).empty();
		$( '.image-id', element ).val( '' );
		$( '.btn-add', element ).removeClass( 'hidden' );
		$( '.btn-remove', element ).addClass( 'hidden' );
	};

	return self;

}( jQuery ) );
