const clonedeep = require( 'lodash.clonedeep' );

const o_social_list = {
	o_social_list_classes: "lrv-a-unstyle-list lrv-u-flex lrv-u-align-items-center u-margin-r-225",
	o_social_list_item_classes: "lrv-u-display-inline-block",
	o_social_list_icons: [
		{
			c_icon_classes: "lrv-u-width-16 lrv-u-height-16 lrv-u-display-block",
			c_icon_name: "twitter",
			c_icon_url: "https://www.facebook.com/deadlinehollywood/",
			c_icon_link_classes: "lrv-u-padding-a-050 lrv-u-display-block lrv-u-border-radius-50p lrv-u-color-black lrv-u-color-grey-dark:hover lrv-u-margin-lr-025 u-background-color-accent-b-100",
			c_icon_rel_name: "noopener noreferrer"
		},
		{
			c_icon_classes: "lrv-u-width-16 lrv-u-height-16 lrv-u-display-block",
			c_icon_name: "facebook",
			c_icon_url: "https://www.facebook.com/deadlinehollywood/",
			c_icon_link_classes: "lrv-u-padding-a-050 lrv-u-display-block lrv-u-border-radius-50p lrv-u-color-black lrv-u-color-grey-dark:hover lrv-u-margin-lr-025 u-background-color-accent-b-100",
			c_icon_rel_name: "noopener noreferrer"
		},
		{
			c_icon_classes: "lrv-u-width-16 lrv-u-height-16 lrv-u-display-block",
			c_icon_name: "youtube",
			c_icon_url: "https://www.facebook.com/deadlinehollywood/",
			c_icon_link_classes: "lrv-u-padding-a-050 lrv-u-display-block lrv-u-border-radius-50p lrv-u-color-black lrv-u-color-grey-dark:hover lrv-u-margin-lr-025 u-background-color-accent-b-100",
			c_icon_rel_name: "noopener noreferrer"
		},
		{
			c_icon_classes: "lrv-u-width-16 lrv-u-height-16 lrv-u-display-block",
			c_icon_name: "instagram",
			c_icon_url: "https://www.facebook.com/deadlinehollywood/",
			c_icon_link_classes: "lrv-u-padding-a-050 lrv-u-display-block lrv-u-border-radius-50p lrv-u-color-black lrv-u-color-grey-dark:hover lrv-u-margin-lr-025 u-background-color-accent-b-100",
			c_icon_rel_name: "noopener noreferrer"
		},
	]
};

const o_nav_prototype = require( '@penskemediacorp/larva-patterns/objects/o-nav/o-nav.horizontal.js' );
const o_nav = clonedeep( o_nav_prototype );
const o_nav_items_c_links__prototype = require( '@penskemediacorp/larva-patterns/components/c-link/c-link.prototype.js' );

o_nav.o_nav_list_items = [];
o_nav.o_nav_list_classes = o_nav.o_nav_list_classes.replace( 'lrv-a-space-children--1', 'a-space-children--150' );
o_nav.o_nav_list_classes += ' lrv-u-line-height-large u-justify-content-center@desktop-max lrv-u-flex-wrap-wrap';
o_nav.o_nav_list_item_classes += ' c-label lrv-u-font-weight-normal lrv-u-font-size-14';

const menuLinks = [  'Advertise', 'Contact', 'Customer Service' ];

for (let i = 0; i < menuLinks.length; i++) {
	let c_link = clonedeep( o_nav_items_c_links__prototype );

	c_link.c_link_text = menuLinks[i];
	c_link.c_link_classes = 'lrv-a-unstyle-link u-color-brand-secondary-30 u-color-brand-primary-40:hover';

	o_nav.o_nav_list_items.push( c_link );
}

const c_icon_prototype = require( '@penskemediacorp/larva-patterns/components/c-icon/c-icon.full.js' );
const c_icon = clonedeep( c_icon_prototype );

c_icon.c_icon_name = 'pmc-logo';
c_icon.c_icon_link_classes = 'u-width-90 u-height-16 lrv-u-margin-r-00 u-margin-l-auto lrv-u-color-white';
c_icon.c_icon_url = '#';

const o_email_capture_form_prototype = require( '@penskemediacorp/larva-patterns/objects/o-email-capture-form/o-email-capture-form.prototype' );
const o_email_capture_form = clonedeep( o_email_capture_form_prototype );

const c_hidden_field_prototype = require( '@penskemediacorp/larva-patterns/components/c-hidden-field/c-hidden-field.prototype' );
c_hidden_field_name = clonedeep( c_hidden_field_prototype );

/* Form data */

