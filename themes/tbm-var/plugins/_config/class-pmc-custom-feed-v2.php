<?php
/**
 * Setup for PMC Custom Feed v2 plugin
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since 2014-07-18
 * @version 2015-03-11 Amit Gupta - added listener on 'pmc_custom_feed_posts' for reuters multimedia feed to replace posts with carousel items
 *
 * @version 2017-09-26 CDWE-677 Milind More
 */

namespace Variety\Plugins\Config;

use PMC\Gallery\View;
use \PMC\Global_Functions\Traits\Singleton;
use \PMC_Vertical;

class PMC_Custom_Feed_V2 {

	use Singleton;

	/**
	 * @var integer Number of curated posts found
	 */
	protected $_curated_post_count = 0;

	/**
	 * Class constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 *  Initialize Hooks.
	 *
	 *
	 */
	protected function _setup_hooks() {

		/*
		 * Actions
		 */
		add_action( 'admin_init', array( $this, 'add_custom_options' ), 12 ); //run after plugin adds its own general options

		/*
		 * Filters
		 */
		add_filter( 'pmc_custom_feed_tracking_images', array( $this, 'pmc_custom_feed_tracking_images' ) );
		add_filter( 'pmc_custom_feed_reuters_curated_posts', array( $this, 'pmc_custom_feed_reuters_curated_posts' ), 10, 3 );
		// @todo causing fatal error in instant-articles feed.
		// add_filter( 'pmc_custom_feed_facebook_instant_articles_content', array( $this, 'filter_pmc_custom_feed_facebook_instant_articles_content' ), 10, 2 ); // @codingStandardsIgnoreLine.
		/**
		 * @todo variety_fetch_author function does not exists in this theme. It exist in pmc-variety-2014 theme.
		 */
		// add_filter( 'pmc_custom_feed_facebook_instant_articles_authors', array( $this, 'filter_pmc_custom_feed_facebook_instant_articles_authors' ) ); // @codingStandardsIgnoreLine.
		add_filter( 'pmc_custom_feed_facebook_instant_articles_sub_title', array( $this, 'filter_pmc_custom_feed_facebook_instant_articles_sub_title' ), 10, 2 );
		/**
		 * @todo variety_fetch_author function does not exists in this theme. It exist in pmc-variety-2014 theme.
		 */
		// add_filter( 'pmc_custom_feed_facebook_instant_articles_inappropriate_content', array( $this, 'filter_pmc_custom_feed_facebook_instant_articles_inappropriate_content' ), 10, 2 ); // @codingStandardsIgnoreLine.
		add_filter( 'pmc_feed_legacy_format_sub_heading', array( $this, 'filter_pmc_feed_legacy_format_sub_heading' ), 10, 2 );
		add_filter( 'pmc_feed_legacy_format_copyright_holder' , array( $this, 'filter_pmc_feed_legacy_format_copyright_holder' ) );
		add_filter( 'pmc_feed_legacy_format_copyright_statement' , array( $this, 'filter_pmc_feed_legacy_format_copyright_statement' ) );
		add_filter( 'pmc_feed_legacy_format_primary_vertical', array( $this, 'filter_pmc_feed_legacy_format_primary_vertical' ), 10, 2 );
		add_filter( 'pmc_custom_feed_item_slug', array( $this, 'set_reuters_custom_feed_item_slug' ), 10, 2 );
		add_filter( 'pmc_custom_feed_variety_review_meta', array( $this, 'filter_pmc_custom_feed_variety_review_meta' ), 10, 2 );
		add_filter( 'pmc_custom_feed_enable_curation', '__return_true' );

	}

	/**
	 * Get the sub heading for the article
	 *
	 * @since 2016-02-24
	 * @version 2016-02-24 Archana Mandhare PMCVIP-941
	 *
	 * @param $sub_heading string
	 * @param $post object
	 *
	 * @return string
	 *
	 */
	public function filter_pmc_feed_legacy_format_sub_heading( $sub_heading, $post ) {
		$sub_heading = get_post_meta( $post->ID , '_variety-sub-heading', true );
		return $sub_heading;
	}

