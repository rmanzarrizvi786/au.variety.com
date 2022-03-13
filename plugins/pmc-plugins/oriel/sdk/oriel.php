<?php
/**
 * Copyright Oriel Ventures Limited, 2017
 *
 * @name Oriel
 * Oriel PHP SDK
 * Version: 8.8.7
 *
 * License:
 * THE SOFTWARE CONTAINED IN THIS PACKAGE IS PROPERTY OF ORIEL VENTURES LIMITED.
 * ANY MODIFICATION, REPRODUCTION OR DISTRIBUTION OF THIS SOFTWARE WITHOUT THE
 * EXPLICIT CONSENT OF ORIEL VENTURES LIMITED IS FORBIDDEN.
 * THIS SOFTWARE COMES AS IS AND OFFERS NO WARRANTIES UNDER ANY CIRCUMSTANCES.
 * YOU MAY USE IT AT YOUR OWN RISK.
 */
namespace Oriel;

define( 'ORIEL__VERSION', '8.8.7' );
define( 'ORIEL__DIR', dirname( __FILE__ ) );

require_once ORIEL__DIR . '/includes/settings.php';
require_once ORIEL__DIR . '/includes/cache.php';
require_once ORIEL__DIR . '/includes/util.php';
require_once ORIEL__DIR . '/includes/api.php';
require_once ORIEL__DIR . '/includes/crypto.php';
require_once ORIEL__DIR . '/includes/parser.php';
require_once ORIEL__DIR . '/includes/formatting.php';
require_once ORIEL__DIR . '/lib/autoload.php';

/**
 * The main entry point of the SDK. Provides methods to insert head script and
 * process Oriel requests.
 *
 * @package Oriel
 */
class Oriel {


	private static $_instance;
	private static $_settings;
	private static $_cache;

	/**
	 * Oriel constructor.
	 */
	private function __construct() {
	}

	/**
	 * Processes a HTML page and depending on given settings
	 *
	 * @param  $html - HTML page source
	 * @return string - processed HTML
	 */
	public function process_html_page( $html ) {
		$settings = self::get_settings();
		$head_key = null;

		$domain = API::get_domain_data();
		if ( $domain['is_stale'] ) {
			return $html;
		}

		$loader          = $domain['loader_script'];
		$loader_settings = array();
		if ( ! $loader ) {
			return $html;
		}

		if ( $settings->dom_parser ) {
			$dom = new \IvoPetkov\HTML5DOMDocument();
			$dom->loadHTML( $html );
			$head = $dom->querySelector( 'head' );
			$body = $dom->querySelector( 'body' );
		} else {
			$head = preg_match( '/<\\s*head[^>]*>/si', $html );
			$body = preg_match( '/<\\s*body[^>]*>/si', $html );
		}

		if ( ! $head || ! $body ) {
			return $html;
		}

		if ( $settings->dom_parser && count( $settings->in_page_messages ) ) {
			foreach ( $settings->in_page_messages as $message ) {
				$article = $body->querySelector( $message['selector'] );
				if ( $article ) {
					$loader_settings['ipm'][] = array(
						's'  => $message['selector'],
						'c'  => base64_encode( $article->innerHTML ),
						'wl' => $message['word_limit'],
					);
					$article->innerHTML       = '';
				}
			}
		}

		if ( ( $settings->obfuscate_picture_sources || $settings->obfuscate_image_sources ) && Crypto::settings_placeholder_exists( $loader ) ) {
			$key = $settings->obfuscation_key;
			if ( ! isset( $key ) || trim( $key ) === '' ) {
				$key = Crypto::generateRandomKey( 16 );
			}

			return Parser::insertScriptAndObfuscate( $key, $html, $loader, $settings, $loader_settings );
		}

		if ( $loader_settings ) {
			$loader = Crypto::inject_settings_in_loader( json_encode( $loader_settings, JSON_NUMERIC_CHECK ), $loader );
		}

		if ( $settings->dom_parser ) {
			$tag = $dom->createElement( 'script' );
			$tag->setAttribute( 'type', 'text/javascript' );
			$tag->appendChild( $dom->createTextNode( $loader ) );

			$firstScript = $head->querySelector( 'script' );
			if ( $firstScript ) {
				$head->insertBefore( $tag, $firstScript );
			} else {
				$head->appendChild( $tag );
			}

			$html = $dom->saveHTML();
		} else {
			$output_script = '<script type="text/javascript">' . $loader . '</script>';
			$html          = HTMLHelper::inject_content( 'head', $output_script, $html, 'start' );
		}

		// Add noscript beacon if the setting is activated.
		if ( isset( $settings->noscript_beacon_url ) ) {
			$noscriptBeacon = "<noscript><img src='" . $settings->noscript_beacon_url . "'/></noscript>";
			$html           = HTMLHelper::inject_content( 'body', $noscriptBeacon, $html, 'start' );
		}

		return $html;

	}


