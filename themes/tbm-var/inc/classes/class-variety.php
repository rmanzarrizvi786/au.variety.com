<?php

/**
 * Spark theme setup.
 *
 * @package pmc-variety
 *
 * @since   2018-08-15
 */

namespace Variety\Inc;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;
use \PMC_Featured_Video_Override;
use \Variety\Inc\Badges\Critics_Pick;
use \Variety\Inc\Badges\In_Contention;

class Variety
{

	use Singleton;

	/**
	 * Class constructor.
	 *
	 * Initializes the theme.
	 */
	protected function __construct()
	{

		// This function use 'vary_cache_on_function()'
		// For create cache group for ESP logged in users.
		// Should execute first.
		$this->_setup_vary_cache_on_function();

		// Autoloader and associated hooks.
		require_once(CHILD_THEME_PATH . '/inc/helpers/autoloader.php');

		require_once(CHILD_THEME_PATH . '/pmc/inc/helpers/autoloader.php');

		$this->_setup_hooks();
		$this->_setup_theme();
	}

	/**
	 * To setup actions/filters.
	 *
	 *
	 * @return void
	 * @since  2017-09-18 - Dhaval Parekh - CDWE-660
	 *
	 *
	 *
	 */
	public function _setup_hooks()
	{

		/**
		 * Actions
		 */
		add_action('init', [$this, 'disable_live_chat']);
		add_action('pre_get_posts', [$this, 'remove_touts_from_frontend'], 15);
		add_action('pre_get_posts', [$this, 'home_page_post_count']);
		add_action('pre_get_posts', [$this, 'docs_post_count']);
		add_action('pmc_tags_head', [$this, 'add_tags_in_head']);
		add_action('pmc-tags-bottom', [$this, 'add_tags_in_footer']);
		add_action('init', [$this, 'remove_relationships_meta'], 0);

		/**
		 * Filters
		 */
		add_filter('vip_live_chat_enabled', '__return_false'); // disable olark
		add_filter('use_block_editor_for_post_type', [$this, 'maybe_load_gutenberg_for_post_type'], 10, 2);
		add_filter('next_posts_link_attributes', [$this, 'add_next_posts_link_attributes']);
		add_filter('previous_posts_link_attributes', [$this, 'add_previous_posts_link_attributes']);
		add_filter('variety_river', [$this, 'add_vip_posts_to_homepage_river'], 10, 2);
		add_filter(
			'pmc_core_vertical_max_top_stories',
			function ($max) {

				return 10;
			}
		);

		add_filter('pmc_do_not_load_plugin', [$this, 'variety_do_plugin_exclusions'], 10, 2);
	}

	/**
	 * Setup Theme
	 *
	 * Hook into `after_setup_theme` to init child theme functionality after the
	 * parent theme's functions and definitions have loaded.
	 *
	 * @codeCoverageIgnore The actual code should be tested, not if it is called.
	 */
	protected function _setup_theme()
	{

		// Load Theme specific plugins.
		Plugins::get_instance()->load_local_plugins();
		Assets::get_instance();
		Carousels::get_instance();
		Image_Captions::get_instance();
		Media::get_instance();
		Archive::get_instance();
		Widgets::get_instance();

		// Load the rest.
		Article::get_instance();
		Comments::get_instance();
		Cache::get_instance();
		Dirt_Redirect::get_instance();
		Executive::get_instance();
		Fields::get_instance();
		Footer_Feed::get_instance();
		Injection::get_instance();
		Legacy_Redirects::get_instance();
		Menus::get_instance();
		Publicize::get_instance();
		Redirects::get_instance();
		Related::get_instance();
		Reviews::get_instance();
		Rewrites::get_instance();
		Single_Settings::get_instance();
		Taxonomies::get_instance();
		Thought_Leaders::get_instance();
		Video::get_instance();

		Widgets\Civicscience::get_instance();

		// Callable Functions.
		require_once(CHILD_THEME_PATH . '/inc/helpers/callable-functions.php');

		// Template Tags.
		require_once(CHILD_THEME_PATH . '/inc/helpers/template-tags.php');

		// Coauthors Plus Data Source
		require_once(CHILD_THEME_PATH . '/inc/classes/class-datasource-cap.php');

		// One offs
		require_once(CHILD_THEME_PATH . '/inc/helpers/one-offs.php');

		// Fields
		require_once(CHILD_THEME_PATH . '/inc/fields.php');
	}

