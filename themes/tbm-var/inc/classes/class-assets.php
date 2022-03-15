<?php

/**
 * Assets
 *
 * The enqueueing of child theme assets.
 *
 * @package pmc-variety
 *
 * @since   2018-12-26
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;
use Variety\Plugins\Variety_VIP\Content;
use Variety\Plugins\Variety_500\Templates;

/**
 * Class Assets
 *
 * @since 2018-12-26
 * @see   \PMC\Global_Functions\Traits\Singleton
 *
 */
class Assets
{

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct()
	{

		$this->_setup_hooks();
	}

	/**
	 * Initializes the theme assets.
	 *
	 */
	protected function _setup_hooks()
	{

		add_action('wp_enqueue_scripts', [$this, 'enqueue_assets'], 11);
		add_action(\PMC\Global_Functions\Styles::INLINE_CSS_HOOK, [$this, 'load_fonts']);
	}

	/**
	 * Enqueue Child Theme Assets
	 *
	 * @action wp_enqueue_scripts, 11
	 *
	 */
	public function enqueue_assets()
	{

		// Don't enqueue anything if on variety-500 template
		if (Templates::is_home() || Templates::is_search() || Templates::is_profile()) {
			return;
		}

		$path = CHILD_THEME_URL . '/assets/build/';

		// JS
		$fmtime = filemtime(CHILD_THEME_PATH . '/assets/public/webfontloader.js');
		wp_register_script(
			'variety-webfonts-js',
			CHILD_THEME_URL . '/assets/public/webfontloader.js',
			[],
			$fmtime,
			true
		);
		wp_enqueue_script('variety-webfonts-js');

		$fmtime = filemtime(CHILD_THEME_PATH . '/assets/build/js/common.js');
		wp_register_script(
			'variety-common-js',
			CHILD_THEME_URL . '/assets/build/js/common.js',
			['jquery'],
			$fmtime,
			true
		);
		wp_enqueue_script('variety-common-js');

		$fmtime = filemtime(CHILD_THEME_PATH . '/assets/build/js/tbm.js');
		wp_register_script(
			'tbm-js',
			CHILD_THEME_URL . '/assets/build/js/tbm.js',
			['jquery'],
			$fmtime,
			true
		);
		wp_enqueue_script('tbm-js');
		wp_localize_script('tbm-js', 'tbm', ['ajaxurl' => admin_url('admin-ajax.php')]);

		// Inline, critical CSS.
		\PMC\Core\Inc\Assets::get_instance()->inline_style('common.inline', CHILD_THEME_PATH);

		// Loaded async.
		$fmtime = filemtime(CHILD_THEME_PATH . '/assets/build/css/common.async.css');
		wp_register_style(
			'common.async',
			CHILD_THEME_URL . '/assets/build/css/common.async.css',
			[],
			$fmtime,
			'all'
		);
		wp_enqueue_style('common.async');

		if (is_front_page() || is_page_template('page-vip.php')) {
			// Inline, critical CSS.
			\PMC\Core\Inc\Assets::get_instance()->inline_style('frontpage.inline', CHILD_THEME_PATH);

			// Loaded async.
			$fmtime = filemtime(CHILD_THEME_PATH . '/assets/build/css/frontpage.async.css');
			wp_register_style(
				'frontpage.async',
				CHILD_THEME_URL . '/assets/build/css/frontpage.async.css',
				[],
				$fmtime,
				'all'
			);
			wp_enqueue_style('frontpage.async');

			// Load JS related to frontpage page.
			wp_enqueue_script('frontpage-js', $path . 'js/frontpage.js', ['jquery'], false, true);
		}

		if (is_page()) {
			// Inline, critical CSS.
			\PMC\Core\Inc\Assets::get_instance()->inline_style('page.inline', CHILD_THEME_PATH);

			// Load JS related to page.
			wp_enqueue_script('page-js', $path . 'js/page.js', ['jquery'], false, true);

			$fmtime = filemtime(CHILD_THEME_PATH . '/assets/build/js/page.js');
			wp_register_script(
				'variety-page-js',
				CHILD_THEME_URL . '/assets/build/js/page.js',
				['jquery'],
				$fmtime,
				true
			);
			wp_enqueue_script('variety-page-js');
		}

		// For each chunk/template query, as needed.
		if (is_singular() || is_post_type_archive([Content::VIP_POST_TYPE, Content::VIP_VIDEO_POST_TYPE]) || is_tax([Content::VIP_PLAYLIST_TAXONOMY])) {
			// Load JS related to single page.
			wp_enqueue_script('single-js', $path . 'js/single.js', ['jquery'], false, true);

			\PMC\Core\Inc\Assets::get_instance()->inline_style('single.inline', CHILD_THEME_PATH);

			$fmtime = filemtime(CHILD_THEME_PATH . '/assets/build/css/single.async.css');
			wp_register_style(
				'single.async',
				CHILD_THEME_URL . '/assets/build/css/single.async.css',
				[],
				$fmtime,
				'all'
			);
			wp_enqueue_style('single.async');
		}

		if (is_archive() || is_page_template('page-vip.php')) {
			// Inline, critical CSS.
			\PMC\Core\Inc\Assets::get_instance()->inline_style('category.inline', CHILD_THEME_PATH);
		}

		if (is_author()) {
			// Inline, critical CSS.
			\PMC\Core\Inc\Assets::get_instance()->inline_style('author.inline', CHILD_THEME_PATH);

			$fmtime = filemtime(CHILD_THEME_PATH . '/assets/build/css/author.async.css');
			wp_register_style(
				'author.async',
				CHILD_THEME_URL . '/assets/build/css/author.async.css',
				[],
				$fmtime,
				'all'
			);
			wp_enqueue_style('author.async');
		}

		if (\Variety\Inc\Featured_Article::get_instance()->is_featured_article()) {
			// Inline, critical CSS.
			\PMC\Core\Inc\Assets::get_instance()->inline_style('featured-article.inline', CHILD_THEME_PATH);

			$fmtime = filemtime(CHILD_THEME_PATH . '/assets/build/css/featured-article.async.css');
			wp_register_style(
				'featured-article.async',
				CHILD_THEME_URL . '/assets/build/css/featured-article.async.css',
				[],
				$fmtime,
				'all'
			);
			wp_enqueue_style('featured-article.async');
		}

		if (is_page('results')) {

			// Inline, critical CSS.
			\PMC\Core\Inc\Assets::get_instance()->inline_style('search-results.inline', CHILD_THEME_PATH);

			$fmtime = filemtime(CHILD_THEME_PATH . '/assets/build/css/search-results.async.css');
			wp_register_style(
				'search-results.async',
				CHILD_THEME_URL . '/assets/build/css/search-results.async.css',
				[],
				$fmtime,
				'all'
			);
			wp_enqueue_style('search-results.async');
		}

		//VIP Marketing Video
		if (is_page_template('page-templates/marketing-landing.php') || is_tag('documentaries-to-watch')) {
			wp_enqueue_script(
				'marketing-landing-yHdpIsEW',
				'https://content.jwplatform.com/libraries/yHdpIsEW.js',
				[],
				false,
				true
			);
		}

		$fmtime = filemtime(CHILD_THEME_PATH . '/assets/build/css/tbm.css');
		wp_register_style(
			'tbm',
			CHILD_THEME_URL . '/assets/build/css/tbm.css',
			[],
			$fmtime,
			'all'
		);
		wp_enqueue_style('tbm');

		// VIP Classic comments script
		/* if (is_singular() && get_option('thread_comments') && !is_singular(\PMC\Gallery\Defaults::NAME)) {
			wp_enqueue_script('comment-reply');
		} */

		if (is_singular()) {
			//Dequeue this script since there is already js that pmc-outbrain widget enqueues this
			wp_dequeue_script('pmc_outbrain_partner_js');
		}

		// if (!\Variety\Plugins\Variety_VIP\Content::is_vip_page()) {
		// 	wp_enqueue_script('variety-non-vip-js', $path . 'js/variety_non_vip.js', [], false, true);
		// }
	}

	/**
	 * Load fonts after critical CSS.
	 *
	 * @throws \Exception Invalid file path.
	 */
	public function load_fonts(): void
	{
		// Don't load anything if on variety-500 template
		if (Templates::is_home() || Templates::is_search() || Templates::is_profile()) {
			return;
		}

		\PMC::render_template(
			sprintf('%s/template-parts/fonts/webfonts.php', untrailingslashit(CHILD_THEME_PATH)),
			[],
			true
		);
	}
}
