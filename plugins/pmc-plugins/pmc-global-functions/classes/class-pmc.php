<?php

use \PMC\Global_Functions\Utility\Device;

class PMC
{
	static private $_is_wp_cli = null;

	private function __construct()
	{
	}

	/**
	 * Truncates a string and puts an ellipse at the end.
	 * output return with balanceTags
	 *
	 * @version 2010-12-30 Satyanarayan Verma
	 * @version 2011-08-02 Adam Tourkow - Changed name to pmc_truncate
	 * @version 2011-12-13 Amit Gupta
	 * @version 2012-02-03 Gabriel Koen
	 * @version 2013-03-21 Miles Johnson - Replaced with a modified Titon.Utility/String::truncate()
	 * @version 2014-12-09 Gabriel Koen
	 */
	public static function truncate($string, $limit = 20, $append_type = 'ellipsis', $strip_html = false)
	{
		if ($strip_html) {
			$string = strip_tags($string);
		}

		$string = self::untexturize($string, 'html');

		$length = mb_strlen($string);

		if ($length <= $limit || !$limit) {
			return $string;
		}

		switch ($append_type) {
				// This is the actual default
			case 'ellipsis':
				$append = '&hellip;';
				break;

				// This is a catch-all for any undefined $append_type params that get passed
			default:
				$append = '';
				break;
		}

		// Generate tokens
		$open = '<';
		$close = '>';
		$tokens = array();
		$token = '';
		$i = 0;

		while ($i < $length) {
			$char = $string[$i];

			if ($char === $open || $char === '&') {
				$tokens[] = $token;
				$token = $char;
			} elseif ($char === $close || $char === ';') {
				$tokens[] = $token . $char;
				$token = '';
			} else {
				$token .= $char;
			}

			$i++;
		}

		$tokens[] = $token;

		// Rejoin any tags split between tokens, such as tags with `style` attributes that include `;`.
		foreach ($tokens as $i => $token) {
			if (
				str_starts_with($token, $open) &&
				!str_ends_with($token, $close)
			) {
				$offset = 1;

				while (
					!str_ends_with($tokens[$i], $close) &&
					(($i + $offset) < count($tokens))
				) {
					$next_index = $i + $offset;

					$tokens[$i]         .= $tokens[$next_index];
					$tokens[$next_index] = '';
					$offset++;
				}
			}
		}

		// Determine output
		$current = 0;
		$inHtml = false;
		$htmlPattern = '/\\' . $open . '\/?(?:.*?)\\' . $close . '/iSu';
		$entityPattern = '/&[a-z0-9]{2,8};|&#[0-9]{1,7};/iSu';
		$output = '';

		foreach ($tokens as $token) {
			// Increase limit by 1 for tokens
			if (preg_match($entityPattern, $token) && $current < $limit) {
				$current++;
				$output .= $token;

				// Increase limit by 0 for HTML tags but check for tag boundaries
			} else if (preg_match($htmlPattern, $token, $matches)) {
				$inHtml = (mb_substr($token, 0, 2) !== $open . '/');
				$output .= $token;

				// Regular string
			} else {
				$length = mb_strlen($token);

				if ($current >= $limit) {
					// Do nothing, we reached the limit

				} else if (($current + $length) >= $limit) {
					$allowed = ($limit - $current);
					$output .= mb_substr($token, 0, $allowed);
					$current += $allowed;
				} else {
					$output .= $token;
					$current += $length;
				}
			}

			// We done?
			if ($current >= $limit && !$inHtml) {
				break;
			}
		}

		$lastChar = mb_substr($output, -1);
		$lastTag = '';

		// Trim off a broken last word
		if ($lastChar !== ' ' && $lastChar !== $close && $lastChar !== ';') {
			$output = mb_substr($string, 0, mb_strrpos($output, ' '));

			// Remove the last HTML tag so we can still process
		} else if ($lastChar === $close) {
			$pos = mb_strrpos($output, $open);
			$lastTag = mb_substr($output, $pos, mb_strlen($output));
			$output = mb_substr($output, 0, $pos);
		}

		// Take care of any punctuations etc at the end of the string
		$punctuations = array(
			// html encoded entity should be first on the list
			"&rdquo;", "&ldquo;", "&hellip;", "&amp;", "&mdash;", "&ndash;", "&#8208;", "&#8209;", "&#8210;", "&#8211;", "&#8212;", "&#8213;",
			"&#8218;", "&#8226;", "&#8230;", "&#8242;", "&#8219;", "&#8220;", "&#8216;", "&#8245;", "&#8217;",
			"...", "..", ".", ",", "?", "!", ":", ";", "?", "-", "--", "(", "[", "{", "/", "&"
		);

		$output = trim($output);

		for ($i = 0; $i < count($punctuations); $i++) {
			$position = mb_strripos($output, $punctuations[$i]);

			// detecting punctuations at the end of string
			if ($position !== false && ($position == mb_strlen($output) - mb_strlen($punctuations[$i]))) {
				$output = mb_substr($output, 0, -1 * mb_strlen($punctuations[$i]));
			}

			unset($position);
		}

		return force_balance_tags(trim($output) . $append . $lastTag);
	}

	/**
	 * Enables the use of a custom CDN for displaying theme images, media library content and static assets.
	 * This is used to serve files off of an example.com subdomain to work around an isuse with Google News not supporting
	 * images served from CDNs on alternate domains.
	 *
	 * Ref: https://penskemediacorp.atlassian.net/browse/PPT-2045
	 * Ref: https://penskemediacorp.atlassian.net/browse/PPT-2049
	 * Ref: https://wordpressvip.zendesk.com/requests/26377
	 *
	 * DEVELOPER NOTES:
	 *  - To test off WPCOM you must have: define( 'IS_WPCOM', true );
	 *  - When implementing, wrap your tests with checks for WPCOM_IS_VIP_ENV to avoid breaking dev servers
	 *  - Custom CDNs do not support SSL: https://wordpressvip.zendesk.com/requests/30102
	 *
	 * @uses filter::pmc_custom_cdn_options
	 * @see wpcom_vip_load_custom_cdn() in vip/plugins/vip-do-not-include-on-wpcom/vip-permastructs.php
	 * @since 2014-05-21 Taylor Lovett
	 *
	 * @version 2014-06-06 Corey Gilmore Add additional documentation, mandatory SSL opt-in (#30102)
	 *
	 */
	public static function load_custom_cdn($cdn_options = array())
	{
		if (!function_exists('wpcom_vip_load_custom_cdn')) {
			return false;
		}

		// Custom CDNs do not support SSL: https://wordpressvip.zendesk.com/requests/30102
		// Sites must explicitly opt-in for a custom SSL CDN domain.
		// You probably shouldn't be doing this, and you need to talk with Corey.
		// Opt-in can be done with: add_filter( 'pmc_custom_cdn_ssl_opt_in', '__return_true' );
		if (PMC::is_https() && !apply_filters('pmc_custom_cdn_ssl_opt_in', '__return_false')) {
			return false;
		}

		// Default options from wpcom_vip_load_custom_cdn()
		$default_cdn_options = array(
			'cdn_host_media'   => '',
			'cdn_host_static'  => '',
			'include_admin'    => false,
		);

		$cdn_options = wp_parse_args($cdn_options, $default_cdn_options);
		$cdn_options = apply_filters('pmc_custom_cdn_options', $cdn_options);

		wpcom_vip_load_custom_cdn($cdn_options);
	}

	/**
	 * Convert unknown encoded string to utf8.
	 *
	 * @codeCoverageIgnore - Just wrapper function, tests are written for the underlying code already.
	 *
	 * @param string $ascii_string
	 *
	 * @return string
	 */
	public static function ascii_to_utf8($ascii_string): string
	{
		return \PMC\Global_Functions\Utility\Strings::get_instance()->ascii_to_utf8($ascii_string);
	}

	/**
	 * Convert fancy quotes/dashes to normal quotes/dashes
	 *
	 * @param string $text Text string in which fancy quotes/dashes etc are to be converted
	 * @param string $type The type in which fancy quotes/dashes etc are to be converted, ie., normal text or HTML. Defaults to Text.
	 *
	 * @since 2012-08-29 Amit Gupta
	 * @version 2013-10-04 Amit Gupta - added '&quot;' to conversion list in text mode in untexturize()
	 */
	public static function untexturize($text, $type = 'text')
	{
		if (empty($text)) {
			return $text;
		}

		//type can be either HTML or TEXT
		$type = (strtolower($type) == 'html') ? 'html' : 'text';

		$utf8_find = array(
			"\xe2\x80\x98", // single left curved quote
			"\xe2\x80\x99", // single right curved quote
			"\xe2\x80\x9c", // double left curved quote
			"\xe2\x80\x9d", // double right curved quote
			"\xe2\x80\x93", // endash
			"\xe2\x80\x94", // emdash
			"\xe2\x80\xa6", // ellipsis
		);

		$char_find = array(
			chr(145), // single left curved quote
			chr(146), // single right curved quote
			chr(147), // double left curved quote
			chr(148), // double right curved quote
			chr(150), // endash
			chr(151), // emdash
			chr(133), // ellipsis
		);

		$text_replace = array("'", "'", '"', '"', '-', '--', '...');
		if ('html' === $type) {
			$text_replace = array("'", "'", '"', '"', "&ndash;", "&mdash;", "&hellip;");
		}

		//do uft-8 replace
		$text = str_replace($utf8_find, $text_replace, $text);

		//do char replace
		// This replacement should not be applied to UTF-8 strings as it mangles multibyte characters.
		if ('UTF-8' !== mb_detect_encoding($text, ['ASCII', 'UTF-8'], true)) {
			$text = str_replace($char_find, $text_replace, $text);
		}

		if ('text' === $type) {
			$text = str_replace("&nbsp;", " ", $text);	//convert html char for space
			$text = str_replace(array('&#8216;', '&#8217;', '&lsquo;', '&rsquo;', '&#x2019;'), "'", $text);	//convert html entity for single quotes and apostrophe

			// Replace En Dash & Em Dash HTML entities with dashes
			$text = str_replace(
				['&#8211;', '&#8212;'],
				['-', '—'],
				$text
			);

			$text = html_entity_decode($text, ENT_QUOTES); //convert html entities to text
		}

		return $text;
	}

	/**
	 * Helper for injecting data into a specific place in a
	 * non-associative array
	 *
	 * @param mixed $value
	 * @param int $position
	 * @param array $array
	 *
	 * @return array
	 */
	public static function array_inject($value, $position, $array)
	{
		$position = ($position - 1);
		$top_half = array_slice($array, 0, $position, true);
		$bottom_half = array_slice($array, $position, count($array), true);
		$array = array_merge($top_half, (array) $value, $bottom_half);

		return $array;
	}

	/**
	 * Merges multiple arrays, recursively, and returns the merged array.
	 *
	 * Adapted from:
	 * https://github.com/drupal/drupal/blob/646584b3f718897179589f4f014531e3d9365331/core/lib/Drupal/Component/Utility/NestedArray.php#L324
	 *
	 * This function is similar to PHP's array_merge_recursive() function, but it
	 * handles non-array values differently. When merging values that are not both
	 * arrays, the latter value replaces the former rather than merging with it.
	 *
	 * Example:
	 * @code
	 * $link_options_1 = [ 'fragment' => 'x', 'attributes' => [ 'title' => 'X', 'class' => [ 'a', 'b' ] ] ];
	 * $link_options_2 = [ 'fragment' => 'y', 'attributes' => [ 'title' => 'Y', 'class' => [ 'c', 'd' ] ] ];
	 *
	 * // This results in [ 'fragment' => [ 'x', 'y' ], 'attributes' => [ 'title' => [ 'X', 'Y' ], 'class' => [ 'a', 'b', 'c', 'd' ] ] ].
	 * $incorrect = array_merge_recursive( $link_options_1, $link_options_2 );
	 *
	 * // This results in [ 'fragment' => 'y', 'attributes' => [ 'title' => 'Y', 'class' => [ 'a', 'b', 'c', 'd' ] ] ].
	 * $correct = PMC::array_merge_deep( [ $link_options_1, $link_options_2 ] );
	 * @endcode
	 *
	 * @param array $arrays
	 *   An arrays of arrays to merge.
	 *
	 * @param bool $preserve_integer_keys
	 *   (optional) If given, integer keys will be preserved and merged instead of
	 *   appended. Defaults to FALSE.
	 *
	 * @return array
	 *   The merged array.
	 */
	public static function array_merge_deep(array $arrays, bool $preserve_integer_keys = false): array
	{
		$result = [];
		foreach ($arrays as $array) {
			foreach ($array as $key => $value) {
				// Renumber integer keys as array_merge_recursive() does unless
				// $preserve_integer_keys is set to TRUE. Note that PHP automatically
				// converts array keys that are integer strings (e.g., '1') to integers.
				if (is_int($key) && !$preserve_integer_keys) {
					$result[] = $value;
				} elseif (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
					// Recurse when both values are arrays.
					$result[$key] = self::array_merge_deep([$result[$key], $value], $preserve_integer_keys);
				} else {
					// Otherwise, use the latter value, overriding any previous value.
					$result[$key] = $value;
				}
			}
		}
		return $result;
	}

	/**
	 * Outputs URL with Google CID data tags
	 *
	 * @param string $url
	 * @param string $primary Maps to utm_campaign
	 * @param string $secondary Maps to utm_medium
	 * @param string $tertiary Maps to utm_source
	 * @param int $slot Maps to utm_content, automatically prepended with "slot", e.g., "slot1"
	 * @param bool $echo Whether to output the anchor or just return it (defaults to false)
	 */
	function heatmap($url, $primary, $secondary, $tertiary, $slot, $echo = false)
	{
		$utm_campaign = sanitize_key($primary);
		$utm_medium = sanitize_key($secondary);
		$utm_source = sanitize_key($tertiary);
		$utm_content = (!empty($slot)) ? 'slot' . (int) $slot : '';

		$url = apply_filters('pmc_heatmap', $url . '#utm_campaign=' . $utm_campaign . '&utm_source=' . $utm_source . '&utm_medium=' . $utm_medium . '&utm_content=' . $utm_content, $url, $primary, $secondary, $tertiary, $slot, $echo);

		if ($echo)
			echo $url;

		return $url;
	}

