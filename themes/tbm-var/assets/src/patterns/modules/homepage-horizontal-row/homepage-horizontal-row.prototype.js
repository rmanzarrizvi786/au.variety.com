const clonedeep = require( 'lodash.clonedeep' );

const vertical_list_prototype = require( '../homepage-vertical-list/homepage-vertical-list.horizontal' );
const vertical_list_first     = clonedeep( vertical_list_prototype );

vertical_list_first.c_span = false;

vertical_list_first.o_tease_list_primary.o_tease_list_items.o_tease_list_classes = vertical_list_first.o_tease_list_primary.o_tease_list_classes.replace( 'u-border-b-1@desktop-xl', '' );

vertical_list_first.o_tease_list_primary.o_tease_list_classes = vertical_list_first.o_tease_list_primary.o_tease_list_classes.replace( 'u-border-r-1@tablet', '' );
vertical_list_first.o_tease_list_primary.o_tease_list_classes = vertical_list_first.o_tease_list_primary.o_tease_list_classes.replace( 'u-width-44p@tablet', 'u-width-100p@tablet' );
vertical_list_first.o_tease_list_primary.o_tease_list_classes = vertical_list_first.o_tease_list_primary.o_tease_list_classes.replace( 'lrv-u-padding-r-1@tablet', '' );

vertical_list_first.o_more_from_heading.c_heading.c_heading_classes = vertical_list_first.o_more_from_heading.c_heading.c_heading_classes.replace( 'a-font-accent-m','a-font-accent-s' );

vertical_list_first.o_more_from_heading.c_heading.c_heading_classes= vertical_list_first.o_more_from_heading.c_heading.c_heading_classes.replace( 'lrv-u-padding-tb-075','lrv-u-padding-b-025' );
vertical_list_first.o_more_from_heading.c_heading.c_heading_classes += ' u-letter-spacing-015-important ';

vertical_list_first.o_tease_list_primary.o_tease_list_items.map( ( item ) => {
	item.o_tease_classes = item.o_tease_classes.replace( 'u-border-b-1@desktop-xl','' );
	item.o_tease_classes = item.o_tease_classes.replace( 'lrv-u-border-b-1','' );
	item.o_tease_classes = item.o_tease_classes.replace( 'u-border-b-0@desktop','' );
	item.o_tease_classes = item.o_tease_classes.replace( 'u-padding-b-125','' );
	item.o_tease_classes = item.o_tease_classes.replace( 'lrv-u-padding-b-1','u-padding-b-150' );

	item.c_title.c_title_classes = item.c_title.c_title_classes.replace( 'u-min-height-36em@desktop-xl','' );
	item.c_title.c_title_classes = item.c_title.c_title_classes.replace( 'u-max-height-36em','' );
	item.c_title.c_title_classes += ' u-font-size-15 u-line-height-125 ';
} );

vertical_list_first.o_tease_list_secondary.o_tease_list_items.map( ( item ) => {
	item.o_tease_classes = item.o_tease_classes.replace( 'u-border-b-1@desktop-xl','' );
	item.o_tease_classes = item.o_tease_classes.replace( 'lrv-u-border-b-1','' );
	item.o_tease_classes = item.o_tease_classes.replace( 'u-border-b-0@desktop','' );
	item.o_tease_classes = item.o_tease_classes.replace( 'u-padding-t-075','' );
	item.o_tease_classes = item.o_tease_classes.replace( 'u-padding-b-125','lrv-u-padding-b-1' );
	item.o_tease_classes = item.o_tease_classes.replace( 'u-padding-b-1@tablet','' );
	item.o_tease_classes = item.o_tease_classes.replace( 'u-padding-b-150@desktop-xl', 'u-padding-b-150' );
	item.o_tease_classes = item.o_tease_classes.replace( 'lrv-u-padding-b-1', '' );
	item.o_tease_classes = item.o_tease_classes.replace( 'u-padding-t-1@desktop-xl', '' );
	item.o_tease_classes += ' u-padding-b-150 ';
	

	item.c_title.c_title_classes += ' lrv-u-font-weight-normal';

	item.c_title.c_title_classes = item.c_title.c_title_classes.replace( 'u-font-size-16@tablet','' );
	item.c_title.c_title_classes = item.c_title.c_title_classes.replace( 'u-line-height-120','u-line-height-125' );
	item.c_title.c_title_classes = item.c_title.c_title_classes.replace( 'u-padding-b-150@desktop-xl','' );
	item.c_title.c_title_classes = item.c_title.c_title_classes.replace( 'u-padding-b-1@tablet','' );
	item.c_title.c_title_classes = item.c_title.c_title_classes.replace( 'u-padding-t-1@desktop-xl','' );
	item.c_title.c_title_classes = item.c_title.c_title_classes.replace( 'u-padding-t-075@tablet','' );
	item.c_title.c_title_classes = item.c_title.c_title_classes.replace( 'u-min-height-36em','' );
	item.c_title.c_title_classes = item.c_title.c_title_classes.replace( 'u-max-height-36em','' );
	item.c_title.c_title_classes = item.c_title.c_title_classes.replace( 'u-margin-t-050@tablet','u-margin-t-00' );

} );

