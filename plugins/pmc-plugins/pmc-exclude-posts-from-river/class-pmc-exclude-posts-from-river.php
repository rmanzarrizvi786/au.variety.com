<?php

/**
 * Plugin class for PMC Exclude Posts From River plugin
 *
 * @author Amit Gupta <agupta@pmc.com>
 * @since 2014-09-05
 * @version 2014-10-31 Amit Gupta - replaced meta-query for post exclusion with array of excluded post IDs passed to 'post__not_in' flag
 * @version 2014-11-20 Amit Gupta - added cache rebuild on 'save_post' hook to remove (automatic cache expiry) wait time for new posts
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Exclude_Posts_From_River {

	use Singleton;

	/**
	 * @var String Key name for the toggle flag
	 */
	const FLAG = '_exclude_post_from_river';

	/**
	 * @var String Unique plugin ID, ideal for use as cache key as well
	 */
	const PLUGIN_ID = 'pmc-exclude-post-from-river';

	/**
	 * @var integer Maximum number of posts to exclude
	 */
	const EXCLUSION_LIMIT = 100;

	/**
	 * @var int Cache life in seconds
	 */
	const CACHE_LIFE = 1800; // 30 minutes

	/**
	 * @var int Filtered cache life in seconds
	 */
	protected $_cache_life = 0;

	/**
	 * @var int returns filtered cache life.
	 */
	protected function _get_cache_life() : int {

		if ( empty( $this->_cache_life ) ) {
			$this->_cache_life = apply_filters( 'pmc_exclude_posts_from_river_cache_life', self::CACHE_LIFE );
			$this->_cache_life = absint( $this->_cache_life );
		}

		return $this->_cache_life;

	}


	/**
	 * @var Array Array containing nonce action and name
	 */
	protected $_nonce = array(
		'action' => 'pmc_exclude_posts_from_river',
		'name' => 'pmc_epfr_nonce',
	);


	/**
	 * Class initialization routine
	 *
	 * @return void
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}


	/**
	 * Setup hooks and listeners
	 *
	 * @return void
	 */
	protected function _setup_hooks() {
		/**
		 * Actions
		 */
		add_action( 'post_submitbox_misc_actions', array( $this, 'add_flag' ) );
		add_action( 'save_post', array( $this, 'save_flag' ) );
		add_action( 'pre_get_posts', array( $this, 'alter_main_query' ) );
		add_action( 'after_setup_theme', array( $this, 'register_meta' ) );
	}


	/**
	 * This function returns the IDs of posts marked from exclusion. It returns
	 * non cached data, be careful with this function.
	 *
	 * @return array Returns an array containing IDs of posts marked for exlusion from Home river
	 */
	public function get_excluded_posts_from_db() {
		$sql = "SELECT post_id FROM " . $GLOBALS['wpdb']->postmeta . " WHERE meta_key=%s ORDER BY meta_id DESC LIMIT 0, %d";

		$post_ids = $GLOBALS['wpdb']->get_col( $GLOBALS['wpdb']->prepare( $sql, self::FLAG, self::EXCLUSION_LIMIT ) );

		if ( ! empty( $post_ids ) ) {
			$post_ids = array_filter( array_unique( array_map( 'intval', (array) $post_ids ) ) );
		} else {
			$post_ids = array();
		}

		return $post_ids;
	}


	/**
	 * This function returns the IDs of posts marked from exclusion. It returns
	 * cached data and is safe to use.
	 *
	 * @return array Returns an array containing IDs of posts marked for exlusion from Home river
	 */
	public function get_excluded_posts() {
		$pmc_cache = new PMC_Cache( self::PLUGIN_ID );

		return $pmc_cache->expires_in( $this->_get_cache_life() )
						->updates_with( array( $this, 'get_excluded_posts_from_db' ) )
						->get();
	}


	/**
	 * Add flag toggle UI to post publish options metabox
	 *
	 * @return void
	 */
	public function add_flag() {
		if ( empty( $GLOBALS['post']->ID ) || intval( $GLOBALS['post']->ID ) < 1 ) {
			return;
		}

		$flag = get_post_meta( $GLOBALS['post']->ID , self::FLAG, true );
		$flag = ( ! empty( $flag ) ) ? true : false;

		//flag-toggle-ui
		echo PMC::render_template( __DIR__ . '/templates/flag-toggle-ui.php', array(
			'key'   => self::FLAG,
			'value' => $flag,
			'nonce' => $this->_nonce,
		) );
	}


	/**
	 * Save flag toggle if its selected else delete the key from post meta
	 *
	 * @param int $post_id ID of the post for which the flag is to be saved/removed
	 * @return void
	 */
	public function save_flag( $post_id ) {
		if ( empty( $_POST[ $this->_nonce['name'] ] ) || ! wp_verify_nonce( $_POST[ $this->_nonce['name'] ], $this->_nonce['action'] ) ) {
			//nonce verification failed, bail out
			return;
		}

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$excluded_posts = $this->get_excluded_posts();
		$rebuild_cache = false;		//assume cache is not to be rebuilt

		if ( ! empty( $_POST[ self::FLAG ] ) ) {
			update_post_meta( $post_id, self::FLAG, 1 );

			if ( ! in_array( $post_id, $excluded_posts ) ) {
				//flag added but post ID not in excluded posts cache, so we'll rebuild it
				$rebuild_cache = true;
			}
		} else {
			delete_post_meta( $post_id, self::FLAG );

			if ( is_array( $excluded_posts ) && in_array( $post_id, $excluded_posts ) ) {
				//flag removed but post ID is in excluded posts cache, so we'll rebuild it
				$rebuild_cache = true;
			}
		}

		if ( $rebuild_cache === true ) {
			$pmc_cache = new PMC_Cache( self::PLUGIN_ID );

			$pmc_cache->invalidate()
						->expires_in( $this->_get_cache_life() )
						->updates_with( array( $this, 'get_excluded_posts_from_db' ) )
						->get();
		}

		unset( $rebuild_cache, $excluded_posts );
	}


	/**
	 * Alter river query to filter out posts which have been marked for exclusion
	 *
	 * @param WP_Query $query Object of the WP_Query class
	 * @return void
	 */
	public function alter_main_query( $query ) {

		$perform_exclusion = false;

		if ( ! is_a( $query, 'WP_Query' ) ) {
			return;
		}

		if ( is_admin() ) {
			return;
		}

		$exclude_from_custom_queries = apply_filters( 'pmc_exclude_posts_from_custom_queries', false, $query );

		if ( ! $query->is_main_query() && ! $exclude_from_custom_queries ) {
			return;
		}

		/**
		 * Exclude from the home query river (page of posts)
		 *
		 * @param bool     $exclude_from_home_query Defaults to false.
		 * @param WP_Query $query                   The current query object
		 */
		$exclude_from_home_query = apply_filters( 'pmc-exclude-posts-from-home-river', true, $query );

		if ( $query->is_home() && $exclude_from_home_query ) {
			$perform_exclusion = true;
		}

		/**
		 * Exclude from all archive rivers (tax, cat, tag archives, etc.)
		 * 02/08/2016 James Mehorter — Added this is_archive() check. Default is false simply because we need to maintain the existing functionality for all existing usages (by default only perform the exclusion for the home main query.)
		 *
		 * @param bool     $exclude_from_archive_queries Defaults to false.
		 * @param WP_Query $query                        The current query object
		 */
		$exclude_from_archive_queries = apply_filters( 'pmc-exclude-posts-from-archive-rivers', false, $query );

		if ( $query->is_archive() && $exclude_from_archive_queries ) {
			$perform_exclusion = true;
		}

		/**
		 * Exclude from any custom query.
		 * 12/04/2019 Keanan Koppenhaver — Default is false simply because we need to maintain the existing functionality for all existing usages (by default only perform the exclusion for the home main query.)
		 *
		 * @param bool     $exclude_from_archive_queries Defaults to false.
		 * @param WP_Query $query                        The current query object
		 */

		if ( $exclude_from_custom_queries ) {
			$perform_exclusion = true;
		}

		// Only perform the exclusion if the above conditionals say so
		if ( ! $perform_exclusion ) {
			return;
		}

		$post_not_in = $query->get( 'post__not_in' );
		$posts_to_exclude = $this->get_excluded_posts();

		if ( empty( $posts_to_exclude ) || ! is_array( $posts_to_exclude ) ) {
			return;
		}

		if ( is_array( $post_not_in ) ) {
			$post_not_in = array_filter( array_unique( array_map( 'intval', array_merge( $post_not_in, $posts_to_exclude ) ) ) );
		} else {
			$post_not_in = $posts_to_exclude;
		}

		$query->set( 'post__not_in', $post_not_in );
	}

	/**
	 * Expose postmeta for Gutenberg.
	 *
	 * @codeCoverageIgnore Registering meta in a plugin without test coverage.
	 */
	public function register_meta(): void {
		register_meta(
			'post',
			static::FLAG,
			[
				'type'          => 'boolean',
				'single'        => true,
				'show_in_rest'  => true,
				'default'       => false,
				'auth_callback' => '__return_true', // Required to edit protected meta.
			]
		);
	}

}	//end of class


//EOF
