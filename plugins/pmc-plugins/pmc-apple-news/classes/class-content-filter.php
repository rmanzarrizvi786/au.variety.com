<?php

namespace PMC\Apple_News;

use PMC;
use PMC\Global_Functions\Traits\Singleton;
use PMC_Featured_Video_Override;
use PMC\EComm\Tracking;

class Content_Filter
{

	use Singleton;

	/**
	 * @var array to store buy-button component style
	 */
	protected $_buy_button_style;

	protected $_default_styles = [];

	/**
	 * Constructor
	 */
	protected function __construct()
	{
		$this->_setup_hooks();
	}

	/**
	 * Method to setup hooks.
	 *
	 * @return void
	 */
	protected function _setup_hooks()
	{

		add_action('init', [$this, 'init_buy_button_for_apple_news']);
		add_action('admin_init', [$this, 'create_pmc_carousel_term']);

		add_filter('apple_news_exporter_content_pre', array($this, 'maybe_append_amazon_products'), 8, 2);
		add_filter('apple_news_exporter_content_pre', array($this, 'exporter_content_pre'), 10, 2);
		add_filter('apple_news_exporter_content', array($this, 'filter_apple_news_exporter_content'));
		add_filter('apple_news_exporter_title', array($this, 'filter_apple_news_exporter_title'), 10, 2);
		add_filter('apple_news_publish_capability', array($this, 'change_apple_news_publish_capability'));
		add_filter('apple_news_list_capability', array($this, 'change_apple_news_list_capability'));
		add_filter('apple_news_exporter_excerpt', array($this, 'filter_apple_news_exporter_excerpt'), 10, 2);
		add_filter('apple_news_get_json', array($this, 'get_unescaped_unicode_json'));
		add_filter('apple_news_body_json', array($this, 'decode_special_chars'));
		add_filter('apple_news_api_post_meta', array($this, 'filter_apple_news_api_post_meta'));
		add_filter('apple_news_generate_json', array($this, 'filter_apple_news_generate_json'), 10, 2);

		// We need to use priority 15 to all code run other filter finished in order to decode the json data
		add_filter('apple_news_generate_json', [$this, 'filter_update_promo_module'], 15, 2);
		add_filter('apple_news_exporter_content_pre', [$this, 'add_promo_content'], 10, 2);

		// Added with "99" priority so all other operations complete first on this hook
		add_filter('apple_news_generate_json', array($this, 'update_amazon_affiliate_code'), 99, 2);

		// Need to register any missing default styles as late as possible with priority 99
		add_filter('apple_news_component_text_styles', [$this, 'register_default_styles_if_not_defined'], 99);
	}

	public function init_buy_button_for_apple_news()
	{

		if (true === apply_filters('pmc_apple_news_enable_buy_button', false)) {
			// we need to use priority 20 to do post processing to fix the json data and inject flag allowAutoplacedAds=false where needed
			add_filter('apple_news_generate_json', [$this, 'add_buy_button_component_to_apple_news_json'], 20, 2);
			add_filter('apple_news_component_text_styles', [$this, 'register_apple_news_custom_component_text_styles']);
			add_filter('apple_news_component_layouts', [$this, 'register_apple_news_custom_layouts']);
		}
	}

	/**
	 * This does not do anything to post content.
	 * It will add filter to change mark up for pmc related link.
	 * To make it Apple News compatible.
	 *
	 * PMCRS-815 - Featured Video is not getting passed on to Apple News.
	 * We have a PMC_Featured_Video_Override plugin that renders the video at the top of the article page.
	 * Using the filter 'apple_news_exporter_content_pre' that we can hook into to pass on this video HTML.
	 * Fixing the bug using this filter
	 *
	 * @param  string  $content Post content for Apple News Exporter.
	 * @param  integer $post_id Post ID.
	 *
	 * @return string Post content for Apple News Exporter.
	 */
	public function exporter_content_pre($content = '', $post_id = 0)
	{

		if (false === has_filter('pmc-related-link-shortcode-markup', array($this, 'get_related_link_markup'))) {
			add_filter('pmc-related-link-shortcode-markup', array($this, 'get_related_link_markup'), 10, 3);
		}

		// Using priority 11: To override page-template of desktop.
		add_filter('pmc_automated_related_links_template', [$this, 'get_apple_news_related_links_template'], 11);

		/**
		 * Add custom handler for [jwplatform] & [jwplayer] shortcodes
		 */
		global $shortcode_tags;

		// Back up current registered shortcodes and clear them all out.
		$orig_shortcode_tags = $shortcode_tags;

		remove_all_shortcodes();

		add_shortcode('jwplatform', array($this, 'handle_jwplayer_shortcode'));
		add_shortcode('jwplayer', array($this, 'handle_jwplayer_shortcode'));
		add_shortcode('youtube', array($this, 'handle_youtube_shortcode'));

		if (true === apply_filters('pmc_apple_news_enable_buy_button', false)) {
			add_shortcode('buy-now', [$this, 'handle_buy_now_shortcode']);
		}

		if (
			true === apply_filters('pmc_automated_related_links_allow_on_apple_news', false, $post_id)
			&& !empty($post_id)
			&& intval($post_id) > 0
			&& class_exists('PMC\Automated_Related_Links\Frontend')
		) {

			$related_links = \PMC\Automated_Related_Links\Frontend::get_instance()->inject_related_links([]);
			$content       = $this->insert_related_links_to_post_content($content, $related_links);
		}

		if (!empty($post_id) && intval($post_id) > 0 && class_exists('PMC_Featured_Video_Override')) {

			// Escaped HTML coming from PMC_Featured_Video_Override.
			$featured_video = PMC_Featured_Video_Override::get_video_html($post_id, array('width' => 300));

			if ($featured_video) {
				$content = $featured_video . PHP_EOL . $content;
			}
		}

		$content = do_shortcode($content, true);

		// Put the original shortcodes back.
		$shortcode_tags = $orig_shortcode_tags;

		return $content;
	}

