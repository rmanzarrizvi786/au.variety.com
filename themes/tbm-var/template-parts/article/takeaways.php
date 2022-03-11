<?php
/**
 * Takeaways Template.
 *
 * @package pmc-variety
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/article-checks.prototype' );

$takeaways = get_post_meta( get_the_ID(), 'variety_takeaways', true );

$data['o_checks_list']['o_checks_list_text_items'] = [];

if ( ! empty( $takeaways['takeaway_list'] ) ) {
	foreach ( $takeaways['takeaway_list'] as $takeaway ) {
		$data['o_checks_list']['o_checks_list_text_items'][] = [
			'o_check_list_text' => $takeaway['takeaway_text'],
		];
	}
}

if ( empty( $data['o_checks_list']['o_checks_list_text_items'] ) ) {
	return;
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/article-checks.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
