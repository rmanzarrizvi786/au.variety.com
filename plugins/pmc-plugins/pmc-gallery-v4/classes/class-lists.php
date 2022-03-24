<?php

namespace PMC\Gallery;

/**
 * Lists functionality for PMC properties. Largely migrated from the old pmc-lists plugin originally from Rolling Stone.
 *
 * TODO: Refactor appropriate parts to work with multiple list membership
 *
 * @since 2019-09-09
 */

use PMC\Global_Functions\Traits\Singleton;
use PMC_Cache;

class Lists
{

	use Singleton;

	/**
	 * Post id.
	 *
	 * @var null
	 */
	public static $id = null;

	/**
	 * The current list.
	 *
	 * @var \WP_Post
	 */
	protected static $_list = array();

	/**
	 * All list items (by list ID)
	 *
	 * @var \WP_Post
	 */
	public static $all_list_items_by_list_id = [];

	/**
	 * The list items query for the current page.
	 *
	 * @var \WP_Query
	 */
	public $list_items_query;

	/**
	 * The direction of the current list.
	 *
	 * @var string
	 */
	public $order;

	/**
	 * The direction of the current list.
	 *
	 * @var array
	 */
	private $_build_cache_info = array();

	/**
	 * Lists constructor.
	 * @codeCoverageIgnore
	 */
	protected function __construct()
	{
		add_action('init', array($this, 'init'));
	}

