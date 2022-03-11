const clonedeep = require( 'lodash.clonedeep' );

const c_lazy_image_prototype = require( '@penskemediacorp/larva-patterns/components/c-lazy-image/c-lazy-image.prototype.js' );
const c_lazy_image = clonedeep( c_lazy_image_prototype );

const o_indicator_prototype = require( '../o-indicator/o-indicator.prototype.js' );
const o_indicator = clonedeep( o_indicator_prototype );

const c_title_prototype = require( '@penskemediacorp/larva-patterns/components/c-title/c-title.prototype.js' );
const c_title = clonedeep( c_title_prototype );

const c_dek_prototype = require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype.js' );
const c_dek = clonedeep( c_dek_prototype );

c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/375x575';
c_lazy_image.c_lazy_image_src_url = 'https://source.unsplash.com/random/375x575';
c_lazy_image.c_lazy_image_srcset_attr = 'https://source.unsplash.com/random/923x539 2x, https://source.unsplash.com/random/375x575 1x';
c_lazy_image.c_lazy_image_crop_class = 'lrv-u-height-100p a-crop-16x9@mobile-max a-crop-923x539';
c_lazy_image.c_lazy_image_classes = 'lrv-a-glue@tablet lrv-a-glue--b-0 lrv-a-glue--l-0 lrv-a-glue--r-0 lrv-a-glue--t-0 a-overlay--b-t35p@tablet u-margin-lr-075@mobile-max u-margin-t-075@mobile-max u-margin-b-075@mobile-max u-padding-a-1@mobile-max';

o_indicator.o_indicator_classes = 'lrv-u-display-block lrv-u-text-align-center lrv-u-text-transform-uppercase u-font-family-accent u-font-size-18 lrv-u-margin-b-050';
o_indicator.c_span.c_span_link_classes = 'lrv-u-color-grey u-color-white@tablet lrv-u-color-brand-primary:hover u-letter-spacing-009';
o_indicator.c_span.c_span_url = '#';
o_indicator.c_span.c_span_text = 'Special Report';

c_title.c_title_url = '#';
c_title.c_title_classes = 'lrv-u-font-family-secondary lrv-u-font-family-primary@tablet lrv-u-text-align-center u-text-transform-uppercase@tablet u-font-weight-bold@mobile-max u-font-size-70@tablet u-font-weight-medium u-letter-spacing-2 u-line-height-1 u-font-size-28@mobile-max u-margin-b-1@mobile-max';
c_title.c_title_link_classes = 'lrv-u-color-black u-color-white@tablet lrv-u-display-block lrv-u-color-brand-primary:hover';
c_title.c_title_text = 'Sports Betting Sweeps the Nation';

c_dek.c_dek_text = 'As legalized sports gambling hits the one-year mark, the U.S. media business is in it to win it';
c_dek.c_dek_classes = 'lrv-u-color-black u-color-white@tablet lrv-u-font-family-secondary lrv-u-text-align-center u-font-size-18 u-letter-spacing-001 u-max-width-482 u-padding-lr-050@mobile-max';

module.exports = {
	o_top_story_classes: 'lrv-a-glue-parent a-crop-923x539@tablet',
	o_top_story_inner_classes: 'lrv-a-glue@tablet lrv-u-flex lrv-u-flex-direction-column lrv-u-align-items-center lrv-u-width-100p a-glue--b-212 a-glue--b-312@tablet u-padding-lr-150 u-padding-b-1@mobile-max margin-b-125@mobile-max',
	is_primary: false,
	c_lazy_image,
	o_indicator,
	c_title,
	c_dek
};
