<?php
/**
 * Footer Feed.
 *
 * Used for building the footer feed for posts across the PMC brands.
 *
 * @package pmc-core-v2
 * @since 2019-01-09
 */

namespace PMC\Core\Inc;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Footer_Feed
 *
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Footer_Feed {

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Initialize Hooks and filters.
	 */
	protected function _setup_hooks() {

		add_filter( 'pmc_footer_list_of_feeds', [ $this, 'set_footer_list' ] );
		add_action( 'wp_feed_options', [ $this, 'modify_feed_options' ] );
		add_filter( 'pmc_footer_feed_image_domains', [ $this, 'set_footer_image_domain' ] );
		add_filter( 'pmc_core_rest_api_data', [ $this, 'get_json_data' ], 10, 2 );

	}

	/**
	 * Set Footer Feed List
	 *
	 * @return array
	 */
	public function set_footer_list() {

		return [
			[
				'feed_source_url' => 'https://www.goldderby.com/custom-feed/pmc_footer/',
				'feed_title'      => 'GoldDerby',
				'css_classes'     => [],
			],
			[
				'feed_source_url' => 'https://tvline.com/feed/pmc_footer/',
				'feed_title'      => 'TVLine',
				'css_classes'     => [],
			],
			[
				'feed_source_url' => 'https://bgr.com/feed/pmc_footer/',
				'feed_title'      => 'BGR',
				'css_classes'     => [],
			],
			[
				'feed_source_url' => 'https://www.rollingstone.com/custom-feed/pmc_footer/',
				'feed_title'      => 'Rolling Stone',
				'css_classes'     => [],
			],
			[
				'feed_source_url' => 'https://spy.com/custom-feed/pmc-footer-feed/',
				'feed_title'      => 'SPY',
				'css_classes'     => [],
			],
		];

	}

	/**
	 * Builds the footer feed data.
	 *
	 * @param array $args The callback params.
	 * @return array $item
	 */
	public static function build_footer_feed( $args ) {

		$item = [];

		$source_url = esc_url_raw( $args['feed_source_url'], [ 'http', 'https' ] );

		// If there is no source, bail.
		if ( ! $source_url ) {
			return [];
		} else {
			$item['source']['url'] = $source_url;
		}

		if ( isset( $args['feed_title'] ) ) {
			$item['source']['name'] = $args['feed_title'];
		}

		add_filter( 'wp_feed_cache_transient_lifetime', 'pmc_set_transient_to_thirty_minutes' );
		$feed = fetch_feed( $source_url );
		remove_filter( 'wp_feed_cache_transient_lifetime', 'pmc_set_transient_to_thirty_minutes' );

		if ( is_wp_error( $feed ) ) {
			return [];
		}

		$max_items = $feed->get_item_quantity( 1 );
		$rss_items = $feed->get_items( 0, $max_items );

		// Allow LOB to alter the image size w/o overriding the entire function.
		list( $image_width, $image_height ) = apply_filters( 'pmc_footer_feed_image_size', [ 232, 175 ] );

		// This is a loop, but it's really only looping over a single item.
		foreach ( $rss_items as $feed_item ) {

			// Excerpt.
			$item['title'] = wp_specialchars_decode( $feed_item->get_title(), ENT_QUOTES );
			$item['url']   = $feed_item->get_permalink();

			$date         = human_time_diff( strtotime( $feed_item->get_date() ), current_time( 'timestamp' ) );
			$item['date'] = sprintf( '%s ago', $date );

			$item['image'] = \pmc_master_get_footer_image( $feed_item, $image_width, $image_height, $feed_item->feed->feed_url );

			if ( empty( $item['image'] ) ) {
				// If we don't have an image in feed then use fallback image.
				$item['image'] = CHILD_THEME_URL . '/assets/public/lazyload-fallback.jpg'; // @codeCoverageIgnore
				$item['image'] = apply_filters( 'pmc_footer_feed_default_image', $item['image'] ); // @codeCoverageIgnore
			}

			$image_base   = add_query_arg( 'quality', '100', $item['image'] );
			$image_base   = set_url_scheme( $image_base, 'https' );
			$image_src    = add_query_arg( 'w', '180', $image_base );
			$image_src_2x = add_query_arg( 'w', '300', $image_base );

			$item['image_src']    = $image_src;
			$item['image_srcset'] = $image_src . ' 180w, ' . $image_src_2x . ' 300w';
			$item['image_sizes']  = '(max-width: 959px) 46%, (max-width: 1259px) 22%, 300px';
		}

		return $item;

	}

	/**
	 * Modifies feed options.
	 *
	 * @since 2017-08-06 Milind More CDWE-480
	 * @param object $feed SimplePie feed Object.
	 */
	public function modify_feed_options( $feed ) {

		// Set useragent to prevent 403.
		$feed->set_useragent( 'Mozilla/4.0 ' . SIMPLEPIE_USERAGENT );
	}

	/**
	 * Adds valid domain for footer feeds images.
	 *
	 * @since 2017-08-06 Milind More CDWE-480
	 * @param array $domains array of domains.
	 * @return array $domains updated array.
	 */
	public function set_footer_image_domain( $domains ) {

		if ( empty( $domains ) || ! is_array( $domains ) ) {
			$domains = [];
		}

		if ( ! in_array( 'tvline.com', (array) $domains, true ) ) {
			$domains[] = 'tvline.com';
		}

		return $domains;
	}

	/**
	 * Get Array for async json return
	 * @return array
	 */
	public function get_json_data( $data, $name ) {

		if ( 'pmc-footer' !== $name ) {
			return $data;
		}

		$footer_feeds = apply_filters( 'pmc_footer_list_of_feeds', [] );

		$feed_data = [];

		foreach ( $footer_feeds as $feed ) {

			$data = $this->build_footer_feed( $feed );

			if ( ! empty( $data['title'] ) ) {
				$feed_data[] = $data;
			}
		}

		return $feed_data;

	}

}
