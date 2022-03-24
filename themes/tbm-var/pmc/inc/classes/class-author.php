<?php

/**
 * Class Author
 *
 * Class for the Author templates.
 *
 * @package pmc-core
 * @since   2019-09-11
 */

namespace PMC\Core\Inc;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Author.
 *
 * @since 2019-09-11
 */
class Author
{

	use Singleton;

	/**
	 * Get Author Posts
	 *
	 * Gets the three most recent posts by an author.
	 *
	 * @param int|string $author_name A \WP_User nickname.
	 *
	 * @return array|bool|mixed|string Cached Author posts.
	 */
	public function get_author_posts($author_name)
	{

		$cache_key = sanitize_key('artnews_author_posts_nickname_' . $author_name);

		$pmc_cache = new \PMC_Cache($cache_key);

		// Cache for 5 min.
		$cache_data = $pmc_cache->expires_in(5 * MINUTE_IN_SECONDS)
			->updates_with(
				[$this, 'author_posts_query'],
				[
					'author_name' => $author_name,
				]
			)
			->get();

		if (is_array($cache_data) && !empty($cache_data) && !is_wp_error($cache_data)) {

			return $cache_data;
		}

		return [];
	}

	/**
	 * Author Posts Query
	 *
	 * Runs a \WP_Query to get the three most recent posts
	 * by a given author.
	 *
	 * This uses the author nicename as it is more reliable
	 * than using a User ID due to the use of the coauthors plugin.
	 *
	 * @param int|string $author_nicename A \WP_User nicename.
	 *
	 * @return array|string Array of posts, else 'none' string.
	 */
	public function author_posts_query($author_nicename)
	{

		/**
		 * Author Posts Post Types Filter
		 *
		 * Filters the post types that are used when fetching the latest posts by an author.
		 *
		 * @param array $post_types An array of custom post types.
		 */
		$author_post_types = apply_filters('pmc_core_author_posts_post_types', ['post']);

		$query = new \WP_Query(
			[
				'post_status'    => 'publish',
				'post_type'      => $author_post_types,
				'author_name'    => $author_nicename,
				'posts_per_page' => 4,
				'no_found_rows'  => true,
			]
		);

		// All we need is the IDs.
		$post_ids = wp_list_pluck($query->posts, 'ID');

		/*
		 * Store a string so that the query isn't run on every page load if
		 * the author truly has no posts.
		 */
		if (empty($post_ids)) {

			return 'none';
		}

		if (is_array($post_ids)) {
			$key = array_search(get_the_ID(), (array) $post_ids, true);
		}

		if (false !== $key) {
			unset($post_ids[$key]);
		}

		// NOTE: It's not possible to satisfy this condition all the time,
		// so must need to ignore it from codeCoverage,
		// however count is already checked in unit-tests.
		if (count($post_ids) > 3) {
			array_pop($post_ids); // @codeCoverageIgnore
		}

		return $post_ids;
	}

	/**
	 * Get all author data which is used in author templates.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $avatar_size Avatar size.
	 *
	 * @return array
	 */
	public function authors_data($post_id = 0, $avatar_size = 'thumbnail')
	{

		global $wp;

		$post_id = (!empty($post_id)) ? $post_id : get_the_ID();

		$custom_author = get_post_meta($post_id, 'author', true);

		if ($custom_author) {
			$byline = $custom_author;
		} else {

			$byline_instance = \PMC\Core\Inc\Meta\Byline::get_instance();

			$authors = $byline_instance->get_authors($post_id);
			$byline  = $byline_instance->get_the_mini_byline($post_id);

			$author_data = [];

			/**
			 * If we only have one author, fill in the details.
			 * This data will not be needed if we have more than one author.
			 */
			if (!empty($authors) && is_array($authors) && 1 === count($authors)) {

				$author = $authors[0];

				$twitter = [];

				if (!empty($author->_pmc_user_twitter)) {

					$share_url = sprintf(
						'https://twitter.com/intent/follow?screen_name=%1$s&tw_p=followbutton&ref_src=twsrc%5Etfw&original_referer=%2$s',
						$author->_pmc_user_twitter,
						rawurlencode(home_url(add_query_arg([], $wp->request)))
					);

					$twitter = [
						'link'      => sprintf('https://twitter.com/%s', trim($author->_pmc_user_twitter, '@')),
						'handle'    => $author->_pmc_user_twitter,
						'share_url' => $share_url,
					];
				}

				$role = (!empty($author->roles[0])) ? $author->roles[0] : $author->_pmc_title;

				$author_data['single_author'] = [
					'author'    => $author,
					'more_info' => [
						'author_name' => $author->display_name,
						'author_role' => $role,
						'byline'      => $byline,
						'twitter'     => $twitter,
					],
				];

				$user_avatar = get_the_post_thumbnail_url($author->ID, $avatar_size);

				if (
					!empty($user_avatar)
					&& false !== strpos($user_avatar, 'gravatar')
				) {
					$user_avatar = false;
				}

				if (!empty($user_avatar)) {
					$author_data['single_author']['picture'] = [
						'image' => $user_avatar,
						'name'  => $author->display_name,
					];
				}
			}
		}

		$author_data['byline'] = $byline;

		return $author_data;
	}
}
