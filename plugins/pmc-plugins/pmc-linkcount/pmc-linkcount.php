<?php

/*
Plugin Name: PMC Link Count
Description: Adds count of links to post meta when an article is saved
Version: 1.0
Author: PMC, Archana Mandhare
License: PMC Proprietary.  All rights reserved.
*/
wpcom_vip_load_plugin('pmc-global-functions', 'pmc-plugins');

use PMC\Global_Functions\Traits\Singleton;

class PMC_LinkCount
{

	use Singleton;

	const FILTER_ALLOW_POST_TYPE = 'pmc_post_linkcount_post_type_allowlist';

	/**
	 * All post types this plugin should support
	 *
	 * @since 2015-08-03
	 *
	 * @version 2015-08-03 - Archana Mandhare - PPT-5233
	 */
	public $post_types = array('post', 'vl-english');

	/**
	 * Init function called when object is instantiated
	 *
	 * @since 2015-08-03
	 *
	 * @version 2015-08-03 - Archana Mandhare - PPT-5233
	 */
	protected function __construct()
	{

		add_action('admin_init', array($this, 'action_admin_init'));
	}

	public function action_admin_init()
	{
		/**
		 * 'pmc_post_linkcount_post_type_allowlist' filter allows LOB to pass post types that this plugin should support
		 *
		 * @since 2015-08-03
		 *
		 * @version 2015-08-03 - Archana Mandhare - PPT-5233
		 *
		 * @params array $this->_post_types
		 *
		 */
		$this->post_types = apply_filters(self::FILTER_ALLOW_POST_TYPE, $this->post_types);

		// @TODO: SADE-517 to be removed
		$this->post_types = apply_filters('pmc_post_linkcount_post_type_whitelist', $this->post_types);

		add_action('save_post', array($this, 'action_pmc_linkcount_on_save_post'), 10, 2);
	}

	/**
	 * Save post action hook to save link count (external, internal article and internal tag) in post meta
	 *
	 * @since 2015-08-03
	 *
	 * @version 2015-08-03 - Archana Mandhare - PPT-5233
	 *
	 * @params string $content - the content of the post we want to search for links and save count in post meta
	 *
	 * @return string $content
	 *
	 */
	public function action_pmc_linkcount_on_save_post($post_id, $post)
	{

		// don't autosave this
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		// AJAX? Not used here
		if (defined('DOING_AJAX') && DOING_AJAX) {
			return;
		}

		// Check for appropriate capabilities
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		// Check for the required post type
		if (!in_array($post->post_type, $this->post_types)) {
			return;
		}

		$this->save_linkcount_in_post_meta($post->ID, $post->post_content);
	}

	/**
	 * function called inside the_content filter to save the meta information for manually added links count in article content
	 * Internal article, internal tags and external links count is saved in post meta for SEO.
	 *
	 * @since 2015-08-03
	 *
	 * @version 2015-08-03 - Archana Mandhare - PPT-5233
	 *
	 * @params int $post_id - the ID of the post we want to save post meta to
	 *         string $content - the content we want to extract the links
	 */
	public function save_linkcount_in_post_meta($post_id, $content)
	{

		global $wp_rewrite;

		$tag_urls      = array();
		$article_urls  = array();
		$external_urls = array();

		$current_host = $this->_get_host_name(get_permalink($post_id));

		// @todo - need to find a better way to determine if this is a tag page link
		$tag_permastruct = $wp_rewrite->get_tag_permastruct(); // return '/tag/%post_tag%'
		$tag_struct      = preg_replace('/%post_tag%/', '', $tag_permastruct);
		$tagbase_option  = get_option('tag_base');
		$tag_base        = !empty($tag_struct) ? $tag_struct : $tagbase_option;

		// Existing tests cover effects, full coverage should include cleanup.
		// @codeCoverageIgnoreStart
		$doc = PMC_DOM::load_dom_content($content);

		if ($doc instanceof DOMDocument) {
			$anchors     = $doc->getElementsByTagName('a');
			$num_anchors = $anchors->length;

			for ($x = 0; $x < $num_anchors; $x++) {

				$anchor = $anchors->item($x);
				$href   = $anchor->getAttribute('href');

				$anchor_host = $this->_get_host_name($href);

				if (!empty($anchor_host)) {

					if ($current_host != $anchor_host) {
						// External link detected.
						$external_urls[] = $href;
					} else {
						// Internal link detected.
						$article_id = url_to_postid($href);
						if (!empty($article_id)) {
							// article page link found
							$article_urls[] = $href;
							continue;
						}

						if (!empty($tag_base) && false !== stripos($href, $tag_base)) {
							// tag page link found
							$tag_urls[] = $href;
							continue;
						}
					}
				}
			}
		}
		// @codeCoverageIgnoreEnd

		$linkcount_meta_tag = array(
			'count' => count($tag_urls),
			'urls'  => $tag_urls
		);

		$meta_key_tag = update_post_meta($post_id, 'pmc_linkcount_tag', $linkcount_meta_tag);

		$linkcount_meta_article = array(
			'count' => count($article_urls),
			'urls'  => $article_urls
		);

		$meta_key_article = update_post_meta($post_id, 'pmc_linkcount_article', $linkcount_meta_article);

		$linkcount_meta_external = array(
			'count' => count($external_urls),
			'urls'  => $external_urls
		);

		$meta_key_external = update_post_meta($post_id, 'pmc_linkcount_external', $linkcount_meta_external);
	}

	/**
	 * Extracts hostname from the given URL
	 *
	 * @since 2015-08-04
	 *
	 * @version 2015-08-04 - Archana Mandhare - PPT-5233
	 *
	 * @params string $url - the URL to parse
	 *
	 * @return string hostname from the url
	 *
	 */
	private function _get_host_name($url)
	{

		if (empty($url) || !is_string($url)) {
			return '';
		}

		$current_host = parse_url($url, PHP_URL_HOST);

		if (!empty($current_host)) {

			$current_host = preg_replace('/^www\./', '', $current_host);

			return strtolower($current_host);
		}

		return '';
	}
}

PMC_LinkCount::get_instance();

// add WP-CLI command support
if (defined('WP_CLI') && WP_CLI) {
	require_once __DIR__ . '/class-pmc-linkcount-cli.php';
}
//EOL
