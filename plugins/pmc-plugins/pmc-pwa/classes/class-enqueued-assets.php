<?php
/**
 * Precache enqueued assets.
 *
 * @package pmc-pwa
 */

namespace PMC\PWA;

use PMC\Global_Functions\Traits\Singleton;
use WP_Dependencies;
use _WP_Dependency;

/**
 * Class Enqueued_Assets.
 */
class Enqueued_Assets {
	use Singleton;

	/**
	 * Filter tag to enable precaching for enqueued scripts.
	 */
	public const FILTER_PRECACHE_SCRIPTS = 'pmc_pwa_precached_scripts';

	/**
	 * Filter tag to enable precaching for enqueued styles.
	 */
	public const FILTER_PRECACHE_STYLES = 'pmc_pwa_precached_styles';

	/**
	 * List of theme directories for comparison against asset URLs.
	 *
	 * @var object
	 */
	protected $_theme_uris;

	/**
	 * Enqueued scripts to precache.
	 *
	 * @var array
	 */
	protected $_precached_scripts = [];

	/**
	 * Enqueued styles to precache.
	 *
	 * @var array
	 */
	protected $_precached_styles = [];

	/**
	 * Enqueued_Assets constructor.
	 */
	protected function __construct() {
		$this->_theme_uris = (object) [
			'stylesheet' => get_stylesheet_directory_uri(),
			'template'   => get_template_directory_uri(),
		];

		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		// Hooked early to make available when using default priority.
		add_filter( static::FILTER_PRECACHE_SCRIPTS, [ $this, 'precache_scripts' ], 0 );
		add_filter( static::FILTER_PRECACHE_STYLES, [ $this, 'precache_styles' ], 0 );

		// Hooked late to capture scripts enqueued at the various priorities used in plugins and themes.
		add_action( 'wp_front_service_worker', [ $this, 'enqueue_other_contexts' ], 999 );

		// Hooked late to ensure we have the final say, lest we precache something that won't be used.
		add_filter( 'js_do_concat', [ $this, 'disable_concatenation' ], 99999, 2 );
		add_filter( 'css_do_concat', [ $this, 'disable_concatenation' ], 99999, 2 );

		add_filter( 'pmc_http2_preload_assets', [ $this, 'skip_preload_header_when_precached' ] );
	}

	/**
	 * Check if script handle is to be precached.
	 *
	 * @param string $handle Script handle.
	 * @return bool
	 */
	public function script_is_precached( string $handle ): bool {
		// Protected class member initialized and used as an array.
		// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
		return in_array( $handle, $this->_precached_scripts, true );
	}

	/**
	 * Check if stylesheet is to be precached.
	 *
	 * @param string $handle Stylesheet handle.
	 * @return bool
	 */
	public function style_is_precached( string $handle ): bool {
		// Protected class member initialized and used as an array.
		// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
		return in_array( $handle, $this->_precached_styles, true );
	}

	/**
	 * Mark allowed assets for precaching.
	 */
	public function add_precache_data(): void {
		$this->_precached_scripts = apply_filters( static::FILTER_PRECACHE_SCRIPTS, [] );
		$this->_precached_styles  = apply_filters( static::FILTER_PRECACHE_STYLES, [] );

		foreach ( $this->_precached_scripts as $script ) {
			wp_script_add_data( $script, 'precache', true );
		}

		foreach ( $this->_precached_styles as $style ) {
			wp_style_add_data( $style, 'precache', true );
		}
	}

	/**
	 * Precache all registered theme scripts.
	 *
	 * @param array $scripts Script handles to precache.
	 * @return array
	 */
	public function precache_scripts( array $scripts ): array {
		return array_merge(
			$scripts,
			$this->_get_handles_to_precache(
				wp_scripts()->registered
			)
		);
	}

	/**
	 * Precache all registered theme styles.
	 *
	 * @param array $styles Stylesheet handles to precache.
	 * @return array
	 */
	public function precache_styles( array $styles ): array {
		return array_merge(
			$styles,
			$this->_get_handles_to_precache(
				wp_styles()->registered
			)
		);
	}

	/**
	 * Extract theme-specific assets from registered items.
	 *
	 * @param array $registered Registered dependencies.
	 * @return array
	 */
	protected function _get_handles_to_precache( array $registered ): array {
		$added_styles = array_filter(
			$registered,
			[ $this, '_should_precache_asset' ]
		);

		$added_styles = wp_list_pluck( $added_styles, 'handle' );

		return array_values( $added_styles );
	}

