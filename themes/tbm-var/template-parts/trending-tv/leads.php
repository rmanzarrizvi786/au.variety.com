<?php
/**
 * Leads Template.
 * Top Stories for Trending TV
 *
 * @package pmc-variety-2020
 */

use PMC\Larva;

$leads_data = \Variety\Inc\Carousels::get_carousel_posts(
	'vy-trending-tv-leads',
	5,
);

if ( empty( $leads_data ) || count( $leads_data ) < 5 ) {
	return;
}
// Get default docs stories row data.
$leads = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/docs-stories-row.prototype' );

$leads['c_span']                = false;
$leads['large_story']['c_span'] = false;

$o_card_prototype  = $leads['stories_row_items'][0];
$stories_row_items = [];

foreach ( $leads_data as $key => $lead ) {
	$o_card                            = $o_card_prototype;
	$o_card['c_title']['c_title_text'] = $lead->post_title;
	$o_card['c_title']['c_title_url']  = isset( $lead->url ) ? $lead->url : get_the_permalink( $lead );
	$o_card['c_dek']['c_dek_text']     = isset( $lead->custom_excerpt ) ? $lead->custom_excerpt : wp_strip_all_tags( \PMC\Core\Inc\Helper::get_the_excerpt( $lead->ID ) );

	$image_id = isset( $lead->image_id ) ? $lead->image_id : get_post_thumbnail_id( $lead->ID );
	Larva\add_controller_data(
		Larva\Controllers\Components\C_Lazy_Image::class,
		[
			'image_id'   => $image_id,
			'image_size' => 'landscape-xlarge',
			'post_id'    => $lead->ID,
		],
		$o_card['c_lazy_image']
	);

	// If first story, set to large top story.
	if ( array_key_first( $leads_data ) === $key ) {

		$author_data                              = PMC\Core\Inc\Author::get_instance()->authors_data( $lead->ID );
		$o_card['c_tagline']['c_tagline_classes'] = 'a-font-secondary-bold lrv-u-font-size-14 u-color-black-pearl u-color-brand-secondary-50:hover lrv-u-margin-t-1 lrv-u-margin-b-00';
		/* translators: %s is the post author */
		$o_card['c_tagline']['c_tagline_markup'] = sprintf( __( 'By %s', 'pmc-variety' ), $author_data['byline'] );

		$o_card['c_lazy_image']['c_lazy_image_srcset_attr'] = '';
		$o_card['c_lazy_image']['c_lazy_image_classes']     = 'u-width-60p@tablet lrv-u-flex-shrink-0';

		$o_card['c_title']['c_title_classes']      = 'a-font-primary-regular a-font-primary-regular-2xl lrv-u-font-size-36@tablet lrv-u-line-height-small u-line-height-1@mobile-max lrv-u-margin-a-00';
		$o_card['c_title']['c_title_link_classes'] = 'lrv-a-unstyle-link u-color-brand-secondary-50:hover';
		unset( $o_card['o_card_classes'] );
		unset( $o_card['o_card_content_classes'] );

		$leads['large_story'] = array_merge( $leads['large_story'], $o_card );
	} else {
		$stories_row_items[] = $o_card;
	}
}

$leads['stories_row_items'] = $stories_row_items;

\PMC::render_template(
	sprintf( '%s/template-parts/patterns/modules/docs-stories-row.php', untrailingslashit( CHILD_THEME_PATH ) ),
	$leads,
	true
);
