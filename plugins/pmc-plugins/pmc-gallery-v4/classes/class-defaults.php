<?php

namespace PMC\Gallery;

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
 */

use \PMC\Global_Functions\Traits\Singleton;

class Defaults
{

	use Singleton;

	/**
	 * Gallery post type name.
	 */
	const NAME = 'pmc-gallery';

	/**
	 * @deprecated
	 */
	const name = self::NAME; // @codingStandardsIgnoreLine - backward compatibility.

	/**
	 * Previous link html.
	 */
	const PREVIOUS_LINK_HTML = '&larr;&nbsp;Previous'; // @codingStandardsIgnoreLine - gettexted okay.

	/**
	 * @deprecated
	 */
	const previous_link_html = self::PREVIOUS_LINK_HTML; // @codingStandardsIgnoreLine - backward compatibility.

	/**
	 * Next link html
	 */
	const NEXT_LINK_HTML = 'Next&nbsp;&rarr;'; // @codingStandardsIgnoreLine - gettexted okay.

	/**
	 * @deprecated
	 */
	const next_link_html = self::NEXT_LINK_HTML; // @codingStandardsIgnoreLine - backward compatibility.

	/**
	 * PMC gallery plugin url.
	 *
	 * @var null
	 */
	public static $url = null;

	/**
	 * Initializes the class.
	 */
	protected function __construct()
	{
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void
	{
		add_action('init', array($this, 'action_init'));
		add_action('load-post-new.php', array($this, 'action_load_new_post'));

		// Ensure this runs after all other saves, lest stale data be cached.
		add_action('wp_insert_post', [$this, 'rebuild_gallery_cache_on_save'], PHP_INT_MAX, 3);
		add_action('added_post_meta', [$this, 'rebuild_gallery_cache_on_meta_update'], PHP_INT_MAX, 3);
		add_action('deleted_post_meta', [$this, 'rebuild_gallery_cache_on_meta_update'], PHP_INT_MAX, 3);
		add_action('updated_post_meta', [$this, 'rebuild_gallery_cache_on_meta_update'], PHP_INT_MAX, 3);

		self::$url = PMC_GALLERY_PLUGIN_URL;

		add_filter('redirect_canonical', array($this, 'prevent_canonical_redirect'), 100);
		add_filter('pmc_sitemaps_post_type_whitelist', [$this, 'whitelist_post_type_for_sitemaps']);

		add_action('fm_post_' . self::NAME, array($this, 'register_custom_fields'));

		add_action('template_redirect', [$this, 'maybe_redirect']);
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
			self::NAME,
			array(
				'labels'        => array(
					'name'               => esc_html__('Galleries', 'pmc-gallery-v4'),
					'singular_name'      => esc_html__('Gallery', 'pmc-gallery-v4'),
					'add_new'            => esc_html__('Add New Gallery', 'pmc-gallery-v4'),
					'add_new_item'       => esc_html__('Add New Gallery', 'pmc-gallery-v4'),
					'edit'               => esc_html__('Edit Gallery', 'pmc-gallery-v4'),
					'edit_item'          => esc_html__('Edit Gallery', 'pmc-gallery-v4'),
					'new_item'           => esc_html__('New Gallery', 'pmc-gallery-v4'),
					'view'               => esc_html__('View Gallery', 'pmc-gallery-v4'),
					'view_item'          => esc_html__('View Gallery', 'pmc-gallery-v4'),
					'search_items'       => esc_html__('Search Galleries', 'pmc-gallery-v4'),
					'not_found'          => esc_html__('No Gallery found', 'pmc-gallery-v4'),
					'not_found_in_trash' => esc_html__('No Gallery found in Trash', 'pmc-gallery-v4'),
				),
				'public'        => true,
				'menu_position' => 5,
				'supports'      => array(
					'title',
					'editor',
					'author',
					'excerpt',
					'comments',
					'thumbnail',
					'custom-fields',
					'trackbacks',
					'revisions',
				),
				'taxonomies'    => array('category', 'post_tag'),
				'menu_icon'     => self::$url . 'assets/build/images/icon.png',
				'has_archive'   => true,
				'rewrite'       => array(
					'slug' => apply_filters('pmc_gallery_standalone_slug', 'gallery'),
				),
				'show_in_rest'  => true,
			)
		);

		// Restores the 'Author' meta box to galleries.
		// The vertical template displays the byline.
		// @codeCoverageIgnoreStart
		if (
			class_exists('\PMC\Core\Inc\Fieldmanager\Fields')
			&& method_exists(\PMC\Core\Inc\Fieldmanager\Fields::get_instance(), 'remove_meta_boxes')
		) {
			remove_action('add_meta_boxes', array(\PMC\Core\Inc\Fieldmanager\Fields::get_instance(), 'remove_meta_boxes'), 20);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Mark new pmc-gallery posts as excluded from landing pages
	 *
	 * @internal called during load-post-new.php so only new galleries are affected
	 *
	 * @param  int     $post_id Post ID.
	 * @param  \WP_Post $post    Post object.
	 * @param  bool    $update  Whether this is an existing post being updated or not.
	 *
	 * @return null
	 */
	public function action_save_post_pmc_gallery($post_id, $post, $update)
	{

		// Only add the Exclusion term to new galleries if
		$term_exists_func = (function_exists('wpcom_vip_term_exists')) ? 'wpcom_vip_term_exists' : 'term_exists';
		$term_exists = $term_exists_func('exclude-from-section-fronts', '_post-options');

		// Filter to change behavior in theme. Defaults to true.
		$show_on_landing_page = apply_filters('pmc_gallery_v4_show_on_landing_pages', true);

		// If `should show on landing` and the term already exists.
		if (true === $show_on_landing_page && !empty($term_exists)) {
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
	 *
	 * @return void
	 */
	public function action_load_new_post()
	{
		global $typenow;

		if (self::NAME === $typenow) {
			add_action(
				'save_post_' . self::NAME,
				array(
					$this,
					'action_save_post_pmc_gallery',
				),
				10,
				3
			);
		}
	}

	/**
	 * Registers scripts and styles to be used with the plugin.
	 *
	 * IMPORTANT: This function only *registers* the scripts and styles.
	 * Use additional hook on 'wp_enqueue_scripts' or 'admin_enqueue_scripts' as needed.
	 */
	public function register_scripts_and_styles()
	{

		// Register frontend scripts.
		wp_register_script(self::NAME, self::$url . 'assets/build/js/gallery.js', [], PMC_GALLERY_VERSION);
		wp_register_script(self::NAME . '-runway', self::$url . 'assets/build/js/gallery-runway.js', ['jquery'], PMC_GALLERY_VERSION);
		wp_register_script(self::NAME . '-vertical', self::$url . 'assets/build/js/gallery-vertical.js', [], PMC_GALLERY_VERSION);
		wp_register_script(self::NAME . '-inline', self::$url . 'assets/build/js/gallery-inline.js', ['jquery'], PMC_GALLERY_VERSION, true);

		// Register frontend styles.
		wp_register_style(self::NAME, self::$url . 'assets/build/css/gallery.css', [], PMC_GALLERY_VERSION);
		wp_register_style(self::NAME . '-runway', self::$url . 'assets/build/css/gallery-runway.css', [], PMC_GALLERY_VERSION);
		wp_register_style(self::NAME . '-vertical', self::$url . 'assets/build/css/gallery-vertical.css', [], PMC_GALLERY_VERSION);
		wp_register_style(self::NAME . '-inline', self::$url . 'assets/build/css/gallery-inline.css', [], PMC_GALLERY_VERSION);

		// Admin scripts and styles.
		// @todo Why is this being enqueued on all admin screens in v3?
		if (is_admin()) {
			wp_enqueue_script('media-grid'); // Not sure why this was done separately.
		}

		wp_register_script(
			self::NAME . '-admin-post',
			self::$url . 'assets/build/js/admin-gallery.js',
			array(
				'jquery',
				'media-views',
				'media-grid',
			),
			false,
			true
		);

		wp_localize_script(
			self::NAME . '-admin-post',
			'pmcGalleryV4AdminGallery',
			[
				'pmcBuyNowEnabled' => class_exists('\PMC\Buy_Now\Admin_UI', false),
			]
		);

		wp_register_style(self::NAME . '-admin-post', self::$url . 'assets/build/css/admin-post.css');
	}

	/**
	 * Defines a rewrite rule for the single image page from gallery
	 */
	protected function _add_rewrite_rules()
	{
		if (apply_filters('pmc_gallery_v4_has_custom_rewrite_rule', false)) {
			return;
		}

		$slug = sanitize_title_with_dashes(apply_filters('pmc_gallery_standalone_slug', 'gallery'));
		add_rewrite_rule(preg_quote($slug) . '/(.+)/(.+)/?$', 'index.php?pmc-gallery=$matches[1]&pmc-gallery-image=$matches[2]', 'top');
		add_rewrite_tag('%pmc-gallery-image%', '([^/]+)');
	}

	/**
	 * Rebuild cached gallery data on update, to limit when front-end requests
	 * trigger rebuilds.
	 *
	 * @param int      $id     Post ID.
	 * @param \WP_Post $post   Post object.
	 * @param bool     $update Whether this is an update or a new post.
	 */
	public function rebuild_gallery_cache_on_save(int $id, \WP_Post $post, bool $update): void
	{
		if (!$update) {
			return;
		}

		if (
			(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			|| 'auto-draft' === $post->post_status
		) {
			return;
		}

		if (self::NAME !== $post->post_type) {
			return;
		}

		// Class normally not loaded when `is_admin()`, so we don't instantiate.
		// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
		require_once PMC_GALLERY_PLUGIN_DIR . '/classes/class-view.php';

		View::rebuild_gallery_by_id($id);
	}

	/**
	 * Rebuild cached gallery data when underlying metadata is updated.
	 *
	 * @param int|string[] $meta_id   Meta ID (or array of IDs when meta is deleted).
	 * @param int          $object_id Object ID.
	 * @param string       $meta_key  Meta key.
	 */
	public function rebuild_gallery_cache_on_meta_update($meta_id, int $object_id, string $meta_key): void
	{
		if (self::NAME !== $meta_key) {
			return;
		}

		if (self::NAME !== get_post_type($object_id)) {
			return;
		}

		$this->rebuild_gallery_cache_on_save(
			$object_id,
			get_post($object_id),
			true
		);
	}

	/**
	 * Whitelist post type for site-map.
	 *
	 * @param  array $post_types List of post type for site map.
	 *
	 * @return array List of post type for site map.
	 */
	public function whitelist_post_type_for_sitemaps($post_types)
	{

		$post_types = (!empty($post_types) && is_array($post_types)) ? $post_types : [];

		if (!in_array(self::NAME, (array) $post_types, true)) {
			$post_types[] = self::NAME;
		}

		return $post_types;
	}

	/**
	 * If the current post is runway gallery.
	 *
	 * @return bool
	 */
	public function is_runway_gallery()
	{
		global $post;

		if (!($post instanceof \WP_Post) || empty($post)) {
			return false;
		}

		$terms = get_the_terms($post->ID, 'gallery-type');

		if (!empty($terms) && !is_wp_error($terms) && !empty($terms[0]->slug)) {

			if (in_array($terms[0]->slug, array('collection', 'details'), true)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Registers a Fieldmanager field for controlling the gallery display type.
	 *
	 * @codeCoverageIgnore
	 */
	public function register_custom_fields()
	{
		global $pagenow;

		if (!class_exists('\Fieldmanager_Select')) {
			return;
		}

		$gallery_display                = apply_filters('pmc_gallery_v4_gallery_display', false);
		$runway_gallery_options_enabled = apply_filters('pmc_gallery_v4_enable_runway_gallery_options', false);
		$default_gallery_display_value  = ('post-new.php' === $pagenow) ? 'vertical' : 'horizontal';
		$default_gallery_display_value  = apply_filters('pmc_gallery_v4_default_gallery', $default_gallery_display_value);

		// The default would show as runway for existing runway gallery.
		if ($runway_gallery_options_enabled && $this->is_runway_gallery()) {
			$default_gallery_display_value = 'runway';
		}

		$gallery_display_options = array(
			'options'       => array(
				'vertical'   => esc_html__('Vertical', 'pmc-gallery-v4'),
				'horizontal' => esc_html__('Horizontal', 'pmc-gallery-v4'),
			),
			'attributes'    => array(
				'style'            => 'width:150px',
				'data-placeholder' => esc_html__('Select', 'pmc-gallery-v4'),
			),
			'default_value' => $default_gallery_display_value,
		);

		if ($runway_gallery_options_enabled) {
			$gallery_display_options['options']['runway'] = esc_html__('Runway', 'pmc-gallery-v4');
		}

		// Do not show gallery options as there is only one gallery display is desired with the filter.
		if (false === $gallery_display) {

			$fm_gallery_options = new \Fieldmanager_Group(
				array(
					'name'           => 'pmc_gallery_options',
					'serialize_data' => false,
					'children'       => array(
						'display' => new \Fieldmanager_Select(
							__('Gallery Display', 'pmc-gallery-v4'),
							$gallery_display_options
						),
					),
				)
			);

			$fm_gallery_options->add_meta_box(esc_html__('Gallery Options', 'pmc-gallery-v4'), array(self::NAME));
		}

		/**
		 * Field has been taken from WWD.
		 * Intro card is the introduction card that shows on the first slide.
		 */
		$fm_intro_card = new \Fieldmanager_Group(
			array(
				'name'           => 'gallery_intro_card_details',
				'serialize_data' => false,
				'children'       => array(
					'title'       => new \Fieldmanager_TextField(__('Title', 'pmc-gallery-v4')),
					'description' => new \Fieldmanager_RichTextArea(
						__('Description', 'pmc-gallery-v4'),
						array(
							'editor_settings' => [
								'teeny'         => true,
								'media_buttons' => false,
								'quicktags'     => false,
							],
						)
					),
				),
				'description'    => __('Shows on the first slide when the gallery starts', 'pmc-gallery-v4'),
			)
		);

		$fm_intro_card->add_meta_box(esc_html__('Intro Card', 'pmc-gallery-v4'), array(self::NAME));
	}

	/**
	 * Prevent canonical redirect for pmc gallery post type.
	 *
	 * @ticket PMCP-1140
	 * @since 2019-01-17 - Sayed Taqui
	 *
	 * Some themes redirect single posts to post permalink if permalink path is not equal to url path.
	 * PMC Gallery v3 used hashbang urls and therefore bailing out for canonical redirect for pmc-gallery post type was not required
	 * however now that we support the single slide slug in pmc-gallery-v4, without this filter it would redirect the single slide slug
	 * back to the gallery permalink if the theme/parent-theme using this pmc-gallery-v4 plugin does the redirect.
	 *
	 * @return string
	 */
	public function prevent_canonical_redirect($redirect_url)
	{

		return (!is_singular(self::NAME)) ? $redirect_url : '';
	}

	/**
	 * If any gallery post type is visited with `non trail slashed` version,
	 * Let's redirect to `trail slashed` version and with 301 status code.
	 *
	 * Note: By default WordPress should redirect to canonical url (with trail slash).
	 * But it was forcefully disabled by this ticket: https://jira.pmcdev.io/browse/PMCP-1140
	 *
	 * @see https://jira.pmcdev.io/browse/PASE-781
	 *
	 * @return void
	 */
	public function maybe_redirect(): void
	{
		$current_url = \PMC::filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);

		if (empty($current_url) || '/' === substr($current_url, -1)) {
			return;
		}

		$post = get_queried_object();

		if (!$post instanceof \WP_Post || self::NAME !== $post->post_type) {
			return;
		}

		$post_url = get_permalink($post);

		if (empty($post_url) || is_wp_error($post_url)) {
			return;
		}

		wp_safe_redirect(trailingslashit($post_url), 301);

		// Don't know how to test exit statement.
		exit; // @codeCoverageIgnore
	}
}

// EOF
