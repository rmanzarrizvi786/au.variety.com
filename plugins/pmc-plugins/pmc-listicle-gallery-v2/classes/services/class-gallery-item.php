<?php

namespace PMC\Listicle_Gallery_V2\Services;

use PMC;
use PMC\Global_Functions\Traits\Singleton;
use Fieldmanager_Group;
use Fieldmanager_Autocomplete;
use Fieldmanager_Datasource_Post;

/**
 * This class handles the Listicle Gallery Item post type.
 *
 * A Listical Gallery Item is an image carousel with accompanying rich text content.
 * One or more of these can be linked to a Listical Gallery.
 *
 * To override the css, register the overriding css with the handle 'listicle-gallery-item-css-overrides'.
 *
 */
class Gallery_Item
{

	use Singleton;

	const POST_TYPE = 'pmc-lst-gallery-item';
	const GALLERY_TAG = 'gallery';

	const REWRITE_REGEX = '^(?:[^/]*/)+' . Gallery::GALLERY_PATH . '/([^/]*)/([^/]*)/?';
	const REWRITE_QUERY = 'index.php?' . self::POST_TYPE . '=$matches[2]&' . self::GALLERY_TAG . '=$matches[1]';

	/**
	 * Gallery item default settings
	 *
	 * @var array
	 */
	protected $_settings = [
		'slide_width' => 898,
		'slide_height' => 511,
		'thumb_width' => 173,
		'thumb_height' => 98,
		'thumbs_count' => 5,
	];

	/**
	 * Overrides the parent _init() method.
	 *
	 */
	protected function __construct()
	{

		add_action('init', [$this, 'action_init']);
		add_action('wp_enqueue_scripts', [$this, 'action_enqueue_scripts']);
		add_action('admin_enqueue_scripts', [$this, 'action_admin_enqueue_scripts'], 99);
		add_action('fm_post_' . self::POST_TYPE, [$this, 'action_fm_post_listicle_gallery_item']);
		add_action('update_postmeta', [$this, 'action_update_postmeta'], 10, 4);
		add_action('updated_post_meta', [$this, 'action_updated_post_meta'], 10, 4);
		add_action('added_post_meta', [$this, 'action_added_post_meta'], 10, 4);
		add_action('before_delete_post', [$this, 'action_before_delete_post'], 10, 1);
		add_action('wp_head', [$this, 'action_wp_head']);

		add_filter('post_type_link', [$this, 'filter_post_type_link'], 0, 2);
		add_filter('pmc_ga_event_tracking', [$this, 'filter_pmc_ga_event_tracking']);
		add_filter('coauthors_supported_post_types', [$this, 'filter_coauthors_supported_post_types']);
		add_filter('query_vars', [$this, 'filter_query_vars']);
		add_filter('pmc_seo_tweaks_robots_override', [$this, 'filter_pmc_seo_tweaks_robots_override']);
		add_filter('pmc_canonical_url', [$this, 'filter_pmc_canonical_url']);
	}

	public function action_init()
	{

		$this->_register_gallery_item_post_type();
		$this->add_custom_rewrite_tag();
		$this->add_custom_rewrite_rule();
		$this->_settings = $this->get_gallery_item_settings();
	}

	/**
	 * Returns the gallery item settings.
	 *
	 * @return array
	 */
	public function get_gallery_item_settings()
	{

		return apply_filters('pmc_listicle_gallery_v2_gallery_item_settings', $this->_settings);
	}

