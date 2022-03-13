<?php
/**
 * Configure service worker caching.
 *
 * @package pmc-pwa
 */

namespace PMC\PWA;

use PMC\Global_Functions\Traits\Singleton;
use WP_Service_Worker_Caching_Routes;
use WP_Service_Worker_Scripts;

/**
 * Class Service_Worker_Cache.
 */
class Service_Worker_Cache {
	use Singleton;

	/**
	 * Cache suffix for page content.
	 */
	public const CACHE_NAME_NAVIGATION = 'pages';

	/**
	 * Cache suffix for themes' static assets.
	 */
	public const CACHE_NAME_THEME_ASSETS = 'theme-assets';

	/**
	 * Cache suffix for plugins' static assets.
	 */
	public const CACHE_NAME_PLUGIN_ASSETS = 'plugin-assets';

	/**
	 * Cache suffix for uploaded files.
	 */
	public const CACHE_NAME_UPLOADS = 'uploads';

	/**
	 * Filter tag to modify the maxinum number of items a single cache can hold.
	 */
	public const FILTER_CACHE_MAX_ENTRIES =
		'pmc_pwa_service_worker_cache_max_entries';

	/**
	 * Service_Worker_Caching constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_filter(
			'wp_service_worker_navigation_caching_strategy',
			[ $this, 'set_navigation_caching_strategy' ]
		);

		add_filter(
			'wp_service_worker_navigation_caching_strategy_args',
			[ $this, 'modify_navigation_caching_args' ]
		);

		$this->_load_integrations();
		add_action( 'wp_front_service_worker', [ $this, 'add_caches' ] );
	}

	/**
	 * Use "Network First" strategy for navigation caching.
	 *
	 * @param string $strategy Caching strategy.
	 * @return string
	 */
	public function set_navigation_caching_strategy( string $strategy ): string {
		return WP_Service_Worker_Caching_Routes::STRATEGY_NETWORK_FIRST;
	}

	/**
	 * Modify arguments for navigation cache, including limiting how many items
	 * are stored.
	 *
	 * @param array $args Caching strategy arguments.
	 * @return array
	 */
	public function modify_navigation_caching_args( array $args ): array {
		$args['cacheName']                             = static::CACHE_NAME_NAVIGATION;
		$args['matchOptions']                          = $this->_get_match_options();
		$args['plugins']['expiration']['matchOptions'] = $this->_get_match_options();
		$args['plugins']['expiration']['maxEntries']   = $this->_get_cache_max_entries(
			'navigation'
		);

		return $args;
	}

	/**
	 * Load integrations.
	 */
	protected function _load_integrations(): void {
		$classes = [
			Integrations\Evergreen_Content::class,
		];

		foreach ( $classes as $class ) {
			call_user_func( [ $class, 'get_instance' ] );
		}
	}

	/**
	 * Register various caches.
	 *
	 * @param WP_Service_Worker_Scripts $scripts Instance of service-worker scripts manager.
	 */
	public function add_caches( WP_Service_Worker_Scripts $scripts ): void {
		$this->_cache_theme_assets( $scripts );
		$this->_cache_plugin_assets( $scripts );
		$this->_cache_uploads( $scripts );
	}

	/**
	 * Cache assets from the active theme.
	 *
	 * @param WP_Service_Worker_Scripts $scripts Instance of service-worker scripts manager.
	 */
	protected function _cache_theme_assets( WP_Service_Worker_Scripts $scripts ): void {
		$directory_patterns = $this->_prepare_directory_patterns_array(
			[
				get_stylesheet_directory_uri(),
				get_template_directory_uri(),
			]
		);

		$scripts->caching_routes()->register(
			'^(' . implode( '|', $directory_patterns ) . ').*',
			[
				'strategy'     => WP_Service_Worker_Caching_Routes::STRATEGY_CACHE_FIRST,
				'cacheName'    => static::CACHE_NAME_THEME_ASSETS,
				'matchOptions' => $this->_get_match_options(),
				'plugins'      => [
					'expiration' => [
						'matchOptions' => $this->_get_match_options(),
						'maxEntries'   => $this->_get_cache_max_entries(
							static::CACHE_NAME_THEME_ASSETS
						),
					],
				],
			]
		);
	}

