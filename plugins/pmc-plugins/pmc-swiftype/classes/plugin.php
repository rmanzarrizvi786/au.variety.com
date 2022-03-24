<?php

/**
 * Class contains Swiftype related functions.
 *
 * @since 2015-11-19 - Mike Auteri - PPT-6376
 * @version 2015-11-19 - Mike Auteri - PPT-6376
 */

namespace PMC\Swiftype;

use CheezCapDropdownOption;
use PMC;
use PMC\Global_Functions\Traits\Singleton;
use PMC_Cheezcap;

class Plugin
{
	use Singleton;

	const VERSION = '2.0';
	var $settings;

	/**
	 * Start yer engines!!
	 * @codeCoverageIgnore: The constructor is ignored because it's not being called directly and isn't generating coverage reports accurately
	 */
	protected function __construct()
	{
		// Note the cheezcap setting filter is BEFORE the is_enabled check, as we always need ability to turn Swiftype on/off.
		add_filter('pmc_global_cheezcap_options', array($this, 'filter_pmc_global_cheezcap_options'));

		if (!$this->is_enabled('pmc_swiftype')) {
			return;
		}

		add_filter('pmc_seo_tweaks_robot_names', [$this, 'filter_pmc_seo_tweaks_robot_names']);
		add_action('after_setup_theme', array($this, 'load_plugin_textdomain'));
		add_action('wp_enqueue_scripts', array($this, 'register_scripts'));
		add_action('wp_head', array($this, 'add_meta_tags'));
		add_action('widgets_init', array($this, 'register_widget'));
		add_action('wp_footer', array($this, 'template_partials'));
		add_action('template_redirect', array($this, 'tag_not_found_redirect'), 99);

		add_filter('pmc_swiftype_date_filters', array($this, 'default_date_filters'));
		add_filter('robots_txt', [$this, 'set_crawl_delay'], 10, 2);

		add_shortcode('pmc_swiftype_site_search', array($this, 'swiftype_shortcode'));

		add_action('parse_request', [$this, 'redirect_core_search']);
	}

	/**
	 * Add `st:robots` to the robot list for pmc seo tweaks
	 * @param  array $robot_names The array of robot names
	 * @return array              The array of robot names
	 */
	public function filter_pmc_seo_tweaks_robot_names($robot_names)
	{
		if (!is_array($robot_names)) {
			$robot_names = [];
		}
		$robot_names[] = 'st:robots';
		return $robot_names;
	}

	/**
	 * Try to search for tag with full name or translated name from swiftype search tag result
	 * Redirect the page to tag page if found
	 */
	public function tag_not_found_redirect()
	{
		if (!is_404() || !get_query_var('tag')) {
			return; // @codeCoverageIgnore
		}

		// try to search the post tag as is, term_exists will do name & slug search
		$tag = urldecode(get_query_var('tag'));
		// note: must use cached function
		$term = term_exists($tag, 'post_tag');

		if (empty($term)) {
			// try to reverse the translation and search for tag name if different
			$tag_name = ucwords(str_replace('-', ' ', $tag));
			if ($tag_name !== $tag) {
				$term = term_exists($tag_name, 'post_tag');
			}
		}

		if (!empty($term) && is_array($term)) {
			$term_link = get_term_link((int) $term['term_id'], 'post_tag');
			if (!$term_link || is_wp_error($term_link)) {
				return; // @codeCoverageIgnore
			}
			$term_link = apply_filters('pmc_tag_not_found_redirect', $term_link);
			if ($term_link) {
				wp_safe_redirect($term_link);
			}
		}
	}

	/**
	 * Check if particular Swiftype feature is enabled.
	 *
	 * @param  string  $opt
	 *
	 * @return boolean
	 */
	public function is_enabled($opt = 'pmc_swiftype')
	{
		// In the case of pmc_swiftype, we need this very early.
		return 'yes' === PMC_Cheezcap::get_instance()->get_option($opt);
	}

	/**
	 * Get default sorting field.
	 *
	 * @return string default sorting field
	 */
	public function get_default_sort_field()
	{
		$sort_type = PMC_Cheezcap::get_instance()->get_option('pmc_swiftype_sort_field');
		$sort_type = sanitize_title($sort_type);

		if (empty($sort_type)) {
			$sort_type = 'published_at-desc';
		}

		return $sort_type;
	}

