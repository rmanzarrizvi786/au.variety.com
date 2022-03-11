const clonedeep = require( 'lodash.clonedeep' );

const c_title_prototype = require( '@penskemediacorp/larva-patterns/components/c-title/c-title.prototype.js' );
const c_title = clonedeep( c_title_prototype );

const o_tease_list_prototype = require( '@penskemediacorp/larva-patterns/objects/o-tease-list/o-tease-list.prototype.js' );
const o_tease_list = clonedeep( o_tease_list_prototype );

const o_tease_prototype = require( '@penskemediacorp/larva-patterns/objects/o-tease/o-tease.prototype.js' );
const o_tease = clonedeep( o_tease_prototype );

c_title.c_title_classes = 'lrv-u-font-size-32 lrv-u-line-height-copy';
c_title.c_title_url = false;
c_title.c_title_text = 'Must Read Stories';

o_tease.is_single = true;
o_tease.o_tease_classes += ' lrv-u-padding-tb-1';
o_tease.o_tease_url = '#';
o_tease.o_tease_link_classes += ' ';
o_tease.o_tease_primary_classes += ' ';
o_tease.o_tease_secondary_classes += ' a-counter-before a-counter__border-radius-tr-025 u-order-n1 lrv-u-margin-r-1';

o_tease.c_heading = false;
o_tease.c_tagline = false;

o_tease.c_title.c_title_text = 'Critic’s Guide to New Delhi: As India Art Fair Returns, the Best Shows in Town';
o_tease.c_title.c_title_classes = 'u-color-black lrv-u-font-size-16 lrv-u-font-weight-normal';

o_tease.c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-4x3';

o_tease_list.o_tease_list_classes += ' a-children-border-vertical a-children-border--grey-light a-counter';
o_tease_list.o_tease_list_item_classes = 'a-counter-increment';

o_tease_list.o_tease_list_items = [
	o_tease,
	o_tease,
	o_tease,
	o_tease,
	o_tease
];

module.exports = {
	trending_stories_widget_classes: 'lrv-u-font-family-primary lrv-u-padding-tb-050 u-border-tb-16 a-counter-config--brand-bottom-left',
	c_title: c_title,
	o_tease_list: o_tease_list
};
