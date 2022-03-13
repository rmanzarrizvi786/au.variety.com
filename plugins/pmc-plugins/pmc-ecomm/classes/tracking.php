<?php
namespace PMC\EComm;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Implement E-Commerce tracking data
 */
class Tracking {
	use Singleton;

	const FILTER_LINK_URL = 'pmc_ecommerce_link_url';
	const FILTER_SOURCE   = 'pmc_ecommerce_source';

	protected $_source;

	protected $_trackers = [];

	/**
	 * Constructor for singleton class
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Method to setup and initialize wp hooks
	 */
	protected function _setup_hooks() : void {
		add_filter( self::FILTER_LINK_URL, [ $this, 'track' ] );

		// Note: We can break the tracker code into separate class if implementation get more complex in the future
		$this->register_tracker( [ $this, 'track_amazon' ] );
		$this->register_tracker( [ $this, 'track_skimlink' ] );
	}

	/**
	 * Method to register a tracker callback
	 * @param Callable @callback A callable object
	 */
	public function register_tracker( $callback ) : void {
		if ( is_callable( $callback ) ) {
			$this->_trackers[] = $callback;
		}
	}

	/**
	 * Add tracking information to the given url link
	 *
	 * @param $url
	 * @param int $post
	 * @return string
	 */
	public function track( string $url, $post = 0 ) : string {
		if ( ! empty( $url ) ) {
			foreach ( $this->_trackers as $track ) {
				$result = call_user_func( $track, $url, $post );

				// Make sure the callback return a result.  If it doesn't, we don't want to return empty result.
				if ( ! empty( $result ) ) {
					$url = $result;
				}
			}
		}
		return $url;
	}

	/**
	 * REV-90: Add amazon associate tracking info to amazon's url
	 * @param string $url
	 * @param int $post
	 * @return string
	 */
	public function track_amazon( string $url, $post = 0 ) : string {
		$host = strtolower( wp_parse_url( $url, PHP_URL_HOST ) );
		if ( in_array( $host, [ 'read.amazon.com', 'amazon.com', 'www.amazon.com', 'amzn.to' ], true ) ) {
			// Add amazon associates tracking info
			$args = [
				'asc_source'   => $this->get_source(),
				'asc_campaign' => $this->get_source(),
			];

			$link = get_permalink( $post );
			if ( ! empty( $link ) ) {
				$args['asc_refurl'] = rawurlencode( $link );
			}

			$url = add_query_arg( $args, $url );
		}
		return $url;
	}

	/**
	 * REV-26: Add Skimlink Referral URLs
	 * @param string $url
	 * @param int $post
	 * @return string
	 */
	public function track_skimlink( string $url, $post = 0 ) : string {
		$host = strtolower( wp_parse_url( $url, PHP_URL_HOST ) );
		if ( in_array( $host, [ 'go.skimresources.com' ], true ) ) {
			$link = get_permalink( $post );
			if ( ! empty( $link ) ) {
				$args = [
					'sref' => rawurlencode( $link ),
				];
				$url  = add_query_arg( $args, $url );
			}
		}
		return $url;
	}

	/**
	 * Return the source info: web, amp, feed name, etc.
	 *
	 * @return string
	 */
	public function get_source() : string {
		if ( ! isset( $this->_source ) ) {
			$this->_source = 'web';
			if ( \PMC::is_amp() ) {
				$this->_source = 'amp';
			} elseif ( is_feed() ) {
				if ( ! empty( $GLOBALS['feed'] ) ) {
					$this->_source = $GLOBALS['feed'];
				} else {
					$this->_source = trim( wp_parse_url( get_permalink(), PHP_URL_PATH ), '/' );
					$this->_source = preg_replace( '@^(custom-)*feed/@', '', $this->_source );
				}
				if ( empty( $this->_source ) ) {
					$this->_source = get_query_var( 'feed' );
					if ( empty( $this->_source ) ) {
						$this->_source = 'feed';
					}
				}
			}
		}
		// Use filter to allow source override, eg. facebook instant articles, apple news, etc..
		return (string) apply_filters( self::FILTER_SOURCE, $this->_source );
	}

}