	public function filter_pmc_global_cheezcap_options($cheezcap_options)
	{
		$cheezcap_options[] = new CheezCapDropdownOption(
			__('Swiftype', 'pmc-swiftype'),
			__('Turn on swiftype', 'pmc-swiftype'),
			'pmc_swiftype',
			array('no', 'yes'),
			0, // 1sts option => no by default
			array(__('No', 'pmc-swiftype'), __('Yes', 'pmc-swiftype'))
		);

		$cheezcap_options[] = new CheezCapDropdownOption(
			__('Swiftype Date Range Search', 'pmc-swiftype'),
			__('Turn on swiftype date range calendar', 'pmc-swiftype'),
			'pmc_swiftype_date_options_specific_dates',
			array('no', 'yes'),
			0, // 1sts option => no by default
			array(__('No', 'pmc-swiftype'), __('Yes', 'pmc-swiftype'))
		);

		$cheezcap_options[] = new CheezCapDropdownOption(
			__('Swiftype Default Sort Field', 'pmc-swiftype'),
			__('Default Sort Field', 'pmc-swiftype'),
			'pmc_swiftype_sort_field',
			array('published_at-desc', '_score-desc', 'published_at-asc', 'comment_count-desc'),
			'published_at desc', // 1sts option => no by default
			array(__('Published Date (newest first)', 'pmc-swiftype'), __('Relevance', 'pmc-swiftype'), __('Published Date (oldest first)', 'pmc-swiftype'), __('Most Commented', 'pmc-swiftype'))
		);

		return $cheezcap_options;
	}

