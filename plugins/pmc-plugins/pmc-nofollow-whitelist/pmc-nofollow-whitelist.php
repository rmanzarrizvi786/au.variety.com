<?php
/*
Plugin Name: PMC Nofollow Whitelist
Plugin URI: http://pmc.com/
Description: A plugin to add nofollow relation in HTML to all external links going to domains not in whitelist
Version: 0.5
Author: Amit Gupta
Author URI: http://pmc.com/
License: PMC Proprietary.  All rights reserved.
*/

/**
 * Static class for plugin
 * @since 2011-11-03 Amit Gupta
 * @version 2012-02-06 Amit Gupta
 * @version 2013-08-09 Amit Gupta
 */

define( 'PMC_NOFOLLOW_WHITELIST_VERSION', 1 );

class PMC_Nofollow_White_List {

	protected static $_options;	//this holds the options fetched from DB
	protected static $_whitelist;	//this holds the whitelist converted into an array
	public static $settings = array();	//this holds the names of plugin settings
	public static $form_fields = array();	//this holds the names of settings form fields

	/* constructor private, this is a static class, so no initialization */
	private function __construct() { }

	/**
	 * ignition on, pre-flight checks
	 */
	public static function init() {
		self::$settings = array(
			'opt_name' => 'pmc_nfwl_options',
			'opt_name_wl' => 'whitelist',
			'opt_group' => 'pmc_nfwl_opt_group',
			'page_slug' => 'pmc_nfwl_settings',
		);
		self::$form_fields = array(
			'wl_id' => 'pmc_fld_wl_id',
			'wl' => 'pmc_nfwl_opt_wl',
		);
		/**
		 * fetch options from DB
		 */
		self::$_options = get_option(self::$settings['opt_name']);
		self::refresh_whitelist();

		PMC_Nofollow_White_List::activate();
	}

	/**
	 * function to handle stuff when plugin is activated
	 */
	public static function activate() {
		if (get_option('pmc_nofollow_whitelist_version') < PMC_NOFOLLOW_WHITELIST_VERSION) {
			//default whitelist to save on activation
			$arr_options = array(
				0 => '_placeholder_',
				self::$settings['opt_name_wl'] => array(
					'bgr.com',
					'hollywoodlife.com',
					'hollybaby.com',
					'tvline.com',
					'deadline.com',
					'movieline.com',
					'oncars.com',
					'pmc.com'
				)
			);
			if(!get_option(self::$settings['opt_name'])) {
				add_option(self::$settings['opt_name'], $arr_options, '', 'yes');
				self::$_options = $arr_options;
				self::refresh_whitelist();
			}
			unset($arr_options);
		}
	}

	/**
	 * grab the whitelist index from the options array & put in the whitelist var
	 */
	protected static function refresh_whitelist() {
		self::$_whitelist = self::$_options[self::$settings['opt_name_wl']];

		//add current domain to whitelist
		self::$_whitelist[] = self::_get_current_domain();
		self::$_whitelist = array_unique( self::$_whitelist );
	}

	/**
	 * called by 'the_content' filter, this one goes through all links & passes them to parse_links() for further
	 * processing
	 */
	public static function filter_text($content) {
		if(!empty($content)) {
			$content = preg_replace_callback( '/<a([^>]*)>(.*?)<\/a[^>]*>/is', 'self::parse_links', $content);
		}
		return $content;
	}

