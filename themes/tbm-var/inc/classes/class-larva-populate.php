<?php
/**
 * Populates Larva Patterns with Post Data.
 *
 * @package pmc-variety
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Populate
 */
class Larva_Populate {

	use Singleton;

	/**
	 * Format the title data.
	 *
	 * Updates for moving to core:
	 * - remove variety_get_card_title / add to a filter
	 *
	 * @param array $post_id
	 * @param array $template A Larva module JSON object with the pattern as a direct key
	 *
	 * @return array
	 */
	public static function c_title( $post_id, $template ) {
		if ( ! empty( $template['c_title'] ) ) {
			$c_title                 = [];
			$c_title['c_title_text'] = \PMC::truncate( variety_get_card_title( $post_id ), 200 );
			$c_title['c_title_url']  = get_permalink( $post_id );

			$c_title = array_merge( $template['c_title'], $c_title );

			return $c_title;
		}
	}

	/**
	 * Format the lazy image.
	 *
	 * @param \WP_Post $current_post
	 * @param array    $template A Larva module JSON object with the pattern as a direct key
	 * @param array    $options  array of options.
	 *                           Supported options:
	 *                           image_size => string; WordPress image size
	 *
	 * @return array
	 */
	public static function c_lazy_image( $current_post, $template, $options = [] ) {

		$post_id      = $current_post->ID;
		$c_lazy_image = [];

		// Add default image size if one is not provided
		if ( empty( $options ) ) {
			$options['image_size'] = 'landscape-large';
		}

		if ( ! empty( $template['c_lazy_image'] ) ) {
			// Carousel sets this up
			if ( ! empty( $current_post->image_id ) ) {
				$thumb_id = $current_post->image_id;
			} else {
				$thumb_id = get_post_thumbnail_id( $post_id );
			}

			$image_data = ( ! empty( $thumb_id ) ) ? \PMC\Core\Inc\Media::get_instance()->get_image_data(
				$thumb_id,
				$options['image_size']
			) : false;

			// These values are set even if no thumbnail
			$c_lazy_image['c_lazy_image_link_url']        = get_permalink( $post_id );
			$c_lazy_image['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();

			// If $image_data doesn't have all necessary data, it will either be false
			// or will have false values for some of the attributes, like src and image_caption
			if ( $image_data && $image_data['src'] ) {
				$c_lazy_image['c_lazy_image_alt_attr']    = $image_data['image_alt'];
				$c_lazy_image['c_lazy_image_srcset_attr'] = \wp_get_attachment_image_srcset( $thumb_id, $options['image_size'] );
				$c_lazy_image['c_lazy_image_sizes_attr']  = \wp_get_attachment_image_sizes( $thumb_id, $options['image_size'] );
				$c_lazy_image['c_lazy_image_src_url']     = $image_data['src'];
			} else {
				// Remove any demo content if there is no image data
				$c_lazy_image['c_lazy_image_src_url']     = false;
				$c_lazy_image['c_lazy_image_srcset_attr'] = false;
				$c_lazy_image['c_lazy_image_sizes_attr']  = false;
			}

			$c_lazy_image = array_merge( $template['c_lazy_image'], $c_lazy_image );
		}

		return $c_lazy_image;
	}

	/**
	 * Format the dek.
	 *
	 * @param array  $post_id
	 * @param array  $template    A Larva module JSON object with the pattern as a direct key
	 * @param object $post_object Optional parameter for WP_Post object to access custom excerpt
	 *
	 * @return array
	 */
	public static function c_dek( $post_id, $template, $post_object = [] ) {

		if ( ! empty( $template['c_dek'] ) ) {
			$c_dek = [];

			if ( ! empty( $post_object->custom_excerpt ) ) {
				$text = $post_object->custom_excerpt;
			} else {
				$text = \PMC\Core\Inc\Helper::get_the_excerpt( $post_id );
			}

			// We are not using c_dek_markup here.
			$c_dek['c_dek_markup'] = false;
			$c_dek['c_dek_text']   = \PMC::truncate( $text, 900, '', true );

			$c_dek = array_merge( $template['c_dek'], $c_dek );

			return $c_dek;
		}

	}

	/**
	 * Format the timestamp.
	 *
	 * @param array $post_id
	 * @param array $template A Larva module JSON object with the pattern as a direct key
	 *
	 * @return array
	 */
	public static function c_timestamp( $post_id, $template ) {

		if ( ! empty( $template['c_timestamp'] ) ) {
			$c_timestamp = [];

			$time_ago = variety_human_time_diff( $post_id );

			$c_timestamp['c_timestamp_text']          = $time_ago;
			$c_timestamp['c_timestamp_datetime_attr'] = date( 'Y-m-d', strtotime( $time_ago ) );

			$c_timestamp = array_merge( $template['c_timestamp'], $c_timestamp );

			return $c_timestamp;
		}
	}

	/**
	 * Format the vertical span.
	 *
	 *
	 * @param array $post_id
	 * @param array $template A Larva module JSON object with the pattern as a direct key
	 *
	 * @return array
	 */
	public static function c_span_vertical( $post_id, $template ) {

		if ( ! empty( $template['c_span'] ) ) {

			$vertical = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $post_id, 'vertical' );

			$c_span = [];

			if ( ! empty( $vertical ) && ! is_wp_error( $vertical ) ) {
				$c_span['c_span_text'] = $vertical->name;
				$c_span['c_span_url']  = get_term_link( $vertical );
			} else {
				$c_span['c_span_text'] = false;
				$c_span['c_span_url']  = false;
			}

			$c_span = array_merge( $template['c_span'], $c_span );

			return $c_span;
		}
	}

	/**
	 * Format the author link.
	 *
	 * @param array $post_id
	 * @param array $template A Larva module JSON object with the pattern as a direct key
	 *
	 * @return array
	 */
	public static function c_link_author( $post_id, $template ) {
		$c_link = [];

		if ( ! empty( $template['c_link'] ) ) {
			$author = \PMC\Core\Inc\Author::get_instance()->authors_data( $post_id );

			if ( ! empty( $author['byline'] ) ) {
				$c_link['c_link_text'] = wp_strip_all_tags( sprintf( 'By %1$s', $author['byline'] ) );

				if ( ! empty( $author['single_author'] ) ) {
					$c_link['c_link_url'] = get_author_posts_url(
						$author['single_author']['author']->ID,
						$author['single_author']['author']->user_nicename
					);
				}

				$c_link = array_merge( $template['c_link'], $c_link );

			}
		}

		return $c_link;
	}

}