	/**
	 * Adds the gallery item scripts
	 *
	 * @return bool
	 */
	public function action_enqueue_scripts()
	{

		if (self::POST_TYPE !== get_post_type()) {
			return false;
		}

		// load slick assets

		pmc_js_libraries_enqueue_script('pmc-slick', '', ['jquery'], '', true, true, false);

		// load gallery item css

		$url = apply_filters('pmc_listicle_gallery_v2_gallery_item_css', LISTICLE_GALLERY_V2_ASSETS_URL . '/build/css/gallery-item.min.css');
		wp_enqueue_style('listicle-gallery-item-css', $url);

		// load gallery item js

		if (defined('WPCOM_IS_VIP_ENV') && WPCOM_IS_VIP_ENV) {
			$url = LISTICLE_GALLERY_V2_ASSETS_URL . '/build/js/gallery-item.min.js';
		} else {
			$url = LISTICLE_GALLERY_V2_ASSETS_URL . '/src/js/gallery-item.js';
		}

		wp_enqueue_script('listicle-gallery-item-js', $url, ['jquery'], PMC_CORE_VERSION, true);

		// pass gallery item settings to the front-end

		wp_localize_script('listicle-gallery-item-js', 'settings', $this->_settings);

		return true;
	}

	/**
	 * Adds the gallery item admin scripts
	 *
	 * @return bool
	 */
	public function action_admin_enqueue_scripts()
	{

		if (self::POST_TYPE !== get_post_type()) {
			return false;
		}

		wp_enqueue_style('listicle-gallery-item-admin-css', LISTICLE_GALLERY_V2_ASSETS_URL . '/build/css/gallery-item-admin.min.css');

		if (defined('WPCOM_IS_VIP_ENV') && WPCOM_IS_VIP_ENV) {
			$url = LISTICLE_GALLERY_V2_ASSETS_URL . '/build/js/gallery-item-admin.min.js';
		} else {
			$url = LISTICLE_GALLERY_V2_ASSETS_URL . '/src/js/gallery-item-admin.js';
		}

		wp_enqueue_script('listicle-gallery-item-admin-js', $url, ['jquery'], PMC_CORE_VERSION, true);
	}

	/**
	 * Registers the custom Listicle Gallery Item post type
	 *
	 */
	protected function _register_gallery_item_post_type()
	{

		register_post_type(self::POST_TYPE, [
			'labels'                => [
				'name'                => __('Galleries', 'pmc-plugins'),
				'singular_name'       => __('Gallery', 'pmc-plugins'),
				'add_new'             => __('Add New', 'pmc-plugins'),
				'add_new_item'        => __('Add New Gallery', 'pmc-plugins'),
				'edit'                => __('Edit', 'pmc-plugins'),
				'edit_item'           => __('Edit Gallery', 'pmc-plugins'),
				'new_item'            => __('New Gallery', 'pmc-plugins'),
				'view'                => __('View', 'pmc-plugins'),
				'view_item'           => __('View Gallery', 'pmc-plugins'),
				'search_items'        => __('Search Galleries', 'pmc-plugins'),
				'not_found'           => __('No Galleries Found', 'pmc-plugins'),
				'not_found_in_trash'  => __('No Galleries Found in Trash', 'pmc-plugins'),
			],
			'public'                => true,
			'menu_position'         => 5.2,
			'supports'              => [
				'title',
				'editor',
				'author',
				'excerpt',
				'comments',
				'revisions',
			],
			'has_archive'           => false,
			'query_var'             => true,
			'publicly_queryable'    => true,
			'show_in_menu'          => 'edit.php?post_type=' . Gallery::POST_TYPE,
		]);
	}

	/**
	 * Defines additional fields for Listical Gallery Item.
	 *
	 * Fields:
	 * parent_gallery - the galleries to which this gallery item will be attached.
	 *
	 */
	public function action_fm_post_listicle_gallery_item()
	{

		$fm_parent_galleries = new Fieldmanager_Group([
			'name'                => 'parent_galleries',
			'limit'               => 0,
			'minimum_count'       => 0,
			'extra_elements'      => 0,
			'add_more_label'      => __('Add another parent'),
			'children'            => [
				'id'                => new Fieldmanager_Autocomplete([
					'datasource'      => new Fieldmanager_Datasource_Post([
						'query_args'    => [
							'post_type'   => Gallery::POST_TYPE,
							'post_status' => 'any',
						],
					]),
				]),
			],
		]);

		$fm_parent_gallery = new Fieldmanager_Group([
			'name'                => 'parent_gallery',
			'limit'               => 1,
			'children'            => [
				'id'                => new Fieldmanager_Autocomplete([
					'datasource'      => new Fieldmanager_Datasource_Post([
						'query_args'    => [
							'post_type'   => Gallery::POST_TYPE,
							'post_status' => 'any',
						],
					]),
				]),
				'parent_galleries'  => $fm_parent_galleries,
			],
		]);

		$fm_parent_gallery->add_meta_box(__('Parent Listicle Gallery', 'pmc-plugins'), self::POST_TYPE);
	}

