<?php

/**
 * Lists functionality for PMC properties.
 *
 * @since 2018-06-04
 */

namespace PMC\Lists;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Lists.
 */
class List_Post
{

	use Singleton;

	/**
	 * Whether the data has been set up.
	 *
	 * @var boolean
	 */
	protected $_is_set_up = false;

	/**
	 * The current list.
	 *
	 * @var \WP_Post
	 */
	protected $_list;

	/**
	 * List items for the current page.
	 *
	 * @var array
	 */
	protected $_list_items;

	/**
	 * The number of items in the current list.
	 *
	 * @var int
	 */
	protected $_list_items_count;

	/**
	 * The direction of the current list.
	 *
	 * @var string
	 */
	protected $_order;

	/**
	 * The relational term linking the current list and its items.
	 *
	 * @var \WP_Term
	 */
	protected $_term;

	/**
	 * If a list item was queried, its index within the items on the current page.
	 *
	 * @var int
	 */
	protected $_queried_item_index;

	/**
	 * The number of posts to show per page.
	 *
	 * @var int
	 */
	protected $_posts_per_page;

	/**
	 * The current page within the list.
	 *
	 * @var int
	 */
	protected $_current_page;

	/**
	 * Lists constructor.
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct()
	{

		add_action('template_redirect', [$this, 'set_up']);
		add_action('wp_head', [$this, 'add_head_links']);
		add_action('pmc_adm_custom_keywords', [$this, 'add_adm_custom_keywords']);

		add_filter('pmc_seo_tweaks_googlebot_news_override', [$this, 'maybe_exclude_googlebot_news_tag']);
		add_filter('pmc_adm_google_publisher_slot', [$this, 'filter_pmc_adm_google_publisher_slot']);
		add_filter('pmc_adm_topic_keywords', [$this, 'filter_adm_topic_keywords'], 10, 3);
		add_filter('pmc_page_meta_expose_authors', [$this, 'whitelist_post_type_for_page_meta_authors']);
		add_filter('pmc_page_meta', [$this, 'update_page_meta_for_list_item']);
	}

	/**
	 * Sets up data.
	 */
	public function set_up()
	{
		if (!(is_singular([Lists::LIST_POST_TYPE, Lists::LIST_ITEM_POST_TYPE]))) {
			return;
		}

		$this->set_up_list();

		if (empty($this->get_list()) || !is_a($this->get_list(), '\WP_Post')) {
			return;
		}

		$this->set_up_order();
		$this->set_up_pagination();
		$this->set_up_list_items();
	}

	/**
	 * Gets the current list.
	 */
	public function set_up_list()
	{

		$this->_term             = null;
		$this->_list_items_count = 0;
		$this->_list             = null;

		if (is_singular(Lists::LIST_POST_TYPE)) {
			$this->_term = get_term_by('slug', strval(get_queried_object_id()), Lists::LIST_RELATION_TAXONOMY);

			if (!empty($this->_term)) {
				$term_count = $this->_term->count;
			} else {
				$term_count = 0;
			}

			$this->_list_items_count = $term_count;
			$this->_list             = get_queried_object();
		} elseif (is_singular(Lists::LIST_ITEM_POST_TYPE)) {
			$this->set_up_list_by_linked_list_item();
		}
	}

	/**
	 * Gets the order in which to display the current list.
	 *
	 * @param int|string $list A list slug or ID.
	 * @return string The order: 'asc' or 'desc'.
	 */
	public function set_up_order()
	{
		$this->_order     = 'asc';
		$this->_numbering = 'asc';

		$numbering = get_post_meta($this->_list->ID, Lists::NUMBERING_OPT_KEY, true);

		if ('desc' === $numbering) {
			$this->_order     = 'desc';
			$this->_numbering = 'desc';
		}

		if ('none' === $numbering) {
			$this->_numbering = 'none';
		}
	}

	/**
	 * Gets the current list items.
	 */
	public function set_up_list_items()
	{

		$this->_list_items       = [];
		$this->_list_items_count = 0;

		$items_query_args = [
			'post_status'    => 'publish',
			'post_type'      => Lists::LIST_ITEM_POST_TYPE,
			'posts_per_page' => $this->get_posts_per_page(),
			'paged'          => $this->get_current_page(),
			'orderby'        => 'menu_order title',
			'order'          => 'asc',
			'tax_query'      => [ // WPCS: slow query ok.
				[
					'taxonomy' => Lists::LIST_RELATION_TAXONOMY,
					'field'    => 'slug',
					'terms'    => $this->get_list()->ID,
				],
			],
		];

		$pmc_cache = new \PMC_Cache(wp_json_encode($items_query_args));  // passing the array as cache key

		$query_data = $pmc_cache->expires_in(900)    // 15 minutes
			->updates_with([$this, 'set_up_list_items_uncached'], [$items_query_args])
			->get();

		if (!empty($query_data)) {
			$this->_list_items       = $query_data['list_items'];
			$this->_list_items_count = $query_data['list_items_count'];
		}
	}