	/**
	 * Inserts related link to apple news feed.
	 *
	 * @param string $content      Post content
	 * @param array $related_links Array of related links to add into post content
	 *
	 * @return string Updated post content.
	 */
	public function insert_related_links_to_post_content($content, $related_links)
	{

		$content       = wpautop($content);
		$content_array = explode('</p>', $content);

		foreach ($content_array as $key => $value) {
			$content_array[$key] = $content_array[$key] . '</p>';
		}

		foreach ($related_links as $key => $value) {

			$value                 = (is_array($value)) ? $value : [$value];
			$content_array[$key] = (isset($content_array[$key])) ? $content_array[$key] : [];
			$content_array[$key] = $content_array[$key] . implode('', $value);
		}

		$content = implode('', $content_array);

		return $content;
	}

	/**
	 * To get template for related link module for Apple news.
	 *
	 * @param string $template Template Path.
	 *
	 * @return string template path.
	 */
	public function get_apple_news_related_links_template($template)
	{
		return sprintf('%s/templates/apple-news-related-links.php', PMC_APPLE_NEWS);
	}

	/**
	 * To remove filter added in apple_news_exporter_content_pre.
	 *
	 * @since 2017-09-15 Archana Mandhare PMCRS-815
	 * @version 2018-03-14 - Dhaval Parekh - READS-1109 - Remove code added in PMCRS-815
	 *
	 * @param $content string post content
	 *
	 * @return string
	 */
	public function filter_apple_news_exporter_content($content)
	{

		// Remove filter added in $this->exporter_content_pre()
		if (false !== has_filter('pmc-related-link-shortcode-markup', array($this, 'get_related_link_markup'))) {
			remove_filter('pmc-related-link-shortcode-markup', array($this, 'get_related_link_markup'));
		}
		remove_filter('pmc_automated_related_links_template', [$this, 'pmc_automated_related_links_template']);

		return $content;
	}

	/**
	 *
	 * PMCRS-814 - The "&" inside the post title was not getting escaped and was being rendered as &amp; in Apple News.
	 * On checking the Apple News plugin I realised it is using raw $post->post_title
	 * but has the filter 'apple_news_exporter_title' that we can hook into.
	 * Fixing the bug using this filter
	 *
	 * @since   2017-09-15 Archana Mandhare PMCRS-814
	 * @version 2018-08-30 Jignesh Nakrani READS-1463 - SEO Title to be pulled and displayed on Apple News articles
	 *
	 * @param string $post_title $post->post_title that is passed by this filter.
	 * @param int    $post_id the integer ID of the post that we are sending to apple News.
	 *
	 * @return string
	 */
	public function filter_apple_news_exporter_title($post_title, $post_id)
	{

		// Get the setting value for option.
		$settings      = get_option('apple_news_settings', array());
		$use_seo_title = empty($settings['use_seo_title']) ? 'no' : $settings['use_seo_title'];

		// Get title from seo meta box.
		$post_seo_title = get_post_meta($post_id, 'mt_seo_title', true);

		if ('yes' !== $use_seo_title || empty($post_seo_title)) {

			$post_seo_title = apply_filters('the_title', $post_title, $post_id);
		}

		return wp_strip_all_tags(\PMC::untexturize(stripslashes($post_seo_title)));
	}

	/**
	 * Allow authors and above to automatically
	 * publish their posts on Apple News.
	 *
	 * @see https://vip.wordpress.com/plugins/apple-news/
	 *
	 * @since 2018-01-02 Archana Mandhare PMCRS-1104
	 *
	 * @return string
	 */
	public function change_apple_news_publish_capability()
	{
		return 'publish_posts';
	}

	/**
	 * Allow editors and above to see the Apple News
	 * listing screen.
	 *
	 * Users with this capability will be able to push any posts
	 * to the Apple News channel
	 *
	 * @see https://vip.wordpress.com/plugins/apple-news/
	 *
	 * @since 2018-01-02 Archana Mandhare PMCRS-1104
	 *
	 * @return string
	 */
	public function change_apple_news_list_capability()
	{
		return 'edit_others_posts';
	}

	/**
	 * The "&" inside the post excerpt was not getting escaped and was being rendered as &amp; in Apple News.
	 * So, we can hook into 'apple_news_exporter_title' filter and fix that bug.
	 *
	 * @param  string $post_excerpt Post excerpt.
	 * @param  int    $post_id Post ID.
	 *
	 * @return string Post excerpt.
	 */
	public function filter_apple_news_exporter_excerpt($post_excerpt, $post_id)
	{

		// To match and special/unicode charactor suffixed with 3 dots (...) at the end of string.
		// because apple-new plugin take 55 word from content and add 3 dots (witout space)
		// if post don't have excerpt
		// It is raising error or sometime prevent for publishing post to apple news.
		// Regex for string likes "Hello world ë...".
		$regex = '/[^a-zA-Z\s\d](\.\.\.)$/D';
		preg_match($regex, $post_excerpt, $matches);

		if (!empty($matches)) {
			$post_excerpt = substr($post_excerpt, 0, -3);
			$post_excerpt = $post_excerpt . ' ...';
		}

		// Since, Apple News plugin not applying 'the_excerpt' filter and taking data from global post.
		// We need to apply it here.
		return html_entity_decode(apply_filters('the_excerpt', $post_excerpt, $post_id));
	}

