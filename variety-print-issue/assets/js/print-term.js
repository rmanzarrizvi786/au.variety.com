/* globals jQuery, wp _varietyPrintIssueTermExports */
/* exported varietyPrintIssueTerm */

var varietyPrintIssueTerm = ( function( $ ) {
	'use strict';

	var self = {
		frame: false,
		modalTitle: 'Select or Upload a Cover',
		buttonText: 'Insert Cover'
	};

	if ( 'undefined' !== typeof _varietyPrintIssueTermExports ) {
		$.extend( self, _varietyPrintIssueTermExports );
	}

	self.init = function() {
		$( document ).ready( function() {
			self.imgContainer = $( '#print-issue-image-wrapper' );
			self.imgId = $( '#print-issue-image-id' );
			self.btnAdd = $( '#print-issue-btn-add' );
			self.btnRemove = $( '#print-issue-btn-remove' );

			self.btnAdd.on( 'click', function( e ) {
				e.preventDefault();
				self.uploadImage();
			} );

			self.btnRemove.on( 'click', function( e ) {
				e.preventDefault();
				self.removeImage();
			} );
		} );

		// Remove the image preview when a new Term is added via AJAX.
		$( document ).ajaxComplete( function( event, xhr, settings ) {
			if ( 'undefined' !== typeof settings.data ) {
				var queryStringArr = settings.data.split( '&' );

				if ( ( 'object' === typeof queryStringArr ) && ( -1 !== $.inArray( 'action=add-tag', queryStringArr ) ) ){
					var response = $( xhr.responseXML ).find( 'term_id' ).text();
					if( '' !== response ) {
						self.removeImage();
					}
				}
			}
		} );
	};

	self.uploadImage = function() {
		if ( ! self.frame ) {
			self.frame = wp.media( {
				frame: 'select',
				title: self.modalTitle,
				button: {
					text: self.buttonText
				},
				multiple: false
			} );
		}

		self.frame.on( 'select', function() {
			var attachment = self.frame.state().get( 'selection' ).first().toJSON();
			self.imgContainer.empty().append( $( '<img>', { src: attachment.url } ) );
			self.imgId.val( attachment.id );
			self.btnAdd.addClass( 'hidden' );
			self.btnRemove.removeClass( 'hidden' );
		} );

		self.frame.open();
	};

	self.removeImage = function() {
		self.imgContainer.empty();
		self.imgId.val( '' );
		self.btnAdd.removeClass( 'hidden' );
		self.btnRemove.addClass( 'hidden' );
	};

	return self;

} ) ( jQuery );