	/**
	 * Returns a \WP_Query for list items.
	 *
	 * @param array $items_query_args \WP_Query args.
	 * @return \WP_Query
	 */
	public function set_up_list_items_uncached($items_query_args = [])
	{

		if (empty($items_query_args)) {
			return [];
		}

		$items_query = new \WP_Query($items_query_args);

		if (is_wp_error($items_query) || !$items_query->have_posts()) {
			return [];
		}

		return [
			'list_items'       => $items_query->posts,
			'list_items_count' => $items_query->found_posts,
		];
	}

	/**
	 * Sets up variables related to list pagination.
	 */
	public function set_up_pagination()
	{

		$this->_posts_per_page = $this->set_up_posts_per_page();

		// _queried_item_index value should be between 0 and $this->_posts_per_page - 1
		// Defaulting to -1 indicates index is out of bounds or not initialized
		$this->_queried_item_index = -1;
		$this->_current_page       = 1;

		if (get_query_var('list_page')) {
			$this->_current_page = intval(get_query_var('list_page'));
			return;
		}

		if (get_query_var('post_type') === Lists::LIST_ITEM_POST_TYPE) {

			// List items should have a unique menu order number, which we can use to get the current page.
			// If menu order numbers are not unique, there could be cases where the wrong page is retrieved
			// and the queried post won't appear in the list.
			// Menu order numbers should be ascending even for lists that will display in ascending order.
			// E.g., In a descending "500 greatest albums" list, the 500th greatest should have menu order 1.
			$item     = get_queried_object();
			$position = $item->menu_order;

			// If we have an item at first position we should stick to default values
			// for $this->_queried_item_index and $this->_current_page
			if (intval($position) > 1) {
				// Get the index of the queried item within the current page.
				// If it is the last item on the page, the % expression will return 0
				// since the index is one more than the programmatic order on the page.
				// To account for this, set the queried_item_index to one less than
				// the posts_per_page value, ensuring it is the last item.
				// E.g., when posts_per_page is 50, the _queried_item_index of a list item
				// with menu_order 56 should be 5, and for menu_item of 100, the
				// _queried_item_index should be 49 since it is the last item on the page of 50.
				if (0 === ($position % $this->_posts_per_page)) {
					$this->_queried_item_index = $this->_posts_per_page - 1;
				} else {
					$this->_queried_item_index = ($position % $this->_posts_per_page) - 1;
				}

				// Calculate the current page. E.g., 56 in a 50-item-per-page list is 2, 106 is 3.
				$this->_current_page = intval(($position - 1) / $this->_posts_per_page) + 1;
			}
		}
	}

	/**
	 * Gets the number of posts per page for the current list.
	 */
	public function set_up_posts_per_page($item_count = null)
	{

		/**
		 * Filters the number of list items to show per page.
		 *
		 * @param int
		 */
		$per_page = intval(apply_filters('pmc_lists_per_page', 50));

		// Upper limit.
		if (100 < $per_page) {
			$per_page = 100;
		}

		// Lower limit.
		if (1 > $per_page) {
			$per_page = 1;
		}

		return $per_page;
	}

	/**
	 * Get a list associated with a list item.
	 *
	 * @return \WP_Post The list post.
	 */
	public function set_up_list_by_linked_list_item()
	{

		$list_terms = get_the_terms(get_queried_object_id(), Lists::LIST_RELATION_TAXONOMY);

		if (empty($list_terms) || !is_array($list_terms)) {
			$this->_list = null;
			return;
		}

		$this->_term             = reset($list_terms);
		$this->_list_items_count = $this->_term->count;

		$list_query_args = [
			'p'           => $this->_term->name,
			'post_status' => 'publish',
			'post_type'   => Lists::LIST_POST_TYPE,
		];

		$pmc_cache = new \PMC_Cache(wp_json_encode($list_query_args));  // passing the array as cache key

		$this->_list = $pmc_cache->expires_in(900)    // 15 minutes
			->updates_with([$this, 'get_post_uncached'], [$list_query_args])
			->get();
	}

