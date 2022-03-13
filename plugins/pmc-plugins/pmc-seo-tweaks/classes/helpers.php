<?php

namespace PMC\SEO_Tweaks;

use \PMC;

class Helpers {

	/**
	 * Change robots.txt file
	 *
	 * @param $output
	 * @param $public
	 *
	 * @return string
	 */
	public static function seo_robots_txt( $output, $public ) {
		if ( ! PMC::is_production() ) {

			if ( ! defined( 'PMC_IS_PRODUCTION' ) || false === PMC_IS_PRODUCTION ) {
				$robots_text  = 'User-agent: *' . PHP_EOL;
				$robots_text .= 'Disallow: /';

				return $robots_text;
			}
		}

		if ( $public ) {
			$output .= 'User-agent: *' . PHP_EOL;

			// Prevent search pages from getting indexed
			$output .= 'Disallow: /?s=' . PHP_EOL;
			$output .= 'Disallow: /*/?s=' . PHP_EOL;
			$output .= 'Disallow: /search/' . PHP_EOL;
			$output .= 'Disallow: /search?' . PHP_EOL;
			$output .= 'Disallow: *?v02' . PHP_EOL;
			$output .= 'Disallow: *?replytocom' . PHP_EOL;
			$output  = apply_filters( 'pmc_robots_txt', $output, $public );
		}

		return $output;
	}

	/**
	 * Hides illegal query vars from bots.
	 */
	public static function hide_illegal_query_vars_from_bots() {
		$illegal_query_vars = array( 'v02', 'replytocom' );

		$user_agent = \PMC::filter_input( INPUT_SERVER, 'HTTP_USER_AGENT' );

		// if its a bot, check for illegal query vars
		if ( ! empty( $user_agent ) && preg_match( '#(bot|yandex|google|jeeves|spider|crawler|slurp)#si', $user_agent ) ) {

			$request_uri = \PMC::filter_input( INPUT_SERVER, 'REQUEST_URI' );

			$url = wp_parse_url( esc_url_raw( $request_uri ) );
			if ( empty( $url['query'] ) ) {
				return;
			}

			$pre_process_query_vars  = explode( '&', $url['query'] );
			$post_process_query_vars = array();

			foreach ( $pre_process_query_vars as $key => $qv ) {

				$add = true;

				foreach ( $illegal_query_vars as $iqv ) {
					if ( preg_match( '#^' . $iqv . '=#is', $qv ) || $iqv === $qv ) {
						$add = false;
						break;
					}
				}

				// rebuild query vars with only valid ones
				if ( $add ) {
					$post_process_query_vars[ $key ] = $qv;
				}
				unset( $add );
			}

			if ( count( $post_process_query_vars ) !== count( $pre_process_query_vars ) ) {
				if ( 0 === count( $post_process_query_vars ) ) {
					wp_safe_redirect( esc_url_raw( $url['path'] ), 301 );
				} else {
					wp_safe_redirect( esc_url_raw( $url['path'] . '?' . implode( '&', $post_process_query_vars ) ), 301 );
				}
			}
		}
	}

	/**
	 * Runs functions that include data we want in the head of the site.
	 *
	 * Use filter pmc_seo_tweaks_robots_override to override the contnet of the robots meta tag
	 */
	public static function wp_head() {
		global $paged;

		$meta_values = apply_filters( 'pmc_seo_tweaks_robots_override', false );
		$robot_names = array_unique( (array) apply_filters( 'pmc_seo_tweaks_robot_names', [ 'robots' ] ) );

		// force no index on all 404 pages
		if ( is_404() ) {
			$meta_values = 'noindex, nofollow';
		}

		if ( empty( $meta_values ) ) {
			$request_uri = \PMC::filter_input( INPUT_SERVER, 'REQUEST_URI' );
			$uri         = trim( PMC::unleadingslashit( untrailingslashit( wp_parse_url( esc_url_raw( $request_uri ), PHP_URL_PATH ) ) ) );
			if ( substr_count( $uri, '/' ) > 1 && strpos( $uri, '/comment-page' ) > 5 ) {
				//its comments page of article
				$meta_values = 'noindex,nofollow';
			} elseif ( is_search() ) {
				$meta_values = 'noindex,nofollow';
			} elseif ( static::noindex_check() || ! PMC::is_production() ) {
				$meta_values = 'noindex,nofollow';
			}
		}

		if ( ! empty( $meta_values ) ) {
			// output value for each robot name
			foreach ( $robot_names as $robot ) {
				printf( '<meta name="%s" content="%s">' . PHP_EOL, esc_attr( $robot ), esc_attr( $meta_values ) ); // @codingStandardsIgnoreLine
			}
		}

		$queried_object = get_queried_object();

		if ( ! empty( $queried_object ) ) {

			$gn_exclude = false;

			// Exclude from Google News.
			// The term exclude-from-google-news is defined in pmc-post-options
			if ( is_single() && taxonomy_exists( PMC\Post_Options\Base::NAME ) ) {
				$gn_exclude = has_term( 'exclude-from-google-news', PMC\Post_Options\Base::NAME, $queried_object );
			}

			$gn_exclude = apply_filters( 'pmc_seo_tweaks_googlebot_news_override', $gn_exclude );

			if ( $gn_exclude ) {
				echo ( apply_filters( 'pmc_seo_tweaks_googlebot_add_explicit_index', false ) ) ? '<meta name="googlebot" content="index, follow" />' . PHP_EOL : '';
			}
		}

		static::canonical();
		static::add_pagination_rel_tags();
	}

