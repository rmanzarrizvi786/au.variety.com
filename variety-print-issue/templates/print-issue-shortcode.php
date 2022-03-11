<?php

$post_with_issue_id = get_the_ID();

$issue_data = \Variety\Plugins\Variety_Print_Issue\Print_Issue_Shortcode::get_instance()->get_print_issue_data( $post_with_issue_id );

if ( empty( $issue_data ) ) {
	return;
}

$print_issue      = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/print-issue.prototype' );
$cover_image_data = \PMC\Core\Inc\Media::get_instance()->get_image_data( $issue_data['image_id'] );

$print_issue['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
$print_issue['c_lazy_image']['c_lazy_image_src_url']         = $cover_image_data['src'] ?? '';
$print_issue['c_lazy_image']['c_lazy_image_alt_attr']        = $cover_image_data['image_alt'] ?? '';
$print_issue['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( $issue_data['image_id'] ) ?? false;
$print_issue['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes( $issue_data['image_id'] ) ?? false;

$print_issue['o_more_link']['c_link']['c_link_text'] = __( 'Subscribe Today', 'pmc-variety' );
$print_issue['o_more_link']['c_link']['c_link_url']  = '/variety-magazine-subscribe/?utm_source=site&utm_medium=VAR_Featured&utm_campaign=MagShop';

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/print-issue.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$print_issue,
	true
);
