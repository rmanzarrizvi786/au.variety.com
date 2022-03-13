<?php
/*
 * This class is to simplify the caching of data returned by a method. If the
 * data is already in cache then that cached data is returned else it fetches
 * the data from the method, stores it in cache and returns it. So instead of
 * making multiple calls to check for cache and then generating data and
 * caching it, a chain of methods can be called instead and this
 * class does all the cache validity and data generation etc.
 *
 * This class uses wp_cache for the caching purpose.
 *
 * @since 2013-04-19 Amit Gupta
 */

class PMC_Cache {

	const error_code = 'pmc_cache';

	/**
	 * @var int Max number of failed attempts allowed after which cache expiry is set to normal
	 */
	const MAX_FAILED_ATTEMPTS_ALLOWED = 5;

	/**
	 * @var string
	 */
	private $cache_group = 'pmc_cache_v1';

	/**
	 * @var string Original key passed to create cache object
	 */
	protected $_unscrambled_key = '';

	/**
	 * @var string Key hash which is used to save/get cache
	 */
	protected $_key = '';

	/**
	 * @var string Key for use to store number of failed attempts
	 */
	protected $_attempt_key = '';

	/**
	 * @var string Suffix for use to store number of failed attempts
	 */
	protected $_attempt_key_suffix = 'attempts';

	/**
	 * @var int Cache expiry in seconds
	 */
	protected $_expiry = 900;    //15 minutes, default expiry

	/**
	 * @var int Cache expiry in seconds in case of failure
	 */
	protected $_expiry_on_failure = 120;    //2 minutes, default expiry in case of failure

	/**
	 * @var callable Callback which will be used to get uncached data
	 */
	protected $_callback;

	/**
	 * @var array Parameters to be passed to callback to get uncached data
	 */
	protected $_params = [];

	/**
	 * Class constructor
	 *
	 * @param string $cache_key
	 * @param string $cache_group
	 *
	 * @throws \ErrorException
	 */
	public function __construct( $cache_key = '', $cache_group = '' ) {

		if ( empty( $cache_key ) || ! is_string( $cache_key ) ) {

			throw new \ErrorException(
				__( 'A non-empty string cache key is required to create PMC_Cache object', 'pmc-global-functions' )
			);

		}

		$this->_unscrambled_key = $cache_key;
		$this->_key             = md5( $cache_key );
		$this->_attempt_key     = md5(
			sprintf(
				'%s_%s',
				$cache_key,
				$this->_attempt_key_suffix
			)
		);

		$this->cache_group = ( ! empty( $cache_group ) && is_string( $cache_group ) ) ? $cache_group : $this->cache_group;

		//call init
		$this->_init();

	}

	/**
	 * Init method to allow cache override via filter
	 *
	 * @return void
	 */
	protected function _init() : void {

		// NOTE!!!
		// Be very careful filtering this cache_group value!!
		// All uses of PMC_Cache use this group!!
		// Check the passed $_key value to ensure you're only
		// filtering the group for your cache instance or similar.
		$cache_group = apply_filters( 'pmc_cache_group_override', $this->cache_group, $this->_key );

		if ( ! empty( $cache_group ) && is_string( $cache_group ) ) {
			$this->cache_group = $cache_group;
		}

		unset( $cache_group );

		// Allow local dev debug and mocking of cache data
		do_action( 'pmc_cache_init', $this );
	}

	/**
	 * Method to get expiry duration in seconds with random number added to prevent race conditions.
	 *
	 * @param int $expiry
	 *
	 * @return int
	 */
	protected function _get_randomized_expiry( int $expiry = 0 ) : int {

		if ( is_callable( 'wp_rand' ) ) {
			$expiry += wp_rand( 1, 90 );

			// We can't cover this code due to wp_rand exist
		} elseif ( is_callable( 'random_int' ) ) {  // @codeCoverageIgnore
			// We can't cover this code due to wp_rand exist
			$expiry += random_int( 1, 90 ); // @codeCoverageIgnore
		}

		return $expiry;

	}

	/**
	 * This function is for deleting the cache
	 */
	public function invalidate() : self {

		wp_cache_delete( $this->_key, $this->cache_group );
		wp_cache_delete( $this->_attempt_key, $this->cache_group );

		return $this;

	}

	/**
	 * Method to set the cache expiry in seconds
	 *
	 * @param int $expiry
	 *
	 * @return \PMC_Cache
	 */
	public function expires_in( $expiry ) : self {

		$expiry = intval( $expiry );

		$this->_expiry = ( 0 < $expiry ) ? $expiry : $this->_expiry;
		$this->_expiry = $this->_get_randomized_expiry( $this->_expiry );

		return $this;

	}

