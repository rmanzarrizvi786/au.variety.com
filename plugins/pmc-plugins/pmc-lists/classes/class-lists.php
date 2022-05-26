<?php

/**
 * Lists functionality for PMC properties.
 *
 * @since 2018-04-23
 */

namespace PMC\Lists;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Lists.
 */
class Lists
{

	use Singleton;

	/**
	 * Post type for the list.
	 */
	const LIST_POST_TYPE = 'pmc_list';

	/**
	 * Post type of the list items.
	 */
	const LIST_ITEM_POST_TYPE = 'pmc_list_item';

	/**
	 * Used for relating lists to list items. Cause querying META is slow.
	 */
	const LIST_RELATION_TAXONOMY = 'pmc_list_relation';

	/**
	 * The meta key for the list numbering direction.
	 */
	const NUMBERING_OPT_KEY = 'pmc_list_numbering';

	/**
	 * The meta key for the list item template type.
	 */
	const TEMPLATE_OPT_KEY = 'pmc_list_template';

	/**
	 * The current list.
	 *
	 * @var \WP_Post
	 */
	public $list;

	/**
	 * List items for the current page.
	 *
	 * @var array
	 */
	public $list_items;

	/**
	 * The list items query for the current page.
	 *
	 * @var \WP_Query
	 */
	public $list_items_query;

	/**
	 * The number of items in the current list.
	 *
	 * @var int
	 */
	public $list_items_count;

	/**
	 * The direction of the current list.
	 *
	 * @var string
	 */
	public $order;

	/**
	 * The relational term linking the current list and its items.
	 *
	 * @var \WP_Term
	 */
	public $term;

	/**
	 * The index of the first list item to be visible on page load.
	 *
	 * @var int
	 */
	public $queried_item_index;