	/**
	 * Default config values.
	 *
	 * @since 2015-11-19 - Mike Auteri - PPT-6376
	 * @version 2015-11-19 - Mike Auteri - PPT-6376
	 * @version 2016-05-24 - James Mehorter - PMCVIP-1561 - Implement allowed and disallowed functionality for the tags facet
	 * @version 2016-07-01 - Mike Auteri - PPT-6824 - Added custom_topics and custom_facet_settings.
	 * @version 2017-04-18 - Dhaval Parekh - CDWE-303 - Give admin option for default serach Results.
	 *
	 * @return array
	 */
	public function defaults()
	{

		$default_sort = $this->get_default_sort_field();
		$default_sort = explode('-', $default_sort);

		$sort_field     = (!empty($default_sort[0])) ? sanitize_title($default_sort[0]) : '';
		$sort_direction = (!empty($default_sort[1])) ? sanitize_title($default_sort[1]) : '';

		// Specific dates
		$default_sort = $this->get_default_sort_field();

		return array(
			'engine_key'            => '',
			'redirect_to'           => '/results',
			'home_url'              => home_url('/'),
			'placeholder_image'     => '',
			'image_size'            => 'medium',
			'sort_field'            => $sort_field,
			'specific_dates'        => (bool) $this->is_enabled('pmc_swiftype_date_options_specific_dates'),
			'sort_direction'        => $sort_direction,
			'autocomplete'          => array(
				'tags'     => array(
					'include' => true,
					'name'    => 'Tags',
				),
				'articles' => array(
					'include' => true,
					'name'    => 'Articles',
				),
			),
			'author_list'           => array(),
			'date_filters'          => array(
				'date_options:radio-options'  => array(
					'title'          => __('Date Filter', 'pmc-swiftype'),
					'default_option' => 0,
				),
				'topics_facet:checkbox-facet' => array(

					// Default # of options to show in the topics facet - @codingStandardsIgnoreLine
					'limit'            => 7,

					// An array of topic names, i.e. term names
					// When present, only allowed topics are shown
					'allowed_items'    => array(),

					// An array of topic names, i.e. term names
					// When present, disallowed topics are not shown
					'disallowed_items' => array(),
				),
				'tags_facet:checkbox-facet'   => array(

					// Default # of options to show in the tags facet - @codingStandardsIgnoreLine
					'limit'            => 7,

					// An array of tag names, i.e. term names
					// When present, only allowed tags are shown
					'allowed_items'    => array(),

					// An array of tag names, i.e. term names
					// When present, disallowed tags are not shown
					'disallowed_items' => array(),
				),
				'author_facet:checkbox-facet' => array(),
			),

			/**
			 * The custom_facet_settings can be set like this example in the Swiftype filter.
			 * Example:
			 *
			 * $config['custom_facet_settings']['verticals_facet'] = array(
			 *   'facet_name' => 'verticals_facet:checkbox-facet',
			 *   'title' => 'Verticals',
			 *   'clear_link' => 'Clear',
			 *   'field' => 'verticals',
			 *   'limit' => 7,
			 *   'disable_checkbox' => true,
			 *   'sort_by' => array( 'count', 'name' ),
			 *   'sort_by_direction' => array( false, true )
			 * );
			 *
			 * You will then add the facet under date_filters (example: verticals_facet:checkbox-facet).
			 * You will also add any allowed_items or disallowed_items there.
			 */
			'custom_facet_settings' => array(),

			'meta_tags'             => array(
				'post_types'                 => array(
					'post' => 'Article',
				),
				'show_content_type_meta_tag' => true,
				'tags'                       => array(
					'post_tag',
				),
				'topics'                     => array(
					'category',
				),
				/**
				 * The custom_topics can be used if topics or tags is not appropriate.
				 * Here is an example of adding one called Vertical:
				 *
				 * $config['meta_tags']['custom_topics']['verticals'] = array( 'vertical' );
				 *
				 * The array can be set to one or more taxonomies.
				 */
				'custom_topics'              => array(),
				'comment_count'              => true,
				'appeared_in_print'          => false,
			),
			'trans'                 => array(
				'search'           => esc_html__('Search', 'pmc-swiftype'),
				'search_button'    => esc_html__('Search', 'pmc-swiftype'),
				'reference'        => esc_html__('Relevance', 'pmc-swiftype'),
				'pub_date_new'     => esc_html__('Published Date (newest first)', 'pmc-swiftype'),
				'pub_date_old'     => esc_html__('Published Date (oldest first)', 'pmc-swiftype'),
				'most_commented'   => esc_html__('Most Commented', 'pmc-swiftype'),
				'clear'            => esc_html__('Clear', 'pmc-swiftype'),
				'content_type'     => esc_html__('Content Type', 'pmc-swiftype'),
				'topics'           => esc_html__('Topics', 'pmc-swiftype'),
				'tags'             => esc_html__('Tags', 'pmc-swiftype'),
				'all'              => esc_html__('All', 'pmc-swiftype'),
				'twentyfour_hours' => esc_html__('Past 24 Hours', 'pmc-swiftype'),
				'seven_days'       => esc_html__('Past 7 Days', 'pmc-swiftype'),
				'thirty_days'      => esc_html__('Past 30 Days', 'pmc-swiftype'),
				'twelve_months'    => esc_html__('Past 12 Months', 'pmc-swiftype'),
				'specific_dates'   => esc_html__('Specific Dates', 'pmc-swiftype'),
			),
		);
	}

	/**
	 * Hook into language translations.
	 *
	 * @since 2015-11-19 - Mike Auteri - PPT-6376
	 * @version 2015-11-19 - Mike Auteri - PPT-6376
	 *
	 * @return void
	 */
	public function load_plugin_textdomain()
	{
		load_theme_textdomain('pmc-swiftype', dirname(__FILE__) . '/../languages');
	}

	/**
	 * Applies theme overrides to default settings.
	 *
	 * @since 2015-11-19 - Mike Auteri - PPT-6376
	 * @version 2015-11-19 - Mike Auteri - PPT-6376
	 *
	 * @uses pmc_swiftype_configs filter
	 * @return array
	 */
	public function get_settings()
	{
		$defaults = self::defaults();

		/**
		 * pmc_swiftype_configs filter to hook in an configure Swiftype.
		 *
		 * @var $defaults array includes:
		 * - engine_key - Key for Swiftype (required).
		 * - redirect_to - Relative page where search is displayed - defaults to /result.
		 * - home_url - Domain of site.
		 * - placeholder_image - Image to display if one is not available for a post.
		 * - autocomplete - Defaults to tags and articles.
		 * - author_list - Whitelisted authors to show in search menu.
		 * - date_filters - Array of facets to display in search menu.
		 * - meta_tags - Includes post_types, tags, topics, appeared_in_print, and custom.
		 *
		 * @since 2015-11-19 - Mike Auteri - PPT-6376
		 * @version 2015-11-19 - Mike Auteri - PPT-6376
		 */
		$configs = apply_filters('pmc_swiftype_configs', $defaults);

		// Apply language translation where needed.
		foreach ($configs['autocomplete'] as $key => $value) {
			// @TODO: The use of esc_html__ here must be a literal text string, can't use dynamic value
			$configs['autocomplete'][$key]['name'] = $configs['autocomplete'][$key]['name'];
		}

		return $configs;
	}