	/**
	 * To convert "&amp;" into & for post body content.
	 * Filter in Apple_Exporter\Components\Component::to_array()
	 *
	 * @param  array $json JSON Object.
	 *
	 * @return array
	 */
	public function decode_special_chars($json)
	{

		if (empty($json) || !is_array($json)) {
			return $json;
		}

		if (empty($json['text'])) {
			return $json;
		}

		$json['text'] = html_entity_decode($json['text']);

		return $json;
	}

	/**
	 * To convert unicode version of charator to it's actual version.
	 *
	 * @param  string $json JSON String.
	 *
	 * @return string JSON String.
	 */
	public function get_unescaped_unicode_json($json)
	{

		$content = json_decode($json);

		return wp_json_encode($content, JSON_UNESCAPED_UNICODE);
	}

	/**
	 * To overwrite HTML markup of Related link.
	 *
	 * @param  string $markup HTML markup of related link.
	 * @param  array  $attrs Shortcode attributes.
	 * @param  string $content Shortcode content.
	 *
	 * @return string HTML markup of related link.
	 */
	public function get_related_link_markup($markup, $attrs, $content)
	{

		if (empty($attrs['href'])) {
			return $markup;
		}

		$attrs = wp_parse_args(
			$attrs,
			[
				'type'   => 'Related',
				'target' => '',
			]
		);

		return sprintf(
			'<p><strong>%1$s</strong>&nbsp;<a href="%2$s" title="%3$s" target="%4$s">%5$s</a></p>',
			esc_html($attrs['type']),
			esc_url($attrs['href']),
			esc_attr($content),
			esc_attr($attrs['target']),
			esc_html($content)
		);
	}


	/**
	 * To handle jwplayer shortcode for Apple News.
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string HTML mark compatiable with Apple news.
	 */
	public function handle_jwplayer_shortcode($atts)
	{

		if (empty($atts) || !is_array($atts) || empty($atts[0])) {
			return '';
		}

		$content_mask = get_option('jwplayer_content_mask');
		$content_mask = (!empty($content_mask)) ? $content_mask : JWPLAYER_CONTENT_MASK;

		$protocol = (JWPLAYER_CONTENT_MASK === $content_mask) ? 'https' : 'http';

		$video_url = sprintf('%s://%s/manifests/%s.m3u8', $protocol, $content_mask, $atts[0]);

		return sprintf('<video src="%s" />', esc_url($video_url));
	}

	/**
	 * To handle Youtube shortcode for Apple News.
	 *
	 * @param  array $atts Shortcode attributes.
	 *
	 * @return string HTML mark compatiable with Apple news.
	 */
	public function handle_youtube_shortcode($atts)
	{

		if (empty($atts) || !is_array($atts) || empty($atts[0])) {
			return '';
		}

		$url = ltrim($atts[0], '=');

		$youtube_domain = array(
			'youtu.be',
			'www.youtube.com',
			'youtube.com',
		);

		if (!wpcom_vip_is_valid_domain($url, $youtube_domain)) {
			return '';
		}

		return html_entity_decode($url);
	}

	/**
	 * Function to add custom style for apple-news component text only
	 *
	 * @param array $style Array of registered component text styles
	 *
	 * @return array
	 */
	public function register_apple_news_custom_component_text_styles($style = []): array
	{

		$style['buy-button-text-style'] = apply_filters(
			'pmc_apple_news_buy_button_text_style',
			[
				'textColor'  => '#ffd535',
				'fontWeight' => 'bold',
				'fontSize'   => 20,
			]
		);

		return $style;
	}

	/**
	 * Register any default styles at are not yet been defined, eg. default-heading-2
	 * @param array $styles
	 * @return array
	 */
	public function register_default_styles_if_not_defined($styles = []): array
	{
		foreach ($this->_default_styles as $name) {
			if (!isset($styles[$name])) {
				if (preg_match('/^default-heading-(\d)$/', $name, $matches)) {
					$styles[$name] = $this->generate_default_heading_style($matches[1]);
				}
			}
		}
		return (array) $styles;
	}

	/**
	 * Mark a default style to be required that is being used by a template for json formatting.
	 */
	public function require_default_style(string $name)
	{
		$this->_default_styles[$name] = $name;
	}

	/**
	 * Generate the default heading style
	 * @see Apple_Exporter\Components\Heading
	 *
	 * @param int $level
	 * @return array
	 */
	public function generate_default_heading_style(int $level)
	{
		$theme = \Apple_Exporter\Theme::get_used();
		return [
			'fontName'      => $theme->get_value('header' . $level . '_font'),
			'fontSize'      => intval($theme->get_value('header' . $level . '_size')),
			'lineHeight'    => intval($theme->get_value('header' . $level . '_line_height')),
			'textColor'     => $theme->get_value('header' . $level . '_color'),
			'textAlignment' => 'left',
			'tracking'      => intval($theme->get_value('header' . $level . '_tracking')) / 100,
		];
	}

	/**
	 * Function to add custom layouts for apple-news component
	 *
	 * @param array $layout Array of component layouts
	 *
	 * @return array
	 */
	public function register_apple_news_custom_layouts($layout = []): array
	{

		$layout['buy-button-layout'] = apply_filters(
			'pmc_apple_news_buy_button_layout',
			[
				'margin'  => 5,
				'padding' => [
					'left'   => '25vw',
					'right'  => '25vw',
					'bottom' => 15,
					'top'    => 15,
				],
			]
		);

		return $layout;
	}

