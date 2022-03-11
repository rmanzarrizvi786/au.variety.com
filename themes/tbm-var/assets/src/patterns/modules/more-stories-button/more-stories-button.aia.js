const clonedeep = require( 'lodash.clonedeep' );
const more_stories_button_prototype = require( './more-stories-button.prototype' );

const more_stories_button_aia = clonedeep( more_stories_button_prototype );

more_stories_button_aia.more_stories_button_classes = 'lrv-u-font-family-secondary';

more_stories_button_aia.c_button.c_button_classes = 'lrv-a-unstyle-button lrv-u-cursor-pointer u-padding-t-075 lrv-u-padding-b-050 lrv-u-padding-lr-1 lrv-u-background-color-brand-primary lrv-u-color-white lrv-u-color-white:hover lrv-u-text-align-center lrv-u-font-size-28 u-background-color-brand-primary-dark:hover lrv-u-display-block lrv-a-icon-after a-icon-arrow-right-fancy u-width-300';
more_stories_button_aia.c_button.c_button_url = '#';

more_stories_button_aia.c_button_prev.c_button_classes = 'lrv-a-unstyle-button lrv-u-cursor-pointer u-padding-t-075 lrv-u-padding-b-050 lrv-u-padding-lr-1 lrv-u-background-color-brand-primary lrv-u-color-white lrv-u-color-white:hover lrv-u-text-align-center lrv-u-font-size-28 u-background-color-brand-primary-dark:hover lrv-a-icon-arrow-left lrv-a-icon-invert lrv-a-icon-before lrv-u-width-300 u-width-150@mobile-max lrv-u-font-size-18@mobile-max lrv-u-justify-content-center';

more_stories_button_aia.c_button_next.c_button_classes = 'lrv-a-unstyle-button lrv-u-cursor-pointer u-padding-t-075 lrv-u-padding-b-050 lrv-u-padding-lr-1 lrv-u-background-color-brand-primary lrv-u-color-white lrv-u-color-white:hover lrv-u-text-align-center lrv-u-font-size-28 u-background-color-brand-primary-dark:hover lrv-a-icon-arrow-right lrv-a-icon-invert lrv-a-icon-after lrv-u-width-300 u-width-150@mobile-max lrv-u-font-size-18@mobile-max lrv-u-justify-content-center';

module.exports = more_stories_button_aia;