vertical_list_first.o_tease_list_secondary.o_tease_list_classes = vertical_list_first.o_tease_list_secondary.o_tease_list_classes.replace( /a-separator-b-1/g, '' );
vertical_list_first.o_tease_list_secondary.o_tease_list_classes = vertical_list_first.o_tease_list_secondary.o_tease_list_classes.replace( 'u-padding-l-1@tablet', '' );
vertical_list_first.o_tease_list_secondary.o_tease_list_classes = vertical_list_first.o_tease_list_secondary.o_tease_list_classes.replace( 'u-padding-lr-00@desktop-xl', 'u-padding-lr-00@tablet' );

vertical_list_first.vertical_list_classes = vertical_list_first.vertical_list_classes.replace( 'u-box-shadow-menu', '' );
vertical_list_first.vertical_list_classes = vertical_list_first.vertical_list_classes.replace( 'u-border-t-6', '' );
vertical_list_first.vertical_list_classes = vertical_list_first.vertical_list_classes.replace( 'u-padding-lr-075@mobile-max', '' );
vertical_list_first.vertical_list_classes = vertical_list_first.vertical_list_classes.replace( 'lrv-u-padding-lr-1@tablet', '' );
vertical_list_first.vertical_list_classes = vertical_list_first.vertical_list_classes.replace( 'lrv-u-background-color-white', '' );
vertical_list_first.vertical_list_classes = vertical_list_first.vertical_list_classes.replace( '  ', '' );

vertical_list_first.vertical_list_classes  += ' lrv-u-margin-tb-075';
vertical_list_first.vertical_list_inner_classes += ' u-padding-lr-075@mobile-max lrv-u-padding-lr-1@tablet u-border-r-1 u-border-color-brand-secondary-40 lrv-u-margin-b-2 ';
vertical_list_first.vertical_list_header_classes += ' u-padding-lr-075@mobile-max lrv-u-padding-lr-1@tablet';

vertical_list_first.vertical_list_inner_classes = vertical_list_first.vertical_list_inner_classes.replace( 'u-flex-direction-column@desktop-xl', 'u-flex-direction-column@tablet' );


vertical_list_first.o_more_link.c_link = null;

const vertical_list_second = clonedeep( vertical_list_first );
const vertical_list_third  = clonedeep( vertical_list_first );
const vertical_list_fourth = clonedeep( vertical_list_first );

vertical_list_fourth.vertical_list_inner_classes = vertical_list_fourth.vertical_list_inner_classes.replace( 'u-border-r-1', '' );

module.exports = {
	homepage_horizontal_row_classes : 'lrv-a-wrapper a-hidden@mobile-max ',
	homepage_horizontal_row_inner_classes : 'lrv-a-grid u-grid-gap-0 a-cols4@tablet u-border-t-6 lrv-u-background-color-white u-box-shadow-menu ',
	vertical_list_first,
	vertical_list_second,
	vertical_list_third,
	vertical_list_fourth,
};