	/**
	 * Filter apple news json resoponse to support gallery images.
	 *
	 * @param array   $json    current json with all components.
	 * @param integer $post_id publishing post id.
	 *
	 * @return array $json new json with gallery component.
	 */
	public function filter_apple_news_generate_json($json, $post_id)
	{

		if (empty($post_id)) {
			return $json;
		}

		$post_type              = get_post_type($post_id);
		$gallery_attachment_ids = array();
		$gallery_id             = 0;
		$gallery_component      = array();

		if ((!empty($post_type)) && ('pmc-gallery' === $post_type)) {
			$gallery_id = $post_id;
		} else {
			$gallery = get_post_meta($post_id, 'pmc-gallery-linked-gallery', true);
			$gallery = json_decode($gallery, true); // Converting response to assoc array.

			if (!empty($gallery) && is_array($gallery) && !empty($gallery['id'])) {
				$gallery_id = $gallery['id'];
			}
		}

		if (empty($gallery_id)) {
			return $json;
		}

		$gallery_attachment_ids = get_post_meta($gallery_id, 'pmc-gallery', true);

		if (!empty($gallery_attachment_ids) && is_array($gallery_attachment_ids)) {
			$items = array();

			/**
			 * As attachment attached to gallery creates new pmc_gallery_attachments post type, so need to use key of pmc-gallery meta response as.
			 * attachments are stored as key => value of {gallery_attachment_id} => {attachment_id} in pmc-gallery meta.
			 * new created pmc_gallery_attachments store caption in caption post meta.
			 */
			foreach ($gallery_attachment_ids as $gallery_attachment_id => $attachment_id) {
				$gallery_attachment_url     = wp_get_attachment_url($attachment_id);
				$gallery_attachment_caption = get_post_meta($gallery_attachment_id, 'caption', true);
				$gallery_attachment_caption = (!empty($gallery_attachment_caption)) ? $gallery_attachment_caption : '';

				if (!empty($gallery_attachment_url)) {
					$items[] = array(
						'URL'     => $gallery_attachment_url,
						'caption' => html_entity_decode(wp_strip_all_tags($gallery_attachment_caption)),
					);
				}
			}

			$gallery_component[] = array(
				'role'  => 'gallery',
				'items' => $items,
			);
		}

		if (!empty($json) && is_array($json) && !empty($json['components']) && is_array($json['components'])) {
			/**
			 * Used array splice to adjust gallery position, To keep gallery after title and byline.
			 * ( so 0 position will be title and 1 position will be byline after we want gallery images )
			 *
			 * @before [ 'title', 'byline', 'body-paragraph-1', 'body-paragraph-2' ]
			 * @after  [ 'title', 'byline', 'gallery', 'body-paragraph-1', 'body-paragraph-2' ]
			 */
			array_splice($json['components'], 2, 0, $gallery_component);
		}

		return $json;
	}

	/**
	 * Check for sections list in meta and add main section as default section.
	 *
	 * @param array $meta post meta values.
	 *
	 * @return array $meta updated post meta.
	 */
	public function filter_apple_news_api_post_meta($meta)
	{

		if (empty($meta) || !is_array($meta)) {
			return $meta;
		}

		$sections          = \Admin_Apple_Sections::get_sections();
		$main_section_link = '';
		$meta_sections     = array();

		if (empty($sections) || !is_array($sections)) {
			return $meta;
		}

		foreach ($sections as $section) {
			if ((!empty($section->name) && ('main' === strtolower($section->name)))) {
				$main_section_link = (!empty($section->links->self)) ? $section->links->self : '';
				break;
			}
		}

		$meta_sections = (!empty($meta['data']['links']['sections']) && is_array($meta['data']['links']['sections'])) ? $meta['data']['links']['sections'] : array();

		if (
			!empty($main_section_link) &&
			!in_array($main_section_link, (array) $meta_sections, true)
		) {
			$meta['data']['links']['sections'][] = $main_section_link;
		}

		return $meta;
	}

	/**
	 * To handle buy-now shortcode for Apple News.
	 *
	 * @param  array  $atts    Shortcode attributes.
	 * @param  string $content
	 *
	 * @return string HTML mark to make button compatiable with Apple news.
	 */
	public function handle_buy_now_shortcode($atts = [], $content = ''): string
	{

		$output = '';

		if (class_exists('\PMC\Store_Products\Product') && !empty($atts['asin'])) {
			$product = \PMC\Store_Products\Product::create_from_asin($atts['asin']);

			if (is_object($product)) {
				$atts['url']   = $product->url;
				$atts['title'] = $product->title;
				$atts['price'] = $product->price;
			}
		}

		if (
			!empty($atts)
			&& is_array($atts)
			&& (isset($atts['link']) || isset($atts['url']))
		) {

			$price = empty($atts['price']) ? '' : $atts['price'];
			$link  = empty($atts['link']) ? $atts['url'] : $atts['link'];
			$text  = '';

			if ($atts['text']) {
				$text = $atts['text'];
			} elseif ($atts['title']) {
				$text = $atts['title'];
			}

			// Add a space between title and price.
			if (!empty($text)) {
				$text .= ' ';
			}

			$output = sprintf('<p>Buy It: %s%s <a href="%s">BUY IT</a></p>', esc_html($text), esc_html($price), esc_url_raw($link));

			$output = apply_filters('pmc_apple_news_buy_now_template', $output, $atts, $content);
		}

		return $output;
	}

