const o_nav_prototype = require( '@penskemediacorp/larva-patterns' ).o_nav;
const o_nav = Object.assign( {}, o_nav_prototype );

const c_link_prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );

o_nav.o_nav_classes = 'o-nav lrv-u-border-b-1 u-border-color-brand-secondary-40 lrv-u-border-t-1 lrv-u-text-align-center lrv-u-padding-b-025 u-padding-t-050@desktop u-padding-t-050@tablet u-padding-t-050@mobile-max lrv-u-margin-t-2 lrv-u-margin-b-2';
o_nav.o_nav_title_text = 'Read More About:';
o_nav.o_nav_title_classes = 'lrv-u-color-black lrv-u-font-family-secondary o-nav__title a-content-ignore u-line-height-140 u-letter-spacing-0002';
o_nav.o_nav_list_items = [];

const menuLinks = [ 'Art Gallery,', 'Retrospective,', 'Mapplethorpe' ];

for (let i = 0; i < menuLinks.length; i++) {
	let c_link = Object.assign( {}, c_link_prototype );

	c_link.c_link_text = menuLinks[i];
	c_link.c_link_classes += ' a-content-ignore lrv-u-color-black u-font-family-secondary lrv-u-whitespace-nowrap u-color-action-blue:hover u-font-size-18@desktop-xl u-font-size-18@tablet u-letter-spacing-0002';

	o_nav.o_nav_list_items.push( c_link );
}

o_nav.o_nav_list_classes += " lrv-u-flex lrv-a-space-children-horizontal lrv-a-space-children--050 lrv-u-align-items-center lrv-u-flex-wrap-wrap lrv-u-justify-content-center";

module.exports = {
	article_tags_classes: 'a-children-icon-bullet lrv-u-font-family-primary u-letter-spacing-012 lrv-u-line-height-large lrv-u-color-brand-primary',
	o_nav: o_nav,
};
