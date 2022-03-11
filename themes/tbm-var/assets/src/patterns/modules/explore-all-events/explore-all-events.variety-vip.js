const clonedeep = require( 'lodash.clonedeep' );

const special_reports_carousel_prototype = require( './explore-all-events.prototype.js' );
const special_reports_carousel = clonedeep( special_reports_carousel_prototype );

module.exports = {
	...special_reports_carousel,
};
