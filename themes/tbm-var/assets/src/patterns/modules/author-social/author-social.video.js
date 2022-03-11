const clonedeep = require( 'lodash.clonedeep' );

const author_social_prototype = require( './author-social.prototype' );
const author_social = clonedeep( author_social_prototype );

author_social.o_comments_link.o_comments_link_classes = author_social.o_comments_link.o_comments_link_classes.replace( 'lrv-u-margin-t-1@mobile-max', 'lrv-u-margin-t-050@mobile-max' );

module.exports = {
	...author_social,
};
