<?php
/**
 * Article Header Template.
 *
 * @package pmc-variety
 */

$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/article-header.variety-vip' );

// Post data.
$data['o_title']['c_heading']['c_heading_text']          = get_the_title();
$data['article_meta']['c_timestamp']['c_timestamp_text'] = get_the_time( 'F j, Y g:ia' ) . ' PT';

// Breadcrumb.
$breadcrumb = \Variety\Plugins\Variety_VIP\Content::get_instance()->get_breadcrumb();

$data['article_meta']['breadcrumbs']['o_nav']['o_nav_list_items']                  = [ $data['article_meta']['breadcrumbs']['o_nav']['o_nav_list_items'][0] ];
$data['article_meta']['breadcrumbs']['o_nav']['o_nav_list_items'][0]['c_link_url'] = '/vip/';

if ( ! empty( $breadcrumb ) ) {
	foreach ( $breadcrumb as $crumb ) {
		$term_link = get_term_link( $crumb );

		if ( empty( $crumb->name ) || empty( $term_link ) || is_wp_error( $term_link ) ) {
			continue;
		}

		$breadcrumb_item                = $data['article_meta']['breadcrumbs']['o_nav']['o_nav_list_items'][0];
		$breadcrumb_item['c_link_text'] = $crumb->name;
		$breadcrumb_item['c_link_url']  = $term_link;


		$data['article_meta']['breadcrumbs']['o_nav']['o_nav_list_items'][] = $breadcrumb_item;
	}
}

// Author.
$author_data = PMC\Core\Inc\Author::get_instance()->authors_data();

if ( ! empty( $author_data['single_author'] ) ) {

	$author_url = get_author_posts_url( $author_data['single_author']['author']->ID, $author_data['single_author']['author']->user_nicename );

	if ( ! empty( $author_data['single_author']['picture']['image'] ) ) {
		$data['author_social']['author']['c_lazy_image']['c_lazy_image_src_url']     = $author_data['single_author']['picture']['image'];
		$data['author_social']['author']['c_lazy_image']['c_lazy_image_alt_attr']    = $author_data['single_author']['picture']['name'] ?? '';
		$data['author_social']['author']['c_lazy_image']['c_lazy_image_srcset_attr'] = \wp_get_attachment_image_srcset( get_post_thumbnail_id() );
		$data['author_social']['author']['c_lazy_image']['c_lazy_image_sizes_attr']  = \wp_get_attachment_image_sizes( get_post_thumbnail_id() );
	} else {
		$data['author_social']['author']['c_lazy_image'] = false;
	}

	$data['author_social']['author']['author_details']['c_tagline']['c_tagline_text'] = $author_data['single_author']['more_info']['author_role'];

	if ( ! empty( $author_data['single_author']['more_info']['twitter'] ) ) {
		$data['author_social']['author']['author_details']['c_link_twitter_profile']['c_link_text'] = $author_data['single_author']['more_info']['twitter']['handle'];
		$data['author_social']['author']['author_details']['c_link_twitter_profile']['c_link_url']  = $author_data['single_author']['more_info']['twitter']['link'];
	} else {
		$data['author_social']['author']['author_details']['c_link_twitter_profile'] = false;
	}

	$data['author_social']['author']['c_link']['c_link_text'] = $author_data['single_author']['author']->display_name;
	$data['author_social']['author']['c_link']['c_link_url']  = $author_url;

	$data['author_social']['author']['author_details']['c_title']['c_title_url']    = $author_url;
	$data['author_social']['author']['author_details']['c_title']['c_title_markup'] = $author_data['single_author']['author']->display_name;

	$author_article_ids       = PMC\Core\Inc\Author::get_instance()->get_author_posts( $author_data['single_author']['author']->user_nicename );
	$author_article_prototype = $data['author_social']['author']['author_details']['stories'][0];
	$author_article_list      = [];

	if ( ! empty( $author_article_ids ) && is_array( $author_article_ids ) ) {

		foreach ( $author_article_ids as $article_id ) {

			$article_item = $author_article_prototype;

			$article_item['c_link']['c_link_text']           = pmc_get_title( $article_id );
			$article_item['c_link']['c_link_url']            = get_the_permalink( $article_id );
			$article_item['c_timestamp']['c_timestamp_text'] = sprintf( '%1$s %2$s', human_time_diff( get_the_time( 'U', $article_id ), current_time( 'timestamp' ) ), __( 'ago', 'pmc-variety' ) );

			$author_article_list[] = $article_item;
		}
	}

	$data['author_social']['c_timestamp']['c_timestamp_text'] = get_the_time( 'F j, Y g:ia' ) . ' PT';

	$data['author_social']['author']['author_details']['stories'] = $author_article_list;

	$data['author_social']['author']['author_details']['c_link_view_all']['c_link_url'] = $author_url;

} else {

	$data['author_social']['author']['is_byline_only']                = true;
	$data['author_social']['author']['c_tagline']['c_tagline_markup'] = $author_data['byline'];

}