	/**
	 * Cache assets from plugins.
	 *
	 * @param WP_Service_Worker_Scripts $scripts Instance of service-worker scripts manager.
	 */
	protected function _cache_plugin_assets( WP_Service_Worker_Scripts $scripts ): void {
		$directory_patterns = $this->_prepare_directory_patterns_array(
			[
				dirname( PMC_GLOBAL_FUNCTIONS_URL ),
				home_url( '/_static/' ),
			]
		);

		$scripts->caching_routes()->register(
			'^(' . implode( '|', $directory_patterns ) . ').*',
			[
				'strategy'     => WP_Service_Worker_Caching_Routes::STRATEGY_CACHE_FIRST,
				'cacheName'    => static::CACHE_NAME_PLUGIN_ASSETS,
				'matchOptions' => $this->_get_match_options(),
				'plugins'      => [
					'expiration' => [
						'matchOptions' => $this->_get_match_options(),
						'maxEntries'   => $this->_get_cache_max_entries(
							static::CACHE_NAME_PLUGIN_ASSETS
						),
					],
				],
			]
		);
	}

	/**
	 * Cache a limited number of uploaded assets for a week.
	 *
	 * @param WP_Service_Worker_Scripts $scripts Instance of service-worker scripts manager.
	 */
	protected function _cache_uploads( WP_Service_Worker_Scripts $scripts ): void {
		/**
		 * Don't cache uploads if we can't expire them. See method docblock.
		 */
		if ( ! $this->_expiration_max_entries_supported() ) {
			return;
		}

		$upload_dir = wp_get_upload_dir();

		$route = sprintf(
			'^(.*%1$s).*\.(png|gif|jpg|jpeg|svg|webp)(\?.*)?$',
			preg_quote(
				$upload_dir['baseurl'],
				'/'
			)
		);

		$scripts->caching_routes()->register(
			$route,
			[
				'strategy'     => WP_Service_Worker_Caching_Routes::STRATEGY_CACHE_FIRST,
				'cacheName'    => static::CACHE_NAME_UPLOADS,
				'matchOptions' => $this->_get_match_options(),
				'plugins'      => [
					'expiration' => [
						'matchOptions'  => $this->_get_match_options(),
						'maxAgeSeconds' => WEEK_IN_SECONDS,
						'maxEntries'    => $this->_get_cache_max_entries(
							static::CACHE_NAME_UPLOADS
						),
					],
				],
			]
		);
	}

	/**
	 * Prior to Workbox 6, `matchOptions` could not be passed to the
	 * Expiration plugin, preventing it from expiring uploads in production.
	 *
	 * Rather than risk filling a visitor's cache with large files that can only
	 * be removed manually, we disable certain caches until Workbox 6 is
	 * released and the PWA plugin is updated to use it.
	 *
	 * @see https://github.com/GoogleChrome/workbox/pull/2533
	 *
	 * @return bool
	 */
	protected function _expiration_max_entries_supported(): bool {
		if (
			( defined( 'IS_UNIT_TEST' ) && true === IS_UNIT_TEST )
			|| class_exists( '\WP_UnitTestCase', false )
		) {

			$mock = apply_filters(
				'pmc_pwa_mock_service_worker_expiration_max_entries_supported',
				null
			);

			if ( is_bool( $mock ) ) {
				return $mock;
			}
		}

		return version_compare(
			PWA_WORKBOX_VERSION,
			'6',
			'>='
		);
	}

	/**
	 * Prepare an array of directory patterns for use in Service Worker regex.
	 *
	 * @param array $dirs Directory patterns.
	 * @return array
	 */
	protected function _prepare_directory_patterns_array( array $dirs ): array {
		// Function declaration enforces type.
		// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
		$dirs = array_unique( $dirs );

		foreach ( $dirs as $key => $pattern ) {
			$dirs[ $key ] = preg_quote(
				trailingslashit( $pattern ),
				'/'
			);
		}

		return $dirs;
	}

	/**
	 * Return options Cache API uses to match cached objects to the keys
	 * requested by the Service Worker.
	 *
	 * @see https://github.com/GoogleChrome/workbox/issues/2206
	 * @see https://developer.mozilla.org/en-US/docs/Web/API/Cache/match#Parameters
	 * @see https://developer.mozilla.org/en-US/docs/Web/API/Cache/delete#Parameters
	 *
	 * @return array
	 */
	protected function _get_match_options(): array {
		return [
			// In production, delete requests won't match due to Vary header.
			'ignoreVary' => true,
		];
	}

	/**
	 * Limit a given cache to a certain number of items.
	 *
	 * @param string $cache Cache name.
	 * @return int
	 */
	protected function _get_cache_max_entries( string $cache ): int {
		return apply_filters(
			static::FILTER_CACHE_MAX_ENTRIES,
			static::CACHE_NAME_UPLOADS === $cache ? 25 : 50,
			$cache
		);
	}
}
