<?php

/**
 * Class Cache  - Oriel Cache, implemented using WP Options API
 */
class Cache {

	private static $_instance;

	/**
	 *
	 * @param mixed $key    Key to be stored
	 * @param mixed $value  Value to be stored
	 * @param int   $expire Expiry time in seconds
	 * @param bool  $commit True to commit to DB, useful for batched sets
	 */
	public function set( $key, $value, $expire = 0, $commit = true ) {
		$t_key = 'oriel_' . $key;
		return set_transient( $t_key, $value, $expire );
	}

	/**
	 *
	 * @param  mixed $key           Key to be fetched
	 * @param  mixed $default_value Default value
	 * @return mixed Value found in cache or default value if passed or null
	 */
	public function get( $key, $default_value = null ) {
		$t_key = 'oriel_' . $key;

		$t_value = get_transient( $t_key );
		if ( false !== $t_value ) {
			return $t_value;
		}
		return $default_value;
	}

	/**
	 *
	 * @param  mixed $key    Key to be deleted
	 * @param  bool  $commit True to commit to DB, useful for batched deletes
	 * @return bool True if key found
	 */
	public function delete( $key, $commit = true ) {
		$t_key = 'oriel_' . $key;
		return delete_transient( $t_key );
	}

	/**
	 * Clears all cache
	 */
	public function erase() {

	}

	/**
	 *
	 * @return OrielCache Singleton instance
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

global $cache;

$cache = Cache::instance();


