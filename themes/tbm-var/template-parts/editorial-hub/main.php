<?php
/**
 * Main Editorial Hub Template.
 *
 * @package pmc-variety
 */

/**
 * Hub Header Setup
 */
// Get default header data.
$header = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/hub-header.prototype' );

// Get global curation settings.
$settings = get_option( 'global_curation', [] );
$settings = $settings['tab_variety_what_to_watch_sponsor'];

// Sponsored by text.
$header['c_heading']['c_heading_text'] = ! empty( $settings['variety_sponsored_by_text'] ) ? $settings['variety_heading_text'] : $header['c_heading']['c_heading_text'];
$header['c_tagline']['c_tagline_text'] = ! empty( $settings['variety_sponsored_by_text'] ) ? $settings['variety_logline_text'] : false;

$header['o_sponsored_by']['o_sponsored_by_text'] = ! empty( $settings['variety_sponsored_by_text'] ) ? $settings['variety_sponsored_by_text'] : $header['o_sponsored_by']['o_sponsored_by_text'];

// Logo Link.
$header['c_logo']['c_logo_url'] = ! empty( $settings['variety_sponsor_link'] ) ? $settings['variety_sponsor_link'] : $header['c_logo']['c_logo_url'];

?>
<div class="__editorial-hub">
<?php
\PMC::render_template(
	sprintf( '%s/template-parts/editorial-hub/hub-header.php', untrailingslashit( CHILD_THEME_PATH ) ),
	[ 'data' => $header ],
	true
);
?>
<?php
/**
 * Hub Section Setup
 */
if ( is_active_sidebar( 'editorial-hub' ) ) {
	dynamic_sidebar( 'editorial-hub' );
}
?>
</div>
