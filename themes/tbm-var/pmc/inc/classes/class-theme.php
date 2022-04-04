<?php

namespace PMC\Core\Inc;

/**
 * Theme | www/wp-content/themes/vip/pmc-core-v2/inc/classes/class-theme.php
 *
 * @since 2017-12-08
 *
 * @version 2017-12-08 - brandoncamenisch - PMCVIP-2907:
 * - This is the main class for the theme. What would normally go in function.php
 * such as theme support, plugin activations, registering widgets, etc. should go
 * into this file.
 *
 **/
class Theme
{

	use \PMC\Global_Functions\Traits\Singleton;

	/**
	 * __construct | www/wp-content/themes/vip/pmc-core-v2/inc/classes/class-theme.php
	 *
	 * @since 2017-12-07 - Class constructor
	 *
	 * @author brandoncamenisch
	 * @version 2017-12-07 - feature/PMCVIP-2907:
	 * - Adding script tag async filter and dockblock for constructor
	 *
	 **/
	protected function __construct()
	{
		$this->_load_plugins();
		$this->_instantiate_singletons();
		$this->_register_widgets();

		add_action('transition_post_status', [$this, 'clear_cache'], 10, 3);
		add_action('after_setup_theme', [$this, 'theme_setup']);
		add_action('widgets_init', [$this, 'register_sidebars']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
		add_filter('script_loader_tag', [$this, 'async_scripts'], 11, 3); // <-- Notice priority 11 for VIP environment
		add_filter('document_title_parts', [$this, 'get_single_page_post_title']); // hook sets aside title from SEO meta box.

		// Hide Admin Bar
		add_filter('show_admin_bar', [$this, '_show_admin_bar']);
		remove_action('wp_head', '_admin_bar_bump_cb');

		add_action('admin_init', [$this, '_admin_init']);

		// Setting this at priority 9 to allow child themes to override if needed
		add_filter('pmc_fieldmanager_version', [$this, 'get_fieldmanager_version'], 9);

		// Ad in content
		add_filter('the_content', array($this, 'inject_ads'));

		// Add Vertical column to Posts list
		add_filter('manage_post_posts_columns', [$this, 'manage_post_posts_columns']);
		add_action('manage_posts_custom_column', [$this, 'manage_posts_custom_column'], 99, 2);

		add_action('pre_get_posts', [$this, 'author_page_exclude_custom_author_posts']);
	}

	public function manage_post_posts_columns($columns)
	{
		$columns['vertical'] = __('Vertical', 'pmc-variety');
		// return $columns;

		unset($columns['coauthors'], $columns['comments']);
		$n_columns = array();
		$move = 'vertical';
		$before = 'categories';
		foreach ($columns as $key => $value) {
			if ($key == $before) {
				$n_columns[$move] = $move;
			}
			$n_columns[$key] = $value;
		}
		$n_columns['coauthors'] = 'Authors';
		return $n_columns;
	}
	function manage_posts_custom_column($column, $post_id)
	{
		switch ($column) {
			case 'vertical':
				$terms = get_the_term_list($post_id, 'vertical', '', ', ', '');
				if (is_string($terms))
					echo $terms;
				break;
			case 'coauthors':
				$custom_author = get_post_meta($post_id, 'author', true);
				echo $custom_author ? '(' . $custom_author . ')' : '';
				break;
		}
	}

	/*
	* Show admin bar only for admins and editors
	*/
	public function _show_admin_bar()
	{
		return current_user_can('edit_posts');
	}

	/**
	 * Redirect non-admin users to home page
	 */
	public function _admin_init()
	{
		if (!current_user_can('edit_posts') && !current_user_can('snaps') && ('/wp-admin/admin-ajax.php' != $_SERVER['PHP_SELF'])) {
			wp_redirect(home_url());
			exit;
		}
	}

	/*
	* Inject Ads in content
	*/
	public function inject_ads($content)
	{

		if (function_exists('get_field') && (get_field('disable_ads') || get_field('disable_ads_in_content'))) :
			return $content;
		endif;

		if (function_exists('amp_is_request') && amp_is_request()) {
			return $content;
		}

		if (is_singular('page')) {
			return $content;
		}

		if (
			(function_exists('get_field') && get_field('paid_content'))
			|| is_page_template('single-template-featured.php')
			|| 'post' != get_post_type()
		) :
			return $content;
		endif;

		$count_articles = isset($_POST['count_articles']) ? (int) $_POST['count_articles'] : 1;

		$after_para = function_exists('get_field') && get_field('ads_after') ? get_field('ads_after') : 3;

		ob_start();
		pmc_adm_render_ads('incontent_1');
		$content_ad_tag = ob_get_contents();
		ob_end_clean();
		if ($after_para == 0) {
			$content = '<div class="ad-mrec" id="ad-incontent-' . $count_articles . '">' . $content_ad_tag . '</div>' . $content;
		} else {
			$content = $this->insert_after_paragraph('<div class="ad-mrec" id="ad-incontent-' . $count_articles . '">' . $content_ad_tag . '</div>', $after_para, $content);
		}

		return $content;
	}

	public function insert_after_paragraph($insertion, $paragraph_id, $content)
	{
		$closing_p = '</p>';
		$paragraphs = explode($closing_p, $content);
		if (count($paragraphs) <= ($paragraph_id + 1)) :
			return $content;
		endif;
		foreach ($paragraphs as $index => $paragraph) {
			if (trim($paragraph)) {
				$paragraphs[$index] .= $closing_p;
			}
			if ($paragraph_id == $index + 1) {
				$paragraphs[$index] .= $insertion;
			}
		}
		return implode('', $paragraphs);
	}

	private function _instantiate_singletons()
	{
		Admin::get_instance();
		Fieldmanager\Fields::get_instance();
	}

	private function _register_widgets()
	{
		// register_widget('\PMC\Core\Inc\Widgets\Social_Profiles');
		// register_widget('\PMC\Core\Inc\Widgets\Newsletter');
		// register_widget('\PMC\Core\Inc\Widgets\Trending_Now');

		require_once CHILD_THEME_PATH . '/widgets/class-jobs.php';
		register_widget('\TBM\Jobs');
	}

	/**
	 * enqueue_assets | www/wp-content/themes/vip/pmc-core-v2/inc/classes/class-theme.php
	 *
	 * @since 2017-12-05 - Enqueues the main site assets for pmc-core-v2 theme.
	 * @uses wp_enqueue_script, wp_enqueue_style, get_template_directory_uri
	 *
	 * @author brandoncamenisch
	 * @version 2017-12-05 - feature/PMCVIP-2907:
	 * - Adding docblock and updating the asset path.
	 *
	 **/
	public function enqueue_assets()
	{
		$url = get_template_directory_uri() . '/assets/build/';
		wp_enqueue_script('pmc-core-site-js', $url . 'js/site.bundle.js', ['jquery'], false, true);
		// wp_enqueue_style('pmc-core-site-css', $url . 'css/site.css');
	}

	public function register_sidebars()
	{

		register_sidebar(
			array(
				'name'          => 'Home right sidebar',
				'id'            => 'home_right_1',
				'before_widget' => '<div>',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="rounded">',
				'after_title'   => '</h2>',
			)
		);

		register_sidebar(
			array(
				'name'          => 'Archive right sidebar',
				'id'            => 'archive_right_1',
				'before_widget' => '<div>',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="rounded">',
				'after_title'   => '</h2>',
			)
		);

		register_sidebar(
			array(
				'name'          => __('Gallery Right Sidebar', 'pmc'),
				'id'            => 'gallery-right',
				'before_widget' => false,
				'after_widget'  => false,
				'before_title'  => false,
				'after_title'   => false,
			)
		);

		register_sidebar(
			array(
				'name'          => __('Article Right Sidebar', 'pmc'),
				'id'            => 'article_right_sidebar',
				'before_widget' => false,
				'after_widget'  => false,
				'before_title'  => false,
				'after_title'   => false,
			)
		);
	}

	private function _load_plugins()
	{
		/**
		 * Filter the list of plugins to load for this theme.
		 *
		 * @param array $plugins Plugins to be passed to {@see load_pmc_plugins()}.
		 */

		// Plugins should be loaded alphabetically
		load_pmc_plugins([
			'plugins'     => [
				'add-meta-tags-mod',
				'ajax-comment-loading',
				'apple-news'              => '1.3',
				'cache-nav-menu',
				'cheezcap',
				'co-authors-plus'         => '3.4',
				'custom-metadata',
				'edit-flow',
				'fieldmanager'            => '1.1',    // When this version is updated, update the version set for 'pmc_fieldmanager_version' hook as well
				'multiple-post-thumbnails',
				'safe-redirect-manager',
				'wpcom-legacy-redirector' => '1.3.0',
				'wpcom-thumbnail-editor',
				'zoninator'               => '0.7',
			],
			// pmc-plugins should be loaded alphabetically
			'pmc-plugins' => [
				'fm-widgets',
				'fm-zones',
				'pmc-ad-placeholders',
				'pmc-adm',
				'pmc-apple-news',
				'pmc-carousel',
				'pmc-content-exchange',
				'pmc-custom-endpoint-template',
				'pmc-custom-feed-v2',
				'pmc-event-tracking',
				'pmc-exacttarget',
				'pmc-facebook-instant-articles',
				'pmc-featured-image-backdoor',
				'pmc-featured-video-override',
				'pmc-field-overrides',
				'pmc-footer',
				'pmc-gallery-v3',
				'pmc-geo-uniques',
				'pmc-google-amp',
				'pmc-google-content-experiments',
				'pmc-google-universal-analytics',
				'pmc-groups',
				'pmc-guest-authors',
				'pmc-header-bidding',
				'pmc-js-libraries',
				'pmc-linkcontent',
				'pmc-nofollow-whitelist',
				'pmc-options',
				'pmc-outbrain',
				'pmc-post-options',
				'pmc-post-savior',
				'pmc-primary-taxonomy',
				'pmc-related-articles',
				'pmc-seo-backdoor',
				'pmc-seo-tweaks',
				'pmc-sitemaps',
				'pmc-social-share-bar',
				'pmc-sticky-ads',
				'pmc-sticky-posts',
				'pmc-sticky-rail-ads',
				'pmc-swiftype',
				'pmc-tags',
				'pmc-tag-links',
				'pmc-templatized-widgets',
				'pmc-twitter-cards',
				'pmc-video-player',
				'wp-help', //VIP deprecated plugin now in our pmc-plugins repo
			],
		]);

		/*
		 * Referencing this from Global namespace because PHP has started
		 * throwing an error on production all of a sudden where it thinks that
		 * this function is in this class' namespace which should not be the case
		 * as PHP defaults to global namespace for all standalone functions
		 *
		 * @since 2018-03-20 Amit Gupta
		 */
		if (function_exists('\wpcom_vip_enable_opengraph')) {
			\wpcom_vip_enable_opengraph();
		}

		// The core's plugins resided at theme level that didn't make it to pmc-plugins
		$core_plugins = [
			'filter-posts',
			'admin-page-locking',
		];

		// Allow child theme to disable the plugin entirely?
		$core_plugins = apply_filters('pmc_core_plugins', $core_plugins);

		if ($core_plugins && is_array($core_plugins)) {
			foreach ($core_plugins as $plugin) {
				$plugin_path = PMC_CORE_PATH . '/plugins/' . $plugin . '/' . $plugin . '.php';
				if (!file_exists($plugin_path)) {
					throw new \Exception(sprintf('Error loading pmc core plugin: %s', $plugin_path));
				}
				require_once($plugin_path);
			}
		}
	}

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which runs
	 * before the init hook. The init hook is too late for some features, such as
	 * indicating support post thumbnails.
	 */
	public function theme_setup()
	{
		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
		 */
		add_theme_support('post-thumbnails');
		add_theme_support('title-tag');
	}

	/**
	 * get_the_post_thumbnail | class-theme.php
	 * @since 2017-08-17 - A helper method that wraps get_attachment_image so a
	 * post id can be used instead of an attachment_id. Get the post thumbnail
	 * based on a desktop of mobile context.
	 * @uses get_attachment_image
	 *
	 * @author brandoncamenisch
	 * @version 2017-08-18 - feature/PMCVIP-2818:
	 * - Wraps get_attachment_image
	 *
	 * @param int|WP_Post $post Optional/Post ID or WP_Post object default global `$post`.
	 * @param string $desktop_size Slug of the image size for desktop
	 * @param string $mobile_size  Slug of the image size for mobile
	 * @param bool   $lazyload
	 * @param array  $attr
	 *
	 * @return string
	 */
	public static function get_the_post_thumbnail($post = null, $desktop_size = '', $mobile_size = null, $lazyload = true, $attr = array())
	{
		$attachment_id = get_post_thumbnail_id($post);

		if (!\PMC::is_mobile() || is_null($mobile_size)) {
			return self::get_attachment_image($attachment_id, $desktop_size, $lazyload, $attr);
		} else {
			return self::get_attachment_image($attachment_id, $mobile_size, $lazyload, $attr);
		}
	}

	/**
	 * get_attachment_image | class-theme.php
	 * @since 2017-08-17 - A helper method to wrap around wp_get_attachment_image
	 * @uses wp_get_attachment_image_src, wp_get_attachment_image
	 *
	 * @author brandoncamenisch
	 * @version 2017-08-17 - feature/PMCVIP-2818:
	 * - Initial version of helper method on borrowed code from HL, VY, and TVL.
	 *
	 * @param $attachment_id int Attachment id for get image.
	 * @param $size string|array Defaults to thumbnail.
	 * @param $lazyload bool Whether to use lazyloading feature.
	 * @param $attr array Passed through to `wp_get_attachment_image`.
	 *
	 * @return string of markup for a default image.
	 **/
	public static function get_attachment_image($attachment_id, $size = 'thumbnail', $lazyload = true, $attr = [], $echo = false)
	{
		if (!empty($attachment_id)) {
			if ($lazyload) {
				$attr['class'] = !empty($attr['class']) ? $attr['class'] . ' lazyload' : 'lazyload';
				$image         = wp_get_attachment_image_src($attachment_id, $size);
				if (empty($image)) {
					return false;
				}
				$attr['data-src'] = isset($image[0]) ? $image[0] : '';
				$attr['itemprop'] = 'thumbnail';
				// Image is encoded as a square 845x845px transparent image as a placeholder
				$attr['src'] = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
			}
			if ($echo) {
				echo wp_get_attachment_image($attachment_id, $size, false, $attr);
				return true;
			} else {
				return wp_get_attachment_image($attachment_id, $size, false, $attr);
			}
		} else {
			return false;
		}
	}

	/**
	 * @since 2017-07-31 Amit Sannad
	 *        Get breadcrumb for templates to display.
	 * @return array
	 */
	public function get_breadcrumb()
	{

		$breadcrumb = [];

		// single posts
		$post_id = get_queried_object_id();

		$vertical = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy($post_id, 'vertical');

		if (!empty($vertical->name)) {
			$breadcrumb[] = $vertical;
		}

		$category_id = apply_filters('breadcrumb_primary_category_id', get_post_meta($post_id, 'categories', true));

		if (!empty($category_id)) {
			$category = get_term_by('id', $category_id, 'category');
		} else {
			$category = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy($post_id, 'category');
		}

		if (!empty($category->name)) {
			$breadcrumb[] = $category;
		}

		$sub_category = '';
		$sub_cat_id   = apply_filters('breadcrumb_secondary_category_id', get_post_meta($post_id, 'subcategories', true));

		if (!empty($sub_cat_id)) {
			$sub_category = get_term_by('id', $sub_cat_id, 'category');
		}

		if (!empty($sub_category->name) && $category->name !== $sub_category->name) {
			$breadcrumb[] = $sub_category;
		}

		if (is_page()) {
			$post_id   = get_queried_object_id();
			$ancestors = get_post_ancestors($post_id);

			if (is_array($ancestors)) {
				$ancestors = array_reverse($ancestors);
			} else {
				$ancestors = [];
			}

			$ancestors[] = $post_id;

			foreach ($ancestors as $ancestor) {
				$item         = [];
				$item['name'] = get_the_title($ancestor);
				$item['link'] = get_permalink($ancestor);

				$breadcrumb[] = (object) $item;
			}
		}

		return $breadcrumb;
	}

	/**
	 * Get the primary term in a given taxonomy for a given (or the current) post.
	 *
	 * Terms are ordered in this site, and this function grabs the first term in the
	 * list, consider that the "primary" term in that taxonomy.
	 *
	 * @param  string $taxonomy Taxonomy for which to get the primary term.
	 * @param  int    $post_id  Optional. Post ID. If absent, uses current post.
	 *
	 * @return boolean|WP_Term WP_Term on success, false on failure.
	 */
	public function get_the_primary_term($taxonomy, $post_id = null)
	{

		_deprecated_function(__METHOD__, '2.0', '\PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy()');

		if (!$post_id) {
			$post_id = get_the_ID();
		}

		$key = "pmc_primary_{$taxonomy}_{$post_id}";

		$primary_term = wp_cache_get($key);
		if (false === $primary_term) {

			// This has to use `wp_get_object_terms()` because we order them
			$terms = wp_get_object_terms($post_id, $taxonomy, ['orderby' => 'term_order']);

			if (!empty($terms) && !is_wp_error($terms)) {
				$primary_term = reset($terms);
				$primary_term = $primary_term->term_id;
			} else {
				$primary_term = 'none'; // if there are no terms, still cache that so we don't db lookup each time
			}

			wp_cache_set($key, $primary_term, '', HOUR_IN_SECONDS); // invalidated on change
		}

		return 'none' === $primary_term ? false : get_term($primary_term, $taxonomy);
	}

	/**
	 * Clear cache on post publish
	 *
	 * @param $new_status New Post status from wp_posts table.
	 * @param $old_status Old Post status from wp_posts table
	 * @param $post       WP_Post object.
	 */
	public function clear_cache($new_status, $old_status, $post)
	{
		if ('publish' !== $new_status) {
			//Do nothing and return
			return;
		}

		$taxonomies = get_object_taxonomies(get_post_type($post->ID));

		if (!empty($taxonomies) && is_array($taxonomies)) {
			foreach ($taxonomies as $taxonomy) {
				$key = "pmc_primary_{$taxonomy}_{$post->ID}";
				wp_cache_delete($key);
			}
		}
	}

	/**
	 * Get hed/dek from pmc-field-overrides plugin.
	 */
	public function get_field_overrides($post)
	{

		if (empty($post)) {
			$post = get_post(get_the_ID());
		}

		if (empty($post)) {
			return;
		}

		return [
			'hed' => pmc_get_title($post),
			'dek' => pmc_get_excerpt($post),
		];
	}

	/**
	 * Get featured video HTML from pmc-featured-video-override plugin
	 *
	 * @param int $post_id WP_Post ID
	 *
	 * @return bool|string|void
	 */
	public static function get_featured_video($post_id = null, $args = [])
	{

		if (empty($post_id)) {
			$post_id = get_the_ID();
		}

		if (empty($post_id)) {
			return;
		}

		if (!is_int($post_id)) {
			return;
		}

		$width = 605;

		if (\PMC::is_mobile()) {
			$width = 300;
		}

		$featured_video_arg = [
			'width' => $width,
		];

		if (is_array($args)) {
			$featured_video_arg = array_merge($featured_video_arg, $args);
		}

		$video_escaped_html = \PMC_Featured_Video_Override::get_video_html($post_id, $featured_video_arg);

		return $video_escaped_html;
	}

	/**
	 * Get Linked gallery data
	 *
	 * @param int $post_id WP_Post ID
	 *
	 * @return array
	 */
	public function get_featured_gallery($post_id)
	{

		if (empty($post_id)) {
			$post_id = get_the_ID();
		}

		$default_return = ['gallery' => ''];

		if (empty($post_id)) {
			return $default_return;
		}

		if (!is_int($post_id)) {
			return $default_return;
		}

		$linked_gallery = \PMC_Gallery_View::get_linked_gallery_data($post_id);

		if (empty($linked_gallery->ID)) {
			return $default_return;
		}

		$gallery = \PMC_Gallery_View::get_instance()->load_gallery($linked_gallery->id, 0);

		return ['gallery' => $gallery];
	}

	/**
	 * Get a human_time_diff(), time, or date a post was published based on its age.
	 *
	 * @param int|WP_Post $post Post ID or object. Default current post.
	 *
	 * @return string A {@see human_time_diff()} if the post is less than an hour
	 *     old, the time it was published if it was published today, the date it was
	 *     published otherwise.
	 */
	public function get_relative_post_date($post = null)
	{

		if (empty($post)) {
			$post = get_post();
		}

		if (empty($post)) {
			return false;
		}

		if ('WP_Post' !== get_class($post)) {
			return false;
		}

		$diff = current_time('timestamp') - get_the_date('U', $post);

		if ($diff < HOUR_IN_SECONDS - 30) {
			$mins = max(1, round($diff / MINUTE_IN_SECONDS));

			return $mins . 'm';
		} elseif ($diff < DAY_IN_SECONDS) {
			$hours = max(1, round($diff / HOUR_IN_SECONDS));

			return $hours . 'h';
		}

		return get_the_date('F j, Y', $post);
	}

	/**
	 * Return terms for post
	 *
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function get_post_terms($post_id = 0)
	{

		if (empty($post_id)) {
			$post_id = get_the_ID();
		}

		$term_array = [
			'vertical' => [],
			'category' => [],
			'post_tag' => [],
		];

		if (empty($post_id)) {
			return $term_array;
		}

		if (!is_int($post_id)) {
			return $term_array;
		}

		//verticals
		if (taxonomy_exists('vertical')) {
			$tax_array['vertical'] = get_the_terms($post_id, 'vertical');
		}
		//categories
		$term_array['category'] = get_the_terms($post_id, 'category');
		//tags
		$term_array['post_tag'] = get_the_terms($post_id, 'post_tag');

		$term_array = apply_filters('pmc_core_post_terms', $term_array, $post_id);

		return $term_array;
	}

	/**
	 * Output an <img> element with `srcset`/`sizes` attributes.
	 *
	 * @param array $args {
	 *                    Arguments used to generate the element. The only required argument is
	 *                    $sizes.
	 *
	 * @type int    $attachment_id
	 *         Optional. If absent, the current post's thumb will be used.
	 *
	 * @type string $sizes_attr
	 *         Optional. A `sizes` attribute, containing a comma-separated
	 *         list of intended display sizes. This is used to determine the
	 *         image source most appropriate for the size of the image in the
	 *         layout, rather than the viewport size. e.g.:
	 *
	 *           `95.39vw, (min-width: 668px) 640px`
	 *
	 *           “This image occupies ~95.39% of the layout. When the viewport
	 *           is above 668px, this image has a static width of 640px.”
	 *
	 *         If absent, the WordPress core default `sizes` value will be used:
	 *
	 *           `(max-width: {{image-width}}px) 100vw, {{image-width}}px`
	 *
	 *           “Assume this image occupies 100% of the viewport, up until
	 *           the viewport reaches the natural size of the image.”
	 *
	 * @type array  $sources
	 *         Optional. An array of named sources. If absent, all available
	 *         (WP-generated) sources associated with the image will be used.
	 *
	 * @type string $default_size
	 *         Optional. The size for the image tag. If absent, the largest
	 *         available (WP-generated) source will be used.
	 *
	 * @type array  $image_attr
	 *         Optional. Array of image attributes passed to
	 *         `wp_get_attachment_image()` for the <img> tag.
	 * }
	 *
	 * @return string
	 *
	 * @example
	 *  PMC\Core\Inc\Theme::get_instance->render_resp_img( array(
	 *         'attachment_id' => get_post_thumbnail_id(),
	 *         'image_attr' => [ 'class' => esc_attr( $class_base ) . 'test-class' ],
	 *         'sources' => null,
	 *         'sizes_attr' => "95.39vw, (min-width: 668px) 640px",
	 *         'default_size' => null
	 *     ) );
	 * @version 2017-04-12 Mat Marquis
	 */
	public function render_resp_img($args = [])
	{

		$args = wp_parse_args(
			$args,
			[
				'attachment_id' => null,
				'sizes_attr'    => null,
				'sources'       => [],
				'default_size'  => null,
				'image_attr'    => ['srcset' => ''],
				'echo'          => false,
				'return'        => false,
			]
		);

		if (empty($args['attachment_id'])) {
			$args['attachment_id'] = get_post_thumbnail_id();
		}

		if (empty($args['attachment_id'])) {
			return;
		}

		if (!empty($args['sources'])) {
			$srcs = [];
			foreach ($args['sources'] as $value) {
				$srcs[$value] = wp_get_attachment_image_src($args['attachment_id'], $value);
			}
			foreach ($srcs as $i => $src) {
				if ($src[3]) {
					$args['image_attr']['srcset'] .= esc_url($src[0]) . ' ' . $src[1] . 'w, ';
				}
			}
			$args['image_attr']['srcset'] = substr($args['image_attr']['srcset'], 0, -2);
		}

		$args['image_attr']['sizes'] = $args['sizes_attr'];

		$image_escaped = wp_get_attachment_image(
			$args['attachment_id'],
			$args['default_size'],
			false,
			$args['image_attr']
		);

		if (true === $args['return']) {
			return $args;
		} elseif (true === $args['echo']) {
			echo $image_escaped; //@codingStandardsIgnoreLine: Ignored because image is already escaped
		} else {
			return $image_escaped;
		}
	}

	/**
	 * Get Random Recent Post
	 *
	 * Get a single random recent post (respects the current vertical. Derived from
	 * `pmc_core_get_the_latest_posts_query`.
	 *
	 * @param string $taxonomy
	 *
	 * @return bool|mixed
	 * @uses    pmc_get_the_primary_term
	 *
	 * @version 2021-04-06 Add param to exclude current post from the random posts.
	 * @version 2020-09-25 AS - Add param to pass taxonomy, so that tax other than vertical can be used
	 *
	 * @version 2018-03-21 brandoncamenisch - feature/WI-498:
	 * - Removing query assignment on if statement while checking cache_key trans
	 *
	 * @since   2017.1.0
	 *
	 * @see     pmc_core_get_the_latest_posts_query
	 * @uses    pmc_core_article_post_types
	 */
	public function get_random_recent_post(string $taxonomy = 'vertical', $exclude_current_post = true)
	{

		if (is_singular()) {
			$post_id = get_the_ID();
		}

		if (!empty($post_id)) {
			$term_obj = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy($post_id, $taxonomy);
		} elseif (is_tax($taxonomy)) {
			$term_obj = get_queried_object();
		} elseif ('category' === $taxonomy && is_category()) {
			$term_obj = get_queried_object();
		} elseif ('post_tag' === $taxonomy && is_tag()) {
			$term_obj = get_queried_object();
		}

		if (empty($term_obj)) {
			$cache_key = sprintf('%s-latest-posts-home', $taxonomy);
		} else {
			$cache_key = sprintf('%s-latest-posts-%d', $taxonomy, $term_obj->term_id);
		}

		$query = get_transient($cache_key);

		if (false === $query) {
			$args = [
				'post_type'      => ['post', 'pmc-gallery'],
				'posts_per_page' => 10,
			];

			if (!empty($term_obj)) {

				$args['tax_query'] = [ //@codingStandardsIgnoreLine: Usage of tax_query is required as we need post with $taxonomy.
					[
						'taxonomy'         => $taxonomy,
						'terms'            => [$term_obj->term_id],
						'include_children' => false,
					],
				];
			}

			$args = apply_filters('pmc_core_random_recent_post_args', $args);

			$query = new \WP_Query($args);

			set_transient($cache_key, $query, 15 * MINUTE_IN_SECONDS);
		}

		// Remove current post ID from the results Ref: BR-1141.
		if (is_singular() && true === $exclude_current_post) {
			foreach ($query->posts as $index => $the_post) {
				if ($the_post->ID === $post_id) {
					unset($query->posts[$index]);
				}
			}
		}

		// Return a random post.
		if (!empty($query->posts) && is_array($query->posts)) {
			return isset($query->posts[mt_rand(0, count($query->posts) - 1)]) ? $query->posts[mt_rand(0, count($query->posts) - 1)] : false;
		}

		return false;
	}

	/**
	 * Fetch a given gallery's teaser image
	 *
	 * Teaser image = the featured image if there is one,
	 * otherwise, the first gallery image
	 *
	 * @param int    $gallery_id                  The gallery attachment post ID
	 * @param int    $first_gallery_attachment_id The ID of the first attachment in the gallery
	 * @param string $image_size                  The image size for which to return it's URL
	 *
	 * @return bool
	 */
	function get_gallery_teaser_image_id(
		$gallery_id = 0,
		$first_gallery_attachment_id = 0,
		$image_size = 'landscape-large'
	) {

		if (empty($gallery_id) || empty($first_gallery_attachment_id) || empty($image_size)) {
			return false;
		}

		$teaser_attachment_id = false;

		// Does this gallery have a featured image?
		$featured_attachment_id = get_post_thumbnail_id($gallery_id);

		// If so, use the featured image as the teaser..
		if (!empty($featured_attachment_id)) {
			$teaser_attachment_id = $featured_attachment_id;
		} else {
			// ..if not, use the first gallery item
			if (!empty($first_gallery_attachment_id)) {
				$teaser_attachment_id = $first_gallery_attachment_id;
			}
		}

		if (!empty($teaser_attachment_id)) {
			return $teaser_attachment_id;
		}

		return false;
	}

	/**
	 * async_scripts | www/wp-content/themes/vip/pmc-core-v2/inc/classes/class-theme.php
	 *
	 * @since 2017-12-07 - Async any scripts we want that we don't have control
	 * over in the code such as VIP plugins. Avoid adding pmc-scripts here as there
	 * are other methods of asyncing our assets. This is a last resort type option.
	 *
	 * @author brandoncamenisch
	 * @version 2017-12-07 - feature/PMCVIP-2907:
	 * - Adding method that asyncs the script
	 *
	 * @param string $tag,
	 * @param string $handle
	 * @param string $src
	 * @return string $tag
	 **/
	public function async_scripts($tag, $handle, $src)
	{
		$to_async = ['google-ajax-comment-loading'];
		if (in_array($handle, (array) $to_async, true)) {
			return str_replace('<script', '<script async', $tag);
		} else {
			return $tag;
		}
	}

	/**
	 * Get newsletter page's URL.
	 *
	 * @return bool|string Returns string if link added in settings else false.
	 */
	public function get_newsletter_url()
	{

		$url = cheezcap_get_option('pmc_core_signup_url', false);

		return (empty($url)) ? false : $url;
	}

	/**
	 * Get all brands which will display at the bottom of the every page.
	 *
	 * @return array Brands.
	 */
	public function footer_brands()
	{

		return [
			'Artnews'                => 'https://artnews.com/',
			'BGR'                    => 'https://bgr.com/',
			'Billboard'              => 'https://billboard.com/',
			'Deadline'               => 'https://deadline.com/',
			'Fairchild Media'        => 'https://fairchildlive.com/',
			'Footwear News'          => 'https://footwearnews.com/',
			'Gold Derby'             => 'https://www.goldderby.com/',
			'IndieWire'              => 'https://www.indiewire.com/',
			'Robb Report'            => 'https://robbreport.com/',
			'Rolling Stone'          => 'https://www.rollingstone.com/',
			'SheKnows'               => 'https://www.sheknows.com/',
			'She Media'              => 'https://www.shemedia.com/',
			'Soaps'                  => 'https://soaps.sheknows.com/',
			'Sourcing Journal'       => 'https://sourcingjournal.com/',
			'Sportico'               => 'https://www.sportico.com/',
			'Spy'                    => 'https://spy.com/',
			'StyleCaster'            => 'https://stylecaster.com/',
			'The Hollywood Reporter' => 'https://hollywoodreporter.com/',
			'TVLine'                 => 'https://tvline.com/',
			'Variety'                => 'https://variety.com/',
			'Vibe'                   => 'https://vibe.com/',
			'WWD'                    => 'https://wwd.com/',
		];
	}

	/**
	 * Function to override parent theme add_theme_support( 'title-tag' ) support.
	 *
	 * Used hook to set title from seo meta box.
	 *
	 * @param array $title Post title.
	 *
	 * @return array $title post seo title
	 * @since 2018-12-18
	 *
	 */
	public function get_single_page_post_title($title)
	{

		if (is_single()) {

			$post_seo_title = get_post_meta(get_the_ID(), 'mt_seo_title', true);

			if (!empty($post_seo_title)) {

				$title['title'] = $post_seo_title;
			}
		}

		return $title;
	}

	/**
	 * Get Tip page url.
	 *
	 * @return bool|string Returns string if link added in settings else false.
	 */
	public function get_tip_page_url()
	{

		$url = cheezcap_get_option('pmc_core_tip_us_url', false);

		return (empty($url)) ? false : $url;
	}

	/**
	 * Get socials profile links which is added from backend `Global Curation >> Social Profiles`.
	 *
	 * @throws \Exception If template not found.
	 *
	 * @return array Social links
	 */
	public function get_social_profiles()
	{

		$social_profiles = [];

		$settings = get_option('global_curation', []);

		if (
			!empty($settings) && isset($settings['tab_social_profiles'])
			&& isset($settings['tab_social_profiles']['social_profiles'])
		) {

			$social_profiles = $settings['tab_social_profiles']['social_profiles'];
		}

		return $social_profiles;
	}

	/**
	 * Get option is elastic search is enabled for the theme or not.
	 *
	 * @return bool
	 */
	public function is_es_enabled()
	{

		$es = get_option('cap_pmc_core_es_enabled', false); // phpcs:ignore

		return (empty($es)) ? false : true;
	}

	/**
	 * Method to return the version of fieldmanager plugin that should be used.
	 * This is hooked on 'pmc_fieldmanager_version' filter.
	 *
	 * @return string
	 */
	public function get_fieldmanager_version(): string
	{
		return '1.1';
	}

	public function author_page_exclude_custom_author_posts($query)
	{
		if (!is_admin() && $query->is_main_query()) {
			if (is_author()) {
				$meta_query = (array)$query->get('meta_query');
				$meta_query[] = [
					'relation' => 'OR',
					[
						'key' => 'author',
						'compare' => 'NOT EXISTS'
					],
					[
						'key' => 'author',
						'value' => '',
						'compare' => '='
					],
				];
				$query->set('meta_query', $meta_query);
			}
		}
	}
}

//EOF
