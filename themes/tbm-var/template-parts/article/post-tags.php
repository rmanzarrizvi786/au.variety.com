<?php
$tags_data = \PMC\Core\Inc\Theme::get_instance()->get_post_terms( get_the_ID() );

$article_tags = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/article-tags.prototype' );

$article_tags_prototype = $article_tags['o_nav']['o_nav_list_items'][0];
$article_tags_list      = [];

if ( ! empty( $tags_data['post_tag'] ) && is_array( $tags_data['post_tag'] ) ) {
	foreach ( $tags_data['post_tag'] as $key => $post_tag ) {

		$article_tag = $article_tags_prototype;
		$comma       = ( $key < ( count( $tags_data['post_tag'] ) - 1 ) ) ? ',' : '';

		$article_tag['c_link_url']  = get_tag_link( $post_tag->term_id );
		$article_tag['c_link_text'] = $post_tag->name ? $post_tag->name . $comma : '';

		$article_tags_list[] = $article_tag;
	}
}

if ( empty( $article_tags_list ) ) {
	return;
}

$article_tags['o_nav']['o_nav_list_items'] = $article_tags_list;

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/article-tags.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$article_tags,
	true
);
