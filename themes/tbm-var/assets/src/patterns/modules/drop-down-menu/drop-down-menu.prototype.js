const clonedeep = require( 'lodash.clonedeep' );

const o_drop_prototype = require( '../../objects/o-drop-menu/o-drop-menu.prototype' );
const o_drop_menu = clonedeep( o_drop_prototype );

o_drop_menu.o_drop_menu_classes += ' lrv-u-display-inline-block';
o_drop_menu.toggle_classes += ' lrv-u-font-family-secondary lrv-u-font-size-12 lrv-u-font-weight-bold lrv-u-line-height-large lrv-u-text-transform-uppercase u-letter-spacing-2 lrv-u-padding-lr-050 u-color-brand-accent-20:hover lrv-u-whitespace-nowrap';
o_drop_menu.o_drop_menu_list_classes = 'lrv-a-glue a-glue--t-100p edition_panel--tooltip u-background-color-accent-c-40 lrv-u-padding-tb-050 lrv-u-margin-t-050 lrv-u-border-a-1 u-border-color-loblolly-grey';
o_drop_menu.o_nav.o_nav_list_item_classes = 'lrv-u-font-family-secondary lrv-u-padding-lr-050 lrv-u-font-size-12 lrv-u-font-weight-bold lrv-u-line-height-medium lrv-u-text-transform-uppercase u-letter-spacing-2 u-color-picked-bluewood u-color-picked-bluewood:hover	lrv-u-background-color-brand-primary:hover';

module.exports = {
	drop_down_menu_classes: '',
	o_drop_menu
};
