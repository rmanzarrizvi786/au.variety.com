<?php
/**
 * Service class to allow conditionally set up CDN Cache bucket cookie
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2018-10-08
 */

namespace PMC\Global_Functions\Service;

use \ErrorException;
use \PMC\Global_Functions\Traits\Singleton;
use \PMC\Global_Functions\Classes\PMC_Cookie;


class CDN_Cache {

	use Singleton;

	/**
	 * @var string Cookie name which will contain cache bucket name(s)
	 */
	const COOKIE_NAME = 'cache_bucket';

	/**
	 * @var string Value used to separate multiple cache bucket names in cookie
	 */
	const COOKIE_VALUES_SEPERATOR = ':';

	/**
	 * @var string Default value for cache bucket cookie that must always be set
	 */
	const CACHE_BUCKET_DEFAULT = '*';

	/**
	 * @var callable Callback whose output decides whether to set cookie or not
	 */
	protected $_callback;

	/**
	 * @var array Parameters to be passed to callback
	 */
	protected $_callback_parameters = [];

	/**
	 * @var array Array containing 1:1 map of expected callback output and cache buckets to add accordingly
	 */
	protected $_map = [];

	/**
	 * @var array Cache buckets that are to be set in cookie
	 */
	protected $_cache_buckets = [];

	/**
	 * @var array Temporary Cache buckets that are stored here pending callback evaluation
	 */
	protected $_temporary_cache_buckets = [];

	/**
	 * @var array Array containing callback output and cache bucket(s) being set
	 */
	protected $_data_to_return = [];

	/**
	 * Class constructor
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {

		// only continue plugin activation only if Fastly plugin is properly loaded
		if ( ! $this->_load_fastly() ) {
			return;
		}

		$this->_setup_hooks();

	}

	/**
	 * Helper function to load and define Fastly default settings
	 *
	 * @return bool
	 *
	 * @codeCoverageIgnore
	 */
	protected function _load_fastly() {
		// HTTP2 server push support
		Http2::get_instance();

		// Auto load Fastly plugin if it wasn't activated yet.
		if ( ! class_exists( 'Purgely' ) ) {

			// We can only define these related Fastly default constant here only if Fastly plugin have not been activate

			// Default browser cache duration
			if ( ! defined( 'PURGELY_CACHE_CONTROL_TTL' ) ) {
				define( 'PURGELY_CACHE_CONTROL_TTL', MINUTE_IN_SECONDS * 5 );
			}

			// Fastly cache duration. Duration of cache stored in Fastly before re-validate
			if ( ! defined( 'PURGELY_SURROGATE_CONTROL_TTL' ) ) {
				define( 'PURGELY_SURROGATE_CONTROL_TTL', MINUTE_IN_SECONDS * 30 );
			}

			// Serve stale cache while validating in background.  If backend failed to validate, stale cache will be serve this amount of seconds before expired
			if ( ! defined( 'PURGELY_STALE_WHILE_REVALIDATE_TTL' ) ) {
				define( 'PURGELY_STALE_WHILE_REVALIDATE_TTL', MINUTE_IN_SECONDS * 30 );
			}

			// Serve stale cache if backend has error
			if ( ! defined( 'PURGELY_STALE_IF_ERROR_TTL' ) ) {
				define( 'PURGELY_STALE_IF_ERROR_TTL', MINUTE_IN_SECONDS * 30 );
			}

			// @TODO: Transition codes, will need to revisit and clean out
			// Prefer location when Fastly plugin deployed to pmc-plugins
			if ( file_exists( PMC_GLOBAL_FUNCTIONS_PATH . '/../fastly/fastly.php' ) ) {
				pmc_load_plugin( 'fastly', 'pmc-plugins' );
			} elseif ( file_exists( WP_PLUGIN_DIR . '/fastly/fastly.php' ) ) {
				// Fall back to VIP GO location
				pmc_load_plugin( 'fastly' );
			}

		}

		pmc_load_plugin( 'pmc-fastly', 'pmc-plugins' );

		add_filter( 'purgely_template_keys', [ $this, 'maybe_add_fastly_template_keys' ] );
		add_filter( 'purgely_surrogate_key_collection', [ $this, 'maybe_add_fastly_surrogate_keys' ] );
		add_filter( 'purgely_always_purged_types', [ $this, 'maybe_add_fastly_always_purged_types' ] );
		add_filter( 'purgely_related_surrogate_keys', [ $this, 'maybe_add_fastly_related_surrogate_keys' ], 10, 2 );

		// The following condition will make this plugin co-exists with vary_cache_on_function
		// Once we detected we're behind CDN caching, we need to disable batcache plugin if exists

		// Only activate if the proper header has been detected from Fastly CDN caching
		if ( empty( \PMC::filter_input( INPUT_SERVER, 'HTTP_X_WP_CB', FILTER_SANITIZE_STRING ) ) ) {
			// return false to disable the plugin since we're not behind Fastly
			return false;
		}

		// We need to disable batcache since we're behind Fastly CDN caching
		if ( function_exists( 'batcache_cancel' ) ) {
			batcache_cancel();
		}

		return true;

	}

