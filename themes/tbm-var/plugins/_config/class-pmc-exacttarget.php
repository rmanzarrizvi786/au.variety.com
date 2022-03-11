<?php

/**
 * Configuration file for pmc-exacttarget plugin.
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since   2017-09-18
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC_Cache;

class PMC_Exacttarget {

	use Singleton;

	/**
	 * Construct Method.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * To setup action/filter.
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		/**
		 * Filters.
		 */
		add_filter( 'sailthru_process_recurring_post', array( $this, 'sailthru_process_recurring_post' ), 10, 2 );
	}

	/**
	 * To add editorial term and primary editorial term in post data.
	 *
	 * @param  array    $post_data Post data use in feed.
	 * @param  \WP_Post $post Original post.
	 *
	 * @return array Modified post data use in feed.
	 */
	public function sailthru_process_recurring_post( $post_data, $post ) {

		if ( empty( $post ) || ! is_a( $post, 'WP_Post' ) ) {
			return $post_data;
		}

		// Set all editorial.
		$all_editorial = get_the_terms( $post->ID, 'editorial' );
		$editorials = array();

		if ( ! empty( $all_editorial ) && ! is_wp_error( $all_editorial ) ) {

			foreach ( $all_editorial as $editorial ) {

				$link = get_term_link( $editorial, 'editorial' );

				if ( empty( $link ) || is_wp_error( $link ) ) {
					continue;
				}

				$editorials[] = array(
					'name' => $editorial->name,
					'link' => $link,
				);

			}
		}

		if ( ! empty( $editorials ) ) {
			$post_data['editorials'] = $editorials;
		}

		// Get Primary editorial term.
		$cache_key = sprintf( 'variety_editorial_primary_%d', absint( $post->ID ) );
		$cache_group = 'variety_editorial';

		$cache = new PMC_Cache( $cache_key, $cache_group );
		$primary_editorial = $cache->updates_with( array( $this, 'get_primary_editorial' ), array( $post ) )
								->get();

		if ( empty( $primary_editorial ) || ! is_a( $primary_editorial, 'WP_Term' ) ) {
			return $post_data;
		}

		$link = get_term_link( $primary_editorial );

		if ( empty( $link ) || is_wp_error( $link ) ) {
			return $post_data;
		}

		$post_data['primary_editorial'] = array(
			'name' => $primary_editorial->name,
			'link' => $link,
		);

		return $post_data;
	}

	/**
	 * To get primary editorial of post.
	 *
	 * @param  \WP_Post|int $post Either Post object or Post ID.
	 *
	 * @return \WP_Term|bool Return WP_Term object on success otherwise FALSE on failed.
	 */
	public function get_primary_editorial( $post ) {

		if ( empty( $post ) ) {
			return false;
		}

		if ( ! taxonomy_exists( 'editorial' ) ) {
			return false;
		}

		$post = get_post( $post );

		if ( empty( $post ) ) {
			return false;
		}

		$terms = get_the_terms( $post, 'editorial' );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return false;
		}

		$terms = array_values( $terms );

		$primary_editorial = get_post_meta( $post->ID, '_variety_primary_editorial', true );

		/**
		 * To get primary editorial term.
		 * Default will be value stored in '_variety_primary_editorial' meta of Post.
		 *
		 * @param int $primary_editorial Term ID of editorial taxonomy.
		 * @param int $Post_ID Post ID
		 *
		 * @version 2017-09-19 - Dhaval Parekh - CDWE-662 - Coppied from old theme.
		 */
		$primary_editorial = apply_filters( 'variety_primary_editorial', $primary_editorial, $post->ID );

		if ( ! empty( $primary_editorial ) ) {

			$primary_editorial = intval( $primary_editorial );

			foreach ( $terms as $term ) {
				if ( $term->term_id === $primary_editorial ) {
					$editorial = $term;
					break;
				}
			}

		}

		if ( empty( $editorial ) ) {
			$editorial = $terms[0];
		}

		return $editorial;
	}

}