	/**
	 * To get cookies name for site.
	 *
	 * @param string $cookie_name Cookies name for site.
	 *
	 * @return string Cookies name for site.
	 * @since  2017-09-18 - Dhaval Parekh - CDWE-660
	 *
	 */
	public function get_cookie_name($cookie_name)
	{

		return 'variety';
	}

	/**
	 * Disable Olark, it breaks up and causes our UI JS to halt
	 * Added on VY RS launch after go ahead from Pete Schiebel in Stormchat
	 *
	 * @since 2017-09-26 Amit Gupta
	 */
	public function disable_live_chat()
	{

		if (!function_exists('wpcom_vip_remove_livechat')) {
			return;
		}

		wpcom_vip_remove_livechat();
	}

	/**
	 * To change to argument for 'Touts' post type.
	 *
	 * @param array $args Argument for Touts post type.
	 *
	 * @return array Argument for Touts post type.
	 * @since 2017-10-31 - Dhaval Parekh
	 *
	 */
	public function get_tout_post_type_args($args)
	{

		if (empty($args) || !is_array($args)) {
			$args = [];
		}

		return array_merge(
			$args,
			[
				'public'              => false,
				'exclude_from_search' => false,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
			]
		);
	}

	/**
	 * Remove Tout Post type from Query.
	 *
	 * @sicne 2010-10-31 - Dhaval Parekh
	 *
	 * @param \WP_Query $query
	 *
	 * @return void
	 */
	public function remove_touts_from_frontend($query)
	{

		if (!is_object($query) || is_admin() || is_feed()) {
			return;
		}

		$post_types = $query->get('post_type');

		if (empty($post_types) || !is_array($post_types)) {
			return;
		}

		$index = array_search('tout', $post_types);

		if (false !== $index) {

			unset($post_types[$index]);

			$query->set('post_type', array_filter($post_types));
		}
	}

	/**
	 *
	 *
	 * To setup vary_cache_on_function
	 *
	 * @return void
	 */
	protected function _setup_vary_cache_on_function()
	{

		if (function_exists('vary_cache_on_function')) {

			vary_cache_on_function(
				'
				if ( ! empty( $_COOKIE["esp_session_cookie"] ) ) {
					return true;
				}
				if ( ! empty( $_COOKIE["authorized_session_cookie"] ) ) {
					return "yes" === $_COOKIE["authorized_session_cookie"];
				}
				return false;
			'
			);
		}
	}

	/**
	 * To get if current user is logged in in ESP ot not.
	 *
	 * @return boolean Return TRUE if user is logged in otherwise FALSE.
	 */
	public function is_user_logged_in()
	{

		if (!empty(PMC::filter_input(INPUT_COOKIE, 'esp_session_cookie', FILTER_SANITIZE_STRING))) {
			return true;
		}

		if (!empty(PMC::filter_input(INPUT_COOKIE, 'authorized_session_cookie', FILTER_SANITIZE_STRING))) {
			return ('yes' === PMC::filter_input(INPUT_COOKIE, 'authorized_session_cookie', FILTER_SANITIZE_STRING));
		}

		return false;
	}

	/**
	 * Add the tags that need to go in the head section
	 *
	 * @return void
	 * @since 2017-11-09 - Archana Mandhare - PMCRS-1013
	 *
	 */
	public function add_tags_in_head(): void
	{

		PMC::render_template(CHILD_THEME_PATH . '/template-parts/tags/head-tags.php', [], true);
	}