	/**
	 * Refreshes a post from the cache.
	 *
	 * @param array $args \WP_Query args.
	 * @return \WP_Post|array The found post or an empty array.
	 */
	public function get_post_uncached(array $args = [])
	{

		if (empty($args)) {
			return [];
		}

		$query = new \WP_Query($args);

		if (!is_wp_error($query) && $query->have_posts()) {
			return reset($query->posts);
		}

		return [];
	}


	/**
	 * Provides the WP_Post object for the current list.
	 *
	 * @return \WP_Post A list post.
	 */
	public function get_list()
	{
		return $this->_list;
	}

	/**
	 * Provides list items (an array of WP_Post objects) for the current page.
	 * E.g., when on page two of a list showing 50 items per page, items 51-100 are returned.
	 *
	 * @return array List of posts found.
	 */
	public function get_list_items()
	{
		return $this->_list_items;
	}

	/**
	 * Provides the term associating list items with their list on the current page.
	 *
	 * @return \WP_Term The term object.
	 */
	public function get_term()
	{
		return $this->_term;
	}

	/**
	 * Gets the total number of list items associated with the current list.
	 *
	 * @return int The number of list items.
	 */
	public function get_list_items_count()
	{
		return $this->_list_items_count;
	}

	/**
	 * Gets the order in which the list should be displayed ('asc' or 'desc').
	 *
	 * @return string Either 'asc' or 'desc'.
	 */
	public function get_order()
	{
		return $this->_order;
	}

	/**
	 * Returns the number if items to show per page.
	 *
	 * @return int
	 */
	public function get_posts_per_page()
	{
		return $this->_posts_per_page;
	}

	/**
	 * If a single item was queried, returns its index within the current page.
	 *
	 * @return int
	 */
	public function get_queried_item_index()
	{
		return $this->_queried_item_index;
	}

	/**
	 * Returns the list page number currently being displayed.
	 *
	 * @return int
	 */
	public function get_current_page()
	{
		return $this->_current_page;
	}

	/**
	 * Returns whether the current list has a next page.
	 *
	 * @return boolean
	 */
	public function has_next_page()
	{
		return $this->get_current_page() * $this->get_posts_per_page() < $this->get_list_items_count();
	}

	/**
	 * Returns the next page number.
	 *
	 * @return int
	 */
	public function get_next_page_number()
	{
		return $this->get_current_page() + 1;
	}

	/**
	 * Gets the permalink for the current list.
	 *
	 * @return string A URL.
	 */
	public function get_list_url()
	{
		return get_permalink($this->_list->ID);
	}

	/**
	 * Gets the numbering direction, or "none" for no numbering.
	 *
	 * @return string
	 */
	public function get_numbering()
	{
		return $this->_numbering;
	}

	/**
	 * Returns the list item previous to the passed-in item.
	 *
	 * @param int $item_id An item ID.
	 * @return mixed A list item or false on failure.
	 */
	public function get_previous_item($item_id)
	{

		$list_items = $this->get_list_items();

		$item_index = false;

		foreach ($list_items as $index => $list_item) {

			if ($item_id === $list_item->ID) {
				$item_index = $index;
				break;
			}
		}

		if (false === $item_index) {
			return false;
		}

		$item = $list_items[$item_index];

		// The first item in a list has no previous item.
		if (0 === $item_index && 2 > $item->menu_order) {
			return false;
		}

		// If the item is not the first in the list but is the first on its page, get the previous post by menu order.
		if (0 === $item_index && 1 < $item->menu_order) {
			$query_args = [
				'post_type'      => Lists::LIST_ITEM_POST_TYPE,
				'menu_order'     => $item->menu_order - 1,
				'posts_per_page' => 1,
				'tax_query'      => [ // WPCS: slow query ok.
					[
						'taxonomy' => Lists::LIST_RELATION_TAXONOMY,
						'field'    => 'slug',
						'terms'    => $this->get_list()->ID,
					],
				],
			];

			$pmc_cache = new \PMC_Cache(wp_json_encode($query_args));  // passing the array as cache key

			return $pmc_cache->expires_in(900)    // 15 minutes
				->updates_with([$this, 'get_post_uncached'], [$query_args])
				->get();
		}

		return $list_items[$item_index - 1];
	}

