const clonedeep = require( 'lodash.clonedeep' );

const short_form_landing_prototype = require( './short-form-landing.prototype.js' );
const short_form_landing = clonedeep( short_form_landing_prototype );

module.exports = {
	...short_form_landing
};
