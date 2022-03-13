<?php
/*
Plugin Name: PMC Page Meta
Description: Provides an interface for accessing common, unified attributes about a page.
Version: 1.0
Author: PMC, Corey Gilmore
License: PMC Proprietary.  All rights reserved.
*/

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

use PMC\Global_Functions\Traits\Singleton;

class PMC_Page_Meta {

	use Singleton;

	protected static $meta = false;

	protected function __construct() {
		add_action( 'wp', array( $this, 'add_pmc_meta_js' ) ); // do NOT change the hook

		// Do not change this -- see the function docblock
		add_action( 'wp_enqueue_scripts', array( $this, 'trigger_pmc_page_meta_enqueue_scripts' ), 10 );
	}

	/**
	 * Add a pmc_meta JS object as high in the head as we can, for use by GTM, GA, and other scripts.
	 * We must use the `wp` hook because `get_page_meta()` relies on WP is_* conditional tags.
	 *
	 * To use the `pmc_meta` JS object, hook into the `pmc_enqueue_scripts_using_pmc_page_meta` actoin
	 *
	 * @since 2015-07-01 Corey Gilmore
	 *
	 * @uses action::wp
	 * @uses action::wp_enqueue_scripts:5
	 * @uses PMC_Scripts::add_script()
	 *
	 * @version 2015-07-01 Corey Gilmore
	 *
	 */
	public static function add_pmc_meta_js() {
		$meta = self::get_page_meta();
		PMC_Scripts::add_script( 'pmc_meta', $meta, 'wp_enqueue_scripts', 5 );
	}

	/**
	 * Fires the `pmc_enqueue_scripts_using_pmc_page_meta` action which should be used by any
	 * scripts that depend on the `pmc_meta` JS object which is output in `PMC_Page_Meta::add_pmc_meta_js()`
	 *
	 * @since 2015-07-01 Corey Gilmore
	 *
	 * @see PMC_Page_Meta::add_pmc_meta_js()
	 * @uses action::wp_enqueue_scripts:10
	 *
	 * @version 2015-07-01 Corey Gilmore Initial version
	 *
	 */
	public static function trigger_pmc_page_meta_enqueue_scripts() {
		$meta = self::get_page_meta();

		/**
		 * Use this hook to safely enqueue any scripts that depend on our `pmc_meta` JS object.
		 *
		 * @since 2015-07-03 Corey Gilmore
		 * @see PPT-5136
		 *
		 * @version 2015-07-01 Corey Gilmore Initial version
		 *
		 * @param array $meta PMC_Page_Meta::$meta, passed for consistency.
		 */
		do_action( 'pmc_enqueue_scripts_using_pmc_page_meta', $meta );
	}

	/**
	 * Compiles page data from meta
	 *
	 * @return array
	 */
	public static function get_page_data() {
		$meta = self::get_page_meta();

		return [
			'type'     => ( ! empty( $meta['page-type'] ) ) ? $meta['page-type'] : '',
			'loggedIn' => ( ! empty( $meta['logged-in'] ) && 'yes' === $meta['logged-in'] ) ? true : false,
		];
	}

	/**
	 * Compiles article data from meta
	 *
	 * @return array
	 */
	public static function get_article_data() {
		$meta = self::get_page_meta();

		$article_data = [];
		
		if ( is_single() ) {
		
			$categories      = ( ! empty( $meta['category'] ) ) ? $meta['category'] : [];
			$tags            = ( ! empty( $meta['tag'] ) ) ? $meta['tag'] : [];
			$verticals       = ( ! empty( $meta['vertical'] ) ) ? $meta['vertical'] : [];
			$page_level_data = array_values( array_unique( array_merge( $verticals, $categories, $tags ) ) );
		
			$article_data = [
				'id'              => (string) get_the_ID(),
				'title'           => get_the_title(),
				'authors'         => ( ! empty( $meta['author'] ) ) ? $meta['author'] : [],
				'section'         => ( ! empty( $meta['primary-category'] ) ) ? $meta['primary-category'] : '',
				'publishedAt'     => get_post_time( 'c', true ),
				'keywords'        => $tags,
				'categories'      => $categories,
				'verticals'       => $verticals,
				'pageLevelData'   => $page_level_data,
				'pageAccessLevel' => ( ! empty( $meta['page_access_level'] ) ) ? $meta['page_access_level'] : '',
			];

		}

		return $article_data;
	}

