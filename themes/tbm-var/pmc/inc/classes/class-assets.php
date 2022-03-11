<?php

/**
 * Assets
 *
 * The enqueueing of theme assets.
 *
 * @package pmc-core-v2
 *
 * @since   2019-08-26
 */

namespace PMC\Core\Inc;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Assets
 *
 * @since 2019-08-26
 * @see   \PMC\Global_Functions\Traits\Singleton
 *
 */
class Assets
{

	use Singleton;

	private $_blocking_stylesheets = [];

	/**
	 * Class constructor.
	 */
	protected function __construct()
	{

		$this->_setup_hooks();
	}

	/**
	 * Initializes the theme assets.
	 */
	protected function _setup_hooks()
	{

		add_action('wp_head', [$this, 'add_preload_polyfill']);

		add_action('wp_enqueue_scripts', [$this, 'enqueue_assets'], 11);

		if (!is_admin()) {
			add_filter('script_loader_tag', [$this, 'filter_script_loader_tag'], 19, 2);
		}

		// Disable selctive JS & CSS concatenation as we're on HTTPS and want defers to work.
		add_filter('css_do_concat', [$this, 'css_concat'], 10, 2);
		add_filter('js_do_concat', [$this, 'js_concat'], 10, 2);

		// Lazyload and defer styles.
		add_filter('ngx_http_concat_style_loader_tag', [$this, 'defer_stylesheets'], 10, 2);
		add_filter('style_loader_tag', [$this, 'defer_stylesheets'], 10, 2);

		add_filter('script_loader_src', [$this, 'filter_asset_src'], 10, 2);
		add_filter('style_loader_src', [$this, 'filter_asset_src'], 10, 2);

		// Disable all actions related to emojis.
		remove_action('admin_print_styles', 'print_emoji_styles');
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('admin_print_scripts', 'print_emoji_detection_script');
		remove_action('wp_print_styles', 'print_emoji_styles');
		remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
		remove_filter('the_content_feed', 'wp_staticize_emoji');
		remove_filter('comment_text_rss', 'wp_staticize_emoji');
		add_filter('emoji_svg_url', '__return_false');
	}

	/**
	 * Enqueue Child Theme Assets
	 *
	 * @action wp_enqueue_scripts, 11
	 *
	 * @codeCoverageIgnore
	 */
	public function enqueue_assets()
	{

		$this->dequeue_assets();
	}

	/**
	 * Dequeue Assets Not needed
	 *
	 * @since  2018.12.26
	 */
	public function dequeue_assets()
	{

		// Remove jQuery Migrate.
		if (!is_admin()) {
			wp_deregister_script('jquery');
			wp_register_script('jquery', false, ['jquery-core'], false);
			wp_enqueue_script('jquery');
			wp_enqueue_script('wp-util');
		}

		if (!is_single()) {
			// MediaElement Player is not needed outside of the single article.
			wp_deregister_style('mediaelement');
			wp_deregister_style('wp-mediaelement');
			wp_deregister_script('wp-mediaelement');
		}

		if (!is_single() && !is_archive()) {
			// Social share bar is not present outside of the single article and archive page.
			wp_dequeue_style('pmc-social-share-bar-common-css');
		}
		if (!is_page()) {
			wp_dequeue_style('pmc-swiftype-style');
		}
		wp_dequeue_style('pmc_related_link');
		wp_dequeue_style('pmc-core-woff-webfonts-css');
		wp_dequeue_style('pmc-core-ttf-webfonts-css');
		wp_dequeue_style('pmc-global-css-overrides');
		wp_dequeue_style('pmcfooter');
		wp_dequeue_style('pmc-core-site-css');

		wp_dequeue_script('pmc-core-site-js');
		wp_dequeue_script('wp-embed');
		wp_dequeue_script('pmc-remove-tracking');
		wp_dequeue_script('devicepx'); //jetpack.

	}

	/**
	 * This function will add async="async" to the script tag for the scripts
	 *
	 * @param string $tag
	 * @param string $handle
	 *
	 * @return string
	 */
	public function filter_script_loader_tag($tag = '', $handle = '')
	{

		// Scripts that should not be deferred.
		$scripts_not_defer = [
			'jquery',
			'jquery-core',
			'jquery-migrate',
			'liveblog',
			'pmc-adm-loader',
			'pmc-adm-dfp-events',
			'pmc-hooks',
			'pmc-video-player-ads-js',
			'waypoints',
			'amp-runtime',
			'amp-anim',
			'amp-iframe',
			'amp-ad',
			'amp-social-share',
			'amp-analytics',
			'adm-fuse',
		];

		$scripts_not_defer = apply_filters('pmc_core_scripts_remove_defer', $scripts_not_defer);

		if (!in_array($handle, (array) $scripts_not_defer, true)) {
			return str_replace([' src', ' async="async"', ' async'], [" defer='defer' src", '', ''], $tag);
		}

		return $tag;
	}

