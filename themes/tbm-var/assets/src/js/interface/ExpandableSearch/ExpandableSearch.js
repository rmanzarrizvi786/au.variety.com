/**
 * Expandable Search
 *
 * The required DOM structure for this pattern is as follows:
 *
 * .js-ExpandableSearch
 * * .js-ExpandableSearch-trigger
 * * .js-ExpandableSearch-target (faded in and out when trigger clicked)
 *
 * The stateful class .is-ExpandableSearch-open is added to .js-ExpandableSearch
 * when it is open.
 *
 * This is intended to go along with the expandable-search Larva module,
 * and is intended to support the Switype UI from the plugin, and to be
 * used in the header, header-sticky, and mega-menu contexts.
 *
 * There is CSS required for the js-fade-* classes and for the trigger to morph
 * into an X when it is open. a-become-close-button is intended here, but has not
 * been updated to support more than the mega menu at the time of this comment.
 *
 * This is largely unmodified and copied from Artnews redesign, 11/2019
 *
 */
export default class ExpandableSearch {
	constructor( el ) {
		this.el = el;
		this.isOpen = false;
		this.trigger = this.el.querySelector( '.js-ExpandableSearch-trigger' );
		this.target = this.el.querySelector( '.js-ExpandableSearch-target' );
		this.targetInput = this.target.querySelector( 'input' );

		this.trigger.addEventListener( 'click', () => {
			this.toggleSearch();
		} );

		// Collapse search on ESC.
		document.addEventListener( 'keydown', ( e ) => {
			if ( 27 === e.keyCode ) {
				this.collapseSearch();
			}
		} );

		// Collapse search on body click, outside of search el.
		document.body.addEventListener( 'click', ( e ) => {
			if ( ! this.el.contains( e.target ) ) {
				this.collapseSearch();
			}
		} );

		// Collapse search when focus leaves search el.
		document.addEventListener( 'focusin', () => {
			// eslint-disable-next-line @wordpress/no-global-active-element
			if ( ! this.el.contains( document.activeElement ) && this.isOpen ) {
				this.collapseSearch();
			}
		} );
	}

	updateState() {
		this.isOpen = this.isOpen ? false : true;
	}

	toggleSearch() {
		this.el.classList.toggle( 'is-ExpandableSearch-open' );

		this.target.toggleAttribute( 'hidden' );
		this.target.classList.toggle( 'js-fade-is-out' );
		this.target.classList.toggle( 'js-fade-is-in' );

		this.updateState();

		// Switch focus to input if it is opened
		if ( this.isOpen ) {
			this.targetInput.focus();
		}
	}

	// Switch focus back to trigger when search is closed.
	collapseSearch() {
		if ( this.isOpen ) {
			this.toggleSearch();
			this.trigger.focus();
		}
	}
}
