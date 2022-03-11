const clonedeep = require( 'lodash.clonedeep' );

const o_tease_prototype = require( '../o-tease/o-tease.homepage.variety-vip.js' );
const o_tease = clonedeep( o_tease_prototype );

const o_tease_list_prototype = require( './o-tease-list.variety-vip.js' );
const o_tease_list = clonedeep( o_tease_list_prototype );

o_tease_list.o_tease_list_classes += ' a-hidden@tablet a-separator-b-1 u-padding-lr-175 a-separator-t-1@mobile-max';
o_tease_list.o_tease_list_item_classes = 'u-border-color-brand-secondary-50';
o_tease_list.o_tease_list_items = [
	o_tease,
	o_tease,
];

module.exports = {
	...o_tease_list
};

