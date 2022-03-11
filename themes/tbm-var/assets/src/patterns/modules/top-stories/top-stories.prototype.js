const clonedeep = require( 'lodash.clonedeep' );

const c_heading = clonedeep( require( '../../components/c-heading/c-heading.accent-m' ) );
const o_story = clonedeep( require( '../../objects/o-story/o-story.prototype' ) );
const o_story_primary = clonedeep( require( '../../objects/o-story/o-story.primary' ) );
const o_story_secondary = clonedeep( require( '../../objects/o-story/o-story.secondary' ) );

const o_story_no_dek = clonedeep( o_story );

const o_latest_news_link = clonedeep( require( '../../objects/o-latest-news-link/o-latest-news-link.prototype' ) );

o_story_no_dek.c_dek = false;

module.exports = {
	c_heading,
	top_stories_classes: 'u-border-t-6@mobile-max u-padding-b-1@mobile-max u-border-color-picked-bluewood',
	top_stories_stories_classes: '',
	o_story_first: clonedeep( o_story_primary ),
	o_story_second: clonedeep( o_story_no_dek ),
	o_story_third: clonedeep( o_story_secondary ),
	o_story_fourth: clonedeep( o_story_secondary ),
	o_story_fifth: clonedeep( o_story_no_dek ),
	o_story_sixth: clonedeep( o_story ),
	o_latest_news_link
};
