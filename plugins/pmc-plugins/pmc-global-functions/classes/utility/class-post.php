<?php
/**
 * Utilities for working with post data regardless of active plugins.
 *
 * @package pmc-global-functions
 */

namespace PMC\Global_Functions\Utility;

use PMC_Primary_Taxonomy;
use WP_Term;

/**
 * Class Post.
 */
class Post {
	/**
	 * Retrieve a post's primary term in a given taxonomy.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @return WP_Term|null
	 */
	public static function get_primary_term(
		int $post_id,
		string $taxonomy = 'category'
	): ?WP_Term {
		$term = null;

		// Prefer explicitly-chosen primary term, when possible.
		if ( class_exists( PMC_Primary_Taxonomy::class, false ) ) {
			$primary_term = PMC_Primary_Taxonomy::get_instance()
				->get_primary_taxonomy(
					$post_id,
					$taxonomy
				);

			if ( $primary_term instanceof WP_Term ) {
				$term = $primary_term;
				unset( $primary_term );
			}
		}

		// Fall back to first term assigned to post from given taxonomy.
		if ( null === $term ) {
			$terms = get_the_terms( $post_id, $taxonomy );

			if ( is_array( $terms ) ) {
				// Cannot cover without unloading `PMC_Primary_Taxonomy`.
				$term = array_shift( $terms ); // @codeCoverageIgnore
			}
		}

		return $term;
	}

	/**
	 * Check if a given post ID can be accessed by the current user.
	 *
	 * Published posts are assumed to be accessible by all users, including
	 * anonymous ones. A post with any other status is only available to users
	 * with the capability to edit that post.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function is_accessible_by_current_user(
		int $post_id
	): bool {
		if ( 'publish' === get_post_status( $post_id ) ) {
			return true;
		}

		if ( ! is_user_logged_in() ) {
			return false;
		}

		$post_type        = get_post_type( $post_id );
		$post_type_object = get_post_type_object( $post_type );
		$edit_cap         = $post_type_object
			? $post_type_object->cap->edit_post
			: 'edit_post';

		return current_user_can( $edit_cap, $post_id );
	}
}