	/**
	 * Registers Swiftype Site Search widget.
	 *
	 * @since 2015-11-19 - Mike Auteri - PPT-6376
	 * @version 2015-11-19 - Mike Auteri - PPT-6376
	 *
	 * @return void
	 */
	public function register_widget()
	{
		register_widget('PMC\Swiftype\Widget');
	}

	/**
	 * Helper function for date filters from settings to be used in page-results.php template.
	 *
	 * @since 2015-11-19 - Mike Auteri - PPT-6376
	 * @version 2015-11-19 - Mike Auteri - PPT-6376
	 *
	 * @param array $filters
	 * @return array
	 */
	public function default_date_filters($filters)
	{
		$swiftype = $this->settings;
		return $swiftype['date_filters'];
	}

	/**
	 * Includes common templates in footer.
	 *
	 * @since 2015-11-19 - Mike Auteri - PPT-6376
	 * @version 2015-11-19 - Mike Auteri - PPT-6376
	 *
	 * @return void
	 */
	public function template_partials()
	{

		/**
		 * Swiftype Template Partial Path
		 *
		 * A filter to change the path of the Swiftype partial template.
		 *
		 * @param string $path The default partial path.
		 */
		$path = apply_filters('swiftype_template_partial_path', dirname(__DIR__) . '/templates/partials.php');

		if (file_exists($path) && validate_file($path) === 0) {
			include_once($path);
		}
	}

	/**
	 * Load scripts for Swiftype.
	 *
	 * @since 2015-11-19 - Mike Auteri - PPT-6376
	 * @version 2015-11-19 - Mike Auteri - PPT-6376
	 *
	 * @return void
	 */
	public function register_scripts()
	{
		$this->settings = $this->get_settings();
		$swiftype       = $this->settings;

		$swiftype['q'] = sanitize_text_field(\PMC::filter_input(INPUT_GET, 'q'));

		if (!empty($swiftype['engine_key'])) {
			wp_enqueue_script('pmc-swiftype-crawler', 'https://s.swiftypecdn.com/cc/' . esc_html($swiftype['engine_key']) . '.js', array(), self::VERSION, true);
		}

		wp_enqueue_style('pmc-swiftype-style', plugins_url('assets/css/style.css', __DIR__), array(), self::VERSION);

		// Use minified JS on production
		if (PMC::is_production()) {
			$js_ext = '.min.js';
		} else {
			$js_ext = '.js';
		}

		wp_enqueue_script('pmc-swiftype-components', plugins_url('assets/js/SwiftypeComponents' . $js_ext, __DIR__), array(), self::VERSION, true);

		/**
		 * Load IE specific script for a range of older versions:
		 * <!--[if lt IE 10]> ... <![endif]-->
		 */
		wp_enqueue_script('pmc-swiftype-components-ie', plugins_url('assets/js/swiftypecomponents-ie.js', __DIR__), array(), self::VERSION, true);

		wp_script_add_data('pmc-swiftype-components-ie', 'conditional', 'lt IE 10');

		/**
		 * Swiftype JS Configuration URL
		 *
		 * A filter to change the URL of the Swiftype configuration JS.
		 *
		 * @param string $url The default configuration url.
		 */
		$configuration_url = apply_filters('swiftype_js_configuration_url', plugins_url('assets/js/configuration' . $js_ext, __DIR__));

		if (!empty($configuration_url)) {

			wp_enqueue_script('pmc-swiftype-config', $configuration_url, array('pmc-swiftype-components'), self::VERSION, true);

			$swiftype['from_str']          = esc_html__('From:', 'pmc-swiftype');
			$swiftype['to_str']            = esc_html__('To:', 'pmc-swiftype');
			$swiftype['refine_search_str'] = esc_html__('Refine Search', 'pmc-swiftype');
			$swiftype['author']            = esc_html__('Author', 'pmc-swiftype');
			$swiftype['appeared_in_print'] = esc_html__('Appeared in Print?', 'pmc-swiftype');

			wp_localize_script('pmc-swiftype-config', 'SwiftypeConfigs', $swiftype);
		}

		if ($this->is_enabled('pmc_swiftype_date_options_specific_dates') && is_page(ltrim($swiftype['redirect_to'], '/'))) {
			wp_enqueue_style('pmc-swiftype-style-datepicker', plugins_url('assets/css/datepicker.css', __DIR__), array(), self::VERSION);

			wp_enqueue_script('jquery-ui-datepicker');
		}

		$post = get_post();
		if (!empty($post) && has_shortcode($post->post_content, 'pmc_swiftype_site_search')) {

			// restore the hash if it has been dropped (some versions of Safari have this bug)
			$script  = "if (window.location.href.indexOf('#') < 0) {";
			$script .= "var cname = 'pmc_search_hash';";
			$script .= 'var pmc_search_hash = pmc.cookie.get(cname);';
			$script .= "pmc.cookie.expire(cname, '/');";
			$script .= 'if (pmc_search_hash) {';
			$script .= 'window.location.hash = pmc_search_hash;';
			$script .= 'window.location.reload();';
			$script .= '}';
			$script .= '}';

			wp_add_inline_script('pmc-swiftype-components', $script, 'before');
		}
	}

