<?php

/**
 * PMC Related Articles
 * Posts are considered related should have similar post tags
 *
 * @author Amit Gupta
 * @since 2012-12-11
 * @version 2013-01-09 Amit Gupta
 * @version 2013-06-26 Amit Gupta
 * @version 2015-03-30 Hau Vong - refactor codes, @see PPT-4539
 * @version 2017-04-13 Hau Vong - Fix performance issue to avoid using post__not_in @see CDWE-310
 *
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Related_Articles {

	use Singleton;

	const CACHE_GROUP    = 'pmc-related-articles';
	const CACHE_DURATION = 1800;	// 30 minutes

	protected function __construct() {
	}

	/**
	 * Helper function to apply default value to query $args
	 * @param array $args
	 * @return array
	 */
	private function _apply_default( array $args ) {

		if ( ! empty( $args['last_n_days'] ) ) {
			$args['date_query'] = array(
					array(
						'inclusive' => true,
						'after'     => intval( $args['last_n_days'] ) .' days ago',
					),
				);
			unset( $args['last_n_days'] );
		}

		if ( ! empty( $args['max_articles'] ) ) {
			$args['posts_per_page'] = intval( $args['max_articles'] );
			unset( $args['max_articles'] );
		}

		if ( isset( $args['posts_per_page'] ) ) {
			// IMPORTANT: restrict number of posts to return to a safe number
			if ( $args['posts_per_page'] < 0 || $args['posts_per_page'] > 100 ) {
				$args['posts_per_page'] = 100;
			}
		}

		// default value that may be override by $args
		$default = array(
				'suppress_filters'    => false,
				'ignore_sticky_posts' => true, // we don't want any sitcky posts
				'no_found_rows'       => true,
				'post_status'         => 'publish',
				'post_type'           => array( 'post' ),
				'posts_per_page'      => 20,
				'orderby'             => 'post_date',
				'order'               => 'DESC',
			);

		// user cheezcap to control default es support
		$es_support = PMC_Cheezcap::get_instance()->get_option( 'pmc_plugin_es_support' );
		if ( 'yes' === $es_support ) {
			$default['es'] = true;
		}

		return wp_parse_args( $args, $default );
	}

	/**
	 * Helper function to map WP_Object into array of related post data
	 * @param array of object
	 * @return array of object
	 * @version 2015-08-12 Harshad Pandit - PPT-5216 - fixed issue - $image[3] was carrying previous post thumb id when current post does not have post thumb;
	 */
	private function _map_posts( array $posts ) {
		$results = array();
		if ( empty( $posts ) ) {
			return array();
		}

		foreach( $posts as $post ) {
			$item = array(
				'post_id'            => $post->ID,
				'rel'                => ! empty( $post->nofollow ) ? 'nofollow' : '',
				'excerpt'            => PMC::untexturize( strip_tags( $post->post_excerpt ) ),
				'date'               => get_the_date( '', $post ),
				'date_utc_timestamp' => get_the_date( 'U', $post ),
				'comment_count'      => intval( get_comments_number( $post ) ),
				'link'               => get_permalink( $post->ID ),
				'title'              => PMC::untexturize( strip_tags( $post->post_title ) ),
			);

			if ( $thumb_id = get_post_thumbnail_id( $post->ID ) ) {
				if ( $image = wp_get_attachment_image_src( $thumb_id, 'related-articles' ) ) {
					$image[3] = $thumb_id;
				}
			}

			if( empty( $image ) ) {
				$images = get_children( array(
					'post_type'      => 'attachment',
					'posts_per_page' => 1,
					'post_mime_type' => 'image',
					'post_parent'    => $post->ID,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
					'no_found_rows'  => true,
				) );

				if( isset( $images ) && ! empty( $images ) ) {
					$attachment = array_shift( $images );	//just need the first element
					$image = wp_get_attachment_image_src( $attachment->ID, 'related-articles' );
					$image[3] = $attachment->ID;
					unset( $attachment );
				}
				unset( $images );
			}

			//if we still don't have an image, fall back to using get_the_image if it exist
			if( ( empty( $image ) || is_wp_error( $image ) ) && function_exists( 'get_the_image' ) ) {
				$image_scan = get_the_image( array(
					'post_id'      => $post->ID,
					'link_to_post' => false,
					'format'       => 'array',
					'image_scan'   => true, //scan post content for the first IMG tag
					'size'         => 'full',
					'echo'         => false,
				) );

				if( ! empty( $image_scan ) ) {
					$image = array(
						$image_scan['src'], // source
						'', // 1: width - get_the_image doesn't know what it is
						'', // 2: height - get_the_image doesn't know what it is
						'', // 3: attachment id - not applicable here
						$image_scan['alt'], // 4: alt text
						'', // 5: title
					);
				}
				unset( $image_scan );
			}

			if( isset( $image[0] ) ) {
				$item['image_src'] =	$image[0];
			} else {
				$item['image_src'] = '';
			}

			if( isset( $image[1] ) ) {
				$item['image_width'] = $image[1];
			} else {
				$item['image_width'] = '';
			}

			if( isset( $image[2] ) ) {
				$item['image_height'] = $image[2];
			} else {
				$item['image_height'] = '';
			}

			if( isset( $image[3] ) ) {
				$item['image_id'] = $image[3];
			} else {
				$item['image_id'] = '';
			}

			if( ! empty( $image[4] ) ) {
				$item['image_alt'] = $image[4];
			} else {
				$item['image_alt'] = '';

				if( ! empty($item['image_id']) ) {
					$alt_text = trim( strip_tags( get_post_meta( $item['image_id'], '_wp_attachment_image_alt', true ) ) );

					if( ! empty( $alt_text ) ) {
						$item['image_alt'] = $alt_text;
					}
				}
			}

			if( ! empty( $image[5] ) ) {
				$item['image_title'] = $image[5];
			} else {
				$item['image_title'] = '';

				if( ! empty($item['image_id']) ) {
					$image_title = trim( strip_tags( get_the_title( $item['image_id'] ) ) );

					if( ! empty($image_title) ) {
						$item['image_title'] = $image_title;
					}
				}
			}

			$author = PMC::get_post_authors( $post->ID, 1, array( 'ID', 'display_name' ) );

			if( ! empty( $author ) ) {
				$author = array_shift( $author );
				$item['author'] = $author['display_name'];
				$item['author_id'] = $author['ID'];
			} else {
				$item['author'] = '';
				$item['author_id'] = 0;
			}

			$results[] = (object)$item;
			unset( $author, $item, $image );
		}
		return $results;
	}

	/**
	 * Helper function to exclude posts.
	 *
	 * @param array $posts       Array of Posts to exclude from.
	 * @param array $exclude_ids Array of post ids to exclude from $posts.
	 *
	 * @return array
	 */
	private function _exclude_posts( $posts, $exclude_ids ) : array {

		if ( ! empty( $posts ) && is_array( $posts ) ) {
			$posts = array_filter(
				$posts,
				function( $post ) use ( $exclude_ids ) {

					if ( isset( $post->ID ) ) {
						return ! in_array( $post->ID, (array) $exclude_ids, true );
					} elseif ( isset( $post->post_id ) ) {
						return ! in_array( $post->post_id, (array) $exclude_ids, true );
					}

					return true;
				}
			);
		}

		return (array) $posts;
	}

	/**
	 * Function accepts post ID and overrides for default options
	 * returns related articles for the post ID.
	 *
	 * @since 2012-12-11 ?
	 * @version 2013-01-29 Amit Gupta
	 * @version 2013-06-26 Amit Gupta
	 * @version 2014-06-13 Hau - add options to query post_types for supporting related pmc gallery
	 * @version 2015-03-30 Hau - refactor codes
	 * @version 2015-10-29 Harshad Pandit - PMCVIP-258 - added two new argument fetch_by_category to get post in category and disable map_posts.
	 * @version 2019-08-28 Kelin Chauhan
	 *
	 * @param mixed $post The post id/object.
	 * @param array $args @see WP_Query
	 *                    last_n_days  => restrict related posts to last n day
	 *                    max_articles => number of articles to return.
	 * @return array The list of related posts
	 */
	public function get_related_articles( $post, array $args = [] ) {

		$post = get_post( $post );

		if ( empty( $post ) ) {
			return false;
		}

		$exclude_ids = [ $post->ID ];

		// Apply default so we can turn on es support as default via cheezcap.
		$args = $this->_apply_default( $args );

		if ( ! isset( $args['disable_fetch_by_tags'] ) || true !== $args['disable_fetch_by_tags'] ) {

			// Use get_the_terms & wp_list_pluck to avoid uncached function calls.
			$terms = get_the_terms( $post->ID, 'post_tag' );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {

				$args['tag__in'] = wp_list_pluck( array_values( $terms ), 'term_id' );

			} elseif ( isset( $args['es'] ) && true === $args['es'] && isset( $args['fetch_by_category'] ) && true === $args['fetch_by_category'] ) {

				// Only enable this portion of the query when elastic search is enabled and no tags were found.
				$categories = get_the_terms( $post->ID, 'category' );

				if ( ( ! empty( $categories ) && ! is_wp_error( $categories ) ) ) {
					$args['category__in'] = wp_list_pluck( array_values( $categories ), 'term_id' );
				}
			}
		}

		$return_assoc_array = isset( $args['return_assoc_array'] ) ? (bool) $args['return_assoc_array'] : false;
		unset( $args['return_assoc_array'] );

		$filler_posts_enabled = apply_filters( 'pmc_related_articles_add_filler_posts', true );

		// Cache key against all values in $args since we use it for WP_Query.
		$cache_key = md5( 'get_related_articles_' . $filler_posts_enabled . wp_json_encode( $args ) );
		$posts     = wp_cache_get( $cache_key, self::CACHE_GROUP );

		if ( $posts ) {

			// We cached posts based on the arguments without the $exclude_ids and then we'll take that ID out before we return the array.
			$posts = $this->_exclude_posts( $posts, $exclude_ids );

			// Only return the number of articles requested.
			if ( count( $posts ) > $args['posts_per_page'] ) {
				$posts = array_splice( $posts, 0, $args['posts_per_page'] );
			}

			// IMPORTANT: We stored data in array of object
			// this conversion is required for backward compatibility.
			if ( $return_assoc_array ) {
				$posts = array_map(
					function( $item ) {
						return (array) $item;
					},
					(array) $posts
				);
			}
			return $posts;
		}

		// We need to grab enough articles in order to exclude some articles.
		$post_per_page           = $args['posts_per_page'];
		$args['posts_per_page'] += count( $exclude_ids );

		$query = new WP_Query( $args );
		$posts = $query->posts;

		if ( is_wp_error( $posts ) ) {
			$posts = array();
		} else {
			$after_excluding = $this->_exclude_posts( $posts, $exclude_ids );
		}

		if ( count( $after_excluding ) < $post_per_page && $filler_posts_enabled ) {

			$exclude_from_filler_posts = array_merge( $exclude_ids, wp_list_pluck( array_values( $after_excluding ), 'ID' ) );

			// Need to remove filter so we can grab any articles for filler.
			unset( $args['tag__in'] );
			unset( $args['date_query'] );
			unset( $args['category__in'] );
			unset( $args['tax_query'] );

			// We need to grab extra posts.
			$args['posts_per_page'] = $post_per_page - count( $after_excluding ) + count( $exclude_from_filler_posts );

			$query        = new WP_Query( $args );
			$filler_posts = $query->get_posts();

			if ( ! empty( $filler_posts ) && ! is_wp_error( $filler_posts ) ) {
				// Excluding duplicate posts.
				$filler_posts = $this->_exclude_posts( $filler_posts, $exclude_from_filler_posts );
				$filler_posts = array_map(
					function( $post ) {
						$post->nofollow = true;
						return $post;
					},
					(array) $filler_posts
				);

				$posts = array_merge( $posts, $filler_posts );
			}
		}

		// If current post is in result set then increase $posts_per_page by one to cache the result.
		if ( in_array( $post->ID, (array) wp_list_pluck( $posts, 'ID' ), true ) ) {
			$post_per_page++;
		}

		// Only return the number of articles requested.
		if ( count( $posts ) > $post_per_page ) {
			$posts = array_splice( $posts, 0, $post_per_page );
		}

		// Added argument to get post without map_posts.
		if ( ! isset( $args['disable_post_mapping'] ) || true !== $args['disable_post_mapping'] ) {
			$posts = $this->_map_posts( $posts, $return_assoc_array );
		}

		// Setting the chache without excluding the current post.
		wp_cache_set( $cache_key, $posts, self::CACHE_GROUP, self::CACHE_DURATION );

		// Excluding duplicate posts.
		$posts = $this->_exclude_posts( $posts, $exclude_ids );

		// IMPORTANT: We stored data in array of object
		// this conversion is required for backward compatibility.
		if ( $return_assoc_array ) {
			$posts = array_map(
				function( $item ) {
					return (array) $item;
				},
				(array) $posts
			);
		}

		return $posts;
	}

	/**
	 * Get evergreen related articles of $post.
	 * Related posts are found based on categories.
	 *
	 * @param int|WP_Post $post            The post id/object.
	 * @param int         $number_of_posts Number of related links to fetch, defaults to 3.
	 *
	 * @return array                       The list of related evergreen posts.
	 */
	public function get_related_evergreen_articles( $post, $number_of_posts = 3 ) : array {

		$post = get_post( $post );

		if ( empty( $post ) ) {
			return [];
		}

		if ( ! class_exists( '\PMC\Post_Options\Taxonomy', false ) ) {
			return []; // @codeCoverageIgnore
		}

		$categories = get_the_terms( $post->ID, 'category' );
		$args       = [];

		// No categories assigned then return.
		if ( empty( $categories ) || is_wp_error( $categories ) ) {
			return [];
		}

		$args['category__in']   = wp_list_pluck( array_values( $categories ), 'term_id' );
		$args['posts_per_page'] = $number_of_posts + 1; // Need to grab enough posts as current post would be removed if it also exists in the result set.

		// Cache key against all values in $args since we use it for WP_Query.
		$cache_key       = md5( 'related-evergreen-posts' . wp_json_encode( $args ) );
		$evergreen_posts = wp_cache_get( $cache_key, self::CACHE_GROUP );

		if ( ! $evergreen_posts ) {

			// Fetch evergreen content posts with given categories.
			$evergreen_posts = \PMC\Post_Options\API::get_instance()->get_posts_having_option( 'evergreen-content', $args );

			if ( empty( $evergreen_posts ) || ! is_array( $evergreen_posts ) ) {
				return [];
			}

			// Setting the cache without excluding the current post.
			wp_cache_set( $cache_key, $evergreen_posts, self::CACHE_GROUP, self::CACHE_DURATION );

		}

		// Remove the current post from result set.
		$evergreen_posts = $this->_exclude_posts( $evergreen_posts, [ $post->ID ] );

		// Return only the number of items requested.
		$evergreen_posts = array_slice( $evergreen_posts, 0, $number_of_posts );

		return $evergreen_posts;

	}

	/**
	 * function accepts post IDs to exclude and overrides for default options
	 * returns recent articles
	 *
	 * @since 2013-06-26 Amit Gupta
	 * @version 2015-03-30 Hau - refactor codes
	 *
	 * @param mixed $exclude_ids The single post id or array of id to exclude
	 * @param array $args @see WP_Query
	 *     last_n_days  => restrict related posts to last n day
	 *     max_articles => number of articles to return
	 * @return array The list of recent posts
	 */
	public function get_recent_articles( $exclude_ids = 0, $args = array() ) {

		if ( empty( $exclude_ids ) ) {
			if( isset( $_POST['ID'] ) ) {
				$exclude_ids = array( intval( $_POST['ID'] ) );
			} else {
				if ( $post = get_post() ) {
					$exclude_ids = array( $post->ID );
				} else {
					//we dont have a post ID so bail out
					return false;
				}
			}
		}
		if ( ! is_array( $exclude_ids ) ) {
			$exclude_ids = array( $exclude_ids );
		}

		$args = $this->_apply_default( $args );

		$return_assoc_array = isset( $args['return_assoc_array'] ) ? (bool)$args['return_assoc_array'] : false;
		unset( $args['return_assoc_array'] );

		// cache key against all values in $args since we use it for WP_Query
		$cache_key = md5( 'get-latest-posts'. serialize( $args ) );
		if ( $posts = wp_cache_get( $cache_key, self::CACHE_GROUP ) ) {
			// IMPORTANT: We stored data in array of object
			// this conversion is required for backward compatibility
			if ( $return_assoc_array ) {
				$posts = array_map( function( $item ) {
					return (array)$item;
				}, $posts );
			}
			return $posts;
		}

		// We need to grab enough articles in order to excludes some articles
		$post_per_page = $args['posts_per_page'];
		$args['posts_per_page'] += count( $exclude_ids );

		$query = new WP_Query( $args );
		$posts = $query->get_posts();

		if ( is_wp_error( $posts ) ) {
			$posts = array();
		}
		else {
			$posts = array_filter( $posts, function( $post ) use( $exclude_ids ) {
					return ! in_array( $post->ID, $exclude_ids, true );
				} );
		}

		// only return the number of articles requested
		if ( count( $posts ) > $post_per_page ) {
			$posts = array_splice( $posts, 0, $post_per_page );
		}

		$posts = $this->_map_posts( $posts );
		wp_cache_set( $cache_key, $posts, self::CACHE_GROUP, self::CACHE_DURATION );

		// IMPORTANT: We stored data in array of object
		// this conversion is required for backward compatibility
		if ( $return_assoc_array ) {
			$posts = array_map( function( $item ) {
				return (array)$item;
			}, $posts );
		}
		return $posts;
	}

//end of class
}


//legacy function for backward compatibility
function pmc_related_articles( $post_id, $opts = array() ) {
	if( ! isset( $GLOBALS['pmc_related_articles'] ) || ! is_a( $GLOBALS['pmc_related_articles'], 'PMC_Related_Articles' ) ) {
		$GLOBALS['pmc_related_articles'] = PMC_Related_Articles::get_instance();
	}

	return $GLOBALS['pmc_related_articles']->get_related_articles( $post_id, $opts );
}

//fetch recent posts only
function pmc_recent_articles( $excluded_posts, $opts = array() ) {
	if( ! isset( $GLOBALS['pmc_related_articles'] ) || ! is_a( $GLOBALS['pmc_related_articles'], 'PMC_Related_Articles' ) ) {
		$GLOBALS['pmc_related_articles'] = PMC_Related_Articles::get_instance();
	}

	return $GLOBALS['pmc_related_articles']->get_recent_articles( $excluded_posts, $opts );
}

//EOF
