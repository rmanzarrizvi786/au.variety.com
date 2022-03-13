<?php
/**
 * This plugin is written to fully integrate with PMC cdn cache behind Fastly CDN
 * Allowing essential assets to push to client via fastly http2 server push
 *
 * @author Hau Vong <hvong@pmc.com>
 *
 * @since  2018-12-17
 *
 * @ref: https://wordpress.org/plugins/http2-server-push/
 * @ref: https://www.fastly.com/blog/optimizing-http2-server-push-fastly
 * @ref: https://docs.fastly.com/guides/performance-tuning/http2-server-push
 *
 */

/**
 *
 * Use the following example combination of statements to configure which assets to allow the plugin
 * to auto detect and add the assets to the preloads header for http2 server push via fastly
 *
 *

	PMC\Global_Functions\Service\Http2::get_instance()  // grab the instance object

	// override the default regular expression patterns
	// Be mindful on the patterns to only include essential assets,
	// we only have limitted headers size of 8k total length in VIP environment
	->set_pattern( [ '/pattern1/', '/pattern2/' ] )

	// set the default whitelist of root related URL path of the assets
	->set_whitelist( [ '/wp-conten/upload/asset1.js', '/wp-conten/upload/asset2.js' ] )

	// Add a new regular expression pattern to match the preload assets URL path
	->register_pattern( '/pattern1/' )
	->register_pattern( '/pattern2/' )

	// Add a new root relative URL path to the white list
	->register_whitelist( '/wp-content/upload/asset3.css' );

 *
 *
 */

/**
 * To manually and fully control and override the preloads assets, use the filter "pmc_http2_preload_assets"
 * with function signature "function( $preloads )"
 *
	$preloads = [
		[
			'uri' => '/relative/path/to/asset',
			'as'  => 'script',  // script | style
		],
		[
			'uri' => '...',
			'as'  => '...',
		],
	];

 *
 */

namespace PMC\Global_Functions\Service;

use \ErrorException;
use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

class Http2 {

	use Singleton;

	// Need this number to be lower than VIP / VIP Go nginx fastcgi_buffers/size setting
	public const MAX_HEADER_SIZE = 1024 * 4;  //  assuming 4k max, 3k is would be the limit

	protected $_preloads           = [];
	protected $_preload_whitelists = [];

	// The default regular expression patter to match assets to include in the preloads push
	protected $_preload_regex_patterns = [
		'/\/pmc-plugins\//',
		'/\/pmc-core/',
		'/\/_static/',
	];

	/**
	 * Class constructor
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {

		// Only activate if the proper header has been detected from Fastly CDN caching
		if ( empty( \PMC::filter_input( INPUT_SERVER, 'HTTP_X_WP_CB', FILTER_SANITIZE_STRING ) ) ) {
			return;
		}

		$this->_setup_hooks();

	}

	/**
	 * Method which sets up our custom listeners on WP hooks
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() : void {

		// We want to attach the http2 process as latest as possible before content body is being flush.
		add_action( 'get_header', [ $this, 'initialize_process' ], 0 );

		// We want to capture script that add by script loader PMC_Feature::top_js_loader
		add_filter( 'script_loader_src', [ $this, 'filter_script_loader_src' ] );

	}

	/**
	 * Filter to capture the script added by PMC_Feature::top_js_loader
	 * @param  string $src The script url source
	 * @return string
	 */
	public function filter_script_loader_src( $src ) {

		$asset_uri = $this->_translate_relative_uri( $src );

		// Make sure uri is valid and not external resources
		if ( ! empty( $asset_uri ) && ! preg_match( '/\/\//', $asset_uri ) ) {
			$this->_preloads[] = [
				'as'  => 'script',
				'uri' => $asset_uri,
			];
		}

		return $src;
	}

	/**
	 * This function is responsible to intialize the process to capture the body into buffer in order
	 * to allow pre-load header output before body content is flushed.
	 *
	 * @codeCoverageIgnore
	 */
	public function initialize_process() : void {

		// We do not want to activate if the requst is a feed, admin, or ajax call.
		if ( is_feed() || is_admin() || wp_doing_ajax() ) {
			return;
		}

		// we want to turn on buffering using a call back function to capture and manipulate the buffer as needed.
		ob_start( [ $this, 'ob_callback' ] );

	}