	/**
	 * Add class in next post link attribute.
	 * For Now restricted to podcasts category as in other templates the filter is added in template only.
	 *
	 * @param string $attributes attributes for the anchor tag.
	 *
	 * @return string
	 */
	public function add_next_posts_link_attributes($attributes)
	{

		if (is_category('podcasts')) {
			return 'class="c-arrows__next"';
		}

		return $attributes;
	}

	/**
	 * Add class in previous post link attribute.
	 * For Now restricted to podcasts category as in other templates the filter is added in template only.
	 *
	 * @param string $attributes attributes for the anchor tag.
	 *
	 * @return string
	 */
	public function add_previous_posts_link_attributes($attributes)
	{

		if (is_category('podcasts')) {
			return 'class="c-arrows__prev"';
		}

		return $attributes;
	}

	/**
	 * Add the tags that need to go in the footer section
	 *
	 * @return void
	 * @throws \Exception
	 * @since 2019-05-06 - Jignesh Nakrani - ROP-1816
	 *
	 */
	public function add_tags_in_footer(): void
	{
		PMC::render_template(
			CHILD_THEME_PATH . '/template-parts/tags/footer-tags.php',
			[],
			true
		);
	}

	/**
	 * Remove Relationship meta as it's not needed.
	 */
	public function remove_relationships_meta()
	{
		$fields = PMC\Core\Inc\Fieldmanager\Fields::get_instance();
		remove_action('fm_post_post', [$fields, 'fields_relationships']);
		remove_action('fm_post_pmc-gallery', [$fields, 'fields_relationships']);
		remove_action('fm_post_pmc_list', [$fields, 'fields_relationships']);
		remove_action('fm_post_pmc-list-slideshow', [$fields, 'fields_relationships']);
	}

	/**
	 * @param $query
	 */
	public function home_page_post_count($query)
	{
		if (!is_admin() && $query->is_main_query() && $query->is_front_page() && !$query->is_paged()) {
			$query->set('posts_per_page', 8);
		}
	}

	/**
	 * @param $query
	 */
	public function docs_post_count($query)
	{
		if (!is_admin() && $query->is_main_query() && is_tag('documentaries-to-watch')) {
			$query->set('posts_per_page', 9);
		}
	}

	/**
	 * @param $query
	 */
	public function get_best_value_offer_effort_key()
	{
		if (\PMC::is_production()) {
			// Currently the Production Annual Key
			return 'VYV111203O4R';
		} else {
			// Currently the PMCDev Annual Key
			return 'VYV111201O27';
		}
	}

	/**
	 * @param $query
	 */
	public function get_standard_offer_effort_key()
	{
		if (\PMC::is_production()) {
			// Currently the Production Monthly Key
			return 'VYV111203SGW';
		} else {
			// Currently the PMCDev Monthly Key
			return 'VYV111203ORI';
		}
	}

	/**
	 * Conditional method to check if current context is CLI context environment or not
	 *
	 * @return bool Returns TRUE if current context is CLI else FALSE
	 *
	 * @codeCoverageIgnore Ignoring this for code coverage since unit tests for this method are not possible at present
	 */
	public function is_cli_environment()
	{
		// run this only in unit tests
		if (
			(defined('IS_UNIT_TEST') && true === IS_UNIT_TEST)
			|| class_exists('\WP_UnitTestCase', false)
		) {

			$mock_cli_context = apply_filters('pmc_is_cli_mock_context', false);

			if (true === $mock_cli_context) {
				return true;
			}
		}

		return (defined('WP_CLI') && true === WP_CLI);
	}

	/**
	 * Filter the plugins to be excluded
	 *
	 * @param bool   $do_exclusion False by default.
	 * @param string $plugin       The name of the plugin
	 *
	 * @return bool Return true to prevent loading of the named plugin.
	 */
	function variety_do_plugin_exclusions($do_exclusion = false, $plugin = '')
	{

		// Exclude the syndication plugin in CLI context
		if ('push-syndication' === $plugin && self::is_cli_environment()) {
			$do_exclusion = true;
		}

		return $do_exclusion;
	}

