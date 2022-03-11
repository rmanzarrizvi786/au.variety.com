const clonedeep = require( 'lodash.clonedeep' );

const o_story = clonedeep( require( './o-story.prototype' ) );

const {
	c_lazy_image,
	c_dek,
	c_title,
} = o_story;

o_story.o_story_classes += ' u-flex-direction-column@tablet u-height-100p@tablet';

o_story.o_story_primary_classes += ' u-height-100p@tablet';
o_story.c_lazy_image.c_lazy_image_classes += ' a-hidden@tablet';

// TODO: c-lazy-image should support c-play-badge
o_story.c_lazy_image_badge_classes = ' a-hidden@tablet';

c_lazy_image.c_lazy_image_classes += ' lrv-u-padding-b-050';

c_title.c_title_classes += ' a-font-primary-regular-s@tablet';

c_dek.c_dek_classes += ' lrv-a-hidden';
c_dek.c_dek_text = '‘Stranger Things 3’ Is Most-Watched Season to Date, Netflix Says';

o_story.o_story_classes += ' o-story--secondary';
o_story.o_story_primary_classes = '';

module.exports = o_story;
