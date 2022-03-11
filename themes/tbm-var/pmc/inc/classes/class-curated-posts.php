<?php
namespace PMC\Core\Inc;

/**
 * Curated and backfilled posts.
 *
 * This class will take a total number of posts and an array of post sources,
 * and it will compile an array of posts for you. A good use case for this class
 * is when you have a meta field for manual curation and want to backfill with
 * reverse chron. This class will handle all the dirty work and ensure that you
 * end with the right number of posts and don't end up with any duplicates.
 *
 * To use this class:
 *
 * 1. Create your object with the total number of posts and an array of sources.
 *    A source can be a callable, WP_Query object, array of posts or post IDs,
 *    or array of WP_Query args. Be conscious of performance here -- if you pass
 *    10 WP_Query objects which have already queried for posts, and the first
 *    produced enough posts to fill the total, the other 9 queried the DB
 *    unnecessarily. Therefore, callables and WP_Query args are preferred.
 *
 *     $curated = new \PMC_Core\Curated_Posts( 10, [ my_post_ids(), 'my_callable_to_backfill_posts', [ 'post_type' => 'post' ] ];
 * 2. Call the `get_posts()` method to get the array of curated posts.
 *
 *     $posts = $curated->get_posts();
 *
 *
 * @copyright 2016 Alley Interactive
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 *
 * The following code is a derivative work of code from the Alley Interactive
 * library "The Curator", which is licensed GPLv2. This code therefore
 * is also licensed under the terms of the GNU Public License, verison 2.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2 or greater,
 * as published by the Free Software Foundation.
 *
 * You may NOT assume that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * The license for this software can likely be found here:
 * http://www.gnu.org/licenses/gpl-2.0.html
 */
class Curated_Posts {

	/**
	 * Array of WP_Posts that this class compiles.
	 *
	 * @var array
	 */
	protected $_posts = [];

	/**
	 * Total number of posts this class aims to fill.
	 *
	 * @var int
	 */
	protected $_total;

	/**
	 * The sources to use to populate Curated_Posts::$posts.
	 *
	 * @var array
	 */
	protected $_sources = [];

	/**
	 * Build the object.
	 *
	 * @param int $total The total number of posts we want.
	 * @param array $sources The sources for posts. Array elements can be a
	 *                       callable, a WP_Query object, an array of posts, or
	 *                       an array of WP_Query args.
	 */
	public function __construct( $total, $sources = [] ) {
		$this->_total = absint( $total );

		if ( ! is_array( $sources ) ) {
			return;
		}

		foreach ( $sources as $source ) {
			$type = $this->_determine_source( $source );
			if ( $type ) {
				$this->_sources[] = [
					'type'   => $type,
					'source' => $source,
				];
			}
		}
	}

	/**
	 * Determine the source of posts passed to the construct.
	 *
	 * @param  mixed $source Callable, WP_Query, array of WP_Posts, or array of
	 *                       WP_Query args.
	 * @return string One of 'callable', 'query', 'posts', 'args', or an empty
	 *                string on failure.
	 */
	protected function _determine_source( $source ) {
		if ( empty( $source ) ) {
			return '';
		}

		if ( is_callable( $source ) ) {
			return 'callable';
		}

		if ( $source instanceof \WP_Query ) {
			return 'query';
		}

		if ( is_array( $source ) ) {
			if ( isset( $source[0] ) && ( $source[0] instanceof \WP_Post || is_int( $source[0] ) ) ) {
				return 'posts';
			} else {
				return 'args';
			}
		}

		return '';
	}

	/**
	 * Get the posts from a given source.
	 *
	 * @param  array $args Must have keys 'type' and 'source'.
	 *
	 * @return array|WP_Post
	 */
	protected function _get_posts_from_source( $args ) {
		$this->_posts = ( ! empty( $this->_posts ) && is_array( $this->_posts ) ) ? $this->_posts : [];

		$type = $args['type'];
		$source = $args['source'];

		$count = $this->_total - count( $this->_posts );

		// Ensure that we need posts
		if ( $count < 1 ) {
			return [];
		}

		$exclude = wp_list_pluck( $this->_posts, 'ID' );

		switch ( $type ) {
			case 'callable' :
				return call_user_func( $source, $count, $exclude );

			case 'query' :
				$source = $source->posts;
				// No break or return here; continues in 'posts'...
			case 'posts' :
				$posts = [];
				foreach ( $source as $post ) {
					// Ensure we have an object for this
					$post = get_post( $post );
					if ( is_a( $post, 'WP_Post' ) && ! in_array( $post->ID, $exclude, true ) ) {
						$posts[] = $post;
					}
					if ( 0 === --$count ) {
						break;
					}
				}

				return $posts;

			case 'args' :
				$buffer_for_exclusions = 3;
				$post__not_in = ( ! empty( $source['post__not_in'] ) ) ? array_merge( (array) $source['post__not_in'], $exclude ) : $exclude;

				unset( $source['post__not_in'] );
				$length         = count( $post__not_in );
				$posts_per_page = $length + $count + $buffer_for_exclusions;

				$source = array_merge( $source, [
					'posts_per_page' => $posts_per_page,
				] );

				$posts     = $this->_query( $source );
				$src_posts = [];

				foreach ( $posts as $cur_posts ) {

					if ( in_array( $cur_posts->ID, (array) $post__not_in, true ) ) {
						continue;
					}

					// skip over post-options: exclude-from-homepage and exclude-from-section-fronts
					if ( is_home() || is_front_page() ) {
						if ( has_term( 'exclude-from-homepage', '_post-options', $cur_posts->ID ) ) {
							continue;
						}
					}

					if ( is_archive() ) {
						if ( has_term( 'exclude-from-section-fronts', '_post-options', $cur_posts->ID ) ) {
							continue;
						}
					}

					$src_posts[] = $cur_posts;
					if ( count( $src_posts ) >= $count ) {
						break;
					}
				}

				return $src_posts;
		}

		return [];
	}

	/**
	 * Gets posts, curated first and backfilled thereafter.
	 *
	 * @return array|WP_Post
	 */
	public function get_posts() {
		$count = 0;
		$sources = $this->_sources;

		while ( $count < $this->_total && ! empty( $sources ) ) {
			$source = array_shift( $sources );
			$this->_posts = array_merge( $this->_posts, $this->_get_posts_from_source( $source ) );
		}

		// Ensure absolutely that we're returning a max of `$this->_total` posts
		return array_slice( $this->_posts, 0, $this->_total );
	}

	/**
	 * A wrapper for get_posts() which sets our needed defaults.
	 *
	 * @param  array $args {@see WP_Query}.
	 * @return array WP_Post objects.
	 */
	protected function _query( $args ) {
		$args = wp_parse_args( $args, [
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'suppress_filters' => false,
		] );

		if ( isset( $args['post__in'] ) && ! isset( $args['orderby'] ) ) {
			$args['orderby'] = 'post__in';
			$args['order']   = 'asc';
		}

		return get_posts( $args );
	}
}
