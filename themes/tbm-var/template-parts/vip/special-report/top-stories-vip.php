<?php
/**
 * Top Story VIP Template.
 *
 * @package pmc-variety
 */

use Variety\Plugins\Variety_VIP\Special_Reports;
use Variety\Plugins\Variety_VIP\Content;

$data   = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/special-report-landing-top.prototype' );
$_posts = \Variety\Plugins\Variety_VIP\Carousel::get_vip_carousel_posts( 'vip-special-reports', 1, false, [ Special_Reports::POST_TYPE ] );

if ( empty( $_posts[0] ) ) {
	return;
}

$_post = $_posts[0];

$data['o_top_story']['c_title']['c_title_text'] = variety_get_card_title( $_post );
$data['o_top_story']['c_title']['c_title_url']  = get_permalink( $_post );

if ( ! empty( $_post->custom_excerpt ) ) {
	$data['o_top_story']['c_dek']['c_dek_text'] = wp_strip_all_tags( $_post->custom_excerpt );
} else {
	$data['o_top_story']['c_dek']['c_dek_text'] = \PMC\Core\Inc\Helper::get_the_excerpt( $_post->ID );
}

if ( ! empty( $_post->image_id ) ) {
	$thumbnail = $_post->image_id;
} else {
	$thumbnail = get_post_thumbnail_id( $_post );
}

$image = \PMC\Core\Inc\Media::get_instance()->get_image_data( $thumbnail, 'landscape-large' );

if ( ! empty( $thumbnail ) ) {

	$data['o_top_story']['c_lazy_image']['c_lazy_image_link_url']        = get_permalink( $_post );
	$data['o_top_story']['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
	$data['o_top_story']['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
	$data['o_top_story']['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$data['o_top_story']['c_lazy_image']['c_lazy_image_srcset_attr']     = wp_get_attachment_image_srcset( $thumbnail );

} else {

	$data['o_top_story']['c_lazy_image']['c_lazy_image_link_url']        = '';
	$data['o_top_story']['c_lazy_image']['c_lazy_image_alt_attr']        = '';
	$data['o_top_story']['c_lazy_image']['c_lazy_image_src_url']         = '';
	$data['o_top_story']['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$data['o_top_story']['c_lazy_image']['c_lazy_image_srcset_attr']     = '';

}

$category = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $_post->ID, Content::VIP_CATEGORY_TAXONOMY );

if ( ! empty( $category ) ) {
	$data['o_top_story']['o_indicator']['c_span']['c_span_text'] = $category->name;
	$data['o_top_story']['o_indicator']['c_span']['c_span_url']  = get_term_link( $category );
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/special-report-landing-top.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
