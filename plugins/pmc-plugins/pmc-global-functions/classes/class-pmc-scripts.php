<?php
/**
 * PMC Scripts.
 *
 * Similar to wp_localize_script, but allows the output of javascript at any
 * action and priority, without any dependencies on registered/enqueued scripts.
 *
 * Sample usage: PMC_Scripts::add_script( 'pmc_head_js', (array)$sample_data, 'wp_head', 15 );
 *
 * @version 1.0.1 2014-01-28 Add support for non-scalar values.
 * @version 1.0 Inital release
 *
 * @author  Corey Gilmore
 * @license PMC Proprietary.  All rights reserved
 *
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Scripts {

	use Singleton;

	protected static $filters = array();
	protected static $default_opts = array();

	protected function __construct() {

		static::$default_opts = array(
			'script_tag_attributes'  => array(), // provide an array of attr_name => attr_value pairs to override things like type or ID
			'object_only'            => false, // this is a very special case, you must pass true for the override see https://penskemediacorp.atlassian.net/browse/PPT-3113
		);

		add_action( 'init', array( $this, 'action_init' ) );
		//script_loader_tag filter hook needs to have higher priority
		add_filter( 'script_loader_tag', array( $this, 'filter_script_loader_tag' ), 19, 3 );
	}

	/**
	 * Handle wp init action, all code that need to run after theme have a chance to add filter/action
	 */
	public function action_init() {

		$hostname = parse_url( get_bloginfo( 'url' ), PHP_URL_HOST );
		$pmc_site_config = array(
			'rot13_hostname' => str_rot13( $hostname ),
			'hostname'       => $hostname,
			'is_proxied'     => null,
		);
		$pmc_site_config = apply_filters( 'pmc_site_config_js', $pmc_site_config );
		PMC_Scripts::add_script( 'pmc_site_config', (array)$pmc_site_config, 'wp_head' );

	}

	/**
	 * Output a script tag attached to a given hook and priority.
	 *
	 * @author Corey Gilmore
	 * @version 2013-06-20 Corey Gilmore
	 */
	public static function add_script( $object_name, $l10n, $action, $priority = 10, $opts = array() ) {
		$priority = intval($priority);

		$opts = wp_parse_args( $opts, static::$default_opts );

		if( empty(static::$filters[$action]) ) {
			static::$filters[$action] = array();
		}

		if( empty(static::$filters[$action][$priority]) ) {
			static::$filters[$action][$priority] = array();
			$function_name = 'priority_' . $priority . '_action_' . $action;
			if( !static::valid_function_name( $function_name ) ) {
				wp_die( esc_html( "'$function_name' is not a valid function name. Confirm the action being used." ) );
			}
			add_action( $action, array('PMC_Scripts', $function_name ), $priority );
		}

		// This isn't ideal, but we want to ensure that bad code never makes it out of dev (it won't work in prod anyway).
		if( !static::valid_js_var_name( $object_name ) ) {
			wp_die( esc_html( "'$object_name' is not a valid javascript variable name. You must correct this." ) );
		}

		// Store the script settings in an array of filters keyed on action, priority. Store any options with the tag as well.
		static::$filters[$action][$priority][$object_name] = array(
			'l10n'  => $l10n,
			'opts'  => $opts,
		);

	}

	protected static function load_scripts( $scripts ) {
		$script_blocks = array();
		$js = array();

		foreach( $scripts as $name => $script ) {
			$l10n = $script['l10n'];
			$opts = $script['opts'];
			$script_tag_opts_hash = md5( serialize( $opts['script_tag_attributes'] ) );
			if( !isset( $script_blocks[$script_tag_opts_hash] ) ) {
				$script_blocks[$script_tag_opts_hash] = array(
					'js'        => '',
					'opts'      => $opts,
					'tag_opts'  => $opts['script_tag_attributes'],
				);
			}
			/**
			 * Use a hash of the script tag opts so we can continue grouping similar blocks of code in a single script tag, based on tag attributes.
			 *
			 */
			$script_blocks[$script_tag_opts_hash]['js'] .= static::localize_script( $name, $l10n, false, $opts ) . "\n";
		}

		foreach( $script_blocks as $script_tag ) {
			if( !empty( $script_tag['js'] ) ) {
				$js[] = static::wrap_script( $script_tag['js'], $script_tag['tag_opts'] );
			}
		}
		return implode( "\n", $js );

	}

	public static function valid_js_var_name( $var_name ) {
		// regexp is the most simple version from http://stackoverflow.com/questions/1661197/valid-characters-for-javascript-variable-names
		$re = '/^[a-zA-Z_$][0-9a-zA-Z_$]*$/';

		return preg_match( $re, $var_name, $matches );
	}

	public static function valid_function_name( $function_name ) {
		// regexp is from http://php.net/manual/en/functions.user-defined.php
		$re = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

		return preg_match( $re, $function_name, $matches );
	}

	/**
	 * Helper function to determine if a value is permitted to be encoded and output in a variable.
	 * Currently allowed types: all scalars (numeric/bool/string), null, object, array
	 *
	 * @return bool true/false if a value is permitted to be encoded and output
	 *
	 */
	public static function is_value_encodable( $value ) {
		return is_scalar($value) || is_null($value) || is_object($value) || is_array($value);
	}

	public static function localize_script( $object_name, $l10n, $wrap = false, $opts = array() ) {
		if( is_array($l10n) && isset($l10n['l10n_print_after']) ) { // back compat, preserve the code in 'l10n_print_after' if present
			$after = $l10n['l10n_print_after'];
			unset($l10n['l10n_print_after']);
		}
		$opts = wp_parse_args( $opts, static::$default_opts );

		foreach( (array) $l10n as $key => $value ) {
			if( !static::is_value_encodable($value) ) {
				continue;
			}

			if( is_bool($value) || !is_scalar($value) ) { // don't convert boolean and non-scalar values to strings
				$l10n[$key] = $value;
			} else {
				$l10n[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8');
			}
		}
		// In some VERY specific cases we only want an object, no variable name. See PPT-3113
		if( $opts['object_only'] === true ) {
			$script = json_encode($l10n) . ';';
		} else {
			// This isn't ideal, but we want to ensure that bad code never makes it out of dev (it won't work in prod anyway).
			if( !static::valid_js_var_name( $object_name ) ) {
				wp_die( esc_html( "'$object_name' is not a valid javascript variable name. You must correct this." ) );
			}
			$script = "var $object_name = " . json_encode($l10n) . ';';
		}

		if ( !empty($after) ) {
			$script .= "\n$after;";
		}

		if( $wrap ) {
			$script = static::wrap_script($script);
		}

		return $script;
	}

	public static function wrap_script( $script, $tag_opts ) {
		$default_tag_opts = array(
			'type'  => 'text/javascript',
		);
		$tag_opts = wp_parse_args( $tag_opts, $default_tag_opts );

		$wrapped = '';
		$wrapped .= "<script"; // CDATA and type='text/javascript' is not needed for HTML 5
		// Loop through the attribute => value key/pair for the script tag
		foreach( $tag_opts as $attr_name => $val ) {
			if( !empty( $val ) ) {
				$wrapped .= ' ' . pmc_sanitize_html_attribute_name( $attr_name ) . '="' . esc_attr( $val ) . '"';
			}
		}
		$wrapped .= ">\n";
		$wrapped .= "/* <![CDATA[ */\n";
		$wrapped .= "$script\n";
		$wrapped .= "/* ]]> */\n";
		$wrapped .= "</script>\n";

		return $wrapped;
	}

	public static function __callStatic($method, $args) {
		if( !preg_match( '/priority_(\d+)_action_(.*)/', $method, $matches ) ) {
			return;
		}

		if( sizeof($matches) != 3 ) {
			return;
		}

		$priority = $matches[1];
		$action = $matches[2];

		if( empty(static::$filters[$action]) || empty(static::$filters[$action][$priority]) ) {
			return;
		}

		echo static::load_scripts( static::$filters[$action][$priority] );

	}

	/**
	 * This function will add async="async" to the script tag for the scripts that are enqueued with the handle name starts with 'pmc-async'.
	 * Please note this is only for the external scripts.
	 * This is not recommended for local scripts or external scripts that need to load synchronously.
	 * But for any reason if you want to use this for local scripts, you should remove the script from being concatenated.
	 *
	 * @param string $tag
	 * @param string $handle
	 * @param string $src
	 *
	 * @return string
	 */
	public function filter_script_loader_tag( $tag = '', $handle = '', $src = '' ) {

		//Add async='async' to the tag only once when $handle name starts with 'pmc-async'
		if ( 0 === strpos( $handle, 'pmc-async' ) && false === strpos( $tag, ' async' ) && false === strpos( $tag, 'async=' ) ) {
			$tag = str_replace( '<script', '<script async="async"', $tag );
		}
		return $tag;
	}

}

PMC_Scripts::get_instance();

// EOF
