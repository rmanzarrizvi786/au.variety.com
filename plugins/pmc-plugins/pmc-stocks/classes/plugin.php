<?php
/**
 * Class contains PMC Stocks Plugin related functions.
 *
 * @since 2016-07-18 - Mike Auteri - PPT-6906
 * @version 2016-07-18 - Mike Auteri - PPT-6906
 */
namespace PMC\Stocks;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

class Plugin {

	use Singleton;

	protected function __construct() {
		add_action( 'init', array( $this, 'register_content' ) );
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
	}

	/**
	 * Register PMC Stocks post types and taxonomies.
	 *
	 * @return void
	 */
	public function register_content() {
		$this->register_post_types();
		$this->register_taxonomies();
	}

	/**
	 * Register PMC Stocks widgets.
	 *
	 * @return void
	 */
	public function register_widgets() {
		register_widget( 'PMC\Stocks\Widget_Global_Index' );
		register_widget( 'PMC\Stocks\Widget_Market_Movers' );
	}

	public function register_scripts() {
		// Last argument registers rather than enqueues.
		pmc_js_libraries_enqueue_script( 'pmc-d3', '', array(), '', '', false );

		wp_register_style( 'pmc-stocks-css', plugins_url( '../assets/css/stocks.css',  __FILE__ ), array(), PMC_STOCKS_VERSION );

		wp_register_script( 'pmc-stocks-js', plugins_url( '../assets/js/stocks.js',  __FILE__ ), array( 'jquery', 'pmc-d3' ), PMC_STOCKS_VERSION );

	}

	/**
	 * Enqueue helper function.
	 *
	 * @param array $localized_scripts
	 */
	public function enqueue( $localized_scripts = array() ) {
		wp_enqueue_style( 'pmc-stocks-css' );
		wp_enqueue_script( 'pmc-stocks-js' );
	}

	/**
	 * Register PMC Stocks post types.
	 *
	 * @return void
	 */
	public function register_post_types() {
		$args = array(
			'public'              => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_in_nav_menus'   => false,
			'show_in_menu'        => false,
		);

		/**
		 * pmc-stock:
		 * - Stores daily data about chosen symbols
		 * - At any given time there should only be one post per stock symbol, which contains the previous day's data.
		 *
		 * post meta includes:
		 * - Individual stock data.
		 * - Averages for multiple data ranges, e.g. 1-day, 1-month, etc.
		 *
		 * taxonomies:
		 * - pmc-stock-region
		 * - pmc-stock-category
		 */
		register_post_type( 'pmc-stock', array_merge( $args, [ 'labels' => [ 'name' => 'PMC Stocks', 'singular_name' => 'PMC Stock' ] ] ) );

		/**
		 * pmc-stock-index:
		 * - Stores daily averages of combined individual stocks.
		 * - At any given time there should be one post per day for all days we can obtains data, e.g., if we store 3 years of data we will have 1095 posts.
		 */
		register_post_type( 'pmc-stock-index', array_merge( $args, [ 'labels' => [ 'name' => 'PMC Stock Indexes', 'singular_name' => 'PMC Stock Index' ] ] ) );

		/**
		 * pmc-stock-summary:
		 * - Stores data range change, high, low, advancers, decliners.
		 * - Any given time contains 1 post
		 *
		 * post meta:
		 * - Index change, high, low, advancers, and decliners for each date range.
		 */
		register_post_type( 'pmc-stock-summary', array_merge( $args, [ 'labels' => [ 'name' => 'PMC Stock Summaries', 'singular_name' => 'PMC Stock Summary' ] ] ) );
	}

	/**
	 * Register PMC Stocks taxonomies.
	 *
	 * @return void
	 */
	public function register_taxonomies() {
		$args = array(
			'public'             => true,
			'show_in_menu'       => false,
			'show_in_nav_menus'  => false,
			'show_tagcloud'      => false,
			'show_in_quick_edit' => false,
			'show_admin_column'  => false,
		);

		register_taxonomy( 'pmc-stock-region', 'pmc-stock', $args );
		register_taxonomy( 'pmc-stock-category', 'pmc-stock', $args );
	}

}

// EOF
