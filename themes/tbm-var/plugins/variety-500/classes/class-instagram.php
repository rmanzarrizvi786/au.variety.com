<?php
/**
 * Instagram
 *
 * Responsible for integrating Instagram.
 *
 * @package pmc-variety-2017
 * @since 1.0
 */

namespace Variety\Plugins\Variety_500;

use \PMC\Global_Functions\Traits\Singleton;
use PMC_Cache;

/**
 * Class Instagram
 *
 * Fetches Instagram images from their API.
 *
 * @since 1.0
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Instagram {

	use Singleton;

	/**
	 * Endpoint
	 *
	 * @since 1.0
	 */
	const ENDPOINT = 'https://www.instagram.com/%s/media';

	/**
	 * Cache Key
	 *
	 * @since 1.0
	 */
	const CACHE_KEY = 'instagram_';

	/**
	 * Hard Limit
	 *
	 * The max amount of Instagram posts we should fetch from the API.
	 *
	 * @since 1.0
	 */
	const HARD_LIMIT = 10;

	/**
	 * Option Group
	 *
	 * @since 1.0
	 */
	const OPTION_GROUP = 'variety_500_instagram';

	/**
	 * Option Name
	 *
	 * @since 1.0
	 */
	const OPTION_NAME = 'instagram_feeds';

	/**
	 * Expiry Option Name
	 *
	 * @since 1.0
	 */
	const EXPIRY_OPTION_NAME = 'instagram_feeds_expiry';

	/**
	 * Cache Hit
	 *
	 * Let's us know if the cache was hit.
	 *
	 * @var bool
	 */
	private $cache_hit = false;

	/**
	 * Get Feed
	 *
	 * Fetches a list of most recent formatted instagram posts.
	 *
	 * @since 1.0
	 *
	 * @param string $user_name The Instagram user name.
	 * @param int    $count Number of items to return.
	 *
	 * @return array
	 */
	public function get_feed( $user_name, $count = 5 ) {
		try {
			$feed = $this->feed_get( $user_name );

			if ( ! empty( $feed ) && is_array( $feed ) ) {
				return array_slice( $feed, 0, $count );
			}
		} catch ( \Exception $e ) {
			return array();
		}

		return array();
	}

	/**
	 * Get Feeds
	 *
	 * Takes multiple Instagram user names and returns an array of
	 * recent images for each user.
	 *
	 * The images are ordered by date within each user.
	 *
	 * The number if images returned for each user is set by
	 * the HARD_LIMIT constant.
	 *
	 * Example:
	 * $feed = array(
	 *    'john_smith' => array(
	 *          {date} => {array of image data},
	 *          {date} => {array of image data},
	 *    ),
	 *    'jane_doe' => array(
	 *          {date} => {array of image data},
	 *          {date} => {array of image data},
	 *    ),
	 * );
	 *
	 * @since 1.0
	 * @param array $user_names A list of Instagram user names to fetch.
	 * @return array
	 */
	public function get_feeds( $user_names ) {
		$feed = array();
		if ( ! empty( $user_names ) && is_array( $user_names ) ) {
			/*
			 * We fetch our data from cached feeds. If there are no feeds cached
			 * we will only fetch one feed at a time. Over a short period of
			 * visits, our cache will be filled up with images again. This is
			 * done to avoid hitting the Instagram API 10-20 times on a single
			 * request.
			 */
			foreach ( $user_names as $user_name ) {
				try {
					$images = $this->feed_get( $user_name );
				} catch ( \Exception $e ) {
					$images = array();
				}

				if ( ! empty( $images ) ) {
					// This method orders by username order, not image creation time.
					$feed[ $user_name ] = $images;
					krsort( $feed[ $user_name ], SORT_NUMERIC );
				}

				/*
				 * This is where we break if we called the Instagram API outside
				 * of our cache (as per above).
				 */
				if ( ! $this->cache_hit ) {
					break;
				}
			}
		}
		return $feed;
	}

	/**
	 * Feed Get
	 *
	 * Checks if we have cached data, if not sends the request to the Instagram
	 * API.
	 *
	 * @since 1.0
	 * @param string $user_name The Instagram user name.
	 * @return bool|mixed|string
	 * @throws \Exception The Exception.
	 */
	private function feed_get( $user_name ) {
		/*
		 * Reset cache hit to true for each new call, will set to false if the
		 * callback is run.
		 */
		$this->cache_hit = true;

		// First check for the feed in our memory cache.
		$feed = wp_cache_get( self::CACHE_KEY . $user_name, Bootstrap::CACHE_GROUP );

		// Check if we have feed data stored in our option if not in cache.
		if ( empty( $feed ) ) {
			$feed = $this->get_feed_from_option( $user_name );
		}

		// If not in option, fetch from the Instagram API and save cache/option.
		if ( empty( $feed ) ) {
			$feed = $this->http_call( $user_name );

			// Save the feed to our cache.
			wp_cache_set( self::CACHE_KEY . $user_name, $feed, Bootstrap::CACHE_GROUP );

			// Save (or update) the feed in our options.
			$this->save_feed_in_option( $user_name, $feed );
		}

		return $feed;
	}

	/**
	 * Get Feed From Option
	 *
	 * Fetches our feed from our options (if it exists). We store our feed in an
	 * option because the VIP cache resets too often and is unrealiable. We
	 * don't want to make 20+ Instagram requests per page.
	 *
	 * @since 1.0
	 * @param string $user_name The Instagram user name.
	 * @return array
	 */
	private function get_feed_from_option( $user_name ) {

		// If the option is expired, return an empty array.
		if ( $this->feed_option_expired() ) {
			return array();
		}

		$feeds = pmc_get_option( self::OPTION_NAME, self::OPTION_GROUP );

		if ( empty( $feeds[ $user_name ] ) ) {
			return array();
		}

		return $feeds[ $user_name ];
	}

	/**
	 * Save Feed In Option
	 *
	 * Saves a feed to our options. If the option has expired, stores a new feed
	 * array in the option and sets a new expiry. If it has not expired, it just
	 * adds the feed data to the existing array and updates the option.
	 *
	 * @since 1.0
	 * @param string $user_name The Instagram user name.
	 * @param array  $feed The feed data.
	 */
	private function save_feed_in_option( $user_name, $feed ) {
		$feeds = pmc_get_option( self::OPTION_NAME, self::OPTION_GROUP );

		/*
		 * If the option doesn't exist or has expired, we need to set a new
		 * expiry date for our options and clear the data in the existing
		 * option if one exists.
		 */
		if ( empty( $feeds ) || $this->feed_option_expired() ) {
			$feeds = array(
				$user_name => $feed,
			);

			/*
			 * We store our feed data in an option for a day. What we're really
			 * doing here is setting the expiry date to a day from now. The data
			 * will get set to a new value (see above) and then updated below.
			 */
			pmc_update_option( self::EXPIRY_OPTION_NAME, strtotime( '+1 day' ), self::OPTION_GROUP );
		} else {
			$feeds[ $user_name ] = $feed;
		}

		pmc_update_option( self::OPTION_NAME, $feeds, self::OPTION_GROUP );
	}

	/**
	 * Feed Option Expired
	 *
	 * Tells us if a feed option has expired or not.
	 *
	 * @since 1.0
	 * @return bool
	 */
	private function feed_option_expired() {
		$expiry = pmc_get_option( self::EXPIRY_OPTION_NAME, self::OPTION_GROUP );

		if ( ! empty( $expiry ) && strtotime( 'now' ) < $expiry ) {
			return false;
		}

		return true;
	}

	/**
	 * HTTP Call
	 *
	 * Does a remote call to the Instagram API to fetch the latest images from
	 * their feed.
	 *
	 * @since 1.0
	 * @param string $user_name The Instagram user name.
	 * @return array
	 * @throws \Exception The Exception.
	 */
	public function http_call( $user_name ) {
		$this->cache_hit = false;
		$items           = array();
		$response        = vip_safe_wp_remote_get( sprintf( self::ENDPOINT, $user_name ), '', 3, 3 );

		if ( ! is_wp_error( $response ) ) {
			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$images = json_decode( wp_remote_retrieve_body( $response ) );

				/*
				 * Before we cache the results, we limit the amount of
				 * results that we are going to return and store to our hard
				 * limit. This saves us space in memory and avoids storing
				 * unnecessary data.
				 */
				if ( ! empty( $images->items ) ) {
					foreach ( array_slice( $images->items, 0, self::HARD_LIMIT ) as $image ) {
						$items[ $image->created_time ] = array(
							'src'     => $image->images->standard_resolution->url,
							'link'    => $image->link,
							'caption' => $image->caption->text,
						);
					}
				}
			} else {
				throw new \Exception( wp_remote_retrieve_response_message( $response ) );
			}
		} else {
			// There was an error making the request.
			throw new \Exception( $response->get_error_message() );
		}

		return $items;
	}
}
