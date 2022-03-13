/**
 * Prepare a page for printing, ensuring that lazy-loaded elements are loaded
 * and so on.
 */
export default class PrintPreparer {
	/**
	 * Constructor.
	 */
	constructor() {
		this.print = this.print.bind( this );
		this.loadLazyImage = this.loadLazyImage.bind( this );
		this.onBeforePrint = this.onBeforePrint.bind( this );

		// If someone uses the keyboard shortcut or menu item, try to fix it.
		window.addEventListener( 'beforeprint', this.onBeforePrint );

		// Support hotlink triggering.
		if ( '#print-page' === window.location.hash ) {
			this.print();
		}
	}

	/**
	 * Handle a print request.
	 *
	 * TODO: show something to the user...
	 */
	async print() {
		const images = document.querySelectorAll( 'img[data-lazy-src]' );

		for ( const image of images ) {
			await this.loadLazyImage( image );
		}

		window.print();
	}

	/**
	 * Load a lazy image.
	 *
	 * @param {Element} img Image element.
	 * @return {Promise} Promise to load specified image.
	 */
	loadLazyImage( img ) {
		return new Promise( ( resolve ) => {
			img.addEventListener( 'load', resolve );
			img.addEventListener( 'error', resolve );

			const src = img.getAttribute( 'data-lazy-src' );

			// Capturing before they're removed below.
			/* eslint-disable @wordpress/no-unused-vars-before-return */
			const srcset = img.getAttribute( 'data-lazy-srcset' );
			const sizes = img.getAttribute( 'data-lazy-sizes' );
			/* eslint-enable @wordpress/no-unused-vars-before-return */

			img.setAttribute( 'data-lazy-loaded', 'true' );

			img.removeAttribute( 'data-lazy-src' );
			img.removeAttribute( 'data-lazy-srcset' );
			img.removeAttribute( 'data-lazy-sizes' );

			if ( ! src ) {
				resolve();
				return;
			}

			img.setAttribute( 'src', src );

			if ( srcset ) {
				img.setAttribute( 'srcset', srcset );
			}

			if ( sizes ) {
				img.setAttribute( 'sizes', sizes );
			}
		} );
	}

	/**
	 * Load all lazy images.
	 */
	onBeforePrint() {
		document
			.querySelectorAll( 'img[data-lazy-src]' )
			.forEach( this.loadLazyImage );
	}
}