	/**
	 * Check if an item should be precached.
	 *
	 * @param _WP_Dependency $dep Enqueued asset.
	 * @return bool
	 */
	protected function _should_precache_asset( _WP_Dependency $dep ): bool {
		return 0 === strpos( $dep->src, $this->_theme_uris->stylesheet )
				|| 0 === strpos( $dep->src, $this->_theme_uris->template );
	}

	/**
	 * Precache assets for pages in addition to the homepage assets already
	 * handled by the plugin's normal routine.
	 */
	public function enqueue_other_contexts(): void {
		global $wp_query;

		ob_start();

		// Add basic assets for singular views.
		$wp_query->is_single   = true;
		$wp_query->is_singular = true;
		$wp_query->is_home     = false;
		wp_enqueue_scripts();

		// Add assets for offline error page.
		$wp_query->is_single   = false;
		$wp_query->is_singular = false;
		$wp_query->set( 'wp_error_template', 'offline' );
		wp_enqueue_scripts();

		// Wait to mark assets for precaching until we build the final list.
		// Hooked late to capture scripts enqueued at the various priorities used in plugins and themes.
		add_action( 'wp_enqueue_scripts', [ $this, 'add_precache_data' ], 99999 );

		// Add assets for 500 error page.
		$wp_query->set( 'wp_error_template', '500' );
		wp_enqueue_scripts();

		ob_end_clean();
	}

	/**
	 * Prevent precached assets from being concatenated, otherwise the cache
	 * won't be used.
	 *
	 * @param bool   $concat To concat or not.
	 * @param string $handle Asset handle.
	 * @return bool
	 */
	public function disable_concatenation( bool $concat, string $handle ): bool {
		if (
			'js_do_concat' === current_filter()
			&& $this->script_is_precached( $handle )
		) {
			return false;
		}

		if (
			'css_do_concat' === current_filter()
			&& $this->style_is_precached( $handle )
		) {
			return false;
		}

		return $concat;
	}

	/**
	 * Prevent precached assets from being served via http/2 server push, as
	 * doing so bypasses the cache without any regard to its status. Since the
	 * server does not know if an asset is precached, it will push assets that
	 * are already in the browser cache.
	 *
	 * @link https://www.w3.org/TR/preload/#server-push-http-2
	 *
	 * @param array $urls Precached URLs and the type they'll be precached as.
	 * @return array
	 */
	public function skip_preload_header_when_precached( array $urls ): array {
		$scripts = $this->_extract_relative_urls_from_enqueued_assets(
			$this->_precached_scripts,
			wp_scripts()
		);

		$styles = $this->_extract_relative_urls_from_enqueued_assets(
			$this->_precached_styles,
			wp_styles()
		);

		// Array parameters are guaranteed by strict typing.
		// phpcs:disable PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam

		foreach ( $urls as $key => $preload ) {
			if (
				'script' === $preload['as']
				&& in_array( $preload['uri'], $scripts, true )
			) {
				unset( $urls[ $key ] );
			} elseif (
				'style' === $preload['as']
				&& in_array( $preload['uri'], $styles, true )
			) {
				unset( $urls[ $key ] );
			}
		}

		return array_values( $urls );

		// phpcs:enable PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
	}

	/**
	 * Convert array of precached handles into an array of relative URLs, to
	 * match what is received from \PMC\Global_Functions\Service\Http2.
	 *
	 * @param array           $precached_items Handles for precached items.
	 * @param WP_Dependencies $dependencies    Instance of \WP_Scripts or
	 *                                         \WP_Styles.
	 * @return array
	 */
	protected function _extract_relative_urls_from_enqueued_assets(
		array $precached_items,
		WP_Dependencies $dependencies
	): array {
		$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$urls      = [];

		foreach ( $precached_items as $handle ) {
			$item = $dependencies->registered[ $handle ] ?? null;

			if ( ! $item instanceof _WP_Dependency ) {
				continue;
			}

			$item_host = wp_parse_url( $item->src, PHP_URL_HOST );

			if ( is_string( $item_host ) && $item_host !== $home_host ) {
				continue;
			}

			$src = $item->src;

			if ( null !== $item->ver ) {
				$src = add_query_arg(
					'ver',
					$item->ver ? $item->ver : $dependencies->default_version,
					$src
				);
			}

			$urls[] = set_url_scheme( $src, 'relative' );
		}

		return $urls;
	}
}
