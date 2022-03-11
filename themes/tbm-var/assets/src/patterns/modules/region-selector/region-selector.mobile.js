const clonedeep = require( 'lodash.clonedeep' );

/* The presence of the global or asia classes on the body element changes the state of
	 the region selector to correctly show the current edition and the ones available to
   switch to. */

const o_region_selector_prototype = require( '../../objects/o-region-selector/o-region-selector.prototype' );
const region_selector = clonedeep( o_region_selector_prototype );

region_selector.toggle_classes += ' js-edition_toggle--mobile lrv-u-color-black lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-font-size-13 u-letter-spacing-2 lrv-u-whitespace-nowrap';
region_selector.dropdown_classes += ' lrv-u-padding-t-050';
region_selector.dropdown_item_classes += ' lrv-u-color-black lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-font-size-13 u-letter-spacing-2 lrv-u-padding-b-050 u-padding-l-050';
region_selector.region_selector_classes += ' a-hidden@tablet u-padding-lr-3';

module.exports = {
  region_selector
};
