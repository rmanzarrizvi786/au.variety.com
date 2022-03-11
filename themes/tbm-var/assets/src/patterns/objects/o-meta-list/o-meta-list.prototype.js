const clonedeep = require( 'lodash.clonedeep' );

const o_meta_item_prototype = require( '../../components/c-meta-item/c-meta-item.prototype' );
const meta_item = clonedeep( o_meta_item_prototype );

meta_texts = [
	[ 'Production', 'A Universal Pictures release of a DreamWorks pictures.' ],
	[ 'Crew', 'Director: Sam Mendes. Screenplay: Sam Mendes.' ],
	[ 'With', 'Scarlet Johansson, Adam Driver.' ],
	[ 'Music By', 'Fil Eisler.' ]
];

const o_meta_list_items = [];

for ( item of meta_texts ) {
	let meta_item = clonedeep( o_meta_item_prototype );

	meta_item.meta_item_label_text = item[0];
	meta_item.meta_item_description_text = item[1];

	o_meta_list_items.push( meta_item );
}

module.exports = {
	o_meta_list_classes: 'lrv-u-font-family-secondary u-color-black lrv-u-font-size-14 u-font-size-15@tablet lrv-a-unstyle-list',
	o_meta_list_items_classes: 'lrv-u-margin-b-1',
	o_meta_list_items,
};