	/**
	 * Defines a rewite tag which we can query to get the gallery item's corresponding gallery.
	 *
	 */
	public function add_custom_rewrite_tag()
	{

		add_rewrite_tag('%' . self::GALLERY_TAG . '%', '([^&]+)');
	}

	/**
	 * Defines a rewite rule for the gallery slug
	 *
	 */
	public function add_custom_rewrite_rule()
	{

		add_rewrite_rule(self::REWRITE_REGEX, self::REWRITE_QUERY, 'top');
	}

	/**
	 * Builds the correct permalink for the gallery item.
	 *
	 * @param string $url
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	public function filter_post_type_link($url, $post)
	{

		if (self::POST_TYPE !== get_post_type($post)) {
			return $url;
		}

		// get the gallery to which this gallery item belongs
		$parent_gallery_id = $this->_get_parent_gallery_id();

		if (!empty($parent_gallery_id)) {
			$parent_gallery_id = intval($parent_gallery_id);
			$gallery = get_post($parent_gallery_id);
			if (!empty($gallery)) {
				$url = $this->_get_gallery_item_url($gallery, $post);
			}
		}

		return $url;
	}

	/**
	 * Returns data for the current gallery item.
	 *
	 * @param \WP_Post $post
	 * @return array
	 */
	public function get_data($post = null)
	{

		if (empty($post)) {
			$post = get_post();
		}

		// get the gallery to which this gallery item belongs

		$gallery           = null;
		$parent_gallery_id = $this->_get_parent_gallery_id();

		if (!empty($parent_gallery_id)) {
			$parent_gallery_id = intval($parent_gallery_id);
			$gallery = get_post($parent_gallery_id);
		}

		// use the gallery to get info about the sibling gallery items

		$siblings = $this->_get_siblings_info($gallery, $post->ID);

		// get the author links

		$author_links = '';

		if (function_exists('coauthors_posts_links')) {

			$authors = coauthors_posts_links(null, null, null, null, false);

			if (!empty(wp_strip_all_tags($authors, true))) {
				$author_links = sprintf(__('By %1$s on %2$s', 'pmc-plugins'), $authors, get_the_date());
			} else {
				$author_links = get_the_date();
			}
		}

		// put it all together

		$data = [
			'title'                       => get_the_title($post),
			'excerpt'                     => get_the_excerpt($post),
			'content'                     => apply_filters('the_content', $post->post_content),
			'date'                        => get_the_date('', $post),
			'authors'                     => $author_links,
			'slides'                      => $this->_get_images($post),
			'current_gallery_item_number' => $siblings['current_gallery_item_number'],
			'prev_gallery_item_url'       => $siblings['prev_gallery_item_url'],
			'next_gallery_item_url'       => $siblings['next_gallery_item_url'],
			'total_gallery_items'         => $siblings['total_gallery_items'],
			'template_path'               => LISTICLE_GALLERY_V2_ROOT_DIR . '/templates/gallery-item.php',
			'tags'                        => $this->_get_tags($post),
		];

		return $data;
	}