	/**
	 * Get the list item after the passed-in list.
	 *
	 * @param int $item_id An item ID.
	 * @return mixed A list item or false on failure.
	 */
	public function get_next_item($item_id)
	{

		$list_items = $this->get_list_items();
		$item_index = false;

		foreach ($list_items as $index => $list_item) {

			if ($item_id === $list_item->ID) {
				$item_index = $index;
				break;
			}
		}

		if (false === $item_index) {
			return false;
		}

		$item = $list_items[$item_index];

		$posts_per_page = $this->get_posts_per_page();

		// The last item in a list has no next item.
		if ($item->menu_order === $this->get_term()->count) {
			return false;
		}

		// If the item is not the last in the list but is the last on its page, get the next post by menu order.
		if ($item_index === $posts_per_page - 1 && $this->get_term()->count > $post->menu_order) {
			$query_args = [
				'post_type'      => Lists::LIST_ITEM_POST_TYPE,
				'menu_order'     => $item->menu_order + 1,
				'posts_per_page' => 1,
				'tax_query'      => [ // WPCS: slow query ok.
					[
						'taxonomy' => Lists::LIST_RELATION_TAXONOMY,
						'field'    => 'slug',
						'terms'    => $this->get_list()->ID,
					],
				],
			];

			$pmc_cache = new \PMC_Cache(wp_json_encode($query_args));  // passing the array as cache key

			return $pmc_cache->expires_in(900)    // 15 minutes
				->updates_with([$this, 'get_post_uncached'], [$query_args])
				->get();
		}

		return $list_items[$item_index + 1];
	}

	/**
	 * Add link elements to the head to indicate previous and next posts for SEO purposes.
	 */
	public function add_head_links($post = null)
	{

		if (is_singular([Lists::LIST_POST_TYPE, Lists::LIST_ITEM_POST_TYPE])) {
			$prev_url = '';
			$next_url = '';

			if (empty($post)) {
				$post = get_queried_object();
			}

			$list_items = $this->get_list_items();

			if (Lists::LIST_POST_TYPE === $post->post_type) {

				if (!empty($list_items)) {
					$list_items_array_keys = array_keys((array) $list_items);
					$first_item = $list_items[reset($list_items_array_keys)];
				} else {
					$first_item = [];
				}

				if (!empty($first_item)) {
					$next_url = get_permalink($first_item);
				}
			} elseif (Lists::LIST_ITEM_POST_TYPE === $post->post_type) {

				$prev_item = $this->get_previous_item($post->ID);

				if (!empty($prev_item)) {
					$prev_url = get_permalink($prev_item);
				}

				$next_item = $this->get_next_item($post->ID);

				if (!empty($next_item)) {
					$next_url = get_permalink($next_item);
				}
			}

			if (!empty($prev_url) || !empty($next_url)) {
				\PMC::render_template(
					PMC_LISTS_PATH . '/templates/head-links.php',
					compact('prev_url', 'next_url'),
					true
				);
			}
		}
	}

	/**
	 * Passing list category and tags to the dfp ad call on list item pages.
	 *
	 * @param array $keywords
	 *
	 * @return array
	 */
	public function add_adm_custom_keywords($keywords = [])
	{

		if (
			!empty($this->_list)
			&& is_a($this->_list, '\WP_Post')
			&& is_singular([Lists::LIST_ITEM_POST_TYPE])
		) {

			$list_id  = (!empty($this->_list->ID)) ? intval($this->_list->ID) : 0;
			$keywords = (is_array($keywords)) ? $keywords : [];

			if (!empty($list_id)) {

				$keywords_taxonomies = ['category', 'post_tag'];

				foreach ($keywords_taxonomies as $taxonomy) {

					$terms = get_the_terms($list_id, $taxonomy);

					if (empty($terms) || is_wp_error($terms)) {
						continue;
					}

					$keywords = array_merge($keywords, wp_list_pluck(array_values($terms), 'slug'));
				}
			}
		}

		return $keywords;
	}

	/**
	 * A filter to override googlebot_news meta tag for pmc_list posts
	 *
	 * @param $gn_exclude bool
	 *
	 * @since 2018-07-31 Jignesh Nakrani READS-1378
	 *
	 * @return bool
	 */
	public function maybe_exclude_googlebot_news_tag($gn_exclude)
	{

		if (is_singular([Lists::LIST_POST_TYPE, Lists::LIST_ITEM_POST_TYPE])) {

			$gn_exclude = true;
		}

		return $gn_exclude;
	}