	/**
	 * Outputs the original HTML or the merged content set by the head script hook.
	 *
	 * @param  callable|string $callback_or_string A callable function with array arguments or a string to be displayed with 404 HTTP status
	 * @param  string          $merged_file        The file contents that was meant to be merged
	 * @return string The original HTML or the merged content set by the head script hook
	 */
	private static function _return_original_html( $callback_or_string ) {

		$cs = $callback_or_string;
		if ( is_array( $cs ) && count( $cs ) > 1 && is_callable( $cs[0] ) ) {
			return call_user_func_array( $cs[0], $cs[1] );
		}

		return $cs;
	}


	/**
	 * Sets the application specific settings and optionally a custom cache implementation.
	 *
	 * @param array $new_settings New settings array
	 * @param Cache $new_cache    New Cache implementation provided by app, optional
	 */
	public function init( $new_settings, $new_cache = null ) {
		global $oriel_settings;
		if ( ! self::$_settings ) {
			self::$_settings = $oriel_settings;
		}
		self::$_settings = new \ArrayObject(
			array_merge( (array) $oriel_settings, $new_settings ),
			\ArrayObject::ARRAY_AS_PROPS
		);

		if ( null !== $new_cache ) {
			self::$_cache = $new_cache;
		}

		$lsettings = self::get_settings();

		if ( $lsettings->enable_remote_settings ) {
			$domain = API::get_domain_data();

			if ( $domain && isset( $domain['integration_settings'] ) ) {
				$remote_settings = $domain['integration_settings'];
				if ( $remote_settings ) {
					if ( isset( $domain['nuke_message'] ) ) {
						$remote_settings['nuke_message'] = $domain['nuke_message'];
					}

					self::$_settings = new \ArrayObject(
						array_merge( (array) $lsettings, $remote_settings ),
						\ArrayObject::ARRAY_AS_PROPS
					);
				}
			}
		}

		process_settings_constraints( self::$_settings );

		if ( self::$_settings->debug ) {
			$this->run_health_check();
		}
	}

	/**
	 * Oriel start hook
	 */
	public function start() {
		if ( class_exists( 'DOMDocument' ) ) {
			ob_start( array( $this, 'process_request' ) );
		}
	}

	/**
	 * Main entry point for processing requests. If the request is Oriel specific, it is redirected
	 * to the Oriel handler.
	 *
	 * @param  $html - HTML source of page
	 * @return string - Processed HTML source of page
	 */
	public function process_request( $html ) {
		$settings     = self::get_settings();

		if ( ! is_string( $html ) || stripos( $html, '</html>' ) === false ) {
			return self::_return_original_html( $html );
		} else {
			// Process regular HTTP request
			return $this->process_html_page( $html );
		}
	}

	/**
	 * End hook
	 */
	public function end() {
		if ( class_exists( 'DOMDocument' ) && ob_get_length() ) {
			ob_end_flush();
		}
	}

	/**
	 * Retrieves SDK settings.
	 *
	 * @return array|\ArrayObject
	 */
	public static function get_settings() {
		return self::$_settings;
	}

	/**
	 * Retrieves cache instance.
	 *
	 * @return Cache
	 */
	public static function get_cache() {
		if ( self::$_cache ) {
			return self::$_cache;
		}

		return Cache::instance();
	}

	/**
	 *
	 * @param string $message Message to be displayed
	 */
	private function _test_fail( $message ) {
		printf( '<div style="color:red">[FAIL] %1$s</div>', esc_html( $message ) );
	}

	/**
	 *
	 * @param string $message Message to be displayed
	 */
	private function _test_success( $message ) {
		printf( '<div style="color:green">[SUCCESS] %1$s</div>', esc_html( $message ) );
	}

	/**
	 * Runs health check suite, currently validating if cURL extension is installed.
	 */
	public function run_health_check() {
		if ( ! function_exists( 'curl_init' ) && ! function_exists( 'wp_remote_get' ) ) {
			$this->_test_fail( 'cURL extension missing.' );
		} else {
			$this->_test_success( 'cURL extension present.' );
		}

		if ( ! class_exists( 'DOMDocument' ) ) {
			$this->_test_fail( 'PHP-DOM missing.' );
		} else {
			$this->_test_success( 'PHP-DOM present.' );
		}

		$key = API::get_head_key();
		if ( ! $key ) {
			$this->_test_fail( 'Invalid API Key' );
		} else {
			$this->_test_success( 'Valid API Key' );
		}
	}


	/**
	 * Creates and returns an Oriel Singleton instance
	 *
	 * @return `Oriel`
	 */
	public static function instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Prevents object cloning
	 */
	private function __clone() {
	}

	/**
	 * Prevents deserialization
	 */
	private function __wakeup() {
	}

}


