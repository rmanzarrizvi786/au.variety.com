<?php
/**
 * Displays linked galleries
 *
 * This class renders the Preview Gallery
 *
 * @package PMC Gallery Plugin
 * @since 1/1/2013 Vicky Biswas
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Gallery_Link_Content_View {

	use Singleton;

	/**
	 * Outputs HTML for linked galleries
	 * @param array $args
	 * @return string|null $output
	 */
	public static function get_html( $args = array() ) {
		if ( ! is_singular() ) {
			return;
		}

		$args = wp_parse_args( (array) $args, array(
			'image_size' => 'pmc-gallery-image',
			'thumbnail_image_size' => 'pmc-gallery-thumb',
			'thumbnail_layout' => 'filmstrip',
			'number_to_show' => 3,
			'thumbnail_structure' => 'thumb',
			'thumbnail_previous_link_text' => PMC_Gallery_Defaults::previous_link_html,
			'thumbnail_next_link_text' => PMC_Gallery_Defaults::next_link_html,
		) );

		$linked_gallery = get_post_meta( get_the_id(), 'pmc-gallery-linked-gallery', true);

		if ( ! $linked_gallery ) {
			return;
		}

		$linked_gallery = json_decode( $linked_gallery );
		if ( ! isset($linked_gallery->id) ) {
			return;
		}

		$gallery = PMC_Gallery_View::get_instance()->load_gallery( $linked_gallery->id, true );

		$gallery_link = $gallery->get_the_permalink( true );

		// Add ref param to the gallery link to trigger "Return to article" link
		$ref_path = parse_url( get_permalink(), PHP_URL_PATH );
		if ( $ref_path ) {
			// Not using add_query_arg() here because we're adding a param to a hash, not querystring
			$gallery_link .= '&ref=' . $ref_path . 'pos=';
		}

		$output = '<div class="gallery-preview">';

		$output .= $gallery->get_the_image( $args['image_size'] );

		$output .= '<a class="view-gallery" href="' . esc_url( $gallery_link ) . '">View Gallery &rsaquo;<br /> ' . $gallery->get_the_count('total') . ' Photos</a>';

		$output .= $gallery->get_the_thumbs( $args );

		$output .= '</div>';

		return $output;
	}
}

//EOF
