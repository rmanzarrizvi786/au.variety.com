const clonedeep = require( 'lodash.clonedeep' );

const o_card_prototype = require( '@penskemediacorp/larva-patterns/objects/o-card/o-card.prototype.js' );
const o_card = clonedeep( o_card_prototype );

const c_tagline_prototype = require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype.js' );
const c_tagline = clonedeep( c_tagline_prototype );

const c_dek_prototype = require( '@penskemediacorp/larva-patterns/components/c-dek/c-dek.prototype.js' );
const c_dek = clonedeep( c_dek_prototype );

const { c_lazy_image, c_title } = o_card;

o_card.o_card_classes = 'lrv-u-display-flex lrv-u-flex-direction-column';
o_card.o_card_content_classes = 'lrv-u-flex lrv-u-flex-direction-column lrv-u-text-align-center';

o_card.c_span = null;
o_card.c_timestamp = null;

c_lazy_image.c_lazy_image_classes = 'lrv-u-display-block u-box-shadow-small-medium';
c_lazy_image.c_lazy_image_crop_class = 'a-crop-35x27';
c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/315x243';
c_lazy_image.c_lazy_image_link_url = '#singleUrl';

c_title.c_title_classes = 'lrv-u-font-family-body lrv-u-font-weight-normal lrv-u-margin-b-1 lrv-u-padding-lr-1 u-font-size-30 u-line-height-120 u-margin-t-125';
c_title.c_title_link_classes = 'lrv-u-color-black lrv-u-display-block lrv-u-color-brand-primary:hover';
c_title.c_title_text = 'Where Blockchain Fits in Media';
c_title.c_title_url = '#singleUrl';

c_tagline.c_tagline_classes = 'c-tagline--author lrv-u-font-family-secondary lrv-u-font-weight-bold lrv-u-margin-b-025 lrv-u-text-transform-uppercase u-color-silver-chalice u-font-size-15 u-letter-spacing-002';
c_tagline.c_tagline_markup = 'By <a href="#author_url" class="u-color-silver-chalice">Kaare Eriksen</a>';
c_tagline.c_tagline_text = '';

c_dek.c_dek_classes = 'lrv-u-font-family-secondary lrv-u-font-size-16 u-font-size-15@tablet u-line-height-140 u-margin-b-0@tablet u-order-1';
c_dek.c_dek_text = '“The claim needs to be predicated on an allegation that the other party copied the expression of the idea — it can’t merely be the idea itself,” said USC preofessor Jonathan Barnett.';

o_card.c_tagline = c_tagline;
o_card.c_dek = c_dek;

module.exports = {
	o_card,
};