	/**
	 * Passing list's category in ad unit path for list item pages
	 *
	 * @param $slot
	 *
	 * @return mixed
	 */
	public function filter_pmc_adm_google_publisher_slot($slot)
	{

		if (
			!empty($slot)
			&& !empty($this->_list)
			&& is_a($this->_list, '\WP_Post')
			&& is_singular([Lists::LIST_ITEM_POST_TYPE])
		) {

			$list_id    = (!empty($this->_list->ID)) ? intval($this->_list->ID) : 0;
			$categories = get_the_category($list_id);

			if (!empty($categories) && !is_wp_error($categories) && is_array($categories)) {
				$category      = array_shift($categories);
				$category_id   = $category->term_id;
				$category_slug = $category->slug;
				$ancestors     = get_ancestors($category_id, 'category');

				if (!empty($ancestors) && is_array($ancestors)) {
					$category_id  = end($ancestors);
					$top_category = get_category($category_id);

					if (!is_wp_error($top_category) & !empty($top_category)) {
						$category_slug = $top_category->slug;
					}
				}

				$slot = str_replace(
					'/list/',
					sprintf('/%s/list/', $category_slug),
					$slot
				);
			}
		}

		return $slot;
	}

	/**
	 * Passing post-list terms for pmc-list-items posts for ADM topic keywords.
	 *
	 * @param array $keywords            Array of keywords.
	 * @param array $keywords_taxonomies Array of taxonomies.
	 * @param array $keywords_post_types Array of post_types.
	 *
	 * @return array List of taxonomies terms from pmc_list for pmc_list_items.
	 */
	public function filter_adm_topic_keywords($keywords, $keywords_taxonomies, $keywords_post_types)
	{

		$keywords = (empty($keywords) || !is_array($keywords)) ? [] : $keywords;

		if (
			!empty($keywords_taxonomies)
			&& is_array($keywords_taxonomies)
			&& !empty($this->_list)
			&& is_a($this->_list, '\WP_Post')
			&& is_singular([Lists::LIST_ITEM_POST_TYPE])
		) {

			$list_id = (!empty($this->_list->ID)) ? intval($this->_list->ID) : 0;

			foreach ($keywords_taxonomies as $taxonomy) {

				$terms = get_the_terms($list_id, $taxonomy);

				if (!empty($terms) && !is_wp_error($terms)) {

					$keywords = array_merge($keywords, wp_list_pluck(array_values($terms), 'slug'));
				}
			}
		}

		return array_filter(array_unique((array) $keywords));
	}

	/**
	 * Function to whitelist pmc_list and pmc_list_item post type for pmc-page-meta authors tag.
	 *
	 * @param array $post_types list of post types
	 *
	 * @return array
	 */
	public function whitelist_post_type_for_page_meta_authors($post_types = [])
	{

		if (is_array($post_types)) {
			$post_types = array_merge($post_types, [Lists::LIST_POST_TYPE, Lists::LIST_ITEM_POST_TYPE]);
		}

		return $post_types;
	}

	/**
	 * Adds missing page meta data for pmc_List_item pages from its parent list.
	 * like categories, tags, vertical, primary-category etc.
	 *
	 * @param array $meta page meta data
	 *
	 * @return array Updated PMC page meta data for pmc-list-item
	 */
	public function update_page_meta_for_list_item($meta = [])
	{

		if (is_singular(Lists::LIST_ITEM_POST_TYPE)) {

			// check if parent list is set up if not call it here
			if (empty($this->_list->ID)) {
				$this->set_up_list_by_linked_list_item();
			}

			// if sill not set up bail out
			if (!empty($this->_list->ID)) {

				// will process taxonomies registered for pmc_list
				$object_taxonomies = get_object_taxonomies(Lists::LIST_POST_TYPE);

				// tags
				if (in_array('post_tag', (array) $object_taxonomies, true)) {

					$tags = get_the_tags($this->_list->ID);

					if (is_array($tags)) {

						$tags = wp_list_pluck(array_values($tags), 'name');

						if (is_array($tags)) {

							$tags = array_values($tags);
						}

						$meta['tag'] = $tags;
					}
				}

				// list of taxonomies that need `primary` value.
				$other_taxonomies = ['vertical', 'category'];

				// Other taxonomies
				foreach ($other_taxonomies as $taxonomy) {

					// will process taxonomies registered for pmc_list
					if (in_array($taxonomy, (array) $object_taxonomies, true)) {

						$terms = get_the_terms($this->_list->ID, $taxonomy);

						if (is_array($terms)) {

							$terms_names                    = wp_list_pluck(array_values($terms), 'name');
							$meta[$taxonomy]              = $terms_names;
							$meta['primary-' . $taxonomy] = reset($terms_names); // set default first as primary as of now

						}

						// get primary taxonomy value from PMC_Primary_Taxonomy if class exist
						if (class_exists('PMC_Primary_Taxonomy')) {

							$primary_tax = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy($this->_list->ID, $taxonomy);

							if (is_object($primary_tax) && !empty($primary_tax->name)) {
								$meta['primary-' . $taxonomy] = $primary_tax->name;
							}
						}
					}
				}
			}
		}

		return $meta;
	}
}
