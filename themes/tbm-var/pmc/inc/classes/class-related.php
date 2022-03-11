<?php

/**
 * Class Related
 *
 * Handlers for the Related functionality.
 *
 * @package pmc-core-v2
 * @since   2017-08-29
 */

namespace PMC\Core\Inc;

/**
 * Class Related
 *
 */
class Related
{

	use \PMC\Global_Functions\Traits\Singleton;

	/**
	 * Initialize the class
	 */
	protected function __construct()
	{
		add_action('init', array($this, 'register_shortcodes'), 11);
	}

	/**
	 * Register Shortcodes
	 *
	 * Loads with priority 11 so that it removes the
	 * pmc-related shortcode after it is registered in pmc-core.
	 *
	 * @since  2017.1.0
	 * @action init, 11
	 */
	public function register_shortcodes()
	{
		remove_shortcode('pmc-related');
		add_shortcode('pmc-related', array($this, 'shortcode_pmc_related'));
	}

	/**
	 * Get Related Items
	 *
	 * Returns an array that contains a "type," (which is
	 * actually a CSS class), as well as an array of posts.
	 *
	 * @since 2017.1.0
	 *
	 * @return array An array of data, including related posts.
	 */
	public function get_related_items()
	{
		$post_obj = get_queried_object();
		$items    = array(
			'type'  => 'post',
			'posts' => [],
		);

		// Else get the primary taxonomy.
		if (empty($items['posts'])) {

			$category = Theme::get_instance()->get_the_primary_term('category', $post_obj->ID);

			// If no categories are assigned to the post, fallback to the "News" category.
			if (empty($category->slug) || !is_string($category->slug)) {
				$category = get_term_by('name', 'news', 'category');
			}

			$items['posts'] = $this->get_related($category->slug);
		}

		return $items;
	}

	/**
	 * Get Related
	 *
	 * Fetches posts based on a specified category.
	 *
	 * @since 2017.1.0
	 * @see   \PMC_Cache
	 *
	 * @param string $category The category to fetch.
	 *
	 * @return array An array of Posts.
	 */
	public function get_related($category)
	{
		if (empty($category) || !is_string($category)) {
			return [];
		}
		$post_id   = get_queried_object_id();
		$cache_key = 'pmc_core_related_' . $category . '_post_' . $post_id;
		$pmc_cache = new \PMC_Cache($cache_key);

		// Expires in 15 minutes.
		$cache_data = $pmc_cache->expires_in(900)
			->updates_with(array($this, 'related_query'), array($category, $post_id))
			->get();

		if (is_array($cache_data) && !is_wp_error($cache_data)) {
			return $cache_data;
		}

		return [];
	}

	/**
	 * Related Query
	 *
	 * Returns an array of recent posts from the requested category,
	 * minus the present post.
	 *
	 * Possibly returns 'none' to prevent the query from running
	 * on every page load.
	 *
	 * @since 2017.1.0
	 *
	 * @param string $category A category term slug.
	 * @param int    $post_id Optional. A \WP_Post ID.
	 * @param int    $count The by number of related posts to return.
	 *
	 * @return array|string Array of posts, else 'none'.
	 */
	public function related_query($category, $post_id = 0, $count = 2)
	{

		// Bail if no category or post id is present.
		if (empty($category) || !is_string($category) || !is_numeric($post_id)) {
			return 'none';
		}

		// Fetch curated related posts from the automated related links plugin if enabled.
		$posts         = [];
		$curated_posts = [];
		$curated_ids   = $this->_automated_related_link_posts($post_id);

		if (!empty($curated_ids)) {
			$query = new \WP_Query([
				'post__in'            => $curated_ids,
				'ignore_sticky_posts' => true,
				'posts_per_page'      => $count,
				'no_found_rows'       => true,
				'suppress_filters'    => false,
			]);

			if (!is_wp_error($query->posts) && !empty($query->posts) && is_array($query->posts)) {
				$curated_posts = $query->posts;
			}
		}

		$curated_posts_count = (!empty($curated_posts) && is_array($curated_posts)) ? count($curated_posts) : 0;

		// If we have more than the amount of related posts we need.
		if ($curated_posts_count >= $count) {
			return array_slice($curated_posts, 0, $count);
		}

		$post__not_in = array_merge([$post_id], $curated_ids);

		$length         = $count - $curated_posts_count;
		$posts_per_page = $length + count($post__not_in);

		/*
		 * Fetch the rest of our related posts that are:
		 * - Not in our list of curated posts
		 * - Not the current post
		 * - The total we need, minus what we already have curated
		 */
		$query = new \WP_Query([
			'category_name'    => $category,
			'posts_per_page'   => $posts_per_page,
			'no_found_rows'    => true,
			'suppress_filters' => false,
		]);

		if (!is_wp_error($query->posts) && !empty($query->posts) && is_array($query->posts)) {
			foreach ($query->posts as $rel_posts) {

				if (in_array($rel_posts->ID, (array) $post__not_in, true)) {
					continue;
				}

				$posts[] = $rel_posts;
				if (count($posts) >= $length) {
					break;
				}
			}
		}

		if (empty($curated_posts) && empty($posts)) {
			return 'none';
		}

		return array_merge($curated_posts, $posts);
	}

