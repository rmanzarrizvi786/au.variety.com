<?php

/**
 * Class Related
 *
 * Handlers for the Related functionality.
 *
 * @package pmc-variety-2017
 * @since   2017.1.0
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;
use Variety\Plugins\Variety_VIP\Content;
use Variety\Plugins\Variety_VIP\Special_Reports;

/**
 * Class Related
 *
 * @since 2017.1.0
 * @see   \PMC\Global_Functions\Traits\Singleton
 */
class Related
{

	use Singleton;

	const CACHE_DURATION = 900;    // Expires in 15 minutes.

	/**
	 * Class constructor.
	 * @codeCoverageIgnore
	 */
	protected function __construct()
	{
		$this->_setup_hooks();
	}

	protected function _setup_hooks()
	{
		add_action('init', array($this, 'register_shortcodes'), 11);

		/**
		 * Disable filler posts in related articles by returning false.
		 *
		 * @see variety_disable_filler_in_related_articles() in pmc-variety-2017/functions.php
		 */
		add_filter('pmc_related_articles_add_filler_posts', '__return_false');
		add_filter('pmc_global_override_related_box', [$this, 'filter_pmc_global_override_related_box']);
		add_filter('pmc_global_amp_override_related_box', [$this, 'filter_pmc_global_amp_override_related_box']);
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
	 * @return array An array of data, including related posts.
	 * @version 2017-10-03 CDWE-698 Get related articles based on tags
	 *
	 * @since   2017.1.0
	 *
	 */
	public function get_related_items()
	{
		$post_obj = get_queried_object();
		$items    = array(
			'type'  => 'post',
			'posts' => array(),
		);

		$items = apply_filters('variety_pre_get_related_items', $items);

		// If is a Dirt article.
		if (empty($items['posts']) && variety_is_dirt($post_obj)) {
			$items['type']  = 'dirt';
			$items['posts'] = $this->get_related('real-estalker');
		}

		// If is a Review article.
		if (empty($items['posts']) && variety_is_review($post_obj)) {
			// This is singular on purpose, so it can be passed as a CSS class.
			$items['type']  = 'review';
			$items['posts'] = $this->get_related('reviews');
		}

		// Else get the related articles based on tags.
		if (empty($items['posts'])) {

			$post_needed = 2;

			$video_post_args = [
				'suppress_filters' => false,
				'posts_per_page'   => 1,
				'post_type'        => 'variety_top_video',
				'date_query'       => array(
					array(
						'column' => 'post_date_gmt',
						'after'  => '1 year ago',
					),
				),
			];

			// use get_the_terms & wp_list_pluck to avoid uncache function call
			$terms = get_the_terms($post_obj->ID, 'post_tag');

			if (!empty($terms) && !is_wp_error($terms)) {
				$video_post_args['tag__in'] = wp_list_pluck(array_values($terms), 'term_id');
			} else {

				$categories = get_the_terms($post_obj->ID, 'category');

				if ((!empty($categories) && !is_wp_error($categories))) {
					$video_post_args['category__in'] = wp_list_pluck(array_values($categories), 'term_id');
				}
			}

			$cache_key = 'vy-get-video-posts-' . serialize($video_post_args);
			$pmc_cache = new \PMC_Cache($cache_key);

			$video_posts = $pmc_cache->expires_in(self::CACHE_DURATION)
				->updates_with('get_posts', [$video_post_args])
				->get();

			if (!empty($video_posts) && is_array($video_posts)) {
				$items['posts'] = $video_posts;
				$post_needed    = 1;
			}

			$articles = pmc_related_articles(
				$post_obj->ID,
				[
					'disable_post_mapping' => true,
					'posts_per_page'       => absint($post_needed),
				]
			);

			if (!empty($articles) && is_array($articles)) {
				$items['posts'] = array_merge($items['posts'], $articles);
			}
		}

		return $items;
	}

	/**
	 * Get Related
	 *
	 * Fetches posts based on a specified category.
	 *
	 * @param \WP_Term/string $term The term to use or category slug.
	 *
	 * @return array An array of Posts.
	 * @since 2017.1.0
	 * @see   \PMC_Cache
	 *
	 * @codeCoverageIgnore
	 */
	public function get_related($term)
	{
		if (is_string($term)) {
			$term = get_term_by('name', $term, 'category');
		}

		if (empty($term->term_id)) {
			return [];
		}

		$post_id   = get_queried_object_id();
		$cache_key = 'variety_related_' . $term->term_id . '_posts';
		$pmc_cache = new \PMC_Cache($cache_key);

		$cache_data = $pmc_cache->expires_in(self::CACHE_DURATION)
			->updates_with([$this, 'related_query'], [$term])
			->get();

		if (is_array($cache_data) && !is_wp_error($cache_data)) {
			// let's remove the current post from the array if it exists.
			foreach ($cache_data as $key => $related_post) {

				if ($related_post->ID === $post_id) {
					unset($cache_data[$key]);
				}
			}

			return array_slice($cache_data, 0, 2);
		}

		return array();
	}

	/**
	 * Related Query
	 *
	 * Returns an array of recent posts from the requested term,
	 * minus the present post.
	 *
	 * Possibly returns 'none' to prevent the query from running
	 * on every page load.
	 *
	 * NOTE: This is uncached function and is not to be used directly on front-end.
	 *
	 * @param \WP_Term $term A term to use for related posts.
	 *
	 * @return array|string Array of posts, else 'none'.
	 * @since 2017.1.0
	 *
	 * @codeCoverageIgnore
	 */
	public function related_query($term)
	{

		if (empty($term->term_id)) {
			return 'none';
		}

		$query = new \WP_Query(
			[
				'post_type'        => apply_filters('variety_related_post_types', ['post']),
				'tax_query'        => [
					[
						'taxonomy'         => $term->taxonomy,
						'terms'            => $term->slug,
						'field'            => 'slug',
						'include_children' => false,
					],
				],
				'post_status'      => 'publish',
				'no_found_rows'    => true,
				'posts_per_page'   => 3,
				'suppress_filters' => false,
			]
		);

		if (empty($query->posts) || !is_array($query->posts) || is_wp_error($query->posts)) {
			return 'none';
		}

		return $query->posts;
	}

	/**
	 * @codeCoverageIgnore
	 *
	 * Shortcode PMC Related
	 *
	 * After removing the pmc-core shortcode, this
	 * renders the shortcode using 2017's markup.
	 *
	 * @param array $atts Array of shortcode attributes.
	 *
	 * @return string
	 * @since   2017.1.0
	 * @see     \PMC_Core_Related_Posts
	 * @globals $post
	 *
	 */
	public function shortcode_pmc_related($atts = array())
	{
		global $post;
		$attrs = shortcode_atts(
			array(
				'post1' => '',
				'post2' => '',
				'post3' => '',
			),
			$atts
		);

		$post_ids = array();
		$items    = array(
			'type'  => 'post',
			'posts' => array(),
		);

		foreach ($attrs as $value) {
			$post_ids[] = url_to_postid($value);
		}

		$post_ids = array_filter($post_ids);

		if (empty($post_ids)) {
			return;
		}

		$query = new \WP_Query(
			array(
				'posts_per_page'   => max(count($post_ids), 3),
				'post_status'      => 'publish',
				'post__in'         => $post_ids,
				'orderby'          => 'post__in',
				'cache_results'    => false,
				'no_found_rows'    => true,
				'suppress_filters' => false,
			)
		);

		// If is a Dirt article.
		if (variety_is_dirt($post)) {
			$items['type'] = 'dirt';
		} elseif (variety_is_review($post)) {
			// This is singular on purpose, so it can be passed as a CSS class.
			$items['type'] = 'review';
		}

		$items['posts'] = $query->posts;
		$items['posts'] = \PMC\Core\Inc::get_instance()->add_tracking($items['posts']);

		if (!empty($items['posts']) && is_array($items['posts'])) {
			return \PMC::render_template(CHILD_THEME_PATH . '/template-parts/article/related.php', compact('items'));
		}
	}

	/**
	 * Filter PMC Global Override Related Box
	 *
	 * Filter to allow editorial to override post-level settings to show/hide
	 * related module site-wide
	 *
	 * @filter pmc_global_override_related_box
	 *
	 * @param string $should_hide post-level setting for override ('true'/'false')
	 *
	 * @return bool
	 *
	 */
	public function filter_pmc_global_override_related_box($should_hide)
	{
		// Never hide on VIP Posts
		if (is_singular([Content::VIP_POST_TYPE, Special_Reports::POST_TYPE])) {
			return false;
		}

		$module_visibility = \PMC_Cheezcap::get_instance()->get_option('related-posts-module-visibility', false);

		if ('inherit' === $module_visibility) {
			return (1 === $should_hide);
		}

		return ('true' === $module_visibility);
	}

	/**
	 * Filter PMC Global AMP Override Related Box
	 *
	 * Filter to allow editorial to override post-level settings to show/hide
	 * related module site-wide on AMP articles specifically
	 *
	 * @filter pmc_global_amp_override_related_box
	 *
	 * @param string $should_hide post-level setting for override ('true'/'false')
	 *
	 * @return bool
	 *
	 */
	public function filter_pmc_global_amp_override_related_box($should_hide)
	{
		$module_visibility = \PMC_Cheezcap::get_instance()->get_option('related-posts-module-amp-visibility', false);

		if ('inherit' === $module_visibility) {
			return (1 === $should_hide);
		}

		return ('true' === $module_visibility);
	}
}
