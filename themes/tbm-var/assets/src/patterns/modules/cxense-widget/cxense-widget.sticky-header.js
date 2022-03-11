const clonedeep = require( 'lodash.clonedeep' );

const cxense_widget = clonedeep( require( '../cxense-widget/cxense-widget.prototype' ) );

cxense_widget.cxense_id_attr = 'cx-module-sticky-header';
cxense_widget.cxense_widget_classes = 'a-subscription-sticky-header';

module.exports = cxense_widget;