	/**
	 * Filter apple news json resoponse to support buy button (shortcode).
	 *
	 * @param array   $json    current json with all components.
	 * @param integer $post_id publishing post id.
	 *
	 * @return array $json new json with link button component.
	 */
	public function add_buy_button_component_to_apple_news_json($json, $post_id)
	{

		// set custom style for apple-news buy-button
		$this->_buy_button_style = apply_filters(
			'pmc_apple_news_buy_button_component_style',
			[
				'backgroundColor' => '#163147',
				'mask'            => [
					'type'   => 'corners',
					'radius' => 25,
				],
			]
		);

		if (is_array($json) && array_key_exists('components', $json)) {

			$regex      = '/<a[^>]*href="([^"]+)"[^>]*>(BUY IT)<\/a>/mi';
			$components = [];

			// In order to match, markdown support needs to be handle other way.
			$settings = get_option(\Admin_Apple_Settings::$option_name);
			if (!empty($settings['html_support']) && 'no' === $settings['html_support']) {
				$regex = '/\[BUY IT\]\(([^"]+)\)/mi';
			}

			foreach ($json['components'] as $component) {

				if ('body' === $component['role'] && preg_match($regex, $component['text'], $matches)) {

					$component['text'] = str_replace($matches[0], '', $component['text']);

					$link_button = [
						'role'      => 'link_button',
						'URL'       => $matches[1],
						'text'      => 'BUY IT',
						'textStyle' => 'buy-button-text-style',
						'style'     => $this->_buy_button_style,
						'layout'    => 'buy-button-layout',
					];

					$sub_components = [];
					$ads_components = [];

					if (!empty($components)) {
						$done = false;
						do {
							$last = end($components);
							// if we detect if last component contains image, we want to group it with the buy now button
							if (
								!empty($last)
								&& ('photo' === $last['role']
									|| ('body' === $last['role'] && preg_match('/<img.*?src=/', $last['text']))
								)
							) {
								array_unshift($sub_components, array_pop($components));
							} elseif (isset($last['allowAutoplacedAds'])) {
								array_unshift($ads_components, array_pop($components));
							} else {
								$done = true;
							}
						} while (!$done);
					}

					$sub_components[] = $component;
					$sub_components[] = $link_button;

					// ROP-2214: We do not want ads to be inject inside the current container or its children
					// @see https://developer.apple.com/documentation/apple_news/apple_news_format/managing_advertisements_in_your_article
					// @ref https://developer.apple.com/documentation/apple_news/container
					$components[] = [
						'role'               => 'container',
						'allowAutoplacedAds' => false,
						'components'         => $sub_components,
					];

					$components = array_merge($components, $ads_components);
				} else {

					$components[] = $this->add_buy_button_component_to_apple_news_json($component, $post_id);
				}
			}

			$json['components'] = $components;
		}

		return $json;
	}

	/**
	 * Add amazon affiliate code to amazon URLs for Apple news.
	 *
	 * @param array $json_content Apple news JSON content.
	 *
	 * @return array
	 */
	public function update_amazon_affiliate_code($json_content): array
	{

		$affiliate_code = apply_filters('pmc_apple_news_amazon_affiliate_code', '');

		// Proceed only if "$affiliate_code" is config from theme
		if (!empty($affiliate_code) && is_array($json_content)) {

			// Get possiable tag overrides.
			$apple_news_settings     = get_option('apple_news_settings', []);
			$use_ecommerce_module    = empty($apple_news_settings['ecommerce_module']) ? 'no' : $apple_news_settings['ecommerce_module'];
			$ecommerce_tag_overrides = [];

			if ('yes' === $use_ecommerce_module) {
				// Apple News Ecommerce affiliate code default.
				$ecommerce_affiliate_code = self::get_instance()->get_default_amazon_ecommerce_affiliate_code();

				// Global override for ecommerce module.
				$ecommerce_amazon_tag = empty($apple_news_settings['ecommerce_module_amazon_tag']) ? '' : $apple_news_settings['ecommerce_module_amazon_tag'];
				if (!empty($ecommerce_amazon_tag)) {
					array_push($ecommerce_tag_overrides, $ecommerce_amazon_tag);
				} else {
					array_push($ecommerce_tag_overrides, $ecommerce_affiliate_code);
				}

				$ecommerce_tag_overrides = array_merge($ecommerce_tag_overrides, $this->get_promo_article_tag_overrides());
				$ecommerce_tag_overrides = array_unique((array) $ecommerce_tag_overrides);
			}

			// As it's JSON array, we will walk through each and every element to check for AMAZON link.
			array_walk_recursive(
				$json_content,
				function (&$content, $key) use ($affiliate_code, $ecommerce_tag_overrides) {

					$links = [];

					if ('url' === strtolower($key) && $this->is_amazon_url($content)) {
						$links[] = $content;
					} elseif ('text' === strtolower($key)) {
						$links = array_merge($links, $this->get_amazon_links_in_content($content));
					}

					if (!empty($links) && is_array($links)) {

						// it will remove duplicate links, as it will not duplicate tag entries.
						$links = array_unique((array) $links);

						foreach ($links as $link) {

							$decoded_link = html_entity_decode($link, ENT_QUOTES);

							// Skip links that already have proper tag query args.
							if (!empty($ecommerce_tag_overrides)) {
								$skip_link = false;

								foreach ($ecommerce_tag_overrides as $ecommerce_tag_override) {

									if (false !== strpos($decoded_link, 'tag=' . $ecommerce_tag_override)) {
										$skip_link = true;
									}
								}

								if (true === $skip_link) {
									continue;
								}
							}
							if ($this->is_amazon_url($decoded_link)) {
								$updated_link = add_query_arg('tag', $affiliate_code, $decoded_link);
								$content      = str_replace($link, esc_url_raw($updated_link), $content);
							}
						}
					}
				}
			);
		}

		return $json_content;
	}

