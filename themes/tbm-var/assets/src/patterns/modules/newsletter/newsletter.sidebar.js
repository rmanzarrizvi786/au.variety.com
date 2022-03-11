const clonedeep = require( 'lodash.clonedeep' );

const newsletter_prototype = require( './newsletter.prototype' );
const newsletter = clonedeep( newsletter_prototype );

newsletter.o_email_capture_form.c_email_field.c_email_field_input_id_attr = 'sb_newsletter_email';

newsletter.c_heading.c_heading_classes = 'lrv-u-color-black lrv-u-font-family-secondary lrv-u-font-size-18 lrv-u-margin-b-050 lrv-u-text-align-center';

newsletter.newsletter_classes = 'u-background-color-accent-c-100 u-box-shadow-menu lrv-u-margin-b-2';

newsletter.o_email_capture_form.c_button.c_button_inner_classes = newsletter.o_email_capture_form.c_button.c_button_inner_classes.replace( 'lrv-u-color-white u-color-black@tablet', 'lrv-u-color-black' );

newsletter.o_email_capture_form.c_email_field.c_email_field_input_classes = newsletter.o_email_capture_form.c_email_field.c_email_field_input_classes.replace( 'u-width-210@tablet', 'u-width-177@tablet' );

newsletter.newsletter_inner_classes = newsletter.newsletter_inner_classes.replace( 'lrv-u-flex@tablet', '' );

newsletter.o_email_capture_form.c_button.c_button_inner_classes = 'lrv-a-icon-after lrv-u-font-weight-bold lrv-u-margin-l-050 lrv-u-text-transform-uppercase a-icon-long-right-arrow-blue lrv-u-font-family-secondary u-font-size-11 u-font-size-12@tablet u-letter-spacing-2';

const c_hidden_field_prototype = require( '@penskemediacorp/larva-patterns/components/c-hidden-field/c-hidden-field.prototype' );
c_hidden_field_name_1 = clonedeep( c_hidden_field_prototype );
c_hidden_field_name_1.c_hidden_field_name_attr = 'Editorial_Daily_Headlines_Opted_In';
c_hidden_field_name_1.c_hidden_field_value_attr = 'Yes';
c_hidden_field_name_1.c_hidden_field_id_attr = 'sb_newsletter_name_1';

c_hidden_field_date_1 = clonedeep( c_hidden_field_prototype );
c_hidden_field_date_1.c_hidden_field_name_attr = 'Editorial_Daily_Headlines_Opt_In_Date';
/* TODO: fill in year/month/day dynamically from the back-end. */
c_hidden_field_date_1.c_hidden_field_value_attr = 'TODO:YYYY-MM-DD';
c_hidden_field_date_1.c_hidden_field_id_attr = 'sb_newsletter_date_1';

c_hidden_field_name_2 = clonedeep( c_hidden_field_prototype );
c_hidden_field_name_2.c_hidden_field_name_attr = 'Editorial_Breaking_News_Opted_In';
c_hidden_field_name_2.c_hidden_field_value_attr = 'Yes';
c_hidden_field_name_2.c_hidden_field_id_attr = 'sb_newsletter_name_2';

c_hidden_field_date_2 = clonedeep( c_hidden_field_prototype );
c_hidden_field_date_2.c_hidden_field_name_attr = 'Editorial_Breaking_News_Opt_In_Date';
/* TODO: fill in year/month/day dynamically from the back-end. */
c_hidden_field_date_2.c_hidden_field_value_attr = 'TODO:YYYY-MM-DD';
c_hidden_field_date_2.c_hidden_field_id_attr = 'sb_newsletter_date_2';

c_hidden_field_source = clonedeep( c_hidden_field_prototype );
c_hidden_field_source.c_hidden_field_name_attr = 'source';
c_hidden_field_source.c_hidden_field_value_attr = 'RightRail';
c_hidden_field_source.c_hidden_field_id_attr = 'sb_newsletter_right_rail';

newsletter.o_email_capture_form.o_email_capture_form_hidden_field_items = [
	c_hidden_field_name_1,
	c_hidden_field_date_1,
	c_hidden_field_name_2,
	c_hidden_field_date_2,
	c_hidden_field_source,
];

module.exports = {
	...newsletter,
};
