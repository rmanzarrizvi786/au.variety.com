<?php
/**
 * This class adds functionality to admin post options exclusions.
 */

namespace PMC\Post_Options;

use \PMC\Global_Functions\Traits\Singleton;
use PMC\Post_Options\Taxonomy;
use PMC\Unit_Test\Mock\WP_Query;

class Exclude_Posts {

	use Singleton;

	const HP_EXCLUSION_SLUG  = 'exclude-from-homepage';
	const HPR_EXCLUSION_SLUG = 'exclude-from-homepage-river';
	const SF_EXCLUSION_SLUG  = 'exclude-from-section-fronts';

	const CACHE_GROUP = 'pmc_post_options';
	const CACHE_LIFE  = 15 * MINUTE_IN_SECONDS; // 15 mins

	/**
	 * @var Array Array containing nonce action and name
	 */
	protected $_nonce = [
		'action' => 'pmc_exclude_posts',
		'name'   => '_pmc_exclude_post_options',
	];

	protected function __construct() {

		add_action( 'pre_get_posts', [ $this, 'exclude_posts' ] );
		add_action( 'set_object_terms', [ $this, 'invalidate_cache_on_unchecked_post_options' ], 10, 6 );
		add_action( 'save_post', [ $this, 'invalidate_cache_on_post_options' ], 10, 2 );
		add_action( 'post_submitbox_start', [ $this, 'action_post_submitbox_start' ], 10, 0 );
		add_action( 'quick_edit_custom_box', [ $this, 'action_post_submitbox_start' ], 10, 2 );

		add_filter( 'pmc_core_top_posts', [ $this, 'filter_pmc_core_top_posts' ], 11, 1 );
		add_filter( 'pmc_google_analytics_bridge_trending_ids', [ $this, 'filter_pmc_google_analytics_bridge_trending_ids' ] );

	}

	/**
	 * Adding WP Nonce to post edit page
	 *
	 */
	public function action_post_submitbox_start() {
		wp_nonce_field( $this->_nonce['action'], $this->_nonce['name'] );
	}

	/**
	 * Updating query to exclude posts
	 *
	 * @param \WP_Query $query
	 * @throws \ErrorException
	 */
	public function exclude_posts( \WP_Query $query ) {

		// We want to be sure its main query. We don't want exclusion to run
		// on all WP_Query objects on the page because that would mess things up.
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! $query->is_home() && ! $query->is_archive() ) {
			return;
		}

		$term_slugs = $query->is_home() ? [ self::HP_EXCLUSION_SLUG, self::HPR_EXCLUSION_SLUG ] : [ self::SF_EXCLUSION_SLUG ];

		$excluded_post_ids = $this->get_excluded_posts( $term_slugs );

		// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams
		$post__not_in = $query->get( 'post__not_in' );

		if ( ! empty( $post__not_in ) ) {
			$excluded_post_ids = array_merge( (array) $post__not_in, $excluded_post_ids );
		}
		
		$excluded_post_ids = array_unique( (array) $excluded_post_ids );

		// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams
		$query->set( 'post__not_in', $excluded_post_ids );

	}

	/**
	 * Get excluded cached posts or uncached if expired
	 *
	 * @param $term_slugs array
	 * @return array
	 * @throws \ErrorException
	 */
	public function get_excluded_posts( $term_slugs ) : array {

		if ( empty( $term_slugs ) || ! is_array( $term_slugs ) ) {
			return [];
		}

		$cache_key = implode( '-', $term_slugs );

		$pmc_cache = new \PMC_Cache( $cache_key, self::CACHE_GROUP );

		$excluded_posts = $pmc_cache->expires_in( self::CACHE_LIFE )
			->updates_with( [ $this, 'get_uncached_excluded_posts' ], [ $term_slugs ] )
			->get();
		
		// This is because boolean value or WP_Error does not have to get merged
		// with the array that is going to be set in 'post__not_in'.
		$excluded_posts = ( ! is_array( $excluded_posts ) || is_wp_error( $excluded_posts ) ) ? [] : $excluded_posts;

		/**
		 * Exclude from the home query river (page of posts) as well as Section Front.
		 * This was added as one requirement in BR-1314
		 * It can be used to exclude the posts from Home Page River as well as Section Front dynamically.
		 * 
		 * IMPORTANT: Do look at the conditions of the exclude_posts function before you use this filter.
		 *            Check the IF conditions.
		 *
		 * @param array $excluded_post_ids
		 */
		
		return array_unique( (array) apply_filters( 'pmc_exclude_posts', $excluded_posts, 'river' ) );

	}

	/**
	 * Fetch uncached excluded posts
	 *
	 * @param $term_slugs
	 * @return array
	 */
	public function get_uncached_excluded_posts( $term_slugs ) {

		$allowed_post_types = Taxonomy::get_instance()->get_post_types();

		$args = [
			'fields'           => 'ids',
			'posts_per_page'   => 100,
			'suppress_filters' => false,
			'post_type'        => $allowed_post_types,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'tax_query'        => [
				[
					'taxonomy' => Taxonomy::NAME,
					'field'    => 'slug',
					'terms'    => $term_slugs,
				],
			],
		];

		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
		return get_posts( $args );

	}


	/**
	 * Method to compare post-options on change and bust cache if unchecked
	 *
	 * @param $object_id
	 * @param $terms
	 * @param $tt_ids
	 * @param $taxonomy
	 * @param $append
	 * @param $old_tt_ids
	 *
	 * @throws \ErrorException
	 */
	public function invalidate_cache_on_unchecked_post_options( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
		if ( Taxonomy::NAME !== $taxonomy ) {
			return;
		}

		$removed_tt_ids = ( false === $append ) ? array_diff( $old_tt_ids, $tt_ids ) : [];

		if ( empty( $removed_tt_ids ) ) {
			return;
		}

		$removed_terms = [];
		foreach ( $removed_tt_ids as $term_id ) {
			$term_obj        = get_term( $term_id, Taxonomy::NAME );
			$removed_terms[] = $term_obj->slug;
		}

		foreach ( $removed_terms as $removed_term ) {
			if ( self::SF_EXCLUSION_SLUG === $removed_term ) {
				$pmc_sf_cache = new \PMC_Cache( self::SF_EXCLUSION_SLUG, self::CACHE_GROUP );
				$pmc_sf_cache->invalidate();
			}

			if ( self::HP_EXCLUSION_SLUG === $removed_term || self::HPR_EXCLUSION_SLUG === $removed_term ) {
				$cache_key = implode( '-', [ self::HP_EXCLUSION_SLUG, self::HPR_EXCLUSION_SLUG ] );
				$pmc_cache = new \PMC_Cache( $cache_key, self::CACHE_GROUP );
				$pmc_cache->invalidate();
			}
		}

	}

	/**
	 * Remove posts from cache upon save/update
	 *
	 * @param $post_id
	 * @param $post
	 */
	public function invalidate_cache_on_post_options( $post_id, $post ) {

		if ( empty( $post_id ) ) {
			return;
		}

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if (
			empty( \PMC::filter_input( INPUT_POST, $this->_nonce['name'], FILTER_SANITIZE_STRING ) ) ||
			! wp_verify_nonce( \PMC::filter_input( INPUT_POST, $this->_nonce['name'], FILTER_SANITIZE_STRING ), $this->_nonce['action'] )
		) {
			return;
		}

		$allowed_post_types = Taxonomy::get_instance()->get_post_types();
		$post_type          = $post->post_type;

		if ( 'publish' !== $post->post_status || ! in_array( $post_type, (array) $allowed_post_types, true ) ) {
			return;
		}

		$term_slugs = [ self::HP_EXCLUSION_SLUG, self::HPR_EXCLUSION_SLUG ];
		$cache_key  = implode( '-', $term_slugs );

		if ( has_term( $term_slugs, Taxonomy::NAME, $post ) ) {
			$pmc_hp_cache = new \PMC_Cache( $cache_key, self::CACHE_GROUP );
			$pmc_hp_cache->invalidate();
		}

		if ( has_term( self::SF_EXCLUSION_SLUG, Taxonomy::NAME, $post ) ) {
			$pmc_sf_cache = new \PMC_Cache( self::SF_EXCLUSION_SLUG, self::CACHE_GROUP );
			$pmc_sf_cache->invalidate();
		}

	}

	/**
	 * Excludes specific post options before returning top posts from cache
	 *
	 * @param array $posts
	 * @return array
	 */
	public function filter_pmc_core_top_posts( $posts = [] ) : array {
		$filtered_posts = [];

		foreach ( $posts as $post ) {

			$exclude = $this->is_post_excluded( $post['post_id'] );

			if ( true === $exclude ) {
				continue;
			}

			$filtered_posts[] = $post;
		}

		return $filtered_posts;
	}

	/**
	 * Excludes specific post options before returning trending posts from ga
	 *
	 * @param array $post_ids
	 * @return array
	 */
	public function filter_pmc_google_analytics_bridge_trending_ids( $post_ids = [] ) : array {
		$filtered_posts = [];

		foreach ( $post_ids as $post_id ) {

			$exclude = $this->is_post_excluded( $post_id );

			if ( true === $exclude ) {
				continue;
			}

			$filtered_posts[] = $post_id;
		}

		return $filtered_posts;
	}

	/**
	 * Determine whether post needs to be excluded
	 * @param $post_id
	 * @return bool
	 */
	public function is_post_excluded( $post_id ) : bool {
		$exclude = false;

		if ( ( is_home() || is_front_page() ) && has_term( [ self::HP_EXCLUSION_SLUG, self::HPR_EXCLUSION_SLUG ], Taxonomy::NAME, $post_id ) ) {
			$exclude = true;
		}

		if ( is_archive() && has_term( self::SF_EXCLUSION_SLUG, Taxonomy::NAME, $post_id ) ) {
			$exclude = true;
		}

		return $exclude;
	}

}