	/**
	 * Prints out the canonical URL in the head.
	 *
	 * @param bool $echo
	 * @param bool $unpaged
	 * @param int  $post_id
	 *
	 * @return bool|false|string|void|\WP_Error
	 */
	public static function canonical( $echo = true, $unpaged = false, $post_id = 0 ) {
		$canonical_safe = false;
		global $page , $paged;
		// Set decent canonicals for homepage, singulars and taxonomy pages

		if ( $post_id > 0 && 'publish' === get_post_status( $post_id ) ) {
			$canonical_safe = esc_url_raw( get_permalink( $post_id ) );
		} elseif ( is_singular() ) {
			$canonical_safe = esc_url_raw( get_permalink( get_queried_object() ) );
			// Fix paginated pages
			if ( get_query_var( 'page' ) > 1 ) {
				global $wp_rewrite;
				if ( ! $wp_rewrite->using_permalinks() ) {
					$link = add_query_arg( 'page', $page, $canonical_safe );
				} else {
					$link = user_trailingslashit( trailingslashit( $canonical_safe ) . $page );
				}
			}
		} else {
			if ( is_search() ) {
				$canonical_safe = esc_url_raw( get_search_link() );
			} elseif ( is_front_page() ) {
				$canonical_safe = esc_url_raw( home_url( '/' ) );
			} elseif ( is_home() && 'page' === get_option( 'show_on_front' ) ) {
				$canonical_safe = esc_url_raw( get_permalink( get_option( 'page_for_posts' ) ) );
			} elseif ( is_tax() || is_tag() || is_category() ) {
				$term = get_queried_object();
				$url  = get_term_link( $term, $term->taxonomy );
				// VIP: Stopping fatal errors "Object of class WP_Error could not be converted to string"
				if ( is_wp_error( $url ) ) {
					$url = '';
				}
				$canonical_safe = esc_url_raw( $url );
			} elseif ( function_exists( 'get_post_type_archive_link' ) && is_post_type_archive() ) {
				$canonical_safe = esc_url_raw( get_post_type_archive_link( get_post_type() ) );
			} elseif ( is_author() ) {
				$canonical_safe = esc_url_raw( get_author_posts_url( get_query_var( 'author' ), get_query_var( 'author_name' ) ) );
			} elseif ( is_archive() ) {
				if ( is_date() ) {
					if ( is_day() ) {
						$canonical_safe = esc_url_raw( get_day_link( get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) ) );
					} elseif ( is_month() ) {
						$canonical_safe = esc_url_raw( get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) ) );
					} elseif ( is_year() ) {
						$canonical_safe = esc_url_raw( get_year_link( get_query_var( 'year' ) ) );
					}
				}
			}

			if ( $canonical_safe && $unpaged ) {
				// force permalink to use http
				$canonical_safe = esc_url_raw( apply_filters( 'pmc_canonical_url', $canonical_safe ) );

				return $canonical_safe;
			}

			if ( $canonical_safe && $paged > 1 ) {
				global $wp_rewrite;
				if ( ! $wp_rewrite->using_permalinks() ) {
					$canonical_safe = add_query_arg( 'paged', $paged, $canonical_safe );
				} else {
					$canonical_safe = user_trailingslashit( trailingslashit( $canonical_safe ) . trailingslashit( $wp_rewrite->pagination_base ) . $paged );
				}
			}
		}

		$canonical_safe = esc_url_raw( apply_filters( 'pmc_canonical_url', $canonical_safe ) );

		if ( $canonical_safe && ! is_wp_error( $canonical_safe ) ) {
			if ( $echo ) {
				echo '<link rel="canonical" href="' . esc_url( $canonical_safe, null, 'other' ) . '" />' . PHP_EOL; // @codingStandardsIgnoreLine
			} else {
				return $canonical_safe;
			}
		}
	}

	/**
	 * For adding rel="next" & rel="prev" to paginated pages
	 *
	 * @since 2018-07-18 - Jignesh Nakrani - READS-1082
	 */
	public static function add_pagination_rel_tags() {

		global $wp_query, $wp_rewrite;

		$pagination_slug = trailingslashit( $wp_rewrite->pagination_base );

		if ( is_front_page() || is_archive() ) {
			$current_page_num = ( 0 === get_query_var( 'paged' ) ) ? 1 : get_query_var( 'paged' );
		} else {
			return;
		}

		if ( is_category() || is_tax() || is_post_type_archive() || is_front_page() ) {
			$url = self::canonical( false, true );
		} else {
			return;
		}

		$url = apply_filters( 'pmc_prev_next_rel_tag_url', $url );

		// Output the meta tags as applicable
		if ( ! empty( $current_page_num ) && ! empty( $url ) && ! is_wp_error( $url ) ) {

			$url = trailingslashit( $url );

			switch ( $current_page_num ) {
				case 1:
					break;
				case 2:
					printf( '<link rel="prev" href="%s" />', esc_url( $url ) );
					break;
				default:
					printf( '<link rel="prev" href="%s/" />', esc_url( $url . $pagination_slug . strval( $current_page_num - 1 ) ) );
					break;
			}

			if ( $wp_query->max_num_pages > 0 && $current_page_num < $wp_query->max_num_pages ) {
				printf( '<link rel="next" href="%s/" />', esc_url( $url . $pagination_slug . strval( $current_page_num + 1 ) ) );
			}
		}

	}

	/**
	 * Filter meta_tags filter in vip plugins add-meta-tags-mod/add-meta-tags.php
	 * Meta description: if no SEO meta description render default description (post excerpt)
	 * Meta keywords: if no SEO meta keywords render empty keywords meta tag instead of default keywords like categories
	 *
	 * @param $meta_tags
	 *
	 * @return mixed|string
	 */
	public static function amt_metatags( $meta_tags ) {
		//category & tags handle their own filter in PMC\SEO_Tweaks\Taxonomy
		if ( is_singular() ) {

			global $post;

			/**
			 * @todo MA: should $seo_description be removed? It's not being used anywhere.
			 */
			$seo_description = apply_filters( 'pmc_metatags_seo_description', get_post_meta( $post->ID, 'mt_seo_description', true ) );
			$seo_keywords    = apply_filters( 'pmc_metatags_seo_keywords', get_post_meta( $post->ID, 'mt_seo_keywords', true ) );

			if ( empty( $seo_keywords ) ) {
				$meta_tags  = preg_replace( '/<meta name="keywords" [^>]+\>/', '', $meta_tags );
				$meta_tags .= '<meta name="keywords" content="" />' . PHP_EOL;
			}

			$post_tags = wp_list_pluck( (array) get_the_tags( $post->ID ), 'name' );

			// Clean up after forcing array type above.
			foreach ( $post_tags as $key => $value ) {
				if ( empty( $value ) ) {
					unset( $post_tags[ $key ] );
				}
			}
			$post_tags = array_values( $post_tags );

			$post_tags = apply_filters( 'pmc_metatags_news_keywords', $post_tags );

			if ( ! empty( $post_tags ) && is_array( $post_tags ) ) {

				$post_tags = array_slice( $post_tags, 0, 10 );

				$post_tags = join( ', ', $post_tags );

				if ( ! empty( $post_tags ) ) {
					$meta_tags .= '<meta name="news_keywords" content="' . esc_attr( $post_tags ) . '" />' . PHP_EOL;
				}

			}

		}

		// Handle adding SEO meta description for Co-Author pages since they are not truly a post.
		$post = get_post( get_queried_object_id() );

		if ( static::_is_co_author_page( $post ) ) {

			$seo_description            = '';
			$possible_meta_descriptions = [
				'mt_seo_description',
				'cap-_pmc_excerpt',
				'cap-description',
			];

			foreach ( $possible_meta_descriptions as $possible_meta_description ) {
				$seo_description = get_post_meta( $post->ID, $possible_meta_description, true );

				if ( ! empty( $seo_description ) ) {
					break;
				}
			}

			if ( ! empty( $seo_description ) ) {
				// Remove a description meta tag if one exists already.
				// Regex: https://regex101.com/r/Ct7alh/1
				$meta_tags  = preg_replace( '/<meta name="description" [^>]+\>/i', '', $meta_tags );
				$meta_tags .= sprintf( '<meta name="description" content="%s" />', esc_attr( $seo_description ) ) . PHP_EOL;
			}

		}

		return $meta_tags;

	}

	/**
	 * Handle title override for Co-Author pages since they are not truly a post.
	 *
	 * @param array $parts
	 *
	 * @return array
	 */
	public static function co_authors_seo_title( array $parts ) : array {

		$post = get_post( get_queried_object_id() );

		if ( static::_is_co_author_page( $post ) ) {

			$title = apply_filters(
				'pmc_metatags_seo_title',
				get_post_meta( $post->ID, 'mt_seo_title', true )
			);

			if (  ! empty( $title ) ) {
				$parts['title'] = $title;
			}

		}

		return $parts;

	}

	/**
	 * Check if we are on a valid Co-Author page for SEO Overrides.
	 *
	 * @param $post
	 *
	 * @return bool
	 */
	protected static function _is_co_author_page( $post ) : bool {

		global $mt_add_meta_tags;

		$valid_post_type = false;

		// Ensure guest-author is enabled for meta tag overrides.
		if ( is_a( $mt_add_meta_tags, '\Add_Meta_Tags' ) ) {
			$valid_post_type = (bool) $mt_add_meta_tags->is_supported_post_type( 'guest-author' );
		}

		if (
			is_author()
			&& is_a( $post, '\WP_Post' )
			&& 'guest-author' === $post->post_type
			&& ! empty( $valid_post_type )
		) {
			return true;
		}

		return false;

	}

	/**
	 * Force the display of entire permalinks on the post add/edit page.
	 *
	 * @param $html
	 * @param $id
	 *
	 * @return string
	 */
	public static function display_full_permalink( $html, $id ) {
		if ( ! intval( $id ) ) {
			return;
		}
		$post_data = get_post( intval( $id ), ARRAY_A );
		$slug      = $post_data['post_name'];

		if ( empty( $slug ) ) {
			list( $permalink, $slug ) = get_sample_permalink( $id );
		}
		return $html . '<div style="padding-top:4px"><strong>Full Slug:</strong> ' . esc_html( $slug ) . '</div>';
	}

	/**
	 * Common function to check if page should render noindex meta or not.
	 *
	 * @since 2014-08-22 Amit Sannad
	 *
	 * @return mixed|void
	 */
	public static function noindex_check() {
		$noindex = false;

		if ( is_search() || is_attachment() || is_404() || ( is_paged() && is_archive() ) ) {
			$noindex = true;
		}

		return apply_filters( 'pmc_meta_robots_noindex', $noindex );
	}

	/**
	 * SEO Fields.
	 *
	 * @param $mt_seo_fields
	 *
	 * @return array
	 */
	public static function mt_seo_fields( $mt_seo_fields ) {
		$mt_seo_fields = array(
			'mt_seo_title'       => array( __( 'Title:', 'add-meta-tags' ), 'text', __( 'The text entered here will alter the &lt;title&gt; tag using the wp_title() function. Use <code>%title%</code> to include the original title or leave empty to keep original title. i.e.) altered title <code>%title%</code>', 'add-meta-tags' ) ),
			'mt_seo_description' => array( __( 'Description:', 'add-meta-tags' ), 'textarea', __( 'This text will be used as description meta information. Left empty a description is automatically generated i.e.) an other description text', 'add-meta-tags' ) ),
		);
		return $mt_seo_fields;
	}

	/**
	 * Prevent preview pages from getting indexed
	 * @param $output
	 * @param $public
	 *
	 * @return string
	 */
	public static function disallow_preview_urls( $output, $public ) {

		if ( $public ) {
			$output .= 'User-agent: *' . PHP_EOL;
			$output .= 'Disallow: /*preview=true' . PHP_EOL;
			$output .= 'Disallow: /*theme_preview=true' . PHP_EOL;
		}
		return $output;
	}

	/**
	 * Register meta used in this plugin
	 *
	 * @codeCoverageIgnore meta is being registered
	 */
	public static function register_meta() {
		register_meta(
			'post',
			'mt_seo_title',
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			]
		);

		register_meta(
			'post',
			'mt_seo_description',
			[
				'type'         => 'string',
				'single'       => true,
				'show_in_rest' => true,
			]
		);

		register_meta(
			'post',
			'_pmc_canonical_override',
			[
				'type'          => 'string',
				'single'        => true,
				'show_in_rest'  => true,
				// private meta requires this
				'auth_callback' => '__return_true',
			]
		);
	}

	/**
	 * Add image preview meta tag to singular pages
	 *
	 * @return void
	 */
	public static function add_image_preview_meta_tag() : void {

		if ( is_singular() ) {
			echo '<meta name="robots" content="max-image-preview:large">';
		}

	}

}

// EOF
