<?php

/**
 * Module: Article CTA Banner
 *
 * Only show if the "Article is free" metabox is checked.
 */

$is_article_free = Variety\Plugins\Variety_VIP\Content::is_article_free( get_the_ID() );

if ( $is_article_free && ! pmc_subscription_user_has_entitlements( [ 'Variety.VarietyVIP' ] ) ) {

	$cta_banner = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/cxense-widget.sticky-header' );

	$cta_banner['cxense_sticky_header_subscribe_widget']['cxense_id_attr'] = 'cx-module-sticky-header';

	\PMC::render_template(
		sprintf( '%s/template-parts/patterns/modules/cxense-widget.php', untrailingslashit( CHILD_THEME_PATH ) ),
		$cta_banner,
		true
	);

}
