<?php

/**
 * Newsletter signup module.
 *
 * @package pmc-variety
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json('modules/homepage-newsletter.prototype');

$data['newsletter']['o_email_capture_form']['o_email_capture_form_hidden_field_items'][1]['c_hidden_field_value_attr'] = date('Y-m-d');

\PMC::render_template(
	sprintf('%s/template-parts/patterns/modules/homepage-newsletter.php', untrailingslashit(CHILD_THEME_PATH)),
	$data,
	true
);
