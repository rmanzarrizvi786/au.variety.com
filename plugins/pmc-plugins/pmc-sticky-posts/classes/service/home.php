<?php
/**
 * Homepage service class for PMC Sticky Posts plugin.
 * This class handles only homepages of sites where the plugin is activated.
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 *
 * @revision 2017-07-28 Hau
 *
 */


namespace PMC\Sticky_Posts\Service;

use \PMC_Cache;
use PMC\Post_Options\API as Post_Options_API;

class Home extends Base {

	/*
	 * Option vars
	 */
	const OPTION_NAME  = 'pmc-stick-on-homepage';
	const OPTION_LABEL = 'Stick on Homepage';

	const CACHE_LIFE = 300;		//5 minutes

	/**
	 * @var PMC\Post_Options\API
	 */
	protected $_post_options_api;

	/**
	 * Class constructor
	 */
	protected function __construct() {
		// need to call parent constructor to initialize parent class private data
		parent::__construct();

		$this->_post_options_api = Post_Options_API::get_instance();
		$this->_setup_hooks();
	}

	/**
	 * Method to setup listeners into WordPress hooks as needed.
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		// This init action must run at a lower priority to allow custom post and custom taxonomy have a chance to register first
		// @TODO: Need to look at and fix how default can be added without this workaround: PMC\Post_Options\API::get_instance()->register_global_options
		add_action( 'init', array( $this, 'maybe_add_option' ) );

		if ( ! is_admin() ) {
			add_action( 'pre_get_posts', array( $this, 'exclude_sticky_posts_in_river_query' ), 20 );	//delay query modification
			add_action( 'pre_get_posts', [ $this, 'disable_wp_sticky_posts_if_needed' ], 99 );
			add_filter( 'the_posts', array( $this, 'inject_sticky_posts' ), 10, 2 );
		}

	}

	/**
	 * We want to set and disable the wp sticky posts and return all posts if there is no explicity flag set
	 * @param  WP_Query $wp_query The WP Query object
	 * @return void
	 */
	public function disable_wp_sticky_posts_if_needed( $query ) {
		if ( ! isset( $query->query_vars['ignore_sticky_posts'] ) ) {
			$query->set( 'ignore_sticky_posts', true );
		}
	}

	/**
	 * Check whether current page is first page of home river or not.
	 *
	 * @param WP_Query $wp_query
	 * @return bool Returns TRUE if current page is first page of home river else FALSE
	 */
	protected function _is_home_page( $wp_query ) {

		if ( $wp_query->is_main_query() && $wp_query->is_home() && intval( $wp_query->get( 'paged' ) ) <= 1 ) {
			return true;
		}

		return false;

	}

	/**
	 * Called on 'init', this method adds a global post option to
	 * stick post on homepage if the option doesn't already exist. This method
	 * must not be called directly.
	 *
	 * @return void
	 */
	public function maybe_add_option() {

		$this->_post_options_api->register_global_options( array(
			self::OPTION_NAME => array(
				'label'       => self::OPTION_LABEL,
				'description' => 'Posts with this term would be stickied on homepage river in configured positions.',
			),
		) );

	}

	/**
	 * Method to fetch sticky posts which are set to be stickied on homepage.
	 * This is called by cache object to (re)build cache and must not be called
	 * directly.
	 *
	 * @return array An array of post objects which are set to be stickied on homepage else empty array if no posts found
	 */
	public function build_sticky_post_collection() {

		return $this->_post_options_api->get_posts_having_option( self::OPTION_LABEL, array(
			'posts_per_page' => $this->_config['max_count'],
			'post_type'      => $this->_config['post_type'],
		) );

	}

	/**
	 * Method to get sticky posts for homepage. This method returns cached results.
	 *
	 * @return array An array of post objects which are set to be stickied on homepage else empty array if no posts found
	 */
	public function get_cached_sticky_posts() {

		$cache = new PMC_Cache( sprintf( '%s-%s', parent::PLUGIN_ID, self::OPTION_NAME ) );

		$posts = $cache->expires_in( self::CACHE_LIFE )
						->updates_with( array( $this, 'build_sticky_post_collection' ) )
						->get();

		if ( ! empty( $posts ) && $this->_config['max_count'] > count( $posts ) ) {

			/*
			 * We didn't get as many posts as we wanted,
			 * so lets re-align our position numbers and counts etc.
			 */
			$this->set_max_count( count( $posts ) )
				->commit_config();

		}

		return $posts;

	}

	/**
	 * Called on 'pre_get_posts', this method excludes sticky posts from
	 * the homepage river query so as to prevent showing duplicate posts
	 * in homepage river.
	 * This method must not be called directly.
	 *
	 * @param WP_Query $query WP_Query object for the current query
	 * @return void
	 */
	public function exclude_sticky_posts_in_river_query( $query ) {

		if ( ! $this->_is_home_page( $query ) ) {
			//its either not main query or its not the homepage
			//so lets bail out of here
			return;
		}

		/*
		 * ***** Sticky Post Exclusion *****
		 */

		$sticky_posts = $this->get_cached_sticky_posts();

		if ( empty( $sticky_posts ) ) {
			//we don't have any sticky posts, bail out
			return;
		}

		$sticky_post_ids = wp_list_pluck( $sticky_posts, 'ID' );

		/*
		 * Lets be smart and respect any existing post IDs marked for
		 * exclusion in current query
		 */
		$excluded_posts = $query->get( 'post__not_in' );

		if ( empty( $excluded_posts ) ) {
			$excluded_posts = array();
		} elseif ( ! is_array( $excluded_posts ) ) {
			$excluded_posts = array( intval( $excluded_posts ) );
		}

		//merge our sticky post IDs with any existing post IDs marked for exclusion
		$excluded_posts = array_filter( array_unique( array_merge( $excluded_posts, $sticky_post_ids ) ) );

		//exclude sticky posts from query
		$query->set( 'post__not_in', $excluded_posts );

		/*
		 * ***** Resizing Resultset *****
		 */

		$page_size = intval( $query->get( 'posts_per_page' ) );

		if ( $page_size < 1 ) {
			//no page size set in query yet, fetch default from options
			$page_size = intval( get_option( 'posts_per_page' ) );
		}

		//deduct number of our sticky posts from page size
		$page_size -= $this->_config['max_count'];

		//set new size for this page, no need to fetch extra posts
		$query->set( 'posts_per_page', $page_size );

	}

	/**
	 * Called on 'the_posts', this method injects sticky posts in
	 * the homepage river resultset at the positions defined for
	 * sticky posts in the service config.
	 * This method must not be called directly.
	 *
	 * @param array $posts array of post objects in which objects of sticky posts are to be injected
	 * @param WP_Query $query WP_Query object for the current query
	 * @return array Array of post objects with sticky posts in their respective positions
	 */
	public function inject_sticky_posts( $posts, $query ) {

		if ( ! $this->_is_home_page( $query ) ) {
			//its either not the main query or its not the homepage
			//so lets bail out of here
			return $posts;
		}

		$sticky_posts = $this->get_cached_sticky_posts();

		if ( empty( $sticky_posts ) ) {
			//we don't have any sticky posts, bail out
			return $posts;
		}

		if ( empty( $posts ) || ! is_array( $posts ) ) {
			$posts = array();
		}

		/*
		 * Iterate and inject sticky posts in their respective positions
		 * in the posts array
		 */
		for ( $i = 0; $i < $this->_config['max_count']; $i++ ) {

			array_splice( $posts, $this->_config['positions'][ $i ], 0, array( $sticky_posts[ $i ] ) );

		}

		return $posts;

	}

}	//end class


//EOF