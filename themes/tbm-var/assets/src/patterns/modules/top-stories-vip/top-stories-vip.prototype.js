const clonedeep = require( 'lodash.clonedeep' );

const o_top_story_prototype = require( '../../objects/o-top-story/o-top-story.prototype.js' );
const o_top_story_primary = clonedeep( o_top_story_prototype );
const o_top_story_secondary_prototype = require( '../../objects/o-top-story/o-top-story.secondary.variety-vip.js' );
const o_top_story_secondary = clonedeep( o_top_story_secondary_prototype );

const o_tease_list_prototype = require( '../../objects/o-tease-list/o-tease-list.homepage.variety-vip.js' );
const o_tease_list = clonedeep( o_tease_list_prototype );

o_top_story_primary.is_primary = true;
o_top_story_primary.o_top_story_classes += ' lrv-u-display-block lrv-u-width-100p lrv-a-span2@tablet';

const top_stories = [
	o_top_story_primary,
	o_top_story_secondary,
	o_top_story_secondary
];

module.exports = {
	top_stories_classes: 'lrv-a-grid lrv-a-cols3@tablet u-grid-gap-025 u-margin-lr-n050',
	top_stories_secondary_classes: 'lrv-u-flex lrv-u-flex-direction-column lrv-u-height-100p lrv-u-justify-content-space-between a-hidden@mobile-max',
	top_stories,
	o_tease_list
};
