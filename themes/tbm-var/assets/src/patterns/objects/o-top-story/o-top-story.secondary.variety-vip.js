const clonedeep = require( 'lodash.clonedeep' );

const o_top_story_prototype = require( './o-top-story.variety-vip.js' );
const o_top_story = clonedeep( o_top_story_prototype );

o_top_story.o_top_story_classes += ' lrv-u-display-block lrv-u-width-100p';
o_top_story.o_top_story_inner_classes = o_top_story.o_top_story_inner_classes.replace( 'a-glue--b-312@tablet', 'a-glue--b-150@tablet' );
o_top_story.c_dek.c_dek_classes = 'lrv-u-display-none';
o_top_story.c_title.c_title_text = 'The Dark Side of Film Financing';
o_top_story.c_title.c_title_classes = 'lrv-u-font-weight-normal lrv-u-text-align-center u-font-family-basic u-font-size-35 u-line-height-1';
o_top_story.o_indicator.c_span.c_span_text = 'Biz';

module.exports = {
	...o_top_story
};
