<?php
/**
 * Callable Functions
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

/**
 * return the main category of the post
 *
 * @since ?
 * @version 2017-09-27 Amit Gupta - copied from pmc-variety-2014/functions.php
 */
function variety_get_main_category( $post ) {

	if ( empty( $post ) ) {
		return null;
	}

	// by default, return the most popular category
	$categories = get_the_terms( $post, 'category' );

	if ( ! empty( $categories ) && ! is_wp_error( $categories ) && is_array( $categories ) && count( $categories ) > 0 ) {

		$categories_count = count( $categories );

		// if post assigned to category reviews, then return it
		if ( $categories_count > 1 && has_category( 'Reviews', $post ) ) {

			// search and return reviews category
			for ( $i = 0; $i < $categories_count; $i++ ) {

				if ( 'reviews' === $categories[ $i ]->slug || 'Reviews' === $categories[ $i ]->name ) {
					return $categories[ $i ];
				}

			}

		}

		// we need to simulate wp_get_object_terms( $post, 'category', array('orderby' => 'count', 'order' => 'DESC') );
		usort(
			$categories,
			function ( $a, $b ) {
				return ( intval( $a->count ) - intval( $b->count ) );
			}
		);

		return $categories[0];

	}

	return null;

}

/**
 * To filter Query args from youtube URL.
 *
 * @before https://www.youtube.com/watch?v=vJjw5kMr9X8&feature=youtu.be
 * @after  https://www.youtube.com/watch?v=vJjw5kMr9X8
 *
 * @param string $url url to filter query args.
 *
 * @return string
 */
function variety_filter_youtube_url( $url ) {

	if ( ! empty( $url ) ) {
		$url_parts = wp_parse_url( $url );
		$host      = ( ! empty( $url_parts['host'] ) ) ? $url_parts['host'] : '';

		if ( 'www.youtube.com' === $host
			|| 'youtube.com' === $host
			|| 'www.youtu.be' === $host
			|| 'youtu.be' === $host ) {

			if ( ! empty( $url_parts['query'] ) ) {
				parse_str( $url_parts['query'], $query_params );
			}

			$url = sprintf( '%1$s://%2$s%3$s', $url_parts['scheme'], $host, $url_parts['path'] );

			if ( ! empty( $query_params['v'] ) && false === strpos( $host, 'youtu.be' ) ) {
				$url = add_query_arg( array( 'v' => $query_params['v'] ), $url );
			}
		}
	}

	return $url;
}

//EOF
