const clonedeep = require( 'lodash.clonedeep' );

const o_player_prototype = require( '../../objects/o-player/o-player.prototype' );
const o_player = clonedeep( o_player_prototype );

const o_figcaption_prototype = require( '../../objects/o-figcaption/o-figcaption.prototype' );
const o_figcaption = clonedeep( o_figcaption_prototype );

o_figcaption.c_figcaption.c_figcaption_caption_markup = 'Editor video description casting comes after Jonah Hill turning down an offer to join the cast. Insiders believe WB already had an offer ready to go out to Dano once Hill passed on the role.';
o_figcaption.c_figcaption.c_figcaption_credit_text = 'Video: Youtube/Mark Anthony';
o_figcaption.c_figcaption.c_figcaption_classes += ' lrv-u-border-b-1 u-border-color-brand-secondary-40 u-padding-lr-125@mobile-max';

module.exports = {
	featured_video_classes: 'u-margin-b-225 u-margin-b-150@mobile-max',
	o_player,
	o_figcaption,
};