	/**
	 * Lists constructor.
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct()
	{

		add_action('init', [$this, 'init']);
		add_action('wp_ajax_pmc_get_lists', [$this, 'get_lists']);
		add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
		add_action('save_post', [$this, 'save_post'], 10, 2);
		add_action('pre_get_posts', [$this, 'modify_reorder_query']);
		add_action('manage_pmc_list_item_posts_custom_column', [$this, 'list_item_manage_custom_column'], 10, 2);
		add_action('manage_pmc_list_posts_custom_column', [$this, 'list_manage_custom_column'], 10, 2);

		add_filter('simple_page_ordering_is_sortable', [$this, 'enable_sorting'], 20, 2);
		add_filter('restrict_manage_posts', [$this, 'filter_list_items']);
		// add_filter('views_edit-' . self::LIST_ITEM_POST_TYPE, [$this, 'show_current_list']);
		add_filter('views_edit-' . self::LIST_ITEM_POST_TYPE, [$this, 'fix_sort_by_order_link'], 99);
		add_filter('custom_menu_order', [$this, 'reorder_menu']);
		add_filter('pmc_sitemaps_post_type_whitelist', [$this, 'whitelist_post_type_for_sitemaps']);
		add_filter('manage_pmc_list_item_posts_columns', [$this, 'list_item_add_custom_column']);
		add_filter('manage_pmc_list_posts_columns', [$this, 'list_add_custom_column']);
	}

	/**
	 * Register the list and list item post types, and add URL query var.
	 */
	public function init()
	{
		register_post_type(self::LIST_POST_TYPE, [
			'labels'               => [
				'name'               => wp_strip_all_tags(__('Lists', 'pmc-lists')),
				'singular_name'      => wp_strip_all_tags(__('List', 'pmc-lists')),
				'add_new'            => wp_strip_all_tags(_x('Add New', 'List', 'pmc-lists')),
				'add_new_item'       => wp_strip_all_tags(__('Add New List', 'pmc-lists')),
				'edit_item'          => wp_strip_all_tags(__('Edit List', 'pmc-lists')),
				'new_item'           => wp_strip_all_tags(__('New List', 'pmc-lists')),
				'view_item'          => wp_strip_all_tags(__('View List', 'pmc-lists')),
				'search_items'       => wp_strip_all_tags(__('Search Lists', 'pmc-lists')),
				'not_found'          => wp_strip_all_tags(__('No Lists found.', 'pmc-lists')),
				'not_found_in_trash' => wp_strip_all_tags(__('No Lists found in Trash.', 'pmc-lists')),
				'all_items'          => wp_strip_all_tags(__('Lists', 'pmc-lists')),
			],
			'public'               => true,
			'supports'             => ['title', 'author', 'comments', 'thumbnail', 'excerpt', 'editor'],
			'has_archive'          => 'lists',
			'rewrite'              => [
				'slug' => 'lists',
			],
			'register_meta_box_cb' => [$this, 'list_meta_boxes'],
			'taxonomies'           => ['category', 'post_tag'],
			'menu_icon'            => 'dashicons-list-view',
		]);

		register_post_type(self::LIST_ITEM_POST_TYPE, [
			'labels'               => [
				'name'               => wp_strip_all_tags(__('List Item', 'pmc-lists')),
				'singular_name'      => wp_strip_all_tags(__('List Item', 'pmc-lists')),
				'add_new'            => wp_strip_all_tags(_x('Add New', 'List Item', 'pmc-lists')),
				'add_new_item'       => wp_strip_all_tags(__('Add New List Item', 'pmc-lists')),
				'edit_item'          => wp_strip_all_tags(__('Edit List Item', 'pmc-lists')),
				'new_item'           => wp_strip_all_tags(__('New List Item', 'pmc-lists')),
				'view_item'          => wp_strip_all_tags(__('View List Item', 'pmc-lists')),
				'search_items'       => wp_strip_all_tags(__('Search List Items', 'pmc-lists')),
				'not_found'          => wp_strip_all_tags(__('No Lists found.', 'pmc-lists')),
				'not_found_in_trash' => wp_strip_all_tags(__('No Lists found in Trash.', 'pmc-lists')),
				'all_items'          => wp_strip_all_tags(__('List Items', 'pmc-lists')),
			],
			'public'               => true,
			'supports'             => ['title', 'author', 'comments', 'editor', 'thumbnail'],
			'register_meta_box_cb' => [$this, 'list_item_meta_boxes'],
			'taxonomies'           => [self::LIST_RELATION_TAXONOMY],
			'has_archive'          => false,
			'show_ui'              => true,
			'show_in_menu'         => 'edit.php?post_type=' . self::LIST_POST_TYPE,
			'hierarchical'         => false,
			'rewrite'              => [
				'slug' => 'lists/item/',
			],
		]);

		register_taxonomy(self::LIST_RELATION_TAXONOMY, self::LIST_ITEM_POST_TYPE, [
			'labels'            => [
				'name' => __('List Relation', 'pmc-lists'),
			],
			'public'            => false,
			'rewrite'           => false,
			'show_ui'           => false,
			'show_in_nav_menus' => false,
			'show_admin_column' => false,
		]);

		// Used for pagination bypassing the main query.
		add_rewrite_tag('%list_page%', '([[0-9]+\])');
	}

	/**
	 * Reorder the list sub menu.
	 *
	 * @param array $menu_order The menu items in order.
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
	public function admin_enqueue_scripts()
	{
		if (is_admin() && self::LIST_ITEM_POST_TYPE === get_post_type()) {
			wp_enqueue_script('pmc-lists', PMC_LISTS_URL . '/assets/build/js/list.js', ['jquery'], '1.0', true);
			wp_localize_script('pmc-lists', 'pmcList', [
				'nonce' => wp_create_nonce('pmc-lists'),
			]);
		}
	}

	/**
	 * Add sorting to columns of list items.
	 *
	 * @param bool   $sortable Is sorting enabled.
	 * @param string $post_type The current post type being viewed.
	 *
	 * @return bool
	 */
	public function enable_sorting($sortable, $post_type = '')
	{
		if (self::LIST_ITEM_POST_TYPE === $post_type) {
			return true;
		}

		return $sortable;
	}

	/**
	 * Metabox for List post type.
	 *
	 * @param WP_Post $post The post object of the current post.
	 */
	public function list_meta_boxes($post)
	{
		// List options meta box.
		add_meta_box('pmc-list-options', esc_html__('List Options', 'pmc-lists'), [$this, 'list_options_meta_box'], $post->post_type, 'side', 'high');
	}

	/**
	 * Metabox for List Items post type.
	 *
	 * @param WP_Post $post The post object of the current post.
	 */
	public function list_item_meta_boxes($post)
	{
		// Meta box to select a list.
		add_meta_box('pmc-list-select', esc_html__('Choose List', 'pmc-lists'), [$this, 'list_select_meta_box'], $post->post_type, 'side', 'high');
	}

