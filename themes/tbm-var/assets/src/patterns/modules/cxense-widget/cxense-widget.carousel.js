const clonedeep = require( 'lodash.clonedeep' );

const cxense_widget = clonedeep( require( './cxense-widget.prototype' ) );

cxense_widget.cxense_id_attr = 'cx-module-top-stories-carousel';

module.exports = cxense_widget;