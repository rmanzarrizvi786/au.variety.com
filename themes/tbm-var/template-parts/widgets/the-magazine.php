<?php
/**
 * The magazine module.
 *
 * @package pmc-variety
 */

if ( empty( $data['issues'] ) ) {
	return;
}

$magazine = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/homepage-voices.magazine' );

$template = $magazine['o_tease_list']['o_tease_list_items'][0];

$magazine['o_tease_list']['o_tease_list_items'] = [];
$i = 0;
foreach ( $data['issues'] as $issue ) {
	if ( ! $issue instanceof \WP_Post ) {
		continue;
	}

	$item = $template;

	if ( ! empty( $issue->url ) ) {
		$url = $issue->url;
	} else {
		$url = get_permalink( $issue );
	}

	if ( ! empty( $issue->image_id ) ) {
		$cover_image_id = $issue->image_id;
	} else {
		$cover_image_id = get_post_thumbnail_id( $issue->ID );
	}

	$image_data = \PMC\Core\Inc\Media::get_instance()->get_image_data( $cover_image_id );

	if ( ! empty( $cover_image_id ) && ! empty( $image_data ) ) {
		$item['c_lazy_image']['c_lazy_image_link_url']        = $url;
		$item['c_lazy_image']['c_lazy_image_alt_attr']        = $image_data['image_alt'];
		$item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$item['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( $cover_image_id );
		$item['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes( $cover_image_id );
		$item['c_lazy_image']['c_lazy_image_src_url']         = $image_data['src'];
		$item['c_lazy_image']['c_figcaption_caption_markup']  = $image_data['image_caption'];
		$item['c_lazy_image']['c_figcaption_credit_text']     = $image_data['image_credit'];
	} else {
		$item['c_lazy_image'] = [];
	}

	$item['c_title']['c_title_text'] = get_the_title( $issue );
	$item['c_title']['c_title_url']  = $url;

	if ( 0 < $i ) {
		$item['o_tease_classes'] = $item['o_tease_classes'] . ' a-hidden@mobile-max';
	} else {
		$data['mobile_text'] = get_the_title( $issue );
	}

	$magazine['o_tease_list']['o_tease_list_items'][] = $item;

	if ( 0 === $i && \PMC::is_mobile() ) {
		break;
	}

	$i++;
}

$magazine['c_link']['c_link_url']                 = '/variety-magazine-subscribe/?utm_source=site_home&utm_medium=MagModule&utm_campaign=MagShop';
$magazine['c_span_subtitle']['c_span_text']       = $data['mobile_text'];
$magazine['c_footer_tagline']['c_tagline_markup'] = sprintf(
	'%s <a href="%s">%s</a>',
	__( 'Already a subscriber?', 'pmc-variety' ),
	'/access-digital/',
	__( 'Access your digital edition', 'pmc-variety' )
);

$magazine['o_more_link']['c_link']['c_link_text'] = __( 'More Cover Stories', 'pmc-variety' );
$magazine['o_more_link']['c_link']['c_link_url']  = '/e/cover-story/';

if ( ! empty( $data['more_text'] ) ) {
	$magazine['o_more_link']['c_link']['c_link_text'] = $data['more_text'];
}

if ( ! empty( $data['more_link'] ) ) {
	$magazine['o_more_link']['c_link']['c_link_url'] = $data['more_link'];
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/homepage-voices.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$magazine,
	true
);
