<?php

/**
 * Class for Gallery Setup
 * registers post type
 *
 * registers scripts
 *  Captioning customizations to the WP media manager
 *  Media manager customizations for pmc-gallery post types (stand-alone galleries)
 *  WP settings page customization when managing gallery settings
 *  http://swipejs.com/
 *  http://benalman.com/projects/jquery-hashchange-plugin/
 *  http://jonnystromberg.com/hash-js
 *  http://www.quirksmode.org/js/detect.html
 *  The main frontend script that makes pmc-gallery work
 *
 * pmc_gallery_standalone_slug filter allows the gallery url to be something other than gallery
 *
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Gallery_Defaults
{

	use Singleton;

	const name = 'pmc-gallery';

	const previous_link_html = '&larr;&nbsp;Previous';

	const next_link_html = 'Next&nbsp;&rarr;';

	public static $url = null;

	/**
	 * @codeCoverageIgnore
	 */
	protected function __construct()
	{
		add_action('init', array($this, 'action_init'));
		add_action('load-post-new.php', array($this, '_action_load_new_post'));
		self::$url = PMC_GALLERY_PLUGIN_URL;

		add_filter('pmc_http_status_410_urls', [$this, 'maybe_set_single_image_410_redirect']);
		add_filter('pmc_sitemaps_post_type_whitelist', [$this, 'whitelist_post_type_for_sitemaps']);
	}

	public function action_init()
	{
		$this->register_post_types();
		$this->register_scripts_and_styles();
		$this->_add_rewrite_rules();
	}

	public function register_post_types()
	{
		register_post_type(
			PMC_Gallery_Defaults::name,
			array(
				'labels' => array(
					'name' => 'Galleries',
					'singular_name' => 'Gallery',
					'add_new' => 'Add New Gallery',
					'add_new_item' => 'Add New Gallery',
					'edit' => 'Edit Gallery',
					'edit_item' => 'Edit Gallery',
					'new_item' => 'New Gallery',
					'view' => 'View Gallery',
					'view_item' => 'View Gallery',
					'search_items' => 'Search Galleries',
					'not_found' => 'No Gallery found',
					'not_found_in_trash' => 'No Gallery found in Trash',
				),
				'public' => true,
				'menu_position' => 5,
				'supports' => array('title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail', 'custom-fields', 'trackbacks', 'revisions'),
				'taxonomies' => array('category', 'post_tag'),
				'menu_icon' => PMC_Gallery_Defaults::$url . 'images/icon.png',
				'has_archive' => true,
				'rewrite' => array(
					'slug' => apply_filters('pmc_gallery_standalone_slug', 'gallery')
				)
			)
		);
	}

	/**
	 * Mark new pmc-gallery posts as excluded from landing pages
	 *
	 * @internal called during load-post-new.php so only new galleries are affected
	 * @param  int     $post_id Post ID.
	 * @param  WP_Post $post    Post object.
	 * @param  bool    $update  Whether this is an existing post being updated or not.
	 * @return null
	 */
	public function _action_save_post_pmc_gallery($post_id, $post, $update)
	{

		// Only add the Exclusion term to new galleries if
		$term_exists = term_exists('exclude-from-section-fronts', '_post-options');

		// the term already exists.
		if (!empty($term_exists)) {
			wp_set_object_terms($post_id, array('exclude-from-section-fronts'), '_post-options');
		}
	}

	/**
	 * Run some code during a new pmc-gallery post creation
	 *
	 * Exclude new gallery posts from landing pages by default
	 *
	 * Authors/editors can remove the exclusion if they want (deselect taxonomy term)
	 *
	 * @param null
	 * @return null
	 */
	public function _action_load_new_post()
	{
		global $typenow;

		if (PMC_Gallery_Defaults::name === $typenow) {
			add_action('save_post_' . PMC_Gallery_Defaults::name, array($this, '_action_save_post_pmc_gallery'), 10, 3);
		}
	}

	/**
	 * IMPORTANT: This function only register scripts and styles to be use via action hook: wp_enqueue_scripts & admin_enqueue_scripts where needed
	 */
	public function register_scripts_and_styles()
	{
		$prefix = '.min';
		if (defined('SCRIPT_DEBUG') && true === SCRIPT_DEBUG) {
			$prefix = '';
		}
		wp_enqueue_script('media-grid');
		// Captioning customizations to the WP media manager.
		// Applies to regular post (embedded) galleries and pmc-gallery post types (stand-alone galleries).
		if (defined('SCRIPT_DEBUG') && true === SCRIPT_DEBUG) {
			wp_register_script(PMC_Gallery_Defaults::name . '-admin', PMC_Gallery_Defaults::$url . 'js/admin/admin-script-tmce-4.js', array('jquery', 'media-views'));

			// extension of wp.media.Post.
			wp_register_script(PMC_Gallery_Defaults::name . '-attachment-detail-two-columns', PMC_Gallery_Defaults::$url . 'js/admin/details-two-columns.js', array('jquery', 'media-views', 'media-grid'), false, true);
			wp_register_script(PMC_Gallery_Defaults::name . '-attachment-compat', PMC_Gallery_Defaults::$url . 'js/admin/attachment-compat.js', array('jquery', 'media-views', 'media-grid'), false, true);
			wp_register_script(PMC_Gallery_Defaults::name . '-edit-attachments', PMC_Gallery_Defaults::$url . 'js/admin/edit-attachments.js', array('jquery', 'media-views', 'media-grid', PMC_Gallery_Defaults::name . '-attachment-detail-two-columns', PMC_Gallery_Defaults::name . '-attachment-compat'), false, true);
			wp_register_script(PMC_Gallery_Defaults::name . '-attachment', PMC_Gallery_Defaults::$url . 'js/admin/attachment.js', array('jquery', 'media-views', PMC_Gallery_Defaults::name . '-edit-attachments'), false, true);
			wp_register_script(PMC_Gallery_Defaults::name . '-select-all', PMC_Gallery_Defaults::$url . 'js/admin/select-all.js', array('jquery', 'media-views', PMC_Gallery_Defaults::name . '-edit-attachments'), false, true);
			wp_register_script(PMC_Gallery_Defaults::name . '-post', PMC_Gallery_Defaults::$url . 'js/admin/post.js', array('jquery', 'media-views', PMC_Gallery_Defaults::name . '-attachment', PMC_Gallery_Defaults::name . '-select-all'), false, true);

			wp_register_script(PMC_Gallery_Defaults::name . '-google-analytics', PMC_Gallery_Defaults::$url . 'js/admin/google-analytics.js', array(), false, true);
			// Media manager customizations for pmc-gallery post types (stand-alone galleries).
			wp_register_script(PMC_Gallery_Defaults::name . '-admin-post', PMC_Gallery_Defaults::$url . 'js/admin/admin-post.js', array('jquery', 'media-views', PMC_Gallery_Defaults::name . '-post', PMC_Gallery_Defaults::name . '-google-analytics'), false, true);

			// WP settings page customization when managing gallery settings.
			wp_register_script(PMC_Gallery_Defaults::name . '-admin-settings', PMC_Gallery_Defaults::$url . 'js/admin/admin-settings.js', array('jquery'));
		} else {
			// @codeCoverageIgnoreStart
			wp_register_script(PMC_Gallery_Defaults::name . '-admin-post', PMC_Gallery_Defaults::$url . 'js/admin-gallery.min.js', array('jquery', 'media-views', 'media-grid'), '1.1', true);
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		wp_localize_script(
			PMC_Gallery_Defaults::name . '-admin-post',
			'pmcGalleryV3AdminGallery',
			[
				'pmcBuyNowEnabled' => class_exists('\PMC\Buy_Now\Admin_UI', false),
			]
		);
		// @codeCoverageIgnoreEnd
		wp_register_style(PMC_Gallery_Defaults::name . '-admin-post', PMC_Gallery_Defaults::$url . 'css/admin-post' . $prefix . '.css');

		// http://swipejs.com/
		// "Swipe is the most accurate touch slider."
		// @todo Move to pmc-global-functions
		if (!wp_script_is('swipe-js', 'registered')) {
			wp_register_script('swipe-js', PMC_Gallery_Defaults::$url . 'js/swipe.js', array(), '1.0');
		}

		// http://benalman.com/projects/jquery-hashchange-plugin/
		// "This jQuery plugin enables very basic bookmarkable #hash history via a cross-browser HTML5 window.onhashchange event."
		// @todo Maybe move to pmc-global-functions (maybe not since we're going to use pushState more?)
		if (!wp_script_is('jquery-hashchange', 'registered')) {
			wp_register_script('jquery-hashchange', PMC_Gallery_Defaults::$url . 'js/hashchange.js', array('jquery'), '1.3');
		}

		// Based on hash.js - http://jonnystromberg.com/hash-js
		// @todo Move to pmc-global-functions
		if (!wp_script_is('qs-js', 'registered')) {
			wp_register_script('qs-js', PMC_Gallery_Defaults::$url . 'js/qs.js');
		}

		// http://www.quirksmode.org/js/detect.html
		// "A useful but often overrated JavaScript function is the browser detect. Sometimes you want to give specific instructions or load a new page in case the viewer uses, for instance, Safari."
		// @todo Change to https://github.com/NielsLeenheer/WhichBrowser — browserdetect.js is no longer maintained
		// @todo Move to pmc-global-functions
		if (!wp_script_is('browserdetect', 'registered')) {
			wp_register_script('browserdetect', PMC_Gallery_Defaults::$url . 'js/browserdetect.js');
		}

		// The main frontend script that makes pmc-gallery work
		wp_register_script(PMC_Gallery_Defaults::name, PMC_Gallery_Defaults::$url . 'js/gallery.js', array('jquery', 'jquery-hashchange', 'qs-js', 'swipe-js', 'browserdetect'));
		wp_register_style(PMC_Gallery_Defaults::name, PMC_Gallery_Defaults::$url . 'css/default' . $prefix . '.css');
	} // function

	/**
	 * Defines a rewrite rule for the single image page from gallery
	 */
	protected function _add_rewrite_rules()
	{
		$slug = sanitize_title_with_dashes(apply_filters('pmc_gallery_standalone_slug', 'gallery'));
		add_rewrite_rule(preg_quote($slug) . '/(.+)/(.+)/?$', 'index.php?pmc-gallery=$matches[1]&pmc-gallery-image=$matches[2]', 'top');
		add_rewrite_tag('%pmc-gallery-image%', '([^/]+)');
	}

	/**
	 * To add url of single image of gallery that need to be set 410 status
	 *
	 * @since 2018-02-06 - Jignesh Nakrani - CDWE-897
	 *
	 * @param  array $removed_urls List of urls.
	 *
	 * @return array List of urls
	 */
	public function maybe_set_single_image_410_redirect($removed_urls)
	{

		if (!empty(get_query_var('pmc-gallery-image'))) {
			if (empty($removed_urls) || !is_array($removed_urls)) {
				$removed_urls = array();
			}

			$request_uri = \PMC::filter_input(INPUT_SERVER, 'REQUEST_URI');
			$request_uri = trim(wp_parse_url($request_uri, PHP_URL_PATH), '/');

			$removed_urls[$request_uri] = 1;
		}
		return $removed_urls;
	}

	/**
	 * Whitelist post type for sitemap.
	 *
	 * @param  array $post_types List of post type for site map.
	 *
	 * @return array List of post type for site map.
	 */
	public function whitelist_post_type_for_sitemaps($post_types)
	{

		$post_types = (!empty($post_types) && is_array($post_types)) ? $post_types : [];

		if (!in_array(self::name, (array) $post_types, true)) {
			$post_types[] = self::name;
		}

		return $post_types;
	}
}

PMC_Gallery_Defaults::get_instance();

//EOF
