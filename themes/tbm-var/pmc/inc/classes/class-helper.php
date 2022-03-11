<?php
/**
 * General Helpers class
 *
 * @package PMC\Core\Inc
 */

namespace PMC\Core\Inc;

class Helper {

	/**
	 * Build and return a string used to display an html tag's attributes
	 *
	 * E.g. Given an array like:
	 * [
	 *    'class' => 'foobar',
	 *    'style' => 'display: block;',
	 *    'itemprop' => 'author',
	 * ]
	 * The returned string would be:
	 * class="foobar" style="display: block;" itemscope itemprop="author"
	 *
	 * @param  array $attributes An array of html attributes to concat together.
	 *                     [
	 *                         'href' => [
	 *                             'value' => 'http://{site}.com',
	 *                             'escape_callback' => 'esc_url',
	 *                         ],
	 *                         'class' => [
	 *                             'value' => 'my-cool-class'
	 *                         ],
	 *                     ]
	 *                     escape_fallback defaults to esc_attr.
	 *
	 * @return string A concatenated string of given html attributes
	 */
	public static function get_escaped_html_tag_attributes( array $attributes ) {

		$string = '';
		$did_itemscope = false;

		if ( empty( $attributes ) ) {
			return '';
		}

		foreach ( $attributes as $property => $data ) {

			if ( empty( $property ) || ! is_string( $property ) ) {
				continue;
			}

			if ( empty( $data['value'] ) ) {
				continue;
			}

			// Disallow any event attribute to prevent exploitation
			// e.g. onclick, onload, etc.
			if ( preg_match( '/^on.+$/', $property ) ) {
				continue;
			}

			$escape_callback = 'esc_attr';

			if ( ! empty( $data['escape_callback'] ) ) {
				$escape_callback = $data['escape_callback'];
			}

			if ( ! is_callable( $escape_callback ) ) {
				continue;
			}

			// Schema attributes must be accompanied by an itemscope attribute
			if ( ! $did_itemscope ) {
				if ( in_array( $property, [ 'itemprop', 'itemtype' ], true ) ) {
					$string .= ' itemscope';

					// There should only ever be one itemscope attribute
					//
					// There should also only be one itemprop or itemtype
					// per elements, however, let's not force that at
					// this time—there may be a use case for that.. _shrug_
					$did_itemscope = true;
				}
			}

			$string .= sprintf(
				' %s="%s"',
				sanitize_text_field( $property ),
				call_user_func( $escape_callback, $data['value'] )
			);
		}

		return $string;
	}

	/**
	 * Get a category link with a vertical.
	 *
	 * @param mixed $category Should be a slug, id, or WP_Term. Defaults to primary of current post
	 *
	 * @return string
	 */
	public static function get_category_link( $category = null ) {
		if ( empty( $category ) ) {
			$category = pmc_get_the_primary_term( 'category' );
		} elseif ( is_numeric( $category ) ) {
			$category = get_term_by( 'id', $category, 'category' );
		}

		if ( empty( $category ) ) {
			return '';
		}

		$term_link = get_term_link( $category, 'category' );

		return ( ! is_wp_error( $term_link ) ) ? $term_link : '';
	}

	/**
	 * Get the top stories module for a vertical landing page.
	 * This is the cached version & should be the one used.
	 *
	 * @param  WP_Term  $term Term object.
	 * @param  int $count Number of posts to get. Maximum of 4.
	 * @return array WP_Post objects if posts found else an empty array
	 */
	public static function get_vertical_top_stories( $term, $count = 3 ) {

		if ( ! is_object( $term ) || empty( $term->taxonomy ) || empty( $term->term_id ) ) {
			return [];
		}

		$count = min( apply_filters( 'pmc_core_vertical_max_top_stories', 4 ), intval( $count ) );

		$cl = get_called_class();

		$cache_key = sprintf( 'pmc_core_get_vertical_top_stories-%s-%s-%d', $term->taxonomy, $term->term_id, $count );

		$cache = new \PMC_Cache( $cache_key );

		return $cache->expires_in( 300 ) // 5 minutes
		             ->updates_with( [ __CLASS__, 'get_uncached_vertical_top_stories' ], [ $term, $count ] )
		             ->get();

	}

	/**
	 * Returns the top stories from a taxonomy term
	 * This is the uncached version and should not be used directly.
	 *
	 * @see pmc_core_get_vertical_top_stories()
	 *
	 * @param  WP_Term  $term Term object.
	 * @param  int $count Number of posts to get. Maximum of 4.
	 * @return array WP_Post objects if posts found else an empty array
	 */
	public static function get_uncached_vertical_top_stories( $term, $count = 3 ) {

		// add buffer count of posts to compensate for possible exclusions
		$buffer_for_exclusions = 3;

		if ( ! is_object( $term ) || empty( $term->taxonomy ) || empty( $term->term_id ) ) {
			return [];
		}

		if ( $count < 1 ) {
			return [];
		}

		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
		$posts = get_posts(
			[
				'suppress_filters' => false,
				'post_type'        => 'post',
				'post_status'      => 'publish',
				'posts_per_page'   => $count + $buffer_for_exclusions,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				'tax_query'        => [
					[
						'taxonomy'         => $term->taxonomy,
						'terms'            => $term->term_id,
						'include_children' => false,
					],
				],
			]
		);

		$filtered_posts = [];

		foreach ( $posts as $post ) {
			// skip over post-options: exclude-from-homepage and exclude-from-section-fronts
			if ( is_home() || is_front_page() ) {
				if ( has_term( 'exclude-from-homepage', '_post-options', $post->ID ) ) {
					continue;
				}
			}

			if ( is_archive() ) {
				if ( has_term( 'exclude-from-section-fronts', '_post-options', $post->ID ) ) {
					continue;
				}
			}

			$filtered_posts[] = $post;

			if ( count( $filtered_posts ) === $count ) {
				break;
			}

		}

		return $filtered_posts;

	}

