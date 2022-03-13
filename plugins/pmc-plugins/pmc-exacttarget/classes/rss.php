<?php
namespace PMC\Exacttarget;

use PMC\Global_Functions\Traits\Singleton;
use PMC_Primary_Taxonomy;
use Sailthru_Blast_Repeat;

class RSS {
	use Singleton;

	protected function __construct() {
		// we want to run this filter late to allow each LOB to fill the data before we add them
		add_filter( 'sailthru_process_recurring_post', [ $this, 'maybe_add_taxonomies' ], 20, 2 );
	}

	/**
	 * @TODO: Re-factor function sailthru_newsletter_process_posts & integrate this function
	 *
	 * @param array $post_data
	 * @param WP_Post $post
	 * @return array
	 */
	public function maybe_add_taxonomies( $post_data, $post ) {
		$post = get_post( $post );

		$taxonomies = [
			'category'  => 'categories',
			'editorial' => 'editorials',
			'vertical'  => 'verticals',
		];

		foreach ( $taxonomies as $taxonomy => $plural ) {
			$primary = sprintf( 'primary_%s', $taxonomy );
			if ( empty( $post_data[ $primary ] ) ) {
				$post_data[ $primary ] = $this->get_primary_taxonomy( $post->ID, $taxonomy );
			}
			if ( empty( $data[ $plural ] ) ) {
				$post_data[ $plural ] = $this->get_taxonomies( $post->ID, $taxonomy );
			}
		}

		return $post_data;
	}