o_email_capture_form.o_email_capture_form_success_url = '#';
o_email_capture_form.o_email_capture_form_name_attr = 'newsletter-module-form';
o_email_capture_form.o_email_capture_form_action_url = '#';
o_email_capture_form.c_email_field.c_email_field_input_name_attr = 'EmailAddress';
o_email_capture_form.c_email_field.c_email_field_input_id_attr = 'mega_newsletter_email';
o_email_capture_form.o_email_capture_form_context_name_attr = 'NewsletterFormPost';

c_hidden_field_name.c_hidden_field_name_attr = 'Editorial_Daily_Headlines_Opted_In';
c_hidden_field_name.c_hidden_field_value_attr = 'Yes';
c_hidden_field_name.c_hidden_field_id_attr = 'mega_newsletter_name';

c_hidden_field_date = clonedeep( c_hidden_field_prototype );
c_hidden_field_date.c_hidden_field_name_attr = 'Editorial_Daily_Headlines_Opt_In_Date';
/* TODO: fill in year/month/day dynamically from the back-end. */
c_hidden_field_date.c_hidden_field_value_attr = 'TODO:YYYY-MM-DD';
c_hidden_field_date.c_hidden_field_id_attr = 'mega_newsletter_date';

c_hidden_field_source = clonedeep( c_hidden_field_prototype );
c_hidden_field_source.c_hidden_field_name_attr = 'source';
c_hidden_field_source.c_hidden_field_value_attr = 'MegaMenu';
c_hidden_field_source.c_hidden_field_id_attr = 'mega_newsletter_src';

o_email_capture_form.o_email_capture_form_hidden_field_items = [
	c_hidden_field_name,
	c_hidden_field_date,
	c_hidden_field_source,
];

const c_subscribe_link = clonedeep( o_nav_items_c_links__prototype );

const o_nav_tips = clonedeep( o_nav_prototype );

const region_selector_prototype = require( '../region-selector/region-selector.prototype' );
const footer_region_selector = clonedeep( region_selector_prototype );

footer_region_selector.region_selector.toggle_classes = footer_region_selector.region_selector.toggle_classes.replace( 'lrv-u-color-white', 'u-color-brand-secondary-30' );
footer_region_selector.region_selector.region_selector_classes += ' u-margin-l-175';

o_email_capture_form.o_email_capture_form_inner_classes = 'lrv-u-flex';
o_email_capture_form.c_email_field.c_email_field_label_classes = 'lrv-u-display-none';
o_email_capture_form.c_email_field.c_email_field_input_classes = 'u-width-200 lrv-u-border-a-0 lrv-u-padding-tb-025 lrv-u-padding-lr-050 u-font-size-13 a-placeholder-color-pale-sky-2';
o_email_capture_form.c_email_field.c_email_field_input_placeholder_attr = 'Enter your email address';
o_email_capture_form.c_button.c_button_classes += ' u-margin-l-075';
o_email_capture_form.c_button.c_button_inner_classes = 'lrv-u-font-family-secondary lrv-u-font-size-12 lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-color-brand-secondary-30 u-letter-spacing-2 lrv-a-icon-after a-icon-long-right-arrow-blue u-color-brand-primary-40:hover@tablet ';

c_subscribe_link.c_link_text = 'Subscribe';
c_subscribe_link.c_link_url = '/subscribe-us/';
c_subscribe_link.c_link_classes = 'u-color-brand-secondary-30 lrv-u-border-a-1 lrv-u-text-transform-uppercase u-border-color-brand-secondary-30 u-color-brand-primary-40:hover lrv-u-padding-a-050';

const tipsMenu = [ 'Have a news tip?' ];

o_nav_tips.o_nav_list_classes = o_nav_tips.o_nav_list_classes.replace( 'lrv-a-space-children--1', 'a-space-children--175' );
o_nav_tips.o_nav_list_items = [];

for ( item of tipsMenu ) {
	let tipItem = clonedeep( o_nav_items_c_links__prototype );

	tipItem.c_link_text = item;
	tipItem.c_link_classes = 'lrv-u-font-family-secondary lrv-u-font-size-12 lrv-u-font-weight-bold lrv-u-text-transform-uppercase u-color-brand-secondary-30 u-color-brand-primary-40:hover u-letter-spacing-2';

	o_nav_tips.o_nav_list_items.push( tipItem );
}

module.exports = {
	'o_email_capture_form': o_email_capture_form,
	'o_social_list': o_social_list,
	'o_nav': o_nav,
	'c_icon': c_icon,
	'mega_menu_footer_alerts_text': 'Alerts and Newsletters',
	'mega_menu_footer_follow_text': 'Follow Us',
	'mega_menu_footer_copyright_text': '© 2020 Penske Media Corporation',
	c_subscribe_link,
	o_nav_tips,
	region_selector: footer_region_selector,
}
