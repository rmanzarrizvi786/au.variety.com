import { useEffect } from 'react';

let analyticsFilterCallbackAdded = false;
let analyticsFilterCallback = ( event ) => event;

function sendEvent( { action, category, label, value } ) {
	try {
		if ( window.pmc && window.pmc.event_tracking ) {
			// @TODO: Should we include an element?
			// const $element = window.jQuery ? window.jQuery( this.myRef.current ) : null;
			// DOM object from selector
			const $element = null;

			const details = false;
			const url = false;
			const nonInteraction = true;
			const preEvents = false;

			if ( window.pmc.hooks && window.pmc.hooks.add_filter ) {
				analyticsFilterCallback = ( event ) => {
					const updatedEvent = { ...event };
					if ( value ) {
						updatedEvent.eventValue = value;
					}
					return updatedEvent;
				};

				if ( ! analyticsFilterCallbackAdded ) {
					window.pmc.hooks.add_filter(
						'pmc-google-analytics-tracking-events',
						function( event ) {
							const updatedEvent = analyticsFilterCallback( event );
							return updatedEvent;
						}.bind( this )
					);
					analyticsFilterCallbackAdded = true;
				}
			}

			window.pmc.event_tracking( $element, action, category, label, details, url, nonInteraction, preEvents );
		}
	} catch ( e ) {
		console.error( e ); // eslint-disable-line
	}
}

function useAnalytics( {
	category,
	label,
	value,
	valueEnabled,
	actions,
	actionTarget,
	enabled,
} ) {
	useEffect( () => {
		if ( ! enabled ) return;
		const action = actions[ actionTarget ];
		if ( action ) {
			sendEvent( {
				action,
				category,
				label,
				value: ( valueEnabled[ actionTarget ] ) ? value : null,
			} );
		}
	}, [ enabled, actionTarget ] );
}

export {
	useAnalytics,
};
