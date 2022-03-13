/**
 * Gallery post type meta box scripts.
 */

const metaboxes = {

	init() {
		this.nextGalleryMetabox();
	},

	/**
	 * Next gallery meta-box settings.
	 */
	nextGalleryMetabox() {
		const nextField = document.getElementById( 'pmc-gallery-next-gallery' );
		const checkbox = document.getElementById( 'pmc-gallery-automatic-next-gallery' );

		if ( ! nextField || ! checkbox ) {
			return;
		}

		const nextInput = nextField.querySelector( '.pmclinkcontent-post-search' );

		/**
		 * Enable/Disable search fields.
		 */
		const toggleSearchFieldDisable = () => {

			if ( checkbox.checked ) {
				nextInput.setAttribute( 'disabled', 'disabled' );
				nextField.classList.add( 'disabled' );
			} else {
				nextInput.removeAttribute( 'disabled' );
				nextField.classList.remove( 'disabled' );
			}
		};

		toggleSearchFieldDisable();

		checkbox.addEventListener( 'change', toggleSearchFieldDisable );

	}
};

jQuery( document ).ready( () => metaboxes.init() );
