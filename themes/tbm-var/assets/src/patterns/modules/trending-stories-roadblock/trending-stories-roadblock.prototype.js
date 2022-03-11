const clonedeep = require( 'lodash.clonedeep' );

const o_card_prototype = require( '@penskemediacorp/larva-patterns/objects/o-card/o-card.prototype' );
const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' );
const c_icon_prototype = require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype' );

const o_card = clonedeep( o_card_prototype );
const c_heading = clonedeep( c_heading_prototype );
const c_icon = clonedeep( c_icon_prototype );

o_card.o_card_classes += ' js-SliderItem a-counter-increment';

o_card.c_lazy_image.c_lazy_image_classes += ' a-counter-before a-counter__border-radius-tr-025 lrv-u-font-family-secondary lrv-u-margin-b-1';
o_card.c_lazy_image.c_lazy_image_crop_class = 'lrv-a-crop-2x3';

o_card.c_span = false;
o_card.c_timestamp = false;

o_card.c_title.c_title_text = '‘To Watch Its Devastation Is Excruciating’: Notre-Dame Cathedral';
o_card.c_title.c_title_classes = 'lrv-u-font-weight-normal lrv-u-font-size-14 lrv-u-font-size-20@desktop lrv-u-font-family-primary';
o_card.c_title.c_title_link_classes = 'u-color-black';

c_heading.c_heading_text = 'Must Read Stories';
c_heading.c_heading_classes = 'lrv-u-font-family-secondary lrv-u-font-size-28 u-font-size-40@tablet lrv-u-font-size-46@desktop-xl';

c_icon.c_icon_name = 'arrow';
c_icon.c_icon_url = false;
c_icon.c_icon_classes = 'lrv-u-display-block lrv-u-width-100p lrv-u-height-100p lrv-u-padding-a-050';

module.exports = {
	c_heading: c_heading,
	c_icon: c_icon,
	stories: [
		o_card,
		o_card,
		o_card,
		o_card,
		o_card,
		o_card,
		o_card,
	]
};
