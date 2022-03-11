const clonedeep = require( 'lodash.clonedeep' );

const homepage_newsletter_prototype = require( './homepage-newsletter.prototype' );
const homepage_newsletter = clonedeep( homepage_newsletter_prototype );

const newsletter_prototype = require( '../newsletter/newsletter.breaking' );
const newsletter = clonedeep( newsletter_prototype );
newsletter.o_email_capture_form.c_email_field.c_email_field_input_id_attr = 'hp_brk_newsletter_email';
newsletter.o_email_capture_form.o_email_capture_form_hidden_field_items[0].c_hidden_field_id_attr = 'hp_brk_newsletter_name';
newsletter.o_email_capture_form.o_email_capture_form_hidden_field_items[1].c_hidden_field_id_attr = 'hp_brk_newsletter_date';
newsletter.o_email_capture_form.o_email_capture_form_hidden_field_items[2].c_hidden_field_id_attr = 'hp_brk_newsletter_src';

homepage_newsletter.homepage_newsletter_classes += ' lrv-u-margin-t-1';

module.exports = {
	...homepage_newsletter,
	newsletter,
};
