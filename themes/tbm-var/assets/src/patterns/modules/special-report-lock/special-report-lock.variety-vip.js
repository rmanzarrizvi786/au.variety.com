const clonedeep = require( 'lodash.clonedeep' );

const read_on_prototype = require( '../../modules/read-on/read-on.variety-vip.js' );
const read_on = clonedeep( read_on_prototype );

const view_full_extended_prototype = require( '../../modules/view-full-extended/view-full-extended.variety-vip.js' );
const view_full_extended = clonedeep( view_full_extended_prototype );

read_on.read_on_classes += ' u-margin-b-2';

module.exports = {
	read_on,
	view_full_extended,
};