	/**
	 * Append Amazon products to the content if available for the post
	 * @param string $content
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function maybe_append_amazon_products($content = '', $post_id = 0)
	{

		if (!empty($post_id) && intval($post_id) > 0 && class_exists('\PMC\Amzn_Onsite\Setup')) {

			$products_str = \PMC\Amzn_Onsite\Setup::get_instance()->get_amazon_products($post_id, 'applenews');
			$content      = $content . $products_str;
		}

		return $content;
	}

	/**
	 * search and add, if needed, term for pmc_carousel
	 *
	 * @return void
	 */
	public function create_pmc_carousel_term(): void
	{

		$term_exists = term_exists('Apple News Promo Module', 'pmc_carousel_modules');

		if (null === $term_exists) {
			wp_insert_term('Apple News Promo Module', 'pmc_carousel_modules');
		}
	}

	/**
	 * Get promo posts from curation module
	 * or fallback to recent top 10 ecomm posts
	 *
	 * @param int $post_id Post ID
	 * @return array $promo_posts_arr Array of Promo Posts
	 */
	public function get_promo_posts($post_id = 0): array
	{

		$promo_post_1    = '';
		$promo_post_2    = '';
		$promo_posts_arr = [];

		// get most recent curated posts for Apple News Promo Module
		$curated_posts = pmc_render_carousel('pmc_carousel_modules', 'apple-news-promo-module', 2, '', ['flush_cache' => true]);

		if (!empty($curated_posts) && is_array($curated_posts)) {

			$promo_post_1 = array_shift($curated_posts);
			$promo_post_1 = ($promo_post_1['ID'] === $promo_post_1['parent_ID'] || empty($promo_post_1['image'])) ? '' : (object) $promo_post_1;

			if (count($curated_posts) > 0) {
				$promo_post_2 = array_shift($curated_posts);
				$promo_post_2 = ($promo_post_2['ID'] === $promo_post_2['parent_ID'] || empty($promo_post_2['image'])) ? '' : (object) $promo_post_2;
			}
		}

		// fetch recent posts if cant get curated posts
		if (empty($promo_post_1) || empty($promo_post_2)) {
			$query_args = [
				'posts_per_page'   => 10,
				'post_type'        => 'post',
				'suppress_filters' => false,
			];

			$query_args = apply_filters('pmc_apple_news_promo_query_args', $query_args);

			// ignoring the phpcs - Added suppress filters = false
			$promo_posts = get_posts($query_args); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts

			// unset current post from $promo_posts
			foreach ($promo_posts as $promo_post) {
				if ($post_id === $promo_post->ID) {
					continue;
				}

				if (empty($promo_post_1) && true === has_post_thumbnail($promo_post->ID)) {
					$promo_post_1 = $promo_post;
					continue;
				}

				$promo_post_2 = (!empty($promo_post_1) && empty($promo_post_2) && true === has_post_thumbnail($promo_post->ID)) ? $promo_post : $promo_post_2;

				if (!empty($promo_post_1) && !empty($promo_post_2)) {
					break;
				}
			}
		}

		$promo_posts_arr['promo_post_1'] = $promo_post_1;
		$promo_posts_arr['promo_post_2'] = $promo_post_2;

		return $promo_posts_arr;
	}

