const clonedeep = require( 'lodash.clonedeep' );

const c_tagline_prototype = require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype' );
const c_tagline = clonedeep( c_tagline_prototype );

const c_title_prototype = require( '@penskemediacorp/larva-patterns/components/c-title/c-title.prototype' );
const c_title = clonedeep( c_title_prototype );

const c_icon_prototype = require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype' );
const c_icon = clonedeep( c_icon_prototype );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' );

const author_details_item_prototype = require( '../author-details-item/author-details-item.prototype' );
const author_details_item = Object.assign( {}, author_details_item_prototype );

const c_icon_more = clonedeep( c_icon_prototype );

const stories = [
		author_details_item,
		author_details_item,
		author_details_item,
];

c_title.c_title_classes = 'u-font-size-21 lrv-u-font-family-secondary';
c_title.c_title_url = '#';
c_title.c_title_text = 'Kevin Tran';
c_title.c_title_link_classes = 'u-color-pale-sky-2 u-color-black:hover';

c_tagline.c_tagline_classes = 'lrv-u-margin-tb-00 u-font-size-15 u-color-brand-secondary-50';
c_tagline.c_tagline_text = 'Data Reporter';

c_icon.c_icon_name = 'twitter';
c_icon.c_icon_classes = 'lrv-u-margin-lr-025 u-color-pale-sky-2 u-width-12 u-height-12';
c_icon.c_icon_url = false;

c_link_twitter_profile = clonedeep( c_link_prototype );
c_link_twitter_profile.c_link_classes = 'lrv-u-font-size-10 u-color-pale-sky-2 u-color-black:hover u-font-family-basic lrv-a-icon-before a-icon-twitter-blue-basic';
c_link_twitter_profile.c_link_text = '@Deadline';
c_link_twitter_profile.c_link_url = '#';
c_link_twitter_profile.c_link_rel_attr = 'noopener';
c_link_twitter_profile.c_link_target_attr = '_blank';

c_link_view_all = clonedeep( c_link_prototype );
c_link_view_all.c_link_classes = 'lrv-a-unstyle-link lrv-u-text-transform-uppercase lrv-u-font-weight-normal lrv-u-font-size-10 u-color-brand-secondary-50 u-color-black:hover u-font-family-basic u-letter-spacing-006 u-margin-t-025 u-text-decoration-underline:hover';
c_link_view_all.c_link_text = 'See All';
c_link_view_all.c_link_url = '#';

c_link_author = clonedeep( c_link_prototype );
c_link_author.c_link_text = 'David Robb';
c_link_author.c_link_url = '#';

c_icon_more.c_icon_name = 'long-arrow';
c_icon_more.c_icon_classes = 'lrv-u-margin-l-050 u-width-16 u-height-16 lrv-u-color-black u-background-color-brand-secondary-20';

module.exports = {
	author_details_classes: 'u-background-color-accent-c-40 u-border-t-6 u-border-color-brand-secondary-40 u-padding-t-075 lrv-a-glue-parent u-box-shadow-variety-author',
	author_details_list_text: 'Latest',
	author_details_list_title_classes: 'lrv-u-border-b-1 u-font-family-basic lrv-u-text-transform-uppercase lrv-u-font-weight-normal lrv-u-letter-spacing-006 lrv-u-font-size-10 u-border-color-brand-secondary-40',
	c_title: c_title,
	c_tagline: c_tagline,
	c_icon_twitter: c_icon,
	c_link_twitter_profile: c_link_twitter_profile,
	c_link_view_all: c_link_view_all,
	stories,
	c_icon_more,
	c_icon_close: '',
};
