const clonedeep = require( 'lodash.clonedeep' );

const author_prototype = require( './author.prototype' );
const author = clonedeep( author_prototype );

author.c_icon.c_icon_classes = 'u-width-16 u-height-16 lrv-u-color-brand-primary';

const author_details_prototype = require( '../author-details/author-details.variety-vip.js' );
author.author_details = clonedeep( author_details_prototype );

author.c_link.c_link_classes = 'u-color-iron-grey';

author.author_classes = 'u-font-size-13 u-font-family-accent u-color-iron-grey';
author.author_byline_classes = 'u-font-family-accent u-color-iron-grey u-letter-spacing-003 lrv-u-margin-r-050 lrv-u-margin-r-1@tablet';
author.author_inner_classes = 'lrv-u-align-items-center lrv-u-flex';
author.author_content_classes = 'lrv-u-flex lrv-u-flex-direction-column lrv-u-width-100p u-margin-t-025@tablet lrv-u-justify-content-center';
author.author_toggle_classes = 'u-justify-content-center@mobile-max';
author.author_wrapper_classes = 'u-flex@tablet lrv-u-width-100p';

module.exports = {
	...author
};
