<?php
/**
 * Manage core Larva assets.
 *
 * @package pmc-larva
 */

namespace PMC\Larva;

use PMC\Global_Functions\Traits\Singleton;
use WP_Post;

/**
 * Class Core_Assets.
 */
class Core_Assets {
	use Singleton;

	/**
	 * Request context as computed by `_get_context()`.
	 *
	 * @var array|null|bool
	 */
	protected $_context = false;

	/**
	 * Core_Assets constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'inline_larva_stylesheet' ], -10 );
		add_filter( 'body_class', [ $this, 'maybe_add_body_class' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ], -10 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue configured Larva stylesheet.
	 */
	public function inline_larva_stylesheet(): void {

		if ( ! $this->_context_is_supported() ) {
			return;
		}

		$core_directory = Config::get_instance()->get( 'core_directory' );

		Assets::get_instance()->inline_tokens(
			Config::get_instance()->get( 'tokens' )
		);

		Assets::get_instance()->inline_style(
			Config::get_instance()->get(
				'css'
			),
			$core_directory
		);

		if ( Config::get_instance()->get(
			'compat_css'
		) ) {

			$brand_name = Config::get_instance()->get(
				'brand_name'
			);

			Assets::get_instance()->inline_style(
				$brand_name . '.compat',
				$core_directory
			);
		}
	}

	/**
	 * Add a body class in the format lrv-{$brand_name}-compat if
	 * compat_css configuration is enabled.
	 *
	 * @param array $classes Array of current body classes
	 *
	 * @return array Array of body classes with or without the compat class
	 */
	public function maybe_add_body_class( array $classes ): array {
		$config = Config::get_instance()->get();

		if ( ! $this->_context_is_supported() || ! $config['compat_css'] ) {
			return $classes;
		}

		$classes[] = 'lrv-' . $config['brand_name'] . '-compat';

		return $classes;
	}

	/**
	 * Register Core scripts, which are later contextually enqueued.
	 */
	public function register_scripts(): void {
		$scripts = [
			''           => [
				'common',
				'pmc-profiles',
				'standalone/video-showcase',
			],
		];

		$assets_instance = Assets::get_instance();

		foreach ( $scripts as $subdirectory => $dir_scripts ) {
			foreach ( $dir_scripts as $suffix ) {
				if ( empty( $subdirectory ) ) {
					$relative_path = $suffix;
				} else {
					$relative_path = "{$subdirectory}/{$suffix}";
				}

				$assets_instance->register_script(
					$this->build_script_handle( $suffix ),
					$relative_path . '.js',
					'core',
					[],
					true
				);
			}
		}
	}

	/**
	 * Enqueue scripts configured for the current context.
	 */
	public function enqueue_scripts(): void {
		if ( ! $this->_context_is_supported() ) {
			return;
		}

		$scripts = Config::get_instance()->get( 'js' );

		if ( ! is_array( $scripts ) ) {
			return;
		}

		$context = $this->_get_context();

		foreach ( $scripts as $index_or_handle => $handle_or_post_types ) {
			if ( is_string( $handle_or_post_types ) ) {
				wp_enqueue_script(
					$this->build_script_handle( $handle_or_post_types )
				);

				continue;
			}

			$handle = $this->build_script_handle( $index_or_handle );
			foreach ( $handle_or_post_types as $post_type ) {
				// `$context` is an array if we've gotten this far.
				// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
				if ( in_array( $post_type, $context, true ) ) {
					wp_enqueue_script( $handle );
				}
			}
		}
	}

	/**
	 * Determine if current context is one for which Larva is configured to
	 * enqueue assets.
	 *
	 * @return bool
	 */
	protected function _context_is_supported(): bool {
		$context = $this->_get_context();

		if ( null === $context ) {
			return false;
		}

		$supported_contexts = Config::get_instance()->get( 'contexts' );

		if ( is_bool( $supported_contexts ) ) {
			return $supported_contexts;
		}

		return ! empty( array_intersect( $supported_contexts, $context ) );
	}

	/**
	 * Determine current request context for conditionally enqueueing assets.
	 *
	 * @return array|null
	 */
	protected function _get_context(): ?array {
		// TODO: introduce logic for context queries, such as "home," which aren't tied to a post type.

		if ( false !== $this->_context ) {
			return $this->_context;
		}

		global $wp_query;

		$queried_object = get_queried_object();

		if ( $queried_object instanceof WP_Post && is_singular() ) {
			$this->_context = [ $queried_object->post_type ];
			return $this->_context;
		}

		// `is_tax()` only supports custom taxonomies.
		if ( is_tax() || is_category() || is_tag() ) {
			$types = wp_list_pluck( $wp_query->posts, 'post_type' );

			// `wp_list_pluck()` always returns an array.
			// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
			$this->_context = array_unique( $types );

			return $this->_context;
		}

		if ( is_archive() || is_search() ) {
			$post_types = get_query_var( 'post_type' );

			/**
			 * These conditions match what `WP_Query::get_posts()` uses, both in
			 * order and in referencing `WP_Query` properties rather than their
			 * corresponding methods.
			 */
			if ( 'any' === $post_types ) {
				$this->_context = get_post_types(
					[
						'exclude_from_search' => false,
					]
				);
			} elseif ( is_array( $post_types ) && ! empty( $post_types ) ) {
				$this->_context = $post_types;
			} elseif ( ! empty( $post_types ) ) {
				$this->_context = [
					$post_types,
				];
			} elseif ( $wp_query->is_attachment ) {
				$this->_context = [
					'attachment',
				];
			} elseif ( $wp_query->is_page ) {
				$this->_context = [
					'page',
				];
			} else {
				$this->_context = [
					'post',
				];
			}

			return $this->_context;
		}

		$this->_context = null;

		return $this->_context;
	}

	/**
	 * Build handle used to register and enqueue a script.
	 *
	 * @param string $slug Script identifier.
	 * @return string
	 */
	public function build_script_handle( string $slug ): string {
		return sprintf(
			'pmc-larva-core-%1$s',
			$slug
		);
	}
}