	/**
	 * List options meta box render.
	 *
	 * @param WP_Post $post The WP Post object.
	 */
	public function list_options_meta_box($post)
	{
		\PMC::render_template(PMC_LISTS_PATH . '/templates/list-options.php', compact('post'), true);
	}

	/**
	 * List select meta box render.
	 *
	 * @param WP_Post $post The WP Post object.
	 */
	public function list_select_meta_box($post)
	{
		$list_id    = '';
		$list_terms = get_the_terms($post->ID, self::LIST_RELATION_TAXONOMY);

		if (is_array($list_terms) && is_a(reset($list_terms), 'WP_Term') && !empty(reset($list_terms)->name)) {
			$list_id = reset($list_terms)->name;
		}

		\PMC::render_template(PMC_LISTS_PATH . '/templates/list-select.php', compact('list_id'), true);
	}

	/**
	 * Actions to be performed when saving a post.
	 *
	 * @param int $post_id The ID of the current post.
	 * @param \WP_Post The current post object.
	 *
	 * @return void|false Returns false if the post is not updated.
	 */
	public function save_post($post_id, $post)
	{

		if (wp_is_post_revision($post_id)) {
			return false;
		}

		// When saving a list, add the ID to the relationship taxonomy to related items to this post.
		if (self::LIST_POST_TYPE === $post->post_type) {
			if (!term_exists($post_id, self::LIST_RELATION_TAXONOMY)) {
				wp_insert_term($post_id, self::LIST_RELATION_TAXONOMY, [
					'slug' => $post_id,
				]);
			}

			// Save options.
			if (!empty($_POST[self::TEMPLATE_OPT_KEY]) && !empty($_POST[self::NUMBERING_OPT_KEY])) { // WPCS: Input var okay. CSRF okay.
				update_post_meta($post_id, self::TEMPLATE_OPT_KEY, sanitize_text_field(wp_unslash($_POST[self::TEMPLATE_OPT_KEY]))); // WPCS: Input var okay. CSRF okay.
				update_post_meta($post_id, self::NUMBERING_OPT_KEY, sanitize_text_field(wp_unslash($_POST[self::NUMBERING_OPT_KEY]))); // WPCS: Input var okay. CSRF okay.
			}
		}

		if (self::LIST_ITEM_POST_TYPE === $post->post_type) {
			// Holder for list term slug to re order
			$reorder_slugs = [];

			// Need to save the old relation term to be able to regenerate menu order for the old list.
			$old_list_term = $this->get_relation_term_for_item($post->ID);

			if (!empty($old_list_term)) {
				$reorder_slugs[$old_list_term->slug] = $old_list_term->slug;
			}

			// Update the post's term from metabox input.
			$list_term_id = \PMC::filter_input(INPUT_POST, 'pmc_list_id', FILTER_SANITIZE_NUMBER_INT);

			// remove action to prevent cyclic call
			remove_action('save_post', [$this, 'save_post']);

			if (!empty($list_term_id)) {
				wp_set_post_terms($post_id, $list_term_id, self::LIST_RELATION_TAXONOMY);

				// Grab the current list term to allow re-ordering
				$list_term = $this->get_relation_term_for_item($post->ID);

				// $list_term can't be empty at this point
				$reorder_slugs[$list_term->slug] = $list_term->slug;

				if (0 === $post->menu_order) {
					wp_update_post([
						'ID'         => $post_id,
						'menu_order' => $list_term->count + 2,
					]);
				}

				if (!empty($reorder_slugs)) {
					foreach ($reorder_slugs as $slug) {
						$this->update_menu_orders($slug);
					}
				}
			} else {
				return false;
			}
		}
	}

