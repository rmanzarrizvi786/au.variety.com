<?php
/**
 * Trait to add functionality to any class to allow for automatic
 * creation of terms for one or multiple taxonomies if those
 * terms don't already exist.
 *
 * This is to be used when adding any Carousel Modules etc. via code.
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2020-02-18
 */

namespace PMC\Global_Functions\Traits\Terms;

trait Creator {

	/**
	 * Method to add taxonomy terms if they don't already exist
	 *
	 * Example of array to be passed to this method
	 *
	 * $terms_to_create = [
	 *     'taxononomy_slug_1' => [
	 *         [
	 *             'term' => 'Term 1',
	 *         ],
	 *         [
	 *             'term' => 'Term 2',
	 *             'args' => [
	 *                 'parent' => 1234,
	 *                 'slug'   => 'child-term-2',
	 *             ],
	 *         ],
	 *     ],
	 *
	 *     'taxononomy_slug_2' => [
	 *         [
	 *             'term' => 'Term 3',
	 *         ],
	 *     ],
	 * ];
	 *
	 * Individual term arrays can have 'term' & 'args' keys as specified for wp_insert_term()
	 * @see https://developer.wordpress.org/reference/functions/wp_insert_term/
	 *
	 * @param array $terms
	 *
	 * @return bool
	 */
	public function maybe_add_terms( array $terms = [] ) : bool {

		if ( empty( $terms ) ) {
			return false;
		}

		$counter     = 0;
		$term_exists = ( function_exists( 'wpcom_vip_term_exists' ) ) ? 'wpcom_vip_term_exists' : 'term_exists';

		foreach ( $terms as $taxonomy => $taxonomy_terms ) {

			foreach ( $taxonomy_terms as $taxonomy_term ) {

				$does_term_exist = $term_exists( $taxonomy_term['term'], $taxonomy );

				if ( ! empty( $does_term_exist ) ) {
					continue;
				}

				$args = ( empty( $taxonomy_term['args'] ) ) ? [] : $taxonomy_term['args'];

				wp_insert_term(
					$taxonomy_term['term'],
					$taxonomy,
					$args
				);

				$counter++;

				unset( $args, $does_term_exist );

			}

		}

		return (bool) ( 0 < $counter );

	}

}    //end trait

//EOF