	/**
	 * Return the copyright holder text
	 *
	 * @since 2016-02-24
	 * @version 2016-02-24 Archana Mandhare PMCVIP-941
	 *
	 * @return string
	 *
	 */
	public function filter_pmc_feed_legacy_format_copyright_holder() {
		return esc_html__( 'Variety Media, LLC', 'pmc-variety' );
	}

	/**
	 * Return the copyright statement text
	 *
	 * @since 2016-02-24
	 * @version 2016-02-24 Archana Mandhare PMCVIP-941
	 *
	 * @return string
	 *
	 */
	public function filter_pmc_feed_legacy_format_copyright_statement() {
		return esc_html__( '&#169; 2016 Variety Media, LLC, a subsidiary of Penske Business Media. All rights reserved.', 'pmc-variety' );
	}

	/**
	 * Return the primary vertical name for the article
	 *
	 * @since 2016-02-24
	 * @version 2016-02-24 Archana Mandhare PMCVIP-941
	 *
	 * @return string
	 *
	 */
	public function filter_pmc_feed_legacy_format_primary_vertical( $vertical, $post ) {

		if ( class_exists( '\PMC_Vertical' ) ) {
			$primary_vertical = \PMC_Vertical::get_instance()->primary_vertical( $post->ID );
			if ( ! empty( $primary_vertical ) ) {
				$vertical = $primary_vertical->name;
			}
		}
		return $vertical;
	}

