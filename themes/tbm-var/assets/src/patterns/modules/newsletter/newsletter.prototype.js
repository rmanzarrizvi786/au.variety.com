const clonedeep = require( 'lodash.clonedeep' );

const c_heading_prototype = require( '@penskemediacorp/larva-patterns/components/c-heading/c-heading.prototype' );
const c_heading = clonedeep( c_heading_prototype );

const o_email_capture_form_prototype = require( '@penskemediacorp/larva-patterns/objects/o-email-capture-form/o-email-capture-form.prototype' );
const o_email_capture_form = clonedeep( o_email_capture_form_prototype );

const c_hidden_field_prototype = require( '@penskemediacorp/larva-patterns/components/c-hidden-field/c-hidden-field.prototype' );
c_hidden_field_name = clonedeep( c_hidden_field_prototype );

/* Form data */

o_email_capture_form.o_email_capture_form_success_url = 'https://pages.email.variety.com/signup/?signup=success';
o_email_capture_form.o_email_capture_form_name_attr = 'newsletter-module-form';
o_email_capture_form.o_email_capture_form_action_url = 'https://pages.email.variety.com/api/';
o_email_capture_form.o_email_capture_form_context_name_attr = 'NewsletterFormPost';

c_hidden_field_name.c_hidden_field_name_attr = 'Editorial_Daily_Headlines_Opted_In';
c_hidden_field_name.c_hidden_field_value_attr = 'Yes';

c_hidden_field_date = clonedeep( c_hidden_field_prototype );
c_hidden_field_date.c_hidden_field_name_attr = 'Editorial_Daily_Headlines_Opt_In_Date';
/* TODO: fill in year/month/day dynamically from the back-end. */
c_hidden_field_date.c_hidden_field_value_attr = 'TODO:YYYY-MM-DD';

c_hidden_field_source = clonedeep( c_hidden_field_prototype );
c_hidden_field_source.c_hidden_field_name_attr = 'source';
c_hidden_field_source.c_hidden_field_value_attr = 'River';

o_email_capture_form.o_email_capture_form_hidden_field_items = [
	c_hidden_field_name,
	c_hidden_field_date,
	c_hidden_field_source,
];

const {
	c_email_field,
	c_button,
} = o_email_capture_form;

c_heading.c_heading_text = 'Sign Up for Variety Newsletters';
c_heading.c_heading_classes = 'lrv-u-color-white lrv-u-font-family-secondary lrv-u-font-size-18 u-font-size-18@tablet u-margin-b-025@mobile-max u-margin-b-auto@tablet lrv-u-margin-r-1@tablet lrv-u-text-align-center';

o_email_capture_form.o_email_capture_form_classes = 'lrv-u-margin-b-050';
o_email_capture_form.o_email_capture_form_inner_classes = 'lrv-u-flex lrv-u-align-items-center lrv-u-justify-content-center';

c_email_field.c_email_field_input_name_attr = 'EmailAddress';
c_email_field.c_email_field_input_placeholder_attr = 'Enter your email address';
c_email_field.c_email_field_label_classes = 'lrv-u-display-none';
c_email_field.c_email_field_input_classes = 'lrv-u-border-a-0 lrv-u-padding-lr-050 lrv-u-padding-tb-025 a-placeholder-color-picked-bluewood u-font-size-11 u-font-size-13@tablet u-width-150 u-width-210@tablet';

c_button.c_button_classes += ' u-margin-l-075@tablet';
c_button.c_button_inner_classes = 'lrv-a-icon-after lrv-u-color-white lrv-u-font-weight-bold lrv-u-margin-l-050 lrv-u-text-transform-uppercase a-icon-long-right-arrow-blue lrv-u-font-family-secondary u-font-size-11 u-font-size-12@tablet u-letter-spacing-2';

module.exports = {
	newsletter_classes: 'lrv-a-wrapper u-background-color-picked-bluewood u-box-shadow-menu@tablet',
	newsletter_inner_classes: 'lrv-u-flex@tablet lrv-u-align-items-center lrv-u-justify-content-center lrv-u-padding-tb-1 u-padding-t-075@tablet u-padding-b-025@tablet',
	c_heading,
	o_email_capture_form,
};
