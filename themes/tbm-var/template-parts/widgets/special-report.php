<?php
/**
 * Special Report module.
 *
 * @package pmc-variety
 */

if ( empty( $data['articles'] ) ) {
	return;
}

$special = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/homepage-voices.special' );

$template = $special['o_tease_list']['o_tease_list_items'][0];

$special['o_tease_list']['o_tease_list_items'] = [];

foreach ( $data['articles'] as $_post ) {
	$populate = new \Variety\Inc\Populate( $_post, $template );
	$item     = $populate->get();

	$special['o_tease_list']['o_tease_list_items'][] = $item;
}

$special['o_more_from_heading']['c_heading']['c_heading_text'] = $data['title'];
$special['c_span_subtitle']['c_span_text']                     = $data['mobile_sub_title'];
$special['o_more_link']['c_link']['c_link_text']               = $data['more_text'];
$special['o_more_link']['c_link']['c_link_url']                = $data['more_link'];

// Mobile lazy image.
if ( ! empty( $data['mobile_image'] ) ) {
	$special['c_lazy_image_mobile']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$special['c_lazy_image_mobile']['c_lazy_image_srcset_attr']     = '';
	$special['c_lazy_image_mobile']['c_lazy_image_sizes_attr']      = '';
	$special['c_lazy_image_mobile']['c_lazy_image_src_url']         = $data['mobile_image'];
} else {
	$special['c_lazy_image_mobile'] = [];
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/homepage-voices.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$special,
	true
);
