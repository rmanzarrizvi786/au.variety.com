
const clonedeep = require( 'lodash.clonedeep' );

const article_meta_prototype = require( '../article-meta/article-meta.variety-vip.js' );
const article_meta = clonedeep( article_meta_prototype );

const o_title_prototype = require( '../../objects/o-title/o-title.article-vip' );
const o_title = clonedeep( o_title_prototype );

const author_social_prototype = require( '../author-social/author-social.variety-vip.js' );
const author_social = clonedeep( author_social_prototype );

const o_figure_prototype = require( '@penskemediacorp/larva-patterns/objects/o-figure/o-figure.prototype.js' );
const o_figure = clonedeep( o_figure_prototype );

author_social.author_social_classes = 'lrv-a-glue-parent lrv-u-margin-b-1 u-margin-b-125@tablet';

o_figure.o_figure_classes = 'lrv-u-font-family-secondary u-color-brand-secondary-50 u-flex-order-n1@mobile-max lrv-u-margin-b-050@mobile-max';
o_figure.c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/831x468';
o_figure.c_figcaption.c_figcaption_classes = 'lrv-u-flex lrv-u-flex-direction-column lrv-u-margin-t-050 lrv-u-padding-tb-1 lrv-u-padding-t-050@tablet u-padding-lr-225@mobile-max';
o_figure.c_figcaption.c_figcaption_caption_markup = 'Willem Dafeo and Robert Pattinson in ‘The Lighthouse.’';
o_figure.c_figcaption.c_figcaption_caption_classes = 'lrv-u-font-size-14 lrv-u-padding-b-050 u-padding-b-1@tablet u-color-dusty-grey';
o_figure.c_figcaption.c_figcaption_credit_text = 'Courtesy of A24';
o_figure.c_figcaption.c_figcaption_credit_classes = 'lrv-u-border-t-1 lrv-u-padding-t-050 lrv-u-text-transform-uppercase u-font-size-9 u-letter-spacing-005 u-color-brand-secondary-50';

o_title.c_heading.c_heading_text = 'Sports Betting at One Year: Media Biz In It to Win It';

module.exports = {
		article_header_classes: 'lrv-u-flex lrv-u-flex-direction-column u-margin-t-2@tablet lrv-u-margin-lr-auto u-max-width-830',
		article_header_inner_classes: '',
		article_meta,
		o_title,
		author_social,
		o_figure
};
