/**
 * ScrollTo
 *
 * This JS module has scrolling to target on same page.
 * Add .js-ScrollTo to link to invoke behavior.
 * If you wish to further fine tune offset, use [data-scrollto-offset-top] on targeted section.
 *
 * @since 05-28-2020
 */

export default class ScrollTo {
	constructor( el ) {
		this.el = el;
		this.el.addEventListener( 'click', this.onClick.bind( this ) );
	}

	onClick( e ) {
		e.preventDefault();
		const target = document.querySelector( this.el.hash );

		// If target is found...
		if ( null !== target ) {
			// If clicked from open dropdown menu, close it.
			const openNav = this.el.closest( '[data-collapsible="expanded"]' );
			if ( null !== openNav ) {
				openNav.classList.remove( 'is-expanded' );
				openNav.dataset.collapsible = 'collapsed';
			}

			// Get value if further offset is provided. Check if target has defined '[data-scrollto-offset-top]' attribute.
			const scrollTop =
				window.pageYOffset || document.documentElement.scrollTop;
			let userOffset = target.getBoundingClientRect().top + scrollTop;

			if ( undefined !== target.dataset.scrolltoOffsetTop ) {
				userOffset += parseInt( target.dataset.scrolltoOffsetTop );
			}

			window.scrollTo( {
				top: userOffset,
				behavior: 'smooth',
			} );
		}
	}
}
