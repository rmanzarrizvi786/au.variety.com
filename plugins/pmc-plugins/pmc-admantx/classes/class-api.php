<?php
namespace PMC\Admantx;

use PMC\Global_Functions\Traits\Singleton;
use PMC_Options;
use PMC_Cheezcap;

class Api {
	use Singleton;

	const API_KEY    = 'dcde31e32d21f5432ab192ee50f9e0a8ef294bab4778ada95dfabeb949b2b9ce';
	const REMOTE_URI = 'https://async01.admantx.com/admantx/service';

	const RESPONSE_FAIL_DISABLED  = [ 'fail', 'fail_disabled' ];
	const RESPONSE_FAIL_ERROR     = [ 'fail', 'fail_error' ];
	const RESPONSE_FAIL_NOT_READY = [ 'fail', 'fail_not_ready' ];
	const RESPONSE_FAIL_PENDING   = [ 'fail', 'fail_pending' ];
	const RESPONSE_NONE           = [ 'none' ];

	const FAILED_CACHE_DURATION     = MINUTE_IN_SECONDS;
	const FAILED_THRESHOLD_DURATION = 6 * HOUR_IN_SECONDS;

	const CACHE_META_NAME = '_pmc_admantx_cache';
	const CACHE_GROUP     = 'pmc_admantx_cache';

	private $_post_id = false;
	private $_url     = false;

	/**
	 * Return the cache duration from cheezecap if available, if not return default
	 * @return int
	 */
	public function get_cache_duration() : int {
		$cache_duration = PMC_Cheezcap::get_instance()->get_option( Plugin::CHEEZCAP_OPTION_CACHE_DURATION );
		if ( empty( $cache_duration ) ) {
			$cache_duration = Plugin::DEFAULT_CACHE_DURATION;
		}
		return intval( $cache_duration );
	}

	/**
	 * Return true of plugin is enabled
	 * @return bool
	 */
	public function is_enabled() {
		return 'no' !== PMC_Cheezcap::get_instance()->get_option( Plugin::CHEEZCAP_OPTION_ENABLE );
	}

	/**
	 * Set the current post & url if post is valid
	 * Note: We do not want to restrict to WP_Post type here since we want to be able to auto validate the cache request
	 * and fallback to the default caching if post is invalid
	 * @param mixed $post
	 * @return $this
	 */
	public function set_post( $post ) : self {
		if ( ! empty( $post ) ) {
			$post = get_post( $post );
		}
		if ( ! empty( $post ) ) {
			$this->_post_id = intval( $post->ID );
			$this->_url     = get_permalink( $this->_post_id );
		} else {
			$this->_post_id = PHP_INT_MIN; // this post should not exists
			$this->_url     = false;
		}
		return $this;
	}

	/**
	 * Set the current url
	 * @param $url
	 * @return $this
	 */
	public function set_url( $url ) : self {
		$this->_url = $url;
		return $this;
	}

	/**
	 * Return the current post id
	 * @return int
	 */
	public function get_post_id() : int {
		if ( empty( $this->_post_id ) ) {
			// Detect current post
			if ( is_singular() ) {
				// Note: We want to use the queried object here and not get_post to avoid potential get_post might returned non main query post; like the home page, etc.
				$this->set_post( get_queried_object() );
			}
		}
		return (int) $this->_post_id;
	}

