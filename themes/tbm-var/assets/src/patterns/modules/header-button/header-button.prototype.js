const clonedeep = require( 'lodash.clonedeep' );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' );

const c_span_main = clonedeep( c_span_prototype );

c_span_main.c_span_classes = 'lrv-u-font-weight-bold lrv-u-font-family-secondary lrv-u-font-size-12 u-letter-spacing-2';
c_span_main.c_span_text = 'Subscribe';

module.exports = {
	header_button_classes: 'lrv-u-color-white lrv-u-border-a-1 u-border-color-brand-secondary-30 u-color-brand-accent-20:hover',
	header_button_url: '/subscribe-us/',
	c_span_main: c_span_main,
	header_button_id_attr: '',
};