	/**
	 * primary engine ->>
	 * gets the stuff from anchor tags, splits & categorizes values on attribute level, performs the
	 * whitelisting checks and the builds back the anchor tags
	 */
	protected static function parse_links( $matches ) {

		if( empty( $matches ) || ( count( $matches ) < 3 ) ) {
			return;
		}

		$is_allowed = false;    //is the domain whitelisted?

		$attrs = static::parse_link_attrs( stripslashes( $matches[1] ) );

		$is_nofollow = null;

		if( is_array( $attrs ) && array_key_exists( 'href' , $attrs ) ) {
			$is_nofollow = self::is_nofollow( $attrs['href'] );
		}

		if( is_null( $is_nofollow ) ) {
			return $matches[0];
		}

		if( $is_nofollow === false ) {
			$is_allowed = true;
		}

		/**
		 * This is to allowlist the Links/URLs depending on the attributes of the Link.
		 * 
		 * @param string 'pmc_nofollow_is_allowed' is the filter hook.
		 * @param bool $is_allowed is the value being filtered.
		 * @param array $attrs The attributes of the Anchor Tag.
		 * 
		 */
		$is_allowed = apply_filters( 'pmc_nofollow_is_allowed', $is_allowed, $attrs );

		unset( $is_nofollow );

		if( is_array( $attrs ) && array_key_exists( "rel", $attrs ) ) {
			$old_rel = $attrs['rel'];
		} else {
			$old_rel = '';
		}

		if( !empty( $old_rel ) ) {
			//lets split up all rel values, multiple spaces, carriage returns, newlines etc should be no bar ;)
			$arr_tmp = preg_split( "/[\s]+/", $old_rel );

			if ( false === $is_allowed && in_array( 'dofollow', (array) $arr_tmp, true ) ) {
				$key = array_search( "dofollow", $arr_tmp );
				unset( $arr_tmp[$key] );
				unset( $key );
			}

			if( in_array( "nofollow", $arr_tmp ) ) {
				$key = array_search( "nofollow", $arr_tmp );
				unset( $arr_tmp[$key] );
				unset( $key );
			}

			$old_rel = " " . implode( " ", $arr_tmp );
			$old_rel = str_replace( " follow", "", $old_rel );
			$old_rel = trim( $old_rel );

			unset( $arr_tmp );
		}

		$attrs['rel'] = $old_rel;

		if ( false === $is_allowed ) {
			$attrs['rel'] .= " nofollow";
		}

		$attrs['rel'] = trim( $attrs['rel'] );

		if( empty( $attrs['rel'] ) ) {
			unset( $attrs['rel'] );
		}

		//if link is not of current site, open in new window
		if( self::get_domain( $attrs['href'] ) !== self::_get_current_domain() ) {
			$attrs['target'] = "_blank";
		}

		$link = '<a ';

		foreach( $attrs as $key => $value ) {

			if( $key === "href" ){
				$escaped_value = esc_url( $value );
			}else{
				$escaped_value = esc_attr( $value );
			}

			$link .= ' ' . esc_attr( $key ) . '="' . $escaped_value .'" ';
		}


		$link .= ' >' . wp_kses( $matches[2], static::_get_kses_allowed_html() ) . '</a>';

		return $link;
	}

	/**
	 * takes in the full URI & returns the domain
	 */
	protected static function get_domain( $uri, $top_level = true ) {
		if ( empty( $uri ) ) {
			return;
		}

		$top_level = ( $top_level === false ) ? false : true;

		if ( strpos( $uri, '://' ) === false ) {
			$uri = 'http://' . ltrim( $uri, '/' );
		}

		$arr_uri = parse_url( $uri );

		if ( ! isset( $arr_uri['host'] ) ) {
			//no domain present, bail out
			return false;
		}

		$domain = strtolower( $arr_uri['host'] );

		unset( $arr_uri );

		if ( $top_level === true ) {
			$domain = implode( '.', array_slice( explode( '.', $domain ), -2 ) );
		}

		return $domain;
	}

	/**
	 * This function returns the current domain
	 */
	protected static function _get_current_domain() {
		return self::get_domain( home_url() );
	}

	/**
	 * scout ->>
	 * based on whitelist, tells whether the URI is to be followed or not
	 */
	protected static function is_nofollow($uri) {
		if(empty($uri)) { return; }
		$domain = self::get_domain($uri);
		if($domain===false) { return false; }
		if(!in_array($domain, self::$_whitelist)) {
			//domain not in whitelist, is to be nofollow-ed
			return true;
		}
		return false;
	}

	/**
	 * Parse and return link attributes
	 *
	 * @param string $value
	 *
	 * @return array|string
	 */
	protected static function parse_link_attrs( $value ) {

		$attrs = shortcode_parse_atts( stripslashes( $value ) );

		// Fix custom attributes that don't get handled properly by shortcode_parse_atts()
		if ( is_array( $attrs ) ) {

			foreach ( $attrs as $k => $v ) {

				// The array shortcode_parse_atts returns contains strings as
				// keys for attributes it finds, e.g. Array( 'href' => 'http://..' )
				// and if it's unable to handle something it returns that value
				// with a numerical key, e.g. Array( 0 => "foo='bar'" ). We're only
				// interested in the later here..
				if ( is_numeric( $k ) ) {

					// Only attempt to parse an attribute if it's like so: "foo='bar'"
					if ( false !== strpos( $v, '=' ) ) {

						$new_attr = explode( '=', $v, 2 );
						unset( $attrs[$k] );

						$attrs[ $new_attr[0] ] = str_replace( array( "'", "\"" ), '', $new_attr[1] );

					}

				}

			}

		}

		return $attrs;

	}

