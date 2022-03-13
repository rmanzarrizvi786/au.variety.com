<?php
/**
 * Class containing utilities to deal with attachments
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-07-29
 */

namespace PMC\Global_Functions\Utility;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC_Cache;
use \PMC;


class Attachment {

	use Singleton;

	const CACHE_LIFE  = 3600;  // 1 hour
	const CACHE_GROUP = 'pmc-attachments-v2';

	/**
	 * Method to get a cleaned up URL without any query string or fragment
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	protected function _get_clean_url( string $url ) : string {

		$clean_url = '';

		if ( empty( $url ) ) {
			return $clean_url;
		}

		$clean_url = $url;

		$clean_url = explode( '#', $clean_url );
		$clean_url = array_shift( $clean_url );

		$clean_url = explode( '?', $clean_url );
		$clean_url = array_shift( $clean_url );

		return trim( $clean_url );

	}

	/**
	 * Method to get file name (without extension) from a URL
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	protected function _get_file_name( string $path ) : string {

		$file_name = '';

		if ( empty( $path ) ) {
			return $file_name;
		}

		$file_name = pathinfo( $path, PATHINFO_FILENAME );
		$file_name = ( empty( $file_name ) || ! is_string( $file_name ) ) ? '' : $file_name;

		return $file_name;

	}

	/**
	 * Method to check if URL is of current file domain or not.
	 * On classic VIP it differs from the site domain while it is same as site domain
	 * on VIP Go & self hosted sites. This method accounts for all cases.
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	protected function _is_url_of_current_domain( string $url ) : bool {

		if ( empty( $url ) ) {
			return false;
		}

		$current_domain = '';

		if ( PMC::is_classic_vip_production() ) {
			$current_domain = ( function_exists( 'wpcom_get_blog_files_url' ) ) ? wpcom_get_blog_files_url() : '';
		}

		if ( empty( $current_domain ) ) {
			$current_domain = home_url();
		}

		$current_domain = wp_parse_url( $current_domain, PHP_URL_HOST );
		$url_domain     = wp_parse_url( $url, PHP_URL_HOST );

		if ( $current_domain === $url_domain ) {
			return true;
		}

		return false;

	}

	/**
	 * Method to get the file name slug to search
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	protected function _get_slug_to_search( string $url ) : string {

		$search_token = '';

		if ( empty( $url ) ) {
			return $search_token;
		}

		$file_name = $this->_get_file_name( $url );

		if ( empty( $file_name ) ) {
			return $search_token;
		}

		$search_token = $file_name;
		$pattern      = '/(-?\d+)$/';
		$matches      = [];

		/*
		 * If there is any number at the end of the string then
		 * we want to remove that number.
		 *
		 * There are weird cases where file slug would be like
		 * 'abc2' & attachment slug would be 'abc-4', which is
		 * why regex is used here instead of exploding it on hyphen.
		 */
		if (
			1 === (int) preg_match( $pattern, $search_token, $matches )
			&& isset( $matches[1] ) && 0 < strlen( $matches[1] )
		) {

			$search_token = substr(
				$search_token,
				0,
				PMC::get_negative_int( strlen( $matches[1] ) )
			);

		}

