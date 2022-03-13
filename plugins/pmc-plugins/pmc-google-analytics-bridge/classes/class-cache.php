<?php
/**
 * Cache helper.
 *
 * @package pmc-google-analytics-bridge
 */

namespace PMC\Google_Analytics_Bridge;

use PMC_Cache;

class Cache {
	/**
	 * Cache group.
	 */
	protected const CACHE_GROUP = 'pmc-ga-bridge';

	/**
	 * Hook to invoke to build cached data.
	 *
	 * @var string
	 */
	protected $_hook;

	/**
	 * Google Analytics API arguments.
	 *
	 * @var array
	 */
	protected $_ga_api_args;

	/**
	 * Cache life in minutes.
	 *
	 * @var int
	 */
	protected $_cache_life_minutes;

	/**
	 * Should cache be rebuilt?
	 *
	 * @var bool
	 */
	protected $_refresh_cache = false;

	/**
	 * Cache constructor.
	 *
	 * @param string $hook               Hook to invoke to build cached data.
	 * @param array  $ga_api_args        Google Analytics API arguments.
	 * @param int    $cache_life_minutes Cache life in minutes.
	 */
	public function __construct(
		string $hook,
		array $ga_api_args,
		int $cache_life_minutes
	) {
		$this->_hook               = $hook;
		$this->_ga_api_args        = $ga_api_args;
		$this->_cache_life_minutes = $cache_life_minutes;

		Cron::schedule( $hook, $ga_api_args, $cache_life_minutes );
	}

	/**
	 * Retrieve value from cache, or fallback if cache is unavailable.
	 *
	 * @return mixed
	 * @throws \ErrorException Invalid cache-update callback.
	 */
	public function get() {
		$cache = new PMC_Cache(
			$this->_get_cache_key(),
			static::CACHE_GROUP
		);

		$cache->expires_in( $this->_cache_life_minutes * MINUTE_IN_SECONDS );

		$cache->updates_with( [ $this, 'get_data_for_cache' ] );

		if ( $this->_refresh_cache ) {
			$cache->invalidate();
		}

		return $cache->get();
	}

	/**
	 * Trigger cache rebuild via cron.
	 */
	public function rebuild_cache(): void {
		if ( ! wp_doing_cron() ) {
			return;
		}

		$this->_refresh_cache = true;

		$this->get();
	}

	/**
	 * Retrieve data to cache.
	 *
	 * @return mixed
	 */
	public function get_data_for_cache() {
		if ( ! wp_doing_cron() ) {
			return $this->_get_fallback();
		}

		$data = apply_filters( $this->_hook, [], $this->_ga_api_args );

		$this->_set_fallback( $data );

		return $data;
	}

	/**
	 * Build cache key.
	 *
	 * @return string
	 */
	protected function _get_cache_key(): string {
		return sprintf(
			'%1$s %2$s %3$d',
			$this->_hook,
			wp_json_encode( $this->_ga_api_args ),
			$this->_cache_life_minutes
		);
	}

	/**
	 * Retrieve fallback data.
	 *
	 * @return mixed
	 */
	protected function _get_fallback() {
		return get_option( $this->_get_fallback_key(), [] );
	}

	/**
	 * Store fallback data.
	 *
	 * @param mixed $data Cache data.
	 */
	protected function _set_fallback( $data ) {
		if ( empty( $data ) || is_wp_error( $data ) ) {
			return;
		}

		update_option(
			$this->_get_fallback_key(),
			$data,
			false
		);
	}

	/**
	 * Build key for fallback cache, stored in options table.
	 *
	 * @return string
	 */
	protected function _get_fallback_key(): string {
		return sprintf(
			'%1$s-%2$s',
			static::CACHE_GROUP,
			md5( $this->_get_cache_key() )
		);
	}
}