	/**
	 * Render the content:encoded <article> section for facebook instant articles section
	 *
	 * @since 2015-12-07
	 * @version 2015-12-07 Archana Mandhare PMCVIP-411
	 *
	 * @param $content string
	 * @param $feed_post object
	 *
	 * Requires complete unit test for this filter function.
	 *
	 * @return string
	 *
	 */
	public function filter_pmc_custom_feed_facebook_instant_articles_content( $content, $feed_post ) {

		$feed_post = get_post( $feed_post );

		if ( empty( $feed_post ) ) {
			return $content;
		}

		$header_html = '';

		$linked_gallery = ( '' !== get_post_meta( $feed_post->ID, 'pmc-gallery-linked-gallery', true ) ) ? json_decode( get_post_meta( $feed_post->ID, 'pmc-gallery-linked-gallery', true ) ) : '';

		//set up gallery variables.
		if ( ! empty( $linked_gallery ) && ! empty( $linked_gallery->id ) ) {

			$gallery = View::get_instance()->load_gallery( $linked_gallery->id, 0 );

			$post_url = wp_parse_url( get_permalink() );
			if ( isset( $post_url['path'] ) ) {
				$linkback = sprintf( '&ref=%spos=', $post_url['path'] );
			} else {
				$linkback = '';
			}

			if ( ! empty( $gallery ) ) {

				$images_count = $gallery->get_the_count( 'total' ); // outout already escaped
				$permalink    = $gallery->get_the_permalink( true ) . $linkback;
				$header_html .= sprintf( '<p><a href="%1$s">%2$s $3$s<span> Photos</span></a></p>', esc_url( $permalink ), esc_html__( 'View Gallery ', 'pmc-variety' ), $images_count );
				$header_html .= $gallery->get_the_thumbs_html5( 'featured-second', 'filmstrip', 2, 'thumb', null, null ); // output already escaped

			}
		}

		if ( ! empty( $header_html ) ) {
			$content = $header_html . $content;
		}

		$after_content = '';

		// render variety review cast & credit
		$vertical = \PMC_Vertical::get_instance()->primary_vertical( $feed_post->ID );
		if ( has_category( 'reviews', $feed_post->ID ) ) {

			switch ( $vertical->slug ) {
				case 'film':
					$render_options = array(
						'title'            => true,
						'origin'           => true,
						'primary-credit'   => __( 'Production', 'pmc-variety' ),
						'secondary-credit' => __( 'Crew', 'pmc-variety' ),
						'cast'             => __( 'With', 'pmc-variety' ),
					);
					break;
				case 'tv':
					$render_options = array(
						'title'            => true,
						'origin'           => true,
						'primary-credit'   => __( 'Production', 'pmc-variety' ),
						'secondary-credit' => __( 'Crew', 'pmc-variety' ),
						'cast'             => __( 'Cast', 'pmc-variety' ),
					);
					break;
				case 'legit':
					$render_options = array(
						'title'            => true,
						'origin'           => true,
						'primary-credit'   => __( 'Production', 'pmc-variety' ),
						'secondary-credit' => __( 'Creative', 'pmc-variety' ),
						'cast'             => __( 'Cast', 'pmc-variety' ),
					);
					break;
				default:
					$render_options = array(
						'title'            => true,
						'origin'           => true,
						'primary-credit'   => __( 'Production', 'pmc-variety' ),
						'secondary-credit' => __( 'Crew', 'pmc-variety' ),
						'cast'             => __( 'Cast', 'pmc-variety' ),
					);
					break;
			}

			// need to buffer the data output to control $title rendering...
			$out_bufs = '';
			if ( $render_options['origin'] ) {
				// we do not want to do any escape, the contents should be raw html entered
				$bufs = wp_kses_post( get_post_meta( $feed_post->ID, 'variety-review-origin', true ) );
				if ( ! empty( $bufs ) ) {
					$out_bufs .= sprintf( "<p>%s</p>\n", $bufs );
				}
			}

			if ( isset( $render_options['primary-credit'] ) ) {
				$bufs = wp_kses_post( get_post_meta( $feed_post->ID, 'variety-primary-credit', true ) );
				if ( ! empty( $bufs ) ) {
					if ( ! empty( $render_options['primary-credit'] ) ) {
						$out_bufs .= sprintf( '<h2>%s</h2>', esc_html( $render_options['primary-credit'] ) );
					}
					$out_bufs .= sprintf( "<p>%</p>\n", $bufs );
				}
			}

			if ( isset( $render_options['secondary-credit'] ) ) {
				$bufs = wp_kses_post( get_post_meta( $feed_post->ID, 'variety-secondary-credit', true ) );
				if ( ! empty( $bufs ) ) {
					if ( ! empty( $render_options['secondary-credit'] ) ) {
						$out_bufs .= sprintf( '<h2>%s</h2>', esc_html( $render_options['secondary-credit'] ) );
					}
					$out_bufs .= sprintf( "<p>%s</p>\n", $bufs );
				}
			}

			if ( isset( $render_options['cast'] ) ) {
				$bufs1 = wp_kses_post( get_post_meta( $feed_post->ID, 'variety-primary-cast', true ) );
				$bufs2 = wp_kses_post( get_post_meta( $feed_post->ID, 'variety-secondary-cast', true ) );
				if ( ! empty( $bufs1 ) || ! empty( $bufs2 ) ) {
					if ( ! empty( $render_options['cast'] ) ) {
						$out_bufs .= sprintf( '<h2>%s</h2>\n', esc_html( $render_options['cast'] ) );
					}
				}
				if ( ! empty( $bufs1 ) ) {
					if ( 'with' === strtolower( $render_options['cast'] ) ) {
						$bufs1 = preg_replace( '/^\\s*(<b>)?\\s*With:\\s*(<\\/b>)?\\s*/i', '', $bufs1 );
					}
					$out_bufs .= sprintf( "<p>%s</p>\n",$bufs1 );
				}
				if ( ! empty( $bufs2 ) ) {
					$out_bufs .= "<p>{$bufs2}</p>\n";
				}
			}
			if ( ! empty( $out_bufs ) ) {
				if ( ! empty( $render_options['title'] ) ) {
					// we need the raw title from post, as get_the_title() would have filter apply and adjust the post title
					$after_content .= sprintf( '<h2>%s</h2>', esc_html( $feed_post->post_title ) );
				} // if

				// Filter the crew credits with SEO Auto Links
				if ( class_exists( '\SEO_Auto_Linker_Front' ) ) {
					$output_content = apply_filters( 'seoal_post_replace', \SEO_Auto_Linker_Front::content( $out_bufs ) );
					$after_content .= wp_kses_post( $output_content );
				} else {
					$after_content .= "{$out_bufs}";
				}
			}
		}

		// See also link
		$see_also_url = \Variety_See_Also_Links::get_instance()->url();
		if ( ! empty( $see_also_url ) ) {
			$after_content .= sprintf( '<p class="related-links"><span>%1$s</span><a href="%2$s">%3$s</a></p>', esc_html__( 'SEE ALSO:', 'pmc-variety' ), esc_url( $see_also_url ), esc_html( \Variety_See_Also_Links::get_instance()->title() ) );
		}

		$content = $content . $after_content;

		return $content;

	}

