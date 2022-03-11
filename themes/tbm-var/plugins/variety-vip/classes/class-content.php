<?php
// phpcs:ignoreFile
// This file was failing pipeline and not part of the feature deployed.
// @codeCoverageIgnoreStart

/**
 * Content
 *
 * Responsible for creating post types and taxonomies.
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Variety_VIP;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Content
 */
class Content
{

	use Singleton;

	/**
	 * Post type name for VIP content.
	 */
	const VIP_POST_TYPE = 'variety_vip_post';

	/**
	 * Default posts per page.
	 */
	const VIP_POSTS_PER_PAGE_DEFAULT = 8;

	/**
	 * Posts per page when on the homepage.
	 */
	const VIP_POSTS_PER_PAGE_ON_HOMEPAGE = 4;

	/**
	 * Post type name for VIP videos.
	 */
	const VIP_VIDEO_POST_TYPE = 'variety_vip_video';

	/**
	 * Post type name for VIP videos.
	 */
	const VIP_REPORT_POST_TYPE = 'variety_vip_report';

	/**
	 * Taxonomy name for VIP categories.
	 */
	const VIP_CATEGORY_TAXONOMY = 'variety_vip_category';

	/**
	 * Taxonomy name for VIP tags.
	 */
	const VIP_TAG_TAXONOMY = 'variety_vip_tag';

	/**
	 * Taxonomy name for VIP Video playlist.
	 */
	const VIP_PLAYLIST_TAXONOMY = 'variety_vip_playlist';