	/**
	 * Sets the menu orders of items in a list.
	 *
	 * Missing menu_orders in the sequence can cause lists to display incorrectly. Setting menu orders while editing
	 * list items via the admin prevents having to run expensive pagination-related queries on the front end.
	 *
	 * This can be slow for long lists, especially where an item early in a list is deleted (meaning all subsequent items
	 * require an update).
	 *
	 * @param string $list_term_slug A list relation term slug.
	 */
	public function update_menu_orders($list_term_slug)
	{

		$list_term = get_term_by('slug', strval($list_term_slug), self::LIST_RELATION_TAXONOMY);

		$list_items_query = new \WP_Query([
			'post_status'    => 'publish',
			'post_type'      => self::LIST_ITEM_POST_TYPE,
			'posts_per_page' => $list_term->count,
			'orderby'        => 'menu_order title',
			'order'          => 'asc',
			'tax_query'      => [ // WPCS: slow query ok.
				[
					'taxonomy' => self::LIST_RELATION_TAXONOMY,
					'terms'    => $list_term->term_id,
				],
			],
		]);

		/**
		 * custom-metadata/custom_metadata.php?L#180 => add_action( 'save_post', array( $this, 'save_post_metadata' ) );
		 *
		 * Removing this action to fix overriding metadata_field value, when updating menu order for pmc-list-items
		 *
		 * @since 06-09-2018 Jignesh Nakrani READS-1488
		 */
		if (class_exists('custom_metadata_manager') && method_exists('custom_metadata_manager', 'save_post_metadata')) {

			remove_action('save_post', array(\custom_metadata_manager::instance(), 'save_post_metadata'));
		}

		if (!is_wp_error($list_items_query) && $list_items_query->have_posts()) {
			foreach ((array) $list_items_query->posts as $index => $item) {

				// In most cases (such as when two items are swapped), most items won't need to update.
				if ($index + 1 !== $item->menu_order) {

					wp_update_post([
						'ID'         => $item->ID,
						'menu_order' => $index + 1,
					]);
				}
			}
		}

		/**
		 * Adding action back.
		 *
		 * custom-metadata/custom_metadata.php?L#180 => add_action( 'save_post', array( $this, 'save_post_metadata' ) );
		 *
		 * @since 07-09-2018 Jignesh Nakrani READS-1488
		 */
		if (class_exists('custom_metadata_manager') && method_exists('custom_metadata_manager', 'save_post_metadata')) {

			add_action('save_post', array(\custom_metadata_manager::instance(), 'save_post_metadata'));
		}
	}

	/**
	 * The simple post ordering plugin reorders posts of all statuses and doesn't include the term
	 * in the query.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param object $query A \WP_Query object before the query is run.
	 */
	public function modify_reorder_query($query)
	{

		if (self::LIST_ITEM_POST_TYPE !== $query->get('post_type')) {
			return;
		}

		if (doing_action('wp_ajax_simple_page_ordering')) {
			$item_id = \PMC::filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

			if (!empty($item_id)) {
				$query->set('status', 'publish');

				$term = $this->get_relation_term_for_item($item_id);
				$query->set('tax_query', [
					[
						'taxonomy' => self::LIST_RELATION_TAXONOMY,
						'terms'    => $term->term_id,
					],
				]);
			}
		}
	}

	/**
	 * Ajax call to get lists for the autocomplete.
	 *
	 * @codeCoverageIgnore
	 */
	public function get_lists()
	{

		$nonce = \PMC::filter_input(INPUT_POST, 'nonce', FILTER_SANITIZE_STRING);

		if (empty($nonce) || !wp_verify_nonce($nonce, 'pmc-lists')) {
			wp_send_json_error('Nonce verification failed.');
		}

		$search_query = \PMC::filter_input(INPUT_POST, 'query', FILTER_SANITIZE_STRING);

		if (!empty($search_query) && is_string($search_query)) {

			$lists = new \WP_Query([
				's'              => $search_query,
				'post_status'    => [
					'publish',
					'draft',
				],
				'post_type'      => self::LIST_POST_TYPE,
				'posts_per_page' => 5,
			]);

			if (!empty($lists->posts)) {
				wp_send_json_success(wp_list_pluck($lists->posts, 'post_title', 'ID'));
			} else {
				wp_send_json_success([]);
			}
		}

		wp_die();
	}

	/**
	 * Displays the current list and an edit link when viewing list items that
	 * are filtered based on a list.
	 *
	 * @return void|false Returns false if the list view template is not rendered.
	 */
	public function show_current_list()
	{
		if (self::LIST_ITEM_POST_TYPE !== get_post_type()) {
			return false;
		}

		$list_id = get_query_var(self::LIST_RELATION_TAXONOMY);

		if (empty($list_id)) {
			return false;
		}

		$list_title     = get_the_title($list_id);
		$list_permalink = get_the_permalink($list_id);

		\PMC::render_template(
			PMC_LISTS_PATH . '/templates/list-view.php',
			compact('list_id', 'list_title', 'list_permalink'),
			true
		);
	}