	/**
	 * Returns a string with which "section" of the site you're on
	 *
	 * @see http://core.trac.wordpress.org/ticket/22110
	 *
	 * @return string
	 */
	public static function where_am_i()
	{
		global $wp_query;

		if (!isset($wp_query)) {
			_doing_it_wrong(__FUNCTION__, __('Conditional query tags do not work before the query is run. Before then, they always return false.'), '3.5');
			return null;
		}

		// Maintain an in-memory cache
		if (isset($GLOBALS['where_am_i'])) {
			return $GLOBALS['where_am_i'];
		}

		$GLOBALS['where_am_i'] = null;

		// Tried to maintain the hierarchy from query.php
		if (true === $wp_query->is_robots) {
			$GLOBALS['where_am_i'] = 'robots';
		} elseif (true === $wp_query->is_attachment) {
			$GLOBALS['where_am_i'] = 'attachment';
		} elseif (true === $wp_query->is_page) {
			$GLOBALS['where_am_i'] = 'page';
		} elseif (true === $wp_query->is_single) {
			$GLOBALS['where_am_i'] = 'single';
		} elseif (true === $wp_query->is_search) {
			$GLOBALS['where_am_i'] = 'search';
		} elseif (true === $wp_query->is_time) {
			$GLOBALS['where_am_i'] = 'time';
		} elseif (true === $wp_query->is_date) {
			$GLOBALS['where_am_i'] = 'date';
		} elseif (true === $wp_query->is_category) {
			$GLOBALS['where_am_i'] = 'category';
		} elseif (true === $wp_query->is_tag) {
			$GLOBALS['where_am_i'] = 'tag';
		} elseif (true === $wp_query->is_tax) {
			$GLOBALS['where_am_i'] = 'custom_taxonomy';
		} elseif (true === $wp_query->is_author) {
			$GLOBALS['where_am_i'] = 'author';
		} elseif (true === $wp_query->is_post_type_archive) {
			$GLOBALS['where_am_i'] = 'post_type';
		} elseif (true === $wp_query->is_feed) {
			$GLOBALS['where_am_i'] = 'feed';
		} elseif (true === $wp_query->is_trackback) {
			$GLOBALS['where_am_i'] = 'trackback';
		} elseif (true === $wp_query->is_admin) {
			$GLOBALS['where_am_i'] = 'admin';
		} elseif (true === $wp_query->is_404) {
			$GLOBALS['where_am_i'] = '404';
		} elseif (true === $wp_query->is_home) {
			$GLOBALS['where_am_i'] = 'home';
		}

		$GLOBALS['where_am_i'] = apply_filters('pmc_where_am_i', $GLOBALS['where_am_i']);

		return $GLOBALS['where_am_i'];
	}

	/**
	 * Returns a string with which "section" of the site you're on for ad targetting
	 *
	 * @return string
	 */
	public static function get_pagezone()
	{
		global $wp_query;

		$pagezone = '';

		if (is_home()) {
			$pagezone = 'home';
		} elseif (is_single()) {
			switch (get_post_type()) {
				case 'gallery':
				case 'pmc-gallery':
					$pagezone = 'gallery';
					break;
				case 'post':
					$pagezone = 'article';
					break;
				default:
					$pagezone = 'single-' . get_post_type();
					break;
			}
		} elseif (is_post_type_archive()) {
			switch (get_post_type()) {
				case 'gallery':
				case 'pmc-gallery':
					$pagezone = 'archive-gallery';
					break;
				case 'post':
					$pagezone = 'archive-article';
					break;
				default:
					$pagezone = 'archive-' . get_post_type();
					break;
			}
		} elseif (is_archive()) {
			$queried_object = get_queried_object();
			if (!empty($queried_object) && !empty($queried_object->taxonomy)) {
				switch ($queried_object->taxonomy) {
					case 'post_tag':
						$pagezone = 'tag';
						break;
					case 'category':
					case 'editorial':
					case 'category':
					case 'vertical':
						$pagezone = $queried_object->taxonomy;
						break;
					default:
						$pagezone = 'tax-' . $queried_object->taxonomy;
						break;
				}
			} elseif (is_author()) {
				$pagezone = 'author';
			}
		}

		if (empty($pagezone)) {
			$pagezone = PMC::where_am_i();
		}
		return apply_filters('pmc_pagezone', $pagezone);
	}


	/**
	 * Check if the device is a desktop based on user agent (if not tablet and not mobile, assume desktoip)
	 *
	 * @return bool
	 *
	 * @since 2014-06-03 Corey Gilmore
	 * @version  2021-05-26 Amit Gupta - Final version to make it an abstraction. There should be no more changes needed here after this.
	 *
	 * @codeCoverageIgnore Ignoring coverage here because this is just an abstraction for Device class method which has its own code coverage.
	 */
	public static function is_desktop()
	{
		return Device::get_instance()->is_desktop();
	}


	/**
	 * Check if the device is mobile, based on user agent
	 *
	 * @param string $kind   The kind of device to check for. Possible values: 'any, 'smart', or 'dumb'.
	 *
	 * @return bool
	 *
	 * @since 2014-06-03 Corey Gilmore
	 * @version  2021-05-26 Amit Gupta - Final version to make it an abstraction. There should be no more changes needed here after this.
	 *
	 * @codeCoverageIgnore Ignoring coverage here because this is just an abstraction for Device class method which has its own code coverage.
	 */
	public static function is_mobile($kind = 'any'): bool
	{
		return Device::get_instance()->is_mobile((string) $kind);
	}

	/**
	 * To check whether current device is a tablet or not
	 *
	 * @return bool
	 *
	 * @since 2012-12-21 Amit Gupta
	 * @version  2021-05-26 Amit Gupta - Final version to make it an abstraction. There should be no more changes needed here after this.
	 *
	 * @codeCoverageIgnore Ignoring coverage here because this is just an abstraction for Device class method which has its own code coverage.
	 */
	public static function is_tablet(): bool
	{
		return Device::get_instance()->is_tablet();
	}

	/**
	 * Check if the current device is an iPad.
	 *
	 * @return bool
	 *
	 * @version  2021-05-26 Amit Gupta - Final version to make it an abstraction. There should be no more changes needed here after this.
	 *
	 * @codeCoverageIgnore Ignoring coverage here because this is just an abstraction for Device class method which has its own code coverage.
	 */
	public static function is_ipad(): bool
	{
		return Device::get_instance()->is_ipad();
	}

	/**
	 * Method to check if current device is a bot or not
	 *
	 * @param string $type Optional Type of bot to be checked - values can include googlebot, googlebot-mobile, googlebot-news, msnbot, alexa, etc.
	 *
	 * @return bool
	 *
	 * @since  2021-05-26 Amit Gupta - Final version to make it an abstraction. There should be no more changes needed here after this.
	 *
	 * @codeCoverageIgnore Ignoring coverage here because this is just an abstraction for Device class method which has its own code coverage.
	 */
	public static function is_bot(string $type = 'any'): bool
	{
		return Device::get_instance()->is_bot($type);
	}

	/**
	 * Method to check if current request is for an AMP URL or not.
	 *
	 * @return boolean Returns TRUE if current request is for an AMP URL else FALSE
	 */
	public static function is_amp()
	{

		if (function_exists('is_amp_endpoint') && is_amp_endpoint()) {
			return true;
		}

		return false;
	}

	/**
	 * WP 3.5 doesn't update menu_order for ordering attachments, so this function updates the menu_order so that you can orderby menu_order
	 *
	 * @param int $id
	 *
	 * @since 2013-01-07 Vicky Biswas
	 */
	public static function gallery_menu_order_fix($post_id)
	{
		if (!isset($_POST['post_content'])) {
			return false;
		}
		$post = get_post($post_id);
		$regex_pattern = get_shortcode_regex();
		preg_match('/' . $regex_pattern . '/s', stripslashes($_POST['post_content']), $regex_matches);
		if (!$regex_matches) {
			return false;
		}
		if ($regex_matches[2] == 'gallery') {
			$attribure_str = str_replace(" ", "&", trim($regex_matches[3]));
			$attribure_str = str_replace('"', '', $attribure_str);
			$attributes = wp_parse_args($attribure_str);
		}

		if (empty($attributes['ids']))
			return;

		$ids = explode(',', $attributes['ids']);
		$images = get_posts(array(
			'post_parent' => $post->ID,
			'numberposts' => '-1',
			'post_status' => 'inherit',
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'post__in' => $ids,
		));
		if ($images) {
			foreach ($images as $attachment_id => $attachment) {
				$update_post = array();
				$update_post['ID'] = $attachment->ID;
				$update_post['menu_order'] = array_search($attachment->ID, $ids);
				wp_update_post($update_post);
			}
		}
	}

	/**
	 * Function to return a CSS class name if post object has an excerpt
	 *
	 * @param null|WP_Post $the_post Post object whose excerpt is to be checked else NULL if global post object is to be checked
	 * @return string Returns a CSS class name if excerpt found on post object else empty string
	 */
	public static function get_excerpt_class($the_post = null)
	{
		if (empty($the_post)) {
			$the_post = $GLOBALS['post'];
		}

		if (!empty($the_post->post_excerpt)) {
			return 'has-excerpt';
		}

		return '';
	}

	/**
	 * This function generates and returns excerpt for a post if
	 * it doesn't have it already.
	 *
	 * @deprecated
	 *
	 * @param int $post_id ID of the post for which excerpt is needed
	 *
	 * @since 2013-01-18 Amit Gupta
	 * @version 2014-12-10 Gabriel Koen
	 */
	public static function get_the_excerpt($post_id = 0)
	{
		$post_id = intval($post_id);
		if ($post_id < 1) {
			if (!isset($GLOBALS['post']) || !isset($GLOBALS['post']->ID) || intval($GLOBALS['post']->ID) < 1) {
				//no post ID passed as parameter and this function wasnt called on a post page or in loop, cant do anything
				return;
			}
			$post_id = intval($GLOBALS['post']->ID);
		}

		$the_post = get_post($post_id);
		if (empty($the_post)) {
			return;
		}

		//if there's an excerpt already then return that
		$excerpt = trim(apply_filters('get_the_excerpt', $the_post->post_excerpt));

		if (!empty($excerpt)) {
			return $excerpt;
		}

		if ($the_post->post_type !== 'attachment') {
			//no excerpt, so we do it the hard way
			$excerpt = $the_post->post_content;
		}

		if (empty($excerpt)) {
			//post content is also empty so we cant do anything further here, bail out
			return;
		}

		//strip out all shortcode tags, no use of them in excerpts
		//and apply the_content filter afterwards to catch any changes, beautification etc
		$excerpt = str_replace(']]>', ']]&gt;', apply_filters('the_content', strip_shortcodes($excerpt)));
		$excerpt_length = apply_filters('excerpt_length', 55);	//get the allowed length (in words) for excerpt

		//if excerpt is longer than allowed length then snip it (no suffix added)
		//and untexturize it
		$excerpt = self::untexturize(wp_trim_words($excerpt, $excerpt_length, ''));

		//use this custom stripper to strip unregistered & escaped shortcode tags if any
		$excerpt = preg_replace('/\[(\w*)(\s*)(.*)\](.*)\[\/\1\]/s', '-tag-stripped-', $excerpt);
		//second pass to catch leftovers of escaped tags
		$excerpt = str_replace(array('[-tag-stripped-]', '-tag-stripped-'), '', $excerpt);

		unset($excerpt_length, $the_post);	//cleanup

		return trim(apply_filters('get_the_excerpt', $excerpt));
	}

