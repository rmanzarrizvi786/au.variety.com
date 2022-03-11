<?php
/**
 * Recommended For You module.
 *
 * @package pmc-variety
 */

use Variety\Plugins\Variety_VIP\Content;

if ( empty( $data['articles'] ) ) {
	return;
}

$recommended = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/recommended-carousel.prototype' );

$template     = $recommended['explore_playlists']['explore_playlists_items'][1];
$vip_template = $recommended['explore_playlists']['explore_playlists_items'][0];

$recommended['explore_playlists']['explore_playlists_items'] = [];

$img_options = [
	'image_size'           => 'landscape-small',
	'image_srcset_enabled' => false,
];

foreach ( $data['articles'] as $_post ) {
	if ( in_array( get_post_type( $_post ), [ Content::VIP_POST_TYPE, Content::VIP_VIDEO_POST_TYPE ], true ) ) {
		$vip      = true;
		$populate = new \Variety\Inc\Populate( $_post, $vip_template, $img_options );
	} else {
		$vip      = false;
		$populate = new \Variety\Inc\Populate( $_post, $template, $img_options );
	}

	$item = $populate->get();

	if ( $vip ) {
		$item['c_span']['c_span_text'] = __( 'VIP+', 'pmc-variety' );
		$item['c_span']['c_span_url']  = \Variety\Plugins\Variety_VIP\VIP::vip_url();
	}

	$recommended['explore_playlists']['explore_playlists_items'][] = $item;
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/recommended-carousel.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$recommended,
	true
);
