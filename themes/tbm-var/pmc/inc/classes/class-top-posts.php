<?php
namespace PMC\Core\Inc;

/**
 * @package PMC\Core\Inc
 */
class Top_Posts {

	protected static $limit;
	protected static $cache_duration;
	protected static $days;
	protected static $period;
	protected static $pool_size = 100;

	use \PMC\Global_Functions\Traits\Singleton;

	/**
	 * Selecting random posts via wp_query is very slow, so instead we're going
	 * to grab the latest 100 posts and randomize them.
	 *
	 * @param int $period Number of days in the past to go back.
	 *
	 * @return array
	 */
	protected static function get_random_posts( $period ) {

		$posts = [ ];

		$query = new \WP_Query( [
			'post_type'           => apply_filters( 'pmc_core_top_posts_post_type', [ 'post' ] ),
			'posts_per_page'      => self::$pool_size,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'date_query'          => [
				[
					'after' => '-' . $period . ' days',
				],
			],
			'meta_query'          => [
				[
					'key'     => '_thumbnail_id',
					'value'   => 0,
					'compare' => '>',
				],
			],
		] );

		if ( $query->have_posts() ) {

			while ( $query->have_posts() ) {
				$query->the_post();

				$posts[] = [
					'post_id'        => get_the_ID(),
					'post_title'     => get_the_title(),
					'post_permalink' => get_the_permalink(),
					'views'          => ( $query->post_count * 1000 + rand( 0, 999 ) ),
				];

			}

			shuffle( $posts );
		}

		return $posts;
	}

	/**
	 * If on VIP, get most popular posts and filter by date.
	 * If not on VIP, return random posts.
	 *
	 * @param int $limit  Number of posts required.
	 * @param int $days   Only accept posts with a publication date within this
	 *                    many days ago.
	 * @param int $period Number of days to cover when calculating popularity.
	 * @param int $cache_duration
	 *
	 * @return array An array of post IDs and share counts.
	 */
	public static function get_posts(
		$limit = 5,
		$days = 365,
		$period = 30,
		$type = 'most_viewed',
		$cache_duration = 3600
	) {

		$timestamp = strtotime( '-' . $days . ' days', time() );
		$page_type = ( is_home() || is_front_page() ) ? 'home' : 'ros';
		$cache_key = 'pmc-most-popular-' . md5( $limit . $days . $period . $type . $cache_duration . $page_type );

		$pmc_cache = new \PMC_Cache( $cache_key, 'pmc_widget' );

		// To prevent cache stampeding, randomizing a time range for the caching period of get_posts.
		$cache_duration = wp_rand( $cache_duration, $cache_duration + 180 );
		$filtered_posts = $pmc_cache->expires_in( $cache_duration )
									->updates_with( [ self::class, 'uncached_get_posts' ], [ $limit, $period, $timestamp, $type ] )
									->get();

		if ( ! is_array( $filtered_posts ) ) {
			$filtered_posts = []; // PMC_Cache returns false if no posts found.
		}

		return $filtered_posts;
	}

	/**
	 * If on VIP, get most popular posts and filter by date.
	 * If not on VIP, return random posts.
	 *
	 * @param int $limit  Number of posts required.
	 * @param int $period Number of days to cover when calculating popularity.
	 *
	 * @return array An array of post IDs and share counts.
	 */
	public static function _get_most_viewed( $limit = 5, $period = 30 ) {

		// Outside of VIP, wpcom_vip_top_posts_array returns pudding-based results.
		// We want IDs of actual random posts instead.
		if ( ! \PMC::is_production() ) {
			$top_posts_array = self::get_random_posts( $period );
		} else {
			$top_posts_array = wpcom_vip_top_posts_array( $period, self::$pool_size );
		}

		return $top_posts_array;
	}

	/**
	 * Get most popular posts by comment_count
	 *
	 * @param int $limit  Number of posts required.
	 * @param int $period Number of days to cover when calculating popularity.
	 *
	 * @return array An array of post IDs and comment counts.
	 */
	private static function _get_most_commented( $limit = 5, $period = 30 ) {
		$args = [
			'suppress_filters' => false,
			'order'            => 'DESC',
			'orderby'          => 'comment_count',
			'numberposts'      => $limit,
			'post_status '     => 'publish',
		];

		$args['date_query'] = [
			[
				'after'     => date( 'Y-m-d H:i:s', strtotime( '-' . $period . ' days', time() ) ),
				'inclusive' => true,
			],
		];

		$posts = [];

		$query = new \WP_Query( $args );

		if ( $query->have_posts() ) {

			while ( $query->have_posts() ) {
				$query->the_post();

				$posts[] = [
					'post_id'        => get_the_ID(),
					'post_title'     => get_the_title(),
					'post_permalink' => get_the_permalink(),
					'views'          => $query->comment_count,
				];
				wp_reset_postdata();
			}

		}

		return $posts;
	}

	/**
	 * Get un-cached get posts.
	 *
	 * @param $limit
	 * @param $period
	 * @param $timestamp
	 * @param $type
	 *
	 * @return array
	 */
	public static function uncached_get_posts( $limit, $period, $timestamp, $type ) {

		if ( 'most_commented' === $type ) {
			$top_posts_array = static::_get_most_commented( $limit * 2, $period );
		} else {
			$top_posts_array = static::_get_most_viewed( $limit * 2, $period );
		}

		$filtered_posts = [];

		$top_posts_array = apply_filters( 'pmc_core_top_posts', $top_posts_array );

		if ( is_array( $top_posts_array ) ) {

			// Lets filter out any invalid posts
			$top_posts_array = array_values(
				array_filter( (array) $top_posts_array, [ get_called_class(), 'is_valid_post' ] )
			);

			foreach ( $top_posts_array as $top_post ) {

				$post_object = get_post( $top_post['post_id'] );

				// Just in case - does the post exist?
				if ( empty( $post_object ) ) {
					continue;
				}

				$top_posts_post_type = apply_filters( 'pmc_core_top_posts_post_type', [ 'post' ] );

				// Is the post of the right content type?
				if ( ! in_array( $post_object->post_type, (array) $top_posts_post_type, true ) ) {
					continue;
				}

				// Is this post recent enough?
				if ( get_post_time( 'U', false, $post_object ) < $timestamp ) {
					continue;
				}

				// add more data that can be used for sorting
				$top_post['post_date_gmt'] = $post_object->post_date_gmt;

				$filtered_posts[] = $top_post;

				// Break out when we have enough posts
				if ( count( $filtered_posts ) === $limit ) {
					break;
				}
			}
		}

		if ( empty( $filtered_posts )
			|| ! is_array( $filtered_posts )
			|| is_wp_error( $filtered_posts )
		) {
			return [];
		}

		$filtered_posts = array_slice( $filtered_posts, 0, $limit, true );

		return $filtered_posts;
	}

	/**
	 * Utility method to filter out invalid posts.
	 * This method should be run over an array of post arrays via array_filter().
	 *
	 * @param array $post
	 *
	 * @return bool
	 */
	public static function is_valid_post( array $post ) : bool {

		if ( ! isset( $post['post_id'] ) || 1 > intval( $post['post_id'] ) ) {

			// This post is invalid
			return false;

		}

		return true;

	}

}

//EOF