	/**
	 * Insert promo content
	 * @param $content
	 * @param $post_id
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function add_promo_content($content, $post_id = 0): string
	{

		if (empty($content)) {
			return $content;
		}

		$settings                      = get_option('apple_news_settings', array());
		$promo_module                  = empty($settings['promo_module']) ? 'no' : $settings['promo_module'];
		$promo_module_utm              = empty($settings['promo_module_utm']) ? '' : $settings['promo_module_utm'];
		$use_ecommerce_module          = empty($settings['ecommerce_module']) ? 'no' : $settings['ecommerce_module'];
		$disable_ecommerce_module_post = \PMC\Post_Options\API::get_instance()->post($post_id)->has_option('disable-ecommerce-module');

		if (
			'yes' !== $promo_module
			|| ('yes' === $use_ecommerce_module
				&& $disable_ecommerce_module_post
			)
		) {
			return $content;
		}

		$content_array = explode('</p>', wpautop($content));

		$promo_posts = $this->get_promo_posts($post_id);

		$promo_post_1 = (!empty($promo_posts) && !empty($promo_posts['promo_post_1'])) ? $promo_posts['promo_post_1'] : '';
		$promo_post_2 = (!empty($promo_posts) && !empty($promo_posts['promo_post_2'])) ? $promo_posts['promo_post_2'] : '';

		// Promo 1.
		if (!empty($promo_post_1)) {
			if ('yes' === $use_ecommerce_module) {
				$location_1   = 2; // Display after 2 paragraphs.
				$promo_data_1 = $this->prepare_ecommerce_promo_content($promo_post_1);
			}

			if (empty($promo_data_1)) {
				$location_1   = intval(floor(count($content_array) * 0.33)); // Display one thrid of the way through the paragraphs.
				$promo_data_1 = $this->prepare_promo_content($promo_post_1, $promo_module_utm);
			}

			$product_section_index = 0;

			foreach ($content_array as $key => $value) {
				$content_array[$key] = $content_array[$key] . '</p>';

				//Below div class is used across all ecom sites.
				$content_products = strpos($value, '<div class="spy__content-divider">');
				$meta_products    = strpos($value, '<section class="pmc-amzn-onsite">');

				//This is to get location that doesn't break the ecom product information layout
				$product_section_index = (false !== $content_products || false !== $meta_products) ? $key : $product_section_index;

				if (($key + 1) === $location_1) {
					// determine where to insert promo module with out interrupting product section.
					$final_location                   = (0 < $product_section_index) ? $product_section_index : $location_1;
					$content_array[$final_location] = $promo_data_1 . $content_array[$final_location];
					break;
				}
			}

			$content = implode('', $content_array);

			// Promo 2.
			$display_promo_1_twice = empty($settings['ecommerce_module_display_promo_1_twice']) ? 'yes' : $settings['ecommerce_module_display_promo_1_twice'];
			if ('yes' === $use_ecommerce_module) {
				if ('yes' === $display_promo_1_twice) {
					$promo_data_2 = $this->prepare_ecommerce_promo_content($promo_post_1);
				} else {
					$promo_data_2 = $this->prepare_ecommerce_promo_content($promo_post_2);
				}
			}

			if (empty($promo_data_2) && !empty($promo_post_2)) {
				$promo_data_2 = $this->prepare_promo_content($promo_post_2, $promo_module_utm);
			}

			if (!empty($promo_data_2)) {
				$content = $content . $promo_data_2;
			}
		}

		return $content;
	}

	/**
	 * Update Json object with promo content
	 *
	 * @param array $json
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function filter_update_promo_module($json, $post_id = 0)
	{

		// Get the setting value for option.
		$settings             = get_option('apple_news_settings', array());
		$promo_module         = empty($settings['promo_module']) ? 'no' : $settings['promo_module'];
		$use_ecommerce_module = empty($settings['ecommerce_module']) ? 'no' : $settings['ecommerce_module'];

		if (
			is_array($json) &&
			!empty($post_id) &&
			!empty($json['components']) &&
			('yes' === $promo_module || 'yes' === $use_ecommerce_module)
		) {
			$json['components'] = Helper::get_instance()->unwrap_json_components($json['components']);
		}
		return $json;
	}

	/**
	 * Helper function to prepare promo module content.
	 *
	 * @param $post
	 * @param $utm_code
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function prepare_promo_content($post, $utm_code)
	{
		$promo_content = '';
		$image         = '';
		$title         = '';
		$url           = '';

		if (!empty($post)) {

			if (!empty($post->ID)) {

				$image = get_the_post_thumbnail_url($post->ID, 'post-thumbnail');
				$title = get_the_title($post->ID);
				$url   = add_query_arg('utm_campaign', $utm_code, get_permalink($post->ID));
			} elseif (empty($post->ID)) {

				if (!empty($post->image)) {
					$image = $post->image;
				}
				if (!empty($post->title)) {
					$title = $post->title;
				}
				if (!empty($post->url)) {
					$url = $post->url;
				}
			}

			if (!empty($title) && !empty($image) && !empty($url)) {

				$url        = add_query_arg('utm_campaign', $utm_code, $url);
				$url        = Tracking::get_instance()->track($url);
				$promo_data = [
					'heading' => apply_filters('pmc_apple_news_promo_heading', __('More from Us', 'pmc-apple-news')),
					'title'   => $title,
					'img_url' => $image,
					'url'     => $url,
				];

				$promo_content = PMC::render_template(
					sprintf('%s/templates/promo-module.php', untrailingslashit(PMC_APPLE_NEWS)),
					$promo_data,
					false
				);
			}
		}

		return $promo_content;
	}

	/**
	 * Helper function to prepare ecommerce promo module content.
	 *
	 * @param $data PMC carousel data
	 * @return string
	 */
	public function prepare_ecommerce_promo_content($data): string
	{
		$data = (array) $data;

		if (empty($data) || empty($data['url'])) {
			return '';
		}

		$promo_link    = $data['url'];
		$promo_post_id = !empty($data['parent_ID']) ? (int) $data['parent_ID'] : 0;

		$promo_data = [
			'title'            => !empty($data['title']) ? $data['title'] : '',
			'image_url'        => !empty($data['image']) ? $data['image'] : '',
			'price'            => '',
			'original_price'   => '',
			'discount_amount'  => '',
			'discount_percent' => '',
			'coupon_code'      => '',
			'coupon_expiry'    => '',
		];

		// Amazon logic.
		$promo_data = $this->update_ecommerce_values_with_amazon_api_data($promo_link, $promo_data);

		if ($this->is_amazon_url($promo_link)) {
			$prime_logo           = PMC_APPLE_NEWS_URL . 'images/prime-logo-large.png';
			$ecommerce_amazon_tag = $this->get_ecommerce_amazon_tag($promo_post_id);

			if (!empty($ecommerce_amazon_tag)) {
				$promo_link = add_query_arg('tag', $ecommerce_amazon_tag, $promo_link);
			}
		}

		// Price override (wp data).
		if (!empty($promo_post_id)) {
			$price_override = get_post_meta($promo_post_id, '_pmc_carousel_override_price', true);
		}

		if (!empty($price_override) && $promo_data['price'] !== $price_override) {
			$promo_data['price'] = $price_override;

			// Remove discount data due to price override throwing off calculations.
			$promo_data['original_price']   = '';
			$promo_data['discount_amount']  = '';
			$promo_data['discount_percent'] = '';
		}

		if (empty($promo_data['price'])) {
			return ''; // Price is required.
		}

		// Call template.
		$promo_settings = get_option('apple_news_settings', []);

		return PMC::render_template(
			sprintf('%s/templates/promo-module-v2.php', untrailingslashit(PMC_APPLE_NEWS)),
			[
				'widget_title'     => empty($promo_settings['ecommerce_module_title']) ? 'Today\'s Top Deal' : $promo_settings['ecommerce_module_title'],
				'title'            => $promo_data['title'],
				'link'             => Tracking::get_instance()->track($promo_link),
				'image_url'        => $promo_data['image_url'],
				'price'            => $promo_data['price'],
				'original_price'   => $promo_data['original_price'],
				'discount_amount'  => $promo_data['discount_amount'],
				'discount_percent' => $promo_data['discount_percent'],
				'coupon_code'      => $promo_data['coupon_code'],
				'coupon_expiry'    => $promo_data['coupon_expiry'],
				'description_text' => empty($promo_settings['ecommerce_module_description']) ? get_bloginfo('name') . ' may receive a commission.' : $promo_settings['ecommerce_module_description'],
				'buy_button_text'  => empty($promo_settings['ecommerce_module_buy_button_text']) ? 'Buy Now' : $promo_settings['ecommerce_module_buy_button_text'],
				'prime_logo'       => empty($prime_logo) ? '' : $prime_logo, // Removes amazon prime logo if empty.
			]
		);
	}

