<?php
/**
 * Plugin Class
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since   2018-04-11
 *
 * @package pmc-maz
 */

namespace PMC\Maz;

use PMC\Global_Functions\Traits\Singleton;

class Plugin {

	use Singleton;

	/**
	 * @var string Maz query variable
	 */
	const MAZ_QUERY_VAR = 'maz';

	/**
	 * Construct Method
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * To identify if current request is maz request or not.
	 *
	 * @return bool True if it is Maz endpoint otherwise False
	 */
	public function is_maz_endpoint() {

		if ( 0 === did_action( 'parse_query' ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( "is_pmc_maz_endpoint() was called before the 'parse_query' hook was called. This function will always return 'false' before the 'parse_query' hook is called.", 'pmc-maz' ) ), '1.0' );
		}

		return ( false !== get_query_var( self::MAZ_QUERY_VAR, false ) );
	}

	/**
	 * To get Maz URL for Post.
	 *
	 * @param  int $post_id Post ID.
	 *
	 * @return boolean|string Maz permalink.
	 */
	public function get_permalink( $post_id ) {

		if ( empty( $post_id ) || 0 >= intval( $post_id ) ) {
			return false;
		}

		$permalink  = get_permalink( $post_id );
		$parsed_url = wp_parse_url( $permalink );

		$structure = get_option( 'permalink_structure' );
		if ( empty( $structure ) || ! empty( $parsed_url['query'] ) ) {
			$maz_url = add_query_arg( self::MAZ_QUERY_VAR, 1, $permalink );
		} else {
			$maz_url = trailingslashit( $permalink ) . user_trailingslashit( self::MAZ_QUERY_VAR );
		}

		return $maz_url;
	}

	/**
	 * To convert url to maz url.
	 *
	 * @param  string $url Post perma link.
	 *
	 * @return string Post maz link.
	 */
	public function make_maz_url( $url ) {

		if ( empty( $url ) ) {
			return '';
		}

		$url = filter_var( $url, FILTER_VALIDATE_URL );

		if ( empty( $url ) ) {
			return '';
		}

		$parsed_url = wp_parse_url( $url );

		if ( ! empty( $parsed_url['query'] ) ) {
			$url     = html_entity_decode( $url );
			$maz_url = add_query_arg( self::MAZ_QUERY_VAR, 1, $url );
		} else {
			$maz_url = trailingslashit( $url ) . user_trailingslashit( self::MAZ_QUERY_VAR );
		}

		return $maz_url;
	}

	/**
	 * To setup actions/filters.
	 *
	 * @return void
	 */
	protected function _setup_hooks() {

		/**
		 * Actions
		 */
		add_action( 'init', array( $this, 'action_init' ) );

		/**
		 * Filters
		 */
		add_filter( 'request', [ $this, 'force_query_var_value' ] );
		add_filter( 'wp_head', [ $this, 'wp_head' ] );
		add_filter( 'body_class', [ $this, 'add_body_class' ] );
		add_filter( 'redirect_canonical', [ $this, 'redirect_canonical' ] );
		add_filter( 'pmc_meta_robots_noindex', [ $this, 'pmc_meta_robots_noindex' ] );

	}

	/**
	 * Init Action.
	 *
	 * @return void
	 */
	public function action_init() {
		add_rewrite_endpoint( self::MAZ_QUERY_VAR, EP_PERMALINK );
	}

	/**
	 * To make sure Maz query string have atleast value.
	 *
	 * @filter request
	 *
	 * @param  array $vars Request query variable.
	 *
	 * @return array Request query variable.
	 */
	public function force_query_var_value( $vars = array() ) {

		if ( isset( $vars[ self::MAZ_QUERY_VAR ] ) && '' === $vars[ self::MAZ_QUERY_VAR ] ) {
			$vars[ self::MAZ_QUERY_VAR ] = 1;
		}

		return $vars;
	}

	/**
	 * To add maz class to body element if it is maz reuqest.
	 *
	 * @filter body_class
	 *
	 * @param  array $classes List of css class will be applied on body.
	 *
	 * @return array List of css class will be applied on body.
	 */
	public function add_body_class( $classes = array() ) {

		if ( empty( $classes ) || ! is_array( $classes ) ) {
			$classes = array();
		}

		if ( is_pmc_maz_endpoint() ) {
			$classes[] = 'pmc-maz';
		}

		return $classes;
	}

	/**
	 * To add inline css if current request is maz request.
	 *
	 * @action wp_head
	 *
	 * @return void
	 */
	public function wp_head() {

		if ( ! is_pmc_maz_endpoint() ) {
			return;
		}

		$header_selector = Settings::get_instance()->get_header_selector();

		if ( ! empty( $header_selector ) ) {

			printf( '<style id="pmc-maz-inline-style" type="text/css">%s{ display:none; }</style>', wp_strip_all_tags( $header_selector ) ); // @codingStandardsIgnoreLine

		}

	}

	/**
	 * Filter to stop canonical redirect if we are on Maz endpoint
	 *
	 * @param  string $redirect_url canonical URL.
	 *
	 * @return string
	 */
	public function redirect_canonical( $redirect_url ) {

		if ( is_pmc_maz_endpoint() ) {
			return false;  // Prevent canonical redirect & cyclic redirect.
		}

		return $redirect_url;
	}

	/**
	 * Filter to add noindex meta if current page is a Maz page.
	 * Also removes canonical URL as page is noindexed.
	 *
	 * @filter pmc_meta_robots_noindex
	 *
	 * @param  bool $noindex Whether to render noindex meta.
	 *
	 * @return bool Whether to render noindex meta.
	 */
	public function pmc_meta_robots_noindex( $noindex ) {
		if ( is_pmc_maz_endpoint() ) {
			return true;
		}
		return $noindex;
	}

}