	/**
	 * Returns information about the gallery items before and after the current gallery item.
	 *
	 * @param \WP_Post $gallery This is the parent gallery field
	 * @param int $post_id      This is the ID of the current gallery item
	 *
	 * @return array
	 */
	protected function _get_siblings_info($gallery, $post_id)
	{

		$siblings = [];

		// set default values

		$siblings['current_gallery_item_number'] = 1;
		$siblings['prev_gallery_item_url'] = '';
		$siblings['next_gallery_item_url'] = '';
		$siblings['total_gallery_items'] = 1;

		if (empty($gallery)) {
			return $siblings;
		}

		// get an array of all the gallery item IDs attached to this gallery

		$gallery_items = get_post_meta($gallery->ID, 'gallery_items', true);

		if (!empty($gallery_items)  && is_array($gallery_items)) {

			// get the key of the current gallery item within the above array

			$key = array_search($post_id, $gallery_items['ids'], true);

			if (false !== $key) {

				// get info about the neighboring gallery items

				$prev_gallery_item_url = '';

				if (!empty($gallery_items['ids'][$key - 1])) {
					$prev_gallery_item = get_post($gallery_items['ids'][$key - 1]);
					if (!empty($prev_gallery_item)) {
						$prev_gallery_item_url = $this->_get_gallery_item_url($gallery, $prev_gallery_item);
					}
				}

				$next_gallery_item_url = '';

				if (!empty($gallery_items['ids'][$key + 1])) {
					$next_gallery_item = get_post($gallery_items['ids'][$key + 1]);
					if (!empty($next_gallery_item)) {
						$next_gallery_item_url = $this->_get_gallery_item_url($gallery, $next_gallery_item);
					}
				}

				$siblings['current_gallery_item_number'] = $key + 1;
				$siblings['prev_gallery_item_url'] = $prev_gallery_item_url;
				$siblings['next_gallery_item_url'] = $next_gallery_item_url;
				$siblings['total_gallery_items'] = count($gallery_items['ids']);
			}
		}

		return $siblings;
	}

	/**
	 * Returns an array of image data for the given gallery item.
	 *
	 * @param $post
	 * @return array
	 */
	protected function _get_images($post)
	{

		$images = [];

		// get the Wordpress gallery attached to the post

		$wp_gallery = get_post_gallery($post, false);

		// extract the ids of the images in the Wordpress gallery

		$ids = explode(',', $wp_gallery['ids']);

		// get the data for each image

		foreach ($ids as $id) {

			$attachment = get_post($id);
			$thumbnail = wp_get_attachment_image_src($id, $this->_settings['thumb_width'], $this->_settings['thumb_height']);

			// set up the image data array, merged with the default values

			$images[] = array_merge(
				[
					'title'     => '',
					'alt'       => '',
					'credit'    => '',
					'caption'   => '',
				],
				[
					'url'       => $attachment->guid,
					'thumb_url' => $thumbnail[0],
					'title'     => $attachment->post_title,
					'alt'       => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
					'credit'    => get_post_meta($attachment->ID, '_image_credit', true),
					'caption'   => $this->_get_image_caption($attachment),
				]
			);
		}

		return $images;
	}

	/**
	 * Gets the image caption and wraps it in <p> if needed.
	 *
	 * @param $attachment
	 * @return string
	 */
	protected function _get_image_caption($attachment)
	{

		$caption = $attachment->post_excerpt;

		if (false === strpos($caption, '<p>')) {
			$caption = '<p>' . $caption . '</p>';
		}

		return $caption;
	}

	/**
	 * Returns the url for a gallery item. The url is built by concatenating the gallery url with
	 * the gallery item slug.
	 *
	 * @param \WP_Post $gallery
	 * @param \WP_Post $gallery_item_id
	 *
	 * @return string
	 */
	protected function _get_gallery_item_url($gallery, $gallery_item)
	{

		// get the gallery url

		$gallery_url = Gallery::get_instance()->_get_gallery_url($gallery);

		// build the gallery item url

		$gallery_item_url = sprintf('%1$s/%2$s/', $gallery_url, $gallery_item->post_name);

		if (static::is_listicle_preview() && !empty($gallery_item_url)) {
			$gallery_item_url = add_query_arg(array('preview_id' => $gallery->ID), $gallery_item_url);
		}

		return $gallery_item_url;
	}