	/**
	 * xxxxxxxxx
	 * Generates an excerpt from the content, if needed.
	 *
	 * The excerpt word amount will be 55 words and if the amount is greater than
	 * that, then the string ' [&hellip;]' will be appended to the excerpt. If the string
	 * is less than 55 words, then the content will be returned as is.
	 *
	 * The 55 word limit can be modified by plugins/themes using the excerpt_length filter
	 * The ' [&hellip;]' string can be modified by plugins/themes using the excerpt_more filter
	 *
	 * Usage:
	 *
	 * Call:
	 * <code>
	 *  PMC::get_the_sanitized_excerpt();
	 * </code>
	 *
	 * Returns:
	 * <code>
	 *  <p class="entry-summary">Excerpt Text for Current Post</p>
	 * </code>
	 *
	 * ----------------------------------------
	 *
	 * Call:
	 * <code>
	 *  PMC::get_the_sanitized_excerpt(array(
	 *  	'post'           => 9000,
	 *  	'excerpt_class'  => 'my-excerpt-class',
	 *  ));
	 *
	 * Returns:
	 * <code>
	 *  <p class="entry-summary my-excerpt-class">Excerpt Text for Post ID 9000</p>
	 * </code>
	 *
	 * ----------------------------------------
	 *
	 * Call:
	 * <code>
	 *  $allowed_excerpt_html = wp_kses_allowed_html( 'pmc-excerpt' );
	 *  // If you don't add the 'data-post-id' attribute to $allowed_excerpt_html it will be stripped by wp_kses
	 *  $allowed_excerpt_html['a']['data-post-id'] = true;
	 *  PMC::get_the_sanitized_excerpt(array(
	 *  	'allowed_html'  => $allowed_excerpt_html,
	 *  	'excerpt_wrap'  => '<p class="%1$s" data-post-id="%3$d">%2$s</p>',
	 *  ));
	 * </code>
	 *
	 * Returns:
	 * <code>
	 *  <p class="entry-summary" data-post-id="1234">Excerpt Text for Current Post</p>
	 * </code>
	 *
	 * @since 2014-12-10 Gabriel Koen and Corey Gilmore
	 * @version 2014-12-15 Amit Gupta - code-refactor, fixed bugs (empty excerpt, more link override, etc), code cleanup, code comments
	 *
	 * @param
	 * @param array $args {
	 *     Optional. Array of extra arguments.
	 *
	 *     @type int|WP_Post   $post            Optional. Post ID or post object. Defaults to global $post.
	 *     @type int           $excerpt_length  Excerpt length. Default to the filtered value of excerpt_length.
	 *     @type string        $excerpt_wrap    How the excerpt should be wrapped. Default is a P tag with a class.
	 *                                          Uses printf() format with numbered placeholders.
	 *                                           %1$s = class
	 *                                           %2$s = excerpt text
	 *                                           %3$s = current post id
	 *     @type bool        $strip_teaser      Strip teaser content before the more text. Default is false.
	 * }
	 * @return string Sanitized excerpt, passed through wp_kses
	 *
	 */
	public static function get_the_sanitized_excerpt($args = array())
	{
		$default_args = array(
			'post'            => 0,
			'more_link_text'  => apply_filters('excerpt_more', '&hellip;Read More'),
			'excerpt_length'  => apply_filters('excerpt_length', 55),
			'excerpt_wrap'    => '<p class="%1$s">%2$s</p>',
			'excerpt_class'   => '',
			'allowed_html'    => 'pmc-excerpt',
			'strip_teaser'    => false,
		);

		// For get_the_excerpt filter compat, we want to ignore the excerpt passed to this function because we're building our own from scratch.
		if (!is_array($args)) {
			_doing_it_wrong('PMC::get_the_sanitized_excerpt()', 'This method overrides the default excerpt functionality. See PMC::get_the_sanitized_excerpt() for documentation.', '4.1');
			$args = array();
		}

		$args = wp_parse_args($args, $default_args);

		// get_post() returns a WP_Post object, or null if the post doesnt exist or an error occurred.
		$the_post = get_post($args['post']);

		if (empty($the_post)) {
			return '';
		}

		// Set $excerpt_text
		// Behavior:
		//  1. Look for custom excerpt
		//  2. Look for more tag
		//  3. Truncate post_content

		$has_teaser = false;
		$more_link_text = $args['more_link_text'];

		$excerpt_text = trim($the_post->post_excerpt);

		if (!empty($excerpt_text)) {
			$has_teaser = true;
		}

		// Based on get_the_content()
		$_page = (isset($GLOBALS['page'])) ? $GLOBALS['page'] : 1;
		$_pages = (isset($GLOBALS['pages'])) ? $GLOBALS['pages'] : array($the_post->post_content);
		$_multipage = (isset($GLOBALS['multipage'])) ? $GLOBALS['multipage'] : 0;
		$_more = (isset($GLOBALS['more'])) ? $GLOBALS['more'] : false;

		// if the requested page doesn't exist
		// give them the highest numbered page that DOES exist
		if ($_page > count($_pages)) {
			$_page = count($_pages);
		}

		// Use post_content for the excerpt if no post_excerpt
		if (empty($excerpt_text)) {
			$page_to_fetch = absint($_page - 1);

			$excerpt_text = (!empty($_pages[$page_to_fetch])) ? trim($_pages[$page_to_fetch]) : '';

			/*
			 * This would be set to FALSE if excerpt is not empty as we are creating excerpt from post content
			 * here and so the excerpt was probably not crafted by post author/editor.
			 */
			$has_teaser = (empty($excerpt_text));

			unset($page_to_fetch);
		}

		// Look for <!--more--> tag and use that
		if (!$has_teaser && preg_match('/<!--more(.*?)?-->/', $excerpt_text, $matches)) {
			$excerpt_text = explode($matches[0], $excerpt_text, 2);
			$excerpt_text = trim(array_shift($excerpt_text));

			if (!empty($matches[1]) && !empty($more_link_text)) {
				$more_link_text = trim($matches[1]);
			}

			/*
			 * This would be set to TRUE if excerpt is not empty as post author/editor
			 * set the marker for excerpt in post content.
			 */
			$has_teaser = (!empty($excerpt_text));
		}

		if (false !== strpos($excerpt_text, '<!--noteaser-->') && (!$_multipage || $_page == 1)) {
			$args['strip_teaser'] = true;
		}

		if ($_more && $args['strip_teaser'] && $has_teaser) {
			$excerpt_text = '';
		}

		if (!empty($excerpt_text)) {
			// strip out all shortcode tags, no use of them in excerpts
			// and apply the_content filter afterwards to catch any changes,
			// beautification etc
			$excerpt_text = strip_shortcodes($excerpt_text);

			// use this custom stripper to strip unregistered & escaped shortcode tags (if any). Only works on shortcodes with a closing tag.
			$excerpt_text = preg_replace('/\[(\w*)(\s*)(.*)\](.*)\[\/\1\]/s', '-tag-stripped-', $excerpt_text);

			// second pass to catch leftovers of escaped tags
			$excerpt_text = str_replace(array('[-tag-stripped-]', '-tag-stripped-'), '', $excerpt_text);

			$excerpt_text = apply_filters('the_content', $excerpt_text);
			$excerpt_text = str_replace(']]>', ']]&gt;', $excerpt_text);

			// if excerpt is longer than allowed length then snip it (no suffix added)
			// and untexturize it
			if (!$has_teaser) {
				// Convert excerpt_length (which is based on word count) to max character count (which is what PMC::truncate() expects).
				$clean_text = strip_tags($excerpt_text);
				$word_count_parts = str_word_count($clean_text, 2);
				$word_count = count($word_count_parts);

				if ($word_count > intval($args['excerpt_length'])) {
					$slice_offset = min(intval($args['excerpt_length']), $word_count);
					$last_word_position = array_keys(array_slice($word_count_parts, $slice_offset, 1, true));
					$last_word_position = array_shift($last_word_position);

					$excerpt_text = self::truncate($excerpt_text, $last_word_position, null);
				}
			} else {
				$excerpt_text = force_balance_tags($excerpt_text);
			}

			$excerpt_text = wp_kses($excerpt_text, $args['allowed_html']);

			// Collapse new lines - also prevents other filters from inserted unwanted <p> tags
			$excerpt_text = str_replace(array("\n", "\r"), ' ', $excerpt_text);
		}

		// If post password required, and a custom excerpt hasn't been crafted.
		if (!$has_teaser && post_password_required($the_post)) {
			$excerpt_text .= apply_filters('pmc-excerpt-password-required-text', 'This post is password protected. Enter the password to read the article.');
		}

		// Append the "more" link. If for some reason the excerpt is empty, this will still append the "more" link. This lets us know our function is still working without looking at the code. It's up to the editors to make sure the post has a proper excerpt.
		if (!empty($more_link_text)) {
			// No jump link in our default more_link_text
			// No opening space (if you want one, override more_link_text)
			$default_more_link_text = sprintf('<a href="%s" class="more-link">%s</a>', esc_url(get_permalink($the_post->ID)), wp_kses_post($more_link_text));

			$excerpt_text .= apply_filters('the_content_more_link', $default_more_link_text, $args['more_link_text'], $the_post->ID);
		}

		$excerpt_class = 'entry-summary ' . trim($args['excerpt_class']);

		$excerpt_text = sprintf(
			$args['excerpt_wrap'],
			esc_attr(trim($excerpt_class)),
			trim($excerpt_text),
			$the_post->ID
		);

		// preview fix for javascript bug with foreign languages
		// not sure exactly what bug this fixes but it's in the_content() in core
		if (isset($GLOBALS['preview']) && $GLOBALS['preview']) {
			$excerpt_text = preg_replace_callback('/\%u([0-9A-F]{4})/', '_convert_urlencoded_to_entities', $excerpt_text);
		}

		return $excerpt_text;
	}

	/**
	 * Sets the allowed HTMl tags for the 'pmc-excerpt' kss context. Used by PMC::get_the_sanitized_excerpt().
	 * Do not call directly.
	 *
	 * @param string $tags    Allowed tags, attributes, and/or entities.
	 * @param string $context The context for which to retrieve tags.
	 *
	 * @return array List of allowed tags and their allowed attributes for use in PMC::get_the_sanitized_excerpt().
	 *
	 * @uses filter::wp_kses_allowed_html
	 * @see wp_kses_allowed_html()
	 *
	 */
	public static function _kses_excerpt_allowed_html($allowed_html, $context)
	{

		if ('pmc-excerpt' === $context) {
			$allowed_html = static::allowed_html(
				'post',
				['a', 'b', 'em', 'i', 's', 'strike', 'strong']
			);
		}

		return $allowed_html;
	}

	/**
	 * @TODO: SADE-517 to be removed
	 * @codeCoverageIgnore SADE-517 to be removed
	 **/
	public static function parse_whitelisted_args(array $args, array $defaults = [])
	{
		return static::parse_allowed_args($args, $defaults);
	}

	/**
	 * Method to compare two arrays and return an array with the values of second array updated with values of corresponding keys of first array.
	 * Any key in $args which does not exist in $defaults would be discarded.
	 *
	 * @since 2018-01-24 Amit Gupta
	 *
	 * @param array $args An array of arguments which is to be parsed
	 * @param array $defaults An array of default arguments which are to be updated with values of corresponding keys in $args
	 *
	 * @return array
	 */
	public static function parse_allowed_args(array $args, array $defaults = [])
	{

		if (empty($defaults)) {
			return [];
		}

		$updated_args = $defaults;	//lets use defaults as starting point

		$allowed_keys = array_keys((array) $defaults);

		for ($i = 0; $i < count($allowed_keys); $i++) {

			$key = $allowed_keys[$i];

			if (isset($args[$key])) {
				$updated_args[$key] = $args[$key];
			}

			unset($key);
		}

		return $updated_args;
	}

	/**
	 * A template function so that we don't have to put inline HTML.
	 * This will parse a template and add data to it using its variables.
	 *
	 * @param string $path template path for include
	 * @param array $variables Array containing variables and data for template
	 * @param boolean $echo Set this to TRUE if the template output is to be sent to browser. Default is FALSE.
	 * @param array $options An array of additional options
	 *
	 * @return string
	 * @throws Exception
	 *
	 * @since 2013-01-24 mjohnson
	 * @version 2017-09-21 Amit Gupta - added 3rd parameter to allow the method to output template instead of returning HTML
	 * @version 2018-01-24 Amit Gupta - added 4th parameter to specify options and added search and loading of templates from parent theme if not in current theme
	 */
	public static function render_template($path, array $variables = [], $echo = false, array $options = [])
	{

		/*
		 * Parse the options with the allowed list so that only
		 * the allowed options remain in the array and
		 * missing options are added with default values.
		 * Any options not defined in defaults here would be
		 * discarded. This is an inclusive behaviour which
		 * wp_parse_args() does not support.
		 */
		$options = static::parse_allowed_args(
			$options,
			[
				'is_relative_path' => false,
			]
		);

		// Set options into individual vars
		$is_relative_path = (true === $options['is_relative_path']);

		if (true !== $is_relative_path && (!file_exists($path) || 0 !== validate_file($path))) {

			/*
			 * Invalid template path
			 * Throw an exception if current env is not production
			 * else silently bail out on production
			 */
			return static::maybe_throw_exception(sprintf('Template %s doesn\'t exist', $path));
		}

		/*
		 * If relative path to template has been passed then
		 * we will look for template in child theme and parent theme
		 */
		if (true === $is_relative_path) {

			$template_path = locate_template([static::unleadingslashit($path)], false);

			if (empty($template_path)) {

				/*
				 * Can't find template in child theme & parent theme
				 * Throw an exception if current env is not production
				 * else silently bail out on production
				 */
				return static::maybe_throw_exception(sprintf('Template %s doesn\'t exist', $path));
			}

			$path = $template_path;

			unset($template_path);
		}

		// Allow overriding variables for a given template
		$variables = (array) apply_filters('pmc_render_template_variables', $variables, $path);

		if (!empty($variables)) {
			extract($variables, EXTR_SKIP);
		}

		if (true === $echo) {
			// load template and output the data
			require $path;
			return '';	//job done, bail out
		}

		ob_start();
		require $path;	//load template output in buffer
		return ob_get_clean();
	}

	/**
	 * Ensure an integer falls within a given range.
	 *
	 * @param int $num the number to check
	 * @param int $min the minimum value allowed
	 * @param int $max the maximum value allowed
	 *
	 * @return int
	 *
	 * @since 2013-02-07 Corey Gilmore
	 */
	public static function numeric_range($num, $min, $max)
	{
		$num = intval($num);
		$num = max(intval($min), $num); // make sure we're larger than the minimum value
		$num = min($num, intval($max)); // and make sure we're smaller than the max

		return $num;
	}

	/** Add ordinals to number.
	 * @param string $number
	 * @return string
	 *
	 * @since 2013-02-08 Amit Sannad
	 */
	public static function add_ordinal_suffix($number = '')
	{

		$ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
		if (($number % 100) >= 11 && ($number % 100) <= 13)
			$abbreviation = $number . 'th';
		else
			$abbreviation = $number . $ends[$number % 10];

		return $abbreviation;
	}

	/**
	 * Set IE to always render in standard mode.
	 * Hides the compatibility view button in the browser as well.
	 */
	public static function set_ie_standard_compatibility($headers)
	{

		if (!is_admin()) {
			$headers["X-UA-Compatible"] = "IE=Edge";
		}

		return $headers;
	}