	/**
	 * Render the variety review meta. i.e. meta from Variety Review Credits.
	 * TODO: Address the test cases for this.
	 *
	 * @since 2018-10-25
	 *
	 * @param $content   string Post Content
	 * @param $feed_post object Feed Post object
	 *
	 * @return string
	 *
	 *
	 */
	public function filter_pmc_custom_feed_variety_review_meta( $content, $feed_post ) {

		// Bail out if it's not a review.
		if ( ! has_category( 'reviews', $feed_post->ID ) ) {
			return $content;
		}

		$render_options = array(
			'primary-credit'   => __( 'Production', 'pmc-variety' ),
			'secondary-credit' => __( 'Crew', 'pmc-variety' ),
			'cast'             => __( 'With', 'pmc-variety' ),
			'music-by'         => __( 'Music By', 'pmc-variety' ),
			'mpaa-rating'      => __( 'MPAA Rating', 'pmc-variety' ),
			'running-time'     => __( 'Running Time', 'pmc-variety' ),
		);

		// need to buffer the data output to control $title rendering...
		$out_bufs = '';

		// Get origin meta.
		// we do not want to do any escape, the contents should be raw html entered
		$meta_origin = wp_kses_post( get_post_meta( $feed_post->ID, 'variety-review-origin', true ) );
		if ( ! empty( $meta_origin ) ) {
			$out_bufs .= sprintf( "<p>%s</p>\n", $meta_origin );
		}

		// Get running time and mpaa ratings.
		$meta_running_time = wp_kses_post( get_post_meta( $feed_post->ID, 'variety-review-running-time', true ) );
		$meta_mpaa_rating  = wp_kses_post( get_post_meta( $feed_post->ID, 'variety-review-mpaa-rating', true ) );

		if ( ! empty( $meta_running_time ) && ! empty( $render_options['running-time'] ) ) {
			$out_bufs .= sprintf( '<p>%1$s: %2$s</p>', esc_html( $render_options['running-time'] ), esc_html( $meta_running_time ) );
		}

		if ( ! empty( $render_options['mpaa-rating'] ) && ! empty( $meta_mpaa_rating ) && 'na' !== $meta_mpaa_rating ) {
			$out_bufs .= sprintf( '<p>%1$s: %2$s</p>', esc_html( $render_options['mpaa-rating'] ), esc_html( $meta_mpaa_rating ) );
		}

		// Get primary credit meta.
		$meta_primary_credit = wp_kses_post( get_post_meta( $feed_post->ID, 'variety-primary-credit', true ) );

		if ( ! empty( $meta_primary_credit ) && ! empty( $render_options['primary-credit'] ) ) {
			$out_bufs .= sprintf( '<h4>%s</h4>', esc_html( $render_options['primary-credit'] ) );
			$out_bufs .= sprintf( "<p>%s</p>\n", $meta_primary_credit );
		}

		// Get music by meta.
		$meta_music_by = wp_kses_post( get_post_meta( $feed_post->ID, 'variety-music-by', true ) );

		if ( ! empty( $meta_music_by ) && ! empty( $render_options['music-by'] ) ) {
			$out_bufs .= sprintf( '<h4>%s</h4>', esc_html( $render_options['music-by'] ) );
			$out_bufs .= sprintf( "<p>%s</p>\n", $meta_music_by );
		}

		// Get secondary credit meta.
		$meta_secondary_credit = wp_kses_post( get_post_meta( $feed_post->ID, 'variety-secondary-credit', true ) );

		if ( ! empty( $meta_secondary_credit ) && ! empty( $render_options['secondary-credit'] ) ) {
			$out_bufs .= sprintf( '<h4>%s</h4>', esc_html( $render_options['secondary-credit'] ) );
			$out_bufs .= sprintf( "<p>%s</p>\n", $meta_secondary_credit );
		}

		$meta_primary_cast   = wp_kses_post( get_post_meta( $feed_post->ID, 'variety-primary-cast', true ) );
		$meta_secondary_cast = wp_kses_post( get_post_meta( $feed_post->ID, 'variety-secondary-cast', true ) );

		if ( ! empty( $meta_primary_cast ) || ! empty( $meta_secondary_cast ) ) {
			if ( ! empty( $render_options['cast'] ) ) {
				$out_bufs .= sprintf( "<h4>%s</h4>\n", esc_html( $render_options['cast'] ) );
			}
		}

		if ( ! empty( $meta_primary_cast ) ) {
			if ( 'with' === strtolower( $render_options['cast'] ) ) {

				// Remove the title/label ( 'With:' ) if its already included in the content.
				$meta_primary_cast = preg_replace( '/^\\s*(<b>)?\\s*With:\\s*(<\\/b>)?\\s*/i', '', $meta_primary_cast );
			}
			$out_bufs .= sprintf( "<p>%s</p>\n", $meta_primary_cast );
		}

		if ( ! empty( $meta_secondary_cast ) ) {
			$meta_secondary_cast .= "<p>{$meta_secondary_cast}</p>\n";
		}

		if ( ! empty( $out_bufs ) ) {
			$content .= $out_bufs;
		}

		return $content;

	}

