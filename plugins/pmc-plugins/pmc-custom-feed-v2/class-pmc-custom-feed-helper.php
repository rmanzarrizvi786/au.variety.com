<?php

/**
 * Class of functions that are used by the templates to render out data in respective nodes.
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Custom_Feed_Helper
{

	use Singleton;

	const CACHE_GROUP = 'pmc-custom-feed';
	const CACHE_KEY   = 'pmc_custom_feed_v2_cache_';
	const FEED_PAGE   = 'fpaged';

	private $_feed_tracking_data;
	private static $_feed_options;
	private static $_post_content_media_links;

	protected function __construct()
	{
		self::init_hooks(); //setup hooks
	}

	/**
	 * Any function of this class that needs to be added to a WordPress
	 * hook goes in here
	 */
	public static function init_hooks()
	{
		$class = get_called_class();

		/*
		 * Actions
		 */
		add_action('pmc_custom_feed_start', array($class, 'pmc_custom_feed_start'), 10, 3);
		add_action('pmc_custom_feed_end', array($class, 'pmc_custom_feed_end'), 10, 3);
		add_action('pmc_custom_feed_item', array($class, 'feed_item'), 10, 2);

		/*
		 * Filters
		 */
		add_filter('pmc_custom_feed_content', array($class, 'pmc_custom_feed_content'), 10, 4);
		add_filter('pmc_custom_feed_content', [$class, 'update_amazon_affiliate_code'], 19); // 19 priority to ensure, the callback is run as late as possible. SheKnows has a callback on this filter on priority 15.
		add_filter('pmc_custom_feed_content', array($class, 'prepend_click_to_read_full_article_link'), 10, 4); // @see SADE-399.
		add_filter('pmc_custom_feed_thumbnail_gallery', [$class, 'update_gallery_caption_amazon_affiliate_code'], 19);  // 19 priority to ensure, the callback is run as late as possible.
		add_filter('pmc_custom_feed_post_start', array($class, 'pmc_custom_feed_post_start'), 10, 2);
		add_filter('rss_enclosure', array($class, 'rss_enclosure'), 20);	//hook into this filter late to run after all others
		add_filter('the_category_rss', array($class, 'replace_categories_with_site_name'), 20, 2);	//hook into this filter late to run after all others
		add_filter('pmc_custom_feed_title', array($class, 'maybe_only_site_name_as_title'), 20);
		add_filter('the_excerpt_rss', array($class, 'convert_excerpt_html_entities'));
		add_filter('content_pagination', array($class, 'remove_content_pagination'));
		add_filter('query_vars', array($class, 'add_query_vars'));
		add_filter('pmc_custom_feed_content', [$class, 'maybe_remove_content_img_attributes'], 10, 4);
	}

	/**
	 * Adds a query var used for paginating the feed.
	 *
	 * @param array $vars
	 * @return array
	 */
	public static function add_query_vars($vars)
	{
		$vars[] = self::FEED_PAGE;
		return $vars;
	}

	public static function remove_content_pagination($pages)
	{
		$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();
		if (is_feed() && !empty($feed_options['send-paginated-content'])) {
			$pages = [join('', $pages)];
		}

		return $pages;
	}

	/**
	 * This function accepts a post ID and returns HTML for its video override if it
	 * exists and if the current feed has the 'append-featured-video' enabled.
	 *
	 * @param int $post_id ID of the post whose featured video override html is to be fetched
	 * @return string HTML of featured video override if available else empty string
	 */
	protected static function _maybe_get_escaped_video_override($post_id)
	{
		if (
			!isset(static::$_feed_options['append-featured-video']) || static::$_feed_options['append-featured-video'] !== true
			|| !class_exists('PMC_Featured_Video_Override') || !PMC_Featured_Video_Override::has_featured_video($post_id)
		) {
			return '';
		}

		$video = get_post_meta($post_id, PMC_Featured_Video_Override::META_KEY, true);

		if (strpos($video, 'jwplatform') !== false) {
			//its [jwplatform], lets deal with it

			$pattern = get_shortcode_regex();
			preg_match('/' . $pattern . '/s', $video, $matches);


			if (is_array($matches) && !empty($matches[3])) {
				$jw_player_id = trim($matches[3]);

				if (class_exists(\PMC\JW_YT_Video_Migration\Post_Migration::class)) {

					$video = \PMC\JW_YT_Video_Migration\Post_Migration::get_instance()->output_shortcode(array($jw_player_id));

					if (!empty($video) && false === strpos($video, 'op-social')) {
						$video = '<figure class="op-social">' . $video . '</figure>';
					}
				}

				if (empty($video)) {

					$jw_player_url = 'https://' . JWPLAYER_CONTENT_MASK . '/players/' . $jw_player_id . '.html';
					$video         = sprintf('<iframe src="%s" width="480" height="270" frameborder="0" scrolling="auto"></iframe>', self::esc_xml($jw_player_url, 'url'));
				}

				unset($jw_player_id);
			} else {
				$video = '';
			}
		} else {
			//not [jwplatform], we can get HTML
			$video = PMC_Featured_Video_Override::get_video_html($post_id);
		}

		$allowed_html = wp_kses_allowed_html('post');

		//allow iframe as thats what is used by youtube & jwplatform for video embed
		$allowed_html['iframe'] = array(
			'src' => true,
			'width' => true,
			'height' => true,
			'frameborder' => true,
			'scrolling' => true,
		);

		//VIP suggested to add this fix before they figure out why it is reversing embeds into shortcodes
		//VIP Ticket: https://wordpressvip.zendesk.com/hc/en-us/requests/75816
		if (is_callable(array('Filter_Embedded_HTML_Objects', 'filter'))) {
			remove_filter('pre_kses', array('Filter_Embedded_HTML_Objects', 'filter'), 11);
		}
		return wp_kses($video, $allowed_html);
	}

	/**
	 * @param $content
	 * @param $feed
	 * @param $post
	 * @param $feed_options
	 *
	 * @return string
	 */
	public static function pmc_custom_feed_content($content, $feed, $post, $feed_options)
	{
		if (!empty($feed_options['query_string'])) {
			//Modify content to have query_string in the anchor tag
			$content = self::pmc_feed_add_query_string($content, false);
		}

		$is_related = (!empty($feed_options['related'])) ? $feed_options['related'] : false;


		if (!empty($feed_options["strip-images-from-content"])) {
			$content = self::remove_image_tag($content);
			//remove empty anchor tag.
			$content = preg_replace('/<a[^>]*>\s*<\/a>/i', '', $content);
		}

		if (!empty($content)) {

			if (!empty($feed_options['prepend_text_p1'])) {
				$prepend_text = strip_tags($feed_options['prepend_text_p1']);
				$content = trim($content);
				if (preg_match('/^<p/', $content)) {
					$content = preg_replace('/^(<p[^>]*>)/', '\1<!--//[inject-text]//-->', $content, 1);
					$content = str_replace('<!--//[inject-text]//-->', $prepend_text, $content);
				} else {
					$content = $prepend_text . $content;
				}
				unset($prepend_text);
			}

			if (!empty($feed_options["add-linked-gallery-link"])) {

				if (class_exists('PMC_Gallery_View')) {
					$linked_gallery_data = PMC_Gallery_View::get_linked_gallery_data($post->ID);
				} elseif (class_exists('PMC_Gallery_Thefrontend')) {
					$linked_gallery_data = PMC_Gallery_Thefrontend::get_linked_gallery_data($post->ID);
				}

				if (!empty($linked_gallery_data) && !empty($linked_gallery_data['url'])) {
					$title = empty($linked_gallery_data['title']) ? "Gallery" : $linked_gallery_data['title'];
					$content .= '<p>Launch Gallery: <a href="' . self::esc_xml($linked_gallery_data['url'], 'url') . '">' . wp_kses_post($title) . '</a></p>';
				}
			}

			/*
			 * Append Featured Video if available
			 * _maybe_get_escaped_video_override() returns kses'd HTML
			 */
			$video_override = static::_maybe_get_escaped_video_override($post->ID);

			if (!empty($video_override)) {
				$content .= sprintf('<p class="featured-video-embed">%s</p>', $video_override);
			}

			unset($video_override);

			//related posts
			if ('on' == $is_related) {

				$not_render_permalink = false;

				//For certain templates we want the related link to point to url which again renders feed and not permalink of the post with slug. Check those templates and render related links accordingly.
				if (!empty($feed_options["related-render-feed-url"])) {
					$not_render_permalink = true;
				}

				if (!empty($feed_options['move-related-links-to-the-middle-of-story'])) {

					$closing_p   = '</p>';
					$new_content = '';
					$move_after  = 2;

					$paragraphs = explode($closing_p, $content);

					if (is_array($paragraphs) && count($paragraphs) > $move_after) {

						// Remove the first $move_after paragraphs as it is.
						$new_content = implode($closing_p, array_slice($paragraphs, 0, $move_after)) . $closing_p;
						$paragraphs  = array_slice($paragraphs, $move_after);

						// Append the related links.
						$new_content .= self::render_related_posts($not_render_permalink, false);
						$new_content .= implode($closing_p, $paragraphs);
						$content      = $new_content;
					} else {
						$content .= self::render_related_posts($not_render_permalink, false);
					}
				} else {
					$content .= self::render_related_posts($not_render_permalink, false);
				}
			}

			// Include best-of-brand articles.
			if (!empty($feed_options['include-best-of-brand-articles']) && true === $feed_options['include-best-of-brand-articles']) {

				if (class_exists('\PMC_Related_Articles', false)) {

					// Fetch the related evergreen articles.
					$best_of_brand_articles = \PMC_Related_Articles::get_instance()->get_related_evergreen_articles($post);

					if (!empty($best_of_brand_articles)) {

						// UTM parameters from feed configs.
						$feed_options_utm_params = PMC\Custom_Feed\PMC_Feed_UTM_Params::get_instance()->get_utm_params($feed_options);

						// UTM parameters for tracking Best of Brand links performance.
						$best_of_brand_utm_params = [
							'utm_source'   => $feed,
							'utm_medium'   => 'feeds',
							'utm_campaign' => 'best_of_brands',
						];

						$utm_params = !empty($feed_options_utm_params) ? $feed_options_utm_params : $best_of_brand_utm_params;

						$best_of_brand_html = sprintf('<div><div><strong>Best of %s</strong></div><ul>', get_bloginfo('name'));

						foreach ($best_of_brand_articles as $article) {

							$best_of_brand_html .= sprintf(
								"<li><a href='%s'>%s</a></li>",
								esc_url(self::pmc_feed_add_query_string(get_permalink($article->ID), true, $utm_params)),
								wp_kses_post($article->post_title)
							);
						}

						$best_of_brand_html .= '</ul></div>';
						$content            .= $best_of_brand_html;
					}
				}
			}

			//render static pre-html from feed config
			if (!empty($feed_options['prehtml'])) {
				$content = wp_kses_post($feed_options['prehtml']) . $content;
			}

			//render static html from feed config
			if (!empty($feed_options['html'])) {
				$content .= wp_kses_post($feed_options['html']);
			}

			if (!empty($feed_options['tracking']) && $feed_options['tracking'] == 'on') {
				$feed_tracking = self::get_feed_tracking();
			}

			if (!empty($feed_tracking)) {
				$content .= $feed_tracking;
			}

			if (!empty($feed_options['convert-html-entities']) && true === $feed_options['convert-html-entities']) {
				$content = html_entity_decode($content, ENT_QUOTES);
			}
		}

		return $content;
	}

	/**
	 * Inserts "Click here to read the full article" link to the post content depending on selected feed option, SADE-399, SADE-573.
	 *
	 * @param string  $content      The content being rendered in the feed item.
	 * @param string  $feed         The feed being accessed.
	 * @param WP_Post $post         Current $post being displayed in the feed.
	 * @param array   $feed_options The current feed's options.
	 *
	 * @return string
	 */
	public static function prepend_click_to_read_full_article_link($content, $feed, $post, $feed_options)
	{

		// Bail if none of the add-article-link options are selected.
		if (
			empty($feed_options['add-article-link-on-top'])
			&& empty($feed_options['add-article-link-to-middle'])
			&& empty($feed_options['add-article-link-to-bottom'])
		) {
			return $content;
		}

		$full_article_link = sprintf(
			'<p><a href="%1$s">%2$s</a></p>',
			esc_url(self::pmc_feed_add_query_string(get_permalink($post))),
			'Click here to read the full article. '
		);

		// If link needs to be added to top of article.
		if (!empty($feed_options['add-article-link-on-top'])) {

			// If content starts with image then append the article link below the image.
			$first_img_tag = '/((?:<[^\/]*?>)*?(?:<img.*?src="(?:.*?)".*?>))/mi'; // @see https://regex101.com/r/XTTH8D/2.

			$content_split = preg_split($first_img_tag, $content, 2, PREG_SPLIT_DELIM_CAPTURE);

			if (is_array($content_split) && 3 === count($content_split) && empty($content_split[0])) {

				$content_split[1] = $content_split[1] . $full_article_link;
				$content          = implode('', $content_split);
			} else {
				$content = $full_article_link . $content;
			}
		}

		// If link needs to be added to middle of article.
		if (!empty($feed_options['add-article-link-to-middle'])) {

			$inject_args = array(
				'should_append_after_tag' => true,
				'paragraphs'              => array(
					4 => array($full_article_link), // Middle is defined as after 4th paragraph.
				),
			);

			$content = \PMC_DOM::inject_paragraph_content($content, $inject_args);
		}

		// If link needs to be added to bottom of article.
		if (!empty($feed_options['add-article-link-to-bottom'])) {

			/**
			 * Don't append if link is already appended ( this may happen if 'add-article-link-to-middle' option is selected and content has less than 4 paragraphs )
			 * or there's only few lines after 4th paragraph.
			 */
			if (false === strpos(substr($content, -2500), $full_article_link)) {
				$content .= $full_article_link;
			}
		}

		return $content;
	}

	/**
	 * Removing the width & height attributes from the images when remove-content-img-attributes enabled and width & height attributes exist.
	 *
	 * @param string  $content      The content being rendered in the feed item.
	 * @param string  $feed         The feed being accessed.
	 * @param WP_Post $post         Current $post being displayed in the feed.
	 * @param array   $feed_options The current feed's options.
	 *
	 * @return string
	 */
	public static function maybe_remove_content_img_attributes($content, $feed, $post, $feed_options)
	{

		// Bail if remove-content-img-attributes option is not enabled.
		if (empty($content) || empty($feed_options['remove-cont-img-width-height-attributes'])) {
			return $content;
		}

		preg_match_all('/<img.*(width="\d+").*>/', $content, $matches);
		$images = (!empty($matches[0])) ? $matches[0] : [];

		if (!empty($images) && is_array($images)) {
			foreach ($images as $image) {
				$modified_image_tag = preg_replace('/width="\d+"/', '', $image);
				$modified_image_tag = preg_replace('/height="\d+"/', '', $modified_image_tag);

				$content = str_replace($image, $modified_image_tag, $content);
			}
		}

		return $content;
	}

	/**
	 * Update amazon affiliate code.
	 *
	 * @param string $content Feed content.
	 *
	 * @return string
	 */
	public static function update_amazon_affiliate_code(string $content): string
	{

		$affiliate_code = PMC_Custom_Feed::get_instance()->get_feed_config('affiliate_code');

		if (empty($affiliate_code)) {
			return $content;
		}

		/**
		 * We could write regex to match the full anchor tag however the below regex is intentional to avoid the use of html tags
		 * and keep things simple. Because when matching HTML tags its recommended to use HTML parser instead
		 * of regex which is slower. Read the famous answer below from stack-overflow.
		 *
		 * @link https://stackoverflow.com/questions/1732348/regex-match-open-tags-except-xhtml-self-contained-tags
		 */
		if (empty($content) || !preg_match_all('@href\s*=\s*"(https?://(?:www.amazon.com|amazon.com|amzn.to|read.amazon.com)[^"]+)"@is', $content, $matches)) {
			return $content;
		}

		$links = (!empty($matches[1])) ? $matches[1] : [];

		if (!empty($links) && is_array($links)) {
			foreach ($links as $link) {
				$decoded_link = html_entity_decode($link, ENT_QUOTES);
				$link_parts   = wp_parse_url($decoded_link);

				parse_str($link_parts['query'], $query);

				$updated_link = add_query_arg('tag', $affiliate_code, $decoded_link);
				$content      = str_replace($link, esc_url($updated_link), $content);
			}
		}

		return $content;
	}

	/**
	 * Update amazon affiliate code in gallery caption.
	 *
	 * @param array $image Gallery image array.
	 *
	 * @return array
	 */
	public static function update_gallery_caption_amazon_affiliate_code($image)
	{

		// Array check because the same filter is used for both object and array at two different places.
		if (!is_array($image) || empty($image['caption'])) {
			return $image;
		}

		$image['caption'] = self::update_amazon_affiliate_code($image['caption']);

		return $image;
	}

	/**
	 * This function, called on 'pmc_custom_feed_start', disables
	 * Handles initial setup for feeds.
	 *
	 * @since 2013-09-03 Amit Sannad
	 * @version 2014-07-14 Amit Gupta - store $feed_options in static var
	 */
	public static function pmc_custom_feed_start($feed, $feed_options, $template_name)
	{
		/*
		 * Store feed options in a static var for later use in this class
		 */
		static::$_feed_options = $feed_options;

		$template_array = array("feed-rss2");

		if (!in_array($template_name, $template_array)) {
			self::clean_filters_for_feed();
		}

		self::enable_pmc_tag_links($feed, $feed_options, $template_name);

		$templates = array(
			'feed-iphone-app.php',
		);

		if (in_array($template_name, $templates)) {
			add_filter("pmc_shortcode_flv_shortcircuit", array(get_called_class(), "render_post_link"));
		}

		if (!empty($feed_options["use-full-size-images"])) {

			// @since 2014-09-23 Amit Sannad
			// Yahoo extracts images from feed items and it chokes on images with query string parameters
			// Remove filter per http://wordpressvip.zendesk.com/tickets/20754
			remove_filter('image_downsize', 'wpcom_resize', 10, 3);

			// Remove any remaining query string parameters from images
			add_filter('pmc_custom_feed_content', array(get_called_class(), 'remove_image_dimensions'), 9, 5);
		}

		// If this option is enabled do not rewrite links to https on SSL
		if (!empty($feed_options['disable-https-rewriting-of-links'])) {

			// if we are on HTTPS remove all other set_url_scheme filters and force HTTP scheme
			if (PMC::is_https()) {
				remove_all_filters('set_url_scheme');
				add_filter('set_url_scheme', array(PMC_Custom_Feed_Helper::get_instance(), 'filter_set_url_scheme_force_http'), 10, 3);
			}
		}

		// Possibly move post media (images & oembed links) from post content
		// and place them into <media:content> nodes
		if (!empty($feed_options['move-post-media-to-media-content-nodes'])) {

			// Enable the display of media:content nodes
			// For these to display, your template must have the following action
			// do_action( 'display_post_media_content_nodes', $feed_options, $post );
			add_action('display_post_media_content_nodes', array('PMC_Custom_Feed_Helper', 'action_render_media_content_tags'), 10, 2);

			// Possibly exclude media nodes which are missing copyrights
			if (!empty($feed_options['exclude-media-content-items-missing-copyrights'])) {

				// Filter each <media:content> item and remove
				// items which do not have attribution/a copyright
				add_filter('pmc_custom_feed_media_item', array('PMC_Custom_Feed_Helper', 'filter_remove_media_content_items_wo_attribution'), 10, 1);
			}
		}

		// Move gallery media (images & oembed links) from gallery slides
		// and place them into <media:content> nodes.
		if (!empty($feed_options['move-gallery-media-to-media-content-nodes'])) {

			// Enable the display of media:content nodes.
			// For these to display, your template must have the following action:
			// do_action( 'pmc_custom_feed_display_gallery_media_content_nodes', $feed_options, $post );
			add_action('pmc_custom_feed_display_gallery_media_content_nodes', ['PMC_Custom_Feed_Helper', 'action_render_gallery_media_content_tags'], 10, 2);

			// Possibly exclude media nodes which are missing copyrights.
			if (!empty($feed_options['exclude-media-content-items-missing-copyrights'])) {

				// Filter each <media:content> item and remove
				// items which do not have attribution/a copyright.
				add_filter('pmc_custom_feed_media_item', ['PMC_Custom_Feed_Helper', 'filter_remove_media_content_items_wo_attribution']);
			}
		}

		// Modifies iframes with YouTube videos.
		if (!empty($feed_options['modify-youtube-iframe'])) {

			add_filter('the_content', array(get_called_class(), 'modify_youtube_iframe'), 15);
		}

		// Add filter to use post's SEO title if 'Use SEO Title' feed option checked.
		if (!empty($feed_options['use-seo-title'])) {

			add_filter('the_title_rss', array(get_called_class(), 'filter_the_title_rss'));
		}

		// Add filter to truncate post content if 'Truncate post content' feed option checked.
		if (!empty($feed_options['truncate-post-content'])) {

			// 'pmc_custom_feed_content' filter used to modify post content in feed.
			// We need to truncate post content at last so added this filter with 15 priority.
			add_filter('pmc_custom_feed_content', array(get_called_class(), 'truncate_post_content'), 15);
		}

		// Add filter to remove the srcset attribute from the img tag inside post_content
		// if 'Remove srcset attribute from img tag' feed option checked.
		if (!empty($feed_options['remove-feed-img-srcset'])) {
			add_filter('wp_calculate_image_srcset_meta', ['PMC_Custom_Feed_Helper', 'filter_wp_calculate_image_srcset_meta_empty'], 10, 1);
		}
	}


	/**
	 * Removes the srcset attribute by setting the srcset image sizes to an empty array.
	 *
	 * @param array  $image_meta    The image meta data as returned by 'wp_get_attachment_metadata()'.
	 *
	 * @return array  $image_meta
	 */
	public function filter_wp_calculate_image_srcset_meta_empty($image_meta = []): array
	{

		$image_meta['sizes'] = [];
		return $image_meta;
	}

	/*
	 * Force url scheme to 'HTTP' irrespective of the site url scheme
	 * when the original and set scheme is HTTPS
	 *
	 * @since 2016-01-15
	 * @version 2016-01-15 Archana Mandhare PMCVIP-460
	 *
	 * @param $url string
	 * @param $scheme string
	 * @param $original_scheme string
	 * @return $url string
	 */
	public function filter_set_url_scheme_force_http($url, $scheme, $original_scheme)
	{

		if ('https' === $scheme && 'https' === $original_scheme) {

			// using get_option('home') here
			// since home_url() calls the set_url_scheme()
			// and there is an endless loop just between these 2 functions
			$home_url = esc_url(get_option('home'));
			$domain   = parse_url($home_url, PHP_URL_HOST);

			$url = str_ireplace('https://' . $domain, 'http://' . $domain, $url);
			$url = str_ireplace('https%3A%2F%2F' . $domain, 'http:%3A%2F%2F' . $domain, $url);
		}

		return $url;
	}

	/**
	 * @since 2014-09-23 Amit Sannad
	 * Copied function from BGR to remove query string from yahoo feeds PPT-3401
	 *
	 * @param $content
	 * @param $feed
	 * @param $post
	 * @param $feed_options
	 * @param $template_name
	 *
	 * @return mixed
	 */
	public static function remove_image_dimensions($content, $feed, $post, $feed_options, $template_name)
	{

		$image_re = '/<img\s+([^>]*)src=["' . "'](https?:\/\/([^'" . '"]*))["' . "']\s+([^>]*)(.*?)>/i";

		$orig_content = $content;

		// Step 1: Find all IMG tags in the content
		if (!preg_match_all($image_re, $content, $matches)) {
			return $content;
		}

		if (isset($matches[2])) {
			$images = $matches[2];
		}

		$find    = array();
		$replace = array();

		// Step 2: Loop over the IMG tags and extract the query args and URL fragment from the source, to remove them
		foreach ($images as $img) {
			$parts = parse_url($img);

			if (empty($parts)) {
				continue;
			}

			// If we have a query string, add it to the string of text to be removed
			$replace_str = '';
			if (!empty($parts['query'])) {
				$replace_str .= '?' . $parts['query'];
			}

			// If we have a URL fragment, append it to the string of text to be removed
			if (!empty($parts['fragment'])) {
				$replace_str .= '#' . $parts['fragment'];
			}

			if (empty($replace_str)) {
				continue;
			}

			$img_no_qs = trim(str_replace($replace_str, '', $img), '#?');
			// Our final stripped-down URL will be the original IMG tag, but with no query arguments or url fragment in the IMG SRC

			if (!empty($img_no_qs) && strcmp($img_no_qs, $img) != 0) {
				// If we didn't accidentally end up with an empty IMG tag, and the new tag isn't the same as the original tag, add them to arrays to be passed to str_replace
				$find[]    = $img;
				$replace[] = $img_no_qs;
			}
		}

		$content = str_replace($find, $replace, $content);

		if (!empty($content) && strlen($content) > 100) {
			return $content;
		} else {
			return $orig_content;
		}
	}

	/**
	 * This function, called on 'pmc_custom_feed_end', enables
	 * Handles final bookkepping for feeds
	 *
	 * @since 2013-09-03 Amit Sannad
	 */
	public static function pmc_custom_feed_end($feed, $feed_options, $template_name)
	{

		self::disable_pmc_tag_links($feed, $feed_options, $template_name);
	}

	/**
	 * Filter each $post displayed in a custom feed
	 *
	 * @version 2017-05-17 CDWE-304 Strip inline images if 'strip-inline-images' custom feed option checked
	 *
	 * @param WP_Post $post         The current $post being displayed in the feed
	 * @param array   $feed_options The current feed's options
	 *
	 * @return WP_Post $post The *possibly* modified post object
	 */
	public static function pmc_custom_feed_post_start($post, $feed_options)
	{
		if (empty($post) || !is_a($post, 'WP_Post')) {
			return false;
		}

		// Skip rendering post if invalid date.
		if (PMC_Custom_Feed_Helper::invalid_date($post->post_date_gmt)) {
			return false;
		}

		$post = PMC_Custom_Feed_Helper::pmc_custom_feed_strip_related_link_shortcode($post, $feed_options);

		// Possibly move post media (images & oembed links) from post content
		// and place them into <media:content> nodes
		// Strip inline images if 'strip-inline-images' custom feed option checked.
		if (!empty($feed_options['move-post-media-to-media-content-nodes']) || !empty($feed_options['strip-inline-images']) || !empty($feed_options['strip-all-images'])) {

			if (!empty($feed_options['move-post-media-to-media-content-nodes'])) {

				// Capture the post's oembed links and remove them from the content
				$post = PMC_Custom_Feed_Helper::capture_media_links_within_post_content($post, $feed_options);
			}

			// Remove images from post content
			// Do so by running wp_kses on the post content and
			// omit the image tag. wp_kses removes html around
			// nested images, which is far superior than simply
			// remove image tags with regex. Seems like hte best
			// approach currently.
			$post->post_content = wp_kses($post->post_content, array(
				'p'      => array(), # paragraph tags are allowed
				'i'      => array(), # italic tags are allowed
				'em'     => array(), # emphasis tags are allowed
				'b'      => array(), # bold tags are allowed
				'strong' => array(), # strong tags are allowed
				'a'      => array(   # anchor tags are allowed
					'href'   => array(),
					'title'  => array(),
					'target' => array(),
				),
			));

			// Remove empty paragraphs created by the above wp_kses() operation
			$post->post_content = str_replace('<p></p>', '', $post->post_content);

			// Strip any leading/trailing whitespace
			// This doesn't have any bearing on the feed itself,
			// it's simply to make the feed output more readable.
			$post->post_content = trim($post->post_content);
		}

		return $post;
	}

	/**
	 * This function accepts image ID and returns/echoes media:credit/<media:credit> tag if
	 * image credit exists
	 *
	 * @codeCoverageIgnore - Ignoring because the unit test case structure is being updated in below PR which is yet to be merged at the time of updating this function.
	 * https://bitbucket.org/penskemediacorp/pmc-plugins/pull-requests/2085/fix-xml-encoding-on-msn-feed-unit-test/
	 *
	 * @todo SADE-232, Update test cases based on updated unit tests structure.
	 *
	 * @since 2013-04-05 Amit Gupta
	 *
	 * @version 2017-05-15 - Chandra Patel - CDWE-304 - Added <mi:hasSyndicationRights> tag
	 *
	 * @version 2019-07-25 - Kelin Chauhan - Changed logic to separate data retrieval.
	 *
	 * @param int  $image_id Attachment ID.
	 * @param bool $echo     Whether to return or echo the data. If true it ignores $with_tag.
	 *
	 * @return string Depending on $echo returns <media:credit> and <mi:hasSyndicationRights> tags.
	 */
	public static function render_image_credit_tag($image_id, $echo = true)
	{

		$image_id = intval($image_id);

		if ($image_id < 1) {
			return;
		}

		$image_credit = self::get_image_credit($image_id);

		if (!empty($image_credit)) {

			$image_credit_tag             = '<media:credit>' . self::esc_xml($image_credit) . '</media:credit>';
			$image_syndication_rights_tag = '';

			// Add <mi:hasSyndicationRights> tag if 'MSN Syndication Rights' custom feed option is checked.

			if (isset(static::$_feed_options['msn-syndication-rights']) && true === static::$_feed_options['msn-syndication-rights']) {
				$image_syndication_rights_tag = '<mi:hasSyndicationRights>1</mi:hasSyndicationRights>';
			}

			// Check if tags needs to be echoed.

			if (true === $echo) {
				echo $image_credit_tag . $image_syndication_rights_tag; // @codingStandardsIgnoreLine - escaping is already done for the tag content using esc_xml method.
			}

			return $image_credit_tag . $image_syndication_rights_tag;
		}
	}

	/**
	 * This function accepts attachment ID and returns image credit data.
	 *
	 * @codeCoverageIgnore - Ignoring because the unit test case structure is being updated in below PR which is yet to be merged at the time of updating this function.
	 * https://bitbucket.org/penskemediacorp/pmc-plugins/pull-requests/2085/fix-xml-encoding-on-msn-feed-unit-test/
	 *
	 * @todo SADE-232, Add test cases based on updated unit tests structure.
	 *
	 * @since 2019-07-25 - Kelin Chauhan.
	 *
	 * @param int  $attachment_id Attachment ID.
	 * @param bool $echo          Whether to return or echo the data. If true it ignores $with_tag.

	 *
	 * @return string Returns image credit info.
	 */
	public static function get_image_credit($attachment_id)
	{

		$attachment_id = intval($attachment_id);

		if ($attachment_id < 1) {
			return;
		}

		$image_credit = get_post_meta($attachment_id, '_image_credit', true);

		// For brands that use caption field as image credit
		$use_caption = apply_filters('pmc_custom_feed_use_caption_credit', false);

		if (true === $use_caption) {
			$image_credit = wp_get_attachment_caption($attachment_id);
		}

		if (empty($image_credit)) {
			return;
		}

		/**
		 * Filters image credit info if anything needs to be added / changed.
		 *
		 * @param string $image_credit  The image credit data.
		 * @param int    $attachment_id Attachment ID.
		 */
		return apply_filters('pmc_custom_feed_image_credit', $image_credit, $attachment_id);
	}

	/**
	 * add query string to url for tracking
	 *
	 * @param string $content      URL to add query strings to.
	 * @param bool   $urlonly      Whether passed content is url or a url within a tag in content.
	 * @param array  $must_have_qs If any custom query params need to be added i.e. utm_* params.
	 *
	 * @return mixed|string
	 */
	public static function pmc_feed_add_query_string($content, $urlonly = true, $must_have_qs = [])
	{
		global $feed, $pmc_custom_feed_qs;

		if (empty($content)) {
			return '';
		}

		$feed_config = PMC_Custom_Feed::get_instance()->get_feed_config();

		if (!empty($feed_config['is-pmc-maz']) && function_exists('pmc_make_maz_url')) {

			$url = filter_var($content, FILTER_VALIDATE_URL);
			if (!empty($url)) {
				$content = pmc_make_maz_url($url);
			}
		}

		if (empty($pmc_custom_feed_qs)) {

			$qstring            = PMC_Custom_Feed::get_instance()->get_feed_config("query_string");
			$pmc_custom_feed_qs = explode("&", $qstring);
			$arr                = array();
			array_walk($pmc_custom_feed_qs, function ($value, $key, $arr_data) {
				$arr = &$arr_data[0];
				$k   = explode('=', $value);
				if (count($k) > 1)
					$arr[$k[0]] = $k[1];
			}, array(&$arr));

			$pmc_custom_feed_qs = $arr;
		}

		if (!empty($must_have_qs) && is_array($must_have_qs)) {
			// Must add the required querystring
			$content = add_query_arg($must_have_qs, $content);
			if (is_array($pmc_custom_feed_qs)) {
				// We need to remove any duplicated querystring
				$pmc_custom_feed_qs = array_diff((array) $pmc_custom_feed_qs, (array) $must_have_qs);
			}
		}

		if (!empty($pmc_custom_feed_qs)) {
			if ($urlonly) {
				// we need to decode the url to valid form before calling add_query_arg to avoid hash (&#038;) bug in querystring
				$content = html_entity_decode($content, ENT_QUOTES);
				return self::esc_xml(add_query_arg($pmc_custom_feed_qs, $content));
			} else {
				$regex = '/<a[^>]*href="([^"]+)"[^>]*>|' . "<a[^>]*href='([^']+)'[^>]*>/si";

				$return_content = preg_replace_callback(
					$regex,
					function ($match) {
						global $pmc_custom_feed_qs;
						$newurl = esc_url(add_query_arg($pmc_custom_feed_qs, $match[1]));
						$atag   = str_replace($match[1], $newurl, $match[0]);

						return $atag;
					},
					$content
				);

				return $return_content;
			}
		} else {
			if ($urlonly) {
				// we need to decode the url to valid form before re-encode into xml entities
				$content = html_entity_decode($content, ENT_QUOTES);
				return self::esc_xml($content);
			}
			return $content;
		}
	}

	/**
	 * Output the xml encoded rss link
	 *
	 * @param object|int|string $post_or_link Post object, ID, or url
	 * @return string
	 */
	public static function the_permalink_rss($post_or_link): string
	{
		$rss_link = is_string($post_or_link) && !empty($post_or_link) ? $post_or_link : get_permalink($post_or_link);
		$rss_link = apply_filters('the_permalink_rss', $rss_link);
		return self::esc_xml($rss_link); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
	}

	public static function validate_post_types($post_types)
	{

		if (!is_array($post_types)) {
			$post_types = explode(',', $post_types);
		}

		return array_filter($post_types, 'post_type_exists');
	}

	/**
	 * Retrieve a feed's posts from cache, using the provided arguments.
	 *
	 * @param string     $feed        Feed name.
	 * @param array|null $filter_args WP_Query arguments.
	 * @return WP_Post[]
	 * @throws ErrorException Invalid use of PMC_Cache.
	 */
	public static function pmc_feed_get_posts($feed = '', $filter_args = null): array
	{
		if (empty($feed)) {
			global $feed;
		}

		$post_id = get_query_var('fpid');

		if (is_numeric($post_id)) {
			$return_post = get_post($post_id);

			if (!$return_post instanceof WP_Post || 'publish' !== $return_post->post_status) {
				return [];
			}

			$post_type_object = get_post_type_object($return_post->post_type);

			if ($post_type_object instanceof WP_Post_Type && $post_type_object->public) {
				return [$return_post];
			}

			return [];
		}

		$args = static::_parse_feed_args($filter_args);

		$key = sprintf(
			'feed-post-ids/%1$s/%2$s/%3$s',
			$feed,
			md5(serialize($args)), // phpcs:ignore
			get_lastpostmodified(
				'gmt',
				static::_get_post_type_from_feed_args($args)
			)
		);

		$cache = new PMC_Cache($key, static::CACHE_GROUP);

		$cache
			->expires_in(12 * HOUR_IN_SECONDS)
			->on_failure_expiry_in(MINUTE_IN_SECONDS)
			->updates_with(
				[__CLASS__, 'pmc_feed_get_post_ids_for_cache'],
				[
					$args,
				]
			);

		$ids = $cache->get();

		if (is_array($ids)) {
			return array_map('get_post', $ids);
		}

		return [];
	}

	/**
	 * Parse passed arguments and feed config into cohesive set of WP_Query
	 * arguments.
	 *
	 * @param array|null $filter_args WP_Query arguments.
	 * @return array
	 */
	protected static function _parse_feed_args(?array $filter_args = null): array
	{
		$feed_options = PMC_Custom_Feed::get_instance();

		$page_number = get_query_var(self::FEED_PAGE, 1);

		$order_by = $feed_options->get_feed_config();
		$order_by = (!empty($order_by['sort-by-last-modified']) && true === $order_by['sort-by-last-modified']) ? 'modified' : 'post_date';

		$args = array(
			'numberposts'      => min(absint($feed_options->get_feed_config('count')), PMC_Custom_Feed::MAX_POST_COUNT),
			'orderby'          => $order_by,
			'order'            => 'desc',
			'paged'            => $page_number,
			'suppress_filters' => false,
		);

		if (empty($filter_args)) {
			$filter_args = apply_filters('pmc_custom_feed_posts_filter', $args, $feed_options->get_feed_config());
		}
		// do we have any filter args
		if (!empty($filter_args) && is_array($filter_args)) {
			// we want the feed setting to override any filter args passed by custom feed template
			$args = array_merge($filter_args, $args);
		}

		//Check custom post type
		$post_types = self::validate_post_types($feed_options->get_feed_config('post_type'));

		if (!empty($post_types)) {
			$args['post_type'] = $post_types;
		} else {
			$args['post_type'] = array('post', 'pmc-gallery');
		}

		return $args;
	}

	/**
	 * Extract a single post type from query arguments, if possible.
	 *
	 * For single-post-type feeds, improves cache life by aiding in ignoring
	 * updates to post objects that won't appear in this feed.
	 *
	 * @param array $args WP_Query arguments.
	 * @return string
	 */
	protected static function _get_post_type_from_feed_args(array $args): string
	{
		$post_type = 'any';

		if (is_string($args['post_type'])) {
			return $args['post_type'];
		}

		if (
			is_array($args['post_type'])
			&& 1 === count($args['post_type'])
		) {
			return array_shift($args['post_type']);
		}

		return $post_type;
	}

	/**
	 * Return a list of IDs corresponding to a feed's query arguments.
	 *
	 * @param array $args WP_Query arguments.
	 * @return int[]
	 */
	public static function pmc_feed_get_post_ids_for_cache(array $args): array
	{
		return wp_list_pluck(
			static::_get_post_objects_for_cache($args),
			'ID'
		);
	}

	/**
	 * Retrieve WP_Post objects for a feed's arguments.
	 *
	 * @param array $args WP_Query arguments.
	 * @return WP_post[]
	 */
	protected static function _get_post_objects_for_cache(array $args): array
	{
		$feed_options              = PMC_Custom_Feed::get_instance();
		$feed_taxonomy_from_option = $feed_options->get_feed_config('taxonomy');

		/**
		 * Filter to enable selection of curation modules.
		 *
		 * @since 2018-12-27, PMCEED-1627, Kelin Chauhan <kelin.chuahan@rtcamp.com>
		 *
		 * @param bool $feed_curation The default value is passed as false.
		 */
		$feed_curation = apply_filters('pmc_custom_feed_enable_curation', false);

		// Get the values of the curation dropdowns.
		$curation_1 = $feed_options->get_feed_config('curation_1');
		$curation_2 = $feed_options->get_feed_config('curation_2');
		$curation_3 = $feed_options->get_feed_config('curation_3');

		/*
		 * Check if feed is for curated content
		 *
		 * If any of the curation dropdowns is selected then omit the Taxonomy Query and Feed Post Type Query
		 * and get the curated posts.
		 */
		if (
			class_exists('PMC_Carousel')
			&& true === $feed_curation
			&& (!empty($curation_1) || !empty($curation_2) || !empty($curation_3))
		) {

			$carousel_options = [
				'add_filler' => false,
			];

			$number_of_post = (int) $feed_options->get_feed_config('count');

			$curation_1_posts = (!empty($curation_1)) ? pmc_render_carousel(PMC_Carousel::modules_taxonomy_name, $curation_1, $number_of_post, '', $carousel_options) : [];
			$curation_2_posts = (!empty($curation_2)) ? pmc_render_carousel(PMC_Carousel::modules_taxonomy_name, $curation_2, $number_of_post, '', $carousel_options) : [];
			$curation_3_posts = (!empty($curation_3)) ? pmc_render_carousel(PMC_Carousel::modules_taxonomy_name, $curation_3, $number_of_post, '', $carousel_options) : [];

			/*
			 * Let's make sure we have array
			 * Not typecasting here because pmc_render_carousel() can return boolean as well in which
			 * case we just want an empty array.
			 */
			$curation_1_posts = (!is_array($curation_1_posts)) ? [] : $curation_1_posts;
			$curation_2_posts = (!is_array($curation_2_posts)) ? [] : $curation_2_posts;
			$curation_3_posts = (!is_array($curation_3_posts)) ? [] : $curation_3_posts;

			// Merge posts from all three curation modules and remove extra posts.
			$curated_posts = array_merge((array) $curation_1_posts, (array) $curation_2_posts, (array) $curation_3_posts);
			$post_ids      = array_values(array_unique((array) wp_list_pluck($curated_posts, 'ID')));
			$curated_posts = array_slice($post_ids, 0, $number_of_post);
			$unique_posts  = [];

			// pmc_render_carousel returns the post as an array, we need post object.
			foreach ($curated_posts as $curated_post_id) {
				$unique_posts[] = get_post($curated_post_id);
			}

			return $unique_posts;
		}
		if (empty($feed_taxonomy_from_option) || 'all posts' === strtolower($feed_taxonomy_from_option)) {
			return get_posts($args); // @codingStandardsIgnoreLine.
		}

		/**
		 * Set tax query params.
		 * Format: tax_query | tax_query | is AND relation
		 * where tax_query = taxonomy: comma seperated term slug : operators( IN, NOT IN, AND )
		 */
		$multi_query = explode('|', $feed_taxonomy_from_option);
		$taxonomies  = get_taxonomies('', 'names');

		foreach ($multi_query as $feed_taxonomy_from_option) {

			$feed_taxonomy = explode(':', $feed_taxonomy_from_option);
			$taxonomy      = sanitize_text_field($feed_taxonomy[0]);
			$term          = sanitize_text_field($feed_taxonomy[1]);
			$term          = explode(',', $term);

			if (in_array($taxonomy, (array) $taxonomies, true)) {
				$taxonomy_array = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $term,
				);

				if (isset($feed_taxonomy[2])) {
					$operator_array = array(
						'IN',
						'NOT IN',
						'AND',
					);

					if (in_array($feed_taxonomy[2], $operator_array, true)) {
						$taxonomy_array['operator'] = $feed_taxonomy[2];
					}
				}

				$args['tax_query'][] = $taxonomy_array;
				if (count($args['tax_query']) > 1) {
					$args['tax_query']['relation'] = 'AND';
				}
			} else {
				$allowed_taxonomies = array('author', 'author_name');

				if (in_array($taxonomy, (array) $allowed_taxonomies, true) && is_array($term)) {
					$args[$taxonomy] = implode(',', $term);
				} else {
					return [];
				}
			}
		}    //end foreach loop

		$args = apply_filters('pmc_custom_feed_posts_filter', $args, $feed_options->get_feed_config());

		return static::_get_feed_posts($args);
	}

	/**
	 * Wrapper function for 'get_posts()' for feeds.
	 *
	 * Results cached in static::pmc_feed_get_posts().
	 *
	 * @param array $args
	 */
	protected static function _get_feed_posts(array $args = []): array
	{

		// Declaration.
		$expires_time = (30 * MINUTE_IN_SECONDS);
		$posts = array();
		$number_of_posts = (!empty($args['numberposts']) && is_numeric($args['numberposts'])) ? absint($args['numberposts']) : 10;

		// If Total number for post for feed is greater than 100 then hard Limit to 100.
		$number_of_posts = intval(min(intval($number_of_posts), 100));

		$post_types = $args['post_type'];

		if (is_string($post_types)) {

			if ('any' === $post_types) {
				$post_types = 'post';
			}

			$post_types = array($post_types);
		}

		// Optimize $args.
		$args['posts_per_page'] = $number_of_posts;

		// Check for 'Not IN' query.
		$tax_queries = (!empty($args['tax_query']) && is_array($args['tax_query'])) ? $args['tax_query'] : array();
		$not_in_queries = array();
		foreach ($tax_queries as $key => $tax_query) {
			if (!empty($tax_query['operator']) && 'not in' === strtolower($tax_query['operator'])) {
				unset($args['tax_query'][$key]);
				$not_in_queries[] = $tax_query;
			}
		}

		// Check for Post types.
		$post_type_posts = array();
		$post_type_args = $args;
		unset($post_type_args['post_type']);

		// Get Post for every single post type individually.
		foreach ($post_types as $post_type) {

			$post_type_args['post_type'] = $post_type;

			$pmc_cache = new PMC_Cache(self::_get_cache_key($post_type_args));
			$query_posts = $pmc_cache->expires_in($expires_time)->updates_with('get_posts', array($post_type_args))->get();

			if (is_array($query_posts) && count($query_posts)) {
				$post_type_posts = array_merge($post_type_posts, $query_posts);
			}
			unset($pmc_cache);
		}

		/**
		 * Skip the posts which come in 'NOT IN'.
		 */
		$posts = array();
		foreach ($post_type_posts as $post) {
			foreach ($not_in_queries as $query) {
				if (empty($query['taxonomy'])) {
					continue;
				}

				$query['terms'] = (is_array($query['terms'])) ? $query['terms'] : array($query['terms']);

				$key = $post->ID . '-' . $query['taxonomy'];
				$pmc_term_cache = new PMC_Cache(self::_get_cache_key($key));
				$post_terms = $pmc_term_cache->expires_in($expires_time)->updates_with('get_the_terms', array($post->ID, $query['taxonomy']))->get();

				// If post doesn't have terms then skip the loop.
				if (empty($post_terms) || !is_array($post_terms)) {
					continue;
				}

				$post_terms = wp_list_pluck($post_terms, 'slug');
				unset($pmc_term_cache);

				foreach ($query['terms'] as $term) {

					if (in_array($term, $post_terms, true)) {
						// Skip this post.
						continue 3;
					}
				}
			}

			// Set timestamp as key to post for sorting.
			$posts[strtotime($post->post_date)] = $post;
		}

		$original_posts = $posts;

		// Sort the result based on post_modified date if 'Sort by Last Modified' custom feed option is checked.
		if (isset($args['orderby']) && 'modified' === $args['orderby']) {
			usort(
				$posts,
				function ($a, $b) {

					$time1 = strtotime($a->post_modified);
					$time2 = strtotime($b->post_modified);

					if ($time1 < $time2) {
						return 1;
					} elseif ($time1 > $time2) {
						return -1;
					} else {
						return 0;
					}
				}
			);
		} else {
			krsort($posts);
		}

		$posts = apply_filters('pmc_feed_get_posts', $posts, $original_posts, $args);

		$posts = array_values($posts);

		// Remove Extra posts.
		$posts = array_slice($posts, 0, $number_of_posts);

		return $posts;
	}

	/**
	 * Generate cache key.
	 *
	 * @param string|array $unique base on that cache key will generate.
	 * @return string Cache key.
	 */
	protected static function _get_cache_key($unique = '')
	{
		if (is_array($unique)) {
			ksort($unique);
			$unique = serialize($unique);
		}
		$md5 = md5($unique);
		$key = static::CACHE_KEY . $md5;
		return $key;
	}

	public static function get_author_display_names($post_id = 0)
	{
		$author_display_names = array();
		$authors              = static::get_authors($post_id);

		if (empty($authors)) {
			$author_display_names[] = 'Staff';
		} else {
			foreach ($authors as $author) {
				$author_display_names[] = $author->display_name;
			}
		}

		return $author_display_names;
	}

	public static function get_authors($post_id = 0)
	{
		if (!$post_id)
			return array();

		if (function_exists('get_coauthors')) {
			$authors = get_coauthors($post_id);
		} else {
			$post    = get_post($post_id);
			$author = get_userdata($post->post_author);
			if (empty($author->website)) {
				$author->website = !empty($author->user_url) ? $author->user_url : '';
			}
			$authors = array($author);
		}

		return $authors;
	}

	/**
	 * @static
	 *
	 * @param string $option
	 * @param string $feed_type
	 *
	 * @return mixed|string|void
	 * returns the content for the current post. The caller can specify to return the content{as is, with no html, with no image}
	 */
	public static function get_content($option = '', $feed_type = 'rss2')
	{
		switch ($option) {
			case 'nohtml':
				return strip_tags(get_the_content_feed($feed_type));
			case 'noimage':
				return self::remove_image_tag(get_the_content_feed($feed_type));
			default:
				return get_the_content_feed($feed_type);
		}
	}

	/**
	 * @static
	 *
	 * @param string $filter ['nohtml','noimage','read_more_label','']
	 *
	 * @return mixed|string|void
	 * returns the excerpt for the current post. The caller can specify to return the excerpt{as is, with no html, with no image,with a differnt readmore label }
	 */
	public static function get_excerpt($filter = '', $options = array())
	{

		$output = apply_filters('the_excerpt_rss', get_the_excerpt());

		switch ($filter) {
			case 'nohtml':
				return strip_tags($output);
			case 'noimage':
				return self::remove_image_tag($output);
			case 'read_more_label':
				if (empty($options) || empty($options['label'])) {
					return $output;
				}

				if (!empty($options['target']) && !preg_match('/<a.*?target=[^>]*?>read more/', $output)) {
					$output = preg_replace('/(<a)(.*?>)(read more)/i', '<a target="' . self::esc_xml($options['target'], 'attr') . '"${2}$3', $output);
				}

				$output = preg_replace('/(<a.*?>)(read more)/i', '${1}' . self::esc_xml($options['label'], 'html'), $output);

				return $output;
			default:
				return $output;
		}
	}

	/**
	 * @static
	 *
	 * @param $content
	 *
	 * @return mixed
	 * helper function for removing the image tags from the content provided.
	 */
	public static function remove_image_tag($content)
	{
		$content = preg_replace('/<img[^>]+\>/i', '', $content);
		// clean up empty tag after images are stripped
		return self::remove_empty_tags($content);
	}

	/**
	 * Helper function to remove empty tags
	 * @param string $content
	 * @return string
	 */
	public static function remove_empty_tags($content)
	{
		return preg_replace('/<(\w+)(?:\s[^>]*)?>\s*<\/\1>/', '', $content);;
	}

	/**
	 * @param        $postid
	 * @param string $node_name
	 * Outputs the image for the specified post ID with the image size set by the feed options.
	 */
	public static function get_image_specific_for_feed($postid, $node_name = '')
	{

		/* This filter already documented inside PMC_Custom_Feed_Helper::render_image_in_post() in same file. */
		$image_id = apply_filters('pmc_custom_feed_render_image_in_post_override', '', static::$_feed_options, get_post($postid), $node_name);

		if (empty($image_id)) {
			$image_id = get_post_thumbnail_id($postid);
		}

		if (empty($image_id)) {
			return;
		}

		$feed_image = PMC_Custom_Feed::get_instance()->get_feed_image_size();

		if (empty($feed_image)) {
			return;
		}

		if (is_array($feed_image)) {
			$image_size = array($feed_image['width'], $feed_image['height']);
		} else {
			$image_size = $feed_image;
		}

		$image_src = wp_get_attachment_image_src($image_id, $image_size);
		if (!empty($image_src)) {
			$url    = $image_src[0];
		}

		if (empty($url)) {
			//Getting attrs for thumbnail, since we are going to resize using photon/VIP resize.
			$img_attrs = PMC::get_attachment_attributes($image_id, "thumbnail", $postid);

			if (!empty($feed_image['width']) && function_exists('wpcom_vip_get_resized_remote_image_url')) {
				$url = wpcom_vip_get_resized_remote_image_url($img_attrs['src'], $feed_image['width'], $feed_image['height'], true);
			} else if (empty($feed_image)) {
				$url = $img_attrs['src'];
			}
		}

		if (empty($url)) {
			$url = plugins_url('default-images/default_custom_feed.jpg', __FILE__);
		}

		if (isset($node_name) && !empty($node_name)) {
			$node_name = sanitize_text_field($node_name);
			echo "<" . $node_name . ">";
			echo self::esc_xml($url, 'url');  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
			echo "</" . $node_name . ">";

			//if its a media enclosure and if image has credit info then put it out
			if (strpos($node_name, 'media') !== false) {
				self::render_image_credit_tag($image_id);
			}
		}
	}

	/**
	 * @static
	 *
	 * @param      $postid
	 * @param null $option
	 * @param      $numberposts
	 *
	 * @return array|bool
	 * helper method to a method that renders in a template.
	 */
	public static function get_image_from_attachment($postid, $option = null, $numberposts = 50)
	{
		if (!$postid) {
			return;
		}
		$image_attachments =  array();

		switch ($option) {
			case 'featured':
				if (has_post_thumbnail($postid)) {
					$image_id        = get_post_thumbnail_id($postid);
					$thumbnail_image = get_post($image_id);
					$image_attachments =  array($thumbnail_image);
				}
				break;

			default:
				$attachment_array = array(
					'post_parent'    => $postid,
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'numberposts'    => min($numberposts, 50),
					'order' 	     => 'ASC',
					'orderby'		 => 'menu_order ID'

				);

				//get all the image attachments
				$image_attachments = get_children($attachment_array);

				break;
		}
		foreach ($image_attachments as $ix => $image) {
			// NOTE: This overlaps with the pmc_custom_feed_thumbnail_gallery and pmc_custom_feed_thumbnail_image filters
			$image_attachments[$ix] = apply_filters('pmc_custom_feed_image_attachment', $image_attachments[$ix]);
		}

		return $image_attachments;
	}

	/**
	 * helper method to retrieve/populate img tag alt attr value (titles/captions/img credits)
	 * on certain feeds for front-end display
	 *
	 * @param $attachment object
	 * @param $feed_options array
	 *
	 * @return string
	 *
	 */
	public static function get_alt_value($attachment, $feed_options): string
	{

		if (
			!is_a($attachment, '\WP_Post') ||
			!is_array($feed_options)
		) {
			return '';
		}

		$image_credit  = get_post_meta($attachment->ID, '_image_credit', true);
		$image_credit  = !empty($image_credit) ? 'Credit: ' . $image_credit : '';
		$image_caption = '';

		// if only feed option featured-media-title checked, set $attachment post_title as image_caption
		// if both feed options, set $attachment post_excerpt as image_caption
		if (!empty($feed_options['featured-media-title'])) {
			$use_caption   = $feed_options['overwrite-featured-media-title-with-caption'];
			$image_caption = !empty($use_caption) ? $attachment->post_excerpt : $attachment->post_title;
		}

		// set alt attr data depending on value of image_caption and image_credit
		if (!empty($image_caption)) {
			$alt_value = !empty($image_credit) ? $image_caption . ' - ' . $image_credit : $image_caption;
		} else {
			$alt_value = !empty($image_credit) ? $image_credit : '';
		}

		return $alt_value;
	}

	/**
	 * @static
	 * @param $postid
	 * @param null $option
	 * @param int $numberposts
	 * @return array|mixed|string
	 * return the gallery images for the feed. Making sure the way the images are returned are the same on the front
	 * end and in the feed.
	 */
	public static function get_gallery_images($postid): array
	{
		$gallery_data = [];

		if (!empty($postid)) {
			$data = apply_filters('pmc_fetch_gallery', false, $postid);

			if (!empty($data) && is_array($data)) {
				$gallery_data = array_map(
					function ($item) {
						$keys = [
							'caption',
							'credit',
							'date',
							'ID',
							'image',
							'mime_type',
							'position',
							'slug',
							'title',
							'url',
						];

						$data = [
							'description' => '', // image's description is for internal use only, do not display
						];

						foreach ($keys as $key) {
							$data[$key] = isset($item[$key]) ? $item[$key] : '';
						}

						if (empty($data['credit']) && isset($item['image_credit'])) {
							$data['credit'] = $item['image_credit'];
						}

						if (!preg_match('/#/', $data['url'])) {
							$data['url'] = $data['url'] . '#!' . $data['position'] . '/' . $data['slug'];
						}

						return $data;
					},
					(array) $data
				);
			}
		}

		return (array) $gallery_data;
	}

	/**
	 * @static
	 *
	 * @param $postid
	 * @param $nodename
	 * helper method to a method that renders in a template. Render the featured image or the first image attachment and if that does not exist, render the default image for the feed
	 * default images live in the default-images folder.
	 */
	public static function render_featured_or_first_image_attachment($postid, $nodename, $size = 'thumbnail')
	{

		$thumbnail_image = self::get_image_from_attachment($postid, 'featured');

		if (!is_array($thumbnail_image) || empty($thumbnail_image)) {
			$thumbnail_image = self::get_image_from_attachment($postid, null, 1);
		}

		if (is_array($thumbnail_image) && 0 < count($thumbnail_image)) {
			$nodename     = sanitize_text_field($nodename);
			$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();

			foreach ($thumbnail_image as $thumb_image) {
				if (empty($thumb_image)) {
					continue;
				}

				$thumb_image = apply_filters('pmc_custom_feed_thumbnail_image', $thumb_image);
				$image_src   = wp_get_attachment_image_src($thumb_image->ID, $size);

				if (empty($image_src)) {
					continue;
				}

				$alt_value = self::get_alt_value($thumb_image, $feed_options);

				echo "<" . $nodename;
				echo ' src="' . self::esc_xml($image_src[0], 'url') . '"';  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
				if (empty($feed_options['remove-cont-img-width-height-attributes'])) {
					echo ' width="' . self::esc_xml(intval($image_src[1]), 'attr') . '"';  // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
					echo ' height="' . self::esc_xml(intval($image_src[2]), 'attr') . '"';  // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
				}
				echo ' type="' . self::esc_xml($thumb_image->post_mime_type, 'attr') . '"';  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
				echo ' title="' . self::esc_xml($thumb_image->post_title, 'attr') . '"';  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
				if (!empty($feed_options['featured-media-title']) && !empty($alt_value)) {
					echo ' alt="' . self::esc_xml($alt_value, 'attr') . '"'; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
				}
				echo ' />';

				//if its a media enclosure and if image has credit info then put it out
				if (strpos($nodename, 'media') !== false) {
					self::render_image_credit_tag($thumb_image->ID);
				}

				break;
			}
		} else {
			$pmc_img_url = PMC_Custom_Feed::get_instance()->get_feed_image_src();
			if (!empty($pmc_img_url)) {
				echo '<img src="' . self::esc_xml($pmc_img_url, 'url') . '" title="default"/>';  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
			}
		}
	}

	/**
	 * @static
	 *
	 * @param $post_var
	 *
	 * @return mixed
	 * Function to render first image with anchortag in post if post is a Gallery
	 */
	public static function render_first_image_in_gallery($post_var, $size = 'thumbnail')
	{

		if (false === apply_filters('pmc_custom_feed_render_first_image_in_gallery', true, $post_var, $size)) {
			return;
		}

		preg_match_all('/\[gallery/i', $post_var->post_content, $matches);

		//if no gallery short code dont render image
		if (count($matches[0]) < 1) {
			return;
		}
		$thumbnail_image = self::get_image_from_attachment($post_var->ID, null, 1);

		if (is_array($thumbnail_image) && 0 < count($thumbnail_image)) {
			$post_url = apply_filters('the_permalink_rss', get_permalink());

			foreach ($thumbnail_image as $thumb_image) {
				// NOTE: This overlaps with the pmc_custom_feed_image_attachment filter
				$thumb_image = apply_filters('pmc_custom_feed_thumbnail_gallery', $thumb_image);
				$image_src   = wp_get_attachment_image_src($thumb_image->ID, $size);
				if (empty($image_src)) {
					continue;
				}
				echo "<a href='";
				echo self::esc_xml(self::pmc_feed_add_query_string($post_url, 'url'));  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
				echo "'><img";
				echo ' src="' . self::esc_xml($image_src[0], 'url') . '"';  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
				echo ' width="' . self::esc_xml($image_src[1], 'attr') . '"';  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
				echo ' height="' . self::esc_xml($image_src[2], 'attr') . '"';  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
				echo ' type="' . self::esc_xml($thumb_image->post_mime_type, 'attr') . '"';  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
				echo ' title="' . self::esc_xml($thumb_image->post_title, 'attr') . '"';  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
				echo ' /></a>';
				break;
			}
		}
	}

	/**
	 * @static
	 *
	 * @param $postid
	 * @param $nodename
	 * adds all images to the post. helper function.
	 */
	public static function render_all_image_in_post($postid, $nodename)
	{

		$image_attachments = self::get_image_from_attachment($postid);

		if (is_array($image_attachments)) {
			$nodename = sanitize_text_field($nodename);
			foreach ($image_attachments as $img_att) {
				$image_src = wp_get_attachment_image_src($img_att->ID);

				echo "<" . $nodename;
				if ('media:content' === $nodename) {
					echo ' url="' . self::esc_xml($image_src[0], 'url') . '"';  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
				} else {
					echo ' src="' . self::esc_xml($image_src[0], 'url') . '"';  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
				}
				echo ' width="' . self::esc_xml($image_src[1], 'attr') . '"';  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
				echo ' height="' . self::esc_xml($image_src[2], 'attr') . '"';  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
				echo ' type="' . self::esc_xml($img_att->post_mime_type, 'attr') . '"';  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()

				if ('media:content' != $nodename) {
					echo ' title="' . self::esc_xml($img_att->post_title, 'attr') . '"';  // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
				}

				echo ' />';

				if ('media:content' == $nodename) {
					echo '<media:description type="plain">' . self::esc_xml_cdata($img_att->post_title) . '</media:description>';
				}

				//if its a media enclosure and if image has credit info then put it out
				if (strpos($nodename, 'media') !== false) {
					self::render_image_credit_tag($img_att->ID);
				}
			}
		}
	}

	/**
	 * Wrapper function to echo the escaped xml cdata node
	 * This method is added to since echo esc_xml_cdata(); used pretty often and require the need of // WPCS: XXS ok comments
	 * @param $content
	 */
	public static function echo_safe_cdata($content)
	{
		echo self::esc_xml_cdata($content);  // WPCS: XSS ok;
	}

	/**
	 * Wrapper function to echo the escaped xml node
	 * This method is added to since echo esc_xml(); used pretty often and require the need of // WPCS: XXS ok comments
	 * @param $content
	 */
	public static function echo_safe_xml($content)
	{
		echo static::esc_xml($content); // WPCS: XSS ok;
	}

	/**
	 * @static
	 *
	 * @param $content string
	 *
	 * @return string
	 * method to render xml CDATA node
	 */
	public static function esc_xml_cdata($content)
	{
		$content = apply_filters('pmc_custom_feed_cdata', $content);
		// IMPORTANT: can't use esc_html here.  This data need to be raw as it is placed inside cdata node: <![CDATA[%s]]>
		// We can't have closing tag ]]> so we need to escape them to prevent raw data from breaking in cdata node.
		$content = str_replace(']]>', ']]&gt;', $content);
		return '<![CDATA[' . $content . ']]>';
	}

	/**
	 * @static
	 *
	 * @param $content string
	 * @param $type string     html|url|attr - backward compatible only, does not apply to xml strict rule
	 *
	 * @return string
	 * method to do data escaping for xml node and allow filter to modify content
	 */
	public static function esc_xml($content, $type = null)
	{
		$content = apply_filters('pmc_custom_feed_data', $content);

		// escape only 5 predefined entity references in XML: https://www.w3schools.com/xml/xml_syntax.asp
		// xml feed should comply with xml encoding rule sets, default to false for backward compatible for now
		// add filter to action 'pmc_custom_feed_start', eg. add_filter( 'pmc_custom_feed_esc_xml_strict', '__return_true' );
		if (true === apply_filters('pmc_custom_feed_esc_xml_strict', false)) {

			// we need to decode html entities first
			$content = html_entity_decode($content, ENT_QUOTES);

			$content = strtr(
				$content,
				array(
					'&' => '&amp;',
					'<' => '&lt;',
					'>' => '&gt;',
					'"' => '&quot;',
				)
			);

			if (true === apply_filters('pmc_custom_feed_esc_xml_strict_numeric_entity', true)) {
				$content = self::encode_numericentity($content);
			}

			return $content;
		}

		switch ($type) {
			case 'attr':
				return esc_attr($content);
			case 'url':
				return esc_url($content);
		}

		return esc_html($content);
	}

	public static function encode_numericentity($content)
	{
		$convmap = array(0x80, 0xffff, 0, 0xffff);
		$content = ent2ncr($content);
		$content = mb_encode_numericentity($content, $convmap, 'UTF-8');
		return $content;
	}

	/**
	 * @static
	 *
	 * @param      $post_var
	 * @param      $nodename
	 * @param null $option
	 *
	 * @return mixed
	 * This method is called from the templates to populate the nodes.
	 */
	public static function render_image_in_post($post_var, $nodename, $option = null, $size = 'thumbnail')
	{

		if (false === apply_filters('pmc_custom_feed_render_image_in_post', true, $post_var, $nodename, $option, $size)) {
			return;
		}

		$feed_options = (!empty(static::$_feed_options) && is_array(static::$_feed_options)) ? static::$_feed_options : [];

		if (!empty($feed_options) && $feed_options['strip-all-images']) {
			return;
		}

		if ('featuredorfirst' === $option) {
			/**
			 * Filter to display any custom post thumbnail image in feed.
			 *
			 * @ticket CDWE-167
			 * @version 2017-02-09 Chandra Patel
			 *
			 * @param array $_feed_options Array of feed options.
			 * @param object $post_var Object of post.
			 * @param string $tag A tag name appear in feed.
			 */
			$post_thumbnail_id = apply_filters('pmc_custom_feed_render_image_in_post_override', '', static::$_feed_options, $post_var, 'content');

			if (!empty($post_thumbnail_id)) {
				$image_src = wp_get_attachment_image_src($post_thumbnail_id, $size);

				if (!empty($image_src)) {
					$thumb_image = get_post($post_thumbnail_id);

					printf(
						'<%s src="%s" width="%d" height="%d" type="%s" title="%s" />',
						self::esc_xml($nodename, 'attr'),
						self::esc_xml($image_src[0], 'url'),
						intval($image_src[1]),
						intval($image_src[2]),
						self::esc_xml($thumb_image->post_mime_type, 'attr'),
						self::esc_xml($thumb_image->post_title, 'attr')
					); // WPCS: XSS ok. Since escaping is handled using self::esc_xml()

					return;
				}
			}
		}

		if (!empty($feed_options['use-full-size-images']) && $feed_options['use-full-size-images']) {

			// Use the full size image unless the default was already overridden.
			if ('thumbnail' === $size) {
				$size = 'full';
			}
		}

		$render_all_img = true;

		switch ($option) {

			case 'checkgallery': //if gallery short tag render all images
				preg_match_all('/\[gallery/i', $post_var->post_content, $matches);

				if (count($matches[0]) < 1) {
					$render_all_img = false;
				}
				break;
			case 'featuredorfirst': //render one in following pref featured image-> first img attachement -> default
				$do_render_image = false;

				//check if post has featured image
				if (has_post_thumbnail($post_var->ID)) {
					$do_render_image = true;
				} else {
					// check if there is image present in post or not
					preg_match_all('/<img[^>]+\>/i', get_the_content_feed('rss2'), $matches);

					if (count($matches[0]) < 1) {
						$do_render_image = true;
					}
				}

				if ($do_render_image === true) {
					self::render_featured_or_first_image_attachment($post_var->ID, $nodename, $size);
				}

				unset($do_render_image);

				$render_all_img = false;
				break;
			default: //render all images
				$render_all_img = true;
		}

		if (false == $render_all_img)
			return;

		self::render_all_image_in_post($post_var->ID, $nodename);
	}

	/**
	 * @static
	 *
	 * @param      $postid
	 * @param bool $featuredonly
	 *
	 * @version    2018-06-19 Kelin Chauhan <kelin.chauhan@rtcamp.com> READS-1310.
	 *
	 * called from the templates for rendering data in nodes.
	 */
	public static function render_featured_or_first_image_in_post($postid, $featuredonly = true)
	{

		$image = self::get_featured_or_first_image_in_post($postid, $featuredonly);

		if (empty($image)) {
			return;
		}

		if (!empty($image['url'])) {

			$feed_config = PMC_Custom_Feed::get_instance()->get_feed_config();

			$caption = apply_filters('pmc_custom_feed_featured_image_caption', $image['caption'], $postid);

			// Use <media:content> tag for rendering images if the feed is for maz, READS-1310.
			if (!empty($feed_config['is-pmc-maz'])) {

				printf(
					'<media:content url="%s" medium="%s" /><media:description>%s</media:description>',
					self::esc_xml($image['url'], 'url'),
					'image',
					self::esc_xml_cdata(PMC::strip_control_characters($caption))
				); // WPCS: XSS ok.

			} else {

				printf(
					'<image>%s</image><media:description>%s</media:description>',
					self::esc_xml($image['url'], 'url'),
					self::esc_xml_cdata(PMC::strip_control_characters($caption))
				); // WPCS: XSS ok.

			}

			if (!empty($image['image_id'])) {
				self::render_image_credit_tag($image['image_id'], true);
			}
		}
	}

	/*
	 * called from to get the featured or first image
	 *
	 * @param      $postid
	 * @param bool $featuredonly
	 *
	 * @return Array
	 */
	public static function get_featured_or_first_image_in_post($postid, $featuredonly = true)
	{

		$image    = array();

		/* This filter already documented inside PMC_Custom_Feed_Helper::render_image_in_post() in same file. */
		$post_thumbnail_id = apply_filters('pmc_custom_feed_render_image_in_post_override', '', static::$_feed_options, get_post($postid), 'image');

		if (!empty($post_thumbnail_id)) {
			$image['image_id'] = $post_thumbnail_id;
		} else {
			$image['image_id'] = get_post_thumbnail_id($postid);
		}

		if (!empty($image['image_id'])) {
			$thumbnail_image = get_post($image['image_id']);

			if (!empty($thumbnail_image)) {
				$image['url'] = $thumbnail_image->guid;

				if ('' != $thumbnail_image->post_excerpt) {
					$image['caption'] = $thumbnail_image->post_excerpt;
				} else {
					$image['caption'] = $thumbnail_image->post_title;
				}
			}
		} else {
			if (!$featuredonly) {

				preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*\/>/i', get_the_content(), $matches);

				if (count($matches[0]) > 0) {
					$first_img       = $matches[0][0];
					$thumbnail_image = self::get_img_attr($first_img);

					$image['url']     = $thumbnail_image['src'];
					$image['caption'] = $thumbnail_image['title'];
				}
			}
		}

		return $image;
	}
	/**
	 * @static
	 *
	 * @param        $img
	 * @param string $array_attr
	 *
	 * @return array|bool
	 * helper function to a function that renders in the template.
	 */
	public static function get_img_attr($img, $array_attr = 'all')
	{
		if (empty($img)) {
			return false;
		}
		$array_attr = (empty($array_attr)) ? 'all' : $array_attr;

		//lets extract all attributes from img tag
		preg_match_all('/(\w+)\s*=\s*(?:(")(.*?)"|(\')(.*?)\')/s', $img, $matches);

		$array_img = array();

		foreach ($matches[1] as $key => $attr_name) {
			if ((!is_array($array_attr) && $array_attr == "all") || (in_array($attr_name, $array_attr))) {
				$array_img[$attr_name] = $matches[3][$key];
			}
		}

		return $array_img;
	}

	/*
	 * This method drops all specific filters we don't want affecting our feed.
	 */
	public static function clean_filters_for_feed()
	{

		remove_filter('the_content_feed', 'feed_changes_content_flipboard');
		remove_filter('the_excerpt_rss', 'feed_changes_description_flipboard');
		remove_filter('the_content', 'tvline_strip_image');

		if (function_exists('vip_remove_enhanced_feed_images')) {
			vip_remove_enhanced_feed_images();
			wpcom_vip_remove_feed_tracking_bug();
		}
		if (function_exists('youtube_link')) {
			add_filter('the_content', 'youtube_link', 1);
			add_filter('the_content_feed', 'youtube_link', 1);
		}

		add_filter('jetpack_shortcode_youtube_whitelist_user_agents', function () {
			return '.+';
		}, 999);
	}

	/*
	 * Add host specific filter
	 * Will need to call this function from feed template
	 */
	public static function add_filter_for_host()
	{

		//Remove h3 tag from hollywoodlife hence will check host here using this host array
		$host_array = array('hollywoodlife.com');
		$host       = parse_url(home_url(), PHP_URL_HOST);

		if (in_array($host, $host_array)) {
			add_filter('the_content', array(
				'PMC_Custom_Feed_Helper',
				'strip_h3_tag'
			));
		}
	}

	/**
	 * @static
	 *
	 * @param $content
	 *
	 * @return mixed
	 * Remove h3 tag from content
	 */
	public static function strip_h3_tag($content)
	{

		$content = preg_replace('#<h3[^>]*>(.*?)</h3>#iu', "$1", $content);

		return $content;
	}

	/**
	 * @static
	 * renders the related post to the current post.
	 */
	public static function render_related_posts($render_feed_link = false, $echo = true)
	{
		global $feed, $post;

		$back_up               = $post;
		$show_related          = PMC_Custom_Feed::get_instance()->get_feed_config('related');
		$related_html          = '';
		$related_links         = [];
		$surrounding_html_tags = [];

		if ('on' !== $show_related) {
			return;
		}

		if (class_exists('PMC\Automated_Related_Links\Plugin')) {
			$instance      = \PMC\Automated_Related_Links\Plugin::get_instance();
			$related_links = $instance->get_related_links(get_the_ID());
		}

		if ((empty($related_links) || !is_array($related_links)) && function_exists('pmc_related_articles')) {
			$related_posts = pmc_related_articles(get_the_ID());
			$related_posts = (!empty($related_posts) && is_array($related_posts)) ? $related_posts : [];

			foreach ($related_posts as $item) {
				$related_links[] = [
					'id'        => $item->post_id,
					'title'     => $item->title,
					'url'       => get_permalink($item->post_id),
					'automated' => true,
				];
			}
		}

		/**
		 * Filters for overriding the related links in the feed.
		 *
		 * @param array $related_links An array of related links.
		 */
		$related_links = apply_filters('pmc_render_related_posts_related_links', $related_links);

		if (!empty($related_links) && is_array($related_links)) {

			$related_links = array_slice($related_links, 0, 3);

			$surrounding_html_tags['before'] = sprintf('<div><div><strong>More from %s</strong></div>', get_bloginfo('name'));
			$surrounding_html_tags['after']  = '</div>';

			/**
			 * Filters surrounding html tags for rendering related posts in feed.
			 *
			 * @param array $surrounding_html_tags An array containing html tags for rendering related posts.
			 * [
			 *     'before' => '<div><div><strong>More from Variety</strong></div>,
			 *     'after'  => '</div>',
			 * ];
			 */
			$surrounding_html_tags = apply_filters('pmc_custom_feed_related_posts_surrounding_html', $surrounding_html_tags);

			$related_html .= $surrounding_html_tags['before'] . '<ul>';

			foreach ($related_links as $item) {
				if ($render_feed_link) {
					$item['url'] = trailingslashit(home_url()) . PMC_Custom_Feed::rewrite_slug . '/' . $feed . '/fpid/' . $item['id'] . '/';
				}

				$related_html .= sprintf(
					"<li><a href='%s'>%s</a></li>",
					self::esc_xml(self::pmc_feed_add_query_string($item['url']), 'url'),
					self::esc_xml($item['title'], 'attr')
				);
			}

			$related_html .= '</ul>' . $surrounding_html_tags['after'];

			if ($echo) {
				echo wp_kses_post($related_html);
			} else {
				return $related_html;
			}
		}

		$post = $back_up;
	}

	/**
	 * @static
	 * renders static html from options.
	 */
	public static function render_html_from_options($echo = true)
	{

		$html = PMC_Custom_Feed::get_instance()->get_feed_config("html");
		if (!empty($html)) {
			if ($echo)
				echo wp_kses_post($html);
			else
				return wp_kses_post($html);
		}
	}

	/**
	 * @static
	 * Short code handling function, based on allowed list all other shortcodes
	 * will return empty
	 */
	public static function handle_shortcode_tag_for_feed()
	{
		global $shortcode_tags;

		$shortcode_array = array(
			'abc',
			'abcnews',
			'aol',
			'avi',
			'blip.tv',
			'bliptv',
			'bloomberg',
			'brightcove',
			'cbs',
			'cnbc',
			'comedycentral',
			'dailymotion',
			'espn',
			'flash',
			'flickr',
			'flickrvideo',
			'flv',
			'foxnews',
			'funnyordie',
			'googlevideo',
			'gvideo',
			'hulu',
			'ifilm',
			'metacafe',
			'mpeg',
			'msnbc',
			'myspace',
			'nbc',
			'pmc_iframe',
			'theplatform',
			'ooyala',
			'quicktime',
			'spike',
			'stage6',
			'starz',
			'ted',
			'teamcoco',
			'theview',
			'usa',
			'veoh',
			'viddler',
			'video',
			'videofile',
			'vimeo',
			'vodpod',
			'wmv',
			'wpvideo',
			'yahoo',
			'youtube',
			'pmc-related-link',
			'buy-now',
		);

		$shortcode_array = apply_filters('pmc_custom_feed_allow_shortcodes', $shortcode_array);

		// @TODO: To be removed
		$shortcode_array = apply_filters('pmc_custom_feed_whitelist_shortcodes', $shortcode_array);

		foreach (array_keys($shortcode_tags) as $tag) {
			if (!in_array($tag, $shortcode_array, true)) {
				add_shortcode($tag, '__return_null');
			}
		}
	}

	/**
	 * @static
	 * renders the variety video URL post meta if it exists.
	 */
	public static function  render_variety_video_url()
	{
		$linked_data = get_post_meta(get_the_ID(), '_variety-video-url', true);
		if (isset($linked_data) && !empty($linked_data)) {

			return '<p><a href="' . self::esc_xml($linked_data, 'url') . '">Watch Video</a></p>';
		}
	}

	/**
	 * This function accepts feed slug to match against and current URI. If
	 * current URI is not passed then it tries to get it from $_SERVER. If the
	 * current URI is of a feed and matches the feed slug passed to this function
	 * then it returns TRUE else FALSE.
	 *
	 * @since 2013-05-03 Amit Gupta
	 */
	public static function is_current_feed($feed_slug, $current_uri = '')
	{
		if (empty($current_uri)) {
			$current_uri = parse_url($_SERVER['REQUEST_URI']);
			$current_uri = (isset($current_uri['path'])) ? $current_uri['path'] : '';
		}
		$current_uri = trim($current_uri, '/');

		if (empty($feed_slug) || empty($current_uri)) {
			return false;
		}

		$current_uri = explode('/', $current_uri);
		$feed_prefix = array_shift($current_uri);
		$current_uri = implode('/', $current_uri);

		if ($feed_prefix == PMC_Custom_Feed::rewrite_slug && $feed_slug == $current_uri) {
			return true;
		}

		return false;
	}

	/**
	 * This function validates the token passed for the current feed in the URI
	 * to authenticate access if current feed requires a token. This prevents
	 * unauthorized access to a feed.
	 *
	 * @since 2013-05-03 Amit Gupta
	 */
	public static function is_current_feed_auth($feed_slug, $feed_token = '')
	{
		if (empty($feed_slug) || empty($feed_token)) {
			//feed slug is empty or feed doesn't require token
			return true;
		}

		//If current URI is not for this feed then bail out
		if (!self::is_current_feed($feed_slug)) {
			return true;
		}

		$current_token = '';
		if (isset($_GET['token']) && !empty($_GET['token'])) {
			$current_token = sanitize_title($_GET['token']);
		}

		if (empty($current_token) || $current_token !== $feed_token) {
			return false;
		}

		return true;
	}

	public static function invalid_date($date)
	{
		return empty($date) || strtotime($date) === false;
	}

	/**
	 * This function, called on 'pmc_custom_feed_start', disables
	 * PMC Tag Links plugin on certain feed templates
	 *
	 * @since 2013-08-14 Amit Gupta
	 */
	public static function disable_pmc_tag_links($feed, $feed_options, $template_name)
	{
		//template names on which PMC Tag Links is to be disabled
		$templates = array(
			'feed-iphone-app.php',
			'feed-iosapp-variety-featured-carousel.php',
			'feed-iosapp-variety-second-stage.php',
		);

		if (!in_array($template_name, $templates)) {
			return;
		}

		//disable PMC Tag Links for now
		add_filter('pmc_tag_links_enabled', function ($enabled) {
			return false;
		});
	}

	/**
	 * This function, called on 'pmc_custom_feed_end', enables
	 * PMC Tag Links plugin
	 *
	 * @since 2013-08-14 Amit Gupta
	 */
	public static function enable_pmc_tag_links($feed, $feed_options, $template_name)
	{
		//enable PMC Tag Links for now
		add_filter('pmc_tag_links_enabled', function ($enabled) {
			return true;
		});
	}

	/**
	 * Comscore feed tracking code.
	 */
	public static function get_feed_tracking()
	{

		$feed_class = self::get_instance();

		if (!empty($feed_class->_feed_tracking_data)) {
			return $feed_class->_feed_tracking_data;
		}

		$tracking_images = array('https://sb.scorecardresearch.com/p?c1=2&c2=6035310&c3=&c4=&c5=&c6=&c15=&cv=2.0&cj=1');

		$tracking_images = apply_filters('pmc_custom_feed_tracking_images', $tracking_images);

		$feed_class->_feed_tracking_data = '';

		if (is_array($tracking_images)) {
			foreach ($tracking_images as $image_url) {
				$feed_class->_feed_tracking_data .= '<img src="' . self::esc_xml($image_url, 'url') . '"/>';
			}
		}

		return $feed_class->_feed_tracking_data;
	}

	public static function get_image_url_specific_for_feed($postid, $backup_size = "thumbnail")
	{

		$image_id = get_post_thumbnail_id($postid);

		if (empty($image_id)) {
			return;
		}

		$feed_image = PMC_Custom_Feed::get_instance()->get_feed_image_size();

		if (isset($feed_image['width'])) {
			$img_attrs = PMC::get_attachment_attributes($image_id, "full", $postid);
			$url = wpcom_vip_get_resized_remote_image_url($img_attrs['src'], $feed_image['width'], $feed_image['height'], true);
		} else {
			$img_attrs = PMC::get_attachment_attributes($image_id, $backup_size, $postid);
			$url = wpcom_vip_get_resized_remote_image_url($img_attrs['src'], $img_attrs['width'], $img_attrs['height'], true);
		}

		return $url;
	}

	/**
	 * Print the <category> tags
	 *
	 * @param bool $include_type_attribute Include the <category type="foo"> attribute.
	 *
	 * @return null
	 */
	public static function the_category_rss($include_type_attribute = true)
	{

		$tax_list   = array();

		if (isset(static::$_feed_options['replace-categories-with-site-name']) && static::$_feed_options['replace-categories-with-site-name'] === true) {
			$tax_list = array(
				get_bloginfo('name') => 'category',
			);
		} else {
			$categories = get_the_category();
			$tags       = get_the_tags();

			if (!empty($tags)) {
				foreach ((array) $tags as $tag) {
					$tag_name            = sanitize_term_field('name', $tag->name, $tag->term_id, 'post_tag', 'rss');
					$tax_list[$tag_name] = 'tag';
				}
			}

			if (!empty($categories)) {
				foreach ((array) $categories as $category) {
					$cat_name            = sanitize_term_field('name', $category->name, $category->term_id, 'category', 'rss');
					$tax_list[$cat_name] = $category->taxonomy;
				}
			}

			$tax_list = apply_filters('pmc_custom_feed_the_category_rss', $tax_list);
		}

		$output_safe = '';

		if (!empty($tax_list)) {

			foreach ($tax_list as $name => $taxonomy) {

				/*
				 * The type="" attribute is not part of the RSS specification
				 * I'm unsure where that originated from, but making it optional
				 * here. Pass false to this function to withhold the type="" attribute
				 */
				if ($include_type_attribute) {
					$output_safe .= sprintf(
						"\t\t<category type='%s'>",
						self::esc_xml($taxonomy, 'attr')
					);
				} else {
					$output_safe .= "\t\t<category>";
				}

				$output_safe .= self::esc_xml_cdata(html_entity_decode($name, ENT_COMPAT, get_option('blog_charset')));

				$output_safe .= "</category>\n";
			}
		}

		echo $output_safe;
	}

	public static function render_post_link()
	{
		global $post;

		return '<p><a href="' . self::esc_xml(get_permalink($post->ID), 'url') . '">Click here to view the embedded video.</a></p>';
	}

	/**
	 * Render the RSS <author> or <dc:creator> tag
	 *
	 * The <author> tag is only meant to contain the authors email address
	 * which isn't always something we want to send around the web. As such,
	 * the Dublin Core standard allows for using a <dc:creator> node instead
	 * which contains just the author name. NOTE: if using the dc:creator
	 * tag make sure you're template's <rss> node contains the Dublin Core
	 * standard: xmlns:dc="http://purl.org/dc/elements/1.1/"
	 *
	 * @param int|WP_Post  $post          The current $post object being displayed
	 * @param bool         $use_dccreator Pass true to use the <dc:creator> tag instead of <author>
	 *
	 * @return null
	 */
	public static function render_rss_author($post = 0, $use_dccreator = false)
	{
		$post = get_post($post);

		if (empty($post)) {
			return;
		}

		$authors = self::get_authors($post->ID);
		if (empty($authors)) {
			return;
		}

		$author = reset($authors);
		unset($authors);

		if (empty($author)) {
			return;
		}

		if (empty($author->website)) {
			$author->website = !empty($author->user_url) ? $author->user_url : '';
		}

		if ($use_dccreator) {
			$rss_author = sprintf(
				'<dc:creator>%s</dc:creator>',
				self::esc_xml($author->display_name)
			);
		} else {
			$rss_author = sprintf(
				'<author><name>%1$s</name><uri>%2$s</uri></author>',
				self::esc_xml($author->display_name),
				self::esc_xml($author->website)
			);
		}
		$rss_author = apply_filters('pmc_custom_feed_rss_author', $rss_author, $author, $post);
		echo $rss_author;
	}

	public static function render_feed_title()
	{
		$title = get_bloginfo_rss('name') . get_wp_title_rss('&#187;');
		$title = html_entity_decode($title, ENT_QUOTES);
		$title = apply_filters('pmc_custom_feed_title', $title);
		printf('<title>%s</title>', self::esc_xml($title));
	}

	public static function render_post_title($tag = 'title')
	{
		$title = get_the_title_rss();
		$title = html_entity_decode($title, ENT_QUOTES);
		$title = apply_filters('pmc_custom_feed_post_title', $title);
		printf('<title>%s</title>', self::esc_xml($title));
	}

	public static function render_attr($filter_name = '')
	{
		$attrs = apply_filters('pmc_custom_feed_attr_' . $filter_name, array());
		if (is_array($attrs)) {
			foreach ($attrs as $key => $value) {
				printf(' %s="%s"', sanitize_file_name($key), self::esc_xml($value));
			}
		}
	}

	/**
	 * Render <media:content> node.
	 *
	 * Don't render if image does not have media credit
	 *
	 * @version 2017-05-16 CDWE-304 Also render <media:credit> and <media:title> nodes.
	 *
	 * @param int $post The Post ID.
	 */
	public static function render_media_content($post = 0, $image_size = 'thumbnail')
	{

		$post = get_post($post);

		$additional_attributes = '';

		if (empty($post)) {
			return;
		}

		$thumbnail_id = get_post_thumbnail_id($post->ID);

		if (empty($thumbnail_id)) {
			return;
		}

		$image = wp_get_attachment_image_src($thumbnail_id, $image_size);

		if (empty($image[0])) {
			return;
		}

		$media_credit = static::render_image_credit_tag($thumbnail_id, false);

		if (empty($media_credit)) {
			return;
		}

		$media_title = static::render_media_title($post->ID, false);

		$img_url                = $image[0];
		$additional_attributes .= (!empty($image[1]) && !empty($image[2])) ? ' width="' . self::esc_xml($image[1]) . '" height="' . self::esc_xml($image[2]) . '"' : '';

		unset($image);
		unset($thumbnail_id);

		// NOTE: This overlaps with the pmc_custom_feed_image_attachment filter
		$img_url = apply_filters('pmc_custom_feed_thumbnail_image_url', $img_url, $post);
		$ext = strtolower(pathinfo(parse_url($img_url, PHP_URL_PATH), PATHINFO_EXTENSION));
		unset($post);

		switch ($ext) {
			case '.gif':
				$mine_type = 'image/gif';
				break;
			case '.png':
				$mine_type = 'image/png';
				break;
			default:
				$mine_type = 'image/jpeg';
				break;
		}

		printf(
			'<media:content url="%1$s" type="%2$s" %3$s>%4$s</media:content>',
			self::esc_xml($img_url),
			$mine_type,
			$additional_attributes, // Escaped already to avoid escaping doublt quotes.
			$media_title . $media_credit
		); // WPCS: XSS ok. Since escaping is handled using self::esc_xml().

	}

	public static function render_media_credit($post = 0)
	{
		$post = get_post($post);

		if (empty($post)) {
			return;
		}

		$thumbnail_id = get_post_thumbnail_id($post->ID);

		if (empty($thumbnail_id)) {
			return;
		}

		self::render_image_credit_tag($thumbnail_id);
	}

	public static function render_media_title($post = 0, $echo = true)
	{

		$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();

		if (empty($feed_options['featured-media-title'])) {
			return;
		}

		$post = get_post($post);

		if (empty($post)) {
			return;
		}

		$thumbnail_id = get_post_thumbnail_id($post->ID);

		if (empty($thumbnail_id)) {
			return;
		}

		$image_id = intval($thumbnail_id);

		if ($image_id < 1) {
			return;
		}
		$img_post    = get_post($image_id);

		/**
		 * If custom feed option is selected than
		 * use Image caption in <media:title/> instead of image title.
		 */
		if (!empty($feed_options['overwrite-featured-media-title-with-caption']) && !empty($img_post->post_excerpt)) {
			$image_title = $img_post->post_excerpt;
		} else {
			$image_title = $img_post->post_title;
		}

		if (!empty($image_title)) {

			$image_title = apply_filters('pmc_custom_feed_image_title', $image_title, $img_post);

			if (empty($feed_options['msfeed'])) {
				$image_title = self::esc_xml($image_title);
			} else {
				// Strip all tags and decode special chars if 'msfeed' feed option selected.
				$image_title = self::esc_xml(wp_strip_all_tags($image_title));
			}

			$image_title_tag = sprintf(
				'<media:title>%s</media:title>',
				$image_title
			);

			if ($echo !== true) {
				return $image_title_tag;
			}

			echo $image_title_tag;
		}
	}

	public static function render_msn_excerpt($tag = 'description')
	{
		$content = apply_filters('the_excerpt_rss', get_the_excerpt());
		$content = html_entity_decode($content, ENT_QUOTES);
		printf('<%1$s>%2$s</%1$s>', self::esc_xml($tag), self::esc_xml_cdata($content)); // WPCS: XSS ok. Since escaping is handled using self::esc_xml()
	}

	public static function render_atom_excerpt($tag = 'summary')
	{
		$content = apply_filters('the_excerpt_rss', get_the_excerpt());
		$tag = apply_filters('pmc_custom_feed_atom_excerpt_tag', $tag);
		$content = html_entity_decode($content, ENT_QUOTES);
		printf('<%1$s type="html">%2$s</%1$s>', sanitize_text_field($tag), self::esc_xml_cdata($content));
	}

	public static function render_post_content($tag = 'content:encoded', $template = '')
	{
		global $feed, $post;
		$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();

		$content = apply_filters('the_content', PMC::strip_shortcodes(get_the_content()));
		$content = apply_filters('the_content_feed', $content, 'rss2');
		$content = apply_filters('pmc_custom_feed_content', $content, $feed, $post, $feed_options, $template);

		$content = html_entity_decode($content, ENT_QUOTES);
		printf('<%1$s>%2$s</%1$s>', sanitize_text_field($tag), self::esc_xml_cdata($content));
	}

	/**
	 *
	 * @codeCoverageIgnore -- Unit test covered in PMCEED-1646
	 */
	public static function render_atom_content($tag = 'content', $template)
	{
		global $feed, $post;
		$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();

		$content = apply_filters('the_content', PMC::strip_shortcodes(get_the_content()));
		$content = apply_filters('the_content_feed', $content, 'rss2');
		$content = apply_filters('pmc_custom_feed_content', $content, $feed, $post, $feed_options, $template);

		$content = html_entity_decode($content, ENT_QUOTES);

		$content = '<div xmlns="http://www.w3.org/1999/xhtml">' . $content . '</div>';
		printf('<%1$s type="xhtml" xml:lang="en" xml:base="http://diveintomark.org/">%2$s</%1$s>', sanitize_text_field($tag), self::esc_xml_cdata($content)); // phpcs:ignore

	}

	private static function _get_term_names($taxonomy, $post = 0)
	{
		$post = get_post($post);
		$terms = get_the_terms($post->ID, $taxonomy);
		$names = array();

		if (!empty($terms) && !is_wp_error($terms)) {
			foreach ($terms as $term) {
				$names[] = sanitize_text_field($term->name);
			}
		}

		return $names;
	}

	public static function render_atom_category($tag = 'atom:category')
	{

		$cat_names = array();

		$cat_names = array_merge(
			$cat_names,
			self::_get_term_names('vertical'),
			self::_get_term_names('category'),
			self::_get_term_names('post_tag')
		);

		$cat_names = apply_filters('pmc_custom_feed_category', $cat_names);

		if (is_array($cat_names)) {
			$tag = sanitize_text_field($tag);
			foreach ($cat_names as $cat_name) {
				// note: $tag need to be sanitized; done before loop for optimization
				printf('<%1$s term="%2$s" />', $tag, self::esc_xml($cat_name));
			}
		}
	}

	/**
	 * This function accepts a <enclosure> node with malformed URL caused by improper
	 * [flv] shortcode parsing and returns a cleaned up <enclosure> node.
	 *
	 * @since 2014-08-05 Amit Gupta
	 */
	public static function get_clean_enclosure($enclosure_node)
	{
		if (empty($enclosure_node) || !is_string($enclosure_node)) {
			//enclosure node is empty/messed up (real bad, beyond hope)
			return $enclosure_node;
		}

		$pattern = '/<enclosure\s+url="(.*?)"\s+length="(.*?)"\s+type="(.*?)"\s*\/>/';

		if (!preg_match($pattern, $enclosure_node, $matches)) {
			return $enclosure_node;
		}

		$url = explode(']', $matches[1]);
		$url = array_pop($url);

		if (intval($matches[2]) < 1) {
			$headers = wp_get_http_headers($url);
			$len = isset($headers['content-length']) ? (int) $headers['content-length'] : 0;

			unset($headers);
		} else {
			$len = intval($matches[2]);
		}

		$enclosure_node = sprintf('<enclosure url="%s" length="%d" type="%s"/>' . "\n", $url, $len, $matches[3]);

		unset($len, $url, $pattern);

		return $enclosure_node;
	}

	/**
	 * Hooked to 'rss_enclosure' filter, this function checks if the media URL in <enclosure> node
	 * is malformed (due to incorrect parsing of [flv] shortcode) or not. If it is then it fixes
	 * the media URL and adds correct media length to the <enclosure> node.
	 *
	 * @since 2014-08-04 Amit Gupta
	 * @version 2014-08-05 Amit Gupta
	 */
	public static function rss_enclosure($enclosure_node)
	{
		if (empty($enclosure_node) || !is_string($enclosure_node) || strpos($enclosure_node, ']') === false) {
			//enclosure node is empty/messed up (real bad, beyond hope)
			//or its alright, return as is & bail out
			return $enclosure_node;
		}

		static $_enclosures = array();	//local static var to track enclosure nodes for every post in current feed

		$post_id = get_the_ID();
		$original_node = $enclosure_node;
		$original_node_md5 = md5($original_node);

		//check if this enclosure node has already been created for this post or not
		if (!empty($_enclosures[$post_id]) && in_array($original_node_md5, $_enclosures[$post_id])) {
			//already done this enclosure for this post, bail out
			return '';
		}

		$pmc_cache = new PMC_Cache(PMC_Custom_Feed::cache_key . $original_node_md5);

		$enclosure_node = $pmc_cache->expires_in(300)	//5 minutes
			->updates_with(array(get_called_class(), 'get_clean_enclosure'), array($original_node))
			->get();

		//record this enclosure as done for the current post to avoid duplicates
		$_enclosures[$post_id][] = $original_node_md5;

		unset($pmc_cache, $original_node_md5, $original_node);

		return $enclosure_node;
	} // function

	public static function render_rss_namespace($posts = false, $feed_options = false)
	{
		if ($namespaces = apply_filters('pmc_custom_feed_rss_namespace', false, $posts, $feed_options)) {
			foreach ($namespaces as $key => $value) {
				printf(' xmlns:%s="%s" ', sanitize_file_name($key), self::esc_xml($value));
			}
		}
	}
	/**
	 * @param $post
	 * @param $post_options
	 * strip the related link shortcode from the post when the option is set.
	 */
	public static function pmc_custom_feed_strip_related_link_shortcode($post, $post_options)
	{

		if (isset($post_options['strip_related_links']) && $post_options['strip_related_links'] == true) {

			add_filter('the_content', function ($post_content) {
				$post_content = PMC::strip_shortcodes($post_content, array('pmc-related-link'));
				return $post_content;
			}, 10, 1);
		}


		return $post;
	}


	public static function get_image_detail($id, $default_attr = array())
	{
		$post = get_post($id);
		if (empty($post)) {
			return array();
		}
		$terms = wp_get_post_terms($post->ID, 'post_tag', array('fields' => 'names'));
		if (!empty($terms) && !is_wp_error($terms)) {
			$keywords = implode(',', $terms);
		}
		unset($terms);
		$link = array_merge($default_attr, array(
			'id'           => $post->ID,
			'guid'         => $post->ID,
			'url'          => wp_get_attachment_url($id, 'full', false),
			'caption'      => $post->post_excerpt,
			'description'  => $post->post_content,
			'title'        => $post->post_title,
			'modified'     => $post->post_modified,
			'modified_gmt' => $post->post_modified_gmt,
			'keywords'     => !empty($keywords) ? $keywords : '',
		));
		return apply_filters('pmc_custom_feed_image_detail', $link, $post);
	}

	public static function get_link_detail($id, $default_attr = array())
	{
		$post = get_post($id);

		if (empty($post)) {
			return array();
		}

		$terms = wp_get_post_terms($post->ID, 'post_tag', array('fields' => 'names'));

		if (!empty($terms) && !is_wp_error($terms)) {
			$keywords = implode(',', $terms);
		}

		unset($terms);

		$link = array_merge($default_attr, array(
			'id'           => $post->ID,
			'guid'         => add_query_arg('p', $post->ID, trailingslashit(get_home_url())),
			'url'          => get_permalink($post->ID),
			'title'        => $post->post_title,
			'modified'     => $post->post_modified,
			'modified_gmt' => $post->post_modified_gmt,
			'published'     => $post->post_date,
			'published_gmt' => $post->post_date_gmt,
			'keywords'     => !empty($keywords) ? $keywords : '',
		));

		return apply_filters('pmc_custom_feed_link_detail', $link, $post);
	}

	/**
	 * Extract image links within <img> tag from post content
	 * @param object $post The post object, note: $post->content maybe be modified if $strip_tag == true
	 * @param boolean $strip_tag Optional: if true, will strip the <img> tags from post content
	 * @return array The array of data @see get_image_detail
	 */
	public static function extract_images($post, $strip_tag = false)
	{

		if (empty($post) || empty($post->post_content)) {
			return array();
		}

		$ids = array();
		$images = array();

		// extract images from html <img> tag
		$post->post_content = preg_replace_callback('/<img\s+([^>]+)\/?>/is', function ($matches) use (&$images, $strip_tag) {

			if (!is_array($images)) {
				$images = array();
			}

			// extracting tag attributes
			$found = preg_match_all('/(\w+)\s*=\s*([\'"])([^\'"]+)\\2/is', $matches[1], $all_matches);

			if ($found > 0) {
				$image = array('type' => 'inline');

				// loop throught each attributes to find the attribute we need to keep
				for ($i = 0; $i < $found; $i++) {

					switch (strtolower($all_matches[1][$i])) {
						case 'src':
							$image['url'] = $all_matches[3][$i];
							$id = self::url_to_postid($image['url']);
							if (!empty($id)) {
								$image = self::get_image_detail($id, $image);
								break 2;
							}
							break;
						case 'alt':
							$image['caption'] = $all_matches[3][$i];
							break;
					} // switch

				} // for

				// valid link?
				if (!empty($image['url'])) {

					// detecting relative url, eg: /link... or link...
					if (
						'//' !== mb_substr($image['url'], 0, 2) // link didn't start with //
						&& '' == parse_url($image['url'], PHP_URL_HOST) // link that has no host
					) {
						// relative isn't useful in feed, so translate into full url via home_url function.
						// eg. /link... -> https://domain.com/link...
						$image['url'] = home_url($image['url']);
					} // if

					$images[$image['url']] = $image;
				}
			} // if found

			return $strip_tag ? '' : $matches[0];
		}, $post->post_content);

		// extract images from embeded gallery
		$post->post_content = preg_replace_callback('/\[gallery\s+ids\s*=\s*([\'"]?)([^\'"]+)\1\s*\]/is', function ($matches) use (&$ids, $strip_tag) {
			$ids = array_merge(is_array($ids) ? $ids : array(), explode(',', str_replace(' ', '', $matches[2])));
			return $strip_tag ? '' : $matches[0];
		}, $post->post_content);

		if (!empty($ids)) {

			foreach ($ids as $id) {
				$image = self::get_image_detail($id, array('type' => 'gallery'));

				// valid link?
				// gallery images should have full url, but just in case
				if (!empty($image['url'])) {

					// detecting relative url, eg: /link... or link...
					if (
						'//' !== mb_substr($image['url'], 0, 2) // link didn't start with //
						&& '' == parse_url($image['url'], PHP_URL_HOST) // link that has no host
					) {
						// relative isn't useful in feed, so translate into full url via home_url function.
						// eg. /link... -> https://domain.com/link...
						$image['url'] = home_url($image['url']);
					} // if

					$images[$image['url']] = $image;
				} // if

			} // foreach

		} // if ! empty

		// need to remove any empty tags after <img> are extracted
		$post->post_content = PMC_Custom_Feed_Helper::remove_empty_tags($post->post_content);

		return $images;
	} // function extract_images

	/**
	 * Extract url links within <a> tag from post content
	 * @param object $post The post object, note: $post->content maybe be modified if $strip_tag == true
	 * @param boolean $strip_tag Optional: if true, will strip the <a> tags from post content
	 * @return array The array of link data @see get_link_detail
	 */
	public static function extract_links($post, $strip_tag = false)
	{

		if (empty($post) || empty($post->post_content)) {
			return array();
		}

		$links = array();

		$post->post_content = preg_replace_callback(
			'/<a\s+([^>]+)>(.*?)<\/a>/is',
			// callback function for preg_replace_callback
			function ($matches) use (&$links, $strip_tag) {
				if (!is_array($links)) {
					$links = array();
				}

				// extracting tag attributes
				$found = preg_match_all('/(\w+)\s*=\s*([\'"])([^\'"]+)\\2/is', $matches[1], $all_matches);

				if ($found > 0) {

					// the url link text
					$link = array('type' => 'inline', 'caption' => $matches[2]);

					// loop throught each attributes to find the attribute we need to keep
					for ($i = 0; $i < $found; $i++) {
						switch (strtolower($all_matches[1][$i])) {
							case 'href':
								$link['url'] = $all_matches[3][$i];
								$id = self::url_to_postid($link['url']);
								if (!empty($id)) {
									$link = self::get_link_detail($id, $link);
									break 2;
								}
								break;
							case 'title':
								$link['title'] = $all_matches[3][$i];
								break;
						}
					} // for

					// only keep valid link
					if (!empty($link['url'])) {

						// detecting relative url, eg: /link... or link...
						if (
							'//' !== mb_substr($link['url'], 0, 2) // link didn't start with //
							&& '' == parse_url($link['url'], PHP_URL_HOST) // link that has no host
						) {
							// relative isn't useful in feed, so translate into full url via home_url function.
							// eg. /link... -> https://domain.com/link...
							$link['url'] = home_url($link['url']);
						}

						// make sure $links list is unique
						$links[$link['url']] = $link;
					} // if ! empty

				} // if found

				// retun only text portion of <a> tag if stripping tag, otherwise return original data.
				return $strip_tag ? " {$matches[2]} "  : $matches[0];
			}, // callback function
			$post->post_content
		);

		// need to remove any empty tags after <a> are extracted
		$post->post_content = PMC_Custom_Feed_Helper::remove_empty_tags($post->post_content);

		return $links;
	} // function extract_links

	/**
	 * Helper function to output the rss <media:*> node
	 * @see: http://www.rssboard.org/media-rss
	 */
	public static function render_common_media_nodes($data)
	{

		$common_names = array('caption', 'keywords', 'title', 'description');

		foreach ($common_names as $name) {
			if (empty($data[$name])) {
				continue;
			}
			printf('<media:%1$s>%2$s</media:%1$s>', tag_escape($name), self::esc_xml($data[$name]));
		}

		if (!empty($data['thumbnail'])) {
			printf('<media:thumbnail url="%s" />', self::esc_xml($data['thumbnail']));
		}

		if (!empty($data['location'])) {
			printf('<media:location description="%s" />', self::esc_xml($data['location']));
		}

		if (!empty($data['player'])) {
			printf('<media:player url="%s" />', self::esc_xml($data['player']));
		}
	}

	/**
	 * Helper function to output the rss <media:content> node
	 * @see: http://www.rssboard.org/media-rss
	 */
	public static function render_image_node($image)
	{
		// open tag
		printf('<media:content url="%s" medium="image"',  self::esc_xml($image['url']));

		if (!empty($image['guid'])) {
			printf(' guid="%s"', self::esc_xml($image['guid']));
		}

		if (!empty($image['modified_gmt'])) {
			printf(' last-modified="%s"', self::esc_xml(mysql2date('D, d M Y H:i:s +0000', $image['modified_gmt'])));
		}

		if (!empty($image['orig_modified_gmt'])) {
			printf(' original-last-modified="%s"', self::esc_xml(mysql2date('D, d M Y H:i:s +0000', $image['orig_modified_gmt'])));
		}

		if (!empty($image['is_featured'])) {
			printf(' featured="true"');
		}

		if (!empty($image['slide_order'])) {
			printf(' slide-order="%s"', self::esc_xml($image['slide_order']));
		}

		// close tag
		echo '>';

		self::render_common_media_nodes($image);

		if (!empty($image['id'])) {
			self::render_image_credit_tag($image['id']);
		}

		// end tag
		echo '</media:content>';
	}

	/**
	 * Helper function to output the rss <media:content> node
	 * @see: http://www.rssboard.org/media-rss
	 */
	public static function render_video_node($video)
	{
		// open tag
		printf('<media:content medium="video"');

		if (!empty($video['duration'])) {
			printf(' duration="%d"', $video['duration']);
		}

		if (!empty($video['size'])) {
			printf(' fileSize="%d"', $video['size']);
		}

		if ('youtube' == $video['type']) {
			printf(' type="video/youtube"');
		} else {
			printf(' url="%s" type="%s"', self::esc_xml($video['url']), PMC_Custom_Feed_Helper::esc_xml($video['type']));
		}

		// close tag
		echo '>';

		if ('youtube' == $video['type']) {
			$video['player'] = $video['url'];
			unset($video['url']);
		}

		self::render_common_media_nodes($video);

		// end tag
		echo '</media:content>';
	} // function

	/**
	 * Helper function to translate url into postid with caching implementation
	 * @see url_to_postid & attachment_url_to_postid
	 */
	public static function url_to_postid($url)
	{
		// extract file extension
		$ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

		// call attachment_url_to_postid if url is an image
		if (in_array($ext, array('png', 'jpg', 'gif', 'jpeg'))) {
			// Swapped uncached Core function for VIP's cached equivalent.
			$post_id = wpcom_vip_attachment_url_to_postid($url); // @codeCoverageIgnore
		}

		if (empty($post_id)) {
			// Swapped uncached Core function for VIP's cached equivalent.
			$post_id = url_to_postid($url); // @codeCoverageIgnore
		}

		return $post_id;
	}

	/**
	 * Called by 'the_category_rss' filter, this function replaces <category> nodes in feed
	 * with a single node containing site name as stored in site options if 'Replace Categories with Site Name' feed
	 * option for current feed is enabled.
	 *
	 * @ticket PPT-4971
	 * @since 2015-06-23 Amit Gupta
	 *
	 * @param string $the_list <category> nodes for the current feed item
	 * @param string $type Optional, default is 'rss2'.
	 * @return string All of the post categories or site name for display in the feed.
	 */
	public static function replace_categories_with_site_name($the_list, $type = 'rss2')
	{
		if (!isset(static::$_feed_options['replace-categories-with-site-name']) || static::$_feed_options['replace-categories-with-site-name'] !== true) {
			return $the_list;
		}

		$site_name = get_bloginfo('name');

		switch (strtolower($type)) {
			case 'rdf':
				$the_list = sprintf("\t\t<dc:subject>%s</dc:subject>\n", self::esc_xml_cdata($site_name));
				break;
			case 'atom':
				$the_list = sprintf('<category scheme="%1$s" term="%2$s" />', self::esc_xml(get_bloginfo_rss('url'), 'attr'), self::esc_xml($site_name, 'attr'));
				break;
			default:
				$the_list = sprintf("\t\t<category>%s</category>\n", self::esc_xml_cdata(html_entity_decode($site_name, ENT_COMPAT, get_option('blog_charset'))));
				break;
		}

		return $the_list;
	}

	/**
	 * Called on 'pmc_custom_feed_title' filter, this function sets the feed
	 * title to only site name if the 'Only Site Name in Feed Title' option
	 * is selected.
	 *
	 * @ticket PPT-5005
	 * @since 2015-07-30 Amit Gupta
	 *
	 * @param string $title
	 * @return string
	 */
	public static function maybe_only_site_name_as_title($title)
	{
		if (!isset(static::$_feed_options['feed-title-site-name']) || static::$_feed_options['feed-title-site-name'] !== true) {
			return $title;
		}

		return get_bloginfo_rss('name');
	}

	/**
	 * This function outputs the image sub-node for channel node containing the site gravatar
	 *
	 * @ticket PPT-5005
	 * @since 2015-07-30 Amit Gupta
	 *
	 * @return void
	 */
	public static function maybe_render_feed_logo()
	{
		if (!isset(static::$_feed_options['add-feed-logo']) || static::$_feed_options['add-feed-logo'] !== true) {
			return;
		}

		if (!function_exists('blavatar_exists') || !blavatar_exists(blavatar_current_domain())) {
			return;
		}
?>
		<image>
			<url><?php echo self::esc_xml(blavatar_url(blavatar_current_domain(), 'img', 144), 'url');  // WPCS: XSS ok. Since escaping is handled using self::esc_xml() 
					?></url>
			<title><?php bloginfo_rss('name'); ?></title>
			<link><?php bloginfo_rss('url'); ?></link>
		</image>
<?php
	}

	/**
	 * Identify media links in the given $post object
	 *
	 * Inspect the post's content to extract and remove video URLs
	 * the extracted urls will then be rendered as <media:content> nodes in the feed
	 *
	 * Run post content through WP_Embed::run_shortcode & WP_Embed::autoembed
	 *
	 * @see extract_embed_links() method below
	 *
	 * @param  WP_Post $post The current $post being displayed in our feed
	 *
	 * @return WP_Post       The *possibly* modified $post object
	 */
	public static function capture_media_links_within_post_content($post, $feed_options)
	{

		if (!is_a($post, 'WP_Post')) {
			return false;
		}

		// Only proceed if we haven't already processed embed links for this post
		if (!empty($GLOBALS['wp_embed']) && empty($post->embed_links)) {

			$class = get_called_class();

			// Add some filters to capture the oembed links from the post
			add_filter('embed_handler_html', array($class, 'filter_extract_embed_links_from_post'), 10, 2);
			add_filter('embed_oembed_html', array($class, 'filter_extract_embed_links_from_post'), 10, 2);
			add_filter('embed_maybe_make_link', array($class, 'filter_extract_embed_links_from_post'), 10, 2);

			// Reset the our internal array of embed links
			// to prevent carry over from processing previous posts
			PMC_Custom_Feed_Helper::$_post_content_media_links = array();

			// Make an temporary copy of the post content
			$content = $post->post_content;

			// Process all shortcodes and autoembed using WP_Embed
			// Below in our filter_extract_embed_links_from_post() method
			// we hook into WP_Embed, which is called when we run the two
			// functions below. That filter_extract_embed_links_from_post() method filters
			// the post content, extracts any shortcode or autoemebed URLs
			// from the post content, and stores them in PMC_Custom_Feed_Helper::$_post_content_media_links.
			// This is how we remove the URL's from content and place them within <media:content> nodes
			$content = $GLOBALS['wp_embed']->run_shortcode($content);
			$content = $GLOBALS['wp_embed']->autoembed($content);

			// Update the current $post object with our processed post content
			$post->post_content = trim($content);

			// Create a variable on the current $post object
			// which contains the post's embed links. We'll
			// grab these when creating the <media:content> nodes
			// to create nodes for each embed link.
			$post->embed_links = PMC_Custom_Feed_Helper::$_post_content_media_links;

			// Remove the oembed capturing filters now that we're done with them
			remove_filter('embed_handler_html', array($class, 'filter_extract_embed_links_from_post'), 10);
			remove_filter('embed_oembed_html', array($class, 'filter_extract_embed_links_from_post'), 10);
			remove_filter('embed_maybe_make_link', array($class, 'filter_extract_embed_links_from_post'), 10);
		}

		return $post;
	}

	/**
	 * Filter WP_Embed to extract embed links in post_content
	 *
	 * @see WP_Embed::run_shortcode & WP_Embed::autoembed
	 *
	 * @param  string $html A post's post_content HTML containing embed links
	 * @param  string $url  The embed url link
	 *
	 * @return string       An empty string.
	 */
	public static function filter_extract_embed_links_from_post($html, $url)
	{

		// Store an internal reference of links for the current $post
		PMC_Custom_Feed_Helper::$_post_content_media_links[] = $url;

		// Remove the embed links from the post content by
		// returning an empty string for each link.
		return '';
	}

	/**
	 * Filter each <media:content> item and remove items with missing attribution
	 *
	 * @param array $media_item An array of the media:content attributes and sub-elements
	 *
	 * @return array An array of the *possibly* modified media:content attributes and sub-elements
	 */
	public static function filter_remove_media_content_items_wo_attribution($media_item = array())
	{

		// Some feeds require that each <media:content> item contain a copyright
		// if there is no copyright present do not include the item.
		if (empty($media_item['sub-elements']['media:copyright'])) {
			return array();
		}

		return $media_item;
	}

	/**
	 * Render the <media:content> tag(s) and their sub-elements
	 *
	 * @see http://www.rssboard.org/media-rss#media-content
	 *
	 * @param array       $feed_options An array of the current feed's options
	 * @param int|WP_Post $post         *optional* The current post to render tags for
	 * @param bool        $echo         Whether to echo or return the output. Default is false.
	 *
	 * @return void|bool|string
	 */
	public static function action_render_media_content_tags($feed_options = array(), $post = 0, $echo = true)
	{

		$post = get_post($post);

		if (empty($post)) {
			return false;
		}

		$media_items      = self::get_media_content_items($feed_options, $post);
		$media_tags_clean = self::_get_media_tags($media_items);

		if (!empty($media_tags_clean)) {
			if ($echo) {
				echo $media_tags_clean;  // WPCS: XSS ok.
			} else {
				return $media_tags_clean;
			}
		}
	}

	/**
	 * Render the <media:content> tag(s) and their sub-elements for gallery slides.
	 *
	 * @see http://www.rssboard.org/media-rss#media-content
	 *
	 * @param array       $feed_options An array of the current feed's options
	 * @param int|WP_Post $post         *optional* The current post to render tags for
	 * @param bool        $echo         Whether to echo or return the output. Default is false.
	 *
	 * @return void|bool|string
	 */
	public static function action_render_gallery_media_content_tags($feed_options = array(), $post = 0, $echo = true)
	{

		$post = get_post($post);

		if (empty($post) || 'pmc-gallery' !== $post->post_type) {
			return false;
		}

		$images      = PMC_Custom_Feed_Helper::get_gallery_images($post->ID);
		$media_items = [];

		// Extract media items from images.

		foreach ($images as $image) {
			$media_items[] = [
				'id'           => $image['ID'],
				'attributes'   => [
					'url'  => $image['image'],
					'type' => $image['mime_type'],
				],
				'sub-elements' => [
					'media:title'       => (!empty($image['title'])) ? $image['title'] : '',
					'media:copyright'   => (!empty($image['credit'])) ? $image['credit'] : '',
					'media:description' => (!empty($image['caption'])) ? $image['caption'] : '',
				],
			];
		}

		$media_tags_clean = self::_get_media_tags($media_items);

		if (!empty($media_tags_clean)) {
			if ($echo) {
				echo $media_tags_clean;  // WPCS: XSS ok.
			} else {
				return $media_tags_clean;
			}
		}
	}

	/**
	 * Builds the media tags for a given array of media items.
	 *
	 * @param array $media_items
	 * @return string
	 *
	 */
	protected static function _get_media_tags($media_items)
	{

		$media_tags_clean = '';

		if (is_array($media_items) && !empty($media_items)) {

			// Only render the <media:group> node where there are multiple sub-elements
			if (count($media_items) > 1) {
				$media_tags_clean .= "<media:group>\r\n";
			}

			// Loop through the media items and output individual <media:content> tags
			foreach ($media_items as $media) {

				if (empty($media)) {
					continue;
				}

				// Start the media:content tag
				$media_tags_clean .= "\t<media:content";

				// Loop through and build the media:content tag attributes
				if (is_array($media['attributes']) && !empty($media['attributes'])) {
					foreach ($media['attributes'] as $attribute_key => $attribute_value) {
						$media_tags_clean .= sprintf(
							' %s="%s"',
							sanitize_title($attribute_key),
							self::esc_xml($attribute_value, 'attr')
						);
					}
				}

				// Close the opening media:content tag
				$media_tags_clean .= ">\r\n";

				// Loop through and build the media:content sub-elements
				if (is_array($media['sub-elements']) && !empty($media['sub-elements'])) {
					foreach ($media['sub-elements'] as $sub_element_key => $sub_element_value) {

						/**
						 * sanitize_file_name() and sanitize_title() both strip out
						 * colons from the string, which we can't use with 'media:description'
						 * using sanitize_text_field() retains colons and seems to be the
						 * best option for escaping here.
						 */
						$media_tags_clean .= sprintf(
							"\t\t<%s>%s</%s>\r\n",
							sanitize_text_field($sub_element_key),
							self::esc_xml($sub_element_value),
							sanitize_text_field($sub_element_key)
						);
					}
				}

				// Close the media:content tag
				$media_tags_clean .= "\t</media:content>\r\n";
			}

			// Only render the <media:group> node where there are multiple sub-elements
			if (count($media_items) > 1) {
				$media_tags_clean .= "</media:group>";
			}

			/**
			 * $media_tags_clean now contains a string that looks like:
			 *
			 * <media:group>
			 *     <media:content
			 *         url="https://this.is.the.media.url"
			 *         type="the-media/mime-type">
			 *
			 *         <media:title>This is the media title</media:title>
			 *         <media:copyright>Copyright notice</media:copyright>
			 *         <media:description>This is the media description</media:description>
			 *     </media:content>
			 * </media:group>
			 */

			$media_tags_clean = apply_filters('pmc_custom_feed_media_content_tags', $media_tags_clean);
		}

		return $media_tags_clean;
	}

	/**
	 * Fetch a multi-dimensional array of media:content items
	 *
	 * @param array       $feed_options An array of the current feed's options
	 * @param int|WP_Post $post The current <item> post object or ID
	 *
	 * @return array|null An array on success, null on failure.
	 */
	public static function get_media_content_items($feed_options = array(), $post = 0)
	{

		$post = get_post($post);

		if (empty($post)) {
			return;
		}

		// Specify some default values
		$image_attachments = $image_attachment_ids = $media_items = array();
		$image_attachment_size = 'thumbnail';

		// Override the default image size if a size was
		// entered in the feed options
		if (!empty($feed_options) && is_array($feed_options)) {
			if (!empty($feed_options['image_size'])) {
				$image_attachment_size = $feed_options['image_size'];
			} else {
				if (!empty($feed_options['use-full-size-images'])) {
					$image_attachment_size = 'full';
				}
			}
		}

		// For now we'll just use the attached images
		// This could be expanded to fetch all other media types.
		// However, if that's done some additions will also need
		// to be made below, e.g. wp_get_attachment_image_src, width, height, etc.
		$image_attachments = get_attached_media('image', $post->ID);

		$image_attachment_ids = array_keys($image_attachments);

		// An image can be inserted in a post which it's not attached to
		// A post could contain 10 images, but if they're not attached
		// they won't be rendered as <media:content> items. At the very
		// least we'll check that the featured image is attached and if
		// it is not we'll add it to the array of attachments.
		$post_thumbnail_id = get_post_thumbnail_id($post->ID);

		if (!empty($post_thumbnail_id)) {
			if (!in_array($post_thumbnail_id, $image_attachment_ids)) {
				$image_attachments[$post_thumbnail_id] = get_post($post_thumbnail_id);
			}
		}

		if (is_array($image_attachments) && !empty($image_attachments)) {

			// Loop through the media items (attachment posts)
			// build an array of details for each item, which
			// will ultimately become individual <media:content> items
			foreach ($image_attachments as $attachment_id => $attachment_post) {

				$media_item = array();

				if (empty($attachment_id)) {
					continue;
				}

				$attachment_url = $attachment_width = $attachment_height = $attachment_mime_type = $attachment_title = $attachment_content = $attachment_copyright = '';

				// Allow the image size of each <media:content> item to be filtered
				// 'thumbnail' is the default
				$attachment_image_size = apply_filters('pmc_custom_feed_media_item_image_size', $image_attachment_size, $attachment_id, $attachment_post);

				$attachment_details = wp_get_attachment_image_src($attachment_id, $attachment_image_size);

				// Becuase these media items are for media
				// only proceed if we have a valid image url
				if (empty($attachment_details) || empty($attachment_details[0])) {
					continue;
				}

				$media_item = array(
					// The id is not used for the tag output,
					// it's presence is simply for use in the filters
					'id'           => $attachment_id,
					'attributes'   => array(
						'url' => $attachment_details[0],
					),
					'sub-elements' => array(),
				);

				// <media:content> attributes may be added here
				// @see http://www.rssboard.org/media-rss#media-content

				// Only specify a width if there is one
				if (!empty($attachment_details[1])) {
					$media_item['attributes']['width'] = $attachment_details[1];
				}

				// Only specify a height if there is one
				if (!empty($attachment_details[2])) {
					$media_item['attributes']['height'] = $attachment_details[2];
				}

				$attachment_mime_type = get_post_mime_type($attachment_id);
				if (!empty($attachment_mime_type)) {
					$media_item['attributes']['type'] = $attachment_mime_type;
				}

				// <media:content> sub-elements may be added here
				// e.g. <media:description>
				// @see http://www.rssboard.org/media-rss#optional-elements

				// Fetch and add the <media:title> sub-element
				$attachment_title = get_the_title($attachment_id);
				if (!empty($attachment_title)) {
					$media_item['sub-elements']['media:title'] = $attachment_title;
				}

				// Fetch and add the <media:description> sub-element
				// Apply the_content filter, strip HTML tags, and trim whitespace
				$attachment_content = trim(strip_tags(apply_filters('the_content', get_post_field('post_content', $attachment_id))));
				if (!empty($attachment_content)) {
					$media_item['sub-elements']['media:description'] = $attachment_content;
				}

				// Fetch and add the <media:copyright> sub-element
				$attachment_copyright = get_post_meta($attachment_id, '_image_credit', true);
				if (!empty($attachment_copyright)) {
					$media_item['sub-elements']['media:copyright'] = $attachment_copyright;
				}

				// Build an array of all the media items
				$media_items[] = $media_item;

				// Unset vars which will be reused in this loop to keep
				// them from using more memory than is needed.
				unset(
					$media_item,
					$attachment,
					$attachment_id,
					$attachment_mime_type,
					$attachment_title,
					$attachment_content,
					$attachment_copyright
				);
			}
		}

		// Grab the post's 'video_url' post meta if it exists
		$post_meta_video_url = get_post_meta($post->ID, 'video_url', true);

		// And add it onto the $post->embed_links array
		if (!empty($post_meta_video_url)) {
			if (is_array($post->embed_links) && !empty($post->embed_links)) {
				$post->embed_links[] = $post_meta_video_url;
			} else {
				$post->embed_links = array($post_meta_video_url);
			}
		}

		// Add any embed links stored in the $post object
		// to the array of feed media items.
		//
		// @see PMC_Custom_Feed_MSN->filter_pmc_custom_feed_post_start()
		//
		// That method extracts embed links from post content and places
		// them into this $post->embed_links variable
		if (is_array($post->embed_links) && !empty($post->embed_links)) {

			$post->embed_links = array_unique($post->embed_links);

			// Loop through each emebed link and create a media item
			foreach ($post->embed_links as $link) {

				// Embed links won't have an author the same way attachments do
				// but each <media:content> item requires a copyright.
				// This isn't a good approach, but for now we'll simply assign
				// the post author as the embed author.
				$authors = self::get_authors($post->ID);

				if (is_array($authors) && !empty($authors)) {
					$author = $authors[0]->data->user_nicename;
				}

				$media_items[] = array(
					'attributes' => array(
						'url' => $link,
					),
					'sub-elements' => array(
						'media:copyright' => $author,
					),
				);
			}
		}

		// Allow the array of media items to be filtered
		$media_items = apply_filters('pmc_custom_feed_media_items', $media_items);
		$cleaned_media_items = array();

		// Loop through each media item and filter them individually
		// Here we also remove any empty media items which may of been created
		foreach ($media_items as $i => $media_item) {

			// Allow the completed <media:content> attributes and sub-elements to be filtered
			$media_item = apply_filters('pmc_custom_feed_media_item', $media_item);

			// Ensure that only valid items are returned. If for example
			// in the MSN feed an item is removed for not having a credit
			// via the filter above, that we don't blindly add it onto the array.
			if (is_array($media_item) && !empty($media_item)) {
				$cleaned_media_items[] = $media_item;
			}
		}

		return $cleaned_media_items;
	}

	/**
	 * Fetch the current post's taxonomy terms
	 *
	 * @param null
	 *
	 * @return array An Array of taxonomy terms for the post
	 */
	public static function get_post_taxonomy_terms()
	{

		$cat_names = array();

		$taxonomies = array('vertical', 'category', 'post_tag');

		$taxonomies = apply_filters('pmc_custom_feed_post_taxonomies', $taxonomies);

		// Loop through the taxonomies and build an array of all the terms
		if (is_array($taxonomies) && !empty($taxonomies)) {
			foreach ($taxonomies as $taxonomy_name) {
				if (!empty($taxonomy_name)) {
					$cat_names = array_merge($cat_names, self::_get_term_names($taxonomy_name));
				}
			}
		}

		$cat_names = apply_filters('pmc_custom_feed_category', $cat_names);

		return $cat_names;
	}

	/**
	 * Output the <media:keywords> tag for the post
	 *
	 * ex: <media:keywords>keyword1, keyword2, keyword3</media:keywords>
	 *
	 * Run via pmc_custom_feed_item action
	 *
	 * @param bool $echo Prints the content by default. Pass false to return a string instead.
	 *
	 * @return null
	 */
	public static function render_media_keywords($echo = true)
	{

		$cat_names = self::get_post_taxonomy_terms();

		if (is_array($cat_names) && !empty($cat_names)) {

			$media_keywords_clean = sprintf(
				"<media:keywords>%s</media:keywords>",
				self::esc_xml_cdata(
					self::esc_xml(
						implode(', ', $cat_names)
					)
				)
			);

			if ($echo) {
				echo $media_keywords_clean;
			} else {
				return $media_keywords_clean;
			}
		}
	}

	/**
	 * Remove 'height' attribute and set width="100%" if iframe with YouTube video
	 *
	 * @since 2017-05-25 CDWE-370 Chandra Patel
	 *
	 * @param string $content The post content.
	 *
	 * @return string $content
	 */
	public static function modify_youtube_iframe($content)
	{

		if (empty($content) || false === strpos($content, 'youtube.com')) {
			return $content;
		}

		try {

			$doc = new DOMDocument();

			$doc->loadHTML($content, LIBXML_HTML_NODEFDTD);

			$iframe_list = $doc->getElementsByTagName('iframe');

			foreach ($iframe_list as $iframe) {

				$iframe_src = $iframe->getAttribute('src');

				if (false === strpos($iframe_src, 'youtube.com')) {
					continue;
				}

				// set the 'width' attribute.
				$iframe->setAttribute('width', '100%');

				// remove the 'height' attribute.
				$iframe->removeAttribute('height');
			}

			$filtered_content = $doc->saveHTML();

			if (!empty($filtered_content)) {

				$content = str_replace(
					array('<html>', '</html>', '<body>', '</body>'),
					'',
					$filtered_content
				);
			}
		} catch (Exception $e) {
			// Something goes wrong. So default content will return.
		}

		return $content;
	}

	/**
	 * Use post's SEO title if 'Use SEO Title' feed option checked and SEO title is set
	 *
	 * @since 2017-08-08 Chandra Patel CDWE-545
	 *
	 * @param string $title The current post title.
	 *
	 * @return string Return SEO title if it's set
	 */
	public static function filter_the_title_rss($title)
	{

		global $post;

		if (empty($post) || !is_a($post, 'WP_Post')) {
			return $title;
		}

		$seo_title = get_post_meta($post->ID, 'mt_seo_title', true);

		if (!empty($seo_title)) {
			return $seo_title;
		}

		return $title;
	}

	/**
	 * Truncate post content to 200 characters
	 *
	 * @since 2017-10-27 Chandra Patel CDWE-729
	 *
	 * @param string $content The post content.
	 *
	 * @return string
	 */
	public static function truncate_post_content($content)
	{

		if (empty($content)) {
			return $content;
		}

		$content = PMC::truncate($content, 200);

		return $content;
	}

	/**
	 * Function to decode unicode character.
	 *
	 * @since 2017-12-26 CDWE-891
	 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
	 *
	 * @param string $output post_excerpt with unicodes.
	 *
	 * @return string $output decoded post_excerpt.
	 */
	public static function convert_excerpt_html_entities($output)
	{

		$feed_options = PMC_Custom_Feed::get_instance()->get_feed_config();

		if (!empty($feed_options['convert-html-entities']) && true === $feed_options['convert-html-entities']) {
			return html_entity_decode($output, ENT_QUOTES);
		}

		return $output;
	}

	/**
	 * To add maz modified date in maz feed.
	 *
	 * @param  WP_Post $post Current post feed object.
	 * @param  array   $feed_options Feed options.
	 *
	 * @return void
	 */
	public static function feed_item($post, $feed_options)
	{

		if (empty($post) || empty($feed_options) || empty($feed_options['is-pmc-maz'])) {
			return;
		}

		$publish_date  = get_the_date('U', $post);
		$modified_date = get_the_modified_date('U', $post);

		if ($publish_date < $modified_date) {
			printf('<maz:modified>%s</maz:modified>', intval($modified_date));
		}
	}

	/**
	 * Function to parse the required shortcodes for feed.
	 *
	 * This function was defined in class-pmc-custom-feed-facebook-instant-articles.php and is moved here as a common function,
	 * and introduced a parameter ( $required_shortcodes ) to make it generic.
	 *
	 *
	 * @param  string $content             Post Content.
	 * @param  array  $feed_options        Feed Options.
	 * @param  array  $required_shortcodes The list of shortcodes to parse for the post. If set to true it will process all the shortcodes.
	 *
	 * @since 2015-12-03
	 *
	 * @version 2015-12-03 Archana Mandhare PMCVIP-411
	 * @version 2016-02-24 Archana Mandhare PMCVIP-905
	 * @version 2018-11-01 Kelin Chauhan <kelin.chauhan@rtcamp.com> PMCEED-852
	 *
	 * @return string $content             Post Content with parsed #required_shortcodes.
	 *
	 */
	public static function process_required_shortcodes($content, $feed_options, $required_shortcodes = true)
	{

		// Bail out if list of shortcodes to process is not provided or is passed as true.
		if (empty($required_shortcodes) || (!is_array($required_shortcodes) && true !== $required_shortcodes)) {
			return $content;
		}

		$orig_content = $content;
		$find         = [];
		$replace      = [];
		$video_width  = 320;
		$video_height = 180;

		if (!preg_match_all('/' . get_shortcode_regex() . '/s', $content, $matches)) {
			return $content;
		}

		for ($i = 0; $i < count($matches[2]); $i++) {
			$shortcode_name    = $matches[2][$i];
			$shortcode_content = $matches[0][$i];
			$shortcode_attrs   = $matches[3][$i];

			// If $required_shortcodes is passed as true then process all shortcodes in switch case block.
			if (true !== $required_shortcodes && (empty($shortcode_name) || !in_array($shortcode_name, (array) $required_shortcodes, true))) {
				continue;
			}

			switch ($shortcode_name) {

				case 'jwplayer':
				case 'jwplatform':
					$attrs = shortcode_parse_atts($shortcode_attrs);
					if (isset($attrs[0])) {
						$jw_player_id = trim($attrs[0]);
						$jw_player_id = trim($jw_player_id, "\xC2\xA0");

						if (!empty($jw_player_id)) {

							if (class_exists('PMC_Featured_Video_Override')) {
								$video_html = PMC_Featured_Video_Override::get_jwplayer_video_html5($jw_player_id, array(
									'width'  => $video_width,
									'height' => $video_height,
								));
							} else {

								// There is no way to simulate this due to class_exists for
								// @codeCoverageIgnoreStart
								if (class_exists('\PMC\JW_YT_Video_Migration\Post_Migration')) {

									$video_html = \PMC\JW_YT_Video_Migration\Post_Migration::get_instance()->output_shortcode(array($jw_player_id));

									if (!empty($video_html) && false === strpos($video_html, 'op-social')) {
										$video_html = '<figure class="op-social">' . $video_html . '</figure>';
									}
								}
								// @codeCoverageIgnoreEnd

							}

							if (empty($video_html)) {
								// There is no way to simulate this due to class_exists for
								// @codeCoverageIgnoreStart
								$jw_player_url = 'https://' . JWPLAYER_CONTENT_MASK . '/players/' . $jw_player_id . '.html';

								$video_html = sprintf(
									'<figure class="op-interactive"><iframe src="%s" width="%s" height="%s" frameborder="0" scrolling="auto"></iframe></figure>',
									self::esc_xml($jw_player_url, 'url'),
									self::esc_xml($video_width, 'attr'),
									self::esc_xml($video_height, 'attr')
								);
								// @codeCoverageIgnoreEnd
							}

							$find[]    = $shortcode_content;
							$replace[] = $video_html;
						}
						unset($jw_player_id);
					}
					break;

				case 'youtube':
					// Its a youtube video
					$video_html = do_shortcode($shortcode_content);
					$find[]     = $shortcode_content;

					if (false === strpos($video_html, 'op-social')) {
						$video_html = '<figure class="op-social">' . $video_html . '</figure>';
					}
					$replace[] = $video_html;

					break;

				case 'pmc-related-link':
				case 'buy-now':
				case 'caption':
					$shortcode_html = do_shortcode($shortcode_content);
					$find[]         = $shortcode_content;
					$replace[]      = $shortcode_html;
					break;

				case 'embed':
					global $wp_embed;
					if (empty($wp_embed)) {
						break;
					}

					$shortcode_html = $wp_embed->run_shortcode($shortcode_content);
					$find[]         = $shortcode_content;
					$replace[]      = $shortcode_html;
					break;

				case 'protected-iframe':
					// Its a protected-iframe shortcode

					if (!empty($feed_options['enable-protected-iframe-embeds'])) {
						$protected_iframe = do_shortcode($shortcode_content);
						$find[]           = $shortcode_content;
						$replace[]        = '<figure class="op-interactive"><iframe>' . $protected_iframe . '</iframe></figure>';
					}
					break;

				default:
					/**
					 * Allow themes to define their own output for shortcodes in feeds.
					 *
					 * @param mixed  $replace_with      Shortcode output to use.
					 * @param string $shortcode_content Original shortcode content.
					 * @param string $shortcode_name    Shortcode name.
					 */
					$replace_with = apply_filters(
						'pmc_process_required_shortcode_default',
						null,
						$shortcode_content,
						$shortcode_name
					);
					if (!is_null($replace_with)) {
						$find[]    = $shortcode_content;
						$replace[] = $replace_with;
					}
					break;
			}
		}

		if (empty($find) || empty($replace)) {
			return $content;
		}

		$content = str_replace($find, $replace, $content);

		if (!empty($content) && strlen($content) > 100) {
			return $content;
		} else {
			return $orig_content;
		}

		return $content;
	} // end of process_required_shortcodes().

} //end of class.

/*
 * Init class
 */
PMC_Custom_Feed_Helper::get_instance();

// EOF
