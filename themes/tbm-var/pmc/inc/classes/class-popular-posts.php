<?php
/**
 * Used for building the list of popular posts from across PMC brands.
 *
 * @package pmc-core-v2
 * @since 2021-06-11
 */

namespace PMC\Core\Inc;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC\Core\Inc\Top_Posts;
use PMC_Options;

/**
 * Class For getting popular posts across brands.
 *
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Popular_Posts {

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
		add_filter( 'pmc_footer_popular_list', [ $this, 'get_footer_list' ] );
		add_filter( 'pmc_core_rest_api_data', [ $this, 'get_data_popular_post' ], 10, 2 );
		add_filter( 'pmc_core_rest_api_data', [ $this, 'get_data_popular_brands' ], 10, 2 );
		add_action( 'init', [ $this, 'schedule_job' ] );
	}

	/**
	 * If filter is set, schedule an hourly job to update the popular posts from across brands.
	 */
	public function schedule_job() {
		if ( apply_filters( 'pmc_popular_posts_schedule_job', false ) ) {
			add_action( 'pmc_popular_posts_run_job', [ $this, 'run_job' ] );
			\pmc_schedule_event( time() + 5, 'hourly', 'pmc_popular_posts_run_job' );
		}
	}

	/**
	 * Generate the list of popular posts. Triggered by hourly schedule event.
	 */
	public function run_job() {

		$this->update_data_popular_brands();

	}

	/**
	 * Get Footer List
	 *
	 * @return array
	 */
	public function get_footer_list() {

		return [
			[
				'feed_source_url' => 'https://variety.com/wp-json/pmc_core/v1/pmc_core_modules/pmc-popular-post',
				'feed_title'      => 'Variety',
				'css_classes'     => [],
			],
			[
				'feed_source_url' => 'https://www.dirt.com/wp-json/pmc_core/v1/pmc_core_modules/pmc-popular-post',
				'feed_title'      => 'Dirt',
				'css_classes'     => [],
			],
			[
				'feed_source_url' => 'https://www.artnews.com/wp-json/pmc_core/v1/pmc_core_modules/pmc-popular-post',
				'feed_title'      => 'Art News',
				'css_classes'     => [],
			],
			[
				'feed_source_url' => 'https://www.sheknows.com/wp-json/pmc_core/v1/pmc_core_modules/pmc-popular-post',
				'feed_title'      => 'She Knows',
				'css_classes'     => [],
			],
			[
				'feed_source_url' => 'https://www.sportico.com/wp-json/pmc_core/v1/pmc_core_modules/pmc-popular-post',
				'feed_title'      => 'Sportico',
				'css_classes'     => [],
			],
			[
				'feed_source_url' => 'https://www.hollywoodreporter.com/wp-json/pmc_core/v1/pmc_core_modules/pmc-popular-post',
				'feed_title'      => 'Hollywood Reporter',
				'css_classes'     => [],
			],
			[
				'feed_source_url' => 'https://www.vibe.com/wp-json/pmc_core/v1/pmc_core_modules/pmc-popular-post',
				'feed_title'      => 'Vibe',
				'css_classes'     => [],
			],
		];

	}

	/**
	 * Builds the list of popular posts across brands.
	 *
	 * @param array $args The callback params.
	 * @return array $item
	 */
	public static function build_popular_list_brands( $args ) {

		$item = [];

		$source_url = apply_filters( 'pmc_popular_post_brands__endpoint_url', esc_url_raw( $args['feed_source_url'], [ 'http', 'https' ] ) );

		// If there is no source, bail.
		if ( empty( $source_url ) || ( filter_var( $source_url, FILTER_VALIDATE_URL ) === false ) ) {
			return [];
		}

		// To get the new endpoint data
		$popular_data = wpcom_vip_file_get_contents( $source_url, 3 );

		if ( ! is_wp_error( $popular_data ) && ! empty( $popular_data ) ) {
			$item = json_decode( $popular_data, true );

			if ( is_array( $item ) ) {
				$item['source']['url'] = $source_url;
				if ( isset( $args['feed_title'] ) ) {
					$item['source']['name'] = $args['feed_title'];
				}
			}
		}

		return $item;

	}

	/**
	 * Get Array for async json return
	 * @return array
	 */
	public function get_data_popular_brands( $data, $name ) {

		if ( 'pmc-footer-popular-brands' !== $name ) {
			return $data;
		}

		$list_data = pmc_get_option( 'pmc_popular_brands' );

		if ( empty( $list_data ) ) {
			$list_data = $this->update_data_popular_brands();
		}

		return $list_data;

	}

	/**
	 * Generate the popular list of posts across brands and update the option.
	 * @return array
	 */
	public function update_data_popular_brands() {

		$footer_list = apply_filters( 'pmc_footer_popular_list', [] );

		$list_data = [];

		foreach ( $footer_list as $list_item ) {

			// ToDo: If the remote call fails, add a fallback to use previously cached data from the option.
			$data = $this->build_popular_list_brands( $list_item );

			if ( ! empty( $data['post_title'] ) ) {
				$list_data[] = $data;
			}
		}

		// Sort in descending order, from most popular to least popular, by views.
		usort(
			$list_data,
			function( $a, $b ) {
				return $b['views'] <=> $a['views'];
			}
		);

		pmc_update_option( 'pmc_popular_brands', $list_data );

		return $list_data;

	}

	/**
	 * Get Array for async json return
	 * @param array $data Endpoint data.
	 * @param string $name Endpoint being called.
	 * @return array
	 */
	public function get_data_popular_post( $data, $name ) {

		if ( 'pmc-popular-post' !== $name ) {
			return $data;
		}

		$days = apply_filters( 'pmc_popular_post_days', 7 );

		$popular_post = Top_Posts::get_posts( 1, $days );

		if ( is_array( $popular_post ) && ! empty( $popular_post ) ) {
			$popular_post = array_pop( $popular_post );
		}

		if ( ! empty( $popular_post['post_title'] ) ) {
			return $popular_post;
		}

		return [];

	}

}