	/**
	 * Return author data for facebook instant articles header section
	 *
	 * @since 2015-12-14
	 * @version 2015-12-14 Archana Mandhare PMCVIP-411
	 *
	 * @param $feed_authors array of objects
	 *
	 * @return array of Authors array
	 *
	 */
	public function filter_pmc_custom_feed_facebook_instant_articles_authors( $feed_authors ) {

		/**
		 * @todo variety_fetch_author function does not exists in this theme. It exist in pmc-variety-2014 theme.
		 */
		$authors_data = variety_fetch_author( true );

		if ( ! empty( $authors_data ) ) {

			// check that we have an array of authors or just one author data
			if ( 1 === count( $authors_data ) && ! is_array( $authors_data[0] ) ) {
				$authors = array( $authors_data );
			} else {
				$authors = $authors_data;
			}

			foreach ( $authors as $author ) {

				$feed_author['user_url'] = $author['url'];
				$feed_author['display_name']  = $author['name'];
				$feed_author['title']  = $author['title'];
				$feed_author['twitter']  = $author['twitter'];
				$variety_authors[] = (object) $feed_author;

			}

			return $variety_authors;
		}

		return $feed_authors;

	}

	/**
	 * Render sub title for facebook instant articles header section
	 *
	 * @since 2015-12-14
	 * @version 2015-12-14 Archana Mandhare PMCVIP-411
	 *
	 * @param $sub_title string
	 * @param $curr_post int | object
	 *
	 * @return string
	 *
	 */
	public function filter_pmc_custom_feed_facebook_instant_articles_sub_title( $sub_title, $curr_post ) {

		$curr_post = get_post( $curr_post );

		if ( ! empty( $curr_post ) ) {

			$sub_heading = trim( get_post_meta( $curr_post->ID, '_variety-sub-heading', true ) );

			if ( ! empty( $sub_heading ) ) {
				return $sub_heading;
			}
		}

		return $sub_title;
	}

