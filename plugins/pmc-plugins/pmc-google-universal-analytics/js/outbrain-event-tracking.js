/**
 * Add Google Analytics tracking to OUTBRAIN widget
 *
 * @author sagarkbhatt <sagar.bhatt@rtcamp.com>
 */

/**
 * To Generate a Minified Version:
 *
 * npm install uglify-js -g
 *
 * cd pmc-plugins/pmc-google-universal-analytics/js/
 * uglifyjs outbrain-event-tracking.js -o outbrain-event-tracking.min.js --compress unused=false
 */

/* global ga */

( function() {
	var outbrain_elements;

	/**
	 * Initialize event tracking for OUTBRAIN element.
	 * Setup data for OUTBRAIN element.
	 *
	 * @param element
	 */
	var pmc_ob_event_tracking = function( element ) {
		this.obElement = element;
		this.device = this.getDevice();
		this.obElementDynamicLinkContainer = '.ob-dynamic-rec-container';
		this.obElementDynamicLink = '.ob-dynamic-rec-link';
		this.obWidgetId = element.getAttribute( 'data-widget-id' );
		this.linksData = [];
		this.initMutationObserver();
	};

	/**
	 * Initialize mutation observer.
	 */
	pmc_ob_event_tracking.prototype.initMutationObserver = function() {
		var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;

		if ( undefined === MutationObserver || null === MutationObserver ) {
			return;
		}

		this.observer = new MutationObserver( this.mutationObserverCallback.bind( this ) );
		this.observer.observe( this.obElement, { childList: true });
	};

	/**
	 * Callback function for mutation observer
	 *
	 * @param mutationsList
	 * @param observer
	 */
	pmc_ob_event_tracking.prototype.mutationObserverCallback = function( mutationsList, observer ) {
		var dynamicLinks = this.obElement.querySelectorAll( this.obElementDynamicLinkContainer );
		if ( dynamicLinks && ( 0 < dynamicLinks.length ) ) {
			observer.disconnect();
			this.getObDynamicLinks( dynamicLinks );
		}
	};

	/**
	 * Function collects all outbrain article links and stores it in object for later use.
	 *
	 * It also stores index of link item container so we can use it later for mapping purpose.
	 * Link will be stored in pretty format without query string to exclude UTM params.
	 * After data collection complete function will fire GA event that track OUTBRAIN load for page,
	 * where eventLabel will be json object that contains all url with corresponding position.
	 *
	 * @param elements
	 */
	pmc_ob_event_tracking.prototype.getObDynamicLinks = function( elements ) {
		var object = {},
			link, el, index, pos;

		for ( index = 0; index < elements.length; index++ ) {
			el = elements[index];

			// start position from 1 instead of 0 that will make it human friendly.
			pos = index + 1;

			el.setAttribute( 'data-index', pos );
			link = el.querySelector( this.obElementDynamicLink );
			link = link ? link.getAttribute( 'href' ) : '';
			object[pos] = this.cleanURL( link );
		}

		this.linksData = object;
		this.sendGaEvent( JSON.stringify( this.linksData ), true );
		this.addObClickEventListener();
	};

	/**
	 * Add click event listener to OUTBRAIN element.
	 */
	pmc_ob_event_tracking.prototype.addObClickEventListener = function() {
		this.obElement.addEventListener( 'click', this.obClickEventCallback.bind( this ) );
	};

	/**
	 * Callback function for OUTBRAIN click event listener.
	 *
	 * @param event
	 */
	pmc_ob_event_tracking.prototype.obClickEventCallback = function( event ) {

		var el = event.srcElement || event.target,
			allowedTarget = '_blank',
			linkContainerClassName, pos, elLink, elTarget, link, label, object;

		// Get anchor tag to get dynamic link.
		while ( el && ( undefined === el.tagName || 'a' !== el.tagName.toLowerCase() || ! el.href ) ) {
			el = el.parentNode;
		}

		elLink   = el.href;
		elTarget = el.target;

		// Is actual target set and not _(self|parent|top) ?
		elTarget = ( allowedTarget === elTarget ) ? allowedTarget : false;

		if ( this.isEmpty( elLink ) ) {
			return;
		}

		// if a link with valid href has been clicked.

		// Get parent of anchor tag to get value of data pos.
		el = el.parentNode;

		// remove dot(.) from class name.
		linkContainerClassName = this.obElementDynamicLinkContainer.substring( 1 );

		if ( el && ( -1 === el.className.indexOf( linkContainerClassName ) ) ) {
			return;
		}

		// Assume a target if Ctrl|shift|meta-click.
		if ( event.ctrlKey || event.shiftKey || event.metaKey || 2 === event.which ) {
			elTarget = allowedTarget;
		}

		pos = el.getAttribute( 'data-index' );
		link = this.linksData[pos];

		if ( this.isEmpty( link ) ) {
			return;
		}

		object = {};
		object[pos] = link;
		label = JSON.stringify( object );

		if ( allowedTarget === elTarget ) { // If target opens a new window then just track.
			this.sendGaEvent( label );
		} else {
			event.preventDefault();
			this.sendGaEvent( label, false, this.createFunctionWithTimeout( function() {
				window.location.href = elLink;
			}) );
		}
	};

	/**
	 * Check for null, undefined and empty var
	 * will not check for empty object
	 *
	 * @param value
	 * @return {boolean}
	 */
	pmc_ob_event_tracking.prototype.isEmpty = function( value ) {
		return ( null == value || 0 === value.length );
	};

	/**
	 * Create function with timeout, use to create hitCallback for GA.
	 * Callback will be fired as soon as the hit has been successfully sent.
	 *
	 * @ref https://developers.google.com/analytics/devguides/collection/analyticsjs/sending-hits
	 *
	 * @param callback
	 * @param opt_timeout
	 * @return {fn}
	 */
	pmc_ob_event_tracking.prototype.createFunctionWithTimeout = function( callback, opt_timeout ) {

		var called = false;

		function fn() {
			if ( ! called ) {
				called = true;
				callback();
			}
		}

		setTimeout( fn, opt_timeout || 1000 );

		return fn;
	};

	/**
	 * Function sends GA events
	 * If load set to true that means, that event will be fired once OUTBRAIN loaded.
	 *
	 * @param eventLabel string Event label
	 * @param load boolean OUTBRAIN load event or not?
	 * @param hitCallback Callback function to use when target is set.
	 */
	pmc_ob_event_tracking.prototype.sendGaEvent = function( eventLabel, load, hitCallback ) {

		var isInteracting = ( undefined !== load ) ? load : false,
			label = this.device + ' [' + this.obWidgetId + '] ' + eventLabel;

		var analyticsObject = {};

		analyticsObject.hitType        = 'event';
		analyticsObject.eventCategory  = 'outbrain';
		analyticsObject.eventAction    = load ? 'load' : 'click';
		analyticsObject.eventLabel     = label;
		analyticsObject.nonInteraction = isInteracting;
		analyticsObject.transport      = 'beacon';

		if ( undefined !== hitCallback && 'function' === typeof hitCallback ) {
			analyticsObject.hitCallback = hitCallback;
		}

		try {
			ga( 'send', analyticsObject );
		} catch ( e ) {

			// If GA not defined then catch undefined function error.
		}
	};

	/**
	 * Remove query string and # link if any from URL
	 * If URL contains OUTBRAIN redirect link then don't clean the URL.
	 *
	 * @param url
	 */
	pmc_ob_event_tracking.prototype.cleanURL = function( url ) {

		if ( ( -1 !== url.indexOf( 'traffic.outbrain.com' ) ) || ( -1 !== url.indexOf( 'paid.outbrain.com' ) ) ) {
			return url;
		}

		return url.split( /[?#]/ )[0]; // remove query string or # link.
	};

	/**
	 * Get current device
	 *
	 * @return {string}
	 */
	pmc_ob_event_tracking.prototype.getDevice = function( ) {
		var defaultDevice = '[D]',
			deviceObject = window.pmc_ga_event_tracking,
			device = deviceObject ? ( deviceObject.device ? deviceObject.device : defaultDevice ) : defaultDevice;

		return device;
	};

	outbrain_elements = document.querySelectorAll( '.OUTBRAIN' );

	if ( 0 < outbrain_elements.length ) {

		for ( var i = 0; i < outbrain_elements.length; i++ ) { // eslint-disable-line
			new pmc_ob_event_tracking( outbrain_elements[i] ); // eslint-disable-line
		}
	}
}() );
