<?php
/**
 * Variety Produced Podcasts Template.
 *
 * @package pmc-variety-2020
 */

// Get data structure.
$wth_variety_pods = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/wth-vy-podcasts.prototype' );

$wth_variety_pods_data = \Variety\Inc\Carousels::get_carousel_posts(
	'vy-wth-variety-pods',
	4,
);

if ( empty( $wth_variety_pods_data ) || count( $wth_variety_pods_data ) < 4 ) {
	return;
}

// Get global curation settings.
$settings = get_option( 'global_curation', [] );
$settings = isset( $settings['tab_variety_what_to_hear'] ) ? $settings['tab_variety_what_to_hear'] : false;

$wth_variety_pods['o_sub_heading']['c_heading']['c_heading_text'] = ! empty( $settings['variety_vy_podcasts_header_copy'] ) ? $settings['variety_vy_podcasts_header_copy'] : __( 'Variety Produced', 'pmc-variety' );

$wth_variety_pods['o_sub_heading']['c_dek']['c_dek_text'] = ! empty( $settings['variety_vy_podcasts_logline_copy'] ) ? $settings['variety_vy_podcasts_logline_copy'] : __( 'Keep it in the family with our suite of Variety produced podcasts covering everything from awards, Broadway and business.', 'pmc-variety' );

$wth_variety_pod_tease_prototype = $wth_variety_pods['o_tease_list']['o_tease_list_items'][0];
$wth_variety_pod_tease_items     = [];

foreach ( $wth_variety_pods_data as $wth_variety_pod ) {
	$wth_variety_pod_tease = $wth_variety_pod_tease_prototype;

	$wth_variety_pod_tease['c_title']['c_title_text'] = $wth_variety_pod->post_title;
	$wth_variety_pod_tease['c_title']['c_title_url']  = isset( $wth_variety_pod->url ) ? $wth_variety_pod->url : get_the_permalink( $wth_variety_pod );

	$details = get_post_meta( $wth_variety_pod->parent_id, 'variety_hear_details', true );

	if ( ! empty( $details['variety_watch_url'] ) ) {
		$wth_variety_pod_tease['c_link']['c_link_url']  = $details['variety_watch_url'];
		$wth_variety_pod_tease['c_link']['c_link_text'] = __( 'Subscribe', 'pmc-variety' );
	} else {
		$wth_variety_pod_tease['c_link'] = false;
	}

	$wth_variety_pod_tease['c_dek']['c_dek_text'] = isset( $wth_variety_pod->custom_excerpt ) ? $wth_variety_pod->custom_excerpt : wp_strip_all_tags( \PMC\Core\Inc\Helper::get_the_excerpt( $wth_variety_pod->ID ) );

	$image_id = isset( $wth_variety_pod->image_id ) ? $wth_variety_pod->image_id : get_post_thumbnail_id( $wth_variety_pod->ID );

	$wth_variety_pod_tease['c_lazy_image']['c_lazy_image_link_url']           = isset( $wth_variety_pod->url ) ? $wth_variety_pod->url : get_the_permalink( $wth_variety_pod );
	$wth_variety_pod_tease['c_lazy_image']['c_lazy_image_placeholder_url']    = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$wth_variety_pod_tease['c_lazy_image']['c_lazy_image_src_url']            = \wp_get_attachment_image_url( $image_id, 'square-medium' );
	$wth_variety_pod_tease['c_lazy_image']['c_lazy_image_screen_reader_text'] = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$wth_variety_pod_tease['c_lazy_image']['c_lazy_image_alt_attr']           = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$wth_variety_pod_tease['c_lazy_image']['c_lazy_image_srcset_attr']        = false;
	$wth_variety_pod_tease['c_lazy_image']['c_lazy_image_sizes_attr']         = false;

	array_push( $wth_variety_pod_tease_items, $wth_variety_pod_tease );
}

$wth_variety_pods['o_tease_list']['o_tease_list_items'] = $wth_variety_pod_tease_items;

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/wth-vy-podcasts.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$wth_variety_pods,
	true
);
