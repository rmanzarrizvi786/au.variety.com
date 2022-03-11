<?php
/**
 * Section Front Widgets
 *
 * Display below the top stories grid.
 */
if ( $is_paged ) {
	return;
}

if ( ! is_array( $menu_items ) || empty( $menu_items['root'] ) ) {
	return;
}

$data['articles'] = \Variety\Inc\Carousels::get_carousel_posts( 'recommended-for-you', 5 );

\PMC::render_template(
	sprintf(
		'%s/template-parts/widgets/recommended-for-you.php',
		untrailingslashit( CHILD_THEME_PATH )
	),
	[ 'data' => $data ],
	true
);

get_template_part( 'template-parts/widgets/newsletter-signup' );