	/**
	 * A function to strip out all special chars (except hyphens & underscores)
	 * and replace all spaces with hyphens. This takes in a string and makes it
	 * suitable for use in a URL.
	 *
	 * This is slightly different from WordPress' sanitize_title() as it strips
	 * out accented chars as well and doesn't strip off legit chars preceded/succeeded
	 * by invalid chars.
	 *
	 * @since 2013-03-13 Amit Gupta
	 * @version 2013-03-13 Amit Gupta
	 */
	public static function sanitize_title($title)
	{
		if (empty($title)) {
			return;
		}

		$original_title = $title;

		$title = preg_replace('/[^a-zA-Z0-9 \-_]/m', '', $title);	//strip out undesired chars
		$title = preg_replace('/\s\-/m', '-', preg_replace('/\-\s/m', '-', $title));		//strip out spaces preceding/succeeding hyphens
		$title = preg_replace('/\s/m', '-', $title);		//convert spaces to hyphens
		$title = preg_replace('/\-_/m', '-', preg_replace('/_\-/m', '-', $title));	//convert all instances of _- and -_ into single hyphens
		$title = preg_replace('/\-{2,}/m', '-', $title);	//convert all instances of multiple successive hyphens into single hyphens
		$title = trim($title, '-');	//strip out any hyphens from beginning/end of title

		return strtolower($title);		//return lowercase string
	}

	/**
	 * Converts the array to a comma-separated sentence where the last element is joined by the connector word.
	 *
	 * @param array $array array of elements to join
	 * @param string $words_connector optional, defaults to ", ". The string used to join elements in arrays with three or more elements
	 * @param string $last_word_connector optional, defaults to " and ". The string used to join the last two element in arrays with three or more elements
	 * @param string $two_words_connector optional, defaults to " and ". The string used to join elements in arrays with exactly two elements
	 *
	 * @since 2013-03-20 Corey Gilmore
	 * @version 2013-03-20 Corey Gilmore
	 *
	 */
	public static function to_sentence($array, $words_connector = ', ', $last_word_connector = ' and ', $two_words_connector = ' and ')
	{
		if (!is_array($array))
			return $array;

		switch (count($array)) {
			case 0:
				$str = '';
				break;

			case 1:
				$str = array_shift($array);
				break;

			case 2:
				$str = implode($two_words_connector, $array);
				break;

			default:
				$last_item = array_pop($array);
				$str = implode($words_connector, $array) . $last_word_connector . $last_item;
				break;
		}

		return $str;
	}

	/**
	 * Returns color contrast from hex value.
	 *
	 * @param     $hex_color
	 * @param int $white_compare
	 *
	 * @return string
	 * returns white is color is close to white, returns black if color is close to black
	 */
	public static function get_color_contrast($hex_color, $white_compare = 128)
	{

		$r   = hexdec(substr($hex_color, 0, 2));
		$g   = hexdec(substr($hex_color, 2, 2));
		$b   = hexdec(substr($hex_color, 4, 2));
		$yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

		return ($yiq >= $white_compare) ? 'white' : 'black';
	}

	/**
	 * This function returns a multi-dimensional array of post authors.
	 *
	 * @param int $post_id
	 * @param mixed $authors_to_return 'all' to get all authors of post else number of authors to fetch
	 * @param array $fields the author_meta to fetch
	 *
	 * @return array
	 * returns a multi-dimensional array of post authors else NULL if no authors found or if $fields do not exist
	 *
	 * @since 2013-06-14 Amit Gupta
	 * @version 2013-06-17 Amit Gupta
	 */
	public static function get_post_authors($post_id = 0, $authors_to_return = 'all', $fields = array())
	{
		$post_id = (intval($post_id) < 1) ? 0 : intval($post_id);

		if ($post_id < 1) {
			return;
		}

		if (!is_array($fields) && !is_string($fields)) {
			return;
		}

		$default_fields = array(
			'ID', 'display_name', 'user_login', 'user_nicename',
		);

		if (empty($fields)) {
			$fields = $default_fields;
		} elseif (is_string($fields)) {
			$fields = array($fields);
		}

		$fields = array_filter(array_unique($fields));

		$post_authors = array();

		if (function_exists('get_coauthors')) {
			$authors = get_coauthors($post_id);

			$fields_count = count($fields);

			foreach ($authors as $author) {
				$post_author = array();

				for ($i = 0; $i < $fields_count; $i++) {
					if (isset($author->{$fields[$i]}) && !empty($author->{$fields[$i]})) {
						$post_author[$fields[$i]] = str_replace(',', '', $author->{$fields[$i]});
					}
				}

				$post_authors[$author->ID] = $post_author;

				unset($post_author);
			}
		} else {	//CA+ not in use, get post author using regular WP API
			$post = get_post($post_id);
			if (empty($post)) {
				return;
			}

			if (isset($post->post_author) && !empty($post->post_author)) {
				$post_author = array();
				$fields_count = count($fields);

				for ($i = 0; $i < $fields_count; $i++) {
					$author = get_the_author_meta($fields[$i], $post->post_author);

					if (!empty($author)) {
						$post_author[$fields[$i]] = str_replace(',', '', $author);
					}
				}

				$post_authors[$post->post_author] = $post_author;

				unset($post_author, $fields_count);
			}

			unset($post);
		}

		if ($authors_to_return !== 'all') {
			$authors_to_return = intval($authors_to_return);

			if ($authors_to_return > 0) {
				$post_authors = array_slice($post_authors, 0, $authors_to_return);
			}
		}

		if (empty($post_authors)) {
			return;
		}

		return $post_authors;
	}

	/**
	 * This function returns a comma separated list of post authors.
	 *
	 * @param int $post_id
	 * @param mixed $authors_to_return 'all' to get all authors of post else number of authors to fetch
	 * @param string $field the author_meta to fetch
	 * @param string $backup_field the author_meta to fetch if $field is not present
	 *
	 * @return string
	 * returns comma separated list of post authors else empty string if no authors found or if $field and $backup_field does not exist
	 *
	 * @since 2013-05-16 Amit Gupta
	 * @version 2013-06-17 Amit Gupta
	 */
	public static function get_post_authors_list($post_id = 0, $authors_to_return = 'all', $field = 'user_login', $backup_field = 'user_nicename')
	{
		$backup_field = (empty($backup_field)) ? 'user_nicename' : $backup_field;
		$field = (empty($field)) ? $backup_field : $field;
		$post_id = (intval($post_id) < 1) ? 0 : intval($post_id);

		if ($post_id < 1) {
			return;
		}

		$arr_fields = array();

		if (!empty($field)) {
			$arr_fields[] = $field;
		}

		if (!empty($backup_field)) {
			$arr_fields[] = $backup_field;
		}

		$arr_fields = array_filter(array_unique($arr_fields));

		$post_authors = array();

		$authors = self::get_post_authors($post_id, $authors_to_return, $arr_fields);

		if (empty($authors)) {
			return;
		}

		foreach ($authors as $author_id => $author) {
			if (isset($author[$field]) && !empty($author[$field]) && !in_array($author[$field], $post_authors)) {
				$post_authors[] = $author[$field];
			} elseif (isset($author[$backup_field]) && !empty($author[$backup_field]) && !in_array($author[$backup_field], $post_authors)) {
				$post_authors[] = $author[$backup_field];
			}
		}

		return implode(',', $post_authors);
	}

	/**
	 * This function is similar to PHP function array_search(), it searches for
	 * a needle in an array and returns the key if needle is found. But unlike
	 * array_search(), this function accepts partial fragment of needle and searches
	 * in every value of haystack.
	 *
	 * @param mixed $needle The fragment or whole value to search
	 * @param array $haystack
	 *
	 * @return mixed Returns the key for needle if it is found in the array, FALSE otherwise.
	 *
	 * @since 2013-05-23 Amit Gupta
	 */
	public static function array_search_partial($needle, $haystack)
	{
		if (empty($needle) || (!is_string($needle) && !is_numeric($needle))) {
			trigger_error('PMC::array_search_partial(): Needle must be a non-empty string or a number', E_USER_WARNING);
			return false;
		}

		if (empty($haystack) || !is_array($haystack)) {
			trigger_error('PMC::array_search_partial(): Haystack cannot be empty and must be an array', E_USER_WARNING);
			return false;
		}

		foreach ($haystack as $key => $value) {
			if (!is_string($value)) {
				continue;	//we'll search only on strings, skip to next iteration
			}

			if (strpos($value, $needle) !== false) {
				return $key;	//found it. return key & bail out
			}
		}

		return false;
	}

	/**
	 * This function is just like PHP str_replace() but replaces only first occurence of needle(s).
	 *
	 * @param mixed $search The value being searched for, otherwise known as the needle. An array may be used to designate multiple needles.
	 * @param mixed $replace The replacement value that replaces found search values. An array may be used to designate multiple replacements.
	 * @param mixed $subject The string or array being searched and replaced on, otherwise known as the haystack. If subject is an array, then the search and replace is performed with every entry of subject, and the return value is an array as well.
	 *
	 * @return mixed This function returns a string or an array with the replaced values.
	 *
	 * @package iG\Utility
	 *
	 * @since 2013-06-02 Amit Gupta
	 * @version 2013-06-03 Amit Gupta
	 */
	public static function str_replace_once($search, $replace, $subject)
	{
		if (empty($search) || empty($subject)) {
			return $subject;
		}

		if (!is_array($search)) {
			if (!is_string($search) && !is_numeric($search)) {
				return $subject;
			}

			$search = array((string) $search);
		}

		if (!is_array($replace)) {
			if (!empty($replace) && !is_string($replace) && !is_numeric($replace)) {
				return $subject;
			}

			$replace = array((string) $replace);
		}

		if (!is_array($subject)) {
			if (!is_string($subject) && !is_numeric($subject)) {
				return $subject;
			}

			$subject = array((string) $subject);
		}

		$search_count = count($search);
		$replace_count = count($replace);
		$subject_count = count($subject);

		for ($j = 0; $j < $subject_count; $j++) {
			for ($i = 0; $i < $search_count; $i++) {
				if (empty($search[$i])) {
					continue;
				}

				$pos = strpos($subject[$j], $search[$i]);

				if (isset($replace[$i])) {
					$replace_with = $replace[$i];
				} elseif ($replace_count == 1) {
					$replace_with = $replace[0];
				} else {
					$replace_with = '';
				}

				if ($pos !== false) {
					$subject[$j] = substr_replace($subject[$j], $replace_with, $pos, strlen($search[$i]));
				}

				unset($replace_with, $pos);
			}
		}

		$subject = ($subject_count == 1) ? array_pop($subject) : $subject;

		unset($subject_count, $replace_count, $search_count);

		return $subject;
	}

	/*
	 * Helper function to generate html image
	 */
	public static function get_image_html($attrs)
	{
		if (empty($attrs)) {
			return '';
		}

		$html = "<img";

		foreach ($attrs as $name => $value) {
			if (!empty($value)) {
				$html .= " $name=" . '"' . ($name == 'src' ? esc_url($value) : esc_attr($value)) . '"';
			}
		}
		$html .= ' />';

		return $html;
	}

	/*
	 * Helper function to generate lazy load html image
	 */
	public static function get_image_html_lazy_load($attrs, $class = 'full lazy', $lazy_src = false)
	{
		$attrs['data-original'] = $attrs['src'];
		$attrs['src'] = $lazy_src ? $lazy_src : get_template_directory_uri() . '/library/images/global/blank.png';
		$attrs['class'] = $class;
		return self::get_image_html($attrs);
	}

	/*
	 * Helper function to generate lazy load html image from attachment
	 */
	public static function get_attachment_image_lazy_load($attachment_id, $size = 'thumbnail', $class = 'full lazy', $lazy_src = false)
	{
		return self::get_image_html_lazy_load(self::get_attachment_attributes($attachment_id, $size));
	}

	public static function get_attachment_image($attachment_id, $size = 'thumbnail', $icon = false, $attr = '')
	{
		$attr = self::get_attachment_attributes($attachment_id, $size, $icon, $attr);
		return self::get_image_html($attr);
	}


	/*
	 * Helper function to generate attachment image alt text:
	 * Priority for fallback: Image alt text field -> SEO title -> Article title
	 * Remove the following prepositions from the article title but not SEO or manual alt text: "a", "an", "as", "at", "but", "by", "for", "in", "to", "via"
	 * Limit to 6 words
	 */
	public static function get_attachment_image_alt_text($attachment_id, $post = null)
	{
		if (!empty($attachment_id)) {
			$alt_text = trim(strip_tags(get_post_meta($attachment_id, '_wp_attachment_image_alt', true)));
		}

		if (empty($alt_text)) {
			$post = get_post($post);

			if (is_object($post)) {
				$alt_text = trim(strip_tags(get_post_meta($post->ID, 'mt_seo_title', true)));

				if (empty($alt_text)) {
					$alt_text = strip_tags($post->post_title);
					// strip prepositions
					$alt_text = preg_replace('/\b(a|an|as|at|but|by|for|in|to|via)\b/i', '', " {$alt_text} ");
					$alt_text = trim(preg_replace('/\s+/', ' ', $alt_text));
				}
			}
		}

		if (!empty($alt_text)) {
			// limit to 6 words only
			$text = preg_split('/[\s\t]+/', $alt_text, 0, PREG_SPLIT_NO_EMPTY);
			$words = array_splice($text, 0, 6);
			$alt_text = implode(' ', $words);
		}

		return $alt_text;
	}

	public static function get_attachment_attributes($attachment_id, $size = 'thumbnail', $post = 0)
	{

		$image = wp_get_attachment_image_src($attachment_id, $size);

		if ($image) {
			list($src, $width, $height) = $image;

			if (is_array($size)) {
				$size = join('x', $size);
			}

			$attachment = get_post($attachment_id);
			$attrs = array(
				'width'  => $width,
				'height' => $height,
				'src'    => $src,
				'class'  => "attachment-$size",
				'alt'    => self::get_attachment_image_alt_text($attachment_id, $post),
			);

			$attrs = apply_filters('wp_get_attachment_image_attributes', $attrs, $attachment);
			return $attrs;
		}

		return false;
	} // function get_attachment_attributes

