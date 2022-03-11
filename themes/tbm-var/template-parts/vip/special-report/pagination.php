<?php
/**
 * More Special Report VIP Template.
 *
 * @package pmc-variety
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/special-report-landing-pagination.prototype' );

// Previous / Next.
$next_post_link = get_next_posts_link() ? next_posts( 0, false ) : false;
$prev_post_link = get_previous_posts_link() ? previous_posts( false ) : false;

// Next
if ( ! empty( $next_post_link ) ) {
	$data['o_more_link']['c_link']['c_link_url']  = $next_post_link;
	$data['o_more_link']['c_link']['c_link_text'] = __( 'More Special Reports', 'pmc-variety' );
} else {
	$data['o_more_link']['c_link'] = null;
}

// Previous.
if ( ! empty( $prev_post_link ) ) {
	$data['special_report_landing_pagination_is_paged']    = true;
	$data['o_more_link_previous']['c_link']['c_link_url']  = $prev_post_link;
	$data['o_more_link_previous']['c_link']['c_link_text'] = __( 'Previous', 'pmc-variety' );
} else {
	// Hide link if there are no next posts
	$data['o_more_link_previous']['c_link'] = false;
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/special-report-landing-pagination.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
