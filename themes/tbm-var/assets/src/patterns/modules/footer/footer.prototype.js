const clonedeep = require( 'lodash.clonedeep' );

const footer_prototype = require( '@penskemediacorp/larva-patterns/modules/footer/footer.prototype.js' );
const footer = clonedeep( footer_prototype );

const footer_menus_prototype = require( '../footer-menus/footer-menus.prototype.js' );
const footer_menus = clonedeep( footer_menus_prototype );

const c_lazy_image_prototype = require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype.js' );
const c_lazy_image = clonedeep( c_lazy_image_prototype );

const c_logo_prototype = require( '../../components/c-logo/c-logo.prototype.js' );
const c_logo = clonedeep( c_logo_prototype );

const c_tagline_prototype = require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype.js' );
const c_tagline = clonedeep( c_tagline_prototype );
const c_tagline_copyright = clonedeep( c_tagline_prototype );
const c_tagline_tip = clonedeep( c_tagline_prototype );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );
const c_link_subscribe = clonedeep( c_link_prototype );

const cxense_widget_prototype = require( '../cxense-widget/cxense-widget.prototype' );
const cxense_interstitial_widget = clonedeep( cxense_widget_prototype );

footer.footer_classes = 'lrv-u-padding-tb-1 lrv-u-padding-lr-1 u-background-color-accent-b';

footer.footer_menus = footer_menus;

c_lazy_image.c_lazy_image_classes = 'lrv-u-flex-shrink-0 lrv-u-margin-r-2 u-width-270 a-hidden@desktop-xl-max';
c_lazy_image.c_lazy_image_src_url = 'https://p200.p0.n0.cdn.getcloudapp.com/items/xQuvyK91/Screen+Shot+2019-11-22+at+3.30.48+PM.png?v=d5ff3b63226287ba91c337bba9b70812';
c_lazy_image.c_lazy_image_placeholder_url = 'https://p200.p0.n0.cdn.getcloudapp.com/items/xQuvyK91/Screen+Shot+2019-11-22+at+3.30.48+PM.png?v=d5ff3b63226287ba91c337bba9b70812';
c_lazy_image.c_lazy_image_crop_class = '';
c_lazy_image.c_lazy_image_link_url = '/subscribe-us/';
c_lazy_image.c_lazy_image_srcset_attr = 'https://p200.p0.n0.cdn.getcloudapp.com/items/xQuvyK91/Screen+Shot+2019-11-22+at+3.30.48+PM.png?v=d5ff3b63226287ba91c337bba9b70812 240w,https://p200.p0.n0.cdn.getcloudapp.com/items/xQuvyK91/Screen+Shot+2019-11-22+at+3.30.48+PM.png?v=d5ff3b63226287ba91c337bba9b70812 320w,https://p200.p0.n0.cdn.getcloudapp.com/items/xQuvyK91/Screen+Shot+2019-11-22+at+3.30.48+PM.png?v=d5ff3b63226287ba91c337bba9b70812 500w,https://p200.p0.n0.cdn.getcloudapp.com/items/xQuvyK91/Screen+Shot+2019-11-22+at+3.30.48+PM.png?v=d5ff3b63226287ba91c337bba9b70812 640w,https://p200.p0.n0.cdn.getcloudapp.com/items/xQuvyK91/Screen+Shot+2019-11-22+at+3.30.48+PM.png?v=d5ff3b63226287ba91c337bba9b70812 1024w';

c_logo.c_logo_screen_reader_text = "Variety";
c_logo.c_logo_classes = 'lrv-u-display-block lrv-u-margin-lr-auto lrv-u-color-white u-color-variety-primary:hover u-width-132@mobile-max u-width-150 lrv-u-margin-t-2';

c_tagline.c_tagline_classes = 'lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-text-transform-uppercase lrv-u-color-white u-letter-spacing-021 lrv-u-font-size-12 u-font-size-15@tablet lrv-u-text-align-center u-margin-tb-025';
c_tagline.c_tagline_text = 'The Business of Entertainment';

c_link_subscribe.c_link_classes = 'lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-display-block lrv-u-text-transform-uppercase u-letter-spacing-021 u-font-size-15@mobile-max lrv-u-font-size-18@tablet lrv-u-color-white:hover u-margin-b-050@tablet u-color-variety-primary';
c_link_subscribe.c_link_url = 'https://variety.com/subscribe-us/';
c_link_subscribe.c_link_text = 'Subscribe Today';

c_tagline_copyright.c_tagline_classes = 'lrv-u-color-white lrv-u-font-size-10 lrv-u-font-family-secondary lrv-u-text-align-center u-margin-tb-1@mobile-max u-margin-t-075';
c_tagline_copyright.c_tagline_text = '';
c_tagline_copyright.c_tagline_markup = '© Copyright 2019 Variety Media, LLC, a subsidiary of Penske Business Media, LLC. Variety and the Flying V logos are trademarks of Variety Media, LLC.<br>Powered by <a href="http://wordpress.com/" class="lrv-u-color-white u-color-variety-primary:hover">WordPress.com</a> VIP';

c_tagline_tip.c_tagline_classes = 'lrv-u-text-transform-uppercase lrv-u-font-family-secondary lrv-u-margin-t-1 lrv-u-margin-b-150 lrv-u-font-size-14 u-font-size-16@tablet lrv-u-margin-b-1@tablet';
c_tagline_tip.c_tagline_text = '';
c_tagline_tip.c_tagline_markup = '<a href="/tips" class="lrv-u-color-white u-color-variety-primary:hover"><strong>Have a News tip?</strong> Let us know</a>';

cxense_interstitial_widget.cxense_id_attr = 'cx-module-interstitial';

module.exports = {
		...footer,
		c_lazy_image,
		c_logo,
		c_tagline,
		c_link_subscribe,
		c_tagline_copyright,
		c_tagline_tip,
		cxense_interstitial_widget
};
