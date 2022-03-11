const clonedeep = require( 'lodash.clonedeep' );

const c_tagline_prototype = require( '@penskemediacorp/larva-patterns/components/c-tagline/c-tagline.prototype' );
const c_tagline = clonedeep( c_tagline_prototype );

const c_title_prototype = require( '@penskemediacorp/larva-patterns/components/c-title/c-title.prototype' );
const c_title = clonedeep( c_title_prototype );

const c_icon_prototype = require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype' );
const c_icon = clonedeep( c_icon_prototype );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' );

const author_details_item_prototype = require( '../author-details-item/author-details-item.variety-vip' );
const author_details_item = Object.assign( {}, author_details_item_prototype );

const c_icon_close = clonedeep( c_icon_prototype );

const stories = [
		author_details_item,
		author_details_item,
		author_details_item,
];

c_title.c_title_classes = 'lrv-u-font-size-15 lrv-u-font-size-18@tablet lrv-u-font-family-secondary';
c_title.c_title_url = '#';
c_title.c_title_text = 'Kevin Tran';
c_title.c_title_link_classes = 'u-color-brand-secondary';

c_tagline.c_tagline_classes = 'lrv-u-font-family-secondary lrv-u-margin-tb-00 lrv-u-font-size-14 u-color-brand-secondary-50 lrv-u-line-height-medium';
c_tagline.c_tagline_text = 'Data Reporter';

c_icon.c_icon_name = 'twitter';
c_icon.c_icon_classes = 'lrv-u-margin-lr-025 u-color-brand-secondary u-width-12 u-height-12';
c_icon.c_icon_url = false;

c_link_twitter_profile = clonedeep( c_link_prototype );
c_link_twitter_profile.c_link_classes = 'lrv-u-font-size-10 u-color-brand-secondary-50 u-font-family-accent lrv-a-icon-before a-icon-twitter-basic u-margin-t-025 u-margin-t-00@tablet';
c_link_twitter_profile.c_link_text = '@Deadline';
c_link_twitter_profile.c_link_url = '#';
c_link_twitter_profile.c_link_rel_attr = 'noopener';
c_link_twitter_profile.c_link_target_attr = '_blank';

c_link_view_all = clonedeep( c_link_prototype );
c_link_view_all.c_link_classes = 'lrv-a-unstyle-link lrv-u-margin-l-auto lrv-u-text-transform-uppercase lrv-u-font-weight-bold lrv-u-font-size-12 u-color-brand-secondary-50 lrv-u-font-family-secondary u-letter-spacing-012 lrv-a-icon-after a-icon-long-right-arrow lrv-u-margin-t-050 u-margin-t-075@tablet lrv-u-margin-b-050@mobile-max';
c_link_view_all.c_link_text = 'More Stories';
c_link_view_all.c_link_url = '#';

c_link_author = clonedeep( c_link_prototype );
c_link_author.c_link_text = 'David Robb';
c_link_author.c_link_url = '#';

c_icon_close.c_icon_name = 'up-caret';
c_icon_close.c_icon_classes = 'u-width-26 u-height-26 lrv-a-glue u-background-color-picked-bluewood lrv-u-color-white lrv-u-border-radius-50p lrv-u-border-a-1 u-padding-a-035 a-glue--l-50p a-glue--b-n13 u-transform-translateX-n50p lrv-u-cursor-pointer u-transform-nx-50p u-margin-t-n1';

module.exports = {
	author_details_classes: 'u-background-color-grey-lightest u-border-color-picked-bluewood u-border-t-6 lrv-a-glue-parent u-box-shadow-variety-expander',
	author_details_list_text: 'Latest',
	author_details_list_title_classes: 'lrv-u-border-b-1 u-font-family-accent lrv-u-text-transform-uppercase lrv-u-letter-spacing-012 lrv-u-font-size-12 u-border-color-loblolly-grey lrv-u-font-weight-normal',
	c_title: c_title,
	c_tagline: c_tagline,
	c_link_twitter_profile: c_link_twitter_profile,
	c_link_view_all: c_link_view_all,
	stories,
	c_icon_close
};
