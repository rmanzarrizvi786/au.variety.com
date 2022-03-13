<?php

/**
 * PMC Custom Google Site Search base class
 *
 * @author Amit Gupta <agupta@pmc.com>
 * @since 2014-08-25
 */

use \PMC\Global_Functions\Traits\Singleton;

abstract class PMC_Customized_Google_Site_Search {

	use Singleton;
	/**
	 * @var string Prefix used when enqueuing assets
	 */
	const ASSET_PREFIX = 'pmc-customized-gss';

	/**
	 * @var string Default search engine
	 */
	const DEFAULT_ENGINE = 'site';

	/**
	 * @var string Key name for use in querystring to denote search engine name
	 */
	const QUERY_KEY = 'engine';

	/**
	 * @var string Search results URI
	 */
	const PAGE_URI = '/results/';


	/**
	 * @var array An array containing names and types of abstract properties that must be implemented in child classes
	 */
	private $_abstract_properties = array(
		'array' => array(
			'_engines', '_banners',
		),
	);


	/**
	 * Initialization
	 *
	 * @return void
	 */
	final protected function __construct() {
		$this->_setup_hooks();
		$this->_enforce_abstract_properties_existence();

		$this->_child_init();
	}


	/**
	 * abstract method for all child classes to implement to run their
	 * respective init stuff
	 */
	abstract protected function _child_init();


	/**
	 * Called on class init, this function enforces existence of abstract
	 * properties in child classes. All properties defined in $this->_abstract_properties
	 * must be set by all child classes of exactly the same type as defined.
	 *
	 * @return void
	 */
	final protected function _enforce_abstract_properties_existence() {
		//check if the child has defined the abstract properties or not
		$current_child = get_class( $this );

		foreach ( $this->_abstract_properties as $type => $properties ) {
			$count = count( $properties );

			for ( $i = 0; $i < $count; $i++ ) {
				if ( property_exists( $this, $properties[ $i ] ) && strtolower( gettype( $this->{$properties[ $i ]} ) ) == $type ) {
					continue;
				}

				//property does not exist
				$error = $current_child . ' class must define $' . $properties[ $i ] . ' property as ' . $type;

				throw new \LogicException( $error );
			}
		}
	}


	/**
	 * Setup hooks which are mandatory for all children
	 *
	 * @return void
	 */
	private function _setup_hooks() {
		/*
		 * Actions
		 */
		add_action( 'wp_head', array( $this, 'intercept_search_results_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_stuff' ) );
		add_action( 'pmc-custom-search-page-title-banner', array( $this, 'show_custom_search_results_banner' ) );
	}


	/**
	 * This function accepts an engine name and checks if that is a registered search engine or not
	 *
	 * @param string $engine_name Search engine name
	 * @return bool TRUE if $engine_name is a registered search engine else FALSE
	 */
	protected function _engine_exists( $engine_name = '' ) {
		return ( ! empty( $engine_name ) ) ? (bool) array_key_exists( $engine_name, $this->_engines ) : false;
	}


	/**
	 * This function accepts an engine name and checks if its banner image is registered or not
	 *
	 * @param string $engine_name Search engine name
	 * @return bool TRUE if banner image for $engine_name is registered else FALSE
	 */
	protected function _engine_banner_exists( $engine_name = '' ) {
		if ( ! empty( $engine_name ) && $this->_engine_exists( $engine_name ) && array_key_exists( $engine_name, $this->_banners ) ) {
			//allow engine banner only if engine exists
			return true;
		}

		return false;
	}


	/**
	 * This function accepts an engine name and returns its corresponding Google Custom Search Engine ID
	 *
	 * @param string $engine_name Search engine name
	 * @param bool $return_empty Set to TRUE if empty string should be returned in case $engine_name engine does not exist
	 * @return string Google Custom Search Engine ID
	 */
	protected function _get_engine_id( $engine_name = '', $return_empty = false ) {
		$engine_id = '';

		if ( $this->_engine_exists( $engine_name ) ) {
			$engine_id = $this->_engines[ $engine_name ];
		} elseif ( $return_empty !== true ) {
			$engine_id = $this->_engines[ self::DEFAULT_ENGINE ];
		}

		return $engine_id;
	}