	/**
	 * Override default tracking image for custom feeds
	 *
	 * @ticket PPT-2833
	 * @since 2014-07-18 Amit Gupta
	 */
	public function pmc_custom_feed_tracking_images( $tracking_images ) {
		return [
			'https://sb.scorecardresearch.com/p?c1=7&c2=6035310&c3=10000&cv=2.0&cj=1',
		];
	}

	/**
	 * Add custom feed options specific to Variety & Variety Latino
	 *
	 * @ticket PPT-4362
	 * @since 2015-03-12 Amit Gupta
	 */
	public function add_custom_options() {
		\PMC_Custom_Feed::get_instance()->add_taxonomy_term_if_not_exist( array(
			'reuters-curated-posts' => 'Reuters: Curated Posts',
		) );
	}

	/**
	 * This function is hooked into 'pmc_custom_feed_posts_filter' to fetch
	 * some extra posts if curated posts have been found for Reuters feed.
	 * This is not meant to be called directly.
	 *
	 * @ticket PPT-4362
	 * @since 2015-03-18 Amit Gupta
	 */
	public function filter_reuters_post_count( $args, $config ) {
		// do we have curated posts?
		if ( ! empty( $this->_curated_post_count ) ) {
			if ( $this->_curated_post_count < $args['numberposts'] ) {
				// fetch extra posts to cover duplicates
				$args['numberposts'] += $this->_curated_post_count;
			} else {
				// we already have enough curated posts
				$args['numberposts'] = 0;
			}
		}

		return $args;
	}

	/**
	 * Override popular posts in reuters multimedia feed with posts in featured
	 * and second stage carousel
	 *
	 * @ticket PPT-4362
	 * @since 2015-03-11 Amit Gupta
	 * @version 2015-03-18 Amit Gupta - added normal feed posts which are appended to curated posts
	 */
	public function pmc_custom_feed_reuters_curated_posts( $posts = array(), $feed = '', $feed_options = array() ) {
		$curated_post_limit = 5;

		$carousel_options = array(
			'add_filler' => false,
		);

		/*
		 * So that we fetch all posts from carousels
		 * as some might get excluded if they're flagged as
		 * inappropriate for syndication
		 */
		$posts_to_fetch = 50;

		$posts_limit = ( ! empty( $feed_options['count'] ) ) ? intval( $feed_options['count'] ) : $posts_to_fetch;

		$post_ids = array();

		/*
		 * Grab posts from Featured Carousel without any filler posts
		 */
		$featured_carousel = pmc_render_carousel( \PMC_Carousel::modules_taxonomy_name, 'featured-carousel', $posts_to_fetch, '', $carousel_options );

		if ( ! empty( $featured_carousel ) && is_array( $featured_carousel ) && count( $featured_carousel ) > 0 ) {
			$post_ids = array_merge( $post_ids, array_values( array_filter( wp_list_pluck( $featured_carousel, 'ID' ) ) ) );
		}

		/*
		 * Grab posts from Second Stage Carousel without any filler posts
		 */
		$second_stage_carousel = pmc_render_carousel( \PMC_Carousel::modules_taxonomy_name, 'second-stage', $posts_to_fetch, '', $carousel_options );

		if ( ! empty( $second_stage_carousel ) && is_array( $second_stage_carousel ) && count( $second_stage_carousel ) > 0 ) {
			$post_ids = array_merge( $post_ids, array_values( array_filter( wp_list_pluck( $second_stage_carousel, 'ID' ) ) ) );
		}

		if ( empty( $post_ids ) || count( $post_ids ) < 1 ) {
			//nothing in carousels, bail out
			return $posts;
		}

		/*
		 * Time to grab post objects of the posts we got from both carousels
		 */
		$carousel_posts = get_posts( array(
			'post_type'           => 'post',
			'post__in'            => $post_ids,
			'numberposts'         => $posts_to_fetch,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'suppress_filters'    => false,
			'date_query'          => array(
				/*
				 * make sure posts are from last N days only as set in
				 * reuters feed class
				 */
				array(
					'column' => 'post_date_gmt',
					'after'  => sprintf( '%d days ago', \PMC_Custom_Feed_Reuters::IMAGE_THRESHOLD_IN_DAYS ),
				),
			),
		) );

		$curated_posts = array();

		if ( ! empty( $carousel_posts ) ) {
			$curated_posts = array_slice( $carousel_posts, 0, $curated_post_limit );
		}

		if ( ! empty( $curated_posts ) ) {
			$order = 1;

			foreach ( $curated_posts as $key => $post ) {
				$curated_posts[ $key ]->order = $order;
				$order++;
			}

			$this->_curated_post_count = count( $curated_posts );

			if ( $this->_curated_post_count >= $posts_limit ) {
				return $curated_posts;
			}

			$posts = $curated_posts;
		}

		add_filter( 'pmc_custom_feed_posts_filter', array( $this, 'filter_reuters_post_count' ), 10, 2 );

		$regular_posts = \PMC_Custom_Feed_Helper::pmc_feed_get_posts( $feed );

		remove_filter( 'pmc_custom_feed_posts_filter', array( $this, 'filter_reuters_post_count' ) );

		if ( ! empty( $regular_posts ) && is_array( $regular_posts ) ) {
			$curated_posts_ids = wp_list_pluck( $curated_posts, 'ID' );

			foreach ( $regular_posts as $regular_post ) {
				if ( in_array( $regular_post->ID, $curated_posts_ids, true ) ) {
					continue;
				}

				$posts[] = $regular_post;
			}
		}

		$posts = array_slice( $posts, 0, $posts_limit );

		unset( $curated_posts, $carousel_posts, $excluded_posts, $second_stage_carousel, $featured_carousel, $post_ids );

		return $posts;
	}