	/**
	 * Fixes a bug in the "Simple Page Ordering" plugin where items sorted by menu_order are descending.
	 *
	 * @param $views An array of view links.
	 * @return $views The filtered array.
	 */
	public function fix_sort_by_order_link($views)
	{

		if (!isset($views['all']) || false === strpos($views['all'], 'orderby=menu_order+title') || false !== strpos($views['all'], 'order=asc')) {
			return $views;
		}

		$views['all'] = str_replace('orderby=menu_order+title', 'orderby=menu_order+title&order=asc', $views['all']);

		return $views;
	}

	/**
	 * Adds the ability to filter list items based on a list. Uses an
	 * autocomplete text input.
	 *
	 * @return void|false False if the list inputs template is not rendered.
	 */
	public function filter_list_items()
	{

		if (self::LIST_ITEM_POST_TYPE !== get_post_type()) {
			return false;
		}

		$list_id = get_query_var(self::LIST_RELATION_TAXONOMY);

		\PMC::render_template(PMC_LISTS_PATH . '/templates/list-inputs.php', [
			'list_id'  => $list_id,
			'taxonomy' => self::LIST_RELATION_TAXONOMY,
		], true);
	}

	/**
	 * Gets the list relation term assigned to a given item.
	 *
	 * @param int $item_id An item ID.
	 * @return null|\WP_Term A term object or null on failure.
	 */
	public function get_relation_term_for_item($item_id)
	{

		$list_terms = get_the_terms($item_id, self::LIST_RELATION_TAXONOMY);

		if (!empty($list_terms)) {
			return reset($list_terms);
		}
	}

	/**
	 * Gets the list post linked by taxonomy to a list item post.
	 *
	 * @param int $item_id A list item ID.
	 * @return \WP_Post|bool The found post or false on failure.
	 */
	public function get_list_for_item($item_id)
	{

		$term = $this->get_relation_term_for_item($item_id);

		// note: need to avoid get_post(0) where it return the default current post object
		if (!empty($term) && is_numeric($term->name)) {
			$post = get_post(intval($term->name));
			if (!empty($post) && self::LIST_POST_TYPE === $post->post_type) {
				return $post;
			}
		}
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

		if (!in_array(self::LIST_POST_TYPE, (array) $post_types, true)) {
			$post_types[] = self::LIST_POST_TYPE;
		}

		return $post_types;
	}

	/**
	 * Add custom list item column in list.
	 *
	 * @param array $default list of deafult columns.
	 *
	 * @return array $default.
	 */
	public function list_item_add_custom_column($default)
	{

		if (empty($default) || !is_array($default)) {
			$default = [];
		}

		$default['list'] = __('List', 'pmc-lists');

		return $default;
	}

	/**
	 * Manage custom list item column for list post type.
	 *
	 * @param string  $column_name  current column name.
	 * @param integer $list_item_id current list id.
	 */
	public function list_item_manage_custom_column($column_name, $list_item_id)
	{

		if (!empty($column_name) && !empty($list_item_id) && 'list' === $column_name) {
			$list_relation = $this->get_relation_term_for_item($list_item_id);

			if (!empty($list_relation->slug)) {
				echo sprintf('<a href="%s">%s</a>', esc_url(add_query_arg(self::LIST_RELATION_TAXONOMY, $list_relation->slug)), get_the_title($list_relation->slug));
			}
		}
	}

	/**
	 * Add custom list item column in list.
	 *
	 * @param array $default list of deafult columns.
	 *
	 * @return array $default.
	 */
	public function list_add_custom_column($default)
	{

		if (empty($default) || !is_array($default)) {
			$default = [];
		}

		$default['list-items'] = __('List Items', 'pmc-lists');

		return $default;
	}

	/**
	 * Manage custom list item column for list post type.
	 *
	 * @param string  $column_name current column name.
	 * @param integer $list_id     current list id.
	 */
	public function list_manage_custom_column($column_name, $list_id)
	{

		if (!empty($column_name) && !empty($list_id) && 'list-items' === $column_name) {
			$list_relation = get_term_by('slug', $list_id, self::LIST_RELATION_TAXONOMY);

			if (!empty($list_relation->count)) {
				echo sprintf(
					'<a href="%s">%s</a>',
					esc_url(
						add_query_arg(
							self::LIST_RELATION_TAXONOMY,
							$list_id,
							admin_url('/edit.php?post_type=' . \PMC\Lists\Lists::LIST_ITEM_POST_TYPE)
						)
					),
					esc_html($list_relation->count)
				);
			}
		}
	}
}
