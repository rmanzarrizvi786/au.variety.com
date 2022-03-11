<?php
/**
 * Archive Trending Topics Template.
 *
 * @package pmc-variety
 */

use Variety\Plugins\Variety_VIP\Content;

if ( is_page_template( 'page-vip.php' ) ) {
	$topics = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/trending-topics.homepage' );
} else {
	$topics = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/trending-topics.variety-vip' );
}

if ( ! empty( $trending_topics_classes ) ) {
	$topics['trending_topics_classes'] = $trending_topics_classes;
}

// Get trending topics.
$menu  = wp_get_nav_menu_name( 'pmc_variety_vip_trending' );
$menu  = wp_get_nav_menu_object( $menu );
$items = wp_get_nav_menu_items( $menu->term_id );

if ( ! empty( $items ) ) {
	$template                  = $topics['trending_topics'][0];
	$topics['trending_topics'] = [];

	foreach ( $items as $item ) {
		if ( 'taxonomy' === $item->type && empty( $item->description ) ) {
			$description = wp_strip_all_tags( term_description( $item->object_id ) );
		} else {
			$description = $item->description;
		}

		$topic                                = $template;
		$topic['o_topic_url']                 = $item->url;
		$topic['c_heading']['c_heading_text'] = $item->title;
		$topic['c_tagline']['c_tagline_text'] = $description;

		$topics['trending_topics'][] = $topic;
	}
} else {
	$topics['trending_topics'] = [];
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/trending-topics.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$topics,
	true
);
