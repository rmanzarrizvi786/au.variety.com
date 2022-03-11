const clonedeep = require( 'lodash.clonedeep' );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js' );
const c_heading = clonedeep( c_heading_prototype );

c_heading.c_heading_classes = 'lrv-u-font-family-secondary u-font-size-25 lrv-u-font-weight-bold u-letter-spacing-001';
c_heading.c_heading_text = 'Trending';

const c_icon_prototype = require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.prototype.js' );
const c_pmc_icon = clonedeep( c_icon_prototype );

// Call c_pmc_icon to prevent conflicts with c_icon
c_pmc_icon.c_icon_classes = 'lrv-u-margin-r-1 u-height-20 u-width-124';
c_pmc_icon.c_icon_name = 'pmc-logo';
c_pmc_icon.c_icon_link_screen_reader_text = 'PMC';

module.exports = {
	outbrain_widget_classes: 'u-border-t-6 u-border-color-brand-primary lrv-u-background-color-white lrv-u-margin-b-150 lrv-u-padding-t-025',
	heading_classes: 'lrv-u-flex u-margin-b-075 u-margin-b-2@tablet lrv-u-align-items-center',
	c_heading,
	c_pmc_icon,
	outbrain_widget_id_attr: 'HOP',
	outbrain_ob_template_attr: 'Variety',
	outbrain_script_url: '//widgets.outbrain.com/outbrain.js?ver=5.4-beta1-47314',
};
