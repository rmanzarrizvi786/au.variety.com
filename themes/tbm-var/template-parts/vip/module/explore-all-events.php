<?php
/**
 * More From VIP Template.
 *
 * @package pmc-variety
 */

use \Variety\Plugins\Variety_VIP\Content;

$data  = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/explore-all-events.variety-vip' );
$terms = [];

$curated = pmc_render_carousel(
	\PMC_Carousel::modules_taxonomy_name,
	'vip-explore-events',
	4,
	'',
	[
		'add_filler'           => false,
		'add_filler_all_posts' => false,
	]
);

$all_terms = get_terms(
	Content::VIP_PLAYLIST_TAXONOMY,
	[
		'orderby'    => 'name',
		'order'      => 'ASC',
		'hide_empty' => false,
		'number'     => 99,
	]
);

if ( ! empty( $curated ) ) {
	foreach ( $curated as $curated_term ) {
		$t = get_term( $curated_term['ID'] );

		if ( empty( $t ) ) {
			continue;
		}

		// Set custom curated values.
		$t->name     = $curated_term['title'];
		$t->image_id = $curated_term['image_id'];

		$terms[] = $t;
	}

	// Remove duplicates.
	$curated_term_ids = wp_list_pluck( $terms, 'term_id' );

	foreach ( $all_terms as $key => $curated_term ) {
		if ( in_array( $curated_term->term_id, (array) $curated_term_ids, true ) ) {
			unset( $all_terms[ $key ] );
		}
	}
}

$terms = array_merge( $terms, $all_terms );

if ( empty( $terms ) ) {
	return;
}

if ( count( $terms ) <= 4 ) {
	$data['o_more_link']         = [];
	$data['o_more_link_desktop'] = [];
}

$template = $data['explore_all_events_items'][0];

$data['explore_all_events_items']        = [];
$data['explore_all_events_hidden_items'] = [];

foreach ( $terms as $index => $current_term ) {
	$item = $template;

	$item['c_title']['c_title_text'] = $current_term->name;
	$item['c_title']['c_title_url']  = get_term_link( $current_term );

	if ( ! empty( $current_term->image_id ) ) {
		$thumbnail = $current_term->image_id;
	} else {
		$meta      = get_term_meta( $current_term->term_id, 'variety_playlist_featured_image', true );
		$thumbnail = $meta['featured_image'] ?? '';
	}

	if ( ! empty( $thumbnail ) ) {
		$image = \PMC\Core\Inc\Media::get_instance()->get_image_data( $thumbnail, 'landscape-large' );

		$item['c_lazy_image']['c_lazy_image_link_url']        = get_term_link( $current_term );
		$item['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
		$item['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
		$item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$item['c_lazy_image']['c_lazy_image_srcset_attr']     = wp_get_attachment_image_srcset( $thumbnail );
	} else {
		$item['c_lazy_image'] = [];
	}

	if ( $index <= 3 ) {
		$data['explore_all_events_items'][] = $item;
	} else {
		$data['explore_all_events_hidden_items'][] = $item;
	}
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/explore-all-events.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
