const clonedeep = require( 'lodash.clonedeep' );
const c_heading_primary           = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' ) );
c_heading_primary.c_heading_text    = 'How Do You Want to Experience Variety';
c_heading_primary.c_heading_classes = 'lrv-u-font-family-secondary u-text-transform-none lrv-u-font-size-40 u-font-size-47@tablet';

const c_icon_vip            = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype' ) );
c_icon_vip.c_icon_name      = 'vip-plus-mobile';
c_icon_vip.c_icon_classes   = 'u-width-75 u-height-25 u-width-100@tablet u-height-30@tablet';

const c_icon_variety            = clonedeep( require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype' ) );
c_icon_variety.c_icon_name        = 'variety-logo';
c_icon_variety.c_icon_classes     = 'u-width-75 u-height-25 u-width-100@tablet u-height-30@tablet lrv-u-color-black';

const o_tab_variety               = clonedeep( require( '../../objects/o-tab/o-tab.prototype' ) );
o_tab_variety.c_span.c_span_text  = 'Variety Magazine';
o_tab_variety.o_tab_url           = '#print-plus-shop-panel-variety';
o_tab_variety.o_tab_link_classes += ' js-TabsToggle';
o_tab_variety.o_tab_classes       = 'lrv-u-font-size-18 lrv-u-color-black lrv-u-text-decoration-none lrv-u-background-color-white lrv-u-padding-tb-1 u-border-radius-tr-5 u-border-radius-tl-5';
o_tab_variety.c_logo              = null;

const o_tab_variety__mobile       = clonedeep( require( '../../objects/o-tab/o-tab.prototype' ) );
o_tab_variety__mobile.c_span              = null;
o_tab_variety__mobile.c_icon              = c_icon_variety;
o_tab_variety__mobile.o_tab_url           = '#print-plus-shop-panel-variety';
o_tab_variety__mobile.o_tab_link_classes += ' js-TabsToggle';
o_tab_variety__mobile.o_tab_classes       = 'lrv-u-text-decoration-none lrv-u-background-color-white lrv-u-padding-tb-1 u-border-radius-tr-5 u-border-radius-tl-5';

const o_tab_vip                = clonedeep( require( '../../objects/o-tab/o-tab.prototype' ) );
o_tab_vip.c_span.c_span_text   = 'Variety Intelligence Platform';
o_tab_vip.o_tab_url            = '#print-plus-shop-panel-vip';
o_tab_vip.o_tab_link_classes  += ' js-TabsToggle is-active';
o_tab_vip.o_tab_classes        = 'lrv-u-font-size-18 lrv-u-color-black lrv-u-text-decoration-none lrv-u-background-color-white lrv-u-padding-tb-1 u-border-radius-tr-5 u-border-radius-tl-5';
o_tab_vip.c_logo               = null;

const o_tab_vip__mobile               = clonedeep( require( '../../objects/o-tab/o-tab.prototype' ) );
o_tab_vip__mobile.c_icon              = c_icon_vip;
o_tab_vip__mobile.c_span              = null;
o_tab_vip__mobile.o_tab_url           = '#print-plus-shop-panel-vip';
o_tab_vip__mobile.o_tab_link_classes += ' js-TabsToggle is-active';
o_tab_vip__mobile.o_tab_classes       = 'lrv-u-text-decoration-none lrv-u-background-color-white lrv-u-padding-tb-1 u-border-radius-tr-5 u-border-radius-tl-5';

module.exports = {
	print_plus_shop_offer_header_classes: 'font-family-secondary-fancy lrv-u-width-100p ' +
		'u-background-color-brand-accent-100-b u-min-height-100 lrv-u-color-white lrv-u-text-transform-uppercase ' +
		'lrv-u-font-weight-bold lrv-u-text-align-center lrv-u-padding-t-2 lrv-u-font-size-32 ',
	print_plus_shop_header_tabs_classes: 'a-hidden@mobile-max lrv-a-grid lrv-a-cols2 u-margin-t-2 lrv-u-margin-lr-auto u-max-width-1000',
	print_plus_shop_header_tabs_classes__mobile: 'a-hidden@tablet lrv-a-grid lrv-a-cols2 u-margin-t-1 lrv-u-margin-lr-auto lrv-u-width-300 ',
	c_heading_primary: c_heading_primary,
	o_tab_variety: o_tab_variety,
	o_tab_vip: o_tab_vip,
	o_tab_variety__mobile: o_tab_variety__mobile,
	o_tab_vip__mobile: o_tab_vip__mobile
};
