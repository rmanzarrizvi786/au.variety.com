const clonedeep = require('lodash.clonedeep');
const author_social = clonedeep(require('../author-social/author-social.prototype'));
const c_tagline = clonedeep(require('@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype'));

author_social.o_comments_link = false;

author_social.author_social_classes = 'lrv-u-text-align-center lrv-u-font-family-secondary';
author_social.author.is_byline_only = true;
author_social.author.c_tagline.c_tagline_markup = '<a href="">Hi</a>';
author_social.author.author_classes = 'lrv-u-margin-t-1 lrv-u-margin-b-2';
author_social.author.author_byline_classes = 'lrv-u-text-align-center lrv-u-font-weight-bold a-inner-links--currentColor';
author_social.author.author_inner_classes = 'lrv-u-margin-t-1';
author_social.author.author_wrapper_classes = 'lrv-u-text-align-center';
author_social.author.author_content_classes = '';

author_social.author.c_tagline_optional = c_tagline;
author_social.author.c_tagline_optional.c_tagline_classes = 'lrv-u-margin-t-00 lrv-u-padding-t-025';
author_social.author.c_tagline_optional.c_tagline_text = 'Photos by Angelina Jolie';
author_social.author_social_share_desktop_classes = 'lrv-u-flex lrv-u-justify-content-center lrv-u-margin-b-050 lrv-u-margin-t-1 lrv-u-margin-t-2@desktop';

module.exports = author_social;