	/**
	 * Check cheezcap before activating everything
	 */
	public function init()
	{
		if (!Lists_Settings::get_instance()->is_gallery_list_enabled()) {
			return;
		}

		add_filter('pmc_do_not_load_plugin', [$this, '_exclude_plugin_from_loading'], 10, 4);
		add_action('wp_ajax_pmc-get-lists', [$this, 'wp_ajax_get_lists']);
		add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_list_scripts']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_assets_for_lists']);
		add_action('save_post', [$this, 'save_post'], 10, 2);
		add_action('save_post_pmc_list_item', [$this, 'save_post_pmc_list_item'], 10, 1);
		add_filter('the_content', array($this, 'add_list_attachment_point'), 20, 2);
		add_filter('pmc_canonical_url', array($this, 'filter_canonical_url'), 20);
		add_action('wp_head', array($this, 'add_next_prev_links'));
		add_filter('pmc_cxense_page_location', [$this, 'filter_pmc_cxense_page_location']);
	}

	/**
	 * Don't load the old pmc-lists plugin
	 *
	 * @param bool $do_exclusion False by default.
	 * @param string $plugin The name of the plugin
	 * @param bool|string $folder The folder which contains the plugin
	 * @param bool|string $version The version of the plugin being loaded. False when not set.
	 *
	 * @return bool Return true to prevent loading of the named plugin.
	 */
	public function _exclude_plugin_from_loading($do_exclusion = false, $plugin = '', $folder = '', $version = false)
	{
		if ('pmc-lists' === $plugin) {
			$do_exclusion = true;
		}

		return $do_exclusion;
	}

	/**
	 * Reorder the list sub menu.
	 *
	 * @param array $menu_order The menu items in order.
	 *
	 * @return mixed
	 */
	public function reorder_menu($menu_order)
	{
		global $submenu;

		$menu = 'edit.php?post_type=pmc_list';

		foreach ($submenu[$menu] as $key => $item) {
			if ('edit.php?post_type=pmc_list_item' === $item[2]) {
				$submenu[$menu][11] = $item;
				unset($submenu[$menu][$key]);
			}
		}

		ksort($submenu[$menu]);

		return $menu_order;
	}

	/**
	 * Add admin scripts for plugin.
	 *
	 * @return void
	 */
	public function admin_enqueue_list_scripts($hook)
	{
		global $post;
		if (in_array($hook, ['edit.php', 'post.php', 'post-new.php'], true) && Lists_Settings::LIST_ITEM_POST_TYPE === get_post_type()) {
			wp_enqueue_script(
				'pmc-get-lists',
				Lists_Settings::PMC_LISTS_URL . 'assets/build/js/admin-list-match.js',
				['jquery'],
				'1.1',
				true
			);
			$wpnonce = wp_create_nonce('pmc_get_lists');
			wp_localize_script(
				'pmc-get-lists',
				'pmcListV4',
				[
					'_nonce' => $wpnonce,
				]
			);
			wp_register_style('pmc-list-css', Lists_Settings::PMC_LISTS_URL . '/assets/build/css/admin-list.css');
			wp_enqueue_style('pmc-list-css');
		}
		if (('post.php' === $hook || 'post-new.php' === $hook) && Lists_Settings::LIST_POST_TYPE === get_post_type()) {
			wp_register_script(
				Lists_Settings::LIST_POST_TYPE . '-admin-post',
				Lists_Settings::PMC_LISTS_URL . 'assets/build/js/admin-list.js',
				array(
					'jquery',
					'jquery-ui-sortable',
				),
				false,
				true
			);
			wp_register_style('pmc-list-css', Lists_Settings::PMC_LISTS_URL . '/assets/build/css/admin-list.css');
			wp_enqueue_style('pmc-list-css');
			wp_enqueue_script(Lists_Settings::LIST_POST_TYPE . '-admin');

			wp_localize_script(
				Lists_Settings::LIST_POST_TYPE . '-admin-post',
				'pmc_list_admin_options',
				array(
					'ajaxurl'         => admin_url('admin-ajax.php'),
					'sortOrderNonce'  => wp_create_nonce('get-list-sorted-nonce'),
					'pmc_list_update' => wp_create_nonce('pmc_list_update'),
				)
			);
			// Get the list items
			$list_items = $this->list_items_for_admin($post->ID);
			wp_localize_script(Lists_Settings::LIST_POST_TYPE . '-admin-post', 'pmc_list_items', $list_items);
			wp_enqueue_script(Lists_Settings::LIST_POST_TYPE . '-admin-post');
		}
	}

	/**
	 * Enqueue styles, scripts and script data
	 */
	public function enqueue_assets_for_lists()
	{
		if (!is_singular(Lists_Settings::LIST_POST_TYPE)) {
			return;
		}
		$handle      = 'pmc-lists-front';
		$list_config = Lists_Settings::get_instance()->get_list_config();
		wp_register_script(
			$handle,
			Lists_Settings::PMC_LISTS_URL . 'assets/build/js/gallery-vertical.js',
			array('jquery'),
			PMC_GALLERY_VERSION
		);
		wp_enqueue_script($handle);
		wp_localize_script($handle, 'pmcGalleryExports', $list_config);

		if (isset($list_config['styles'])) {
			$styles = View::get_instance()->get_gallery_styles($list_config['styles']);
		}

		if ($styles) {
			wp_enqueue_style('pmc-gallery-vertical');
			wp_add_inline_style('pmc-gallery-vertical', $styles);
		}

		wp_enqueue_style(Defaults::NAME . '-vertical');
	}

	/**
	 * Actions to be performed when saving a post.
	 *
	 * @param int $list_id The ID of the current post.
	 * @param \WP_Post The current post object.
	 *
	 * @return void|false Returns false if the post is not updated.
	 */
	public function save_post($list_id, $post)
	{
		if (wp_is_post_revision($list_id)) {
			return false;
		}
		// When saving a list, add the ID to the relationship taxonomy to related items to this post.
		if (Lists_Settings::LIST_POST_TYPE === $post->post_type) {
			//	verify nonce from form.
			$nonce = \PMC::filter_input(INPUT_POST, 'pmc-listoptions-nonce', FILTER_SANITIZE_STRING);
			if (
				(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
				|| (wp_doing_ajax())
				|| (isset($_REQUEST['bulk_edit']))
				|| (!$nonce)
				|| (!wp_verify_nonce($nonce, 'set_list_display_options'))
				|| (!current_user_can('edit_posts', $list_id))
			) {
				return;
			}

			// When saving a list, create the taxonomy item used to connect list items to this list.
			if (!term_exists($list_id, Lists_Settings::LIST_RELATION_TAXONOMY)) {
				wp_insert_term(
					$list_id,
					Lists_Settings::LIST_RELATION_TAXONOMY,
					[
						'slug' => $list_id,
					]
				);
			}
			// Save list item ordering.
			$post_list_items = \PMC::filter_input(INPUT_POST, 'pmclistitems', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
			if ($post_list_items) {
				// codecov ignore because I can't figure out how to test list
				// Setting $_POST array seems to not work on filter_input for testing.
				update_post_meta($list_id, Lists_Settings::ORDERED_LIST_ITEMS, $post_list_items);  // @codeCoverageIgnore
			}
			// Check and remove items
			$pmc_listitems_remove = \PMC::filter_input(
				INPUT_POST,
				'pmc-listitems-remove',
				FILTER_DEFAULT,
				FILTER_FORCE_ARRAY
			);
			if ($pmc_listitems_remove) {
				$old_pmc_list         = get_term_by('slug', $list_id, Lists_Settings::LIST_RELATION_TAXONOMY);
				$pmc_listitems_remove = is_array($pmc_listitems_remove) ? $pmc_listitems_remove : [$pmc_listitems_remove];
				foreach ($pmc_listitems_remove as $list_item) {
					// Remove the association
					wp_remove_object_terms(
						$list_item,
						$old_pmc_list->term_id,
						Lists_Settings::LIST_RELATION_TAXONOMY
					);
				}
			}

			// Save list display options.
			$post_value_template  = \PMC::filter_input(
				INPUT_POST,
				Lists_Settings::TEMPLATE_OPT_KEY,
				FILTER_UNSAFE_RAW
			);
			$post_value_numbering = \PMC::filter_input(
				INPUT_POST,
				Lists_Settings::NUMBERING_OPT_KEY,
				FILTER_UNSAFE_RAW
			);

			if ($post_value_template) {
				update_post_meta(
					$list_id,
					Lists_Settings::TEMPLATE_OPT_KEY,
					sanitize_text_field(wp_unslash($post_value_template))
				);
			}
			if ($post_value_numbering) {
				update_post_meta(
					$list_id,
					Lists_Settings::NUMBERING_OPT_KEY,
					sanitize_text_field(wp_unslash($post_value_numbering))
				);
			}

			$pmc_cache = Plugin::get_instance()->create_cache_instance('all_list_items-' . $list_id, 'lists');
			$pmc_cache->invalidate();
		}
	}


	/**
	 * Save the list item
	 *
	 * @param $list_id
	 */
	function save_post_pmc_list_item($list_id)
	{
		if (wp_is_post_revision($list_id)) {
			return;
		}

		if (isset($_REQUEST['bulk_edit'])) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		// Get new list id from the POST
		$pmc_list_id = \PMC::filter_input(INPUT_POST, 'pmc_list_id', FILTER_UNSAFE_RAW);

		if (!$pmc_list_id)
			return;

		// get old list id from db
		$old_pmc_list    = self::get_relation_term_for_item($list_id);
		$old_pmc_list_id = $old_pmc_list->slug;

		// remove action to prevent cyclic call
		remove_action('save_post', [$this, 'save_post']);
		if ($pmc_list_id === $old_pmc_list_id) {
			// New List and Old are the same, don't need to do anything extra
			return;
		}
		$item_key = false;
		// Get old list id. If it exists, we need to disassociate this.
		$old_ordered_listitems = get_post_meta($old_pmc_list_id, Lists_Settings::ORDERED_LIST_ITEMS, true);
		$old_ordered_listitems = is_array($old_ordered_listitems) ? $old_ordered_listitems : array();
		if (!empty($old_ordered_listitems)) {
			$item_key = array_search($list_id, (array) $old_ordered_listitems, true);
			if (false !== $item_key) {
				// We need to delete the listitem from the old list
				array_splice($old_ordered_listitems, $item_key, 1);
				update_post_meta($old_pmc_list_id, Lists_Settings::ORDERED_LIST_ITEMS, $old_ordered_listitems);
			};
			unset($item_key);
		}
		// Remove the old taxonomy
		wp_remove_object_terms($list_id, $old_pmc_list->term_id, Lists_Settings::LIST_RELATION_TAXONOMY);

		// Now check for new list assignment
		$ordered_listitems = get_post_meta($pmc_list_id, Lists_Settings::ORDERED_LIST_ITEMS, true);
		$ordered_listitems = is_array($ordered_listitems) ? $ordered_listitems : array();
		// add new list assignment
		wp_set_post_terms($list_id, $pmc_list_id, Lists_Settings::LIST_RELATION_TAXONOMY, true);
		if (!empty($ordered_listitems)) {
			// If it's not empty, check to see if it's already assigned.
			$item_key = array_search($list_id, (array) $ordered_listitems, true);
		}
		if (false === $item_key || empty($ordered_listitems)) {
			// List item is not part of the list yet OR it's an empty list right now.
			// Don't need to do anything if the list item is already associated.
			$ordered_listitems[] = $list_id;
			update_post_meta($pmc_list_id, Lists_Settings::ORDERED_LIST_ITEMS, $ordered_listitems);
		}

		// Cache rebuild for item updated for lists.		
		$this->_build_cache_info = [
			'list_id'     => $pmc_list_id,
			'old_list_id' => $old_pmc_list_id,
		];
		add_action('shutdown', [$this, 'action_shutdown']);
	}

	/**
	 * Trigger cache refresh during shutdown.
	 */
	public function action_shutdown()
	{
		if (!empty($this->_build_cache_info)) {
			$this->pmc_built_async_cache($this->_build_cache_info['list_id'], $this->_build_cache_info['old_list_id']);
		}
	}

	/**
	 * Remove old cache and rebuild the updated one for the list.
	 *
	 * @param int $pmc_list_id New assigned list id.
	 * @param int $old_pmc_list_id Removed/old list id.
	 */
	public function pmc_built_async_cache($pmc_list_id, $old_pmc_list_id)
	{

		$lists_ids_to_rebuild_cache = array($pmc_list_id, $old_pmc_list_id);

		// Rebuild Cache for both lists ( removed and added lists ).
		foreach ($lists_ids_to_rebuild_cache as $list_id) {

			// Skip when there is no old list assosiated.
			if (empty($list_id)) {
				continue;
			}

			$pmc_cache = Plugin::get_instance()->create_cache_instance('all_list_items-' . $list_id, 'lists');
			$pmc_cache->invalidate();

			$pmc_cache = Plugin::get_instance()->create_cache_instance('all_list_items-' . $list_id, 'lists');

			$pmc_list_cache = $pmc_cache->expires_in(15 * MINUTE_IN_SECONDS)->updates_with(
				[
					$this,
					'get_all_list_items_sorted',
				],
				[
					$list_id,
				]
			)->get();

			self::$all_list_items_by_list_id[$list_id] = $pmc_list_cache;
		}

		return self::$all_list_items_by_list_id[$pmc_list_id];
	}

	/**
	 * Gets the list relation term assigned to a given item.
	 *
	 * @param int $item_id An item ID.
	 *
	 * @return null|\WP_Term A term object or null on failure.
	 */
	public static function get_relation_term_for_item($item_id)
	{

		$list_terms = get_the_terms($item_id, Lists_Settings::LIST_RELATION_TAXONOMY);

		if (!empty($list_terms)) {
			return reset($list_terms);
		}
	}


	/**
	 * Ajax call to get lists for the autocomplete.
	 *
	 */
	public function wp_ajax_get_lists()
	{
		$nonce = \PMC::filter_input(INPUT_POST, '_nonce', FILTER_SANITIZE_STRING);
		if (empty($nonce) || !wp_verify_nonce($nonce, 'pmc_get_lists')) {
			wp_send_json_error('Nonce verification failed.');
		}
		$search_query = \PMC::filter_input(INPUT_POST, 'query', FILTER_SANITIZE_STRING);

		if (!empty($search_query) && is_string($search_query)) {
			$lists = new \WP_Query(
				[
					's'              => $search_query,
					'post_status'    => [
						'publish',
						'draft',
					],
					'post_type'      => Lists_Settings::LIST_POST_TYPE,
					'posts_per_page' => 5,
				]
			);
			if (!empty($lists->posts)) {
				wp_send_json_success(wp_list_pluck($lists->posts, 'post_title', 'ID'));
			} else {
				wp_send_json_success([]);
			}
		}
		wp_die();
		// codecov ignore because this final bracket keeps getting missed because of the wp_die
	} // @codeCoverageIgnore

	/**
	 * Gets the list post linked by taxonomy to a list item post.
	 *
	 * @param int $item_id A list item ID.
	 *
	 * @return \WP_Post|bool The found post or false on failure.
	 */
	public function get_list_for_item($item_id)
	{

		$term = self::get_relation_term_for_item($item_id);

		// note: need to avoid get_post(0) where it return the default current post object
		if (!empty($term) && is_numeric($term->name)) {
			$post = get_post(intval($term->name));
			if (!empty($post) && Lists_Settings::LIST_POST_TYPE === $post->post_type) {
				return $post;
			}
		}
	}

	public function get_all_list_items($list_id)
	{
		$pmc_cache = Plugin::get_instance()->create_cache_instance('all_list_items-' . $list_id, 'lists');

		$pmc_list_cache = $pmc_cache->expires_in(15 * MINUTE_IN_SECONDS)->updates_with(
			[
				$this,
				'get_all_list_items_sorted',
			],
			[
				$list_id,
			]
		)->get();

		self::$all_list_items_by_list_id[$list_id] = $pmc_list_cache;

		return self::$all_list_items_by_list_id[$list_id];
	}

	public function get_all_list_items_sorted($list_id)
	{

		if (isset(self::$all_list_items_by_list_id[$list_id])) {
			return self::$all_list_items_by_list_id[$list_id];
		}

		$list_query = [
			'post_status'            => 'publish',
			'post_type'              => Lists_Settings::LIST_ITEM_POST_TYPE,
			'posts_per_page'         => -1,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'tax_query'              => [
				[
					'taxonomy' => Lists_Settings::LIST_RELATION_TAXONOMY,
					'field'    => 'slug',
					'terms'    => $list_id,
				],
			],
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts'    => true,
		];

		$unordered_list_items = (new \WP_Query($list_query))->posts;

		if (empty($unordered_list_items)) {
			return [];
		}

		$sorted_list_items = self::sort_list_items($list_id, $unordered_list_items);

		// Process slugs here, needed for pagination to work
		// Cannot rely on post_name here -- some themes modify post slugs to append ID's (pmc-rollingstone-2018)
		foreach ($sorted_list_items as &$item) {
			$post_slug = get_permalink($item->ID);
			$post_slug = rtrim($post_slug, '/');
			$post_slug = explode('/', $post_slug);
			$post_slug = array_pop($post_slug);

			$item->slug = $post_slug;
		}

		self::$all_list_items_by_list_id[$list_id] = $sorted_list_items;

		return self::$all_list_items_by_list_id[$list_id];
	}

	/**
	 * Provide scaled down list for the admin for sorting
	 *
	 * @param $list_id
	 *
	 * @return array of list items for admin
	 */
	public function list_items_for_admin($list_id)
	{
		$items     = $this->get_all_list_items($list_id);
		$for_admin = [];
		foreach ($items as $count => $item) {
			$f_item             = [
				'num'      => $count,
				'ID'       => $item->ID,
				'title'    => $item->post_title,
				'status'   => $item->post_status,
				'imageID'  => get_post_thumbnail_id($item->ID),
				'editLink' => get_edit_post_link($item->ID),
			];

			$external_image_src = '';
			if (get_post_meta($item->ID, 'thumbnail_ext_url')) {
				$external_image_src = get_post_meta($item->ID, 'thumbnail_ext_url', true);
			}

			if ('' != $external_image_src) {
				$f_item['imageSrc'] = '<img src="' . $external_image_src . '&w=130&h=130&crop=1">';
			} else {
				$f_item['imageSrc'] = has_post_thumbnail($item->ID) ? get_the_post_thumbnail($item->ID, 'thumbnail') : '';
			}
			$for_admin[]        = $f_item;
		}

		return $for_admin;
	}

	/**
	 * Orders list items according to the user-specified order saved in post_meta.
	 * Any items that are in the main list but not the ordered list are appended
	 *
	 * @param $list_id integer ID of list
	 * @param items array of list items
	 *
	 * @return array
	 */
	public static function sort_list_items($list_id, $items)
	{
		// Array of WP_Post objects, keyed by post ID for lookup.
		$items_lookup = array_column($items, null, 'ID');

		// Array of post IDs in user-specified order from post meta.
		$ordered_list = get_post_meta($list_id, Lists_Settings::ORDERED_LIST_ITEMS, true);

		// Initialize
		$sorted_list_items = [];

		// Build array in specified order
		foreach ((array) $ordered_list as $ordered_list_item_post_id) {
			if (isset($items_lookup[$ordered_list_item_post_id]) && $items_lookup[$ordered_list_item_post_id] instanceof \WP_Post) {
				$sorted_list_items[] = $items_lookup[$ordered_list_item_post_id];
				unset($items_lookup[$ordered_list_item_post_id]);
			}
		}

		// Append remaining un-ordered list items
		foreach ((array) $items_lookup as $unordered_list_item) {
			if ($unordered_list_item instanceof \WP_Post) {
				$sorted_list_items[] = $unordered_list_item;
			}
		}

		return $sorted_list_items;
	}

	/**
	 * Fetch List
	 *
	 * @param mixed  $list_id May be a string or int with a
	 *                        single list ID
	 *
	 * @param string $list_template
	 *
	 * @return array|\WP_Post|null
	 * @TODO: does this need to be more like fetch_gallery and retrieve mutliple lists?
	 *
	 *
	 */
	public static function fetch_list($list_id = null, $list_template = '')
	{
		global $post;

		// return the cached data if available
		if (!empty(self::$_list)) {
			return self::$_list;
		}

		// Nothing passed use post id.
		if (null === $list_id) {
			if (isset($post->ID)) {
				$list_id = $post->ID;
			} else {
				return null;
			}
		}

		$all_list_items       = Lists::get_instance()->get_all_list_items($list_id);
		$all_list_items_count = count($all_list_items);

		$current_linked_item_slug  = get_query_var('pmc_list_item_slug');
		$current_linked_item_index = array_search($current_linked_item_slug, array_column($all_list_items, 'post_name'), true);

		$list_items_per_page = Lists_Settings::get_instance()->get_list_items_per_page();

		$current_page                 = max(1, ceil(($current_linked_item_index + 1) / $list_items_per_page));
		$current_page_items           = array_slice($all_list_items, ($current_page - 1) * $list_items_per_page, $list_items_per_page, true);
		$current_page_items_count     = count($current_page_items);
		$current_page_last_item_index = array_keys((array) $current_page_items)[$current_page_items_count - 1];

		if (!$current_page_items) {
			return array();
		}
		remove_filter('the_content', array(Lists::get_instance(), 'add_list_attachment_point'), 20);
		$list_response  = array();
		$list_url       = get_permalink($list_id);
		$list_order     = get_post_meta($list_id, Lists_Settings::NUMBERING_OPT_KEY, true);

		// Ready list item data for response.
		foreach ($current_page_items as $index => $item) {

			$image_id  = empty($item->custom_thumbnail_id) ? get_post_thumbnail_id($item->ID) : $item->custom_thumbnail_id;

			$external_image_src = '';
			if (get_post_meta($item->ID, 'thumbnail_ext_url')) {
				$external_image_src = get_post_meta($item->ID, 'thumbnail_ext_url', true);
			}
			if ('' != $external_image_src) {
				// list($original_width, $original_height) = getimagesize($external_image_src);
				list($original_width, $original_height) = get_post_meta($item->ID, 'thumbnail_ext_url_dims', true);
			} else {
				$image_src = wp_get_attachment_image_src($image_id, 'ratio-1x1');

				// Get post attachment meta.
				$attachment_meta = get_post_meta($image_id);
				$attachment_meta = (is_array($attachment_meta)) ? $attachment_meta : array();

				$original_image_meta = wp_get_attachment_image_src($image_id, 'full');

				$original_width  = isset($original_image_meta[1]) ? $original_image_meta[1] : null;
				$original_height = isset($original_image_meta[2]) ? $original_image_meta[2] : null;
			}

			if (get_post_meta($item->ID, 'thumbnail_ext_image_credit')) {
				$thumbnail_ext_image_credit = get_post_meta($item->ID, 'thumbnail_ext_image_credit', true);
			}

			// Cap image height while maintaining aspect ratio
			$new_full_height = min($original_height, 575);
			$new_full_width  = intval(round($new_full_height * ($original_width / max((int) $original_height, 1))));

			$data_to_fill = [
				'ID'              => $item->ID,
				'position'        => $index,
				'positionDisplay' => 'desc' === $list_order ? $all_list_items_count - $index : $index + 1,
				'date'            => $item->post_date,
				'modified'        => $item->post_modified,
				'title'           => empty($item->custom_title) ? $item->post_title : $item->custom_title,
				'subtitle'        => static::get_list_item_subtitle($item),
				'slug'            => $item->slug,
				'caption'         => self::get_list_item_image_caption($item),
				'description'     => apply_filters('the_content', $item->post_content),
				'alt'             => !empty($attachment_meta['_wp_attachment_image_alt'][0]) ? $attachment_meta['_wp_attachment_image_alt'][0] : '',
				'image_credit'    => isset($thumbnail_ext_image_credit) ? $thumbnail_ext_image_credit : (!empty($attachment_meta['_image_credit'][0]) ? $attachment_meta['_image_credit'][0] : ''),
				'url'             => $list_url,
				'image'           => isset($external_image_src) && '' != $external_image_src ? $external_image_src : (isset($image_src[0]) ? $image_src[0] : null),
				'sizes'           => isset($external_image_src) && '' != $external_image_src ? Lists_Settings::get_image_sizes_external($external_image_src) : Lists_Settings::get_image_sizes($image_id, $list_template),
				'fullWidth'       => $new_full_width,
				'fullHeight'      => $new_full_height,
				'mime_type'       => 'image',
				'ad'              => '',
				// 'appleSongID'     => static::get_list_item_apple_song_id($item),
				// @TODO Make this configurable from theme config or wp-admin?
				// 'enableAppleGA'   => (!\PMC_TimeMachine::create('America/New_York')->has_passed('2020-11-07')),
			];
			$data_to_fill = self::get_video_list_item($item, $data_to_fill);

			if ($index !== $current_page_last_item_index) {
				$ad_frequency = absint(cheezcap_get_option('pmc_list_ad_frequency'));
				$ad_frequency = $ad_frequency ? $ad_frequency : 1;
				$ad_location  = 'lists-top-river-ad';
				$ad_location2 = false;

				if ((0 === ($index + 1 + ($ad_frequency - 1)) % $ad_frequency) && 1 !== $index + 1) {
					$ad_location  = 'in-list-x';
					$ad_location2 = 'lists-river-ad'; // fall back to deprecated location if not found
				} elseif (1 === $index + 1) {
					$ad_location  = 'in-list-1';
					$ad_location2 = 'lists-top-river-ad';  // fall back to deprecated location if not found
				}

				// Note: $ad_location2 is a fallback if first ad location has empty ads
				// Add for backward compatible
				$data_to_fill['ads'] = [$ad_location, $ad_location2];
			}

			$data_to_fill    = apply_filters('pmc_list_item_data', $data_to_fill, $item);

			$list_response[] = $data_to_fill;
		}

		self::$_list = apply_filters('pmc_list_data', $list_response, self::$id);
		add_filter('the_content', array(Lists::get_instance(), 'add_list_attachment_point'), 20, 2);

		// View::process_ads(self::$_list);

		return self::$_list;
	}

	/**
	 * @param $list_item \WP_Post list item object
	 *
	 * @return string image caption for the list item
	 */
	public static function get_list_item_image_caption($list_item)
	{
		$image_caption = !empty($list_item->custom_excerpt) ? $list_item->custom_excerpt : $list_item->post_excerpt;
		return apply_filters('pmc_gallery_v4_lists_list_item_image_caption', $image_caption, $list_item);
	}

	/**
	 * @param $list_item \WP_Post list item object
	 *
	 * @return string|null subtitle for the list item
	 */
	public static function get_list_item_subtitle(object $list_item): ?string
	{
		if (true === apply_filters('pmc_gallery_v4_lists_enable_list_item_subtitle', false)) {
			$fieldname = sprintf('%s_subtitle', Lists_Settings::LIST_ITEM_POST_TYPE);
			return (!empty($list_item->{$fieldname})) ? $list_item->{$fieldname} : null;
		}
		return null;
	}

	/**
	 * @param $list_item \WP_Post list item object
	 *
	 * @return string|null Apple Music song ID for the list item
	 */
	public static function get_list_item_apple_song_id(object $list_item): ?string
	{
		if (true === apply_filters('pmc_gallery_v4_lists_enable_list_item_apple_music_player', false)) {
			$fieldname = sprintf('%s_apple_song_id', Lists_Settings::LIST_ITEM_POST_TYPE);
			return (!empty($list_item->{$fieldname})) ? $list_item->{$fieldname} : null;
		}
		return null;
	}

	/**
	 * Reset $_list.
	 */
	public static function reset_list_var()
	{
		self::$_list = array();
	}

	/**
	 * @param $content string content body of List
	 *
	 * @return string content adding the div to insert react component.
	 */
	public function add_list_attachment_point($content)
	{
		if (is_admin()) {
			return $content;
		}

		if (Lists_Settings::LIST_POST_TYPE !== get_post_type()) {
			return $content;
		}

		$shell = '<div id="pmc-gallery-vertical">';
		$shell .= View::get_instance()->create_react_app_shell_placeholder('c-gallery-vertical-loader');
		$shell .= '</div>';

		$content = $content . ' ' . $shell;

		return $content;
	}

	/**
	 *
	 * If the List Item should be a video, not an image, add the video and change
	 * the mime_type to video.
	 *
	 * Cribbed from pmc-rollingstone-2018/inc/helpers/template-tags.php
	 *
	 * @param $list_item   \WP_Post list item object
	 * @param $parsed_data array List item parsed for localized front end
	 *
	 * @return array $parsed_data array list item
	 */
	public static function get_video_list_item($list_item, $parsed_data)
	{
		// Fetch the video source. PMC_Featured_Video_Override::META_KEY
		$video_source = get_post_meta($list_item->ID, '_pmc_featured_video_override_data', true);

		if (empty($video_source)) {
			$video_source = get_post_meta($list_item->ID, 'pmc_top_video_source', true);
		}

		if (empty($video_source)) {
			return $parsed_data;
		}

		$video_source = self::filter_youtube_url($video_source);

		// For YouTube, apply an iFrame. Preferes for www.youtube.com/embed links.
		if (false !== strpos($video_source, 'youtube.com')) {
			$video_html = '<iframe type="text/html" width="670" height="407" data-src="%1$s" allowfullscreen="true" style="border:0;"></iframe>';

			$video_source = sprintf($video_html, esc_url($video_source));
		} elseif (!empty(wp_parse_url($video_source, PHP_URL_HOST))) {    // phpcs:ignore

			// Run it via oEmbed parser to parse any embeds in there
			$video_source = ''; // wpcom_vip_wp_oembed_get($video_source);
		} else {

			// Run source via shortcode parser to parse any shortcodes in there
			$video_source = do_shortcode($video_source);
		}
		ob_start();
		get_template_part('template-parts/video-picture');
		$video_badge = ob_get_contents();
		ob_end_clean();
		$parsed_data['video']     = $video_badge . $video_source;
		$parsed_data['mime_type'] = 'video';

		return $parsed_data;
	}

	/**
	 * To filter Query args from youtube URL.
	 *
	 * @before https://www.youtube.com/watch?v=vJjw5kMr9X8&feature=youtu.be
	 * @after  https://www.youtube.com/embed/vJjw5kMr9X8
	 *
	 * @param string $url url to filter query args.
	 *
	 * @return string
	 */
	public static function filter_youtube_url($url)
	{

		if (!empty($url)) {
			$url_parts = wp_parse_url($url);
			$host      = !empty($url_parts['host']) ? $url_parts['host'] : '';

			if (
				'www.youtube.com' === $host
				|| 'youtube.com' === $host
				|| 'www.youtu.be' === $host
				|| 'youtu.be' === $host
			) {
				$host = 'youtube.com';
				$path = '/embed/';
				// The url pattern will most likely either be youtube/watch?v=XXXX or youtu.be/XXXXX
				// Both should be converted to youtube.com/embed/XXXX

				if (!empty($url_parts['query'])) {
					parse_str($url_parts['query'], $query_params);
				}
				if (!preg_match('/^\/(embed|watch)/', $url_parts['path'])) {
					preg_match('/^\/([a-zA-Z-\d_]+)/', $url_parts['path'], $path_matches);
					if (!empty($path_matches)) {
						$query_params = ['v' => $path_matches[1]];
					}
				}
				$url = sprintf('%1$s://%2$s%3$s', $url_parts['scheme'], $host, $path);

				if (!empty($query_params['v'])) {
					$url .= $query_params['v'];
					$url .= '?version=3&#038;rel=1&#038;fs=1&#038;autohide=2&#038;showsearch=0&#038;showinfo=1&#038;iv_load_policy=1&#038;wmode=transparent';
				}
			}
		}

		return $url;
	}

	/**
	 * Filter the rel="canonical" href
	 * @param string $rel_canonical Existing value.
	 * @return string
	 */
	public function filter_canonical_url($rel_canonical)
	{
		if (!is_singular(Lists_Settings::LIST_POST_TYPE)) {
			return $rel_canonical;
		}

		global $wp;
		return trailingslashit(home_url($wp->request));
	}

	/**
	 * Get current slide slug.
	 * Used by add_next_prev_links() to retrieve the slug for nav arrows.
	 * Used by filter_canonical_url() to filter the canonical url.
	 *
	 * @return mixed
	 */
	public function get_current_slug()
	{
		global $wp;

		if (!is_singular(Lists_Settings::LIST_POST_TYPE)) {
			return false;
		}

		$current_permalink = trailingslashit(get_permalink());
		$current_url       = trailingslashit(home_url($wp->request));
		$current_slug      = str_replace($current_url, '', $current_permalink);

		return $current_slug;
	}

	/**
	 * Add next prev links.
	 *
	 * @return void
	 */
	public function add_next_prev_links()
	{
		if (!is_singular(Lists_Settings::LIST_POST_TYPE)) {
			return;
		}

		$all_list_items = $this->get_all_list_items(get_the_ID());

		$current_linked_item_index = array_search(
			get_query_var('pmc_list_item_slug'),
			array_column((array) $all_list_items, 'post_name'),
			true
		);

		$links = [
			'prev' => $all_list_items[$current_linked_item_index - 1]->slug ?? false,
			'next' => $all_list_items[$current_linked_item_index + 1]->slug ?? false,
		];

		// when viewing list without deep link, override "next" to be the first list item slug
		if (false === $current_linked_item_index) {
			$links['next'] = $all_list_items[0]->slug ?? false;
		}

		foreach ($links as $rel => $href) {
			echo $href ? sprintf('<link rel="%s" href="%s">', esc_attr($rel), esc_url(trailingslashit(get_permalink()) . $href)) : '';
		}
	}

	/**
	 * Trick Cxense into thinking deep-link canonical URLs are actually the list base URL.
	 *
	 * @param $page_location
	 *
	 * @return string
	 */
	public function filter_pmc_cxense_page_location($page_location)
	{

		if (is_singular(Lists_Settings::LIST_POST_TYPE)) {
			$page_location = trailingslashit(get_permalink());
		}

		if (is_singular(Lists_Settings::LIST_ITEM_POST_TYPE)) {
			$list          = $this->get_list_for_item(get_the_ID());
			$page_location = trailingslashit(get_permalink($list));
		}

		return $page_location;
	}
}
