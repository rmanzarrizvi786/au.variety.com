const clonedeep = require( 'lodash.clonedeep' );

const newsletter_prototype = require( './newsletter.prototype' );
const newsletter = clonedeep( newsletter_prototype );

const {
	c_heading
} = newsletter;

c_heading.c_heading_text = 'Sign up for Variety Breaking News Alerts';
c_heading.c_heading_classes = c_heading.c_heading_classes.replace( 'u-margin-b-025@mobile-max', 'lrv-u-margin-b-050' );
c_heading.c_heading_classes += ' u-max-width-60p@mobile-max u-margin-lr-auto@mobile-max u-line-height-120';

const c_hidden_field_prototype = require( '@penskemediacorp/larva-patterns/components/c-hidden-field/c-hidden-field.prototype' );
c_hidden_field_name = clonedeep( c_hidden_field_prototype );

c_hidden_field_name.c_hidden_field_name_attr = 'Editorial_Breaking_News_Opted_In';
c_hidden_field_name.c_hidden_field_value_attr = 'Yes';

c_hidden_field_date = clonedeep( c_hidden_field_prototype );
c_hidden_field_date.c_hidden_field_name_attr = 'Editorial_Breaking_News_Opt_In_Date';
/* TODO: fill in year/month/day dynamically from the back-end. */
c_hidden_field_date.c_hidden_field_value_attr = 'TODO:YYYY-MM-DD';

c_hidden_field_source = clonedeep( c_hidden_field_prototype );
c_hidden_field_source.c_hidden_field_name_attr = 'source';
c_hidden_field_source.c_hidden_field_value_attr = 'River';

newsletter.o_email_capture_form.o_email_capture_form_hidden_field_items = [
	c_hidden_field_name,
	c_hidden_field_date,
	c_hidden_field_source,
];

module.exports = {
	...newsletter,
};