	/**
	 * This method is to set a shorter expiry in case data fetch fails so as to try fetching data quicker than waiting for full expiry
	 *
	 * @param int $expiry
	 *
	 * @return \PMC_Cache
	 */
	public function on_failure_expiry_in( int $expiry = 0 ) : self {

		$this->_expiry_on_failure = ( 0 < $expiry ) ? $expiry : $this->_expiry_on_failure;
		$this->_expiry_on_failure = $this->_get_randomized_expiry( $this->_expiry_on_failure );

		return $this;

	}

	/**
	 * Method to return the number of failed attempts made.
	 *
	 * @return int
	 */
	public function get_failed_attempts_count() : int {

		$count = wp_cache_get( $this->_attempt_key, $this->cache_group );
		$count = ( empty( $count ) ) ? 0 : absint( $count );

		return $count;

	}

	/**
	 * Method to determine if we have maxed out on failed attempts or not.
	 *
	 * @param int $count
	 *
	 * @return bool
	 */
	protected function _has_failed_attempts_maxed_out( int $count ) : bool {

		return (bool) ( self::MAX_FAILED_ATTEMPTS_ALLOWED < $count );

	}

	/**
	 * Method to set failed attempt count.
	 *
	 * @return void
	 */
	protected function _set_failed_attempt_count( int $count = 0 ) : void {

		// Ignoring on PHPCS because cache expiry defaults to >900 seconds
		wp_cache_set( $this->_attempt_key, $count, $this->cache_group, $this->_expiry );    // phpcs:ignore

	}

	/**
	 * This function accepts the callback from which data is to be received
	 *
	 * @param callable $callback
	 * @param array    $params
	 *
	 * @return \PMC_Cache|\WP_Error
	 */
	public function updates_with( $callback, $params = [] ) {

		if ( empty( $callback ) || ! is_callable( $callback ) ) {

			return new WP_Error(
				self::error_code,
				__( 'Callback passed is not callable', 'pmc-global-functions' )
			);

		}

		if ( ! is_array( $params ) ) {

			return new WP_Error(
				self::error_code,
				__( 'All parameters for the callback must be in an array', 'pmc-global-functions' )
			);

		}

		$this->_callback = $callback;
		$this->_params   = $params;

		return $this;

	}

	/**
	 * Method to get data from cache
	 *
	 * @return bool|mixed
	 */
	protected function _get_data_from_cache() {

		// $this->get() must operate on the raw value, as noted therein.
		return wp_cache_get( $this->_key, $this->cache_group );

	}

	/**
	 * Method to get data from callback
	 *
	 * @return bool|mixed
	 */
	protected function _get_data_from_callback() {

		try {

			$data = call_user_func_array( $this->_callback, $this->_params );
			$data = ( empty( $data ) ) ? false : $data;

		} catch ( \Exception $e ) {
			$data = false;
		}

		return $data;

	}

	/**
	 * Method to save data in WP cache
	 *
	 * @param mixed $data
	 *
	 * @return void
	 */
	protected function _set_data_in_cache( $data ) : void {

		$expiry = $this->_expiry;

		if ( empty( $data ) ) {

			$failed_attempts = $this->get_failed_attempts_count();
			$failed_attempts++;

			$expiry = $this->_expiry_on_failure;

			if ( $this->_has_failed_attempts_maxed_out( $failed_attempts ) ) {
				$failed_attempts = 0;
				$expiry          = $this->_expiry;
			}

			$this->_set_failed_attempt_count( $failed_attempts );

			$data = 'empty';

		}

		// Ignoring on PHPCS because cache expiry defaults to >900 seconds
		wp_cache_set( $this->_key, $data, $this->cache_group, $expiry );    // phpcs:ignore

	}

	/**
	 * This function returns the data from cache if it exists or returns the
	 * data it gets back from the callback and caches it as well
	 */
	public function get() {

		$data = $this->_get_data_from_cache();

		/**
		 * To disambiguate a boolean false returned by the cache callback from one
		 * indicating that `wp_cache_get()` found nothing, we use the string
		 * `empty` as a proxy.
		 *
		 * This logic cannot live in $this->_get_data_from_cache().
		 */
		if ( 'empty' === $data ) {
			return false;
		}
		if ( false !== $data ) {
			return $data;
		}

		//If we don't have a callback to get data from or if its not a valid
		//callback then return error. This will happen in the case when
		//updates_with() is not called before get()
		if ( empty( $this->_callback ) || ! is_callable( $this->_callback ) ) {

			return new WP_Error(
				self::error_code,
				__( 'No valid callback set', 'pmc-global-functions' )
			);

		}

		$data = $this->_get_data_from_callback();

		$this->_set_data_in_cache( $data );

		return $data;

	}

}    //end of class

//EOF
