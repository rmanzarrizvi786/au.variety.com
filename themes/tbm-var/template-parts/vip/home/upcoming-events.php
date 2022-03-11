<?php
/**
 * Upcoming Events VIP Template.
 *
 * @package pmc-variety
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/upcoming-events.variety-vip' );

if ( ! empty( $upcoming_events_classes ) ) {
	$data['upcoming_events_classes'] = $upcoming_events_classes;
}

$global_curations = get_option( 'global_curation', [] );

if ( empty( $global_curations['tab_variety_vip_upcoming_event']['vip_event'] ) ) {
	return;
}

$image = \PMC\Core\Inc\Media::get_instance()->get_image_data( $global_curations['tab_variety_vip_upcoming_event']['vip_event']['event_image'], 'landscape-large' );

$data['c_lazy_image']['c_lazy_image_link_url']        = $global_curations['tab_variety_vip_upcoming_event']['vip_event']['event_link'] ?? '';
$data['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
$data['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
$data['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
$data['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( $global_curations['tab_variety_vip_upcoming_event']['vip_event']['event_image'] );

$data['o_more_heading']['c_heading']['c_heading_text'] = $global_curations['tab_variety_vip_upcoming_event']['vip_event']['event_header'] ?? __( 'Upcoming Events', 'pmc-variety' );
$data['c_title']['c_title_text']                       = $global_curations['tab_variety_vip_upcoming_event']['vip_event']['event_name'] ?? '';
$data['c_title']['c_title_url']                        = $global_curations['tab_variety_vip_upcoming_event']['vip_event']['event_link'] ?? '';
$data['c_dek']['c_dek_text']                           = $global_curations['tab_variety_vip_upcoming_event']['vip_event']['event_description'] ?? '';
$data['o_more_link']['c_link']['c_link_url']           = $global_curations['tab_variety_vip_upcoming_event']['vip_event']['event_link'] ?? '';
$data['o_more_link']['c_link']['c_link_target_attr']   = false === strpos( ( $global_curations['tab_variety_vip_upcoming_event']['vip_event']['event_link'] ?? '' ), 'variety' ) ? '_blank' : '';


\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/upcoming-events.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
