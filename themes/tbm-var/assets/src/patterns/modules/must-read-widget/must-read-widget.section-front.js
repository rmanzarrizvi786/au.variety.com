const clonedeep = require( 'lodash.clonedeep' );
const must_read_widget = clonedeep( require( './must-read-widget.prototype' ) );

must_read_widget.must_read_widget_classes = 'u-border-color-brand-primary u-box-shadow-menu';

module.exports = must_read_widget;
