const clonedeep = require( 'lodash.clonedeep' );

const newsletter_prototype = require( '../newsletter/newsletter.prototype' );
const newsletter = clonedeep( newsletter_prototype );
newsletter.o_email_capture_form.c_email_field.c_email_field_input_id_attr = 'hp_newsletter_email';
newsletter.o_email_capture_form.o_email_capture_form_hidden_field_items[0].c_hidden_field_id_attr = 'hp_newsletter_name';
newsletter.o_email_capture_form.o_email_capture_form_hidden_field_items[1].c_hidden_field_id_attr = 'hp_newsletter_date';
newsletter.o_email_capture_form.o_email_capture_form_hidden_field_items[2].c_hidden_field_id_attr = 'hp_newsletter_src';

module.exports = {
	homepage_newsletter_classes: 'lrv-a-wrapper',
	newsletter,
};
