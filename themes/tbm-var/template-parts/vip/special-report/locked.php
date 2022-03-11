<?php
/**
 * Special Report Read On VIP Template.
 *
 * @package pmc-variety
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/special-report-lock.variety-vip' );

if ( ! empty( $read_on_classes ) ) {
	$data['read_on']['read_on_classes'] = $read_on_classes;
}

$template       = $data['read_on']['read_on_items'][0];
$report_details = get_post_meta( get_the_ID(), 'variety_special_report', true );

$data['read_on']['read_on_items'] = [];

if ( ! empty( $report_details['tease']['tease_list'] ) ) {
	foreach ( $report_details['tease']['tease_list'] as $index => $tease ) {
		$item = $template;

		$item['c_span']['c_span_text'] = $index + 1;
		$item['c_dek']['c_dek_text']   = $tease['tease_text'];

		$data['read_on']['read_on_items'][] = $item;
	}
}

$report_details = get_post_meta( get_the_ID(), 'variety_special_report', true );

if ( ! empty( $view_full_classes ) ) {
	$data['view_full_extended']['view_full_classes'] = $view_full_classes;
}

$data['view_full_extended']['c_link']['c_link_url']        = $report_details['report_details']['offsite_url'] ?? '';
$data['view_full_extended']['c_link_mobile']['c_link_url'] = $report_details['report_details']['offsite_url'] ?? '';

$data['view_full_extended']['c_tagline']['c_tagline_markup'] = sprintf(
	'%s <a href="%s" class="u-color-brand-secondary-50 u-text-decoration-underline u-color-brand-secondary-50:hover u-text-decoration-none:hover">%s</a>',
	__( "Don't have an account?", 'pmc-variety' ),
	'/subscribe-us/',
	__( 'Sign Up', 'pmc-variety' )
);

if ( ! empty( $report_details['report_details']['cover_image'] ) ) {
	$image = \PMC\Core\Inc\Media::get_instance()->get_image_data( $report_details['report_details']['cover_image'], 'landscape-large' );

	$data['view_full_extended']['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
	$data['view_full_extended']['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
	$data['view_full_extended']['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$data['view_full_extended']['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( $report_details['report_details']['cover_image'] );
} else {
	$data['view_full_extended']['c_lazy_image'] = [];
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/special-report-lock.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