	/**
	 * This function accepts an engine name and returns its search results page URL
	 *
	 * @param string $engine_name Search engine name
	 * @return string Search results page URL
	 */
	protected function _get_search_url( $engine_name = '' ) {
		if ( strpos( $engine_name, ':' ) !== false ) {
			//engine ID was passed instead of engine name, so lets grab corresponding engine name
			$engine_name = array_search( $engine_name, $this->_engines );
		}

		if ( empty( $engine_name ) ) {
			//no engine name set, fallback to default engine
			$engine_name = self::DEFAULT_ENGINE;
		}

		//return search results page URL with engine name in querystring
		return add_query_arg( self::QUERY_KEY, $engine_name, home_url( self::PAGE_URI ) );
	}


	/**
	 * This function checks if the current page is search results page or not
	 *
	 * @return bool TRUE if current page is search results page else FALSE
	 */
	protected function _is_results_page() {
		if ( ! empty( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], self::PAGE_URI ) === 0 ) {
			return true;
		}

		return false;
	}


	/**
	 * This function returns the name of current search engine if current page is search results page
	 * else if will return an empty string.
	 *
	 * @return string Current search engine if current page is search results page else an empty string
	 */
	protected function _get_current_engine_name() {
		$engine_name = '';

		if ( array_key_exists( self::QUERY_KEY, $_GET ) ) {
			$engine_name = sanitize_title( strip_tags( urldecode( $_GET[ self::QUERY_KEY ] ) ) );
		}

		return $engine_name;
	}


	/**
	 * This function returns the Google Search Engine ID of current search engine if current page is search results page
	 * else if will return an empty string.
	 *
	 * @return string Google Search Engine ID of current search engine if current page is search results page else an empty string
	 */
	protected function _get_current_engine() {
		return $this->_get_engine_id( $this->_get_current_engine_name(), true );
	}


	/**
	 * Enqueues styles/scripts for front-end
	 *
	 * @return void
	 */
	public function enqueue_stuff() {
		if ( is_admin() ) {
			return;
		}

		wp_enqueue_style( self::ASSET_PREFIX . '-search-box', plugins_url( 'assets/css/search-box.css', __FILE__ ), array(), false, 'all' );
	}


	/**
	 * This function returns/prints the HTML for customized Google Custom Search form
	 *
	 * @param array $options An array of options to customize the search form including search results page URL, search engine name
	 * @param bool $return Set to TRUE to make it return the HTML else FALSE to print the HTML where this function is called
	 * @return void/string Returns HTML to render form if $return is TRUE else it renders the form and does not return anything
	 */
	public function render_search_box( array $options = array(), $return = false ) {
		$default_options = array(
			'search_results_url' => $this->_get_search_url(),
			'engine_name' => self::DEFAULT_ENGINE,
			'search_box_placeholder' => 'search',
		);

		$options = wp_parse_args( $options, $default_options );

		$html = PMC::render_template( __DIR__ . '/views/custom-google-search-box.php', $options );

		if ( $return === true ) {
			return $html;
		}

		echo $html;
	}


	/**
	 * This is an interceptor function hooked into 'wp_head' to change '[pmc_google_site_search]' shortcode
	 * in post_content of current page if current page is search results page and current search engine name
	 * is available.
	 *
	 * @return void
	 */
	public function intercept_search_results_shortcode() {
		if ( is_admin() || ! is_page() || ! $this->_is_results_page() ) {
			return;
		}

		$engine = $this->_get_current_engine();
		$shortcode = '[pmc_google_site_search site_search_key=%s]';

		if ( ! empty( $GLOBALS['post'] ) && ! empty( $GLOBALS['post']->post_content ) && ! empty( $engine ) ) {
			$GLOBALS['post']->post_content = sprintf( $shortcode, $engine );
		}

		unset( $shortcode, $engine );
	}


	/**
	 * This function is hooked into 'pmc-custom-search-page-title-banner' action and displays
	 * a banner image for current search engine if a banner image is registered and exists on filesystem.
	 *
	 * @return void
	 */
	public function show_custom_search_results_banner( $post_id ) {
		if ( is_admin() || ! $this->_is_results_page() ) {
			//not search results page, bail out
			return;
		}

		$current_engine = $this->_get_current_engine_name();

		if ( empty( $current_engine ) || ! $this->_engine_banner_exists( $current_engine ) ) {
			//not an existing engine
			return;
		}

		$banner_url = '%s/' . PMC::unleadingslashit( $this->_banners[ $current_engine ] );

		if ( ! file_exists( sprintf( $banner_url, untrailingslashit( get_stylesheet_directory() ) ) ) ) {
			//banner image does not exist, bail out
			return;
		}

		$banner_url = sprintf( $banner_url, untrailingslashit( get_stylesheet_directory_uri() ) );
		printf( '<img src="%s" class="search-page-title-banner">', $banner_url );
	}


}	//end of class


//EOF
