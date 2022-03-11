<?php
/**
 * Featured Chart VIP Template.
 *
 * @package pmc-variety
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/featured-chart.variety-vip' );

if ( ! empty( $featured_chart_classes ) ) {
	$data['featured_chart_classes'] = $featured_chart_classes;
}

if ( ! empty( $featured_chart_iframe_url ) ) {
	$data['featured_chart_iframe_url'] = $featured_chart_iframe_url;
}

if ( ! empty( $featured_chart_iframe_height_attr ) ) {
	$data['featured_chart_iframe_height_attr'] = $featured_chart_iframe_height_attr;
}

if ( ! empty( $c_button_text ) ) {
	$data['c_button']['c_button_text'] = $c_button_text;
}

if ( ! empty( $c_button_url ) ) {
	$data['c_button']['c_button_url'] = $c_button_url;
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/featured-chart.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
