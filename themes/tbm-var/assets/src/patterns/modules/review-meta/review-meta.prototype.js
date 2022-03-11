const clonedeep = require( 'lodash.clonedeep' );

const o_meta_list_prototype = require( '../../objects/o-meta-list/o-meta-list.prototype' );
const o_meta_list = clonedeep( o_meta_list_prototype );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' );
const c_heading = clonedeep( c_heading_prototype );

const c_title = clonedeep( c_heading_prototype );
c_title.c_heading_classes = 'lrv-u-font-family-secondary u-color-black lrv-u-font-size-14 u-font-size-15@tablet lrv-u-margin-b-1';
c_title.c_heading_text = 'Film Review: \'Proud Mary\'';

c_heading.c_heading_classes = 'lrv-u-font-family-secondary u-color-black lrv-u-font-size-14 u-font-size-15@tablet lrv-u-margin-b-1';
c_heading.c_heading_text = 'Reviewed at Arclight Cinemas, Hollywood, Nov. 21, 2019.';

module.exports = {
	review_meta_classes: 'u-padding-t-150 lrv-u-padding-b-050 u-border-tb-1 u-border-color-loblolly-grey u-margin-b-150 u-margin-t-150 u-margin-b-2@tablet u-margin-t-2@tablet',
	c_title,
	c_heading,
	o_meta_list,
};