	/**
	 * Class constructor.
	 */
	protected function __construct()
	{

		$this->_setup_hooks();
	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks()
	{

		add_action('init', [$this, 'register_post_types'], 21);
		add_action('init', [$this, 'register_taxonomies'], 22);
		add_action('pmc_primary_taxonomy_settings', [$this, 'show_primary_taxonomy_meta']);
		add_filter('pmclinkcontent_post_types', [$this, 'set_curation_post_type']);
		add_filter('pmc-linkcontent-sf-types', [$this, 'set_curation_taxonomies']);
		add_filter(
			'pmc_field_override_post_types',
			function ($post_types) {
				return array_merge($post_types, [Content::VIP_POST_TYPE, Content::VIP_VIDEO_POST_TYPE, Special_Reports::POST_TYPE]);
			}
		);

		$post_types = [self::VIP_POST_TYPE, self::VIP_VIDEO_POST_TYPE, Special_Reports::POST_TYPE];

		foreach ($post_types as $post_type) {
			add_action("fm_post_{$post_type}", [$this, 'add_meta']);
		}

		add_filter('pre_post_link', [$this, 'filter_permalinks'], 10, 3);
		add_filter('post_type_link', [$this, 'post_type_link'], 10, 3);

		add_action('pre_get_posts', [$this, 'vip_archive_query']);
		add_filter('found_posts', [$this, 'vip_archive_found_posts'], 10, 2);
		add_action('pre_get_posts', [$this, 'add_additional_post_types']);
	}

	/**
	 * Create post types for VIP.
	 */
	public function register_post_types()
	{

		$labels = [
			'name'                  => _x('VIP Posts', 'Post type general name', 'pmc-variety'),
			'singular_name'         => _x('VIP Post', 'Post type singular name', 'pmc-variety'),
			'menu_name'             => _x('VIP Posts', 'Admin Menu text', 'pmc-variety'),
			'name_admin_bar'        => _x('VIP Post', 'Add New on Toolbar', 'pmc-variety'),
			'add_new'               => __('Add New', 'pmc-variety'),
			'add_new_item'          => __('Add New VIP Post', 'pmc-variety'),
			'new_item'              => __('New VIP Post', 'pmc-variety'),
			'edit_item'             => __('Edit VIP Post', 'pmc-variety'),
			'view_item'             => __('View VIP Post', 'pmc-variety'),
			'all_items'             => __('All VIP Posts', 'pmc-variety'),
			'search_items'          => __('Search VIP Posts', 'pmc-variety'),
			'parent_item_colon'     => __('Parent VIP Posts:', 'pmc-variety'),
			'not_found'             => __('No VIP Posts found.', 'pmc-variety'),
			'not_found_in_trash'    => __('No VIP Posts found in Trash.', 'pmc-variety'),
			'featured_image'        => _x('VIP Post Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'pmc-variety'),
			'archives'              => _x('VIP Post archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'pmc-variety'),
			'insert_into_item'      => _x('Insert into VIP Post', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'pmc-variety'),
			'uploaded_to_this_item' => _x('Uploaded to this VIP Post', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'pmc-variety'),
			'filter_items_list'     => _x('Filter VIP Posts list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'pmc-variety'),
			'items_list_navigation' => _x('VIP Posts list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'pmc-variety'),
			'items_list'            => _x('VIP Posts list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'pmc-variety'),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => ['slug' => 'vip'],
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'taxonomies'         => ['post_tag'],
			'supports'           => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'],
		];

		register_post_type(self::VIP_POST_TYPE, $args);

		$labels = [
			'name'                  => _x('VIP Videos', 'Video type general name', 'pmc-variety'),
			'singular_name'         => _x('VIP Video', 'Video type singular name', 'pmc-variety'),
			'menu_name'             => _x('VIP Videos', 'Admin Menu text', 'pmc-variety'),
			'name_admin_bar'        => _x('VIP Video', 'Add New on Toolbar', 'pmc-variety'),
			'add_new'               => __('Add New', 'pmc-variety'),
			'add_new_item'          => __('Add New VIP Video', 'pmc-variety'),
			'new_item'              => __('New VIP Video', 'pmc-variety'),
			'edit_item'             => __('Edit VIP Video', 'pmc-variety'),
			'view_item'             => __('View VIP Video', 'pmc-variety'),
			'all_items'             => __('All VIP Videos', 'pmc-variety'),
			'search_items'          => __('Search VIP Videos', 'pmc-variety'),
			'parent_item_colon'     => __('Parent VIP Videos:', 'pmc-variety'),
			'not_found'             => __('No VIP Videos found.', 'pmc-variety'),
			'not_found_in_trash'    => __('No VIP Videos found in Trash.', 'pmc-variety'),
			'featured_image'        => _x('VIP Video Cover Image', 'Overrides the “Featured Image” phrase for this video type. Added in 4.3', 'pmc-variety'),
			'archives'              => _x('VIP Video archives', 'The video type archive label used in nav menus. Default “Video Archives”. Added in 4.4', 'pmc-variety'),
			'insert_into_item'      => _x('Insert into VIP Video', 'Overrides the “Insert into video”/”Insert into page” phrase (used when inserting media into a video). Added in 4.4', 'pmc-variety'),
			'uploaded_to_this_item' => _x('Uploaded to this VIP Video', 'Overrides the “Uploaded to this video”/”Uploaded to this page” phrase (used when viewing media attached to a video). Added in 4.4', 'pmc-variety'),
			'filter_items_list'     => _x('Filter VIP Videos list', 'Screen reader text for the filter links heading on the video type listing screen. Default “Filter videos list”/”Filter pages list”. Added in 4.4', 'pmc-variety'),
			'items_list_navigation' => _x('VIP Videos list navigation', 'Screen reader text for the pagination heading on the video type listing screen. Default “Videos list navigation”/”Pages list navigation”. Added in 4.4', 'pmc-variety'),
			'items_list'            => _x('VIP Videos list', 'Screen reader text for the items list heading on the video type listing screen. Default “Videos list”/”Pages list”. Added in 4.4', 'pmc-variety'),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => ['slug' => 'vip-video'],
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'taxonomies'         => ['post_tag'],
			'supports'           => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'],
		];

		register_post_type(self::VIP_VIDEO_POST_TYPE, $args);
	}

	/**
	 * Register taxonomies.
	 * Test TODO
	 */
	public function register_taxonomies()
	{

		$labels = [
			'name'              => _x('VIP Categories', 'taxonomy general name', 'pmc-variety'),
			'singular_name'     => _x('VIP Category', 'taxonomy singular name', 'pmc-variety'),
			'search_items'      => __('Search VIP Categories', 'pmc-variety'),
			'all_items'         => __('All VIP Categories', 'pmc-variety'),
			'parent_item'       => __('Parent VIP Category', 'pmc-variety'),
			'parent_item_colon' => __('Parent VIP Category:', 'pmc-variety'),
			'edit_item'         => __('Edit VIP Category', 'pmc-variety'),
			'update_item'       => __('Update VIP Category', 'pmc-variety'),
			'add_new_item'      => __('Add New VIP Category', 'pmc-variety'),
			'new_item_name'     => __('New VIP Category Name', 'pmc-variety'),
			'menu_name'         => __('VIP Category', 'pmc-variety'),
		];

		$args = [
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => ['slug' => 'vip-category'],
		];

		register_taxonomy(self::VIP_CATEGORY_TAXONOMY, [self::VIP_POST_TYPE, self::VIP_VIDEO_POST_TYPE], $args);

		$labels = [
			'name'                       => _x('VIP Tags', 'taxonomy general name', 'pmc-variety'),
			'singular_name'              => _x('VIP Tag', 'taxonomy singular name', 'pmc-variety'),
			'search_items'               => __('Search VIP Tags', 'pmc-variety'),
			'popular_items'              => __('Popular VIP Tags', 'pmc-variety'),
			'all_items'                  => __('All VIP Tags', 'pmc-variety'),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __('Edit VIP Tag', 'pmc-variety'),
			'update_item'                => __('Update VIP Tag', 'pmc-variety'),
			'add_new_item'               => __('Add New VIP Tag', 'pmc-variety'),
			'new_item_name'              => __('New VIP Tag Name', 'pmc-variety'),
			'separate_items_with_commas' => __('Separate VIP tags with commas', 'pmc-variety'),
			'add_or_remove_items'        => __('Add or remove VIP tags', 'pmc-variety'),
			'choose_from_most_used'      => __('Choose from the most used VIP tags', 'pmc-variety'),
			'not_found'                  => __('No VIP tags found.', 'pmc-variety'),
			'menu_name'                  => __('VIP Tags', 'pmc-variety'),
		];

		$args = [
			'hierarchical'          => true,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => ['slug' => 'vip-tag'],
		];

		register_taxonomy(self::VIP_TAG_TAXONOMY, [self::VIP_POST_TYPE, self::VIP_VIDEO_POST_TYPE], $args);

		$labels = [
			'name'                       => _x('VIP Playlists', 'taxonomy general name', 'pmc-variety'),
			'singular_name'              => _x('VIP Playlist', 'taxonomy singular name', 'pmc-variety'),
			'search_items'               => __('Search VIP Playlists', 'pmc-variety'),
			'popular_items'              => __('Popular VIP Playlists', 'pmc-variety'),
			'all_items'                  => __('All VIP Playlists', 'pmc-variety'),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __('Edit VIP Playlist', 'pmc-variety'),
			'update_item'                => __('Update VIP Playlist', 'pmc-variety'),
			'add_new_item'               => __('Add New VIP Playlist', 'pmc-variety'),
			'new_item_name'              => __('New VIP Playlist Name', 'pmc-variety'),
			'separate_items_with_commas' => __('Separate VIP Playlists with commas', 'pmc-variety'),
			'add_or_remove_items'        => __('Add or remove VIP Playlists', 'pmc-variety'),
			'choose_from_most_used'      => __('Choose from the most used VIP Playlists', 'pmc-variety'),
			'not_found'                  => __('No VIP Playlists found.', 'pmc-variety'),
			'menu_name'                  => __('VIP Playlists', 'pmc-variety'),
		];

		$args = [
			'hierarchical'          => true,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => ['slug' => 'vip-playlist'],
		];

		register_taxonomy(self::VIP_PLAYLIST_TAXONOMY, [self::VIP_VIDEO_POST_TYPE], $args);
	}

	/**
	 * Enable primary categories for VIP Categories.
	 *
	 * @param array $args The taxonomy meta arguements.
	 *
	 * @return array
	 */
	public function show_primary_taxonomy_meta($args)
	{

		if (self::is_vip_admin_page()) {
			$args['post_type'] = [self::VIP_POST_TYPE, self::VIP_VIDEO_POST_TYPE];
			$args['taxonomy']  = [
				self::VIP_TAG_TAXONOMY      => __('VIP Tag', 'pmc-variety'),
				self::VIP_CATEGORY_TAXONOMY => __('VIP Category', 'pmc-variety'),
				self::VIP_PLAYLIST_TAXONOMY => __('VIP Playlist', 'pmc-variety'),
			];
		}

		return $args;
	}

	/**
	 * Add post types to carousel curation.
	 *
	 * Test TODO
	 *
	 * @param array $post_types List of post types.
	 *
	 * @return array
	 */
	public function set_curation_post_type($post_types)
	{

		$post_types[] = Content::VIP_POST_TYPE;
		$post_types[] = Content::VIP_VIDEO_POST_TYPE;
		$post_types[] = Special_Reports::POST_TYPE;
		if (class_exists('\PMC\Lists\Lists')) {
			$post_types[] = \PMC\Lists\Lists::LIST_POST_TYPE;
		}
		$post_types[] = 'pmc-gallery';
		$post_types[] = Special_Reports::POST_TYPE;

		return $post_types;
	}

	/**
	 * Add taxonomies to carousel curation.
	 *
	 * @param array $taxonomies List of taxonomies.
	 *
	 * @return array
	 */
	public function set_curation_taxonomies($taxonomies)
	{
		$taxonomies[] = Content::VIP_PLAYLIST_TAXONOMY;
		return $taxonomies;
	}

	/**
	 * Get breadcrumb for VIP post.
	 *
	 * @return array
	 */
	public function get_breadcrumb()
	{

		$breadcrumb = [];

		$post_id  = get_queried_object_id();
		$category = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy($post_id, Content::VIP_CATEGORY_TAXONOMY);

		if (!empty($category->name)) {
			$breadcrumb[] = $category;
		}

		$tag = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy($post_id, Content::VIP_TAG_TAXONOMY);

		if (!empty($tag)) {
			$breadcrumb[] = $tag;
		}

		return $breadcrumb;
	}

	/**
	 * Is the current page a VIP page?
	 *
	 * @return bool
	 */
	public static function is_vip_page()
	{

		return !is_admin() &&
			(is_singular([Content::VIP_POST_TYPE, Content::VIP_VIDEO_POST_TYPE, Special_Reports::POST_TYPE]) ||
				is_tax([Content::VIP_CATEGORY_TAXONOMY, Content::VIP_TAG_TAXONOMY, Content::VIP_PLAYLIST_TAXONOMY]) ||
				is_post_type_archive([Content::VIP_POST_TYPE, Content::VIP_VIDEO_POST_TYPE, Special_Reports::POST_TYPE]) ||
				is_page_template('page-vip.php') ||
				is_page_template('page-vip-page.php') ||
				is_page_template('page-templates/marketing-landing.php') ||
				is_page_template('page-templates/vip-corporate-subscriptions.php')
			);
	}

	/**
	 * Is the current page a VIP admin page?
	 *
	 * @return bool
	 */
	public static function is_vip_admin_page()
	{
		global $pagenow, $typenow;

		$screen = get_current_screen();

		if ('edit' === $screen->base || 'add' === $screen->base || 'post.php' === $pagenow || 'post-new.php' === $pagenow) { // phpcs:ignore CSRF okay. Input var okay.
			return Content::VIP_POST_TYPE === $screen->post_type || Content::VIP_VIDEO_POST_TYPE === $screen->post_type || Content::VIP_POST_TYPE === $typenow || Content::VIP_VIDEO_POST_TYPE === $typenow;
		}

		return false;
	}

	/**
	 * Return latest VIP posts.
	 *
	 * @param int $posts_per_page Number of posts to return.
	 *
	 * @return \WP_Query
	 */
	public static function get_latest_posts($posts_per_page = 5)
	{
		return new \WP_Query(
			[
				'post_type'      => self::VIP_POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => $posts_per_page,
			]
		);
	}

	/**
	 * Add Fieldmanager Meta Box for if article is free or not.
	 * @throws \FM_Developer_Exception
	 */
	public function add_meta()
	{

		$post_types = [self::VIP_POST_TYPE, self::VIP_VIDEO_POST_TYPE, Special_Reports::POST_TYPE];

		$this->add_is_free_meta($post_types);
		$this->add_takeaway_fields($post_types);
	}

	/**
	 * Add Fieldmanager Meta Box for if article is free or not.
	 * @param array $post_types
	 *
	 * @throws \FM_Developer_Exception
	 */
	public function add_is_free_meta($post_types)
	{

		$fm = new \Fieldmanager_Group(
			[
				'name'           => 'variety_subscription_type',
				'serialize_data' => false,
				'add_to_prefix'  => false,
				'children'       => [
					'vip_free' => new \Fieldmanager_Checkbox(
						[
							'label'           => __('Article is free', 'pmc-variety'),
							'default_value'   => 'N',
							'checked_value'   => 'Y',
							'unchecked_value' => 'N',
						]
					),
				],
			]
		);

		$fm->add_meta_box(__('Article Type', 'pmc-variety'), $post_types);
	}

	/**
	 * Add Fieldmanager Meta Box for key takeaways.
	 * @param array $post_types
	 *
	 * @throws \FM_Developer_Exception
	 */
	public function add_takeaway_fields($post_types)
	{

		$fm = new \Fieldmanager_Group(
			[
				'name'     => 'variety_vip_takeaways',
				'children' => [
					'takeaway_list' => new \Fieldmanager_Group(
						[
							'label'          => esc_html__('Takeaway', 'pmc-variety'),
							'limit'          => 10,
							'add_more_label' => esc_html__('Add Another Takeaway', 'pmc-variety'),
							'sortable'       => true,
							'collapsible'    => true,
							'children'       => [
								'takeaway_text' => new \Fieldmanager_TextArea(esc_html__('Takeaway Copy', 'pmc-variety')),
							],
						]
					),
				],
			]
		);

		$fm->add_meta_box(__('Key Takeaways', 'pmc-variety'), $post_types);
	}

	/**
	 * Return if vip post is free or not.
	 *
	 * @param $post_id
	 *
	 * @return bool
	 */
	public function is_article_free($post_id)
	{

		if (empty($post_id)) {
			return false;
		}

		$free = get_post_meta($post_id, 'vip_free', true);

		if ('Y' === $free) {
			return true;
		}

		return false;
	}

	/**
	 * Appends the ID of the post type at the end of the permalink.
	 *
	 * Test TODO
	 *
	 * @param string  $link The post type permalink.
	 * @param integer $post_id   The ID of the post.
	 *
	 * @return mixed
	 */
	public function post_type_link($link, $post_id)
	{

		$post = get_post($post_id);

		if ('auto-draft' === get_post_status($post)) {
			return $link;
		}

		if (!empty($post->post_type) && in_array(
			$post->post_type,
			[self::VIP_POST_TYPE, Special_Reports::POST_TYPE],
			true
		)) {
			$link = untrailingslashit($link);
			$link = $link . '-%post_id%/';
		}

		return $this->filter_permalinks($link, $post_id);
	}

	/**
	 * Filter post and post type permalinks to include post_id at the end
	 *
	 * Test TODO
	 *
	 * @param string  $link      The unfiltered permalink.
	 * @param integer $post_id   The ID of the post.
	 * @param bool    $leavename Whether to keep the post name.
	 *
	 * @return mixed
	 */
	public function filter_permalinks($link, $post_id, $leavename = false)
	{

		$post = get_post($post_id);

		if (empty($post)) {
			return $link;
		}

		if (!in_array($post->post_type, [self::VIP_POST_TYPE, Special_Reports::POST_TYPE], true)) {
			return $link;
		}

		if (
			'auto-draft' === get_post_status($post) ||
			false === strpos($link, '%')
		) {
			return $link;
		}

		if (preg_match('/\\/\\?p=\\d+\$/', $link)) {
			return $link;
		}

		$link = str_replace('-' . $post->ID, '', $link);

		$link = str_replace('%post_id%', $post->ID, $link);

		if (true !== $leavename) {
			$link = str_replace('%postname%', $post->post_name, $link);
		}

		return $link;
	}

	/**
	 * Modify the VIP archive query to show different post_per_page depending on if is_paged() true or false.
	 *
	 * @param \WP_Query $query The WP Query object.
	 *
	 * @return \WP_Query
	 */
	public function vip_archive_query($query)
	{
		if (!is_admin() && $query->is_main_query() && is_post_type_archive(self::VIP_POST_TYPE)) {
			if (is_paged()) {
				$query->set('posts_per_page', self::VIP_POSTS_PER_PAGE_DEFAULT);
				// Adjust offset so we don't lose posts!
				$offset = self::VIP_POSTS_PER_PAGE_ON_HOMEPAGE + (($query->query_vars['paged'] - 2) * self::VIP_POSTS_PER_PAGE_DEFAULT);
				$query->set('offset', $offset);
			} else {
				$query->set('posts_per_page', self::VIP_POSTS_PER_PAGE_ON_HOMEPAGE);
			}
		}
		return $query;
	}

	/**
	 * Method to add variety_vip_post post types to variety_vip_tag, author, and tag archive pages
	 *
	 * @param \WP_Query $query The WP Query object.
	 *
	 * @return \WP_Query
	 */
	public function add_additional_post_types($query)
	{
		if (is_admin() || !$query->is_main_query()) {
			return $query;
		}

		if ($query->is_tax(self::VIP_TAG_TAXONOMY)) {
			$query->set('post_type', [self::VIP_POST_TYPE, self::VIP_VIDEO_POST_TYPE, self::VIP_REPORT_POST_TYPE]);
		}

		if ($query->is_author()) {
			$query->set('post_type', [self::VIP_POST_TYPE]);
		}

		if ($query->is_tag()) {
			$query->set('post_type', [self::VIP_POST_TYPE]);
		}

		return $query;
	}

	/**
	 * Adjust number of "found_posts" to account for the offset modification done above
	 *
	 * @param int $found_posts The number of posts found.
	 * @param \WP_Query $query The WP Query instance (passed by reference).
	 *
	 * @return int $found_posts
	 *
	 */

	public function vip_archive_found_posts($found_posts, $query)
	{

		if (!is_admin() && $query->is_main_query() && is_post_type_archive(self::VIP_POST_TYPE)) {
			if (is_paged()) {
				return ($found_posts - (self::VIP_POSTS_PER_PAGE_ON_HOMEPAGE - self::VIP_POSTS_PER_PAGE_DEFAULT));
			}
		}

		return $found_posts;
	}
}

// This file was failing pipeline and not part of the feature deployed.
// @codeCoverageIgnoreEnd