	/**
	 * Output namespaces into the <html> html tag
	 *
	 * @param bool $echo Defaults to true. Pass false to return a string.
	 *
	 * @return mixed|string|void
	 */
	public static function html_tag_namespace( $echo = true ) {

		$namespaces = apply_filters( 'pmc_html_tag_namespaces', [] );

		if ( empty( $namespaces ) ) {
			return '';
		}

		$namespaces = static::get_escaped_html_tag_attributes( $namespaces );

		if ( ! $echo ) {
			return $namespaces;
		}

		// Safe attributes.
		echo $namespaces;

	}

	/**
	 * Get posts based on term_id.
	 *
	 * @param int    $term_id Term id.
	 * @param string $taxonomy Taxonomy slug.
	 * @param int    $number  Number of posts needs to fetch, default is 5.
	 *
	 * @return array Returns posts array else empty array.
	 */
	public static function get_term_posts( $term_id, $taxonomy, $number = 5 ) {

		if ( empty( $term_id ) || empty( $taxonomy ) ) {
			return [];
		}

		// add buffer count of posts to compensate for possible exclusions
		$buffer_for_exclusions = 3;

		$args = [
			'post_type'           => apply_filters( 'pmc_get_term_posts_post_type', 'post' ),
			'post_status'         => 'publish',
			'tax_query'           => [ // WPCS: slow query ok.
				[
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => $term_id,
				],
			],
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'include_children'    => false,
			'posts_per_page'      => $number + $buffer_for_exclusions,
			'date_query'          => [
				'after' => '360 day ago',
			],
		];

		// @codeCoverageIgnoreStart
		if ( Theme::get_instance()->is_es_enabled() ) {
			$args['es'] = true;
		}
		if ( is_post_type_archive( 'pmc_top_video' ) ) {
			$args['post_type'] = 'pmc_top_video';
		}
		// @codeCoverageIgnoreEnd
		$query = new \WP_Query(
			$args
		);


		$filtered_posts = [];

		if ( ! empty( $query->posts ) ) {

			foreach ( $query->posts as $post ) {
				// skip over post-options: exclude-from-homepage and exclude-from-section-fronts
				if ( is_home() || is_front_page() ) {
					if ( has_term( 'exclude-from-homepage', '_post-options', $post->ID ) ) {
						continue;
					}
				}

				if ( is_archive() ) {
					if ( has_term( 'exclude-from-section-fronts', '_post-options', $post->ID ) ) {
						continue;
					}
				}

				$filtered_posts[] = $post;

				if ( count( $filtered_posts ) === $number ) {
					break;
				}

			}

			/* Restore original Post Data */
			wp_reset_postdata();
		}

		return $filtered_posts;
	}

	/**
	 * Get posts based on term_id(this function is wrapper for - get_term_posts()).
	 *
	 * @param int    $term_id Term id.
	 * @param string $taxonomy Taxonomy slug.
	 * @param int    $number  Number of posts needs to fetch, default is 5.
	 *
	 * @return array Returns posts array else empty array.
	 */
	public static function get_term_posts_cache( $term_id, $taxonomy, $number = 5 ) {

		if ( empty( $term_id ) || ! is_numeric( $term_id ) || empty( $taxonomy ) ) {
			return [];
		}

		$cache_key = sanitize_key( 'pmc_core_most_recent_tag_' . $term_id . '_' . $number );

		$pmc_cache = new \PMC_Cache( $cache_key );

		$time_in_secs = 10 * MINUTE_IN_SECONDS + wp_rand( 1, 150 );
		$time_in_secs = intval( $time_in_secs );

		// Cache for 1 hour.
		$cache_data = $pmc_cache->expires_in( $time_in_secs )
								->updates_with(
									[ __CLASS__, 'get_term_posts' ],
									[
										'term_id'  => $term_id,
										'taxonomy' => $taxonomy,
										'number'   => $number,
									]
								)
								->get();

		if ( is_array( $cache_data ) && ! empty( $cache_data ) && ! is_wp_error( $cache_data ) ) {

			return $cache_data;
		}

		return [];
	}

	/**
	 * Keep links inside Excerpt, based on wp_trim_excerpt
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return mixed|void
	 *
	 */
	public static function get_the_excerpt( $post_id ) {

		$text = '';

		// We want to ignore <!--more--> and pagination, so accessing post_content directly instead of using get_the_content().
		$post = get_post( $post_id );

		// Return override if it exists.
		$override = get_post_meta( $post->ID, 'override_post_excerpt', true );

		if ( ! empty( $override ) ) {
			return $override;
		}

		if ( ! empty( $post->post_excerpt ) ) {
			$text = $post->post_excerpt;
		}

		if ( empty( $text ) && ! empty( $post->post_content ) ) {

			$text = $post->post_content;

			$text = strip_shortcodes( $text );

			$text = \PMC::truncate( $text, 200, '' );

			$text = apply_filters( 'the_content', $text );

			$text = str_replace( ']]>', ']]&gt;', $text );

			// strip tags, but not these
			$text = strip_tags( $text, '<a><i><em><b><strong>' );

		}

		if ( ! empty( $text ) ) {
			$text = \PMC::truncate( $text, 200 );
		}

		return $text;
	}

}

// EOF
