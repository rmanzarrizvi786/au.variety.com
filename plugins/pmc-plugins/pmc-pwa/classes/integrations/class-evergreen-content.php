<?php
/**
 * Cache Evergreen Content.
 *
 * @package pmc-pwa
 */

namespace PMC\PWA\Integrations;

use PMC\Dateless_Link;
use PMC\Global_Functions\Traits\Singleton;
use PMC\Post_Options;
use PMC\PWA\Components;
use PMC\PWA\Utils;
use WP_Service_Workers;
use WP_Service_Worker_Scripts;

/**
 * Class Evergreen_Content.
 */
class Evergreen_Content {
	use Singleton;

	/**
	 * Filter tag to disable integration.
	 */
	public const FILTER_ENABLE = 'pmc_pwa_integrations_evergreen_content';

	/**
	 * Evergreen_Content constructor.
	 */
	protected function __construct() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		pmc_load_plugin( 'pmc-dateless-link', 'pmc-plugins' );

		$this->_setup_hooks();
	}

	/**
	 * Helper to check if the integration is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		return (bool) apply_filters( static::FILTER_ENABLE, false );
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		// Prepopulate before underlying component is output.
		add_action( 'wp_front_service_worker', [ $this, 'prepopulate_evergreen_cache' ], 5 );
		add_action( 'wp_front_service_worker', [ $this, 'add_route' ] );
	}

	/**
	 * Cache several recent evergreen articles.
	 */
	public function prepopulate_evergreen_cache(): void {
		$posts = $this->_get_post_ids();

		if ( empty( $posts ) ) {
			return;
		}

		array_map(
			[
				Components::get_instance(),
				'add_url_to_cache',
			],
			array_map(
				'get_permalink',
				(array) $posts
			)
		);
	}

	/**
	 * Cache as "Stale While Revalidate" URLs for evergreen content.
	 *
	 * @param WP_Service_Worker_Scripts $scripts Instance of service-worker scripts manager.
	 */
	public function add_route( WP_Service_Worker_Scripts $scripts ): void {
		$link_settings = Dateless_Link\Permalink::get_instance()->get_registered_settings();

		if ( ! isset( $link_settings['evergreen-content'] ) ) {
			return;
		}

		$path  = preg_quote(
			'/'
			. $link_settings['evergreen-content']['permalink_prefix']
			. '/',
			'/'
		);
		$path .= '.*';

		Components::get_instance()->cache_url_as_stale_while_revalidate( $path );
	}

	/**
	 * Return array of evergreen post IDs.
	 *
	 * @return array
	 */
	protected function _get_post_ids(): array {
		if ( ! $this->is_enabled() ) {
			return [];
		}

		return Post_Options\API::get_instance()->get_posts_having_option(
			'evergreen-content',
			[
				'posts_per_page' => 5,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			]
		);
	}
}