		return $search_token;

	}

	/**
	 * Method to search for a file URL using Elastic Search and to get its corresponding Attachment ID
	 *
	 * @param string $url
	 *
	 * @return int
	 */
	protected function _get_postid_from_url_via_es( string $url ) : int {

		$post_id = -1;

		if ( empty( $url ) ) {
			return $post_id;
		}

		$url = set_url_scheme( $url, 'https' );    // lets make sure URL has protocol

		if ( strpos( $url, '://' ) === false ) {

			// relative URLs without host are of no use to us
			// bail out
			return $post_id;

		}

		$url          = substr( $url, 8 );    // we don't need transport protocol anymore
		$site_id      = 0;    // assume for failure
		$request_data = (object) [
			'from'   => 0,
			'size'   => 5,
			'fields' => [
				'post_id',
				'url',
			],
			'query'  => (object) [
				'constant_score' => (object) [
					'filter' => (object) [
						'bool' => (object) [
							'must' => [
								(object) [
									'term' => (object) [
										'url.raw' => $url,
									],
								],
							],
						],
					],
				],
			],
		];

		$request_data = wp_json_encode( $request_data );

		if ( PMC::is_classic_vip_production() ) {
			$site_id = get_current_blog_id();
		} else {
			$jp_options = get_option( 'jetpack_options' );
			$site_id    = intval( $jp_options['id'] );

			unset( $jp_options );
		}

		if ( 1 > $site_id ) {

			// without site ID we can't query ES
			// bail out
			return $post_id;

		}

		$api_url  = sprintf( 'https://public-api.wordpress.com/rest/v1/sites/%d/search', intval( $site_id ) );
		$response = wp_remote_post( $api_url, [ 'body' => $request_data ] );

		if ( empty( $response['body'] ) ) {

			// bad response received from ES
			// bail out
			return $post_id;

		}

		$response = json_decode( $response['body'] );

		if ( isset( $response->results->total ) && 0 < intval( $response->results->total ) ) {

			$results = ( empty( $response->results->hits ) ) ? [] : $response->results->hits;

			foreach ( $results as $post ) {

				if (
					! empty( $post->fields->url ) && $post->fields->url === $url
					&& ! empty( $post->fields->post_id ) && 0 < intval( $post->fields->post_id )
				) {
					$post_id = intval( $post->fields->post_id );
					break;
				}

			}

		}

		unset( $site_id, $request_data, $api_url, $response );

		return intval( $post_id );

	}

	/**
	 * Method to get attachment post ID from an attachment file URL.
	 * This is an uncached version and should not be used directly.
	 *
	 * This method is an alternate for wpcom_vip_attachment_url_to_postid()
	 * with a fallback to attachment_url_to_postid()
	 * because VIP method has 12+ hour cache and does not work well with wp-cli commands.
	 *
	 * @see https://wordpressvip.zendesk.com/hc/en-us/requests/96080
	 *
	 * @param string $url
	 *
	 * @return int Returns -1 if the URL passed fails preliminary checks, 0 if no post ID found else it returns the post ID associated with attachment file URL
	 */
	public function get_postid_from_url_uncached( string $url ) : int {

		$post_id = -1;

		if ( empty( $url ) ) {
			return $post_id;
		}

		$clean_url = $this->_get_clean_url( $url );

		if ( empty( $clean_url ) ) {
			return $post_id;
		}

		/*
		 * If the domain of attachment URL does not match current site's domain
		 * then its not an attachment on current site and we will save
		 * our expensive hit to the DB.
		 */
		if ( ! $this->_is_url_of_current_domain( $clean_url ) ) {
			return $post_id;
		}

		$file_name = $this->_get_slug_to_search( $clean_url );

		if ( empty( $file_name ) ) {
			return $post_id;
		}

		if ( empty( $GLOBALS['wpdb'] ) || ! is_object( $GLOBALS['wpdb'] ) ) {
			return $post_id;
		}

		$file_name .= '%';    // append wildcard

		$clean_url_http  = set_url_scheme( $clean_url, 'http' );
		$clean_url_https = set_url_scheme( $clean_url, 'https' );

		/*
		 * This has been used intentionally instead of wpcom_vip_attachment_url_to_postid()
		 * because that method has 12+ hour cache.
		 */
		$sql = sprintf(
			"SELECT ID, guid FROM %s WHERE post_type = 'attachment' AND post_name LIKE %%s LIMIT 20",
			$GLOBALS['wpdb']->posts
		);

		$results = $GLOBALS['wpdb']->get_results(
			$GLOBALS['wpdb']->prepare( $sql, $file_name )
		);

		if ( ! empty( $results ) && is_array( $results ) ) {

			foreach ( $results as $row ) {

				if ( $row->guid === $clean_url_http ) {

					$post_id = intval( $row->ID );

					break;    // found our ID, break out of loop

				}

				if ( $row->guid === $clean_url_https ) {

					$post_id = intval( $row->ID );

					break;    // found our ID, break out of loop

				}

			}    // end foreach loop

		}

		if ( 1 > $post_id ) {

			/*
			 * This is our fallback option since this is a bit slower than doing lookup
			 * on post_name and matching guid in PHP.
			 *
			 * Ignoring this block as it has its own separate unit test.
			 */
			$post_id = (int) $this->_get_postid_from_url_via_es( $clean_url_http );    // @codeCoverageIgnore

		}

		return $post_id;

	}

	/**
	 * Method to get attachment post ID from an attachment file URL
	 *
	 * @param string $url
	 *
	 * @return int
	 */
	public function get_postid_from_url( string $url ) : int {

		$post_id = -1;

		if ( empty( $url ) ) {
			return $post_id;
		}

		$clean_url = $this->_get_clean_url( $url );

		if ( empty( $clean_url ) ) {
			return $post_id;
		}

		$cache = new PMC_Cache( $clean_url, self::CACHE_GROUP );

		$post_id = $cache
						->expires_in( self::CACHE_LIFE )
						->updates_with(
							[ $this, 'get_postid_from_url_uncached' ],
							[ $clean_url ]
						)
						->get();

		return intval( $post_id );

	}

	/**
	 * Retrieve image credit.
	 *
	 * @param int $image_id Image ID.
	 * @return string|null
	 */
	public function get_image_credit( int $image_id ): ?string {
		$credit = get_post_meta( $image_id, '_image_credit', true );

		return is_string( $credit ) && ! empty( $credit ) ? $credit : null;
	}

}    //end class

//EOF
