<?php
/**
 * Special Report Grid VIP Template.
 *
 * @package pmc-variety
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/special-report-landing-grid.prototype' );

global $wp_query, $paged;

$latest   = $wp_query->posts;
$template = $data['special_report_landing_items'][0];

$data['special_report_landing_items'] = [];

if ( ! empty( $latest ) ) {
	foreach ( $latest as $_post ) {
		$item = $template;

		$item['o_card']['c_title']['c_title_text'] = pmc_get_title( $_post );
		$item['o_card']['c_title']['c_title_url']  = get_permalink( $_post );


		$report_details = get_post_meta( $_post->ID, 'variety_special_report', true );

		if ( ! empty( $report_details['report_details']['cover_image'] ) ) {

			$image = \PMC\Core\Inc\Media::get_instance()->get_image_data( $report_details['report_details']['cover_image'], 'landscape-large' );

			$item['o_card']['c_lazy_image']['c_lazy_image_link_url']        = get_permalink( $_post );
			$item['o_card']['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
			$item['o_card']['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
			$item['o_card']['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
			$item['o_card']['c_lazy_image']['c_lazy_image_srcset_attr']     = wp_get_attachment_image_srcset( $report_details['report_details']['cover_image'] );

		} else {

			$thumbnail = get_post_thumbnail_id( $_post );
			$image     = \PMC\Core\Inc\Media::get_instance()->get_image_data( $thumbnail, 'landscape-large' );

			if ( ! empty( $thumbnail ) ) {

				$item['o_card']['c_lazy_image']['c_lazy_image_link_url']        = get_permalink( $_post );
				$item['o_card']['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
				$item['o_card']['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
				$item['o_card']['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
				$item['o_card']['c_lazy_image']['c_lazy_image_srcset_attr']     = wp_get_attachment_image_srcset( $thumbnail );

			} else {

				$item['o_card']['c_lazy_image']['c_lazy_image_link_url']        = '';
				$item['o_card']['c_lazy_image']['c_lazy_image_alt_attr']        = '';
				$item['o_card']['c_lazy_image']['c_lazy_image_src_url']         = '';
				$item['o_card']['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
				$item['o_card']['c_lazy_image']['c_lazy_image_srcset_attr']     = '';

			}
		}
		$author = \PMC\Core\Inc\Author::get_instance()->authors_data( $_post->ID );

		if ( ! empty( $author['byline'] ) ) {
			$item['o_card']['c_tagline']['c_tagline_markup'] = sprintf( 'By %1$s', $author['byline'] );
		} else {
			$item['o_card']['c_tagline']['c_tagline_markup'] = '';
		}

		$item['o_card']['c_dek']['c_dek_text'] = \PMC\Core\Inc\Helper::get_the_excerpt( $_post->ID );

		$data['special_report_landing_items'][] = $item;
	}
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/special-report-landing-grid.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
