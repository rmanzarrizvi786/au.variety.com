const clonedeep = require( 'lodash.clonedeep' );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype' );
const c_link__parent = clonedeep( c_link_prototype );

c_link__parent.c_link_classes = 'lrv-a-unstyle-link lrv-u-color-black lrv-u-width-100p u-color-brand-secondary-30@tablet lrv-u-font-weight-bold lrv-u-font-family-secondary lrv-u-font-size-18 u-font-size-24@tablet u-color-brand-primary-40:hover@tablet';

const menuLinks = [ 'News', 'Reviews', 'Podcasts', 'Box Office', 'Events', 'Columns' ];
const childLinks = [];

for ( item of menuLinks ) {
	let c_link__child = clonedeep( c_link_prototype );

	c_link__child.c_link_text = item;
	c_link__child.c_link_classes = 'lrv-a-unstyle-link lrv-u-color-black u-padding-l-250 u-padding-l-00@tablet lrv-u-padding-tb-050@tablet lrv-u-display-block u-color-brand-secondary-30@tablet u-color-brand-primary-40:hover@tablet';

	childLinks.push( c_link__child );
}

module.exports = {
	mega_menu_parent_list_item_classes: 'lrv-js-MobileHeightToggle lrv-u-padding-b-050',
	mega_menu_parent_list_item_inner_classes: 'lrv-u-flex lrv-u-border-b-1 u-border-color-iron-grey lrv-u-padding-b-050 u-padding-b-025@tablet',
	mega_menu_children_list_classes: 'lrv-js-MobileHeightToggle-target lrv-a-unstyle-list lrv-u-padding-t-050 u-padding-t-075@tablet u-margin-b-150@tablet',
	mega_menu_children_list_item_classes: 'lrv-u-font-size-18 lrv-u-font-family-secondary lrv-u-margin-b-025 u-font-family-body@tablet u-font-size-15@tablet',
	mega_menu_toggle_button_classes: 'lrv-js-MobileHeightToggle-trigger lrv-a-unstyle-button u-display-inline-flex lrv-u-margin-l-00 lrv-u-cursor-pointer a-stacking-context a-hidden@tablet lrv-a-icon-before a-icon-down-caret-thin',
	c_link: c_link__parent,
	mega_menu_item_children: childLinks,
};
