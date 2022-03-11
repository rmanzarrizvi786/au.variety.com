const clonedeep = require( 'lodash.clonedeep' );

const article_meta_prototype = require( '../article-meta/article-meta.prototype.js' );
const article_meta = clonedeep( article_meta_prototype );

const o_title_prototype = require( '../../objects/o-title/o-title.article' );
const o_title = clonedeep( o_title_prototype );

const o_custom_paragraph_prototype = require( '../../objects/o-custom-paragraph/o-custom-paragraph.intro' );
const o_custom_paragraph = clonedeep( o_custom_paragraph_prototype );

const author_social_prototype = require( '../author-social/author-social.prototype.js' );
const author_social = clonedeep( author_social_prototype );

const o_figure_prototype = require( '@penskemediacorp/larva-patterns/objects/o-figure/o-figure.prototype.js' );
const o_figure = clonedeep( o_figure_prototype );

const o_figcaption_prototype = require( '../../objects/o-figcaption/o-figcaption.prototype' );
const o_figcaption = clonedeep( o_figcaption_prototype );

const dirt_details_prototype = require( '../../modules/dirt-details/dirt-details.prototype.js' );
const dirt_details = clonedeep( dirt_details_prototype );

o_title.c_heading.c_heading_text = 'Paul Dano Set To Play The Riddler in Matt Reeves and Warner Bros. The Batman';
o_custom_paragraph.o_custom_paragraph_text = 'Dano’s casting comes on the heels of Jonah Hill turning down an offer to join the cast. Insiders believe WB already had an offer ready to go out to Dano once Hill passed on the role.';

o_figure.o_figure_classes = 'lrv-u-font-family-secondary u-color-brand-secondary-50 lrv-u-border-b-1 u-border-color-brand-secondary-40  u-margin-b-225 u-margin-b-150@mobile-max';
o_figure.c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/831x468';
o_figure.c_figcaption.c_figcaption_classes = o_figcaption.c_figcaption.c_figcaption_classes + ' u-padding-lr-125@mobile-max';
o_figure.c_figcaption.c_figcaption_caption_markup = 'Dano’s casting comes after Jonah Hill turning down an offer to join the cast. Insiders believe WB already had an offer ready to go out to Dano once Hill passed on the role.';
o_figure.c_figcaption.c_figcaption_caption_classes = o_figcaption.c_figcaption.c_figcaption_caption_classes;
o_figure.c_figcaption.c_figcaption_credit_text = 'Photo: Getty Images/Matt LaFleur';
o_figure.c_figcaption.c_figcaption_credit_classes = o_figcaption.c_figcaption.c_figcaption_credit_classes;

module.exports = {
	article_header_classes: '',
	article_header_inner_classes: '',
	article_header_feature_classes: 'u-margin-lr-n050@mobile-max',
	article_meta,
	o_title,
	o_custom_paragraph,
	author_social,
	o_figure,
	dirt_details
};
