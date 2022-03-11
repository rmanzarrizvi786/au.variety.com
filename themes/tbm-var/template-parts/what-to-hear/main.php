<?php
/**
 * Main Docs Template.
 *
 * @package pmc-variety-2020
 */

/**
 * Docs Header Setup
 */
// Get default header data.
$header = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/docs-header.what-to-hear' );

// Current page.
$current_page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

// Get global curation settings.
$settings = get_option( 'global_curation', [] );
$settings = isset( $settings['tab_variety_what_to_hear'] ) ? $settings['tab_variety_what_to_hear'] : false;

$header['o_sponsored_by']['o_sponsored_by_text'] = ! empty( $settings['variety_sponsored_by_text'] ) ? $settings['variety_sponsored_by_text'] : $header['o_sponsored_by']['o_sponsored_by_text'];
$header['c_logo']['c_logo_url']                  = ! empty( $settings['variety_sponsor_link'] ) ? $settings['variety_sponsor_link'] : $header['c_logo']['c_logo_url'];

?>
<div class="__what-to-hear lrv-a-wrapper">
<?php
\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/docs-header.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$header,
	true
);
if ( 1 === $current_page ) {
	\PMC::render_template(
		sprintf( '%s/template-parts/what-to-hear/top-stories.php', untrailingslashit( CHILD_THEME_PATH ) ),
		[],
		true
	);
	\PMC::render_template(
		sprintf( '%s/template-parts/what-to-hear/vy-recommends.php', untrailingslashit( CHILD_THEME_PATH ) ),
		[],
		true
	);
	\PMC::render_template(
		sprintf( '%s/template-parts/what-to-hear/audible-recommends.php', untrailingslashit( CHILD_THEME_PATH ) ),
		[],
		true
	);
	\PMC::render_template(
		sprintf( '%s/template-parts/what-to-hear/podcasts.php', untrailingslashit( CHILD_THEME_PATH ) ),
		[],
		true
	);
	\PMC::render_template(
		sprintf( '%s/template-parts/what-to-hear/vy-podcasts.php', untrailingslashit( CHILD_THEME_PATH ) ),
		[],
		true
	);
	\PMC::render_template(
		sprintf( '%s/template-parts/what-to-hear/album-reviews.php', untrailingslashit( CHILD_THEME_PATH ) ),
		[],
		true
	);
}

\PMC::render_template(
	sprintf( '%s/template-parts/common/latest-news.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[
		'module'      => 'modules/latest-news-river.wth',
		'header_text' => __( 'What To Hear News', 'pmc-variety' ),
		'more_button' => __( 'More What To Hear News', 'pmc-variety' ),
	],
	true
);
?>
</div>
