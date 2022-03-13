<?php
namespace PMC\Export;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Helper class, all helper methods should be added here.
 */
class Helper {

	use Singleton;

	/**
	 * This function accepts a string and returns its word count after stripping
	 * off HTML and WordPress shortcodes from it.
	 */
	public static function get_word_count( $content ) {

		if ( empty( $content ) || ! is_string( $content ) ) {
			return 0;
		}

		$content = wp_strip_all_tags( strip_shortcodes( $content ) ); //scrub content

		/*
		 * doing our own counting instead of str_word_count() because it counts
		 * a word like fri3nd as 2 words -> fri & nd
		 */
		$arr_content = array_filter( array_map( 'trim', (array) explode( ' ', $content ) ) ); //remove items with just spaces

		return count( $arr_content );
	}

	/**
	 * Helper function to get the word count of a post.
	 *
	 * @param int|WP_Post $post WP Post object or Post ID.
	 *
	 * @return int|bool Number of words or false if something went wrong.
	 */
	public static function get_post_word_count( $post ) {

		$post = get_post( $post );

		if ( ! $post ) {
			return false;
		}

		$word_count = static::get_word_count( $post->post_content );

		return $word_count;
	}

	/**
	 * Helper function to count number of images in a post.
	 *
	 * @param int|WP_Post $post WordPress Post object or Post ID.
	 *
	 * @todo Count number of images in Lists if Stake Holders asks for it.
	 *
	 * @return int|bool Returns number of images or false if something went wrong.
	 */
	public static function get_image_count( $post ) {

		$post = get_post( $post );

		if ( ! $post ) {
			return false;
		}

		$image_count = ( ! empty( get_post_thumbnail_id( $post ) ) ) ? 1 : 0;

		// If the post is gallery then count the gallery images.
		if ( 'pmc-gallery' === $post->post_type ) {

			$gallery_images = get_post_meta( $post->ID, 'pmc-gallery', true );

			if ( ! empty( $gallery_images ) && is_array( $gallery_images ) ) {
				$image_count += count( $gallery_images );
			}
		}

		// Count number of images in post content.
		$post_content = do_shortcode( $post->post_content );

		if ( ! empty( $post_content ) ) {
			$image_count += substr_count( $post_content, '<img' );
		}

		return $image_count;

	}

	/**
	 * Helper function to get the terms along with primary term of a given taxonomy of a post.
	 *
	 * @param int|WP_Post $post WP Post object or Post ID.
	 *
	 * @return array Returns an array containing term names or empty array if something went wrong.
	 */
	public static function get_post_taxonomy_categorization( $post, $taxonomy = 'category' ) {

		$post = get_post( $post );
		if ( ! $post ) {
			return [];
		}

		if ( empty( $taxonomy ) ) {
			return [];
		}

		$primary_term_id = 0;
		$term_names      = [];

		// Get the terms.
		$terms = get_the_terms( $post->ID, $taxonomy );

		if ( ! empty( $terms ) && is_array( $terms ) ) {

			// We want to identify the primary taxonomy
			if ( class_exists( 'PMC_Primary_Taxonomy' ) ) {
				$primary_term = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $post, $taxonomy );
				if ( ! is_wp_error( $primary_term ) && is_object( $primary_term ) && ! empty( $primary_term->term_id ) ) {
					$primary_term_id = $primary_term->term_id;
				}
			}

			foreach ( $terms as $term ) {
				if ( $term->term_id === $primary_term_id ) {
					$term_names[] = $term->name . ' ( Primary )';
				} else {
					$term_names[] = $term->name;
				}
			}

		}

		return $term_names;
	}
}
