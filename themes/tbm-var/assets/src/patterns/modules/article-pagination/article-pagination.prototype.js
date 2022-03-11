const clonedeep = require( 'lodash.clonedeep' );

const more_stories_button_prototype = require( '../more-stories-button/more-stories-button.prototype' );
const more_stories_button = clonedeep( more_stories_button_prototype );

more_stories_button.c_button = false;

more_stories_button.c_button_prev.c_button_text = 'Page 1';
more_stories_button.c_button_prev.c_button_rel_attr = 'prev';

more_stories_button.c_button_next.c_button_text = 'Page 2';
more_stories_button.c_button_next.c_button_rel_attr = 'next';

const c_tagline_prototype = require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype' );
const c_tagline = clonedeep( c_tagline_prototype );

c_tagline.c_tagline_classes = 'lrv-u-font-family-body lrv-u-font-size-18';
c_tagline.c_tagline_text = false;
c_tagline.c_tagline_markup = '<p><a href="" rel="canonical">Article Title</a>, Page 1 of 3</p>';

module.exports = {
	c_tagline: c_tagline,
	more_stories_button: more_stories_button,
};