	/**
	 * Output buffering callback to intercept and process any headers before body content is flush
	 * @param  string $bufs The string to be return
	 * @return string       The $bufs plus any debug info
	 */
	public function ob_callback( string $bufs = '' ) : string {

		// This is the last chance any header can added
		$assets = $this->get_preload_assets( $bufs );

		if ( ! empty( $assets ) && is_array( $assets ) ) {

			// piecing the header link into a single string to save some header bytes
			// @see https://docs.fastly.com/guides/performance-tuning/http2-server-push
			$links        = [];
			$current_size = 0;

			// Determinte the existing header size
			$headers = headers_list();
			if ( ! empty( $headers ) ) {
				// can't cover this code in unit test because headers_list doesn't work under cli process
				$current_size = strlen( implode( "\n", $headers ) ); // @codeCoverageIgnore
			}
			unset( $headers );

			foreach ( $assets as $asset ) {

				if ( empty( $asset['uri'] ) || empty( $asset['as'] ) ) {
					continue;
				}

				$link = sprintf( '<%s>; rel=preload; as=%s', $asset['uri'], $asset['as'] );
				$size = strlen( "link: \n" . $link );

				if ( $current_size + $size > self::MAX_HEADER_SIZE ) {
					// we need to break out of loop if additional header reached max buffer size
					break;
				}

				$links[]      = $link;
				$current_size = $current_size + $size;

			}

			$links = apply_filters( 'pmc_http2_preload_links', $links );

			if ( ! empty( $links ) ) {
				header( 'link: ' . implode( ', ', $links ) );
				unset( $links );
			}

		}

		// note: we may attach any debug via html comments info to $bufs before return

		return $bufs;
	}

	/**
	 * Return the list of preload assets
	 * @param  string $bufs The string to be process
	 * @return array
	 */
	public function get_preload_assets( string $bufs ) : array {

		global $wp_scripts, $wp_styles;

		$preloads = $this->_preloads;

		// First extract the styles
		$this->_extract_preload_assets( $preloads, $wp_styles );

		// Process VIP css concat, there is no filter expose, need to extract from the html source
		if ( preg_match_all( '/<link rel=(\'|")stylesheet\1 id=\1[^\1]+?\1 href=\1https:\/\/[^\/]+?(\/_static\/[^\1]+?)\1/', $bufs, $matches ) ) {
			foreach ( $matches[2] as $asset_uri ) {
				$preloads[] = [
					'uri' => $asset_uri,
					'as'  => 'style',
				];
			}
		}

		// Second extract the scripts
		$this->_extract_preload_assets( $preloads, $wp_scripts );

		// Process VIP js concat, there is no filter expose, need to extract from the html source
		if ( preg_match_all( '/<script type=(\'|"")text\/javascript\1 src=\1https:\/\/[^\/]+?(\/_static\/[^\1]+?)\1/', $bufs, $matches ) ) {
			foreach ( $matches[2] as $asset_uri ) {
				$preloads[] = [
					'uri' => $asset_uri,
					'as'  => 'script',
				];
			}
		}

		$whitelists     = apply_filters( 'pmc_http2_whitelists', $this->_preload_whitelists );
		$regex_patterns = apply_filters( 'pmc_http2_regex_patterns', $this->_preload_regex_patterns );
		$script_lists   = [];
		$style_lists    = [];

		foreach ( $preloads as $asset ) {

			if ( empty( $asset['uri'] ) || empty( $asset['as'] ) ) {
				// This continue statement doesn't need to be covered.  We can't fake the $preloads to contains invalid value.
				// However, we need the empty statement check against variable before use as a coding standard to avoid potential future code break.
				continue; // @codeCoverageIgnore
			}

			$asset_uri = $asset['uri'];

			$can_add = false;
			if ( ! empty( $regex_patterns ) ) {
				foreach ( $regex_patterns as $pattern ) {
					// preg_match doesn't cause exception, it is safe to call without try catch
					$can_add = ( preg_match( $pattern, $asset_uri ) > 0 );
					if ( $can_add ) {
						break;
					}
				}
			}

			if ( ! $can_add && ! empty( $whitelists ) ) {
				$can_add = in_array( $asset_uri, (array) $whitelists, true );
			}

			if ( $can_add ) {
				if ( 'style' === $asset['as'] ) {
					if ( ! isset( $style_lists[ $asset_uri ] ) ) {
						$style_lists[ $asset_uri ] = $asset;
					}
				} else {
					if ( ! isset( $script_lists[ $asset_uri ] ) ) {
						$script_lists[ $asset_uri ] = $asset;
					}
				}
			}

		}

		// We want to preload styles before scripts
		$preloads = array_values( array_merge( (array) $style_lists, (array) $script_lists ) );
		unset( $lists, $style_lists, $script_lists );

		return apply_filters( 'pmc_http2_preload_assets', $preloads );

	}