	/**
	 * Enable block editor for specific post types
	 */
	public function maybe_load_gutenberg_for_post_type($status, $post_type)
	{
		if (\PMC\Hub\Post_Type::POST_TYPE === $post_type) {
			return true;
		}

		return false;
	}

	/**
	 * Add VIP posts to homepage.
	 *
	 * @param $river
	 * @param $template
	 *
	 * @return array|mixed
	 */
	public function add_vip_posts_to_homepage_river($river, $template)
	{
		if (!is_home() || is_paged()) {
			return $river;
		}

		$vip_posts = [];
		$args      = [
			'post_type'      => ['variety_vip_post', 'variety_vip_video', 'variety_vip_report'],
			'posts_per_page' => 2,
		];

		$the_query = new \WP_Query($args);

		if ($the_query->have_posts()) {
			while ($the_query->have_posts()) {
				$the_query->the_post();
				$the_permalink = get_post_permalink();

				$vip_post = $template;

				// Title.
				$vip_post['c_title']['c_title_url']  = $the_permalink;
				$vip_post['c_title']['c_title_text'] = variety_get_card_title();

				// Featured Image/Video.
				$image = \PMC\Core\Inc\Media::get_instance()->get_image_data(get_post_thumbnail_id(), 'landscape-large');

				if (!empty($image['src'])) {
					$vip_post['c_lazy_image']['c_lazy_image_link_url']        = $the_permalink;
					$vip_post['c_lazy_image']['c_lazy_image_alt_attr']        = $image['image_alt'];
					$vip_post['c_lazy_image']['c_lazy_image_placeholder_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
					$vip_post['c_lazy_image']['c_lazy_image_srcset_attr']     = \wp_get_attachment_image_srcset(get_post_thumbnail_id());
					$vip_post['c_lazy_image']['c_lazy_image_sizes_attr']      = \wp_get_attachment_image_sizes(get_post_thumbnail_id());
					$vip_post['c_lazy_image']['c_lazy_image_src_url']         = $image['src'];
					$vip_post['c_figcaption']['c_figcaption_caption_markup']  = $image['image_caption'];
					$vip_post['c_figcaption']['c_figcaption_credit_text']     = $image['image_credit'];
				} else {
					$vip_post['c_lazy_image'] = [];
				}

				$vip_post['is_video'] = false;

				if (PMC_Featured_Video_Override::get_instance()->has_featured_video(get_the_ID())) {
					$vip_post['is_video']            = true;
					$vip_post['video_permalink_url'] = $the_permalink;
				}

				$vip_post['o_taxonomy_item']['c_span']['c_span_text']         = __('VIP+', 'pmc-variety');
				$vip_post['o_taxonomy_item']['c_span']['c_span_url']          = \Variety\Plugins\Variety_VIP\VIP::vip_url();
				$vip_post['o_taxonomy_item']['c_span']['c_span_link_classes'] = str_replace('u-color-pale-sky-2', 'u-color-brand-vip-primary', $vip_post['o_taxonomy_item']['c_span']['c_span_link_classes']);

				// Time.
				$vip_post['c_timestamp']['c_timestamp_text'] = variety_human_time_diff(get_the_ID());

				// Add to vip_posts, if there are any VIP posts
				$vip_posts[] = $vip_post;
			}
		}

		wp_reset_postdata();

		if (!empty($vip_posts)) {
			if (!empty($vip_posts[0] && is_array($vip_posts[0]))) {
				$river['o_tease_news_list_primary']['o_tease_list_items'][] = $vip_posts[0];
			}
			if (!empty($vip_posts[1]) && is_array($vip_posts[1])) {
				$river['o_tease_news_list_secondary']['o_tease_list_items'][] = $vip_posts[1];
			}
		}

		return $river;
	}
}
// EOF