	/*
	 * Helper function to generate <a> link or <span> label.
	 */
	public static function get_html_link_or_label($url, $label, $attrs = false)
	{
		if (!empty($url)) {
			$html = '<a href="' . esc_url($url) . '"';
		} else {
			$html = '<span ';
		}
		if (!empty($attrs)) {
			foreach ($attrs as $name => $value) {
				$html .= ' ' . sanitize_text_field($name) . '="' . esc_attr($value) . '"';
			}
		}
		$html .= '>' . esc_html($label);
		if (!empty($url)) {
			$html .= '</a>';
		} else {
			$html .= '</span>';
		}
		return $html;
	}

	public static function enqueue_ab_test_js()
	{
		wp_enqueue_script('pmc-ab-test', pmc_global_functions_url('/js/pmc-ab-test.js'), array('jquery'));
	}

	public static function enqueue_socialite_js()
	{
		wp_enqueue_script('socialite', pmc_global_functions_url('/js/socialite.js'), array(), false, true);
		wp_enqueue_script('pmc-socialite-plugin', pmc_global_functions_url('/js/pmc-socialite-plugin.js'), array('socialite', 'jquery'), false, true);
	}

	public static function enqueue_chosen()
	{
		wp_enqueue_script('chosen-jquery', pmc_global_functions_url('/chosen/chosen.jquery.js'), array('jquery'), false, true);
		wp_enqueue_style('chosen-css', pmc_global_functions_url('/chosen/chosen.css'));
	}

	public static function enqueue_sticky_rightrail()
	{
		wp_enqueue_script('pmc-sticky-rightrail', pmc_global_functions_url('/js/pmc-sticky-rightrail.js'), array(), false, true);
	}

	/**
	 * @param array $paragraphs An indexed paragraph position array ( 1 => 'text 1', 2 => 'text 2'...) where contents are to be inject into $contents
	 * @param string $content A string containing the html text where paragraphs are marked as <p>..</p>
	 * @param array $args
	 * @return mixed This function return a string with paragraphs data injected into content
	 */
	public static function inject_paragraph_content(array $paragraphs, $content, $args = array())
	{
		$default_args = array(
			'append' => true, // Whether to append this content if there are not enough paragraphs
			'minimum_characters' => false, // Minumum number of characters before a new paragraph is injected, so that we don't break layout or formatting
		);
		$args = wp_parse_args($args, $default_args);
		$content = wpautop($content);

		// Sometimes the content dictates a minimum number of characters before a new paragraph is injected, so that we don't break layout or formatting.
		// To do this we have to count the number of characters in each paragraph, not counting HTML, until we reach the minimum character count.
		// We have to re-order the $paragraphs array based on this new minimum, and sometimes shift subsequent paragraphs to avoid collisions.
		if ($args['minimum_characters']) {
			$clean_content = strip_tags($content, '<p>');
			$clean_tokens = explode('</p>', $clean_content);
			$character_count = 0;
			$minimum_injection_point = 0;
			for ($i = 0; $i < count($clean_tokens); $i++) {
				// There's no telling what the short code contains. For example, if it's [flv] or [gist] the amount of characters has nothing to do with the size of the content.  I think the best solution is to acknowledge there's a short code, and count it in the # of paragraphs, but ignore the size of it.
				if (!preg_match('~' . get_shortcode_regex() . '~', $clean_tokens[$i])) {
					$character_count += mb_strlen($clean_tokens[$i]);
				}
				if ($character_count >= $args['minimum_characters']) {
					$minimum_injection_point = ($i + 1);
					break;
				}
			}

			$injection_points = array_keys($paragraphs);
			$new_injection_point = 0;
			$new_paragraphs = array();
			foreach ($injection_points as $injection_point) {
				if ($injection_point < $minimum_injection_point) {
					$new_injection_point = $minimum_injection_point;
				} elseif (isset($new_paragraphs[$injection_point])) {
					$new_injection_point = ($injection_point + 1);
				} else {
					$new_injection_point = $injection_point;
				}
				$new_paragraphs[$new_injection_point] = $paragraphs[$injection_point];
			}

			$paragraphs = $new_paragraphs;
		}

		$tokens = explode('</p>', wpautop($content));
		$extras = array();
		foreach ($paragraphs as $pos => $value) {
			if (isset($tokens[$pos])) {
				$tokens[$pos] = $value . $tokens[$pos];
			} else if ($args['append']) {
				$extras[] = $value;
			}
		}
		return implode('</p>', $tokens) . implode('', $extras);
	}

	/**
	 * Helper function for inserting text/html at a specific position in the given content
	 *
	 * Example, insert <div>foo</div> after the 4th paragraph
	 *
	 * @since 2015-11-06 PMC, Mike Auteri, James Mehorter, re: PMCVIP-418, PMCVIP-450
	 *
	 * @param string $content            The full content
	 * @param string $insertion          What to insert
	 * @param string $html_tag_name      The HTML tag to look for, Defaults to 'p'
	 * @param int    $insert_at_position The HTML to insert after
	 *
	 * @return string The content with the added insertion
	 */
	public static function insert_in_content($content = '', $insertion = '', $html_tag_name = 'p', $insert_at_position = 1)
	{

		// Position should never be 0, 1 is the earliest position possible
		if (0 === $insert_at_position)
			$insert_at_position = 1;

		// We explode the content on the closing tags because
		// the opening tags may have inline attributes like
		// style="" for example. Knowing that.. We could use
		// regex to explode on the opening tags, though, let's KISS
		$html_closing_tag = "</$html_tag_name>";

		// Split our content into an array of content pieces
		// ..an index for each $html_closing_tag found..
		// e.g. If $html_tag = 'p', we'll get an array of paragraphs
		// minus their closing tags..
		$content_pieces = explode($html_closing_tag, $content);

		// Add the html closing tags back onto each array item
		$content_pieces = preg_filter('/$/', $html_closing_tag, $content_pieces);

		// Is the given content long enough for the desired insert position?
		if (isset($content_pieces[$insert_at_position - 1])) {

			// ..yes it is, let's insert the insertion at the desired position
			array_splice($content_pieces, $insert_at_position, 0, $insertion);
		} else {

			// ..no, the content is shorter than the desired length/position
			// append the insertion onto the end
			array_push($content_pieces, $insertion);
		}

		// Return the reassembled content with the insertion
		return implode('', $content_pieces);
	}

	/**
	 * Convert Date to different timezone
	 *
	 * @param        $date
	 * @param string $to_time_zone
	 * @param string $from_time_zone
	 *
	 * @return string
	 */
	public static function convert_date_timezone($date, $to_time_zone = "default_tz", $from_time_zone = "UTC")
	{

		if (empty($to_time_zone)) {
			return;
		}

		if ('default_tz' == $to_time_zone) {
			$to_time_zone = get_option("timezone_string");
		}

		$dt = new DateTime($date, new DateTimeZone($from_time_zone));

		$dt->setTimezone(new DateTimeZone($to_time_zone));

		return $dt;
	}


	/**
	 * Check for required classes and functions.
	 *
	 * @param string $feature The name of the feature that will be affected. Used when printing a notice.
	 * @param array $opts array of options containing:
	 *   @param array $required_classes Optional. Array of class names to check the existence of.
	 *   @param array $required_functions Optional. Array of function names to check the existence of.
	 *   @param bool $print_notice Optional, defaults to true. true to print an admin notice, false to print nothing.
	 *
	 * @param array $missing Passed by reference. Will contain an array containing:
	 *   @param array $missing_classes Optional. Passed by reference, will contain all of the missing classes.
	 *   @param array $missing_functions Optional. Passed by reference, will contain all of the missing functions.
	 *
	 *
	 * @return bool true if all classes and functions exist, false otherwise.
	 *
	 * @since 2013-09-18 Corey Gilmore
	 * @version 2013-09-18 Corey Gilmore
	 *
	 */
	public static function check_dependencies($feature, $opts = array(), &$missing = array())
	{
		$default_opts = array(
			'print_notice'        => true,
			'required_classes'    => array(),
			'required_functions'  => array(),
		);

		$missing = array(
			'classes'    => array(),
			'functions'  => array(),
		);

		$opts = wp_parse_args($opts, $default_opts);

		if (is_array($opts['required_classes'])) {
			foreach ($opts['required_classes'] as $dep) {
				if (!class_exists($dep))
					$missing['classes'][] = $dep;
			}
		}

		if (is_array($opts['required_functions'])) {
			foreach ($opts['required_functions'] as $dep) {
				if (!function_exists($dep))
					$missing['functions'][] = $dep;
			}
		}

		if (empty($missing['classes']) && empty($missing['functions'])) {
			return true;
		} else {
			if ($opts['print_notice']) {
				$missing_objects_text = array();
				if (!class_exists('PMC_Admin_Notice')) {
					$missing['classes'][] = 'PMC_Admin_Notice';
				}

				if (!empty($missing['classes'])) {
					$missing_classes = PMC::to_sentence($missing['classes']);
					$missing_objects_text[] = sprintf('Missing %s: %s', _n('class', 'classes', sizeof($missing['classes']), 'pmc'), esc_html($missing_classes));
				}

				if (!empty($missing['functions'])) {
					$missing_functions = PMC::to_sentence($missing['functions']);
					$missing_objects_text[] = sprintf('Missing %s: %s', _n('function', 'functions', sizeof($missing['functions']), 'pmc'), esc_html($missing_functions));
				}

				$message = sprintf('%s is disabled. %s', esc_html($feature), esc_html(PMC::to_sentence($missing_objects_text, '. ', '. ', '. ')));

				if (!class_exists('PMC_Admin_Notice')) {
					add_action('admin_notices', function () use ($message) {
						echo '<div class="error"><p>' . $message . '</p></div>';
					});
				} else {
					PMC_Admin_Notice::add_admin_notice($message, array(
						'dismissible'      => true,
						'snooze_time'      => 3 * HOUR_IN_SECONDS,
						'notice_classes'   => array('error'),
					));
				}
			}

			return false;
		}

		return false;
	}

	/**
	 * Conditional method to check if current environment is production environment or not
	 *
	 * @return bool Returns TRUE if current environment is production else FALSE
	 *
	 * Ignoring this for code coverage since unit tests for this method are not possible at present
	 * @codeCoverageIgnore
	 */
	public static function is_production(): bool
	{

		// run this only in unit tests
		if (
			(defined('IS_UNIT_TEST') && true === IS_UNIT_TEST)
			|| class_exists('\WP_UnitTestCase', false)
		) {

			$mock_production_env = apply_filters('pmc_is_production_mock_env', false);

			if (true === $mock_production_env) {
				return true;
			}
		}

		// Check if site is self-hosted
		if (defined('PMC_IS_PRODUCTION') && true === PMC_IS_PRODUCTION) {
			return true;
		}

		// Check if site is on WPCOM VIP Go
		if (static::is_vip_go_production()) {
			return true;
		}

		return false;
	}

	/**
	 * Conditional method to check if current environment is WPCOM VIP production environment or not
	 *
	 * @return bool Returns TRUE if current environment is WPCOM VIP production else FALSE
	 *
	 * Ignoring this for code coverage since unit tests for this method are not possible at present
	 * @codeCoverageIgnore
	 */
	public static function is_classic_vip_production(): bool
	{

		// run this only in unit tests
		if (
			(defined('IS_UNIT_TEST') && true === IS_UNIT_TEST)
			|| class_exists('\WP_UnitTestCase', false)
		) {

			$mock_production_env = apply_filters('pmc_is_production_mock_env', false);

			if (true === $mock_production_env) {
				return true;
			}
		}

		// Check if site is on WPCOM VIP
		if (defined('WPCOM_IS_VIP_ENV') && true === WPCOM_IS_VIP_ENV) {
			return true;
		}

		return false;
	}

	/**
	 * Conditional method to check if current environment is VIP Go production environment or not
	 *
	 * @return bool Returns TRUE if current environment is VIP Go production else FALSE
	 *
	 * Ignoring this for code coverage since unit tests for this method are not possible at present
	 * @codeCoverageIgnore
	 */
	public static function is_vip_go_production(): bool
	{

		// run this only in unit tests
		if (
			(defined('IS_UNIT_TEST') && true === IS_UNIT_TEST)
			|| class_exists('\WP_UnitTestCase', false)
		) {

			$mock_production_env = apply_filters('pmc_is_production_mock_env', false);

			if (true === $mock_production_env) {
				return true;
			}
		}

		// Check if site is on WPCOM VIP Go
		if (defined('VIP_GO_ENV') && ('production' === VIP_GO_ENV || true === VIP_GO_ENV)) {
			return true;
		}

		return false;
	}

	/**
	 * Strip all non-printable characters from a string.
	 *
	 * @param string $input
	 * @return string
	 *
	 * @since 2014-05-27 Corey Gilmore
	 * @version 2014-05-27 Corey Gilmore
	 *
	 * @see http://stackoverflow.com/questions/1497885/remove-control-characters-from-php-string
	 */
	public static function strip_control_characters($input)
	{
		return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
	}

	/**
	 * Replace non-printable characters from a string.
	 *
	 * @param $content
	 * @return mixed
	 *
	 * @since 2015-10-26 - Javier Martinez
	 * @version 2015-10-26 - Javier Martinez - PMCVIP-459
	 *
	 */
	public static function replace_control_characters($content)
	{
		static $replace = null;
		static $search = null;

		// Use a static var to save a few CPU cycles when this is called multiple times
		if (is_null($search)) {
			$char_cleanup = array(
				// array of $match_string => $replace_string patterns
				'\xA0' => '&nbsp;', // replace hidden hex-encoded &nbsp; characters - see PMCVIP-458
				'\x00' => '', // Strip null
			);

			$search = array_keys($char_cleanup);
			$replace = array_values($char_cleanup);
		}

		$text = str_replace($search, $replace, $content);

		return $text;
	}

