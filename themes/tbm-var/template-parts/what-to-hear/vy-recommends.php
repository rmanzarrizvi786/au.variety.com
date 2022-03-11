<?php
/**
 * Audible Recommends Template.
 *
 * @package pmc-variety-2020
 */

// Get data structure.
$vy_recommends = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/wth-stories-slider.prototype' );

$vy_recommends_data = \Variety\Inc\Carousels::get_carousel_posts(
	'vy-wth-variety-recs',
	10,
);

if ( empty( $vy_recommends_data ) || count( $vy_recommends_data ) < 5 ) {
	return;
}

// Get global curation settings.
$settings = get_option( 'global_curation', [] );
$settings = isset( $settings['tab_variety_what_to_hear'] ) ? $settings['tab_variety_what_to_hear'] : false;

$vy_recommends['o_sub_heading']['c_heading']['c_heading_text'] = __( 'Variety Recommends', 'pmc-variety' );
$vy_recommends['o_sub_heading']['c_span']['c_span_text']       = __( 'Audiobooks', 'pmc-variety' );
$vy_recommends['o_sub_heading']['c_dek']['c_dek_text']         = __( 'Dive in to the best audiobooks for your next vacation or long car dive getting Hollywood\'s attention.', 'pmc-variety' );

if ( ! empty( $settings['variety_vy_recommends_header_copy'] ) ) {
	$vy_recommends['o_sub_heading']['c_heading']['c_heading_text'] = $settings['variety_vy_recommends_header_copy'];
}

if ( ! empty( $settings['variety_vy_recommends_logline_copy'] ) ) {
	$vy_recommends['o_sub_heading']['c_dek']['c_dek_text'] = $settings['variety_vy_recommends_logline_copy'];
}

$vy_recommends_item_prototype = $vy_recommends['wth_stories_slider_items'][1];
$vy_recommends_items          = [];

foreach ( $vy_recommends_data as $key => $vy_recommend ) {

	$vy_recommends_item = $vy_recommends_item_prototype;
	if ( array_key_first( $vy_recommends_data ) === $key ) {
		$vy_recommends_item = $vy_recommends['wth_stories_slider_items'][0];
	}

	$vy_recommends_item['c_title']['c_title_text'] = $vy_recommend->post_title;
	$vy_recommends_item['c_title']['c_title_url']  = isset( $vy_recommend->url ) ? $vy_recommend->url : get_the_permalink( $vy_recommend );

	$hear_details = get_post_meta( $vy_recommend->parent_id, 'variety_hear_details', true );

	$vy_recommends_item['o_span_group']['o_span_group_items'][0]['c_span_text'] = ! empty( $hear_details['variety_ab_author'] ) ? $hear_details['variety_ab_author'] : '';
	$vy_recommends_item['o_span_group']['o_span_group_items'][1]['c_span_text'] = ! empty( $hear_details['variety_ab_narrator'] ) ? $hear_details['variety_ab_narrator'] : '';

	if ( ! empty( $hear_details['variety_watch_url'] ) ) {
		$vy_recommends_item['c_link']['c_link_url'] = $hear_details['variety_watch_url'];
	} else {
		$vy_recommends_item['c_link'] = false;
	}


	$vy_recommends_item['c_dek']['c_dek_text'] = isset( $vy_recommend->custom_excerpt ) ? $vy_recommend->custom_excerpt : wp_strip_all_tags( \PMC\Core\Inc\Helper::get_the_excerpt( $vy_recommend->ID ) );

	$image_id = isset( $vy_recommend->image_id ) ? $vy_recommend->image_id : get_post_thumbnail_id( $vy_recommend->ID );

	$vy_recommends_item['c_lazy_image']['c_lazy_image_link_url']           = isset( $vy_recommend->url ) ? $vy_recommend->url : get_the_permalink( $vy_recommend );
	$vy_recommends_item['c_lazy_image']['c_lazy_image_placeholder_url']    = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$vy_recommends_item['c_lazy_image']['c_lazy_image_src_url']            = \wp_get_attachment_image_url( $image_id, 'square-medium' );
	$vy_recommends_item['c_lazy_image']['c_lazy_image_screen_reader_text'] = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$vy_recommends_item['c_lazy_image']['c_lazy_image_alt_attr']           = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$vy_recommends_item['c_lazy_image']['c_lazy_image_srcset_attr']        = false;
	$vy_recommends_item['c_lazy_image']['c_lazy_image_sizes_attr']         = false;

	array_push( $vy_recommends_items, $vy_recommends_item );
}
$vy_recommends['wth_stories_slider_items'] = $vy_recommends_items;
?>
<div class="u-margin-b-2@tablet lrv-u-border-b-1 u-border-color-brand-secondary-40">
	<?php
	\PMC::render_template(
		sprintf( '%s/template-parts/patterns/modules/wth-stories-slider.php', untrailingslashit( CHILD_THEME_PATH ) ),
		$vy_recommends,
		true
	);
	?>
</div>
<?php
