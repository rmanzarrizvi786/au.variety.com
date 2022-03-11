const clonedeep = require( 'lodash.clonedeep' );

const o_card_prototype = require( '@penskemediacorp/larva-patterns/objects/o-card/o-card.prototype' );
const o_card = clonedeep( o_card_prototype );

const {
	c_lazy_image,
	c_title,
	c_span,
} = o_card;

o_card.o_card_classes = 'u-width-245 u-width-190@tablet u-width-250@desktop-xl';
o_card.o_card_content_classes = 'lrv-u-flex lrv-u-flex-direction-column';
o_card.c_timestamp = null;

c_lazy_image.c_lazy_image_placeholder_url = 'https://source.unsplash.com/random/245x140';
c_lazy_image.c_lazy_image_link_url = '#playlist';

c_title.c_title_classes = 'lrv-u-padding-t-025 u-order-n1 lrv-u-padding-t-050@tablet';
c_title.c_title_text = 'Academy Awards';
c_title.c_title_url = '#playlist';
c_title.c_title_link_classes = 'lrv-u-color-white lrv-u-font-family-secondary lrv-u-font-weight-bold u-font-size-15 u-font-size-16@tablet lrv-u-display-block';

c_span.c_span_text = '24 Videos';
c_span.c_span_classes = 'lrv-u-font-size-10 u-color-brand-secondary-60 u-font-family-basic lrv-u-font-size-14@tablet u-margin-t-025@tablet';

module.exports = {
	...o_card,
};
