import 'waypoints/lib/noframework.waypoints.min.js';

/**
 * Scroll custom functionality
 */
export default class LatestNewsButton {
	constructor() {
		this.latestNewsButtonElement = {};
		this.waypointEndElement = {};
		this.waypointStartElement = {};

		this.init = this.init.bind( this );
		this.addEventHandler = this.addEventHandler.bind( this );

		this.init();
	}

	/**
	 * Initialize.
	 *
	 * @return {void}
	 */
	init() {
		this.latestNewsButtonElement = document.querySelector(
			'.js-LatestNewsButton'
		);
		this.waypointStartElement = document.querySelector(
			'.js-LatestNewsButton-WaypointStart'
		);
		this.waypointEndElement = document.querySelector(
			'.js-LatestNewsButton-WaypointEnd'
		);
		this.scrollDestinationElement = document.querySelector(
			'.js-LatestNewsButton-ScrollDestination'
		);

		if ( ! this.latestNewsButtonElement ) {
			return;
		}

		this.addEventHandler( this.latestNewsButtonElement );

		this.waypointStart = new Waypoint( {
			element: this.waypointStartElement,
			handler: ( direction ) => {
				if ( 'down' === direction ) {
					this.latestNewsButtonElement.style.transform =
						'translateY(0)';
				} else if ( 'up' === direction ) {
					this.latestNewsButtonElement.style.transform =
						'translateY(30vh)';
				}
			},
			offset: '0%',
		} );

		this.waypointEnd = new Waypoint( {
			element: this.waypointEndElement,

			handler: ( direction ) => {
				if ( 'down' === direction ) {
					this.latestNewsButtonElement.style.transform =
						'translateY(30vh)';
				} else if ( 'up' === direction ) {
					this.latestNewsButtonElement.style.transform =
						'translateY(0)';
				}
			},
			offset: '100%',
		} );
	}

	/**
	 * Add event handlers.
	 *
	 * @param {string} element
	 */
	addEventHandler( element ) {
		element.addEventListener( 'click', ( event ) => {
			event.preventDefault();

			/* eslint-disable no-unused-expressions */
			this.scrollDestinationElement &&
				window.scrollTo( {
					top: this.scrollDestinationElement.offsetTop - 50,
					left: 0,
					behavior: 'smooth',
				} );
			/* eslint-enable no-unused-expressions */
		} );
	}
}
