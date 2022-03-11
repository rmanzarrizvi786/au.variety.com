<?php
/**
 * Review Template.
 *
 * @package pmc-variety
 */

if ( ! variety_is_review() ) {
	return;
}

$data         = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/review-meta.prototype' );
$article_data = \Variety\Inc\Reviews::get_instance()->get_review_data();

$data['c_title']['c_heading_text']        = get_the_title();
$data['o_meta_list']['o_meta_list_items'] = [];

if ( ! empty( $article_data ) ) {
	$data['c_heading']['c_heading_text']      = $article_data['origin'];
	$data['o_meta_list']['o_meta_list_items'] = [
		[
			'meta_item_label_text'       => __( 'Production', 'pmc-variety' ),
			'meta_item_description_text' => $article_data['production'],
		],
		[
			'meta_item_label_text'       => __( 'Crew', 'pmc-variety' ),
			'meta_item_description_text' => $article_data['crew'],
		],
		[
			'meta_item_label_text'       => $article_data['label_cast'],
			'meta_item_description_text' => sprintf( '%1$s %2$s', $article_data['primary_cast'], $article_data['secondary_cast'] ),
		],
		[
			'meta_item_label_text'       => __( 'Music By', 'pmc-variety' ),
			'meta_item_description_text' => $article_data['music_by'],
		],
	];
}

if ( empty( $data['c_heading']['c_heading_text'] ) ) {
	unset( $data['c_heading'] );
}

foreach ( $data['o_meta_list']['o_meta_list_items'] as $index => $list_item ) {
	if ( empty( trim( $list_item['meta_item_description_text'] ) ) ) {
		unset( $data['o_meta_list']['o_meta_list_items'][ $index ] );
	}
}

if ( 0 < count( $data['o_meta_list']['o_meta_list_items'] ) || ! empty( trim( $data['c_heading']['c_heading_text'] ) ) ) {
	\PMC::render_template(
		sprintf( '%s/template-parts/patterns/modules/review-meta.php', untrailingslashit( CHILD_THEME_PATH ) ),
		$data,
		true
	);
}
