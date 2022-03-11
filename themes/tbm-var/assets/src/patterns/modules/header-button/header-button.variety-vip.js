const clonedeep = require( 'lodash.clonedeep' );

const header_button_prototype = require( './header-button.prototype.js' );
const header_button = clonedeep( header_button_prototype );

const { c_span_main } = header_button;

header_button.header_button_classes = header_button.header_button_classes.replace( 'u-border-color-brand-secondary-30', 'u-border-color-black' );
header_button.header_button_classes = header_button.header_button_classes.replace( 'u-color-brand-accent-20:hover', 'u-color-brand-primary:hover' );
header_button.header_button_classes = header_button.header_button_classes.replace( 'lrv-u-color-white', 'lrv-u-color-black' );
header_button.header_button_classes += ' lrv-u-background-color-white lrv-u-text-transform-uppercase';

module.exports = {
	...header_button,
};
