const clonedeep = require( 'lodash.clonedeep' );

const special_report_landing_top_prototype = require( './special-report-landing-top.prototype' );
const special_report_landing_top = clonedeep( special_report_landing_top_prototype );

const {
	o_top_story,
} = special_report_landing_top;

o_top_story.o_top_story_classes = o_top_story.o_top_story_classes.replace( 'a-crop-923x539', 'a-crop-1400x663' );
o_top_story.c_lazy_image.c_lazy_image_crop_class = o_top_story.c_lazy_image.c_lazy_image_crop_class.replace( 'a-crop-923x539', 'a-crop-1400x663' );
o_top_story.c_dek.c_dek_classes = o_top_story.c_dek.c_dek_classes.replace( 'a-hidden@mobile-max', '' );
o_top_story.o_top_story_inner_classes = o_top_story.o_top_story_inner_classes.replace( 'a-glue--b-312@tablet', 'a-glue--b-125@tablet' );

module.exports = {
	...special_report_landing_top,
};