	/**
	 * Takes in wp data and replaces keys with no data
	 * with amazon api data if there is any.
	 *
	 * @param array $promo_link
	 * @param array $ecommerce_data
	 * @return array
	 */
	public function update_ecommerce_values_with_amazon_api_data($promo_link, $ecommerce_data): array
	{
		if (
			!class_exists('\PMC\Store_Products\Product') ||
			!class_exists('\PMC\Store_Products\Shortcode') ||
			empty($promo_link)
		) {
			return $ecommerce_data;
		}

		$product = apply_filters('pmc_carousel_product', []);
		if (empty($product)) {
			$asin = \PMC\Store_Products\Shortcode::get_asin_from_amazon_url($promo_link);
			if (empty($asin)) {
				return $ecommerce_data;
			}

			$product = \PMC\Store_Products\Product::create_from_asin($asin);
			if (empty($product) || !is_object($product)) {
				return $ecommerce_data;
			}
		}

		$product = (array) $product;
		foreach ($ecommerce_data as $key => $value) {
			if (empty($value) && !empty($product[$key])) {
				$ecommerce_data[$key] = (string) $product[$key];
			}
		}

		return $ecommerce_data;
	}

	/**
	 * Determine if URL is an Amazon URL.
	 *
	 * @param string $url Something that may be an Amazon URL.
	 * @return boolean
	 */
	public function is_amazon_url($url): bool
	{
		$host        = wp_parse_url($url, PHP_URL_HOST);
		$amazon_urls = ['amazon.com', 'www.amazon.com', 'amzn.to'];

		if (in_array($host, (array) $amazon_urls, true)) {
			return true;
		}

		return false;
	}

	/**
	 * Get default apple news ecommerce tag.
	 *
	 * @return string
	 */
	public function get_default_amazon_ecommerce_affiliate_code(): string
	{
		return (string) apply_filters('pmc_apple_news_amazon_ecommerce_affiliate_code', '');
	}

	/**
	 * Get default apple news ecommerce tag.
	 *
	 * @return string
	 */
	public function get_amazon_ecommerce_affiliate_code_override(): string
	{
		$apple_news_settings           = get_option('apple_news_settings', []);
		$ecommerce_amazon_tag_override = empty($apple_news_settings['ecommerce_module_amazon_tag']) ? '' : $apple_news_settings['ecommerce_module_amazon_tag'];

		return (string) $ecommerce_amazon_tag_override;
	}

	/**
	 * Get apple news ecommerce tag query arg for specific post.
	 *
	 * @param int $post_id ID of current article.
	 * @return string
	 */
	public function get_ecommerce_amazon_tag($post_id = 0): string
	{
		// Look at post first.
		if (!empty($post_id)) {
			$ecommerce_amazon_tag_post = get_post_meta($post_id, '_pmc_carousel_override_tag', true);
			if (!empty($ecommerce_amazon_tag_post)) {
				return (string) $ecommerce_amazon_tag_post;
			}
		}

		// Use ecommerce setting override second.
		$ecommerce_amazon_tag_override = $this->get_amazon_ecommerce_affiliate_code_override();

		if (!empty($ecommerce_amazon_tag_override)) {
			return (string) $ecommerce_amazon_tag_override;
		}

		// Use fallback third.
		return (string) $this->get_default_amazon_ecommerce_affiliate_code();
	}

	/**
	 * Get tag overrides from Apple News promo articles/posts
	 *
	 * @return array
	 */
	public function get_promo_article_tag_overrides(): array
	{
		$tag_overrides = [];
		$curated_posts = pmc_render_carousel('pmc_carousel_modules', 'apple-news-promo-module', 2, '', ['flush_cache' => true]);
		if (!empty($curated_posts) && is_array($curated_posts)) {
			foreach ($curated_posts as $curated_post) {
				$ecommerce_amazon_tag_post = get_post_meta($curated_post['parent_ID'], '_pmc_carousel_override_tag', true);
				if (!empty($ecommerce_amazon_tag_post)) {
					array_push($tag_overrides, $ecommerce_amazon_tag_post);
				}
			}
		}

		return $tag_overrides;
	}

	/**
	 * Get amazon links in content (if any)
	 * 
	 * @param string $content
	 *
	 * @return array
	 * 
	 * @link Regex definition https://regex101.com/r/ieNev0/1
	 */
	public function get_amazon_links_in_content($content): array
	{
		$links = [];
		if (preg_match_all('/(\(|")((?:https?:\/\/)?(?:www.)?(?:amazon|amzn)\.com[^"]+)(\)|")/is', $content, $matches) && !empty($matches[2])) {
			$links = array_merge($links, $matches[2]);
		}

		return $links;
	}
}
