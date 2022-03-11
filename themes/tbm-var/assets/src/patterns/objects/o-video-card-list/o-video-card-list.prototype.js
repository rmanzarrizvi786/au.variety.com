const clonedeep = require( 'lodash.clonedeep' );

const o_video_card_prototype = require( '../o-video-card/o-video-card.prototype.js' );
const o_video_card = clonedeep( o_video_card_prototype );

const o_video_card_list_items = [
	o_video_card,
	o_video_card,
	o_video_card,
	o_video_card,
];

module.exports = {
	modifier_class: 'lrv-a-unstyle-list',
	o_video_card_list_classes: '',
	o_video_card_list_item_classes: '',
	o_video_card_list_items,
};
