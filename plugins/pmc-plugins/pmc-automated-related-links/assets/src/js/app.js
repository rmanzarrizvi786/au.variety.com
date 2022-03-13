/* global jQuery, add_filter */

const pmc_automated_related_links = {

	/**
	 * Init method.
	 *
	 * @return void
	 */
	init: function init() {
		if ( 'function' === typeof add_filter ) {
			add_filter( 'pmc-google-analytics-tracking-events', this.filter_ga_events );
		}
	},

	/**
	 * To filter event tracking data for related link.
	 *
	 * @param {Object} event Event data.
	 * @param {Object} element DOM Element.
	 *
	 * @return {Object}
	 */
	filter_ga_events: function filter_ga_events( event, element ) {

		if ( 'object' !== typeof event || 'article-page' !== event.eventCategory || 'click' !== event.eventAction ) {
			return event;
		}

		if ( -1 === event.eventLabel.search( 'related-article' ) && -1 === event.eventLabel.search( 'related-evergreen' ) ) {
			return event;
		}

		let index = element.data( 'index' );

		if ( 'undefined' ===  typeof index ) {
			const list_item = element.closest( 'li' );

			index = list_item.data( 'index' );
		}

		if ( 'number' ===  typeof index ) {
			event.eventValue = index;
		}

		return event;

	}

};


jQuery( document ).ready( function() {
	pmc_automated_related_links.init();
});
