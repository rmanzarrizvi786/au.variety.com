const clonedeep = require( 'lodash.clonedeep' );

const c_span = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-span/c-span.prototype' ) );
c_span.c_span_classes = 'lrv-u-font-family-secondary lrv-u-font-weight-bold u-font-size-23';
c_span.c_span_text = 'HBO Max Non-Fiction Exec Team Takes Shape With Lizzie Fox';

module.exports = {
	o_tab_url: '',
	o_tab_classes: 'lrv-u-flex lrv-u-align-items-center lrv-u-padding-b-1 u-padding-t-075  ',
	o_tab_link_classes: 'lrv-u-display-block u-color-brand-secondary-50 u-font-family-accent u-font-size-13 u-letter-spacing-009 u-margin-t-075 ',
	c_span,
};