	/**
	 * Get meta data for the current post.
	 *
	 * @param bool $bypass_cache Optionally bypass the static cache.
	 *                           Used during unit testing.
	 *                           Defaults to `false`.
	 *
	 * @return bool|mixed|void
	 */
	public static function get_page_meta( bool $bypass_cache = false ) {
		if( is_feed() ) {
			return null;
		}

		// Allow bypassing static cache below
		// Used during unit testing
		$bypass_cache = apply_filters( 'pmc_page_meta_get_page_meta_bypass_cache', $bypass_cache );

		if ( ! empty( static::$meta ) && ! $bypass_cache ) {
			return static::$meta;
		}

		$lob  = defined( 'PMC_SITE_NAME' ) ? PMC_SITE_NAME : '';
		$meta = array(
			'lob'              => $lob,
			'lob_genre'        => self::get_lob_genre( $lob ),
			'page-type'        => '', // Home, Gallery, post, tag, etc.
			'env'              => '', // desktop/mobile/tablet/unknown
			'primary-category' => '',
			'primary-vertical' => '',
			'vertical'         => '',
			'category'         => '',
			'tag'              => '',
			'author'           => '',
			'logged-in'        => '',
			'subscriber-type'  => '',
		);

		$meta['page-type'] = PMC::get_pagezone();

		if( PMC::is_desktop() ) {
			$meta['env'] = 'desktop';
		} else if( PMC::is_mobile() ) {
			$meta['env'] = 'mobile';
		} else if( PMC::is_tablet() ) {
			$meta['env'] = 'tablet';
		} else {
			$meta['env'] = 'unknown';
		}

		// Add the country code, if it is known
		if( function_exists( 'pmc_geo_get_user_location' ) ) {
			$geo = pmc_geo_get_user_location();
			// Can also be 'default'
			if( empty( $geo ) ) {
				$geo = 'unknown';
			}
		} else {
			$geo = 'unknown';
		}
		$meta['country'] = $geo;

		// Singular-specific fields like authors and taxonomies
		if( is_singular() ) {
			$post_type = get_post_type();
			$single_post = get_post();
			$object_taxonomies = get_object_taxonomies( $post_type );

			// Verticals
			if( taxonomy_exists( 'vertical' ) ) {
				$primary_vertical = false;

				if( class_exists( 'PMC_Vertical' ) ) {
					$primary_vertical = PMC_Vertical::get_instance()->primary_vertical( $single_post );
					$verticals = PMC_Vertical::get_instance()->get_post_terms( $single_post );
				} elseif( class_exists( 'PMC_Primary_Taxonomy' ) ) {
					$primary_vertical = PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $single_post->ID, 'vertical' );
					$verticals = get_the_terms( $single_post->ID, 'vertical' );
				} elseif ( function_exists( 'wwd_get_the_primary_term' ) ) {
					$primary_vertical = wwd_get_the_primary_term( 'vertical', $single_post->ID );

					if ( ! empty( $primary_vertical ) ) {
						$verticals = get_the_terms( $single_post->ID, 'vertical' );
					} elseif ( class_exists( 'PMC_Gallery_Defaults' ) ) {
						// linked gallery may inherit article term
						if ( get_post_type( $single_post->ID ) === PMC_Gallery_Defaults::name ) {
							// if it's a linked gallery, let's inherit the linked post vertical
							if ( $article_id = PMC_Gallery_View::get_linked_post_id( $single_post->ID ) ) {
								$primary_vertical = wwd_get_the_primary_term( 'vertical', $article_id );
								$verticals = get_the_terms( $article_id, 'vertical' );
							}
						}
					}
				}

				if( is_object( $primary_vertical ) && !empty( $primary_vertical->slug ) ) {
					$meta['primary-vertical'] = $primary_vertical->slug;
				}

				if( ! empty( $verticals ) && is_array( $verticals ) ) {
					$meta['vertical'] =  wp_list_pluck( array_values( $verticals ), 'slug' );
				}

			}

			// Categories
			if( in_array( 'category', $object_taxonomies ) ) {
				if( class_exists( 'PMC_Primary_Taxonomy' ) ) {
					$primary_category = PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy( $single_post, 'category');

					if( is_object( $primary_category ) && !empty( $primary_category->name ) ) {
						$meta['primary-category'] = $primary_category->name;
					}
				}

				$categories = get_the_category();
				if( is_array( $categories ) ) {
					$category_names = wp_list_pluck( array_values( $categories ), 'name' );
					$meta['category'] = $category_names;
					$meta['primary-category'] = reset( $category_names );
				}
			}

			// Tags
			if( in_array( 'post_tag', $object_taxonomies ) ) {
				$tags = get_the_tags();
				if( is_array( $tags ) ) {
					$tags = wp_list_pluck( array_values( $tags ), 'name' );
					/**
					 * Work around odd VIP-only (cache-related?) bug
					 *  We should have:
					 *   $tags = array( "Business","Retail","North America", );
					 *
					 *  but fairly often we're getting:
					 *   $tags = array( "124456" => "golden globes","1326537" => "Jeffrey Tambor","52057" => "Transparent", );
					 *
					 * This checks for: $tags is an array, $tags[FIRST] is an two-item array, and the key of $tags[FIRST] is numeric.
					 * If all of those are true, it extracts the values (tag names).
					 *
					 * @author 2015-02-19 Corey Gilmore
					 * @see PPT-4198
					 *
					 */

					if( is_array( $tags ) ) {
						reset( $tags );
						$key = key( $tags ); // grab the first key

						// Check to see if the first element is a two-item array
						if( !empty( $tags[$key] ) && is_array( $tags[$key] ) && count( $tags[$key] ) == 2 ) {

							// Check for a non-zero numeric key -- almost definitely the ID of a tag
							if( is_numeric( $key ) && $key != 0 ) {
								$tags = array_values( $tags );
							}
						}
					}
					// End workaround

					$meta['tag'] = $tags;
				}
			}

			// Authors
			if( function_exists( 'get_coauthors' ) ) {
				// Only expose authors for post types where actually we care who wrote it
				$expose_authors = array( 'post', 'english' );
				$expose_authors = apply_filters( 'pmc_page_meta_expose_authors', $expose_authors );

				if( in_array( $post_type, $expose_authors ) ) {
					$authors = get_coauthors();
					if( is_array( $authors ) ) {
						$meta['author'] = wp_list_pluck( array_values( $authors ), 'display_name' );
					}
				}
			}
		}