	/**
	 * Sanitizes the text to be used as slide annotation.
	 *
	 * @param string $text
	 * @return string
	 */
	public function sanitize_annotation($text)
	{

		$clean = wp_kses($text, [
			'em'     => [],
			'strong' => [],
			'i'      => [],
			'b'      => [],
		]);

		return $clean;
	}

	/**
	 * Adds this post type to the list of supported Co-Authors Plus post types.
	 *
	 * @param array $post_types
	 * @return array
	 */
	public function filter_coauthors_supported_post_types($post_types)
	{

		$post_types[] = self::POST_TYPE;
		return $post_types;
	}

	/**
	 * Adds our gallery tag to the public query variables so we can use it with 'get_query_var'.
	 *
	 * @param $vars
	 * @return array
	 */
	public function filter_query_vars($vars)
	{

		$vars[] = self::GALLERY_TAG;
		return $vars;
	}

	/**
	 * Generate event tracking for the gallery item buttons.
	 *
	 * @param array $events
	 * @return array
	 */
	public function filter_pmc_ga_event_tracking($events = [])
	{

		if (is_singular(self::POST_TYPE)) {
			return array_merge([
				[
					'selector'       => '.pmc-listicle-gallery-v2 .gallery-nav .prev',
					'category'       => 'Navigation',
					'label'          => 'Previous Gallery',
				],
				[
					'selector'       => '.pmc-listicle-gallery-v2 .gallery-nav .next',
					'category'       => 'Navigation',
					'label'          => 'Next Gallery',
				],
				[
					'selector'       => '.pmc-listicle-gallery-v2 .slides-nav.left',
					'category'       => 'Navigation',
					'label'          => 'Previous Slide',
					'nonInteraction' => true,
				],
				[
					'selector'       => '.pmc-listicle-gallery-v2 .slides-nav.right',
					'category'       => 'Navigation',
					'label'          => 'Next Slide',
					'nonInteraction' => true,
				],
				[
					'selector'       => '.pmc-listicle-gallery-v2 .gallery-slides .slide',
					'category'       => 'Image Selection',
					'label'          => 'Slide Viewed Modal',
					'nonInteraction' => true,
				],
				[
					'selector'       => '.pmc-listicle-gallery-v2 .gallery-thumbs .thumbnail',
					'category'       => 'Image Selection',
					'label'          => 'Thumbnail clicked',
					'nonInteraction' => true,
				],
			], $events);
		}

		return $events;
	}

	/**
	 * This triggers before the post meta data are updated and determines if this post should be
	 * detached from the parent gallery that it is currently attached to.
	 *
	 * @param $meta_id
	 * @param $post_id
	 * @param $meta_key
	 * @param $meta_value
	 */
	public function action_update_postmeta($meta_id, $post_id, $meta_key, $meta_value)
	{

		if (self::POST_TYPE !== get_post_type()) {
			return;
		}

		if ('parent_gallery' !== $meta_key) {
			return;
		}

		$old_meta_value = get_post_meta($post_id, 'parent_gallery', true);
		$id = (!empty($old_meta_value['id'])) ? $old_meta_value['id'] : null;
		$parent_galleries = (!empty($old_meta_value['parent_galleries'])) ? $old_meta_value['parent_galleries'] : [];

		if (!empty($id)) {
			$this->_detach_this_post_from_parent_gallery($post_id, $id);
		}

		if (!empty($parent_galleries)) {
			foreach ($parent_galleries as $parent_gallery) {
				$this->_detach_this_post_from_parent_gallery($post_id, $parent_gallery['id']);
			}
		}
	}

