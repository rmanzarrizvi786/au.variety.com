<?php
/**
 * File contains class for Variety Scorecard API.
 *
 * CDWE-477 -- Copied from pmc-variety-2014 theme.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-08-21
 */

use \PMC\Global_Functions\Traits\Singleton;

class Variety_Scorecard_API {

	use Singleton;

	const CACHE_LIFE = 900;    // 15 minutes

	/**
	 * Url for records.
	 *
	 * @var string API URL.
	 */
	private $_url_records = '';

	/**
	 * Url for networks.
	 *
	 * @var string API URL.
	 */
	private $_url_networks = '';

	/**
	 * Url for genre.
	 *
	 * @var string API URL.
	 */
	private $_url_genre = '';

	/**
	 * Url for status.
	 *
	 * @var string API URL.
	 */
	private $_url_status = '';

	/**
	 * Class Initialization.
	 */
	protected function __construct() {

		// Make instace of class.
		$scorecard           = Variety_Scorecard_Settings::get_instance();
		$this->_url_records  = $scorecard->get_option( 'url_get_records' );
		$this->_url_networks = $scorecard->get_option( 'url_get_networks' );
		$this->_url_genre    = $scorecard->get_option( 'url_get_genre' );
		$this->_url_status   = $scorecard->get_option( 'url_get_status' );

	}

	/**
	 * Get data from API urls.
	 *
	 * @param string $tag  Name of current remote data call, which used for cache_key.
	 * @param string $url  API URL.
	 * @param bool   $args List of arguments.
	 *
	 * @return string|bool Returns response body otherwise false.
	 */
	public function get_remote_data( $tag = 'rows', $url, $args = false ) {

		if ( empty( $url ) ) {
			return false;
		}

		$cache_key = 'scorecard-records-' . $tag;

		$cache = new \PMC_Cache( $cache_key );

		$result = $cache->expires_in( self::CACHE_LIFE )
						->updates_with( array( $this, 'get_remote_records' ), array( $url, $args ) )
						->get();

		if ( ! empty( $result ) && ! is_wp_error( $result ) ) {
			return $result;
		}

		return false;
	}

	/**
	 * Get data from API urls.
	 *
	 * @param string $url  API URL.
	 * @param bool   $args List of arguments.
	 *
	 * @return string|bool Returns response body otherwise false.
	 */
	public function get_remote_records( $url, $args = false ) {

		if ( empty( $url ) ) {
			return false;
		}

		if ( is_array( $args ) ) {
			foreach ( $args as $key => $value ) {
				$refresh_key = sprintf( '#%s#', $key );
				$url         = str_replace( $refresh_key, $value, $url );
			}
		}

		$options = array(
			'sslverify'  => false,
			'decompress' => true,
		);

		$result = vip_safe_wp_remote_get( $url, '', 3, 3, 20, $options );

		if ( ! empty( $result ) && ! is_wp_error( $result ) && ! empty( $result['response'] ) && 200 === intval( $result['response']['code'] ) ) {
			return $result['body'];
		}

		return false;
	}

	/**
	 * Get modified time of data
	 *
	 * @return false|int Returns time if data is modified otherwise returns 0.
	 */
	public function get_modified_time() {

		$options = array(
			'page_size'       => Variety_Scorecard::PAGE_SIZE,
			'page'            => 1,
			'network_id'      => '',
			'network_type_id' => '',
			'status_id'       => '',
			'genre_id'        => '',
		);

		$bufs = $this->get_remote_data( 'rows', $this->_url_records, $options );

		if ( ! empty( $bufs ) ) {

			$result = json_decode( $bufs, true );

			if ( ! empty( $result ) ) {
				return strtotime( $result['modified'] );
			}
		}

		return 0;
	}

	/**
	 * Get records from API.
	 *
	 * @param array|bool $args List of arguments, defaults to false.
	 *
	 * @return array|mixed|null|object Returns records if found, otherwise returns null.
	 */
	public function get_records( $args = false ) {

		$options = array(
			'page'            => 1,
			'page_size'       => Variety_Scorecard::PAGE_SIZE,
			'network_id'      => '',
			'network_type_id' => '',
			'genre_id'        => '',
			'status_id'       => '',
		);

		if ( is_array( $args ) ) {
			$options = array_merge( $options, $args );
		}

		$options['page_size'] = intval( $options['page_size'] );
		if ( $options['page_size'] <= 0 ) {
			$options['page_size'] = Variety_Scorecard::PAGE_SIZE;
		}

		$options['page'] = intval( $options['page'] );
		if ( $options['page'] < 1 ) {
			$options['page'] = 1;
		}

		$cache_key = md5( "p{$options['page']}|ps{$options['page_size']}|n{$options['network_id']}|nt{$options['network_type_id']}|s{$options['status_id']}|g{$options['genre_id']}" );

		$bufs = $this->get_remote_data( $cache_key, $this->_url_records, $options );

		if ( ! empty( $bufs ) ) {
			return json_decode( $bufs, true );
		}

		return null;
	}

	/**
	 * Get list of networks.
	 *
	 * @return array|mixed|null|object Returns records if found, otherwise returns null.
	 */
	public function get_networks() {

		$bufs = $this->get_remote_data( 'networks', $this->_url_networks );

		if ( ! empty( $bufs ) ) {
			return json_decode( $bufs, true );
		}

		return null;
	}

	/**
	 * Get list of genre.
	 *
	 * @return array|mixed|null|object Returns records if found, otherwise returns null.
	 */
	public function get_genre() {

		$bufs = $this->get_remote_data( 'genre', $this->_url_genre );

		if ( ! empty( $bufs ) ) {
			return json_decode( $bufs, true );
		}

		return null;
	}

	/**
	 * Get list of statuses.
	 *
	 * @return array|mixed|null|object Returns records if found, otherwise returns null.
	 */
	public function get_status() {

		$bufs = $this->get_remote_data( 'status', $this->_url_status );

		if ( ! empty( $bufs ) ) {
			return json_decode( $bufs, true );
		}

		return null;
	}

}
