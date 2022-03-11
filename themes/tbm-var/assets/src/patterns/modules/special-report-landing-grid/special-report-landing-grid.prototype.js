const clonedeep = require( 'lodash.clonedeep' );

const o_grid_story_prototype = require( '../../objects/o-grid-story/o-grid-story.prototype.js' );
const o_grid_story = clonedeep( o_grid_story_prototype );

o_grid_story.o_card.o_card_classes += ' lrv-u-padding-b-050 u-border-color-loblolly-grey';

special_report_landing_items = [
	o_grid_story,
	o_grid_story,
	o_grid_story,
	o_grid_story,
	o_grid_story,
	o_grid_story,
	o_grid_story,
	o_grid_story,
];

module.exports = {
	special_report_landing_content_classes: 'lrv-a-wrapper lrv-a-grid lrv-a-cols4@tablet u-padding-lr-2@mobile-max a-separator-b-1@mobile-max u-grid-gap-175 u-grid-gap-250@tablet u-margin-b-150@tablet',
	special_report_landing_items,
};
