<?php
/**
 * VIP Banner module (uses CTA Banner module).
 *
 * There is an ad module called VIP Banner - that is an ad and needs to be renamed.
 *
 * @package pmc-variety
 */

$banner = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/cxense-widget.introducing' );

$banner['cxense_widget']['cxense_id_attr'] = 'cx-module-introducing';

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/cxense-widget.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$banner,
	true
);
