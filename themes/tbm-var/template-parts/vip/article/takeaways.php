<?php
/**
 * VIP Takeaways Template.
 *
 * @package pmc-variety
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/vip-takeaways.prototype' );

$takeaways = get_post_meta( get_the_ID(), 'variety_vip_takeaways', true );

$data['o_checks_list']['o_checks_list_text_items'] = [];

if ( ! empty( $takeaways['takeaway_list'] ) ) {
	foreach ( $takeaways['takeaway_list'] as $takeaway ) {
		$data['o_checks_list']['o_checks_list_text_items'][] = [
			'o_check_list_text' => $takeaway['takeaway_text'],
		];
	}
}

if ( empty( $data['o_checks_list']['o_checks_list_text_items'] ) || empty( $takeaways['takeaway_list'] ) ) {
	return;
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/vip-takeaways.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
