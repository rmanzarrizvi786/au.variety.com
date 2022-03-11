const clonedeep = require('lodash.clonedeep');

const c_heading = require('@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype.js');

const c_heading_main = clonedeep(c_heading);
const c_heading_time = clonedeep(c_heading);

c_heading_main.c_heading_classes = 'u-font-family-accent lrv-u-text-transform-uppercase u-font-size-42@tablet u-font-size-32@mobile-max u-font-weight-bold lrv-u-text-align-center u-letter-spacing-012 u-letter-spacing-050@mobile-max';
c_heading_main.c_heading_id_attr = 'trending_tv_main';
c_heading_main.c_heading_text = 'Engagement Heading';

c_heading_time.c_heading_classes = 'u-font-family-secondary lrv-u-text-transform-uppercase u-font-size-11 lrv-u-text-align-center u-letter-spacing-003 ';
c_heading_time.c_heading_id_attr = 'trending_tv_days';
c_heading_time.c_heading_text = 'Last 7 Days';
c_heading_time.c_heading_outer = true;
c_heading_time.c_heading_outer_classes = 'lrv-u-border-t-3 u-border-b-3  u-background-color-shimmer lrv-u-background-color-grey-light lrv-u-padding-tb-050',

module.exports = {
	c_heading_main: c_heading_main,
	c_heading_time: c_heading_time,
}