	/**
	 * wpcom-friendly detection of SSL
	 *
	 * Related tickets:
	 *  https://wordpressvip.zendesk.com/requests/30102 (justification for this function)
	 *  https://wordpressvip.zendesk.com/requests/30091 (original inception)
	 *  https://wordpressvip.zendesk.com/requests/27386 (custom CDNs and SSL, batcache keys on SSL)
	 *
	 * @since 2014-06-06 Corey Gilmore
	 * @version 2014-06-06 Corey Gilmore
	 *
	 */
	public static function is_https()
	{
		$https = false;

		if (!empty($_SERVER['HTTPS'])) {
			if ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) {
				$https = true;
			}
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
			$https = true;
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && ($_SERVER['HTTP_X_FORWARDED_SSL'] == 'on' || $_SERVER['HTTP_X_FORWARDED_SSL'] == 1)) {
			$https = true;
		}

		return $https;
	}

	/**
	 * Retrieve the full path of the highest priority template file that exists in the given theme path
	 *
	 * Looks for template file in the STYLESHEETPATH before TEMPLATEPATH so that
	 * child theme can override parent theme template.
	 * This is a souped up version of locate_template() in WordPress core.
	 *
	 * @param string $template_name Name of template with is to be located. This can also be a sprintf() style path format in which first %s token will be replaced with STYLESHEETPATH or TEMPLATEPATH.
	 * @return string|boolean Returns full path of the first template located or FALSE if template cannot be located
	 *
	 * @since 2014-06-06 Amit Gupta
	 */
	public static function locate_template($template_name)
	{
		if (empty($template_name) || !is_string($template_name)) {
			return false;
		}

		if (strpos($template_name, '/') === false) {
			$template_name = '%s/' . sanitize_file_name($template_name);
		} else {
			$path_parts = explode('/', $template_name);
			$path_parts = array_map('sanitize_file_name', $path_parts);
			$template_name = implode('/', $path_parts);
		}

		//check in current theme dir
		$template = sprintf($template_name, STYLESHEETPATH);

		if (!file_exists($template)) {
			$template = '';		//free up the var
		}

		if (empty($template)) {
			//check in parent theme dir
			$template = sprintf($template_name, TEMPLATEPATH);

			if (!file_exists($template)) {
				$template = '';		//free up the var
			}
		}

		if (!empty($template)) {
			//template found, return the path
			return $template;
		}

		return false;
	}

	/**
	 * Remove forward slash from the beginning of a string
	 *
	 * @param string $string String from which forward slash is to be removed from beginning
	 * @return string String with forward slash removed from beginning
	 *
	 * @since 2014-06-23 Amit Gupta
	 */
	public static function unleadingslashit($string)
	{
		return ltrim($string, '/');
	}

	/**
	 * Add one forward slash at the beginning of a string
	 *
	 * @param string $string String to which forward slash is to be added at beginning
	 * @return string String with forward slash added at beginning
	 *
	 * @since 2014-06-23 Amit Gupta
	 */
	public static function leadingslashit($string)
	{
		return '/' . static::unleadingslashit($string);
	}

	/**
	 * Make url ssl friendly by removing http:
	 * We need to use schemeless url to make all pages to comply with HTTPS traffic.
	 * If https is detected, do http to https host translation from a mapping array
	 * This function will be use for css, js, and image references where needed.
	 *
	 * @since 2014-09-17 Hau Vong Initial version
	 *
	 * @param string $url The url to be process
	 * @return string The processed value
	 */
	public static function ssl_friendly_url($url)
	{

		$url = apply_filters('pmc_pre_ssl_friendly_url', $url);

		if (empty($url) || !preg_match('/^https?:\/\//', $url)) {
			return $url;
		}

		$original_url = $url;

		if (PMC::is_https()) {
			$url_parts = parse_url($original_url);
			$url_parts = array_merge(array('path' => '/', 'query' => '', 'fragment' => ''), $url_parts);
			$url = '';

			// if url scheme is http, we need to do http to https translation
			if ('http' === $url_parts['scheme']) {
				$host = strtolower($url_parts['host']);
				$mapping = array(
					'b.scorecardresearch.com' => 'sb.scorecardresearch.com',
					'static.chartbeat.com'    => 'a248.e.akamai.net/chartbeat.download.akamai.com/102508',
					'objects.tremormedia.com' => 'a248.e.akamai.net/f/1761/2685/4d/objects.tremormedia.com',
					'edge.quantserve.com'     => 'secure.quantserve.com',
					'www.vimg.net'            => 's3.amazonaws.com/www.vimg.net',
					'media-vimg-net.vimg.net' => 's3.amazonaws.com/media-vimg-net.vimg.net',
					'cdn.variety.com'         => 'pmcvariety.files.wordpress.com',
					'cdn.varietylatino.com'   => 'pmcvarietylatino.files.wordpress.com',
					'cdn.hollywoodlife.com'   => 'pmchollywoodlife.files.wordpress.com',
					'cdn.tvline.com'          => 'pmctvline2.files.wordpress.com',
					'cdn.deadline.com'        => 'pmcdeadline2.files.wordpress.com',
					'cdn.bgr.com'             => 'boygeniusreport.files.wordpress.com',
					'tap-cdn.rubiconproject.com' => 'tap.rubiconproject.com',
				);

				if (!empty($mapping[$host])) {
					$url_parts['host'] = $mapping[$host];
					$url = 'https:';
				}

				unset($host, $mapping);
			} else {
				// retain https
				$url = 'https:';
			} // if url scheme is https

			$url .= '//' . $url_parts['host'];

			if (!empty($url_parts['path'])) {
				$url .= $url_parts['path'];
			}

			if (!empty($url_parts['query'])) {
				$url .= '?' . $url_parts['query'];
			}

			if (!empty($url_parts['fragment'])) {
				$url .= '#' . $url_parts['fragment'];
			}
		} else {
			// only strip http:, leave https: untouch
			$url = preg_replace('/^http:\/\//i', '//', $url);
		} // http traffic

		return apply_filters('pmc_ssl_friendly_url', $url, $original_url);
	} // function ssl_friendly_url

	/**
	 * apply esc_url and make url ssl friendly
	 * @see function PMC::ssl_friendly_url
	 * @param string $url The url to be sanitized
	 * @return string The sanitized value
	 */
	public static function esc_url_ssl_friendly($url)
	{
		return esc_url(PMC::ssl_friendly_url($url));
	}

	/**
	 * apply esc_url_raw and make url ssl friendly
	 * we need this function to use in js script output
	 * @see function PMC::ssl_friendly_url
	 * @param string $url The url to be sanitized
	 * @return string The sanitized value
	 */
	public static function esc_url_raw_ssl_friendly($url)
	{
		return esc_url_raw(PMC::ssl_friendly_url($url));
	}

	/**
	 * Parse the html and make all image references to comply with HTTPS traffic
	 * @since 2014-09-17 Hau Vong Initial version
	 * @see PMC::esc_url_ssl_friendly
	 * @param string $html the html content to be process
	 * @return string The processed html content
	 */
	public static function html_ssl_friendly($html)
	{
		if (false !== mb_stripos($html, '<img')) {
			$html = preg_replace_callback(
				'/<img([^>]+)\/?>/is',
				function ($matches) {
					$attributes = $matches[1];
					$attributes = preg_replace_callback(
						'/(\w+)\s*=\s*(\'|")([^\'"]+)\2/is',
						function ($matches) {
							$name = $matches[1];
							$value = $matches[3];
							if ('src' == strtolower($name)) {
								return sprintf('%s="%s"', tag_escape($name), PMC::esc_url_ssl_friendly($value));
							}
							return sprintf('%s="%s"', tag_escape($name), esc_attr($value));
						},
						$attributes
					);
					return '<img' . $attributes . '>';
				}, // function
				$html
			);
		}
		return apply_filters('pmc_html_ssl_friendly', $html);
	} // html_ssl_friendly


	/**
	 * Template function to determine if current page is a vertical archive or not
	 *
	 * @param string $vertical (optional) Vertical name to check
	 * @return boolean Returns TRUE if current page is vertical archive else FALSE
	 *
	 * @since 2014-09-15 Amit Gupta
	 */
	public static function is_vertical($vertical = '')
	{
		return is_tax('vertical', $vertical);
	}


	/**
	 * Template function to determine if a post is in a vertical or not
	 *
	 * @param string $vertical (optional) Vertical name to check
	 * @param string|WP_Post $post (optional) Post (ID or object) to check instead of the current post
	 * @return boolean Returns TRUE if post is in vertical else FALSE
	 *
	 * @since 2014-09-15 Amit Gupta
	 */
	public static function in_vertical($vertical, $post = null)
	{
		return has_term($vertical, 'vertical', $post);
	}


	/**
	 * Template function to fetch disk path or URL to a site asset (image/js/css)
	 * allowing for different directory structures across PMC sites, particularly useful
	 * in shared plugins.
	 *
	 * @uses pmc-site-assets-dir Optional filter implemented by a site to give disk path to its assets root. Its needed if assets are not in site theme root.
	 * @uses pmc-site-assets-dir-uri Optional filter implemented by a site to give URI to its assets root. Its needed if assets are not in site theme root.
	 *
	 * @param string $asset Relative path to the asset
	 * @param string $type Type of path needed. Only one of two values accepted, 'url' or 'dir'.
	 * @return string Full path to the asset
	 */
	public static function get_asset_path($asset = '', $type = 'url')
	{
		if (empty($asset) || !is_string($asset)) {
			return $asset;
		}

		$type = (strtolower($type) !== 'url') ? 'dir' : 'url';

		switch ($type) {
			case 'dir':
				$asset = sprintf('%s/%s', untrailingslashit(apply_filters('pmc-site-assets-dir', get_stylesheet_directory())), static::unleadingslashit($asset));
				break;
			case 'url':
				$asset = sprintf('%s/%s', untrailingslashit(apply_filters('pmc-site-assets-dir-uri', get_stylesheet_directory_uri())), static::unleadingslashit($asset));
				break;
		}

		return $asset;
	}


	/**
	 * Template function to check if a site asset (image/js/css)
	 * file exists or not.
	 *
	 * @uses pmc-site-assets-dir Optional filter implemented by a site to give disk path to its assets root. Its needed if assets are not in site theme root.
	 * @uses pmc-site-assets-dir-uri Optional filter implemented by a site to give URI to its assets root. Its needed if assets are not in site theme root.
	 *
	 * @param string $asset Relative path to the asset
	 * @return boolean TRUE if asset file exists else FALSE
	 */
	public static function asset_exists($asset = '')
	{
		if (empty($asset)) {
			return false;
		}

		return (bool) file_exists(static::get_asset_path($asset, 'dir'));
	}


	/**
	 * This function allows throwing of exceptions conditionally (to avoid try/catch
	 * a zillion times) especially when using a template function. It will throw an exception
	 * only if current environment is not production, else it just returns FALSE.
	 *
	 * @param string $message Message to pass to Exception
	 * @param string $exception Name of Exception class
	 * @param mixed $return_on_production (optional) Value to return on production environment
	 * @return void|mixed Retruns FALSE (or value set in $return_on_production) if current environment is production else throws the Exception
	 *
	 * @since 2014-11-26 Amit Gupta - ported over from AwardsLine 2.0
	 */
	public static function maybe_throw_exception($message = '', $exception = 'ErrorException', $return_on_production = false)
	{
		if (static::is_production()) {
			return $return_on_production;
		}

		if (strpos($exception, '\\') === false) {
			$exception = '\\' . $exception;
		}

		throw new $exception($message);
	}


	/**
	 * This function returns an object containing info about gallery linked to a post.
	 *
	 * @param integer|WP_Post $post_id ID or object of Post if not current post
	 * @return object|boolean Object containing info about linked gallery if one found else FALSE
	 *
	 * @since 2014-11-26 Amit Gupta - ported over from AwardsLine 2.0
	 */
	public static function get_linked_gallery($post_id = 0, $include_gallery_items = false)
	{
		$post_id = intval($post_id);

		if (empty($post_id)) {
			//no post ID, bail out
			return false;
		}

		if (!class_exists('PMC_Gallery_Common')) {
			/*
			 * Unable to find PMC_Gallery_Common class, pmc-gallery plugin hasn't been activated.
			 * If current environment is not production then throw an exception to alert developer
			 * else fail silently
			 */
			return self::maybe_throw_exception('PMC_Gallery_Common class not found');
		}

		$linked_gallery = get_post_meta($post_id, PMC_Gallery_Common::KEY . '-linked-gallery', true);

		if (empty($linked_gallery)) {
			//no gallery linked to post, bail out
			return false;
		}

		$linked_gallery = json_decode($linked_gallery);

		if (empty($linked_gallery)) {
			//invalid json, bail out
			return false;
		}

		if ($include_gallery_items) {
			$gallery_items = self::get_linked_gallery_items($post_id);

			if (!empty($gallery_items) && is_array($gallery_items)) {
				$linked_gallery->items = $gallery_items;
			}
		}

		return apply_filters('pmc_linked_gallery', $linked_gallery);
	}

	/**
	 * Get an array of the attachments in a post's linked gallery
	 *
	 * @param int $post_id The ID of a post with a linked gallery
	 *
	 * @return bool|array False on failure. Array of attachment post ID's on success.
	 */
	public static function get_linked_gallery_items($post_id = 0)
	{
		$post_id = intval($post_id);

		if (empty($post_id)) {
			return false;
		}

		$linked_gallery = self::get_linked_gallery($post_id);

		if (!empty($linked_gallery->id)) {
			return self::get_gallery_items($linked_gallery->id);
		}

		return false;
	}

	/**
	 * Template tag to check if a gallery is linked to a post or not.
	 *
	 * @param integer|WP_Post $post_id ID or object of Post if not current post
	 * @return boolean Returns TRUE if a gallery is linked to the post else FALSE
	 *
	 * @since 2014-11-26 Amit Gupta - ported over from AwardsLine 2.0
	 */
	public static function has_linked_gallery($post_id = 0)
	{
		if (self::get_linked_gallery($post_id) !== false) {
			return true;
		}

		return false;
	}

	/**
	 * Get the items in a gallery
	 *
	 * @param int $gallery_id The gallery ID
	 *
	 * @return bool|array False on failure, an array of attachment ids on success.
	 */
	public static function get_gallery_items($gallery_id = 0)
	{
		$gallery_items = get_post_meta($gallery_id, PMC_Gallery_Common::KEY, true);

		if (!empty($gallery_items) && is_array($gallery_items)) {
			return $gallery_items;
		}

		return false;
	}


	/**
	 * This function returns an array containing meta info about the
	 * gallery associated (linked) with a post.
	 *
	 * @param integer|WP_Post $post_id ID or object of Post if not current post
	 * @return array Returns an array containing proper Gallery URL and image count or FALSE if no Gallery is attached to post
	 *
	 * @since 2014-11-26 Amit Gupta - ported over from AwardsLine 2.0
	 */
	public static function get_hero_gallery_meta($post_id = 0)
	{
		$post_id = intval($post_id);

		if (empty($post_id)) {
			//no post ID, bail out
			return false;
		}

		if (!static::has_linked_gallery($post_id)) {
			//no linked gallery, bail out
			return false;
		}

		$linked_gallery = static::get_linked_gallery($post_id);

		if (!class_exists('PMC_Gallery_Thefrontend')) {
			/*
			 * Unable to find PMC_Gallery_Thefrontend class, either this function is being called
			 * in wp-admin or pmc-gallery plugin hasn't been activated.
			 * If current environment is not production then throw an exception to alert developer
			 * else fail silently
			 */
			return static::maybe_throw_exception('PMC_Gallery_Thefrontend class not found');
		}

		$gallery = PMC_Gallery_Thefrontend::load_gallery($linked_gallery->id, 0);

		$gallery_url = $linked_gallery->url;

		if (empty($gallery_url)) {
			$gallery_url = get_permalink($linked_gallery->id);
		}

		return array(
			'url'   => sprintf('%s/#!&ref=%spos=', untrailingslashit($gallery_url), parse_url(get_permalink($post_id), PHP_URL_PATH)),
			'count' => intval(strip_tags($gallery->get_the_count('total'))),
		);
	}

	/**
	 * For Mobile we need to serve images of smaller size. To do that cleanest way is to define seperate image sizes for mobile and let it render automatically whenever images are shown.
	 *
	 * @param string $image_size
	 *
	 * @return bool
	 * @since 2015-01-15 Amit Sannad for PPT-4033
	 */
	public static function get_image_size_name_for_mobile($image_size = "thumbnail")
	{

		if (PMC::is_mobile()) {
			if (has_image_size($image_size . '-mobile')) {
				return $image_size . '-mobile';
			}
		}

		return false;
	}

	/**
	 * Expose pmc_strip_shortcode filter to allow strip short code override
	 * @see strip_shortcodes
	 */
	public static function strip_shortcodes($content, $shortcodes = array())
	{
		global $shortcode_tags;

		if (false === strpos($content, '[')) {
			return $content;
		}

		if (empty($shortcode_tags) || !is_array($shortcode_tags)) {
			return $content;
		}

		$pattern = get_shortcode_regex();
		$content = preg_replace_callback("/$pattern/s", function ($m) use ($shortcodes) {
			// allow [[foo]] syntax for escaping a tag
			if ($m[1] == '[' && $m[6] == ']') {
				return substr($m[0], 1, -1);
			}

			// if $shortcodes list is provided, any shortcode not on list will not be strip
			if (!empty($shortcodes) && !in_array($m[2], $shortcodes)) {
				return $m[0];
			}

			// strip shortcode
			return apply_filters('pmc_strip_shortcode', $m[1] . $m[6], $m[2], $m[0]);
		}, $content);

		return $content;
	}

	/**
	 * Strip external URLs from whatever content is passed to it. This is controlled by a bunch of filters for greatest level of flexibility.
	 *
	 * @uses $post
	 *
	 * @since 2015-07-23 - Mike Auteri - PPT-5181
	 * @version 2015-07-23
	 *
	 * @param string $content
	 * @return string
	 */
	public static function strip_disallowed_urls($content)
	{

		global $post;
		$allowed_hosts = array();

		// Allowed all hosts we consider internal for this site
		$hosts = array(
			parse_url(wpcom_vip_noncdn_uri(dirname(__FILE__)), PHP_URL_HOST),
			parse_url(site_url(), PHP_URL_HOST),
			parse_url(home_url(), PHP_URL_HOST),
			'i0.wp.com',
			'i1.wp.com',
			'i2.wp.com',
			'i3.wp.com',
		);

		/**
		 * pmc_strip_disallowed_urls filter to apply settings for flexiblity on content manipulation
		 *
		 * @var $args array includes:
		 * - external_url_link_source_boolean boolean
		 * - external_url_allowlist_boolean boolean
		 * - external_strip_all_boolean boolean
		 * - site_hosts array
		 * - feeds_external_url_allowlist array
		 * - external_url_allowlist array
		 * - bypass_url_allowlist boolean
		 * @var $post_id integer
		 *
		 * @since 2015-08-12 - Mike Auteri - PPT-5181
		 * @version  2015-08-12
		 *
		 */
		$args = array(
			'external_url_link_source_boolean' => false,
			'external_url_allowlist_boolean'   => false,
			'external_strip_all_boolean'       => false,
			'site_hosts'                       => $hosts,
			'feeds_external_url_allowlist'     => array(),
			'external_url_allowlist'           => array(),
			'bypass_url_allowlist'             => false,
		);
		$settings = apply_filters('pmc_strip_disallowed_urls', $args, $post->ID);

		// @TODO: SADE-517 to be removed
		$mappings = [
			'external_url_whitelist_boolean' => 'external_url_allowlist_boolean',
			'feeds_external_url_whitelist'   => 'feeds_external_url_allowlist',
			'external_url_whitelist'         => 'external_url_allowlist',
			'bypass_url_whitelist'           => 'bypass_url_allowlist',
		];

		foreach ($mappings as $from => $to) {
			if (isset($settings[$from])) {
				$settings[$to] = $settings[$from];
			}
		}

		/**
		 * external_url_link_source_boolean - If TRUE replaces any external, non-allowed URLs with a link back to the source post.
		 *
		 * @since 2015-08-12 - Mike Auteri - PPT-5181
		 * @version 2015-08-12
		 */
		$external_link_source = $settings['external_url_link_source_boolean'];

		/**
		 * external_url_allowlist_boolean - If TRUE strip all non-allowed, non-external domains.
		 *
		 * @since 2015-08-12 - Mike Auteri - PPT-5181
		 * @version 2015-08-12
		 */
		$external_allowlist_boolean = (bool) $settings['external_url_allowlist_boolean'];

		/**
		 * external_strip_all_boolean - If TRUE strips all external URLs.
		 *
		 * @since 2015-08-12 - Mike Auteri - PPT-5181
		 * @version 2015-08-12
		 */
		$external_strip_all = $settings['external_strip_all_boolean'];

		// If all of these are false, just return content. It means no manipulation was instructed.
		if (!$external_link_source && !$external_allowlist_boolean && !$external_strip_all) {
			return $content;
		}

		if (empty($content)) {
			return $content;
		}

		/**
		 * bypass_url_allowlist - If TRUE bypasses running content through this function.
		 *
		 * @since 2015-08-12 - Mike Auteri - PPT-5181
		 * @version 2015-08-12
		 */
		if ($settings['bypass_url_allowlist']) {
			return $content;
		}

		// Handle allow list for feeds and non-feeds here.
		if ($external_allowlist_boolean) {
			if (is_feed()) {

				/**
				 * feeds_external_url_allowlist - Array of external domains to allowlist in a feed.
				 *
				 * @since 2015-08-12 - Mike Auteri - PPT-5181
				 * @version 2015-08-12
				 */
				$allowed_hosts = $settings['feeds_external_url_allowlist'];
			} else {

				/**
				 * external_url_allowlist - Array of external domain to allowlist in a non-feed.
				 *
				 * @since 2015-08-12 - Mike Auteri - PPT-5181
				 * @version 2015-08-12
				 */
				$allowed_hosts = $settings['external_url_allowlist'];
			}
		}

		/**
		 * site_hosts - Array of all internal domains.
		 *
		 * @since 2015-08-12 - Mike Auteri - PPT-5181
		 * @version 2015-08-12
		 */
		$site_hosts = $settings['site_hosts'];

		// Remove any www. if there are any
		foreach ($site_hosts as $key => $value) {
			$site_hosts[$key] = preg_replace('/^www\./', '', $value);
		}

		$allowed_hosts = array_merge($site_hosts, $allowed_hosts);

		// Strip all external links, so set to $site_hosts, which is just the interal link array.
		// This likely needs to be the last condition before process starts.
		if ($external_strip_all) {
			$allowed_hosts = $site_hosts;
		}

		try {
			$doc = new DOMDocument();

			libxml_use_internal_errors(true);
			$doc->loadHTML('<?xml encoding="UTF-8">' . $content); // @change Corey Gilmore 2012-12-14 hack -- http://php.net/manual/en/domdocument.loadhtml.php#95251
			libxml_clear_errors();

			$body = $doc->getElementsByTagName('body')->item(0);
			$anchors = $doc->getElementsByTagName('a');
			$num_anchors = $anchors->length;

			$anchor_search = array();
			$anchor_replace = array();

			// loop backwards, otherwise removing allowed elements screws up enumeration
			for ($x = $num_anchors - 1; $x >= 0; $x--) {
				$allowed_host = false;
				$anchor = $anchors->item($x);
				$href = $anchor->getAttribute('href');
				$anchor_host = parse_url($href, PHP_URL_HOST);
				$anchor_host = preg_replace('/^www\./', '', $anchor_host);

				if (!empty($anchor_host)) {
					$anchor_host = strtolower($anchor_host);
					foreach ($allowed_hosts as $_host) {
						$_host = trim(strtolower($_host));
						if (0 == strcasecmp($anchor_host, $_host)) {
							$allowed_host = true;
							break;
						}
					}

					if ($allowed_host) {
						continue;
					}
				}

				// get the HTML for the non-allowed anchor
				$bad_anchor_html = $doc->saveXML($anchor);

				// @change Mike Auteri 2015-07-22: Clean up decoded html entites. Example: $bad_anchor_html converts HTML entity &rsquo; to ’. This will cause matches to fail.

				// strip tags (except anchor) to match $anchor->textContent to $bad_anchor_html.
				// $anchor_inner_html will repopulate any nested HTML tags that were stripped.
				// This is literally just for apples to apples matching.
				$bad_anchor_html_tag_strip = strip_tags($bad_anchor_html, '<a>');

				// Attempt to preserve HTML inside the anchor (eg: <a href="#">this <em>example</em></a>)
				$anchor_inner_html = PMC_DOM::domnode_get_innerhtml($anchor);

				// fall back to plain text if there is any issue retrieving the contents of the anchor
				if (empty($anchor_inner_html) || $anchor_inner_html == $anchor) {
					$anchor_inner_html = htmlentities($anchor->textContent);
				}

				// $anchor->textContent will match $bad_anchor_html_tag_strip.
				// $anchor_inner_html will replace and include entities and markup that was stripped earlier.
				$bad_anchor_html = str_replace($anchor->textContent, $anchor_inner_html, $bad_anchor_html_tag_strip);

				if ($external_link_source && !$external_strip_all) {
					// Replaces external URL with URL of the post
					$href = $anchor->getAttribute('href');
					$post_url = get_permalink($post->ID);

					$anchor_search[] = $bad_anchor_html;
					$anchor_replace[] = str_replace($href, $post_url, $bad_anchor_html);
				} else {
					// one final safety check - don't accidentally remove any elements
					if (!empty($anchor_inner_html) && !empty($bad_anchor_html)) {
						$anchor_search[] = $bad_anchor_html;
						$anchor_replace[] = $anchor_inner_html;
					}
				}
			}

			if (!empty($anchor_search)) {
				// need contents text to match up since we're using saveXML
				$content = PMC_DOM::domnode_get_innerhtml($body);
				$content = str_replace($anchor_search, $anchor_replace, $content);
			}
		} catch (Exception $e) {
			// Something terrible happened... just return an empty string per Corey Gilmore
			$content = '';
		}

		return $content;
	}	//end strip_disallowed_urls()

	/**
	 * Method to check if an array is associative array or not.
	 *
	 * @param array $array_to_check Array which is to be checked
	 * @return boolean Returns TRUE if the array is associative else FALSE. Even a single numeric key would make this function return FALSE.
	 *
	 * @since 2015-10-21 Amit Gupta
	 */
	public static function is_associative_array(array $array_to_check)
	{
		return !(bool) count(array_filter(array_keys($array_to_check), 'is_numeric'));
	}

	/**
	 * Method to get the current site name determined on basis of active theme.
	 * This function can be used anywhere in theme/plugin without waiting for any dependency to load.
	 *
	 * @return string
	 *
	 * @since 2015-11-02 Amit Gupta
	 * @version 2016-10-12 Brandon Camenisch <bcamenisch@pmc.com> - feature/PMCVIP-2213:
	 * - Adding support for GD theme
	 */
	public static function get_current_site_name()
	{

		$current_theme = explode('/', get_stylesheet_directory());

		if (!is_array($current_theme) || count($current_theme) < 2) {
			return false;
		}

		switch (array_pop($current_theme)) {

			case 'bgr':
				return 'bgr';

			case 'pmc-411':
			case 'pmc-411-mobile':
				return '411';

			case 'pmc-deadline':
				return 'deadline';

			case 'pmc-footwearnews':
				return 'footwearnews';

			case 'pmc-hollywoodlife':
			case 'pmc-hollywoodlife-2015':
			case 'pmc-hollywoodlife-2017':
				return 'hollywoodlife';

			case 'pmc-tvline-2014':
				return 'tvline';

			case 'pmc-variety-2014':
				return 'variety';

			case 'pmc-variety-latino':
				return 'varietylatino';

			case 'pmc-wwd-2015':
			case 'pmc-wwd-2016':
				return 'wwd';

			case 'pmc-goldderby':
				return 'goldderby';

			case 'pmc-indiewire-2016':
			case 'indiewire':
				return 'indiewire';

			case 'pmc-robbreport-2017':
				return 'robbreport';
		}

		return false;
	}

	/**
	 * Conditional method to check if current site is same as the name passed to the method.
	 *
	 * @param string $site_name Name of the site which is to be checked
	 * @return boolean Returns TRUE if current site name matches $site_name else FALSE
	 *
	 * @since 2015-11-02 Amit Gupta
	 */
	public static function is_current_site_name($site_name = '')
	{

		if (!empty($site_name) && is_string($site_name) && strtolower($site_name) == static::get_current_site_name()) {
			return true;
		}

		return false;
	}

	/**
	 * @since 2015-11-04 Amit Sannad
	 *        Copied this from pmc-custom-feed-helper in v2 by @hvong
	 * @static
	 *
	 * @param $content string
	 *
	 * @return string
	 * method to do data escaping for xml node and allow filter to modify content
	 */
	public static function esc_xml($content)
	{
		$content = apply_filters('pmc_custom_feed_data', $content);

		// we need to decode html entities first
		$content = html_entity_decode($content, ENT_QUOTES);

		$content = strtr($content, array(
			'&' => '&amp;',
			'<' => '&lt;',
			'>' => '&gt;',
			'"' => '&quot;',
		));

		return self::encode_numericentity($content);
	}

	public static function encode_numericentity($content)
	{
		$convmap = array(0x80, 0xffff, 0, 0xffff);
		$content = ent2ncr($content);
		$content = mb_encode_numericentity($content, $convmap, 'UTF-8');

		return $content;
	}

	/**
	 * Custom implementation of WordPress function is_post_type_archive().
	 * It accepts only a string as parameter, multiple post types can be passed
	 * as comma separated values.
	 *
	 * @since 2016-09-09 Amit Gupta
	 *
	 * @param string $post_type Post types to check. Multiple post types can be passed as comma separated values.
	 * @return boolean Returns TRUE if current page is archive of post type(s) passed as parameter, else FALSE
	 */
	public static function is_post_type_archive($post_type)
	{

		if (empty($post_type) || !is_string($post_type)) {
			//invalid data, bail out
			return false;
		}

		$post_types = explode(',', $post_type);
		$post_types = array_map('trim', $post_types);

		return is_post_type_archive($post_types);
	}

	/**
	 * This method is an improved version of PHP's filter_input() and
	 * works well on PHP Cli as well which PHP default method does not.
	 *
	 * @since 2017-10-05 Amit Gupta
	 *
	 * @param int $type One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV
	 * @param string $variable_name Name of a variable to get
	 * @param int $filter The ID of the filter to apply
	 * @param mixed $options filter to apply
	 * @return mixed Value of the requested variable on success, FALSE if the filter fails, or NULL if the variable_name variable is not set.
	 */
	public static function filter_input($type, $variable_name, $filter = FILTER_DEFAULT, $options = null)
	{

		// Cannot run coverage on CLI
		// @codeCoverageIgnoreStart

		if (php_sapi_name() !== 'cli') {
			/*
			 * Code is not running on PHP Cli and we are in clear.
			 * Use the PHP method and bail out.
			 */
			switch ($filter) {
				case FILTER_SANITIZE_STRING:
					$sanitized_variable = sanitize_text_field(filter_input($type, $variable_name, $filter, $options));
					break;
				default:
					$sanitized_variable = filter_input($type, $variable_name, $filter, $options);
					break;
			}
			return $sanitized_variable;
		}

		// @codeCoverageIgnoreEnd

		/*
		 * Code is running on PHP Cli and INPUT_SERVER returns NULL
		 * even for set vars when run on Cli
		 * See: https://bugs.php.net/bug.php?id=49184
		 *
		 * This is a workaround for that bug till its resolved in PHP binary
		 * which doesn't look to be anytime soon. This is a friggin' 10 year old bug.
		 */

		$input = '';

		$allowed_html_tags = wp_kses_allowed_html('post');

		/*
		 * Marking the switch() block below to be ignored by PHPCS
		 * because PHPCS squawks on using superglobals like $_POST or $_GET
		 * directly but it can't be helped in this case as this code
		 * is running on Cli.
		 */

		// @codingStandardsIgnoreStart

		switch ($type) {

			case INPUT_GET:
				if (!isset($_GET[$variable_name])) {
					return null;
				}

				$input = wp_kses($_GET[$variable_name], $allowed_html_tags);
				break;

			case INPUT_POST:
				if (!isset($_POST[$variable_name])) {
					return null;
				}

				$input = wp_kses($_POST[$variable_name], $allowed_html_tags);
				break;

			case INPUT_COOKIE:
				if (!isset($_COOKIE[$variable_name])) {
					return null;
				}

				$input = wp_kses($_COOKIE[$variable_name], $allowed_html_tags);
				break;

			case INPUT_SERVER:
				if (!isset($_SERVER[$variable_name])) {
					return null;
				}

				$input = wp_kses($_SERVER[$variable_name], $allowed_html_tags);
				break;

			case INPUT_ENV:
				if (!isset($_ENV[$variable_name])) {
					return null;
				}

				$input = wp_kses($_ENV[$variable_name], $allowed_html_tags);
				break;

			default:
				return null;
				break;
		}	// end switch()

		// @codingStandardsIgnoreEnd

		return filter_var($input, $filter, $options);
	}	//end filter_input()

	/**
	 * Method to get word count of any text block
	 *
	 * @param string $text
	 * @return int
	 */
	public static function get_word_count(string $text): int
	{

		if (empty($text)) {
			return 0;
		}

		return intval(
			str_word_count(
				trim(
					wp_strip_all_tags(strip_shortcodes($text), true)
				)
			)
		);
	}

	/**
	 * Method to check for existence of a file
	 *
	 * @param string $file_path Physical path of the file which is to be checked
	 *
	 * @return bool Returns TRUE if file path is valid else FALSE
	 */
	public static function is_file_path_valid(string $file_path): bool
	{

		if (!empty($file_path) && file_exists($file_path) && validate_file($file_path) === 0) {
			return true;
		}

		return false;
	}

	/**
	 * Opposite of what abs() does. This method returns the negative value of an integer.
	 *
	 * @param int $number
	 *
	 * @return int
	 */
	public static function get_negative_int(int $number = 0): int
	{

		if (0 >= $number) {
			return $number;
		}

		return (-1 * $number);
	}

	/**
	 * Is a valid URL of the current domain.
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	public static function is_current_domain(string $url): bool
	{

		if (false !== filter_var($url, FILTER_VALIDATE_URL)) {
			return (wp_parse_url(home_url())['host'] === wp_parse_url($url)['host']); // phpcs:ignore
		}

		return false;
	}

	/**
	 * Helper to wp_kses_allowed_html to limit or expand HTML tags for use in KSES.
	 *
	 * @param string|array $context The context for which to retrieve tags.
	 *                              Allowed values are 'post', 'strip', 'data',
	 *                              'entities', or the name of a field filter
	 *                              such as 'pre_user_description'.
	 * @param array        $limit   Limit the HTML tags allowed from the context provided.
	 * @param array        $expand  Associative array to expand on the context given to
	 *                              allow additional HTML tags not provided within that
	 *                              context.
	 * @param bool         $force   If true, the context of $expand will override a
	 *                              previous set value within the context rather than
	 *                              attempt to merge the values.
	 *
	 * @return array
	 */
	public static function allowed_html($context = 'post', array $limit = [], array $expand = [], bool $force = false): array
	{

		$allowed_html = wp_kses_allowed_html($context);
		$return       = [];

		if (!empty($limit)) {
			foreach ($limit as $key) {
				if (isset($allowed_html[$key])) {
					$return[$key] = $allowed_html[$key];
				}
			}
		} else {
			$return = $allowed_html;
		}

		if (!empty($expand) && static::is_associative_array($expand)) {
			if ($force) {
				$return = array_merge($return, $expand);
			} else {
				foreach ($expand as $key => $value) {
					$return[$key] = (!isset($return[$key])) ? [] : $return[$key];
					$return[$key] = array_merge($return[$key], $value);
				}
			}
		}

		return $return;
	}

	/**
	 * Checks if the current user is a cxense bot.
	 * @see : https://jira.pmcdev.io/browse/PMCS-2069
	 * @TODO: Change references to this function to use Device class is_bot function
	 * @codeCoverageIgnore
	 * @return bool
	 */
	public static function is_cxense_bot(): bool
	{
		// @codeCoverageIgnoreStart
		// Variant determiner for caches.
		if (function_exists('vary_cache_on_function')) {
			vary_cache_on_function(self::is_cxense_bot_function_string());
		}

		return isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/cxensebot/i', $_SERVER['HTTP_USER_AGENT']); // phpcs:ignore
		// @codeCoverageIgnoreEnd
	}

	public static function is_cxense_bot_function_string()
	{
		return 'return isset( $_SERVER[\'HTTP_USER_AGENT\'] ) && preg_match( \'/cxensebot/i\', $_SERVER[\'HTTP_USER_AGENT\'] );';
	}

	/**
	 * Copy of _wp_render_title_tag() with additional code to untexturize title before rendering it
	 *
	 * @see _wp_render_title_tag()
	 *
	 * @return void
	 */
	public static function render_title_tag(): void
	{
		if (!current_theme_supports('title-tag')) {
			return;
		}

		$title_og = wp_get_document_title();
		$title    = static::untexturize($title_og);


		/**
		 * Filter to allow override on title string before it is rendered on frontend.
		 *
		 * @param string $title    Untexturized version of title string which will be rendered on frontend
		 * @param string $title_og Original title string as returned by WP
		 */
		$title = apply_filters('pmc_render_title_tag', $title, $title_og);

		printf('<title>%s</title>' . PHP_EOL, $title);    // phpcs:ignore

	}

	/**
	 * Helper function to add menu only if it already not exist to prevent duplicates
	 * @see WP function: add_menu_page
	 */
	public static function maybe_add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function = '__return_false', $icon_url = '', $position = null): void
	{
		global $menu, $submenu;

		foreach ($menu as $items) {
			if (in_array($menu_slug, (array) $items, true)) {
				return;
			}
		}

		add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);

		if ('__return_false' === $function) {
			if (!isset($submenu[$menu_slug])) {
				$submenu[$menu_slug] = [];
			}
		}
	}

	/**
	 * Method to get value of a nested key in a multi-dimensional array
	 * by specifying nesting as separated by forward slash.
	 * This is similar to how folder nesting is specified on path.
	 *
	 * Example:
	 *
	 * $a = [
	 *     'vehicles' => [
	 *         'car'   => [
	 *             'daily'   => 'Tesla',
	 *             'fun'     => 'McLaren',
	 *             'weekend' => 'Maybach',
	 *         ],
	 *         'plane' => 'Gulfstream',
	 *         'bike'  => 'Ducati',
	 *     ],
	 * ];
	 *
	 * $daily_car = PMC::get_array_value( $a, 'vehicles/car/daily' );
	 *
	 *
	 * @param array  $arr Array from which value is to be fetched
	 * @param string $key Forward slash separated array keys - each forward slash denotes next sub-dimension
	 *
	 * @return array|mixed|null Returns NULL if key does not exist else returns the value corresponding to the key
	 */
	public static function get_array_value(array $arr, string $key)
	{

		if (empty($key)) {
			return null;
		}

		if (isset($arr[$key])) {
			return $arr[$key];
		}

		$keys       = explode('/', $key);
		$keys       = array_filter($keys);
		$keys       = array_values($keys);
		$keys_count = count($keys);

		$value = null;

		for ($i = 0; $i < $keys_count; $i++) {

			$value_to_use = (0 === $i) ? $arr : $value_to_use;

			if (!isset($value_to_use[$keys[$i]])) {
				break;
			}

			$value_to_use = $value_to_use[$keys[$i]];

			if (($keys_count - 1) === $i) {
				$value = $value_to_use;
			}
		}

		return $value;
	}

	/**
	 * Updated version of PHP's intval() since its rather stupid.
	 * PHP's intval() returns `10` int value for both `'10'` and `'10-02-2019'`
	 * which is rather stupid. If a string has to be converted to an integer
	 * then there's no point in converting it partially - either its completely int or its not.
	 *
	 * @param mixed $val Value which is to be converted to an integer
	 * @param int   $base
	 *
	 * @return int Integer value if the parameter passed is numeric else it returns zero.
	 */
	public static function intval($val, int $base = 10): int
	{
		return (is_numeric($val)) ? intval($val, $base) : 0;
	}

	/**
	 * Return true if WP CLI is active
	 * @param bool $value If passed, set the value to be returned (use by unit test to mock the value)
	 * @return bool|null
	 */
	public static function is_wp_cli($value = null)
	{
		if (!isset(self::$_is_wp_cli) || isset($value)) {
			if (!isset($value) || !is_bool($value)) {
				$value = defined('WP_CLI') && WP_CLI;
			}
			self::$_is_wp_cli = $value;
		}
		return self::$_is_wp_cli;
	}

	/**
	 * @return string
	 */
	public static function lob(): string
	{
		$lob = defined('PMC_SITE_NAME') ? PMC_SITE_NAME : '';
		return (string) apply_filters('pmc_lob', $lob);
	}
}	//end of class

// Mandate the saving of menu_order
add_action('pre_post_update', 'PMC::gallery_menu_order_fix');

add_action('wp_headers', 'PMC::set_ie_standard_compatibility');

/**
 * Enable custom CDNs
 *
 * @version 2014-12-12 Corey Gilmore PPT-3846: Move from init::1 to parse_query::10 to allow more granular control (eg, use is_* functions)
 */
add_action('parse_query', 'PMC::load_custom_cdn');

// Required for PMC::get_the_sanitized_excerpt()
add_filter('wp_kses_allowed_html', array('PMC', '_kses_excerpt_allowed_html'), 10, 2);

//EOF
