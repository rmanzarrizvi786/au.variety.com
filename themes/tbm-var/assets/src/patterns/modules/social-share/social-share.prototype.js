const social_icon_link_classes = 'lrv-u-display-block u-display-inline-flex lrv-u-border-radius-50p lrv-a-unstyle-link lrv-u-padding-a-050 u-background-color-accent-c-100  u-color-pale-sky-2 lrv-u-border-a-1 u-border-color-map-accent-c-100 u-border-color-pale-sky-2:hover';

const icon_classes = 'u-width-14 u-height-14';

module.exports = {
	social_share_classes: '',
	social_share_prefix: false,
	social_share_prefix_classes: 'u-display-none@mobile-max lrv-u-color-grey lrv-u-font-size-10 lrv-u-text-transform-uppercase lrv-u-margin-r-1@tablet lrv-u-margin-b-050@mobile-max',
	social_share_prefix_text: 'Share',
	social_share_items_classes: 'lrv-a-space-children--1 lrv-a-space-children-horizontal',
	primary: [
		{
			"c_icon_link_classes": social_icon_link_classes,
			"c_icon_url": "#",
			"c_icon_rel_name": "facebook",
			"c_icon_name": "facebook",
			"c_icon_classes": icon_classes
		},
		{
			"c_icon_link_classes": social_icon_link_classes,
			"c_icon_url": "#",
			"c_icon_rel_name": "twitter",
			"c_icon_name": "twitter",
			"c_icon_classes": icon_classes
		},
		{
			"c_icon_link_classes": social_icon_link_classes,
			"c_icon_url": "#",
			"c_icon_rel_name": "pinterest",
			"c_icon_name": "pinterest",
			"c_icon_classes": icon_classes
		},
		{
			"c_icon_link_classes": social_icon_link_classes,
			"c_icon_url": "#",
			"c_icon_rel_name": "tumblr",
			"c_icon_name": "tumblr",
			"c_icon_classes": icon_classes
		}
	],
	secondary: [
		{
			"c_icon_link_classes": social_icon_link_classes,
			"c_icon_url": "#",
			"c_icon_rel_name": "reddit",
			"c_icon_name": "reddit",
			"c_icon_classes": icon_classes
		},
		{
			"c_icon_link_classes": social_icon_link_classes,
			"c_icon_url": "#",
			"c_icon_rel_name": "linkedin",
			"c_icon_name": "linkedin",
			"c_icon_classes": icon_classes
		},
	],
	plus_icon: {
		"c_icon_link_classes": social_icon_link_classes,
		"c_icon_url": "#",
		"c_icon_rel_name": "ellipsis",
		"c_icon_name": "ellipsis",
		"c_icon_classes": icon_classes
	}
};
