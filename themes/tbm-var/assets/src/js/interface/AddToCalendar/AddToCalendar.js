/**
 * Add To Calendar
 *
 * Ticket: https://jira.pmcdev.io/browse/PMCP-2881
 * This module is intended to add event to calendar manully by downloading ICS file.
 *
 * This JS pattern requires the following class and data-attributes added to HTML:
 * - `js-AddToCalendar` - Add class to the element of event link.
 * - `data-title` - Title of the event.
 * - `data-location` - Location of the event.
 * - `data-start` - Start date of the event.
 *
 * NOTE: This event meant to be All day event so event-end date is not required here.
 * It generates next day based on the start date.
 *
 * <a class="js-AddToCalendar" data-start="2021-03-25" data-title="Event Title" data-location="Apple TV+">
 * 	Download ICS
 * </a>
 */
export default class AddToCalendar {
	constructor( el ) {
		this.el = el;

		this.eventTitle = this.el.dataset.title;
		this.eventStart = this.el.dataset.start;
		this.eventLocation = this.el.dataset.location;

		if ( ! this.eventTitle || ! this.eventStart ) {
			console.error( 'Event title or start date missing.' ); // eslint-disable-line no-console
			return;
		}

		this.dtstart = this.getDateFormat();
		this.dtend = this.getEventEndDate();

		if ( ! this.dtstart || ! this.dtend ) {
			console.error( 'Event start date or end date missing.' ); // eslint-disable-line no-console
			return;
		}

		this.makeIcsFile = this.makeIcsFile.bind( this );

		this.el.addEventListener( 'click', this.makeIcsFile );
	}

	/**
	 * Gets required date format for ICS file.
	 *
	 * @return {string} formatted date
	 */
	getDateFormat() {
		let event = new Date( this.eventStart );

		if ( ! this.isValidDate( event ) ) {
			console.error( 'Invalid event date found' ); // eslint-disable-line no-console
			return '';
		}

		event = event.toISOString();
		event = event.split( 'T' )[ 0 ];
		event = event.split( '-' );
		event = event.join( '' );

		return event;
	}

	/**
	 * Get Event End Date.
	 *
	 * End date will be next day of the event start.
	 *
	 * @return {string} Formatted end date
	 */
	getEventEndDate() {
		const day = new Date( this.eventStart );

		if ( ! this.isValidDate( day ) ) {
			console.error( 'Invalid event date found' ); // eslint-disable-line no-console
			return '';
		}

		const nextDay = new Date( day );

		if ( ! this.isValidDate( nextDay ) ) {
			return '';
		}

		nextDay.setDate( day.getDate() + 1 );

		return this.getDateFormat( nextDay );
	}

	/**
	 * Check valid date instance.
	 *
	 * @param {string} date
	 * @return {boolean} if the date is valid.
	 */
	isValidDate( date ) {
		return date instanceof Date && ! isNaN( date );
	}

	/**
	 * Make ICS File and download
	 *
	 * Ref: https://codepen.io/vlemoine/pen/MLwygX
	 *
	 * @param {Event} e
	 */
	makeIcsFile( e ) {
		e.preventDefault();

		const icsFormat =
			'BEGIN:VCALENDAR\n' +
			'VERSION:2.0\n' +
			'BEGIN:VEVENT\n' +
			'SUMMARY:' +
			this.eventTitle +
			'\n' +
			'DTSTART;VALUE=DATE:' +
			this.dtstart +
			'\n' +
			'DTEND;VALUE=DATE:' +
			this.dtend +
			'\n' +
			'LOCATION:' +
			this.eventLocation +
			'\n' +
			'END:VEVENT\n' +
			'END:VCALENDAR';

		// Create Element to download element.
		const element = document.createElement( 'a' );
		element.setAttribute(
			'href',
			'data:text/plain;charset=utf-8,' + encodeURIComponent( icsFormat )
		);
		element.setAttribute( 'download', 'event.ics' );

		element.style.display = 'none';
		document.body.appendChild( element );

		element.click();

		// Remove Element.
		document.body.removeChild( element );
	}
}
