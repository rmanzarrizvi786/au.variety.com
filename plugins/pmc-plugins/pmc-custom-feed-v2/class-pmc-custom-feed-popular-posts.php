<?php
/**
 * @codeCoverageIgnore Cannot test as wpcom_vip_top_posts_array only works on production
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Custom_Feed_Popular_Posts {

	use Singleton;

	const MAX_AGE_IN_DAY = 30;
	const MAX_COUNT = 5;
	private $_post_count = 0;

	public function filter_post_count( $args, $config ) {

		// do we have popular posts?
		if ( !empty( $this->_post_count ) ) {
			if ( $this->_post_count < $args['numberposts'] ) {
				// fetch extra post to cover duplicates
				$args['numberposts'] += $this->_post_count;
			} else {
				// popular posts already has enough number of posts
				$args['numberposts'] = 0;
			}
		}

		return $args;
	}

	private function _get_post_ids() {

		// feed is requesting a specific post
		if ( is_numeric( get_query_var( "fpid" ) ) ) {
			return array( get_query_var( "fpid" ) );
		}

		$post_ids = array();

		$post_types = PMC_Custom_Feed_Helper::validate_post_types( PMC_Custom_Feed::get_instance()->get_feed_config( 'post_type' ) );

		if ( empty( $post_types ) ) {
			$post_types = array( 'post' );
		}

		if ( true != WPCOM_IS_VIP_ENV ) {

			// Emulate VIP top posts array output
			$popular_posts = get_posts(
				array(
					'numberposts'      => self::MAX_COUNT,
					'orderby'          => 'post_date',
					'post_type'        => $post_types,
					'no_found_rows'    => true,
					'suppress_filters' => false,
				)
			);

			return wp_list_pluck( $popular_posts, 'ID' );

		}

		// Using WordPress.com Stats to get the top posts
		if ( function_exists( 'wpcom_vip_top_posts_array' ) ) {
			// grab top 100 most popular posts so we can do some filter against post date to last 30 days.
			$popular_posts = wpcom_vip_top_posts_array( 2, '100' );
			$count         = 0;

			foreach ( $popular_posts as $top_post ) {
				if ( ! in_array( get_post_type( $top_post['post_id'] ), $post_types ) ) {
					continue; //not a post, move to next iteration
				}
				// type is in $post_types, we'll take it
				$post_ids[] = $top_post['post_id'];
				$count ++;

				if ( $count >= self::MAX_COUNT ) {
					break; //got all posts that we need, bail the loop
				}
			}

		}

		return $post_ids;
	}

	public function get_posts( $feed ) {

		$number_of_post = intval( PMC_Custom_Feed::get_instance()->get_feed_config( 'count' ) );
		$popular_posts = array();

		$post_ids = $this->_get_post_ids();
		// use static variable to store ids for filter
		$this->_post_count = count( $post_ids );

		if ( $this->_post_count > 0 ) {
			$post_types = PMC_Custom_Feed_Helper::validate_post_types( PMC_Custom_Feed::get_instance()->get_feed_config( 'post_type' ) );

			if ( empty( $post_types ) ) {
				$post_types = array( 'post', 'pmc-gallery' );
			}

			$feed_posts = get_posts(
				array(
					'post_type'   => $post_types,
					'post__in'    => $post_ids,
					'numberposts' => $this->_post_count,
				)
			);

			$max_age_in_day = apply_filters('pmc_custom_feed_popular_post_last_n_days', self::MAX_AGE_IN_DAY );

			$post_ids = array();
			$order = 1;
			foreach ( $feed_posts as $post ) {

				// check to make sure post are within the last 30 days
				$timestamp = $post->post_date_gmt;
				if ( !is_numeric( $timestamp ) ) {
					$timestamp = strtotime( $timestamp );
				}

				$age = time() - $timestamp;
				if ( $age/DAY_IN_SECONDS > $max_age_in_day ) {
					continue;
				}

				// need to check older post that might be excluded but not in $excluded_posts list
				if ( PMC\Custom_Feed\PMC_Option_Inappropriate_For_Syndication::get_instance()->is_exclude( $post ) ) {
					continue;
				}

				$post->order = $order++;
				$popular_posts[] = $post;
				$post_ids[] = $post->ID;
			}

			if ( $number_of_post <= count( $popular_posts ) ) {
				unset( $post_ids );
				return $popular_posts;
			}
		}

		add_filter( 'pmc_custom_feed_posts_filter', array( $this, 'filter_post_count' ), 10, 2 );
		$feed_posts = PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed );
		remove_filter( 'pmc_custom_feed_posts_filter', array( $this, 'filter_post_count' ) );

		foreach ( $feed_posts as $post ) {
			if ( in_array( $post->ID, $post_ids ) ) {
				continue;
			}
			$popular_posts[] = $post;
		}

		$popular_posts = array_slice( $popular_posts, 0, $number_of_post );

		return $popular_posts;
	} // function

} // class

// EOF
