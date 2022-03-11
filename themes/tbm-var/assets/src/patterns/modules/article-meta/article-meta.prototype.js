const clonedeep = require( 'lodash.clonedeep' );

const o_indicator_prototype = require( '../../objects/o-indicator/o-indicator.prototype.js' );
const o_indicator = clonedeep( o_indicator_prototype );

const c_timestamp_prototype = require( '@penskemediacorp/larva-patterns/components/c-timestamp/c-timestamp.prototype.js' );
const c_timestamp = clonedeep( c_timestamp_prototype );

const breadcrumbs_prototype = require( '../breadcrumbs/breadcrumbs.prototype.js' );

c_timestamp.c_timestamp_classes = 'u-color-iron-grey a-font-basic-m';
c_timestamp.c_timestamp_text = 'August 29, 2016 3:10PM';

o_indicator.c_span.c_span_text = 'Special Report » Corporate Focus';
o_indicator.c_span.c_span_url = "#";
o_indicator.o_indicator_classes = '';

module.exports = {
	article_meta_classes: 'lrv-u-flex lrv-u-flex-wrap-wrap u-border-color-dusty-grey u-align-items-center@tablet lrv-u-margin-b-050 lrv-u-padding-b-025 u-padding-b-075@tablet lrv-u-justify-content-space-between',
	o_indicator,
	breadcrumbs: breadcrumbs_prototype,
	c_timestamp,
};
