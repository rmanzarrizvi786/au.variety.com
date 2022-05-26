<?php

/**
 * CTA Subscribe Template.
 *
 * Appears below single article content.
 *
 * @package pmc-variety
 */

return;

if (!pmc_subscription_user_has_entitlements(['Variety.VarietyVIP'])) {
	$variant = Variety\Plugins\Variety_VIP\Content::is_vip_page() ? 'variety-vip' : 'prototype';

	$cta_subscribe = PMC\Core\Inc\Larva::get_instance()->get_json('modules/cta-subscribe.' . $variant);
	$cta_subscribe['cxense_article_end_subscribe_module']['cxense_id_attr'] = 'cx-module-article-end';

	\PMC::render_template(
		sprintf('%s/template-parts/patterns/modules/cta-subscribe.php', untrailingslashit(CHILD_THEME_PATH)),
		$cta_subscribe,
		true
	);
}