	/**
	 * Add `rel="preload"` polyfill.
	 *
	 * This is part of Google's recommendation for lazy loading of style sheets.
	 *
	 * @since 2018.12.26
	 *
	 * @see   https://github.com/filamentgroup/loadCSS/blob/master/src/cssrelpreload.js
	 *
	 * @codeCoverageIgnore
	 */
	public function add_preload_polyfill()
	{

?>
		<script>
			<?php
			// Note that the non-minified resource is available for code review in assets/src/js/vendor/cssrelpreload.js
			\PMC::render_template(PMC_CORE_PATH . '/assets/public/cssrelpreload.js', [], true);
			?>
		</script>
<?php
	}

	/**
	 * Defer stylesheets loading.
	 *
	 * Adds `rel="preload"` attribute to non-whitelisted style tags.
	 *
	 * @param string $tag    The tag.
	 * @param string $handle The handle.
	 *
	 * @return string Updated style tag.
	 * @since 2018.12.26
	 */
	public function defer_stylesheets($tag, $handle)
	{

		if (!is_admin()) {

			// Whitelist stylesheets that should not be lazy-loaded.
			$render_blocking_stylesheets = [];

			$render_blocking_stylesheets = array_merge($render_blocking_stylesheets, $this->_blocking_stylesheets);

			if (!in_array($handle, (array) $render_blocking_stylesheets, true)) {
				$new_tag  = '<noscript>' . str_replace(" id='", " id='fallback-", $tag) . '</noscript>';
				$new_tag .= str_replace(
					" rel='stylesheet'",
					" rel='preload' as='style' onload='this.onload=null;this.rel=\"stylesheet\"'",
					$tag
				); // phpcs:ignore

				return $new_tag;
			}
		}

		return $tag;
	}

	/**
	 * @param $handle
	 */
	public function enqueue_style($handle)
	{

		$this->_blocking_stylesheets[] = $handle;
		wp_enqueue_style($handle);
	}

	/**
	 * Queue css inline to render inline in page source.
	 *
	 * Queueing ensures that preload tags and other necessary tags can appear
	 * before the inline CSS, which otherwise outputs extremely early on `wp_head`.
	 *
	 * @param string $css_slug Stylesheet slug.
	 * @param string $path     Path to top level of theme directory.
	 */
	public function inline_style(string $css_slug, string $path): void
	{
		\PMC\Global_Functions\Styles::get_instance()->inline(
			$css_slug,
			rtrim($path, '/') . '/assets/build/css/'
		);
	}

	/**
	 * @param $do_concat
	 * @param $handle
	 *
	 * @return bool
	 */
	public function css_concat($do_concat, $handle)
	{

		if (in_array($handle, (array) $this->_blocking_stylesheets, true)) {
			$do_concat = false;
		}

		return $do_concat;
	}

	/**
	 * @param $do_concat
	 * @param $handle
	 *
	 * @return bool
	 */
	public function js_concat($do_concat, $handle)
	{

		$concat_js = ['pmc-utils', 'pmc-hooks', 'pmc-adm-dfp-events', 'pmc-adm-loader'];

		if (in_array($handle, (array) $concat_js, true)) {
			return true;
		}

		return false;
	}

	/**
	 *
	 * filter_asset_src
	 *
	 * Method that changes the source url into a minified version of the script. The script must be registered or this
	 * won't work obviously.
	 *
	 * @param string $src
	 * @param string $handle
	 *
	 * @return  string $src
	 *
	 */
	function filter_asset_src($src, $handle)
	{

		$rep_arr = [
			'pmc-ga-event-tracking',
			'jquery-inview',
			'pmc-adm-loader',
			'pmc-adm-style',
			'pmc-adm-style-admin',
			'pmc-comscore',
			'pmc-frisbee-js',
			'pmc-helpdesk',
			'pmc-hooks',
			'pmc-jquery-extensions',
			'pmc-utils',
			'waypoints',

		];

		$qual = apply_filters('pmc_core_speed_asset_qual', \PMC::is_production());

		if ($qual && in_array($handle, (array) $rep_arr, true)) {
			$src = str_replace('.js', '.min.js', $src);
			$src = str_replace('.css', '.min.css', $src);

			// fix any instantes where we just accidentally added multiple '.min'
			$src = str_replace('.min.min', '.min', $src);
		}

		return $src;
	}
}
