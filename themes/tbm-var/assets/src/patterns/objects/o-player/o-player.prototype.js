const clonedeep = require( 'lodash.clonedeep' );

const c_play_icon_prototype = require( '../../components/c-play-badge/c-play-badge.prototype.js' );
const c_play_icon = clonedeep( c_play_icon_prototype );

c_play_icon.c_play_badge_classes = 'lrv-a-glue a-glue--a-50p u-transform-translate-a-n50p u-width-50 u-height-50 u-width-60@tablet u-height-60@tablet is-to-be-hidden';

module.exports = {
	"o_player_alt_attr": "Thumbnail of the embedded video",
	"o_player_image_url": "https://source.unsplash.com/random/595x333",
	o_player_trigger_data_attr: "<div class='embed-youtube'><iframe title='Spring - Blender Open Movie' width='500' height='281' src='https://www.youtube.com/embed/WhWc3b3KhnY?feature=oembed&autoplay=1' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen=''></iframe></div>",
	o_player_type_data_attr: "oembed",
	modifier_class: "",
	o_player_classes: "u-margin-r-1@tablet",
	o_player_crop_class: "lrv-a-crop-16x9 lrv-a-glue-parent c-play-badge-parent",
	o_player_image_classes: "is-to-be-hidden lrv-u-display-block",
	o_player_link_classes: 'lrv-u-cursor-pointer',
	c_play_icon,
};