	/**
	 * This triggers after the post meta data are updated and determines if this post should be
	 * attached to a new parent gallery.
	 *
	 * @param $meta_id
	 * @param $post_id
	 * @param $meta_key
	 * @param $meta_value
	 */
	public function action_updated_post_meta($meta_id, $post_id, $meta_key, $meta_value)
	{

		if (self::POST_TYPE !== get_post_type()) {
			return;
		}

		if ('parent_gallery' !== $meta_key) {
			return;
		}

		$new_meta_value = get_post_meta($post_id, 'parent_gallery', true);
		$id = (!empty($new_meta_value['id'])) ? $new_meta_value['id'] : null;
		$parent_galleries = (!empty($new_meta_value['parent_galleries'])) ? $new_meta_value['parent_galleries'] : [];

		if (!empty($id)) {
			$this->_attach_this_post_to_parent_gallery($post_id, $id);
		}

		if (!empty($parent_galleries)) {
			foreach ($parent_galleries as $parent_gallery) {
				$this->_attach_this_post_to_parent_gallery($post_id, $parent_gallery['id']);
			}
		}
	}

	/**
	 * This triggers after the post meta data are added and determines if this post should be
	 * attached to a new parent gallery.
	 *
	 * @param $meta_id
	 * @param $post_id
	 * @param $meta_key
	 * @param $meta_value
	 */
	public function action_added_post_meta($meta_id, $post_id, $meta_key, $meta_value)
	{

		$this->action_updated_post_meta($meta_id, $post_id, $meta_key, $meta_value);
	}

	/**
	 * This triggers before the post meta data are deleted and determines if this post should be
	 * detached from the parent gallery that it is currently attached to.
	 *
	 * @param $post_id
	 */
	public function action_before_delete_post($post_id)
	{

		if (self::POST_TYPE !== get_post_type()) {
			return;
		}

		$meta = get_post_meta($post_id, 'parent_gallery', true);

		if (!empty($meta['id'])) {
			$this->_detach_this_post_from_parent_gallery($post_id, $meta['id']);
		}

		if (!empty($meta['parent_galleries'])) {
			foreach ($meta['parent_galleries'] as $parent_gallery) {
				$this->_detach_this_post_from_parent_gallery($post_id, $parent_gallery['id']);
			}
		}
	}

	/**
	 * This adds this post's ID to the parent gallery's list of gallery items.
	 *
	 * @param int $post_id
	 * @param int $parent_gallery_id
	 */
	protected function _attach_this_post_to_parent_gallery($post_id, $parent_gallery_id)
	{

		// get parent gallery's list of gallery items

		$gallery_items = get_post_meta($parent_gallery_id, 'gallery_items', true);

		// add the post ID to the list, if not already there

		if (!empty($gallery_items) && is_array($gallery_items)) {
			if (is_array($gallery_items['ids'])) {
				if (false === array_search($post_id, $gallery_items['ids'], true)) {
					$gallery_items['ids'][] = $post_id;
					update_post_meta($parent_gallery_id, 'gallery_items', $gallery_items);
				}
			}
		}
	}

	/**
	 * This removes this post's ID from the parent gallery's list of gallery items.
	 *
	 * @param int $post_id
	 * @param int $parent_gallery_id
	 */
	protected function _detach_this_post_from_parent_gallery($post_id, $parent_gallery_id)
	{

		// get parent gallery's list of gallery items

		$gallery_items = get_post_meta($parent_gallery_id, 'gallery_items', true);

		// remove the post ID from the list, if found

		if (!empty($gallery_items) && is_array($gallery_items)) {
			if (!empty($gallery_items['ids']) &&  is_array($gallery_items['ids'])) {
				$key = array_search($post_id, $gallery_items['ids'], true);
				if (false !== $key) {
					unset($gallery_items['ids'][$key]);
					$gallery_items['ids'] = array_values($gallery_items['ids']);
					update_post_meta($parent_gallery_id, 'gallery_items', $gallery_items);
				}
			}
		}
	}

