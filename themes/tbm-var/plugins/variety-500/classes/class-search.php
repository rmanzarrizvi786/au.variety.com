<?php
/**
 * Search
 *
 * Responsible for search feature of Variety 500.
 *
 * @package pmc-variety-2017
 * @since 1.0
 */

namespace Variety\Plugins\Variety_500;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Search
 *
 * Sets up Swiftype.
 *
 * @since 1.0
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Search {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 * Initialize our search filters.
	 *
	 * @since 1.0
	 */
	protected function __construct() {
		add_filter( 'variety_swiftype_engine_key', array( $this, 'set_engine_key' ) );
		add_filter( 'swiftype_js_configuration_url', array( $this, 'set_configuration_url' ) );
		add_filter( 'swiftype_template_partial_path', array( $this, 'set_partial_path' ) );
		add_filter( 'swiftype_shortcode_template_path', array( $this, 'set_shortcode_path' ) );
		add_action( 'wp_head', array( $this, 'add_meta_tags' ), 2 );
	}

	/**
	 * Set Configuration URL
	 *
	 * Sets the path to the configuration file if we're on the Variety 500 home,
	 * profile or search page.
	 *
	 * @since 1.0
	 * @param string $url The original URL.
	 * @return string
	 */
	public function set_configuration_url( $url ) {
		if ( Templates::is_home() || Templates::is_search() || Templates::is_profile() ) {
			$url = untrailingslashit( VARIETY_500_PLUGIN_URL ) . '/assets/js/vendor/swiftype.js';
		}

		return $url;
	}

	/**
	 * Set Partial Path
	 *
	 * Sets the path to the partial template if we're on the Variety 500 home,
	 * profile or search page.
	 *
	 * @since 1.0
	 * @param string $path The original path.
	 * @return string
	 */
	public function set_partial_path( $path ) {
		if ( Templates::is_home() || Templates::is_search() || Templates::is_profile() ) {
			$path = untrailingslashit( VARIETY_500_ROOT ) . '/templates/swiftype/partials.php';
		}

		return $path;
	}

	/**
	 * Set Shortcode Path
	 *
	 * Sets the path to the shortcode template if we're on the Variety 500 home,
	 * profile or search page.
	 *
	 * @since 1.0
	 * @param string $path The original path.
	 * @return string
	 */
	public function set_shortcode_path( $path ) {
		if ( Templates::is_home() || Templates::is_search() || Templates::is_profile() ) {
			$path = untrailingslashit( VARIETY_500_ROOT ) . '/templates/swiftype/page-results.php';
		}

		return $path;
	}

	/**
	 * Set Engine Key
	 *
	 * Replaces the Engine Key for Swiftype if we're on the Variety 500 home,
	 * profile or search page.
	 *
	 * @since 1.0
	 * @param string $engine_key The replacement Engine Key.
	 * @return string
	 */
	public function set_engine_key( $engine_key ) {
		if ( Templates::is_home() || Templates::is_search() || Templates::is_profile() ) {
			$engine_key = 'waTC3PGq4_Yx7yGfWLc5';
		}

		return $engine_key;
	}

	/**
	 * Add Meta Tags
	 *
	 * Prints meta tags into the HTML header.
	 *
	 * @since 1.0
	 */
	public function add_meta_tags() {
		if ( is_post_type_archive( 'hollywood_exec' ) ) {
			printf( '<meta name="st:robots" content="follow, index">' );
		}
	}

	/**
	 * Get all terms of vy500_year taxonomy.
	 */
	public static function get_all_vy500_terms_for_search() {

		$args = [
			'taxonomy' => 'vy500_year',
			'number'   => 10,
			'orderby'  => 'slug',
			'order'    => 'DESC',
		];

		$variety_500_years = get_terms( $args );

		if ( empty( $variety_500_years ) ||
			( is_wp_error( $variety_500_years ) ) ||
			( ! is_array( $variety_500_years ) )
		) {
			return [];
		}

		$variety_500_current_year = get_option( 'variety_500_year', date( 'Y' ) );

		$filtered_terms = [];

		foreach ( (array) $variety_500_years as $year ) {

			// @codeCoverageIgnoreStart
			if ( ! is_a( $year, 'WP_Term' ) ) {
				continue;
			}
			// @codeCoverageIgnoreEnd
			if ( absint( $year->slug ) <= absint( $variety_500_current_year ) ) {

				// Include current years and previous years only.
				$filtered_terms[] = $year;
			}
		}

		return $filtered_terms;
	}
}

// EOF.
