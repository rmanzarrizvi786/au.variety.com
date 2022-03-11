const clonedeep = require( 'lodash.clonedeep' );

const top_stories = clonedeep( require( './top-stories.prototype' ) );

top_stories.c_heading = false;

top_stories.o_story_first.c_dek = false;
top_stories.o_story_sixth = false;

top_stories.top_stories_classes = '';
top_stories.top_stories_stories_classes = 'u-grid-template-rows-auto';

top_stories.o_latest_news_link = false;

module.exports = top_stories;
