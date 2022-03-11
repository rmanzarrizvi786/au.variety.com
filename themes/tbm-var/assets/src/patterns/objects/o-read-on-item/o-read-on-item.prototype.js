const clonedeep = require( 'lodash.clonedeep' );

const c_span_prototype = require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype.js' );
const c_span = clonedeep( c_span_prototype );

const c_dek_prototype = require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype.js' );
const c_dek = clonedeep( c_dek_prototype );

c_span.c_span_classes = 'lrv-u-background-color-brand-primary lrv-u-border-radius-50p lrv-u-color-white lrv-u-display-block lrv-u-margin-lr-auto lrv-u-text-align-center u-width-38 u-height-38 u-font-family-accent u-line-height-230';
c_span.c_span_text = '1';

c_dek.c_dek_classes = 'lrv-u-font-family-secondary lrv-u-margin-b-050 lrv-u-text-align-center u-font-size-19 u-color-brand-secondary-50 u-line-height-125';
c_dek.c_dek_text = 'Assessing the size of the potential audience for legal sports betting, looking across a broad range of estimates';

module.exports = {
	o_read_on_item_classes: '',
	c_span,
	c_dek,
};
