<?php
/**
 * Trait containing utility functions for posts
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2018-03-23
 */

namespace SNW\Traits\CEO_Press;

use \PMC_Cache;

trait SNW_Posts {

	/**
	 * Method to get cached version of posts based on post query parameters
	 *
	 * @param array $args
	 * @param int   $cache_expiry
	 *
	 * @return array
	 */
	public function get_cached_posts( array $args, $cache_expiry = 600 ) {

		$cache_expiry = intval( max( intval( $cache_expiry ), 600 ) ); // keep min 10 min cache

		$cache_key = sprintf( 'snwcp_%s_%d', md5( maybe_serialize( $args ) ), $cache_expiry );

		$cache = new PMC_Cache( $cache_key );

		return $cache->expires_in( $cache_expiry )
					->updates_with( [ $this, 'get_uncached_posts' ], [ $args ] )
					->get();

	}

	/**
	 * Method to get uncached version of posts based on post query parameters.
	 * This method should not be called directly and get_cached_posts() should be used.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_uncached_posts( array $args ) {

		if ( empty( $args ) ) {
			return [];
		}

		$default_args = array(
			'posts_per_page'   => 1,
			'suppress_filters' => false,
		);

		$args = wp_parse_args( $args, $default_args );

		$posts = get_posts( $args ); // @codingStandardsIgnoreLine

		if ( ! empty( $posts ) && ! is_wp_error( $posts ) ) {
			return $posts;
		}

		return [];

	}

}    // end trait


//EOF