	/**
	 * Shortcode PMC Related
	 *
	 * After removing the pmc-core shortcode, this
	 * renders the shortcode using 2017's markup.
	 *
	 * @since   2017.1.0
	 * @see     \PMC_Core_Related_Posts
	 * @globals $post
	 *
	 * @param array $atts Array of shortcode attributes.
	 *
	 * @return string|void
	 */
	public function shortcode_pmc_related($atts = [])
	{

		$attrs = shortcode_atts(array(
			'post1' => '',
			'post2' => '',
			'post3' => '',
		), $atts);

		$post_ids = [];
		$items    = array(
			'type'  => 'post',
			'posts' => [],
		);

		$items = apply_filters('pmc_core_shortcode_pmc_related_args', $items);

		foreach ($attrs as $value) {
			$post_ids[] = url_to_postid($value);
		}

		$post_ids = array_filter($post_ids);
		if (empty($post_ids) || !is_array($post_ids)) {
			return false;
		}

		$query = new \WP_Query(array(
			'posts_per_page'   => max(count($post_ids), 3),
			'post_status'      => 'publish',
			'post__in'         => $post_ids,
			'orderby'          => 'post__in',
			'cache_results'    => false,
			'no_found_rows'    => true,
			'suppress_filters' => false,
		));

		$items['posts'] = $query->posts;
		$items['posts'] = $this->add_tracking($items['posts']);

		if (empty($items['posts']) || !is_array($items['posts'])) {
			return false;
		}

		$path = locate_template('template-parts/article/related.php');

		return \PMC::render_template($path, compact('items'));
	}

	/**
	 *
	 * add_tracking
	 *
	 * Adds a tracking url to the post object for use in the shortcode template.
	 *
	 * @author  Brandon Camenisch <bcamenisch@pmc.com>
	 * @since   2016-10-13
	 *
	 * @version 2016-10-13 Brandon Camenisch <bcamenisch@pmc.com> - feature/PPT-6951:
	 * - Adding method
	 * @version 2018-03-22 brandoncamenisch - feature/WI-498:
	 * - Return $related_post var instead of false it should return what it was
	 * passed unmodified. shortcode_pmc_related was getting expected posts back
	 * as false.
	 *
	 * @param mixed $related_post
	 *
	 * @return object
	 *
	 */
	public function add_tracking($related_post = [])
	{
		global $post;

		if (empty($related_post) || !is_array($related_post) || empty($post->ID)) {
			return $related_post;
		}

		foreach ($related_post as $k => $rel) {

			if (!is_object($rel) || empty($rel->ID)) {
				continue;
			}

			$rel->tracking_link = sprintf(
				'%s#icn=pmc-related&ici=%d_link%d',
				get_the_permalink($rel->ID),
				$post->ID,
				($k + 1)
			);
		}

		return $related_post;
	}

	/**
	 * Fetch posts curated by the Automated Related Links plugin.
	 *
	 * @param  int $post_id Post id.
	 * @return array
	 */
	protected function _automated_related_link_posts($post_id)
	{

		// Return empty if plugin is not available.
		if (!class_exists('PMC_Automated_Related_Links') || empty($post_id)) {
			return [];
		}

		$posts = get_post_meta($post_id, '_pmc_automated_related_links', true);

		// Bail early if empty.
		if (empty($posts['data'])) {
			return [];
		}

		$ids = wp_list_pluck($posts['data'], 'id');

		return array_filter(array_unique((array) $ids));
	}
}
