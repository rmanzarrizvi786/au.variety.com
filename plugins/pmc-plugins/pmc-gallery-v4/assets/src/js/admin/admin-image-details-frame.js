/**
 * Script to add and manage credit field on/from caption shortcode frame
 */
(function ( $, _ ) {

	var media = wp.media;
	var origImageDetails = media.view.ImageDetails;

	media.view.ImageDetails = origImageDetails.extend( {

		// Add event listener to save credit field on change
		events: _.defaults( origImageDetails.prototype.events, {
			'change [data-setting="imageCredit"]': 'updateImageCredit'
		} ),

		/**
		 * Add image_credit value to ImageDetail model when frame prepares for render
		 */
		prepare: function() {

			var attachment;

			this.model.set( 'image_credit', this.model.attachment.get( 'image_credit' ) );

			if ( this.model.attachment ) {
				attachment = this.model.attachment.toJSON();
			}

			return _.defaults({
				model: this.model.toJSON(),
				attachment: attachment
			}, this.options );

		},

		/**
		 * Callback to event listener for credit field change
		 *
		 * It fires manual ajax call to save image credit on changes
		 * Also updates its value to media model and attachment model for later use.
		 */
		updateImageCredit: function( event ) {

			var model = this,
				data = {};

			if ( event ) {
				event.preventDefault();
			}

			// If we do not have the necessary nonce, fail immediately.
			if ( ! this.model.attachment.get( 'nonces' ) || ! this.model.attachment.get( 'nonces' ).update ) {
				return $.Deferred().rejectWith( this ).promise();
			}

			data.image_credit = event.target.value;

			return wp.media.post( 'pmc-save-attachment-credit', _.defaults({
				attachment_id: this.model.attachment.get( 'id' ),
				nonce: this.model.attachment.get( 'nonces' ).update,
			}, data ) ).done( function( resp, status, xhr ) {
				if ( resp ) {
					model.model.set( data );
					model.model.attachment.set( data );
				}
			});
		}

	} );
}( jQuery, _ ));
