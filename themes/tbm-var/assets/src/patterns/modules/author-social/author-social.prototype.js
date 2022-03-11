const clonedeep = require( 'lodash.clonedeep' );

const c_sponsored = require( '../../components/c-sponsored/c-sponsored.prototype' );

const author_prototype = require( '../author/author.prototype' );
const author = clonedeep( author_prototype );

const social_share_prototype = require( '../social-share/social-share.prototype' );
const social_share = clonedeep( social_share_prototype );

const o_comments_link_prototype = require( '../../objects/o-comments-link/o-comments-link.prototype' );
const o_comments_link = clonedeep( o_comments_link_prototype );

o_comments_link.c_link.c_link_classes += ' lrv-u-padding-t-050@tablet u-margin-r-125@tablet';
o_comments_link.o_comments_link_classes += ' lrv-u-flex-grow-1 u-flex-grow-0@tablet lrv-u-margin-t-1@mobile-max';

module.exports = {
	author_social_classes: '',
	author_social_share_desktop_classes: 'lrv-u-flex u-justify-content-end lrv-u-align-items-center lrv-u-margin-b-050',
	author_social_share_mobile_classes: '',
	author_social_timestamp_classes: '',
	c_sponsored,
	author,
	o_comments_link,
	social_share,
	c_timestamp: false,
	c_tagline: false,
};
