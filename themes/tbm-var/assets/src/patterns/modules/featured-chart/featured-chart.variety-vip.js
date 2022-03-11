const clonedeep = require( 'lodash.clonedeep' );

const featured_chart_prototype = require( './featured-chart.prototype.js' );
const featured_chart = clonedeep( featured_chart_prototype );

module.exports = {
	...featured_chart_prototype
};
