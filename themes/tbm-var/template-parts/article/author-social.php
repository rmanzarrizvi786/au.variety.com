<?php
/**
 * Author Social Module
 *
 * Note: This is redundant to what is in article-header.
 * Since we want to use the author-social in another context,
 * it is useful to break out this object preparation code
 * into another file.
 */

if ( \Variety\Inc\Featured_Article::get_instance()->is_featured_article() ) {
	$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/author-social.featured-article' );
} else {
	$data = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/author-social.prototype' );
}

// Author.
$author_data = PMC\Core\Inc\Author::get_instance()->authors_data();

if ( ! empty( $author_data['single_author'] ) ) {

	$author_url = get_author_posts_url( $author_data['single_author']['author']->ID, $author_data['single_author']['author']->user_nicename );

	if ( ! empty( $author_data['single_author']['picture']['image'] ) ) {
		$data['author']['c_lazy_image']['c_lazy_image_src_url']     = $author_data['single_author']['picture']['image'];
		$data['author']['c_lazy_image']['c_lazy_image_alt_attr']    = $author_data['single_author']['picture']['name'] ?? '';
		$data['author']['c_lazy_image']['c_lazy_image_srcset_attr'] = \wp_get_attachment_image_srcset( get_post_thumbnail_id() );
		$data['author']['c_lazy_image']['c_lazy_image_sizes_attr']  = \wp_get_attachment_image_sizes( get_post_thumbnail_id() );
	} else {
		$data['author']['c_lazy_image'] = false;
	}

	$data['author']['author_details']['c_tagline']['c_tagline_text'] = $author_data['single_author']['more_info']['author_role'];

	if ( ! empty( $author_data['single_author']['more_info']['twitter'] ) ) {
		$data['author']['author_details']['c_link_twitter_profile']['c_link_text'] = $author_data['single_author']['more_info']['twitter']['handle'];
		$data['author']['author_details']['c_link_twitter_profile']['c_link_url']  = $author_data['single_author']['more_info']['twitter']['link'];
	} else {
		$data['author']['author_details']['c_link_twitter_profile'] = false;
	}

	$data['author']['c_link']['c_link_text'] = $author_data['single_author']['author']->display_name;
	$data['author']['c_link']['c_link_url']  = $author_url;

	$data['author']['author_details']['c_title']['c_title_url']    = $author_url;
	$data['author']['author_details']['c_title']['c_title_markup'] = $author_data['single_author']['author']->display_name;

	$author_article_ids       = PMC\Core\Inc\Author::get_instance()->get_author_posts( $author_data['single_author']['author']->user_nicename );
	$author_article_prototype = $data['author']['author_details']['stories'][0];
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

	$data['author']['author_details']['stories'] = $author_article_list;

	$data['author']['author_details']['c_link_view_all']['c_link_url'] = $author_url;

} else {
	$data['author']['is_byline_only']                = true;
	$data['author']['c_tagline']['c_tagline_markup'] = $author_data['byline'];
}

$data['author']['is_byline_only']                = true;
$data['author']['c_tagline']['c_tagline_markup'] = $author_data['byline'];

// Comment count - not on Featured Article.
if ( ! \Variety\Inc\Featured_Article::get_instance()->is_featured_article() ) {
	$data['o_comments_link']['c_link']['c_link_text'] = '';
}

// Social.
$social_share_data = \PMC\Core\Inc\Sharing::get_instance()->get_icons();

if ( \PMC\Core\Inc\Sharing::has_icons( $social_share_data ) ) {

	$social_icon_prototype = $data['social_share']['primary'][0];

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

	$data['social_share']['primary']   = $primary_share_items;
	$data['social_share']['secondary'] = $secondary_share_items;

} else {

	$data['social_share']['primary']   = [];
	$data['social_share']['secondary'] = [];

}

$photo_tagline = get_post_meta( get_the_ID(), '_variety_photos_tagline', true );

if ( ! empty( $photo_tagline ) ) {
	$photo_tagline = __( 'Photographs by ', 'pmc-variety' ) . $photo_tagline;
}

$data['author']['c_tagline_optional']['c_tagline_text'] = $photo_tagline;

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/author-social.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$data,
	true
);
