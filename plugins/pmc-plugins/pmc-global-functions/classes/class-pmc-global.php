<?php

use PMC\Global_Functions\Traits\Singleton;

final class PMC_Global {

	use Singleton;

	private $_body_class_add    = [];
	private $_body_class_remove = [];

	protected function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		add_filter( 'body_class', [ $this, 'filter_body_class' ] );

		// We need to add this filter as early as possible to prevent is_home use incorrectly
		add_action( 'parse_query', [ $this, 'fix_is_home' ], 1 );

		// Add to very top of the page markup
		add_action( 'wp_head', [ $this, 'recruiting_promotion' ], 0 );

		add_filter( 'rewrite_rules_array', [ $this, 'filter_fix_feed_rewrite_rules' ] );
	}

	/**
	 * Fix feed/(...) rewrite rules that interfered by slug rewrite from custom post type, etc. %category%/gallery
	 * The rewrite set in register_post_type: 'rewrite' => [ 'slug' => '%category%/gallery' ]
	 * by default, set feeds setting to inherit/true causing wp feed endpoint support for the registered custom post type.
	 * WordPress converts this special %category% into regex (.+?)/(feed|rdf|rss|....)/?$ => index.php?category_name=$matches[1]&feed=$matches[2]
	 * This rule is added before feed rewrite rules and cause the rule to take priority which cause extra filter
	 * category=feed is added to the feed query causing all feed to be empty.
	 *
	 * To fix this without making code change to theme/plugin calling register_post_type,
	 * we're moving the wp feed rewrite rules to the top, giving it higher priority for pattern matching.
	 *
	 * @param $rules
	 * @return array
	 */
	public function filter_fix_feed_rewrite_rules( $rules ) : array {

		if ( ! empty( $GLOBALS['wp_rewrite']->feeds ) ) {
			$pattern    = '(' . implode( '|', (array) $GLOBALS['wp_rewrite']->feeds ) . ')/?$';
			$feed_regex = $GLOBALS['wp_rewrite']->feed_base . '/' . $pattern;

			if ( isset( $rules[ $feed_regex ] ) ) {
				$feed_rules = [
					$feed_regex => $rules[ $feed_regex ],
				];
				unset( $rules[ $feed_regex ] );
				$rules = array_merge( $feed_rules, $rules );
			}
		}

		return (array) $rules;
	}

	/**
	 * Add a recruiting message in the source code for potential applicants.
	 */
	public function recruiting_promotion() : void {
		\PMC::render_template( PMC_GLOBAL_FUNCTIONS_PATH . '/templates/recruiting.php', [], true );
	}

	/**
	 * Prevent custom template page detect as is_home / is_front_page
	 * Note: we do not define datatype for $template to avoid invalid usage that may cause fatal
	 *
	 * @param \WP_Query $query
	 */
	function fix_is_home( $query ) {
		global $wp;

		// Home page should never contain a request uri unless it's paginated
		// Custom template page would not have paged set
		// We also want to make changes on main query
		if ( ! empty( $wp->request ) && empty( $query->is_paged ) && $query->is_main_query() ) {
			$query->is_home = false;
		}

	}

	/**
	 * implement filter body_class to remove/add body classes
	 * @since 2014-08-05: migrate from bgr_body_class
	 * @param $classes array
	 */
	public function filter_body_class( $classes ) {

		if( !empty( $this->_body_class_add ) ) {
			$classes = array_unique( array_merge( $classes, $this->_body_class_add ) );
		}

		if( !empty( $this->_body_class_remove ) ) {
			$classes = array_diff( $classes, $this->_body_class_remove );
		}

		// Gallery archives need to be treated differently, and CSS :not() isn't reliable
		if( in_array( 'archive', $classes ) && !in_array( 'post-type-archive-gallery', $classes ) ) {
			$classes[] = 'archive-not-gallery';
		}

		return $classes;

	}

	/**
	 * this class will add a class to the body via body_class filter
	 * @since 2014-08-05: migrate from bgr_set_body_class
	 * @param $class array|string
	 */
	public function add_body_class( $class ) {
		if( empty( $class ) ) {
			return;
		}
		if( !is_array( $class ) ) {
			$class = preg_split( '#\s+#', $class );
		}
		$this->_body_class_add = array_merge( $this->_body_class_add, $class );
	}

	/**
	 * this function will remove the class from the body via body_class filter
	 * @since 2014-08-05: migrate from bgr_set_body_class
	 * @param $class array|string
	 */
	public function remove_body_class( $class ) {
		if( empty( $class ) ) {
			return;
		}
		if( !is_array( $class ) ) {
			$class = preg_split( '#\s+#', $class );
		}
		$this->_body_class_remove = array_merge( $this->_body_class_remove, $class );
	}

}
