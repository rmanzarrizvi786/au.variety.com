<?php

/**
 * Configuration file for pmc-related-link plugin.
 *
 * @author  Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since   2017-10-03 - CDWE-675
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;


class PMC_Related_Link
{

	use Singleton;

	const SHORT_CODE = 'pmc-related-link';

	/**
	 * Construct Method.
	 */
	protected function __construct()
	{
		$this->_setup_hooks();
	}

	/**
	 * To setup actions/filters.
	 *
	 * @return void
	 */
	protected function _setup_hooks()
	{

		/**
		 * Actions
		 */
		// Because 'is_amp_endpoint()' is only available after `parse_query` action.
		add_action('parse_query', array($this, 'register_shortcodes'));
	}

	/**
	 * Conditional method to check if current URL is AMP URL or not.
	 *
	 * @since  2017-10-03 - Dhaval Parekh - CDWE-675
	 *
	 * @return boolean Returns TRUE if current URL is AMP URL else FALSE
	 */
	protected function _is_amp()
	{

		if (function_exists('is_amp_endpoint') && is_amp_endpoint()) {
			return true;
		}

		return false;
	}

	/**
	 * To reset short code callback function.
	 * So, On short code we will show nothing.
	 *
	 * @return void
	 */
	public function register_shortcodes()
	{

		/**
		 * Only for none AMP pages.
		 * We keep want related links as normal on AMP page.
		 */
		if (!$this->_is_amp()) {
			remove_shortcode(self::SHORT_CODE);
			add_shortcode(self::SHORT_CODE, '__return_empty_string');
		}
	}

	/**
	 * To extract pmc-related-link shortcode and it's data.
	 *
	 * @param  string $content Content from where shortcode need to extract.
	 *
	 * @return array|bool Shortcode data in array on success, Otherwise FALSE.
	 */
	protected function _extract_shortcode_from_content($content)
	{

		if (empty($content) || false === strpos($content, self::SHORT_CODE)) {
			return false;
		}

		$pattern = get_shortcode_regex(array(self::SHORT_CODE));

		$post_data = array();

		if (preg_match_all('/' . $pattern . '/s', $content, $matches)) {

			foreach ($matches[0] as $key => $value) {

				/**
				 * $matches[3] return the shortcode attribute as string
				 * Replace space with '&' for parse_str() function
				 */
				$get = str_replace(' ', '&', $matches[3][$key]);
				parse_str($get, $output);

				if (!empty($output['href'])) {
					$post_data[$key]['href']  = trim($output['href'], '"');
					$post_data[$key]['title'] = $matches[5][$key];
				}
			}
		}

		if (empty($post_data) || !is_array($post_data)) {
			return false;
		}

		return $post_data;
	}

	/**
	 * To extract pmc-related-link shortcode and it's data.
	 * And return formated data for related link box.
	 *
	 * @since 2018-03-05 - Dhaval Parekh - READS_1043 Move video post type at top.
	 *
	 * @param  string $content Content of post.
	 *
	 * @return array|bool Related artical data on success otherwise FALSE.
	 */
	public function get_related_items($content)
	{

		if (empty($content) || false === strpos($content, self::SHORT_CODE)) {
			return false;
		}

		$post_data = $this->_extract_shortcode_from_content($content);

		if (empty($post_data) || !is_array($post_data)) {
			return false;
		}

		$post_ids = array();
		$items = array(
			'type'  => 'post',
			'posts' => array(),
		);

		foreach ($post_data as $data) {
			$post_ids[] = url_to_postid($data['href']);
		}

		$post_ids = array_filter($post_ids);
		$post_ids = array_map('absint', $post_ids);

		if (empty($post_ids)) {
			return false;
		}

		$items['type'] = 'pmc-related-link';

		$args = array(
			'posts_per_page'   => max(count($post_ids), 3),
			'post_type'        => 'any',
			'post_status'      => 'publish',
			'post__in'         => $post_ids,
			'orderby'          => 'post__in',
			'no_found_rows'    => true,
		);

		$wp_query = new \WP_Query($args);

		$items['posts'] = $wp_query->posts;
		$items['posts'] = \PMC\Core\Inc\Related::get_instance()->add_tracking($items['posts']);

		foreach ($items['posts'] as $key => $item) {

			/**
			 * Change the post title with title that need to show.
			 */
			$items['posts'][$key]->post_title = $post_data[$key]['title'];
		}

		/**
		 * If related link have "Video" post type then move it to top.
		 */
		$post_types = wp_list_pluck($items['posts'], 'post_type');

		if (is_array($post_types) && in_array('variety_top_video', (array) $post_types, true)) {

			$index = array_search('variety_top_video', $post_types);

			if (!empty($index) && intval($index) >= 1) {

				$video_posts = [$items['posts'][$index]];
				unset($items['posts'][$index]);

				$items['posts'] = array_merge($video_posts, $items['posts']);
			}
		}

		return $items;
	}
}
