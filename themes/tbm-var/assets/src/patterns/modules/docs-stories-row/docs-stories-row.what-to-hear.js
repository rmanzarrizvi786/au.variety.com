const clonedeep  = require( 'lodash.clonedeep' );
const stories = clonedeep( require( './docs-stories-row.prototype' ) );

stories.stories_row_classes = 'lrv-a-wrapper';
stories.stories_row_items.map( item => {
	item.o_card_classes = 'lrv-u-flex lrv-u-flex-direction-column u-border-t-1@mobile-max u-padding-t-075@mobile-max u-border-color-loblolly-grey';
	item.c_title.c_title_classes = 'a-font-secondary-bold lrv-u-font-size-18@mobile-max lrv-u-font-size-16 lrv-u-font-size-18@desktop u-line-height-120@desktop lrv-u-margin-t-075 u-color-brand-secondary-50:hover';
	item.c_dek.c_dek_classes = 'lrv-u-font-family-secondary lrv-u-font-size-14@tablet u-line-height-140 u-font-size-16@desktop u-line-height-135@desktop lrv-u-margin-t-050@mobile-max lrv-u-margin-b-00 u-margin-t-050@tablet';
});

module.exports = stories;
