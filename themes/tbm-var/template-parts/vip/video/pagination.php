<?php
/**
 * Archive Video Pagination.
 *
 * @package pmc-variety
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/video-landing-pagination.prototype' );

global $wp_query, $paged;

$current_page = empty( $paged ) ? 1 : intval( $paged );

// Next.
if ( $current_page < $wp_query->max_num_pages ) {
	$_link = next_posts( 0, false );

	if ( ! empty( $_link ) ) {
		$data['c_link_next']['c_link_text'] = __( 'Next', 'pmc-variety' );
		$data['c_link_next']['c_link_url']  = $_link;
	}
} else {
	$data['c_link_next'] = [];
}

// Previous.
if ( $current_page > 1 ) {
	$_link = previous_posts( 0, false );

	if ( ! empty( $_link ) ) {
		$data['c_link_previous']['c_link_text'] = __( 'Previous', 'pmc-variety' );
		$data['c_link_previous']['c_link_url']  = $_link;
	}
} else {
	$data['c_link_previous'] = [];
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/video-landing-pagination.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
