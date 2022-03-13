<?php
namespace PMC\Sitemaps;
use PMC\Global_Functions\Traits\Singleton;

/**
 * The news sitemap is implemented by Jetpack.
 * We're adding this class to allow additional customization
 *
 * Class News_Sitemap
 * @package PMC\Sitemaps
 */
class News_Sitemap {
	use Singleton;

	protected function __construct() {
		// Filter to exclude post in news sitemap
		add_filter( 'jetpack_sitemap_news_skip_post', [ $this, 'maybe_skip_post_in_news_sitemap' ], 10, 2 );
		add_filter( 'jetpack_sitemap_news_ns', [ $this, 'filter_sitemap_news_namespaces' ] );
	}

	/**
	 * @see https://developer.jetpack.com/hooks/jetpack_sitemap_news_skip_post/
	 *
	 * Method to determine if a post should be excluded from News Sitemap or not.
	 * This is hooked on to 'jetpack_sitemap_news_skip_post' filter.
	 *
	 * @param bool   $skip
	 * @param object $post
	 *
	 * @return bool
	 */

	public function maybe_skip_post_in_news_sitemap( bool $skip, object $post ) : bool {

		// Option added via pmc option plugin
		if ( class_exists( \PMC\Post_Options\API::class ) && \PMC\Post_Options\API::get_instance()->post( $post )->has_option( 'exclude-from-google-news' ) ) {
			return true;
		}

		return $skip;

	}

	/**
	 * @see https://developer.jetpack.com/hooks/jetpack_sitemap_news_ns/
	 *
	 * Method to filter namespaces for news sitemap.
	 *
	 * @param array  $namespace
	 *
	 * @return array
	 */
	public function filter_sitemap_news_namespaces( array $namespaces ) : array {

		if ( is_array( $namespaces ) && current_theme_supports( 'post-thumbnails' ) ) {
			// Referenced from jetpack plugin, all namespace URLs non-https
			$namespaces['xmlns:image'] = 'http://www.google.com/schemas/sitemap-image/1.1'; // phpcs:ignore
		}

		return $namespaces;
	}
}
