<?php
/**
 * Top Stories Template.
 *
 * @package pmc-variety-2020
 */

// Get default docs stories row data.
$stories = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/docs-stories-row.what-to-hear' );

$stories_data = \Variety\Inc\Carousels::get_carousel_posts(
	'vy-wth-leads',
	5,
);

if ( empty( $stories_data ) || count( $stories_data ) < 5 ) {
	return;
}

$stories['c_span']                = false;
$stories['large_story']['c_span'] = false;

$o_card_prototype  = $stories['stories_row_items'][0];
$stories_row_items = [];

foreach ( $stories_data as $key => $review ) {
	// If first review, set to large top story.
	if ( array_key_first( $stories_data ) === $key ) {
		$stories['large_story']['c_title']['c_title_text'] = $review->post_title;
		$stories['large_story']['c_title']['c_title_url']  = isset( $review->url ) ? $review->url : get_the_permalink( $review );

		$stories['large_story']['c_dek']['c_dek_text'] = isset( $review->custom_excerpt ) ? $review->custom_excerpt : wp_strip_all_tags( \PMC\Core\Inc\Helper::get_the_excerpt( $review->ID ) );

		$author_data = PMC\Core\Inc\Author::get_instance()->authors_data( $review->ID );
		$author_url  = get_author_posts_url( $author_data['single_author']['author']->ID, $author_data['single_author']['author']->user_nicename );

		$stories['large_story']['c_tagline']['c_tagline_text']   = '';
		$stories['large_story']['c_tagline']['c_tagline_markup'] = "By {$author_data['byline']}";

		$image_id = isset( $review->image_id ) ? $review->image_id : get_post_thumbnail_id( $review->ID );

		$stories['large_story']['c_lazy_image']['c_lazy_image_link_url']           = isset( $review->url ) ? $review->url : get_the_permalink( $review );
		$stories['large_story']['c_lazy_image']['c_lazy_image_placeholder_url']    = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
		$stories['large_story']['c_lazy_image']['c_lazy_image_src_url']            = \wp_get_attachment_image_url( $image_id, 'landscape-xlarge' );
		$stories['large_story']['c_lazy_image']['c_lazy_image_screen_reader_text'] = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		$stories['large_story']['c_lazy_image']['c_lazy_image_alt_attr']           = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		$stories['large_story']['c_lazy_image']['c_lazy_image_srcset_attr']        = false;
		$stories['large_story']['c_lazy_image']['c_lazy_image_sizes_attr']         = false;

		continue;
	}

	// Otherwise, set to bottom row card.
	$row_card = $o_card_prototype;

	$row_card['c_title']['c_title_text'] = $review->post_title;
	$row_card['c_title']['c_title_url']  = isset( $review->url ) ? $review->url : get_the_permalink( $review );

	$row_card['c_dek']['c_dek_text'] = isset( $review->custom_excerpt ) ? $review->custom_excerpt : wp_strip_all_tags( \PMC\Core\Inc\Helper::get_the_excerpt( $review->ID ) );

	$image_id = isset( $review->image_id ) ? $review->image_id : get_post_thumbnail_id( $review->ID );

	$row_card['c_lazy_image']['c_lazy_image_link_url']           = isset( $review->url ) ? $review->url : get_the_permalink( $review );
	$row_card['c_lazy_image']['c_lazy_image_placeholder_url']    = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
	$row_card['c_lazy_image']['c_lazy_image_src_url']            = \wp_get_attachment_image_url( $image_id, 'landscape-xlarge' );
	$row_card['c_lazy_image']['c_lazy_image_screen_reader_text'] = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$row_card['c_lazy_image']['c_lazy_image_alt_attr']           = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	$row_card['c_lazy_image']['c_lazy_image_srcset_attr']        = false;
	$row_card['c_lazy_image']['c_lazy_image_sizes_attr']         = false;

	array_push( $stories_row_items, $row_card );
}

$stories['stories_row_items'] = $stories_row_items;
?>
<div class="u-padding-b-125 u-margin-b-2@tablet lrv-u-border-b-1 u-border-color-brand-secondary-40">
	<?php
	\PMC::render_template(
		sprintf( '%s/template-parts/patterns/modules/docs-stories-row.php', untrailingslashit( CHILD_THEME_PATH ) ),
		$stories,
		true
	);
	?>
</div>
<?php