	/**
	 * Method which sets up our custom listeners on WP hooks
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() : void {

		// set the listener at priority 20 to allow any other code
		// to run and set cache buckets if needed
		add_action( 'send_headers', [ $this, 'maybe_set_cookie' ], 20 );

	}

	/**
	 * Method to check if all in order before callback is evaluated
	 *
	 * @return bool Returns TRUE if all in order else FALSE
	 */
	protected function _is_pre_flight_ok() : bool {

		if ( empty( $this->_map ) ) {
			return false;
		}

		if ( empty( $this->_callback ) || ! is_callable( $this->_callback ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Method to sanitize bucket names
	 *
	 * @param string $bucket_name
	 *
	 * @return string
	 */
	protected function _sanitize_bucket_names( string $bucket_name ) : string {

		$bucket_name = str_replace( self::COOKIE_VALUES_SEPERATOR, ' ', $bucket_name );

		return trim( $bucket_name );

	}

	/**
	 * Method to run the specified callback and return its output
	 *
	 * @return mixed Value returned from the callback
	 *
	 * @throws \ErrorException
	 */
	protected function _get_callback_output() {

		if ( empty( $this->_callback ) ) {
			throw new ErrorException(
				sprintf(
					'%s::%s() expects a valid callback to be available',
					__CLASS__,
					__FUNCTION__
				)
			);
		}

		$callback_output = call_user_func_array( $this->_callback, $this->_callback_parameters );

		if ( is_bool( $callback_output ) ) {
			$callback_output = ( true === $callback_output ) ? 'true' : 'false';
		}

		return $callback_output;

	}

	/**
	 * Method to evaluate callback output and set buckets as per output
	 *
	 * @return bool Returns TRUE on success else FALSE
	 *
	 * @throws \ErrorException
	 */
	protected function _execute_plan() : bool {

		if ( ! $this->_is_pre_flight_ok() ) {
			// Pre-flight checks failed
			throw new ErrorException( 'An output to cache-bucket map and a valid callback must be set before evaluation can be done' );
		}

		$bucket_names    = [];
		$callback_output = $this->_get_callback_output();

		if ( isset( $this->_map[ $callback_output ] ) ) {
			$bucket_names = $this->_map[ $callback_output ];
		} elseif ( isset( $this->_map['default'] ) ) {
			$bucket_names = $this->_map['default'];
		} else {
			// invalid output returned by callback
			// and no default specified
			// so lets set global default
			$bucket_names = self::CACHE_BUCKET_DEFAULT;
		}

		$this->_set_buckets( (array) $bucket_names );

		$this->_earmark_bucket_names();

		$this->_data_to_return = [
			'callback_output' => $callback_output,
			'cache_buckets'   => (array) $bucket_names,
		];

		return true;

	}

	/**
	 * Method to mark temporary cache bucket names as ready for final use
	 *
	 * @return void
	 */
	protected function _earmark_bucket_names() : void {

		if ( empty( $this->_temporary_cache_buckets ) ) {
			return;
		}

		$this->_cache_buckets = array_merge( $this->_cache_buckets, $this->_temporary_cache_buckets );

	}

	/**
	 * Method to cleanup temporary values like temporary cache buckets, callback, callback parameters
	 *
	 * @return void
	 */
	protected function _cleanup_temporary_data() : void {

		// empty out the temp var, don't need these values anymore
		// and we don't want another callback to be able to run on same value(s)
		$this->_temporary_cache_buckets = [];
		$this->_map                     = [];
		$this->_data_to_return          = [];

		$this->_callback            = '';
		$this->_callback_parameters = [];

	}

	/**
	 * Filter to always purge surrogate keys
	 * @param  array $keys the existing surrogate keys
	 * @return array
	 */
	public function maybe_add_fastly_always_purged_types( $keys ) : array {
		if ( ! is_array( $keys ) ) {
			$keys = [];
		}

		if ( ! in_array( 'tm-feed', (array) $keys, true ) ) {
			$keys[] = 'tm-feed';
		}

		// Temporary alwasy trigger cache purge on archive template
		// @TODO: remove this when re-fine trigger and surrogate keys are add via maybe_add_fastly_surrogate_keys & maybe_add_fastly_related_surrogate_keys
		if ( ! in_array( 'tm-archive', (array) $keys, true ) ) {
			$keys[] = 'tm-archive';
		}

		return $keys;
	}

	public function maybe_add_fastly_template_keys( $keys ) : array {
		if ( ! is_array( $keys ) ) {
			$keys = [];
		}

		if ( is_single() ) {
			$post_type = get_post_type();
			if ( in_array( $post_type, [ '_pmc-custom-feed', 'pmc-custom-feed' ], true ) ) {
				$keys = [ 'tm-feed' ];
			}
		} elseif ( is_post_type_archive() ) {
			$post_type = get_query_var( 'post_type' );
			if ( in_array( $post_type, [ 'pmc-gallery' ], true ) ) {
				$keys = [ 'tm-gallery' ];
			}
		}

		return $keys;
	}

	/**
	 * Filter to add additional surrogate key to allow cache purge triggering
	 * @related to function maybe_add_fastly_related_surrogate_keys
	 *
	 * @param  array $keys the existing surrogate keys
	 * @return array
	 */
	public function maybe_add_fastly_surrogate_keys( $keys ) : array {
		if ( ! is_array( $keys ) ) {
			$keys = [];
		}

		if ( is_archive() ) {
			if ( is_year() ) {
				// Generate keys: y-####
				$key = 'y-' . get_query_var( 'year' );
				if ( ! in_array( $key, (array) $keys, true ) ) {
					$keys[] = $key;
				}
			}
		}

		return $keys;
	}


	/**
	 * Filter to add surrogate key to trigger cache purge during post save
	 * @related to function maybe_add_fastly_surrogate_keys
	 *
	 * @param  array $keys the existing surrogate keys
	 * @return array
	 */
	public function maybe_add_fastly_related_surrogate_keys( $keys, $post ) : array {
		if ( ! is_array( $keys ) ) {
			$keys = [];
		}

		$post = get_post( $post );

		if ( ! empty( $post ) ) {

			// Generate keys: y-####
			$key = 'y-' . get_the_date( 'Y', $post );
			if ( ! in_array( $key, (array) $keys, true ) ) {
				$keys[] = $key;
			}

		}

		return $keys;
	}

	/**
	 * This method is called on 'send_headers' hook and sets the cookie for cache bucket
	 * if one or more have been specified.
	 *
	 * @return void
	 */
	public function maybe_set_cookie() : void {

		if ( empty( $this->_cache_buckets ) ) {
			return;
		}

		$domain = wp_parse_url( get_home_url(), PHP_URL_HOST );

		PMC_Cookie::get_instance()->set_unsigned_cookie(
			self::COOKIE_NAME,
			implode( self::COOKIE_VALUES_SEPERATOR, $this->_cache_buckets ),
			0,
			'/',
			$domain,
			false,
			false,
			false
		);

		// empty out cache buckets var as it is not needed anymore
		// and this method should not run again in same execution cycle
		$this->_cache_buckets = [];

	}

	/**
	 * This method removes the cache bucket cookie
	 *
	 * @return \PMC\Global_Functions\Service\CDN_Cache
	 */
	public function remove_cookie() : CDN_Cache {

		$domain = wp_parse_url( get_home_url(), PHP_URL_HOST );

		/*
		 * Set expiry to one month in past because PHP does not actually has cookie removal mechanism
		 * and this is the way to tell browser that this particular cookie has expired.
		 */
		$expiry_time = strtotime( '-1 month' );

		PMC_Cookie::get_instance()->set_unsigned_cookie(
			self::COOKIE_NAME,
			'',
			$expiry_time,
			'/',
			$domain,
			false,
			false,
			false
		);

		return $this;

	}

	/**
	 * Method to set cache bucket names
	 *
	 * @param array $bucket_names
	 *
	 * @return void
	 */
	protected function _set_buckets( array $bucket_names ) : void {

		$bucket_names = array_map( [ $this, '_sanitize_bucket_names' ], array_values( $bucket_names ) );

		$this->_temporary_cache_buckets = array_merge(
			$this->_temporary_cache_buckets,
			array_filter( $bucket_names )
		);

	}

	/**
	 * Method to set the callback output and cache buckets map
	 *
	 * @param array $map An associative array with 1:1 mapping of possible callback output and corresponding buckets to set
	 *
	 * @return \PMC\Global_Functions\Service\CDN_Cache
	 */
	public function map_buckets( array $map ) : CDN_Cache {

		$this->_map = $map;

		return $this;

	}

	/**
	 * Method to set callback whose evaluation determines whether to set cache bucket or not
	 *
	 * @param callable $callback
	 * @param array    $callback_parameters
	 *
	 * @return \PMC\Global_Functions\Service\CDN_Cache
	 */
	public function for_callback( callable $callback, array $callback_parameters = [] ) : CDN_Cache {

		$this->_callback            = $callback;
		$this->_callback_parameters = $callback_parameters;

		return $this;

	}

	/**
	 * Method which earmarks cache bucket(s) for cookie if callback evaluates to any of the provided outputs in the map
	 *
	 * @return array Returns an associative array containing callback output and cache buckets set on success else an empty array on failure.
	 *
	 * @throws \ErrorException
	 */
	public function per_evaluation() : array {

		$this->_execute_plan();

		$data_to_return = $this->_data_to_return;

		$this->_cleanup_temporary_data();

		return $data_to_return;

	}

}    // end class


//EOF