// Social.
$social_share_data = \PMC\Core\Inc\Sharing::get_instance()->get_icons();

if ( \PMC\Core\Inc\Sharing::has_icons( $social_share_data ) ) {

	$social_icon_prototype = $data['author_social']['social_share']['primary'][0];

	$primary_share_items   = [];
	$secondary_share_items = [];

	if ( ! empty( $social_share_data['primary'] ) && is_array( $social_share_data['primary'] ) ) {

		foreach ( $social_share_data['primary'] as $icon_name => $icon_data ) {

			$share_icon = $social_icon_prototype;

			$share_icon['c_icon_url']          = $icon_data->url;
			$share_icon['c_icon_name']         = $icon_name;
			$share_icon['c_icon_rel_name']     = $icon_name;
			$share_icon['c_icon_link_classes'] = sprintf( '%1$s u-color-%2$s:hover', $share_icon['c_icon_link_classes'], $icon_name );

			$primary_share_items[] = $share_icon;
		}
	}

	if ( ! empty( $social_share_data['secondary'] ) && is_array( $social_share_data['secondary'] ) ) {

		foreach ( $social_share_data['secondary'] as $icon_name => $icon_data ) {

			$share_icon = $social_icon_prototype;

			$share_icon['c_icon_url']          = $icon_data->url;
			$share_icon['c_icon_name']         = $icon_name;
			$share_icon['c_icon_rel_name']     = $icon_name;
			$share_icon['c_icon_link_classes'] = sprintf( '%1$s u-color-%2$s:hover', $share_icon['c_icon_link_classes'], $icon_name );

			$secondary_share_items[] = $share_icon;
		}
	}

	$data['author_social']['social_share']['primary']   = $primary_share_items;
	$data['author_social']['social_share']['secondary'] = $secondary_share_items;

} else {

	$data['author_social']['social_share']['primary']   = [];
	$data['author_social']['social_share']['secondary'] = [];

}

// Featured Image.
$image_size = 'landscape-large';
$image      = \PMC\Core\Inc\Media::get_instance()->get_image_data_by_post( get_the_ID(), $image_size );

if ( ! empty( $image['src'] ) ) {
	$data['o_figure']['o_figure_link_url']                            = get_permalink();
	$data['o_figure']['o_figure_link_classes']                        = 'u-flex-order-n1@mobile-max';
	$data['o_figure']['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
	$data['o_figure']['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$data['o_figure']['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset( get_post_thumbnail_id(), $image_size );
	$data['o_figure']['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes( get_post_thumbnail_id(), $image_size );
	$data['o_figure']['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
	$data['o_figure']['c_figcaption']['c_figcaption_caption_markup']  = $image['image_caption'];
	$data['o_figure']['c_figcaption']['c_figcaption_credit_text']     = $image['image_credit'];
} else {
	$data['o_figure'] = [];
}

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/article-header.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