	/**
	 * Helper function to generate the related taxonomies data for rss xml feed
	 *
	 * @param int $post_id
	 * @param string $taxonomy
	 * @return array
	 */
	public function get_taxonomies( int $post_id, string $taxonomy ) : array {
		$data = [];

		if ( taxonomy_exists( $taxonomy ) ) {
			$terms = get_the_terms( $post_id, $taxonomy );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$data[] = [
						'name' => $term->name,
						'link' => get_term_link( $term, $taxonomy ),
					];
				}
			}
		}

		return $data;
	}

	/**
	 * @param int $post_id
	 * @param string $taxonomy
	 * @return array
	 */
	public function get_primary_taxonomy( int $post_id, string $taxonomy ) : array {
		$data = [];

		if ( taxonomy_exists( $taxonomy ) ) {

			$primary_taxonomy = false;

			if ( class_exists( PMC_Primary_Taxonomy::class ) ) {
				$primary_taxonomy = PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $post_id, $taxonomy );
			}

			// Fallback to pmc-core-v2 function if exists
			if ( empty( $primary_taxonomy ) && function_exists( 'pmc_get_the_primary_term' ) ) {
				$primary_taxonomy = pmc_get_the_primary_term( $taxonomy, $post_id );
			}

			if ( ! empty( $primary_taxonomy ) && is_a( $primary_taxonomy, 'WP_Term' ) ) {
				$data = [
					'name' => $primary_taxonomy->name,
					'link' => get_term_link( $primary_taxonomy ),
				];
			}
		}

		return $data;
	}

	/**
	 * Gets the RSS feed data from the Newsletter config.
	 *
	 * @param string $repeat_hash Newsletter configuration hash.
	 *
	 * @return array rss feed data array.
	 * @TODO: Run phpcs and php linting on get_data. Fix errors and create PHPUnit tests.
	 * @codeCoverageIgnore Legacy code moved from sailthru.php.
	 */
	public function get_data( string $repeat_hash ) : array {

		$repeat = Sailthru_Blast_Repeat::load_by_feed_ref( $repeat_hash );

		// Set feed variables
		$query                 = $repeat['query'];
		$subject               = stripslashes( $repeat['subject'] );
		$default_thumbnail_src = ! empty( $repeat['default_thumbnail_src'] ) ? $repeat['default_thumbnail_src'] : get_option( 'global_default_image' );
		$featured_post_id      = 0;
		$feed                  = [];

		// Check for a featured post configuration
		if ( ! empty( $repeat['featured_post_id'] ) && is_int( $repeat['featured_post_id'] ) ) {
			if ( 'publish' === get_post_status( $repeat['featured_post_id'] ) ) {
				$featured_post_id = $repeat['featured_post_id'];
			}
		}
		$featured_post_id = intval( apply_filters( 'pmc_exacttarget_recurring_newsletter_featured_post_id', intval( $featured_post_id ), $query ) );

		// Set up args for get_posts
		$args                     = [];
		$args['numberposts']      = absint( $query['number_of_posts'] );
		$args['post_status']      = 'publish';
		$args['suppress_filters'] = false;
		$args['no_found_rows']    = 1;

		// Zone query overrides everything since its hand curated.
		if ( empty( $query['filter_posts_by_zone'] ) ) {
			$is_zone_query = false;
		} else {
			$is_zone_query = true;
		}

		if ( false === $is_zone_query ) {
			if ( ! empty( $query['story_source'] ) ) {
				if ( 'most_commented' === $query['story_source'] ) {
					$args['orderby'] = 'comment_count';
					$args['order']   = 'DESC';
					// will be used in where filter
					add_filter( 'posts_orderby', 'sailthru_orderby_filter' );
				} elseif ( 'most_popular' === $query['story_source'] || 'wp_most_popular' === $query['story_source'] ) {
					$most_popular_posts = sailthru_newsletter_get_most_popular( $query['number_of_posts'], $query );
					$most_popular_ids   = wp_list_pluck( $most_popular_posts, 'post_id' );
					if ( is_array( $most_popular_ids ) && count( $most_popular_ids ) > 0 ) {
						if ( $featured_post_id ) {
							$most_popular_ids = array_diff( $most_popular_ids, array( $featured_post_id ) );
						}
						$args['post__in'] = $most_popular_ids;
						$args['orderby']  = 'post__in';
					} else {
						$feed['errors'] = 'Most popular did not return any posts';
						return $feed;
					}
				}
			} else {
				$args['orderby'] = 'date';
				$args['order']   = 'DESC';
				if ( isset( $query['filter_posts_by_cat'] ) && $query['filter_posts_by_cat'] ) {
					$args['category__in'] = $query['filter_categories'];
				}
				if ( isset( $query['filter_posts_by_tag'] ) && $query['filter_posts_by_tag'] ) {
					$args['tag__in'] = $query['filter_tags'];
				}
			}
		}

		if ( $featured_post_id ) {
			// Ignored legacy code errors.
			$args['exclude'] = $featured_post_id; // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
			$featured_post   = get_post( $featured_post_id );
			sailthru_newsletter_process_posts( $featured_post, true, $repeat );
		} elseif ( ! empty( $query['auto_set_featured'] ) ) {
			$args['numberposts'] += 1;
		}

		$GLOBALS['pmc_newsletter_post_days'] = intval( $query['story_source_days'] );

		//This overrides all the filters above as this is hand curated.
		if ( $is_zone_query ) {
			if ( ! empty( $query['filter_zones'][0] ) ) {
				$posts = z_get_posts_in_zone( $query['filter_zones'][0] );
			}
		} else {
			add_filter( 'posts_where', 'sailthru_where_filter' );
			// Ignored because it is legacy code and suppress_filter is set to false.
			$posts = get_posts( $args ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts

			remove_filter( 'posts_where', 'sailthru_where_filter' );
			if ( 'most_commented' === $query['story_source'] ) {
				remove_filter( 'posts_orderby', 'sailthru_orderby_filter' );
			}
		}

		// If we don't have any posts, then bail.
		if ( empty( $posts ) ) {
			$feed['errors'] = 'The query did not return any posts';
			return $feed;
		}

		if ( empty( $featured_post_id ) && ! empty( $query['auto_set_featured'] ) ) { //make the first element the features post if auto set is true, and remove the last post
			$featured_post = array_shift( $posts );
			sailthru_newsletter_process_posts( $featured_post, true, $repeat );
		} elseif ( empty( $featured_post_id ) ) {
			if ( isset( $query['require_featured'] ) && $query['require_featured'] ) {
				header( 'HTTP/1.0 400 Bad Request' );
				die;
			}
			$featured_post = null;
		}

		if ( $featured_post && empty( $featured_post['thumb'] ) ) { //give the featured post a default image, resized properly
			$width                  = get_option( 'mmcnewsletter_feature_image_width' );
			$height                 = get_option( 'mmcnewsletter_feature_image_height' );
			$featured_post['thumb'] = create_mmcnewsletter_feature_image( $default_thumbnail_src, $width, $height );
		}

		$width                 = get_option( 'mmcnewsletter_thumb_width' );
		$height                = get_option( 'mmcnewsletter_thumb_height' );
		$default_thumbnail_src = create_mmcnewsletter_feature_image( $default_thumbnail_src, $width, $height );

		array_walk( $posts, 'sailthru_newsletter_process_posts', $repeat );

		foreach ( $posts as $i => $post ) {
			if ( ! isset( $post['thumb'] ) || empty( $post['thumb'] ) ) {
				$posts[ $i ]['thumb'] = $default_thumbnail_src;
			}
		}

		$clean_post_title = str_replace( '&amp;', 'and', $featured_post['title'] );
		$clean_post_title = str_replace( '&', 'and', $clean_post_title );
		$subject          = str_replace( '[title]', stripslashes( $clean_post_title ), $subject );
		$subject          = ! empty( $subject ) ? $subject : stripslashes( $featured_post['title'] );
		$feed             = ( array(
			'posts'                 => $posts,
			'subject'               => wp_strip_all_tags( $subject ),
			'default_thumbnail_src' => $default_thumbnail_src,
			'thumbs'                => array(
				'height' => get_option( 'mmcnewsletter_thumb_height' ),
				'width'  => get_option( 'mmcnewsletter_thumb_width' ),
			),
			'feat_thumb'            => array(
				'height' => get_option( 'mmcnewsletter_feature_image_height' ),
				'width'  => get_option( 'mmcnewsletter_feature_image_width' ),
			),
		) );

		if ( ! empty( $featured_post ) ) {
			$feed['featured_post'] = $featured_post;
		}

		if ( has_filter( 'sailthru_generate_feed' ) ) {
			$feed_generated = apply_filters( 'sailthru_generate_feed', $repeat );

			if ( ! empty( $feed_generated ) && $feed_generated !== $repeat ) {
				$feed['meta_data'] = $feed_generated;
			}
		}

		return $feed;
	}

	/**
	 * Render the RSS inline into the template.
	 *
	 * @param array $newsletter_configuration_data RSS feed data from the Newsletter config.
	 * @param bool $echo
	 */
	public function render( array $newsletter_configuration_data, bool $echo = false ) {
		return \PMC::render_template(
			sprintf( '%s/templates/rss.php', untrailingslashit( PMC_EXACTTARGET_PATH ) ),
			[
				'data'      => $newsletter_configuration_data,
				'no_header' => ! $echo,
			],
			$echo
		);
	}

}
