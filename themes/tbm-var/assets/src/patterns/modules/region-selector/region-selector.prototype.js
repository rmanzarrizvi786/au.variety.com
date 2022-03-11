const clonedeep = require( 'lodash.clonedeep' );

/* The presence of the global or asia classes on the body element changes the state of
	 the region selector to correctly show the current edition and the ones available to
	 switch to. */

const o_region_selector_prototype = require( '../../objects/o-region-selector/o-region-selector.prototype' );
const region_selector = clonedeep( o_region_selector_prototype );

region_selector.toggle_classes += ' lrv-u-color-white lrv-u-font-family-secondary lrv-u-font-size-12 lrv-u-font-weight-bold lrv-u-line-height-large lrv-u-text-transform-uppercase u-letter-spacing-2 lrv-u-padding-lr-050 u-color-brand-accent-20:hover lrv-u-whitespace-nowrap';
region_selector.dropdown_classes += ' lrv-a-glue a-glue--t-100p edition_panel--tooltip u-background-color-accent-c-40 lrv-u-padding-tb-050 lrv-u-margin-t-050 lrv-u-border-a-1 u-border-color-loblolly-grey';
region_selector.dropdown_item_classes += ' lrv-u-font-family-secondary lrv-u-padding-lr-050 lrv-u-font-size-12 lrv-u-font-weight-bold lrv-u-line-height-medium lrv-u-text-transform-uppercase u-letter-spacing-2 u-color-picked-bluewood u-color-picked-bluewood:hover	lrv-u-background-color-brand-primary:hover';
region_selector.region_selector_classes += '';

module.exports = {
	region_selector
};