		// Add the meta if user is from EU or not
		$meta['is_eu'] = ( 'eu' === \PMC\Geo_Uniques\Plugin::get_instance()->pmc_geo_get_region_code() );

		$meta = apply_filters( 'pmc_page_meta', $meta );

		static::$meta = $meta;
		return $meta;
	}

	/**
	 * Return the lob site genre, can be overridden by theme via filter pmc_lob_genre
	 * @param $lob
	 * @return mixed|void
	 */
	public static function get_lob_genre( $lob ) {
		$genre    = 'Entertainment';
		$mappings = [
			'artnews'         => 'Lifestyle',
			'billboard'       => 'Music',
			'blogher'         => 'Lifestyle',
			'deadline'        => 'Entertainment',
			'dirt'            => 'Lifestyle',
			'goldderby'       => 'Entertainment',
			'footwearnews'    => 'Fashion',
			'indiewire'       => 'Entertainment',
			'rollingstone'    => 'Music',
			'robbreport'      => 'Lifestyle',
			'sheknows'        => 'Lifestyle',
			'soaps'           => 'Entertainment',
			'sourcingjournal' => 'Fashion',
			'sportico'        => 'Sports',
			'spy'             => 'Lifestyle',
			'stylecaster'     => 'Lifestyle',
			'thr'             => 'Entertainment',
			'tvline'          => 'Entertainment',
			'variety'         => 'Entertainment',
			'vibe'            => 'Music',
			'wwd'             => 'Fashion',
			'bgr'             => 'Lifestyle',
		];
		if ( isset( $mappings[ $lob ] ) ) {
			$genre = $mappings[ $lob ];
		}
		return apply_filters( 'pmc_lob_genre', $genre, $lob );
	}
}

PMC_Page_Meta::get_instance();

// EOF
