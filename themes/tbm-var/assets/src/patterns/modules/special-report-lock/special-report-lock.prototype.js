const clonedeep = require( 'lodash.clonedeep' );

const read_on_prototype = require( '../../modules/read-on/read-on.variety-vip.js' );
const read_on = clonedeep( read_on_prototype );

const view_full_prototype = require( '../../modules/view-full/view-full.variety-vip.js' );
const view_full = clonedeep( view_full_prototype );

read_on.read_on_classes += ' u-margin-b-2';

view_full.view_full_classes += ' u-margin-b-2';

module.exports = {
	read_on,
	view_full,
};
