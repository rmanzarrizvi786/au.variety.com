const clonedeep = require( 'lodash.clonedeep' );

const o_tease_list = clonedeep( require( '@penskemediacorp/larva-patterns/objects/o-tease-list/o-tease-list.prototype' ) );
const cxense_static_toaster_widget = clonedeep( require( '../cxense-widget/cxense-widget.prototype' ) );

o_tease_list.o_tease_list_classes += ' lrv-a-grid lrv-a-cols4@tablet a-separator-r-1 a-separator-spacing--r-050 a-separator-spacing--r-0@desktop-xl u-grid-gap-1';
o_tease_list.o_tease_list_item_classes = 'lrv-u-border-color-grey-light';

cxense_static_toaster_widget.cxense_id_attr = 'cx-subscribe-to-vip-tease';
cxense_static_toaster_widget.cxense_widget_classes = 'lrv-u-flex-shrink-0 u-width-25p';


o_tease_list.o_tease_list_items.map( item => {
	item.o_tease_classes = 'lrv-u-flex';
	item.o_tease_secondary_classes += ' u-order-n1 u-margin-r-175 u-max-width-60 lrv-u-display-none u-display-block@desktop-xl';
	item.c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-1x1';
	item.c_heading = false;
	item.c_title.c_title_url = '#';
	item.c_title.c_title_link_classes = 'lrv-a-unstyle-link';
	item.c_title.c_title_classes = 'a-font-secondary-bold-s a-border-triangle-before';
	item.c_tagline.c_tagline_text = 'Here is a small tagline.';
	item.c_tagline.c_tagline_classes = 'a-font-secondary-regular-s lrv-u-margin-tb-00';
	item.c_tagline.c_tagline_markup = false;
} );

o_tease_list.o_tease_list_items.push( clonedeep( o_tease_list.o_tease_list_items[0] ) );

module.exports = {
	o_tease_list,
	cxense_static_toaster_widget,
};