	/**
	 * If content is by ispot.tv then exclude it from the feed
	 *
	 * @since 2016-01-04
	 * @version 2016-01-04 Archana Mandhare PMCVIP-678
	 *
	 * @param $appropriate bool
	 * @param $post object
	 *
	 * @return bool
	 *
	 */
	public function filter_pmc_custom_feed_facebook_instant_articles_inappropriate_content( $appropriate, $post ) {
		/**
		 * @todo variety_fetch_author function does not exists in this theme. It exist in pmc-variety-2014 theme.
		 */
		$authors = variety_fetch_author( true );

		$appropriate = ( empty( $authors ) ) ? true : $appropriate;
		if ( $appropriate ) {
			return true;
		}

		foreach ( $authors as $author ) {
			if ( ! empty( $author['name'] ) && 'ispot.tv' === strtolower( $author['name'] ) ) {
				return true;
			}
		}

		return $appropriate;

	}

	/**
	 * This sets the value for custom <slug> tag for Reuters feed
	 *
	 * @since ?
	 * @version 2017-09-27 Amit Gupta - ported over from pmc-variety-2014
	 */
	public function set_reuters_custom_feed_item_slug( $slug, $post = false ) {

		if ( ! empty( $post ) ) {

			$vertical = PMC_Vertical::get_instance()->primary_vertical( $post, false );

			$category = variety_get_main_category( $post );

			$vertical = ( ! empty( $vertical ) && ! empty( $vertical->slug ) ) ? strtoupper( $vertical->slug ) : 'MORE';
			$category = ( ! empty( $category ) && ! empty( $category->slug ) ) ? strtoupper( $category->slug ) : 'NEWS';

			$slug = sprintf( 'VARIETY-ENTERTAINMENT-%s/%s', esc_html( $vertical ), esc_html( $category ) );

		}

		return $slug;

	}

} //end of class

//EOF
