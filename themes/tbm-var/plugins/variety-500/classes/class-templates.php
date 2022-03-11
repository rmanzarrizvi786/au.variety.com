<?php
/**
 * Templates
 *
 * Responsible for handling templates.
 *
 * @package pmc-variety-2017
 * @since 1.0
 */

namespace Variety\Plugins\Variety_500;

use PMC\Global_Functions\Traits\Singleton;
use PMC_Ads_Dfp_Skin;
use PMC_Ads_Interruptus;

/**
 * Class Templates
 *
 * Adds the admin functionality for Variety 500.
 *
 * @since 1.0
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Templates {

	use Singleton;

	const ASSET_VERSION = '2020.2.2';
	/**
	 * Class constructor.
	 *
	 * @codeCoverageIgnore
	 *
	 * Initialize our templating filters.
	 *
	 * @since 1.0
	 */
	protected function __construct() {
		add_action( 'single_template', array( $this, 'load_profile_template' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 1000 );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		add_filter( 'theme_page_templates', array( $this, 'load_page_templates' ) );

		add_action( 'wp', array( $this, 'on_action_wp' ) );
		add_action( 'wp_head', array( $this, 'add_homepage_meta_tags' ) );

	}

	/**
	 * The function will add meta tags on v500 homepage.
	 *
	 * @since 2017-09-13 Milind More CDWE-623
	 *
	 * @return void
	 */
	public function add_homepage_meta_tags() {

		if ( ! $this->is_home() ) {
			return;
		}

		printf( '<meta name="title" content="%s" />', esc_attr( wp_title( '', false ) ) );
		echo "\n";

	}

	/**
	 * Callback function for wp action.
	 *
	 * @action  wp
	 *
	 * @since   2017-09-13 Milind More CDWE-623
	 *
	 * @version 2019-01-02 Dhaval Parekh READS-1635
	 *
	 * @return void
	 */
	public function on_action_wp() {

		if ( self::is_home() || self::is_profile() ) {

			/**
			 * This will remove default <title> tag from v500 home page and profile page.
			 *
			 * Variety 500 plugin has different header which is customized for variety 500 pages,
			 * So we need to remove default site title to avoid duplication.
			 */
			remove_action( 'wp_head', '_wp_render_title_tag', 1 );

		}

		if ( self::is_home() || self::is_profile() || self::is_search() ) {
			/**
			 * To remove interrupts and skin ads from all variety 500 pages.
			 */
			remove_action( 'pmc-tags-top', [ PMC_Ads_Interruptus::get_instance(), 'action_interruptus' ] );
			remove_action( 'pmc-tags-top', [ PMC_Ads_Dfp_Skin::get_instance(), 'action_add_dfp_skin_markup' ] );
			add_filter( 'pmc_adm_should_render_ad', '__return_false' );
			add_filter( 'pmc_adm_dfp_skin_enabled', '__return_false' );
		}

		if ( self::is_search() ) {
			remove_filter( 'wp_head', [ 'LazyLoad_Images', 'setup_filters' ], 9999 );
		}

	}

	/**
	 * Load Profile Template
	 *
	 * Loads the profile template if it's a Variety 500 profile.
	 *
	 * @since 1.0
	 * @param string $template Exising WordPress template to be loaded.
	 * @return string
	 */
	public function load_profile_template( $template ) {
		if ( self::is_profile() ) {
			return untrailingslashit( VARIETY_500_ROOT ) . '/templates/profile.php';
		}

		return $template;
	}

	/**
	 * Load Page Templates
	 *
	 * Adds the home and search templates to the page attributes template
	 * selector so that we can add a home and search page for Variety 500 to the
	 * website.
	 *
	 * @since 1.0
	 * @param array $page_templates List of existing page templates.
	 * @return array
	 */
	public function load_page_templates( $page_templates ) {
		// Add our home and search page templates.
		$page_templates['plugins/variety-500/templates/home.php']   = __( 'Variety 500 - Home', 'pmc-variety' );
		$page_templates['plugins/variety-500/templates/search.php'] = __( 'Variety 500 - Search', 'pmc-variety' );
		return $page_templates;
	}

	/**
	 * Is Profile
	 *
	 * Checks to see if the current profile we're on is a Variety 500 profile
	 * based on the current year.
	 *
	 * @since 1.0
	 * @return bool
	 */
	public static function is_profile() {
		if ( is_admin() || ! class_exists( '\Variety_Hollywood_Executives_API' ) ) {
			return false;
		}

		if ( is_post_type_archive( \Variety_Hollywood_Executives_API::POST_TYPE ) ) {
			return false;
		}

		global $post;

		if ( empty( $post ) || ! is_a( $post, 'WP_Post' ) ) {
			return false;
		}

		if ( ! \Variety_Hollywood_Executives_API::POST_TYPE === $post->post_type ) {
			return false;
		}

		$is_preview = \PMC::filter_input( INPUT_GET, 'preview', FILTER_SANITIZE_STRING );

		if ( 'true' === $is_preview && 'hollywood_exec' === $post->post_type ) {
			return true;
		}

		// Will complete unit tests in PMCEED-826
		// @codeCoverageIgnoreStart
		$vy500_years_terms = get_the_terms( $post->ID, 'vy500_year' );
		$curr_year         = absint( get_option( 'variety_500_year', date( 'Y' ) ) );


		if ( empty( $vy500_years_terms ) || is_wp_error( $vy500_years_terms ) ) {
			return false;
		}

		$flag = false;

		// loop through term year slugs
		foreach ( (array) $vy500_years_terms as $vy500_years_term ) {

			// check that we're actually dealing with a WP_Term
			if ( ! is_a( $vy500_years_term, 'WP_Term' ) ) {
				continue;
			}

			// the current year must be greater than or equal to term->slug for this to be a Profile
			if ( $curr_year >= absint( $vy500_years_term->slug ) ) {
				$flag = true;
				break;
			}
		}

		return $flag;
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Is Home
	 *
	 * Test if it's the Home page template.
	 *
	 * @since 1.0
	 * @return bool
	 */
	public static function is_home() {
		return is_page_template( 'plugins/variety-500/templates/home.php' );
	}

	/**
	 * Is Search
	 *
	 * Test if it's the Search page template.
	 *
	 * @since 1.0
	 * @return bool
	 */
	public static function is_search() {
		return is_page_template( 'plugins/variety-500/templates/search.php' );
	}

	/**
	 * Header
	 *
	 * Loads the Variety 500 header.
	 *
	 * @since 1.0
	 */
	public static function header() {
		require untrailingslashit( VARIETY_500_ROOT ) . '/templates/header.php';
	}

	/**
	 * Site Header
	 *
	 * Loads the Variety 500 site header.
	 *
	 * @since 1.0
	 */
	public static function site_header() {
		require untrailingslashit( VARIETY_500_ROOT ) . '/templates/site-header.php';
	}

	/**
	 * Footer
	 *
	 * Loads the Variety 500 footer.
	 *
	 * @since 1.0
	 */
	public static function footer() {
		require untrailingslashit( VARIETY_500_ROOT ) . '/templates/footer.php';
	}

	/**
	 * Get Home URL
	 *
	 * Get the Permalink of the page using the V500 Home page template.
	 *
	 * @since 1.0
	 * @return string A URL, else an empty string.
	 */
	public static function get_home_url() {
		$link = wp_cache_get( 'home_url', Bootstrap::CACHE_GROUP );

		if ( ! empty( $link ) ) {
			return $link;
		}

		$query = new \WP_Query( array(
			'post_type'              => 'page',
			'posts_per_page'         => 1,
			'meta_key'               => '_wp_page_template',
			'meta_value'             => 'plugins/variety-500/templates/home.php',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		) );

		if ( ! empty( $query->posts[0]->ID ) && is_numeric( $query->posts[0]->ID ) ) {
			$link = get_permalink( $query->posts[0]->ID );

			// Ensure we don't get a "false" value.
			if ( ! empty( $link ) ) {
				wp_cache_set( 'home_url', $link, Bootstrap::CACHE_GROUP );
				return $link;
			}
		}

		return '';
	}

	/**
	 * Get Search URL along with query parameters.
	 *
	 * Get the Permalink of the page using the V500 Search page template with query parameters,
	 * where hash character '#' is used instead of question mark to indicate query string start
	 * (Swifttype's React app requirement). Also, the filters are decorated so that they are
	 * recognized and parsed by the SPA on the search page.
	 *
	 * @since 1.0
	 * @param array $args Query parameters to be added to the Search URL.
	 * @return string A URL, else an empty string.
	 */
	public static function get_search_url( $args = array() ) {
		$search_url = self::get_search_url_base();

		// Decorate filter names so that Swifttype app can parse them correctly.
		$params = array();

		foreach ( $args as $key => $value ) {
			if ( 'q' === $key || 'page' === $key || 'per_page' === $key ) {
				$params[ $key ] = $value;
			} else {
				$params[ 'filters[page][' . $key . '][type]' ]      = 'and';
				$params[ 'filters[page][' . $key . '][values][0]' ] = $value;
			}
		}

		// @todo Remove this condition once current year is set to 2018
		// and add below params to default params.
		if ( self::is_vy500_year_ge_2018() ) {

			$current_vy500_year = get_option( 'variety_500_year', date( 'Y' ) );

			$params['filters[page][vy500_year][type]']      = 'and';
			$params['filters[page][vy500_year][values][0]'] = $current_vy500_year;
		}

		// Provide default parameters that are required.
		$params = wp_parse_args( $params, array(
			'q'        => '',
			'page'     => 1,
			'per_page' => 24,
		) );

		// Build query string and return.
		$search_url_with_params = add_query_arg( $params, $search_url );
		$search_url_with_params = str_replace( '?', '#', $search_url_with_params );
		return $search_url_with_params;
	}

	/**
	 * Get Search URL Base
	 *
	 * Get the Permalink of the page using the V500 Search page template.
	 *
	 * @since 1.0
	 * @return string A URL, else an empty string.
	 */
	private static function get_search_url_base() {
		$link = wp_cache_get( 'search_url', Bootstrap::CACHE_GROUP );

		if ( ! empty( $link ) ) {
			return $link;
		}

		$query = new \WP_Query( array(
			'post_type'              => 'page',
			'posts_per_page'         => 1,
			'meta_key'               => '_wp_page_template',
			'meta_value'             => 'plugins/variety-500/templates/search.php',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		) );

		if ( ! empty( $query->posts[0]->ID ) && is_numeric( $query->posts[0]->ID ) ) {
			$link = get_permalink( $query->posts[0]->ID );

			// Ensure we don't get a "false" value.
			if ( ! empty( $link ) ) {
				wp_cache_set( 'search_url', $link, Bootstrap::CACHE_GROUP );
				return $link;
			}
		}

		return '';
	}

	/**
	 * Enqueue Assets
	 *
	 * Enqueue the Variety 500 stylesheets and scripts, and deregister unneeded assets.
	 *
	 * @see pmc_variety_scripts_and_styles()
	 *
	 * @since 1.0
	 */
	public static function enqueue_assets() {
		if ( ! self::is_home() && ! self::is_search() && ! self::is_profile() ) {
			return;
		}

		// Deregister stylesheets.
		wp_deregister_style( 'variety' );
		wp_deregister_style( 'variety-hollywood-exec-profile-style' );
		wp_deregister_style( 'variety-hollywood-exec-style-desktop' );

		// Enqueue the Variety 500 stylesheets.
		wp_enqueue_style( 'pmc-variety-500-stylesheet', untrailingslashit( VARIETY_500_PLUGIN_URL ) . '/assets/css/main.css', [], self::ASSET_VERSION );
		wp_enqueue_style( 'pmc-variety-500-google-fonts', '//fonts.googleapis.com/css?family=Teko:500' );

		// Deregister scripts.
		wp_deregister_script( 'pmc-variety-js' );
		wp_deregister_script( 'pmc-variety-modernizr' );

		// Enqueue the Variety 500 scripts.
		wp_enqueue_script( 'pmc-variety-500-modernizr-custom', untrailingslashit( VARIETY_500_PLUGIN_URL ) . '/assets/js/vendor/modernizr-custom.js', array(), self::ASSET_VERSION, false );
		wp_enqueue_script(
			'pmc-variety-500-main-script',
			untrailingslashit( VARIETY_500_PLUGIN_URL ) . '/assets/js/main.js',
			array(
				'jquery',
				'underscore',
			),
			self::ASSET_VERSION
		);

		/*
		 * Include the Chosen library (for filtering).
		 * This will be enqueued only on the search results page.
		 */
		if ( self::is_search() ) {
			wp_dequeue_script( 'google-ajax-comment-loading' );
			\pmc_js_libraries_enqueue_script( 'pmc-chosen' );
		}

		/*
		 * Slick JS Script
		 *
		 * Used for the homepage instagram feed animations and will only be
		 * loaded if on the homepage of Variety 500.
		 */
		if ( self::is_home() ) {
			wp_enqueue_script( 'pmc-variety-500-slick-script', untrailingslashit( VARIETY_500_PLUGIN_URL ) . '/assets/js/vendor/slick.js', array( 'jquery' ) );
		}
	}

	/**
	 * Save Post
	 *
	 * Template-related procedures to
	 * perform on the save_post action.
	 *
	 * @since 1.0
	 * @param string|int $post_id The Post ID.
	 * @param object     $post \WP_Post object.
	 */
	public function save_post( $post_id, $post ) {
		if ( 'page' !== $post->post_type ) {
			return;
		}
		// Delete our caches in case the page template is updated.
		wp_cache_delete( 'home_url', Bootstrap::CACHE_GROUP );
		wp_cache_delete( 'search_url', Bootstrap::CACHE_GROUP );
	}

	/**
	 * If current year greater or equal 2018 then return true.
	 *
	 * @return bool True if current year greater or equal 2018
	 */
	public static function is_vy500_year_ge_2018() {

		$current_vy500_year = get_option( 'variety_500_year', date( 'Y' ) );

		if ( absint( $current_vy500_year ) >= 2018 ) {
			return true;
		}

		return false;
	}
}
