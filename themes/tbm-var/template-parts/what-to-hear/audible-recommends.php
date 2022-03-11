<?php
/**
 * Audible Recommends Template.
 *
 * @package pmc-variety-2020
 */

// Get data structure.
$aud_recommends = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/wth-stories-slider.prototype' );

$aud_recommends_data = \Variety\Inc\Carousels::get_carousel_posts(
	'vy-wth-audible-recs',
	10,
);

if ( empty( $aud_recommends_data ) || count( $aud_recommends_data ) < 5 ) {
	return;
}

$aud_recommends['o_sub_heading']['c_heading']['c_heading_text'] = __( 'Audible Recommends', 'pmc-variety' );
$aud_recommends['o_sub_heading']['c_span']['c_span_text']       = __( 'Partner Content', 'pmc-variety' );
$aud_recommends['o_sub_heading']['c_span']['c_span_classes']   .= ' u-colors-map-sponsored-90';

$aud_recommends['o_sub_heading']['c_dek']['c_dek_text'] = __( 'Our partners at Audible give their picks for the best audio books to consider.', 'pmc-variety' );

$aud_recommends_item_prototype = $aud_recommends['wth_stories_slider_items'][1];
$aud_recommends_items          = [];

foreach ( $aud_recommends_data as $key => $aud_recommend ) {

	$aud_recommends_item = $aud_recommends_item_prototype;
	if ( array_key_first( $aud_recommends_data ) === $key ) {
		$aud_recommends_item = $aud_recommends['wth_stories_slider_items'][0];
	}

	$aud_recommends_item['c_title']['c_title_text'] = $aud_recommend->post_title;
	$aud_recommends_item['c_title']['c_title_url']  = isset( $aud_recommend->url ) ? $aud_recommend->url : get_the_permalink( $aud_recommend );

	$hear_details = get_post_meta( $aud_recommend->parent_id, 'variety_hear_details', true );

	$aud_recommends_item['o_span_group']['o_span_group_items'][0]['c_span_text'] = ! empty( $hear_details['variety_ab_author'] ) ? $hear_details['variety_ab_author'] : '';
	$aud_recommends_item['o_span_group']['o_span_group_items'][1]['c_span_text'] = ! empty( $hear_details['variety_ab_narrator'] ) ? $hear_details['variety_ab_narrator'] : '';

	if ( ! empty( $hear_details['variety_watch_url'] ) ) {
		$aud_recommends_item['c_link']['c_link_url'] = $hear_details['variety_watch_url'];
	} else {
		$aud_recommends_item['c_link'] = false;
	}


	$aud_recommends_item['c_dek']['c_dek_text'] = isset( $aud_recommend->custom_excerpt ) ? $aud_recommend->custom_excerpt : wp_strip_all_tags( \PMC\Core\Inc\Helper::get_the_excerpt( $aud_recommend->ID ) );

	$image_id = isset( $aud_recommend->image_id ) ? $aud_recommend->image_id : get_post_thumbnail_id( $aud_recommend->ID );

	$aud_recommends_item['c_lazy_image']['c_lazy_image_link_url']           = isset( $aud_recommend->url ) ? $aud_recommend->url : get_the_permalink( $aud_recommend );
	$aud_recommends_item['c_lazy_image']['c_lazy_image_placeholder_url']    = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$aud_recommends_item['c_lazy_image']['c_lazy_image_src_url']            = \wp_get_attachment_image_url( $image_id, 'square-medium' );
	$aud_recommends_item['c_lazy_image']['c_lazy_image_screen_reader_text'] = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$aud_recommends_item['c_lazy_image']['c_lazy_image_alt_attr']           = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$aud_recommends_item['c_lazy_image']['c_lazy_image_srcset_attr']        = false;
	$aud_recommends_item['c_lazy_image']['c_lazy_image_sizes_attr']         = false;

	array_push( $aud_recommends_items, $aud_recommends_item );
}
$aud_recommends['wth_stories_slider_items'] = $aud_recommends_items;
?>
<div class="u-margin-b-125@tablet lrv-u-border-b-1 u-border-color-brand-secondary-40">
	<?php
	\PMC::render_template(
		sprintf( '%s/template-parts/patterns/modules/wth-stories-slider.php', untrailingslashit( CHILD_THEME_PATH ) ),
		$aud_recommends,
		true
	);
	?>
</div>
<?php