	/**
	 * Get the url link of the current post
	 * @return false|string
	 */
	public function get_url() : string {
		if ( empty( $this->_url ) ) {
			$post_id = $this->get_post_id();
			if ( empty( $post_id ) || PHP_INT_MIN === $post_id ) {
				$request_uri = \PMC::filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL );
				if ( ! empty( $request_uri ) ) {
					$this->_url = home_url( wp_parse_url( $request_uri, PHP_URL_PATH ), 'https' );
				}
			}
		}
		return (string) $this->_url;
	}

	/**
	 * Retrieve the data from cache, return result if found.
	 * When cache expired or not found, schedule the data fetch
	 *
	 * @param false $url
	 * @param int $post_id
	 * @return array
	 */
	public function get() : array {

		$data = false;

		if ( ! $this->is_enabled() ) {
			return self::RESPONSE_FAIL_DISABLED;
		}

		// Content on list pages changes often and is not targeted via contextual information anyway.
		if ( is_home() || is_front_page() || is_archive() || is_404() ) {
			return self::RESPONSE_FAIL_DISABLED;
		}

		$cache_data = $this->fetch_cache( $this->get_post_id(), $this->get_url() );
		if ( ! empty( $cache_data ) ) {
			if ( $cache_data['expiration'] < time() ) {
				// IMPORTANT: we want to continue serving the stalled cache
				// cache has expired, schedule the data fetch to refresh the cache via cron scheduler
				$this->schedule_fetch( 'expired' );
			}

			// return the existing cached data
			$data = $cache_data['data'];
		} else {

			// @NOTE: Transition code, try peek into PMC_Cache to see if we have any existing cache content
			$cache   = new \PMC_Cache( 'pmc_admantx:' . $this->get_url(), 'pmc_admantx' );
			$content = $cache->get();
			if ( ! empty( $content ) && ! is_wp_error( $content ) && is_array( $content ) ) {
				if ( 'fail' === $content[0] ) {
					return $this->_update_cache( $content, 'failed' );
				} else {
					return $this->_update_cache( $content, 'success' );
				}
			}

			// When cache is cold, it mean the article just publish and admantx might not crawled yet.
			// Therefore, it is better to always schedule delay cache refresh and serve response fail not ready
			$this->schedule_fetch( 'empty' );

		}

		// If data is not ready, return failed not ready state
		return ! empty( $data ) ? $data : self::RESPONSE_FAIL_NOT_READY;
	}

	/**
	 * Fetch the data via remote get and cache the result
	 * NOTE: This function is use by wp schedule event, must accept $post_id and/nor $url
	 *
	 * @param $url
	 * @param $post_id
	 * @return mixed|string[]
	 */
	public function fetch_data( $post_id = 0, $url = false ) : array {

		if ( ! empty( $post_id ) ) {
			$post = get_post( $post_id );
			if ( 'publish' !== $post->post_status ) {
				// There is a slight possibility the article might be delete after fetch schedule.
				return self::RESPONSE_FAIL_NOT_READY;
			}

			$this->set_post( $post_id );
		} else {
			$this->set_url( $url );
		}

		if ( empty( $this->_url ) ) {
			// This should never happen. A catch all condition.
			return self::RESPONSE_FAIL_NOT_READY;
		}

		$admantx_query = [
			'key'       => self::API_KEY,
			'decorator' => 'template.pmc',
			'filter'    => [ 'default' ],
			'method'    => 'descriptor',
			'mode'      => 'async',
			'type'      => 'URL',
			'body'      => $this->_url,
		];

		$args = [
			'request' => wp_json_encode( $admantx_query, JSON_UNESCAPED_SLASHES ),
		];

		$url      = add_query_arg( $args, self::REMOTE_URI );
		$response = wp_safe_remote_get( $url, [ 'timeout' => 3 ] );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return $this->_update_cache( self::RESPONSE_FAIL_ERROR, 'failed' );
		}

		$bufs = wp_remote_retrieve_body( $response );
		$bufs = trim( str_replace( [ 'pmc_admantx.callback(', ');' ], '', $bufs ) );
		$json = json_decode( $bufs, false );

		if ( ! empty( $json ) && 'ok' === strtolower( $json->status ) ) {

			if ( isset( $json->admants ) && ! is_array( $json->admants ) ) {
				return $this->_update_cache( self::RESPONSE_FAIL_ERROR, 'failed' );
			} elseif ( ! isset( $json->admants ) || 0 === count( $json->admants ) ) {
				return $this->_update_cache( self::RESPONSE_NONE, 'success' );
			}

			return $this->_update_cache( (array) $json->admants, 'success' );

		}

		if ( ! empty( $json ) && 'pending' === strtolower( $json->status ) ) {
			return $this->_update_cache( self::RESPONSE_FAIL_PENDING, 'failed' );
		}

		return $this->_update_cache( self::RESPONSE_FAIL_ERROR, 'failed' );

	}

	/**
	 * Helper function update the cache using post meta
	 * @param array $content
	 * @param string $status
	 * @return array
	 */
	private function _update_cache( array $content, string $status ) : array {

		$cache_data = $this->fetch_cache();

		if ( 'failed' === $status ) {

			if ( empty( $cache_data['first_failed'] ) || empty( $cache_data['failed_count'] ) ) {
				$cache_data = [
					'first_failed' => time(),
					'failed_count' => 0,
				];
			}

			if ( time() - $cache_data['first_failed'] > self::FAILED_THRESHOLD_DURATION ) {
				// If failed longer than the max threshold, we should switch to the default cache duration
				// We don't want to keep re-trying
				$cache_duration = $this->get_cache_duration();
			} else {
				$cache_duration = self::FAILED_CACHE_DURATION;
			}
			$cache_data['failed_count'] = (int) $cache_data['failed_count'] + 1;
			$cache_data['last_failed']  = time();

		} else {
			$cache_duration = $this->get_cache_duration();
			if ( empty( $cache_data['first_success'] ) ) {
				$cache_data['first_success'] = time();
			}
		}

		// Determine if we need to clear page cache base on existing cached entry
		$should_flush_page_cache = ! (
			is_array( $cache_data['data'] )
			&& count( $cache_data['data'] ) === count( $content )
			&& array_diff( $cache_data['data'], $content ) === array_diff( $content, $cache_data['data'] )
		);

		// We wan to serve the existing stalled cache if current content failed to retrieved from remote
		if ( empty( $cache_data['data'] ) || empty( $cache_data['status'] ) || 'failed' === $cache_data['status'] || 'failed' !== $status ) {
			$cache_data['data']         = $content;
			$cache_data['status']       = $status;
			$cache_data['last_updated'] = time();
		}

		$cache_data['expiration'] = time() + (int) $cache_duration;

		if ( ! empty( $this->_post_id ) && PHP_INT_MIN !== $this->_post_id ) {
			update_post_meta( $this->_post_id, self::CACHE_META_NAME, $cache_data );
			if ( $should_flush_page_cache && function_exists( 'wpcom_vip_purge_edge_cache_for_post' ) ) {
				wpcom_vip_purge_edge_cache_for_post( $this->_post_id );
			}
		} else {
			if ( ! empty( $this->_url ) ) {
				$cache_key = md5( $this->_url );
				// Fall back to PMC Options custom post type since we don't have a post id
				// Since we already disable home & archive page, there shouldn't be many custom template pages left
				PMC_Options::get_instance( self::CACHE_GROUP )->update_option( $cache_key, $cache_data );
			}
		}
		return (array) $content;
	}

	/**
	 * Fetch the cache from post meta or PMC_Options
	 * @return false|mixed
	 */
	public function fetch_cache() : array {
		if ( ! empty( $this->_post_id ) && PHP_INT_MIN !== $this->_post_id ) {
			$cache_data = get_post_meta( $this->_post_id, self::CACHE_META_NAME, true );
		} elseif ( ! empty( $this->_url ) ) {
			$cache_key  = md5( $this->_url );
			$cache_data = PMC_Options::get_instance( self::CACHE_GROUP )->get_option( $cache_key );
		}

		if ( empty( $cache_data ) ) {
			$cache_data = [];
		}

		return (array) $cache_data;
	}

	/**
	 * Schedule the data fetch at a later time for the current post
	 */
	public function schedule_fetch( $cache_state ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		// @TODO: future improvement by checking $cache_state to determine how to fetch the data, trigger cron refresh etc...
		// For now, schedule the fetch at shutdown event
		add_action( 'shutdown', [ $this, 'action_shutdown' ] );
	}

	/**
	 * Trigger cache refresh during shutdown
	 */
	public function action_shutdown() {
		$this->fetch_data( $this->get_post_id(), $this->get_url() );
	}

}

//EOF
