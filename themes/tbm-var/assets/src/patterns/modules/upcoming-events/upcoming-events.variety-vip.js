const clonedeep = require( 'lodash.clonedeep' );

const upcoming_events_prototype = require( './upcoming-events.prototype.js' );
const upcoming_events = clonedeep( upcoming_events_prototype );

module.exports = {
	...upcoming_events
};
