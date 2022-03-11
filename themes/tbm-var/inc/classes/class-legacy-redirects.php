<?php
/**
 * Add Legacy Redirects
 *
 * @since 2017-09-04 CDWE-583
 *
 * @author Chandra Patel <chandrakumar.patel@rtcamp.com>
 *
 * @package pmc-variety-2017
 */

namespace Variety\Inc;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

class Legacy_Redirects {

	use Singleton;

	protected $_original_request_uri = false;

	/**
	 * Construct method.
	 */
	protected function __construct() {

		$this->_original_request_uri = $_SERVER['REQUEST_URI']; // phpcs:ignore

		$this->_redirect_beta_urls();

		$this->_redirect_301();

		$this->_redirect_301_tag_redirection();

		// Earlier priority so that modified URLs are available to other filters.
		add_filter( 'wpcom_legacy_redirector_request_path', [ $this, 'strip_query_strings_from_legacy_urls' ], 5 );
	}

	/**
	 * Return the original REQUEST_URI before it was modified
	 * @return string
	 */
	public function original_request_uri() : string {
		return $this->_original_request_uri;
	}

	/**
	 * Redirect old URLs to new format
	 *
	 * OLD: /2013/vertical/category/postname
	 * NEW: /2013/vertical/category/postname-post_id/
	 *
	 * @since 2017-09-04 CDWE-583
	 *
	 * @see variety_redirect_beta_urls() in pmc-variety-2014/functions.php
	 *
	 * @return void
	 */
	protected function _redirect_beta_urls() {

		$url        = wp_parse_url( PMC::filter_input( INPUT_SERVER, 'REQUEST_URI' ) );
		$path_parts = explode( '/', trim( $url['path'], '/' ) );

		// Only redirect for article pages; check for year.
		if ( count( $path_parts ) > 2 && preg_match( '/^[0-9]{4}$/', $path_parts[0] ) && 'amp' !== end( $path_parts ) && 'maz' !== end( $path_parts ) ) {

			$post_name = end( $path_parts );

			/*
			 * Check if:
			 * - 2nd item in $path_parts is a number
			 * - $post_name has .html/.htm extension.
			 * If it has then bail out, we don't need this function messing with this URL
			 * as this URL is to be redirected to a post URL by VIP Legacy Redirect plugin
			 */
			if ( strlen( $path_parts[1] ) === 2 && is_numeric( $path_parts[1] ) && strpos( strtolower( $post_name ), '.htm' ) !== false ) {

				// bail out, not a variety URL (old or new)
				// and this needs to be redirected to correct URL.
				return;

			}

			// feed edge case - last part of the url is feed and not this function expects
			// so if last part of url is feed it uses the second last part to check for post ID.
			if ( 'feed' === $post_name ) {
				$post_name = $path_parts[ count( $path_parts ) - 2 ];
			}

			$post_parts = explode( '-', $post_name );

			// Override request URI so that WordPress handles the redirect.
			if ( ! is_numeric( end( $post_parts ) ) ) {

				$new_url = '?name=' . $post_name;

				if ( isset( $url['query'] ) ) {
					$new_url .= '&' . $url['query'];
				}

				$_SERVER['REQUEST_URI'] = '/' . $new_url;

				add_filter( 'wpcom_legacy_redirector_request_path', [ $this, 'original_request_uri' ] );
				add_filter( 'srm_requested_path', [ $this, 'original_request_uri' ] );

			}

		}

	}

	/**
	 * Redirection block for the site's 301 redirects
	 *
	 * @see redirect() in pmc-variety-2014/plugins/class-variety.php
	 */
	protected function _redirect_301() {

		if ( ! function_exists( 'vip_redirects' ) ) {
			return;
		}

		$vip_redirects_array = array(
			'/video'                   => '/v/video/',
			'/conferences'             => 'https://events.variety.com/',
			'/conference/'             => 'https://events.variety.com/',
			'/film'                    => '/v/film/',
			'/tv'                      => '/v/tv/',
			'/department/technology'   => '/v/digital/',
			'/department/music'        => '/v/music/',
			'/department/legit'        => '/v/legit/',
			'/latest-news'             => '/c/news/',
			'/events'                  => '/v/scene/',
			'/walkoffame'              => '/t/walk-of-fame-honor/',
			'/subscribetoday'          => 'https://www.pubservice.com/variety/default.aspx?PC=VY&PK=A5MVTDM/',
			'/basicsubscribe'          => 'https://www.pubservice.com/variety/default.aspx?PC=VY&PK=A5MVTBA/',
			'/freetote'                => 'https://www.pubservice.com/variety/default.aspx?PC=VY&PK=A5MVUDM/',
			'/basicprint'              => 'https://www.pubservice.com/variety/default.aspx?PC=VY&PK=A5MVUBA/',
			'/bonusandshow'            => 'https://docs.google.com/a/variety.com/spreadsheet/viewform?formkey=dGw3Vkh0bGF6YkN5NmJndUl2SE5wSHc6MA#gid=0',
			'/sundance'                => '/t/sundance/',
			'/berlin/'                 => '/t/berlin-film-festival/',
			'/people-news/'            => '/c/people-news/',
			'/people-news/obituaries'  => '/c/people-news/obituaries-people-news/',
			'/opinions/'               => '/v/voices/',
			'/gallery-listing/'        => '/gallery/',
			'/biography/2685/'         => '/author/erin-maxwell/',
			'/author/p-robert-marich/' => '/author/robert-marich/',
			'/biography/2588/'         => '/author/robert-marich/',
			'/subscribe-international' => 'https://www.pubservice.com/variety/default.aspx?PC=VY&PK=M0BI9IP',
			'/subscribe-canada'        => 'https://www.pubservice.com/variety/default.aspx?PC=VY&PK=M0BI9CP',
			'/vscore-top-250/'         => '/variety-magazine-subscribe/',
			'/production-charts/'      => '/variety-magazine-subscribe/',
		);

		vip_redirects( $vip_redirects_array );

	}

	/**
	 * 301 Redirection block for post_tag.
	 *
	 * @since  2017-10-03 - Vishal Kakadiya
	 *
	 * @return void
	 */
	protected function _redirect_301_tag_redirection() {

		if ( ! function_exists( 'vip_redirects' ) ) {
			return;
		}

		$current_url = PMC::filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING );

		if ( ! empty( $current_url ) ) {

			if ( false !== strpos( $current_url, '/tag/' ) ) {

				$redirection_url = str_replace( '/tag/', '/t/', $current_url );

				vip_redirects( array(
					$current_url => $redirection_url,
				) );
			}
		}
	}

	/**
	 * Strip query strings from legacy URLs so that they can match existing
	 * redirects.
	 *
	 * @param string $url Legacy URL.
	 * @return string
	 */
	public function strip_query_strings_from_legacy_urls( $url ) {
		if ( false === strpos( $url, '?' ) ) {
			return $url;
		}

		if ( false === stripos( $url, '/review/VE' ) ) {
			return $url;
		}

		$parts = explode( '?', $url, 2 );
		return $parts[0];
	}
}
