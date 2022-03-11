const clonedeep = require( 'lodash.clonedeep' );

const mega_menu_item_prototype = require( '../mega-menu-item/mega-menu-item.prototype' );

const mega_menu_content_items = [];
const menuLinks = [ 'TV', 'Film', 'Music', 'Awards', 'Video', 'Dirt', 'Digital', 'More' ];

for ( let item of menuLinks ) {
	let mega_menu_item = clonedeep( mega_menu_item_prototype );

	mega_menu_item.c_link.c_link_text = item;

	mega_menu_content_items.push( mega_menu_item );
}

module.exports = {
	mega_menu_content_items,
};