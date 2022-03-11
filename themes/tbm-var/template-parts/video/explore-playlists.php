<?php
/**
 * Explore Playlists.
 *
 * @package pmc-variety
 */

if ( is_tax( 'vcategory' ) && ! is_tax( 'vcategory', 'contenders' ) ) {
	$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/explore-playlists.full' );
} else {
	$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/explore-playlists.prototype' );
}

$curated = pmc_render_carousel(
	\PMC_Carousel::modules_taxonomy_name,
	'explore-playlists',
	5,
	'',
	[
		'add_filler'           => false,
		'add_filler_all_posts' => false,
	]
);

if ( empty( $curated ) ) {
	return;
}

$template = $data['explore_playlists_items'][0];

$data['explore_playlists_items'] = [];

foreach ( $curated as $t ) {
	$item  = $template;
	$_term = get_term( $t['ID'] );

	if ( empty( $_term ) ) {
		continue;
	}

	// Set custom curated values.
	$_term->name     = $t['title'];
	$_term->image_id = $t['image_id'];

	$item['o_card']['c_title']['c_title_text'] = $_term->name;
	$item['o_card']['c_title']['c_title_url']  = get_term_link( $_term );

	if ( ! empty( $_term->image_id ) ) {
		$thumbnail = $_term->image_id;
	} else {
		$thumbnail = get_term_meta( $_term->term_id, 'vcat-image-id', true );
	}

	if ( ! empty( $thumbnail ) ) {
		$image = \PMC\Core\Inc\Media::get_instance()->get_image_data( $thumbnail, 'landscape-large' );

		$item['o_card']['c_lazy_image']['c_lazy_image_link_url']        = get_term_link( $_term );
		$item['o_card']['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
		$item['o_card']['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
		$item['o_card']['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$item['o_card']['c_lazy_image']['c_lazy_image_srcset_attr']     = wp_get_attachment_image_srcset( $thumbnail );
	} else {
		$item['o_card']['c_lazy_image'] = [];
	}

	$item['o_card']['c_span']['c_span_text'] = sprintf( '%d %s', $_term->count, __( 'Videos', 'pmc-variety' ) );

	$data['explore_playlists_items'][] = $item;
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/explore-playlists.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