	/**
	 * Extract the assets from WP_Scripts | WP_Styles object
	 * @param  Array &$preloads The reference to the array to extract uri into;
	 * @param  object $obj      The WP_Scripts | WP_Styles object
	 * @return void
	 */
	protected function _extract_preload_assets( array &$preloads, object $obj ) : void {
		// IMPORTANT: We need to use variable by reference avoid local scope param from allocating extra stack memory for performance purpose.
		// The $preloads can potentially contains large amount data that we can't control and we don't want to duplicate the memory stack

		// we only extract assets that have been processed
		if ( empty( $obj->done ) || ! is_array( $obj->done ) ) {
			return;
		}

		// the type of asset we're extracting
		$asset_type = ( ( $obj instanceof \WP_Scripts ) ? 'script' : 'style' );

		foreach ( $obj->done as $handle ) {

			if ( ! isset( $obj->registered[ $handle ] ) ) {
				// This continue statement doesn't need to be covered.  We relying on the WP_Scripts | WP_Styles code correctness.
				// However, we need the empty statement check against variable before use as a coding standard to avoid potential future code break.
				continue; // @codeCoverageIgnore
			}

			$src = $obj->registered[ $handle ]->src;

			// @see class.wp-scripts.php / class.wp-styles.php
			if ( null !== $obj->registered[ $handle ]->ver ) {
				$ver = ( ( $obj->registered[ $handle ]->ver ) ?: $obj->default_version );
				$src = add_query_arg( 'ver', $ver, $src );
			}

			$asset_uri = $this->_translate_relative_uri( $src );

			// check to see if uri is valid, not duplicate, etc...
			if ( ! empty( $asset_uri ) && ! preg_match( '/\/\//', $asset_uri ) ) {
				$preloads[] = [
					'uri' => $asset_uri,
					'as'  => $asset_type,
				];
			}

		}

	}

	/**
	 * Translate the uri into a relative uri string
	 * @param  string $uri Uri to translate
	 * @return string      The relative uri string
	 */
	protected function _translate_relative_uri( string $uri ) : string {

		$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$site_host = wp_parse_url( site_url(), PHP_URL_HOST );
		$pattern   = preg_quote( $home_host ) . '|' . preg_quote( $site_host );
		$uri       = preg_replace( '/https?:\/\/' . $pattern . '/i', '', $uri );

		return $uri;

	}

	/**
	 * Helper function to set the default regex pattern for preload assets
	 * @param array $regex_patterns The list of Regex pattern
	 */
	public function set_pattern( array $regex_patterns ) : Http2 {
		$this->_preload_regex_patterns = $regex_patterns;
		return $this;
	}

	/**
	 * Helper function to set the default list of preload asset uri
	 * @param array $list The array of preload asset uri
	 */
	public function set_whitelist( array $lists ) : Http2 {
		$this->_preload_whitelists = $lists;
		return $this;
	}

	/**
	 * Helper function to register a new regex pattern for preload asset
	 * @param  string $pattern The regex pattern
	 */
	public function register_pattern( string $pattern ) : Http2 {
		if ( ! empty( $pattern ) && ! in_array( $pattern, (array) $this->_preload_regex_patterns, true ) ) {
			$this->_preload_regex_patterns[] = $pattern;
		}
		return $this;
	}

	/**
	 * Helper function to register a new hitelist uri for preload asset
	 * @param  string $uri The uri to register
	 */
	public function register_whitelist( string $uri ) : Http2 {
		if ( ! empty( $uri ) && ! in_array( $uri, (array) $this->_preload_whitelists, true ) ) {
			$this->_preload_whitelists[] = $uri;
		}
		return $this;
	}

}

