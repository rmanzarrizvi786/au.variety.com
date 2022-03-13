<?php
/**
 * Implement curated posts to replace popular posts feed.
 * @see PPT-4362
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since 2014-07-18
 * @version 2015-03-11 Amit Gupta - added listener on 'pmc_custom_feed_posts' for reuters multimedia feed to replace posts with carousel items
 * @version 2015-03-25 Hau - Move from variety theme into pmc-plugins
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Custom_Feed_Curated_Posts {

	use Singleton;

	const MAX_AGE_IN_DAY = 30;
	const MAX_COUNT      = 5;

	/**
	 * Return curated posts for reuters multimedia feed with posts in featured
	 * and second stage carousel
	 *
	 * @ticket PPT-4362
	 * @since 2015-03-11 Amit Gupta
	 * @version 2015-03-18 Amit Gupta - added normal feed posts which are appended to curated posts
	 * @version 2015-03-25 Hau - refactor & simplify code
	 */
	public function get_posts( $feed ) {

		$number_of_post = intval( PMC_Custom_Feed::get_instance()->get_feed_config( 'count' ) );

		$carousel_options = array(
			'add_filler' => false,
		);

		/*
		 * So that we fetch all posts from carousels
		 * as some might get excluded if they're flagged as
		 * inappropriate for syndication
		 */
		$posts_to_fetch = 50;
		if ( empty( $number_of_post ) ) {
			$number_of_post = $posts_to_fetch;
		}

		$post_ids = array();

		/*
		 * Grab posts from Featured Carousel without any filler posts
		 */
		$featured_carousel = pmc_render_carousel( PMC_Carousel::modules_taxonomy_name, 'featured-carousel', $posts_to_fetch, '', $carousel_options );

		if ( ! empty( $featured_carousel ) && is_array( $featured_carousel ) && count( $featured_carousel ) > 0 ) {
			$post_ids = array_merge( $post_ids, array_values( array_filter( wp_list_pluck( $featured_carousel, 'ID' ) ) ) );
		}

		unset( $featured_carousel );

		/*
		 * Grab posts from Second Stage Carousel without any filler posts
		 */
		$second_stage_carousel = pmc_render_carousel( PMC_Carousel::modules_taxonomy_name, 'second-stage', $posts_to_fetch, '', $carousel_options );

		if ( ! empty( $second_stage_carousel ) && is_array( $second_stage_carousel ) && count( $second_stage_carousel ) > 0 ) {
			$post_ids = array_merge( $post_ids, array_values( array_filter( wp_list_pluck( $second_stage_carousel, 'ID' ) ) ) );
		}
		unset ( $second_stage_carousel );

		if ( empty( $post_ids ) || count( $post_ids ) < 1 ) {
			//nothing in carousels, bail out
			return $posts;
		}

		$post_ids = array_filter( $post_ids, function( $id ) {
			return ! PMC\Custom_Feed\PMC_Option_Inappropriate_For_Syndication::get_instance()->is_exclude( $id );
		} );

		/*
		 * Time to grab post objects of the posts we got from both carousels
		 */
		$posts = get_posts( array(
			'post_type'           => 'post',
			'post__in'            => $post_ids,
			'numberposts'         => self::MAX_COUNT,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'suppress_filters'    => false,
			'date_query'          => array(
				// restrict post to MAX_AGE_IN_DAY
				array(
					'column' => 'post_date_gmt',
					'after'  => sprintf( '%d days ago', self::MAX_AGE_IN_DAY ),
				),
			),
		) );

		// not enough post? then get additional post from regular feed
		if ( count( $posts ) < $number_of_post ) {
			$post_ids = wp_list_pluck( $posts, 'ID' );
			$args = array();

			// need to exclude the curated posts
			if ( ! empty( $post_ids ) ) {
				$args['post__not_in'] = $post_ids;
			}

			$regular_posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed, $args );

			if ( is_array( $regular_posts ) ) {
				foreach ( $regular_posts as $post ) {

					$posts[] = $post;

					// bail if we have enough posts
					if ( count( $posts ) >= $number_of_post ) {
						break;
					}
				} // foreach
			}

			unset( $post_ids, $regular_posts );
		} // if

		if ( !empty( $posts ) ) {
			$posts = array_slice( array_values( $posts ), 0, $number_of_post );
			$i = 0;
			// apply order to first 5 items
			while ( $i < count( $posts ) && $i < 5 ) {
				$posts[$i]->order = $i + 1;
				$i += 1;
			}
		}

		return $posts;
	} // get_posts

}	//end of class

//EOF