	/**
	 * Register '[link-follow]' Shortcode which remove 'rel="nofollow"'
	 *
	 * Because PMC Nofollow Whitelist plugin add 'rel="nofollow"' on 'the_content' filter and
	 * we need to execute shortcode after that.
	 *
	 * @param string $content The post content.
	 *
	 * @return string $content
	 */
	public static function register_link_follow_shortcode( $content ) {

		global $shortcode_tags;

		if ( empty( $content ) ) {
			return $content;
		}

		// Back up current registered shortcodes and clear them all out
		$orig_shortcode_tags = $shortcode_tags;
		remove_all_shortcodes();

		add_shortcode( 'link-follow', [ get_called_class(), 'allow_link_follow' ] );

		// Do the shortcode (only the [link-follow] one is registered)
		$content = do_shortcode( $content );

		// Put the original shortcodes back
		$shortcode_tags = $orig_shortcode_tags;

		return $content;

	}

	/**
	 * Find all anchor tags from content and remove 'rel' attribute.
	 *
	 * @param array|string $attr    Shortcode attributes array or empty string.
	 * @param string       $content Shortcode content.
	 *
	 * @return string
	 */
	public static function allow_link_follow( $attr, $content = '' ) {

		if ( ! empty( $content ) ) {

			$content = preg_replace_callback( '/<a([^>]*)>(.*?)<\/a[^>]*>/is', [ get_called_class(), 'remove_rel_attribute' ], $content );

		}

		return $content;

	}

	/**
	 * Remove 'rel' attribute from anchor tags
	 *
	 * @param array $matches An array of regex expression matches.
	 *
	 * @return string|void
	 */
	protected static function remove_rel_attribute( $matches ) {

		if ( empty( $matches ) || ( count( $matches ) < 3 ) ) {
			return;
		}

		$attrs = static::parse_link_attrs( stripslashes( $matches[1] ) );

		if ( ! is_array( $attrs ) ) {
			return;
		}

		// Remove 'rel' attribute if its value is 'nofollow'.
		if ( ! empty( $attrs['rel'] ) && 'nofollow' === $attrs['rel'] ) {
			unset( $attrs['rel'] );
		}

		$link = '<a ';

		foreach ( $attrs as $key => $value ) {

			if ( 'href' === $key ) {
				$escaped_value = esc_url( $value );
			} else {
				$escaped_value = esc_attr( $value );
			}

			$link .= ' ' . esc_attr( $key ) . '="' . $escaped_value . '" ';

		}

		$link .= ' >' . wp_kses( $matches[2], static::_get_kses_allowed_html() ) . '</a>';

		return $link;

	}

	/**
	 * Retrieve array of allowed HTML tags and attributes for use with
	 * `wp_kses()`.
	 *
	 * Allows links to contain SVGs from Larva's `c-icon` component.
	 *
	 * @return array
	 */
	protected static function _get_kses_allowed_html(): array {
		return array_merge(
			[
				'svg' => [
					'class'       => true,
					'aria-hidden' => true,
				],
				'use' => [
					'xlink:href' => true,
				],
			],
			wp_kses_allowed_html( 'post' )
		);
	}


	/* End of Class */
}

if(is_admin()) {
	//load up plugin admin UI
	include_once("pmc-nfwl-settings.php");
}

/*
 * activated plugin on init action to fetch correct mapped domain at home_url() on production.
 * Ref: https://vip.wordpress.com/documentation/home_url-vs-site_url/
 */
add_action( 'init', 'PMC_Nofollow_White_List::init' );

//add filter on content
add_filter("the_content", "PMC_Nofollow_White_List::filter_text", 10);

add_filter( 'the_content', [ 'PMC_Nofollow_White_List', 'register_link_follow_shortcode' ], 12 );


//EOF
