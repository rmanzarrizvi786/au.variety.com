<?php
/**
 * Special Reports Carousel VIP Template.
 *
 * @package pmc-variety
 */

use Variety\Plugins\Variety_VIP\Content;
use Variety\Plugins\Variety_VIP\Special_Reports;
use Variety\Inc\Carousels;

$data   = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/special-reports-carousel.variety-vip' );
$_posts = \Variety\Plugins\Variety_VIP\Carousel::get_vip_carousel_posts( 'vip-home-special-reports', 6, false, [ Special_Reports::POST_TYPE ] );

if ( empty( $_posts ) ) {
	return;
}

$template = $data['special_report_items'][0];

$data['special_report_items'] = [];

foreach ( $_posts as $index => $_post ) {
	$item = $template;

	$item['c_title']['c_title_text']     = get_the_date( 'F Y', $_post->ID );
	$item['c_heading']['c_heading_text'] = variety_get_card_title( $_post );
	$item['o_slide_link_url']            = get_permalink( $_post );
	$item['c_title']['c_title_url']      = get_permalink( $_post );

	$report_details = get_post_meta( $_post->ID, 'variety_special_report', true );

	if ( ! empty( $report_details['report_details']['cover_image'] ) ) {

		$image = \PMC\Core\Inc\Media::get_instance()->get_image_data( $report_details['report_details']['cover_image'], 'landscape-large' );

		$item['c_lazy_image']['c_lazy_image_link_url']        = get_permalink( $_post );
		$item['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
		$item['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
		$item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$item['c_lazy_image']['c_lazy_image_srcset_attr']     = wp_get_attachment_image_srcset( $report_details['report_details']['cover_image'] );

	} else {

		if ( ! empty( $_post->image_id ) ) {
			$thumbnail = $_post->image_id;
		} else {
			$thumbnail = get_post_thumbnail_id( $_post );
		}

		if ( ! empty( $thumbnail ) ) {
			$image = \PMC\Core\Inc\Media::get_instance()->get_image_data( $thumbnail, 'landscape-large' );

			$item['c_lazy_image']['c_lazy_image_link_url']        = get_permalink( $_post );
			$item['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
			$item['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
			$item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
			$item['c_lazy_image']['c_lazy_image_srcset_attr']     = wp_get_attachment_image_srcset( $thumbnail );

		} else {

			$item['c_lazy_image']['c_lazy_image_link_url']        = '';
			$item['c_lazy_image']['c_lazy_image_alt_attr']        = '';
			$item['c_lazy_image']['c_lazy_image_src_url']         = '';
			$item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
			$item['c_lazy_image']['c_lazy_image_srcset_attr']     = '';

		}
	}
	$item['o_indicator']['c_span']['c_span_text'] = variety_get_card_title( $_post );
	$item['o_indicator']['c_span']['c_span_url']  = get_permalink( $_post );

	$item['c_timestamp']['c_timestamp_text'] = get_the_date( 'F Y', $_post->ID );

	$data['special_report_items'][] = $item;

}

// Move the last item to the first position due to the second item being initially selected.
$data['special_report_items'] = array_merge( array_splice( $data['special_report_items'], -1 ), $data['special_report_items'] );

$data['o_more_link']['c_link']['c_link_url'] = '/vip-special-reports/';

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/special-reports-carousel.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