	/**
	 * Search results page shortcode.
	 *
	 * @since 2015-11-19 - Mike Auteri - PPT-6376
	 * @version 2015-11-19 - Mike Auteri - PPT-6376
	 *
	 * @param array $atts
	 * @return string
	 */
	public function swiftype_shortcode($atts)
	{

		/**
		 * Swiftype Shortcode Template Path
		 *
		 * A filter to change the path of the Swiftype shortcode template.
		 *
		 * @param string $path The default template path.
		 */
		$path = apply_filters('swiftype_shortcode_template_path', dirname(__DIR__) . '/templates/page-results.php');

		if (file_exists($path) && validate_file($path) === 0) {
			return PMC::render_template(
				$path,
				['swiftype' => $this->settings]
			);
		}
	}

	/**
	 * Swiftype meta tags.
	 *
	 * @since 2015-11-19 - Mike Auteri - PPT-6376
	 * @version 2015-11-19 - Mike Auteri - PPT-6376
	 * @version 2016-07-01 - Mike Auteri - PPT-6824 - Added support for custom_topics.
	 *
	 * @todo clean up this method - Ticket PPT-6847: Clean up and refactor add_meta_tags method in Swiftype
	 *
	 * @return void
	 */
	public function add_meta_tags()
	{

		$post     = get_queried_object();
		$swiftype = $this->settings;

		// if we don't have any settings, bail out
		if (!is_a($post, '\WP_Post') || empty($post->post_type) || empty($swiftype['meta_tags']['post_types'])) {
			return;
		}

		if (!is_single() || !array_key_exists($post->post_type, $swiftype['meta_tags']['post_types'])) {
			return;
		}

		$short_circuit = apply_filters('pmc_swiftype_plugin_add_meta_tags_short_circuit', false, $post);

		if (true === $short_circuit) {
			// This function has been short circuited and
			// meta tag output is no longer our concern
			// Bail out
			return;
		}

		echo "\n";
		echo '<!-- Swiftype Meta Tags Start -->' . "\n";

		$featured_image_url = $this->get_best_image($post->ID, $swiftype['image_size']);

		$authors = PMC::get_post_authors($post->ID, 'all', array('display_name'));

		$tag_list = array();
		if (isset($swiftype['meta_tags']['tags'])) {
			foreach ($swiftype['meta_tags']['tags'] as $tag) {
				$terms = get_the_terms($post->ID, $tag);
				if ($terms && !is_wp_error($terms)) {
					$tag_list[] = $terms;
				}
			}
		}

		$topic_list = array();
		if (isset($swiftype['meta_tags']['topics'])) {
			foreach ($swiftype['meta_tags']['topics'] as $topic) {
				$terms = get_the_terms($post->ID, $topic);
				if ($terms && !is_wp_error($terms)) {
					$topic_list[] = $terms;
				}
			}
		}

		$custom_topics_list = array();
		if (!empty($swiftype['meta_tags']['custom_topics'])) {
			foreach ($swiftype['meta_tags']['custom_topics'] as $key => $value) {
				if (!is_array($value)) {
					continue;
				}
				$custom_topics_list[$key] = array();
				foreach ($value as $tax) {
					if (empty($tax) || !is_string($tax)) {
						continue;
					}
					$terms = get_the_terms($post->ID, $tax);
					if ($terms && !is_wp_error($terms)) {
						$custom_topics_list[$key][] = current($terms);
					}
				}
			}
		}

		if (
			!empty($swiftype['meta_tags']['show_content_type_meta_tag'])
			&& isset($swiftype['meta_tags']['post_types'][$post->post_type])
		) {
			printf('<meta class="swiftype" name="content_type" data-type="string" content="%s" />', esc_attr($swiftype['meta_tags']['post_types'][$post->post_type]));
			echo "\n";
		}

		if (is_a($post, '\WP_Post')) {
			printf('<meta class="swiftype" name="post_id" data-type="integer" content="%s" />', intval($post->ID));
			echo "\n";
		}

		printf('<meta class="swiftype" name="title" data-type="string" content="%s" />', esc_attr($post->post_title));
		echo "\n";

		if (!empty($authors)) {
			foreach ($authors as $author) {
				printf('<meta class="swiftype" name="author" data-type="string" content="%s" />', esc_attr($author['display_name']));
				echo "\n";
			}
		}

		printf('<meta class="swiftype" name="published_at" data-type="date" content="%s" />', esc_attr($post->post_date));
		echo "\n";

		if (!empty($featured_image_url)) {
			printf('<meta class="swiftype" name="image" data-type="enum" content="%s" />', esc_url($featured_image_url));
			echo "\n";
		}

		if ($swiftype['meta_tags']['comment_count']) {
			$comment_count = get_comments_number($post->ID);
			printf('<meta class="swiftype" name="comment_count" data-type="integer" content="%d" />', intval($comment_count));
			echo "\n";
		}

		if (!empty($tag_list)) {
			foreach ($tag_list as $tag) {
				if (is_array($tag)) {
					foreach ($tag as $tax) {
						printf('<meta class="swiftype" name="tags" data-type="string" content="%s" />', esc_attr($tax->name));
						echo "\n";
					}
				}
			}
		}

		if (!empty($topic_list)) {
			foreach ($topic_list as $topic) {
				if (is_array($topic)) {
					foreach ($topic as $term) {
						if (!is_a($term, 'WP_Term')) {
							continue;
						}
						printf('<meta class="swiftype" name="topics" data-type="string" content="%s" />', esc_attr($term->name));
						echo "\n";
					}
				}
			}
		}

		if (!empty($custom_topics_list)) {
			foreach ($custom_topics_list as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $term) {
						if (!is_a($term, 'WP_Term')) {
							continue;
						}
						printf('<meta class="swiftype" name="%s" data-type="string" content="%s" />', esc_attr($key), esc_attr($term->name));
						echo "\n";
					}
				}
			}
		}

		if ($swiftype['meta_tags']['appeared_in_print']) {
			printf('<meta class="swiftype" name="appeared_in_print" data-type="string" content="%s" />', esc_attr($swiftype['meta_tags']['appeared_in_print']));
			echo "\n";
		}

		$post_content = wp_strip_all_tags(strip_shortcodes($post->post_content));
		$post_content = apply_filters('pmc_swiftype_meta_tags_body_post_content', $post_content, $post);

		printf('<meta class="swiftype" name="body" data-type="text" content="%s" />', esc_attr($post_content));


		if (!empty($swiftype['meta_tags']['custom'])) {
			foreach ($swiftype['meta_tags']['custom'] as $name => $value) {
				printf(
					'<meta class="swiftype" name="%s" data-type="%s" content="%s" />',
					esc_attr($name),
					esc_attr($value['data_type']),
					esc_attr($value['content'])
				);
				echo PHP_EOL;
			}
		}

		echo "\n";
		echo '<!-- Swiftype Meta Tags End -->' . "\n";
		echo "\n";
	}

	/**
	 * Get the best available image for this post.
	 *
	 * @param int $post_id
	 * @param string $image_size
	 * @return string
	 */
	function get_best_image($post_id = 0, $image_size = 'medium')
	{
		if (!intval($post_id)) {
			return '';
		}

		if (has_post_thumbnail($post_id)) {
			$featured_image_url = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), $image_size);
			if (!empty($featured_image_url) && is_array($featured_image_url)) {
				return current($featured_image_url);
			}
		}

		// next see if there's gallery meta.
		$gallery = get_post_meta($post_id, 'pmc-gallery', true);
		if (!empty($gallery) && is_array($gallery)) {
			$gallery_id        = current($gallery);
			$gallery_image_url = wp_get_attachment_image_src($gallery_id, $image_size);
			if (!empty($gallery_image_url) && is_array($gallery_image_url)) {
				return current($gallery_image_url);
			}
		}

		// lastly, try to use an attached image.
		$args     = array(
			'fields'           => 'ids',
			'post_type'        => 'attachment',
			'post_mime_type'   => 'image',
			'post_parent'      => $post_id,
			'suppress_filters' => false,
			'posts_per_page'   => 1,
			'order'            => 'ASC',
		);
		$image_id = get_posts($args); // @codingStandardsIgnoreLine
		if (!empty($image_id) && is_array($image_id)) {
			$image_id = current($image_id);
			$thumb    = wp_get_attachment_image_src($image_id, $image_size);
			if (!empty($thumb) && is_array($thumb)) {
				return current($thumb);
			}
		}

		// i foundz nothing :-(
		return '';
	}

	/**
	 * set_crawl_delay | classes/plugin.php
	 *
	 * @since 2018-06-27 - Limits the crawl rate of swiftype
	 * @uses robots_txt
	 * @see see https://swiftype.com/documentation/site-search/robots
	 *
	 * @author brandoncamenisch
	 * @version 2018-06-27 - feature/WI-714:
	 */
	public function set_crawl_delay($output, $public)
	{
		return $output .= PHP_EOL . 'User-agent: Swiftbot' . PHP_EOL . ' Crawl-delay: 5' . PHP_EOL;
	}

	/**
	 * Redirect front-end Core search to SwiftType.
	 *
	 * @param \WP $request WP object.
	 */
	public function redirect_core_search(\WP $request): void
	{
		if (!isset($request->query_vars['s'])) {
			return;
		}

		// Cannot cover due to constants.
		// @codeCoverageIgnoreStart
		if (
			is_admin()
			|| (defined('REST_REQUEST') && REST_REQUEST)
			|| (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)
		) {
			return;
		}
		// @codeCoverageIgnoreEnd

		/**
		 * VIP noted slow Core-search queries in two forms:
		 * - /search/SiteNews?q=%22pathology%22&s=date&t
		 * - /search/www.jiangxiymcg.cn
		 *
		 * SwiftType doesn't use `date` as a sort option, so we drop the search
		 * query when the `q` query string is also set.
		 */
		// Search requests aren't nonced in WP.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (isset($_REQUEST['q'])) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$query = sanitize_text_field($_REQUEST['q']);
		} else {
			// Too early for `get_search_query()`.
			$query = sanitize_text_field($request->query_vars['s']);
		}

		/**
		 * We need a redirect URL in the format `/#?q=query`, but `add_query_arg`
		 * will produce `/?q=query#` when passed a URL with a hash.
		 */
		$redirect_qs = add_query_arg(
			'q',
			rawurlencode($query),
			'/'
		);

		$settings  = $this->get_settings();
		$redirect  = untrailingslashit($settings['home_url']);
		$redirect .= user_trailingslashit($settings['redirect_to']);
		$redirect .= '#';
		$redirect .= ltrim($redirect_qs, '/');

		// Prevent a query if we can't redirect.
		if (false === wp_validate_redirect($redirect, false)) {
			unset($request->query_vars['s']);
			$request->query_vars['error'] = 404;

			return;
		}

		wp_safe_redirect($redirect);
		// No way to cover in PHPUnit.
		exit; // @codeCoverageIgnore
	}
}

//EOF
