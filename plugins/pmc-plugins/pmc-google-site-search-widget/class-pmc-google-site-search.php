<?php

class PMC_Google_Site_search {

	public static function load() {
		// add filter to indicate robots noindex
		add_filter( 'pmc_meta_robots_noindex', array( get_called_class(), 'meta_robots_noindex' ) );

		// add filter to append disallow on search page
		add_filter( 'pmc_robots_txt', array( get_called_class(), 'robots_txt' ), 10, 2 );

		//Add shortcode
		add_shortcode( 'pmc_google_site_search_v2', array( get_called_class(), 'google_site_search' ) );

		if( ! defined ('PMC_IS_VIP_GO_SITE' ) || PMC_IS_VIP_GO_SITE !== true ) {
			wpcom_vip_remove_opensearch();
		}

		add_action( 'wp_head', array( get_called_class(), 'insert_autodiscovery' ) );

		add_action( 'init', [ get_called_class(), 'add_rewrite_rule' ] );

		add_filter( 'query_vars', array( get_called_class(), 'add_query_vars' ) );

		add_action( 'template_redirect', array( get_called_class(), 'render_opensearch' ), 99 );

		add_filter( 'redirect_canonical', array( get_called_class(), 'prevent_trailing_slash' ) );

	}

	/**
	 * Add google site search shortcode v2
	 * This allows the search result to be displayed on a page that we have the shortcode
	 * defined on. Use v2 version of Google site search api https://developers.google.com/custom-search/docs/element.
	 * @version 1.0.0.0 2014-08-19 Amit Sannad
	 */
	public static function google_site_search( $atts ) {

		wp_enqueue_script( 'pmc-gss-script-v2', plugins_url( 'assets/js/script-v2.js', __FILE__ ), array( 'jquery' ), false, true );

		$default_atts = array(
			'site_search_key' => ''
		);
		$atts         = shortcode_atts( $default_atts, $atts );

		return "
		<script type='text/javascript'>
				var _pmc_google_site_search_id = '" . esc_js( $atts['site_search_key'] ) . "';
		</script>
		<div id='cse-result'>
			<div class='cse-search-form'></div>
			<div class='cse-results'></div>
		</div>";
	}

	/**
	 * Noindex on search results page
	 */
	public static function meta_robots_noindex( $value ) {

		if ( is_admin() || ! is_page() ) {
			return $value;
		}

		if ( strpos( $_SERVER['REQUEST_URI'], '/results/' ) === 0 ) {
			return true;
		}

		return $value;
	}

	/**
	 * Disallow /results/ on robotx.txt
	 */
	public static function robots_txt( $output, $public ) {

		if ( $public ) {
			$output .= "Disallow: /results/\n";
		}

		return $output;
	}

	public static function add_query_vars( $query_vars ) {
		$query_vars[] = 'pmc_opensearch';

		return $query_vars;
	}

	public static function insert_autodiscovery() {
		?>
		<link rel="search" type="application/opensearchdescription+xml"
		      href="<?php echo esc_url( home_url( '/pmc-opensearch' ) ); ?>"
		      title="<?php echo esc_attr( get_bloginfo( 'name' ) . "  Search" ); ?>"/>
	<?php
	}

	/**
	 * Cancel the canonical redirect (thereby preventing trailing slash)
	 */
	public static function prevent_trailing_slash( $redirect_url ) {
		$open_search = get_query_var( 'pmc_opensearch' );
		if ( 'opensearch' == $open_search ) {
			return false;
		}

		return $redirect_url;
	}

	public static function render_opensearch() {
		$open_search = get_query_var( 'pmc_opensearch' );
		if ( 'opensearch' !== $open_search ) {
			return;
		}
		header( 'Content-Type: text/xml' );
		echo '<?xml version="1.0" encoding="' . get_bloginfo( 'charset' ) . '"?>';
		?>
		<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/"
		                       xmlns:moz="http://www.mozilla.org/2006/browser/search/">
			<ShortName><?php echo esc_html( get_bloginfo( 'name' ) ); ?></ShortName>
			<Description><?php echo esc_html( get_bloginfo( 'description' ) ); ?></Description>
			<InputEncoding>UTF-8</InputEncoding>
			<Image width="16" height="16"
			       type="image/x-icon"><?php echo esc_url( get_stylesheet_directory_uri() . "/library/images/favicon.ico" ); ?></Image>
			<Url type="text/html" method="get"
			     template="<?php echo esc_url( home_url( '/' ) ); ?>results/?q={searchTerms}"></Url>
		</OpenSearchDescription>
		<?php
		die();
	}

	/**
	 * Add rewrite rule for opensearch
	 */
	public static function add_rewrite_rule() {
		add_rewrite_rule( 'pmc-opensearch$', 'index.php?pmc_opensearch=opensearch', 'top' );
	}
}

PMC_Google_Site_search::load();
//EOF
