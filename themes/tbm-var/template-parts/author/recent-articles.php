<?php
/**
 * Recent Articles River
 *
 * Used on Author Pages
 *
 * @package pmc-variety
 */

global $wp_query, $paged;

$river = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/latest-news-river.author' );

$template = $river['o_tease_news_list_primary']['o_tease_list_items'][0];

$river['o_tease_news_list_primary']['o_tease_list_items']   = [];
$river['o_tease_news_list_secondary']['o_tease_list_items'] = [];

if ( true === is_paged() ) {
	$river['o_more_from_heading']['c_heading']['c_heading_text'] = __( 'More Articles', 'pmc-variety' );
} elseif ( is_archive() ) {

	$current_term = get_queried_object();

	if ( ! empty( $current_term->labels->archives ) ) {
		$river['o_more_from_heading']['c_heading']['c_heading_text'] = $current_term->labels->archives;
	}
}

$count = 1;

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();

		$item = $template;

		// Title.
		$item['c_title']['c_title_text'] = variety_get_card_title();
		$item['c_title']['c_title_url']  = get_permalink();

		// Featured Image/Video.
		$image = \PMC\Core\Inc\Media::get_instance()->get_image_data( get_post_thumbnail_id(), 'landscape-large' );

		if ( ! empty( $image['src'] ) ) {
			$item['c_lazy_image']['c_lazy_image_link_url']        = get_permalink();
			$item['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
			$item['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
			$item['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( get_post_thumbnail_id() );
			$item['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes( get_post_thumbnail_id() );
			$item['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
			$item['c_figcaption']['c_figcaption_caption_markup']  = $image['image_caption'];
			$item['c_figcaption']['c_figcaption_credit_text']     = $image['image_credit'];
		} else {
			$item['c_lazy_image'] = [];
		}

		$item['is_video'] = false;

		if ( PMC_Featured_Video_Override::get_instance()->has_featured_video( get_the_ID() ) || \Variety_Top_Videos::POST_TYPE_NAME === get_post_type( get_the_ID() ) ) {
			$item['is_video']            = true;
			$item['video_permalink_url'] = get_permalink();
		}

		// Vertical.
		$vertical = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( get_the_ID(), 'vertical' );

		if ( ! empty( $vertical ) ) {
			$item['o_taxonomy_item']['c_span']['c_span_text'] = $vertical->name;
			$item['o_taxonomy_item']['c_span']['c_span_url']  = get_term_link( $vertical );
		}

		if ( 'variety_vip_post' === get_post_type() ) {
			$item['o_taxonomy_item']['c_span']['c_span_text']         = __( 'VIP+', 'pmc-variety' );
			$item['o_taxonomy_item']['c_span']['c_span_url']          = \Variety\Plugins\Variety_VIP\VIP::vip_url();
			$item['o_taxonomy_item']['c_span']['c_span_link_classes'] = str_replace( 'u-color-pale-sky-2', 'u-color-brand-vip-primary', $item['o_taxonomy_item']['c_span']['c_span_link_classes'] );
		}

		// Time.
		$item['c_timestamp']['c_timestamp_text'] = variety_human_time_diff( get_the_ID() );

		if ( $count <= 4 ) {
			$river['o_tease_news_list_primary']['o_tease_list_items'][] = $item;
		} else {
			$river['o_tease_news_list_secondary']['o_tease_list_items'][] = $item;
		}

		$count ++;
	}

}

// Previous / Next.
$next_post_link = get_next_posts_link() ? next_posts( 0, false ) : false;

if ( ! empty( $next_post_link ) ) {
	$river['o_more_link']['c_link']['c_link_url']           = $next_post_link;
	$river['o_more_link_previous']['c_link']['c_link_text'] = __( 'More News', 'pmc-variety' );
} else {
	// Hide link if there are no next posts
	$river['o_more_link']['c_link'] = false;
}

$prev_post_link = get_previous_posts_link() ? previous_posts( false ) : false;

// Previous.
if ( ! empty( $prev_post_link ) ) {
	$river['latest_news_river_is_paged']                    = true;
	$river['o_more_link_previous']['c_link']['c_link_url']  = $prev_post_link;
	$river['o_more_link_previous']['c_link']['c_link_text'] = __( 'Previous', 'pmc-variety' );
} else {
	$river['o_more_link_previous']['c_link'] = false;
}

\PMC::render_template(
	sprintf(
		'%s/template-parts/patterns/modules/latest-news-river.php',
		untrailingslashit( CHILD_THEME_PATH )
	),
	$river,
	true
);
