const clonedeep = require( 'lodash.clonedeep' );

const cxense_widget = clonedeep( require( '../cxense-widget/cxense-widget.prototype' ) );

cxense_widget.cxense_id_attr = 'cx-module-300x250';

module.exports = cxense_widget;