	/**
	 * Gets the tags belonging to this post's parent gallery.
	 * Note that gallery items do not have tags themselves.
	 *
	 * @param \WP_Post $post
	 * @return array|bool
	 */
	protected function _get_tags($post)
	{

		// get the gallery to which this gallery item belongs

		$parent_gallery_id = $this->_get_parent_gallery_id();

		if (empty($parent_gallery_id)) {
			return false;
		}

		$gallery = get_post($parent_gallery_id);

		// get the parent gallery tags

		$terms = get_the_terms($gallery->ID, 'post_tag');

		if (is_wp_error($terms) || empty($terms) || !is_array($terms)) {
			return false;
		}

		$tags = [];

		foreach ($terms as $term) {

			$term_link = get_term_link($term->term_id);

			if (!is_wp_error($term_link)) {
				$tags[] = [
					'link' => $term_link,
					'name' => $term->name,
				];
			}
		}

		return $tags;
	}

	/**
	 * Returns parent Gallery ID for current Gallery item
	 *
	 * @return int Post ID, or 0 on failure.
	 */
	protected function _get_parent_gallery_id()
	{

		global $wp;

		$parent_gallery_id = static::is_listicle_preview();

		if (!$parent_gallery_id) {

			$url_part = explode('/', home_url($wp->request));
			array_pop($url_part);
			$parent_gallery_url = implode('/', $url_part);
			$parent_gallery_id  = url_to_postid($parent_gallery_url);
		}

		return $parent_gallery_id;
	}

	/**
	 * Returns true if current pmc-listicle slideshow is in preview state.
	 *
	 * @since 2018-08-03 Jignesh Nakrani READS-1388
	 *
	 * @return bool
	 */
	public static function is_listicle_preview()
	{

		$is_preview = false;
		$preview_id = PMC::filter_input(INPUT_GET, 'preview_id');

		if (is_user_logged_in() && !empty($preview_id)) {

			$is_preview = absint($preview_id);
		}

		return $is_preview;
	}

	/**
	 * Used filter pmc_seo_tweaks_robots_override to add the 'noindex, nofollow' for 'pmc-lst-gallery-item' posts.
	 *
	 * @param $meta_values
	 *
	 * @return string
	 */
	function filter_pmc_seo_tweaks_robots_override($meta_values)
	{

		return (is_singular(self::POST_TYPE) && empty($this->_get_parent_gallery_id())) ? 'noindex, nofollow' : $meta_values;
	}

	/**
	 * Used filter pmc_canonical_url to Removes canonical url tag for 'pmc-lst-gallery-item' post type
	 *
	 * @param $canonical_url string canonical URL for the current page
	 *
	 * @return bool|string
	 */
	public function filter_pmc_canonical_url($canonical_url)
	{

		return (is_singular(self::POST_TYPE) && empty($this->_get_parent_gallery_id())) ? false : $canonical_url;
	}

	/**
	 * adds rel=next and rel=prev meta tag for 'pmc-lst-gallery-item' in <head> section
	 *
	 * @return bool
	 */
	public function action_wp_head()
	{

		// Process only if current page is for pmc-lst-gallery-item post type
		if (!is_singular(self::POST_TYPE)) {
			return false;
		}

		$post = get_post();

		if (empty($post)) {
			return false;
		}

		// get the gallery to which this gallery item belongs
		$gallery           = null;
		$parent_gallery_id = $this->_get_parent_gallery_id();

		if (!empty($parent_gallery_id)) {
			$parent_gallery_id = intval($parent_gallery_id);
			$gallery           = get_post($parent_gallery_id);

			// Parent gallery URL for first gallery-item. (Required as 'prev' URL)
			$gallery_url = Gallery::get_instance()->_get_gallery_url($gallery);
		}

		// use the gallery to get info about the sibling gallery items
		$siblings = $this->_get_siblings_info($gallery, $post->ID);

		if (!empty($siblings['next_gallery_item_url'])) {
			echo sprintf('<link rel="next" href="%s" >', esc_url($siblings['next_gallery_item_url']));
		}

		if (!empty($siblings['prev_gallery_item_url'])) {
			echo sprintf('<link rel="prev" href="%s" >', esc_url($siblings['prev_gallery_item_url']));
		} elseif (!empty($gallery_url)) {
			echo sprintf('<link rel="prev" href="%s" >', esc_url(trailingslashit($gallery_url)));
		}
	}
}
