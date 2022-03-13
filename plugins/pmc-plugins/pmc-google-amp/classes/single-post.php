<?php

namespace PMC\Google_Amp;

use \PMC;
use \PMC_Feature;
use \PMC\Global_Functions\Traits\Singleton;
use \PMC_Featured_Video_Override;
use \PMC_Ads;
use \PMC_Cheezcap;
use \PMC_Google_Universal_Analytics;
use \PMC_Cache;
use \AMP_DOM_Utils;
use \AMP_Img_Sanitizer;

class Single_Post
{

	use Singleton;

	const COMSORE_ID                                   = '6035310';
	const TREASUREDATA_HOST                            = 'in.treasuredata.com';
	const TREASUREDATA_API_KEY                         = '11476/06ffb9b35bf3ec02eef748c099164f74dc7b35e2';
	const TREASUREDATA_DATABASE                        = 'raw_web_event_prod';
	const AMP_STICKY_AD_STATUS                         = 'pmc_amp_sticky_ad_status';
	const AMP_MOVE_HEADER_AD_BEFORE_CONTENT_STATUS     = 'pmc_amp_move_header_ad_before_content_status';
	const AMP_LAUNCH_GALLERY_STATUS                    = 'pmc_amp_launch_gallery_status';
	const AMP_GALLERY_TEASER_STATUS                    = 'pmc_amp_gallery_teaser_status';
	const AMP_INCLUDE_THUMBNAIL_IN_RELATED_LINK_STATUS = 'pmc_amp_include_thumbnail_in_related_link_status';
	const AMP_GALLERY_DEPTH_TRACKING_STATUS            = 'pmc_amp_depth_event_tracking_status';
	const AMP_GALLERY_THUMBNAIL_STATUS                 = 'pmc_amp_gallery_thumbnail_status';
	const AMP_INLINE_LINK_EVENT_TRACKING_STATUS        = 'pmc_amp_inline_event_tracking_status';
	const AMP_SCROLL_EVENT_TRACKING_STATUS             = 'pmc_amp_scroll_event_tracking_status';
	const AMP_SIDE_MENU_STATUS                         = 'pmc_amp_side_menu_status';
	const AMP_MORE_FROM_CATEGORY_STATUS                = 'pmc_amp_more_from_category_status';
	const AMP_COMMENT_BUTTON_STATUS                    = 'pmc_amp_comment_button_status';
	const AMP_BREADCRUMBS_EVENT_TRACKING               = 'pmc_amp_breadcrumbs_event_tracking';
	const AMP_SIDE_HAMBURGER_MENU_EVENT_TRACKING       = 'pmc_amp_hamburger_menu_event_tracking';
	const AMP_PUBLISHER_LOGO_OPTION_NAME               = 'pmc_amp_publisher_logo_option_name';
	const AMP_SKIMLINKS_SCRIPT                         = 'pmc_amp_skimlinks_script';
	const AMP_AUTO_REFRESH_ADS                         = 'pmc_amp_auto_refresh_ads_status';
	const AMP_ENABLE_PAGE_NEXT_FEATURE                 = 'pmc_amp_enable_page_next_feature';
	const AMP_JWPLAYER_ID                              = 'pmc_amp_jwplayer_id';
	const AMP_JWPLAYER_DOCKING                         = 'pmc_amp_jwplayer_docking';
	const AMP_AD_REFRESH_INTERVAL                      = 30;
	const AMP_NEXT_FEATURE_PAGE_COUNT                  = 5;

	public $content_max_width = 568;

	/**
	 * Array of IX amp slot ids.
	 *
	 * @var array
	 */
	public $ix_amp_ad_slot_ids = [];

	public static $ad_locations = array(
		'amp-header'        => 'Amp Header',
		'amp-mid-article'   => 'Amp Mid Article',
		'amp-mid-article-1' => 'Amp Mid Article 2',
		'amp-mid-article-x' => 'Amp Mid Article X',
		'amp-bottom'        => 'Amp Bottom',
		'amp-adhesion'      => 'Amp Adhesion',
	);

	public static $divid_count = 0;

	/*
	* Setup the hooks on init
	*
	* @since 2016-03-12
	* @version 2016-03-12 Archana Mandhare PMCVIP-1008
	* @version 2016-03-15 Archana Mandhare PMCVIP-1010
	* @version 2016-11-16 Debabrata Karfa PMCVIP-2426
	* @version 2016-12-01 Debabrata Karfa - PMCVIP-2582 - Add action to load social share bar template at header and footer
	*/
	protected function __construct()
	{
		// Action hooks
		add_action('amp_post_template_css', array($this, 'action_amp_additional_css_styles'));
		add_action('pmc_amp_content_after_header', array($this, 'get_ad'), 10, 2);
		add_action('pmc_amp_content_after_header', array($this, 'get_sticky_ad'), 10);
		add_action('pmc_amp_content_after_header', array($this, 'render_breadcrumbs'), 10);
		add_action('pmc_amp_content_before_footer', array($this, 'more_posts_from_category'));
		add_action('pmc_amp_content_before_footer', array($this, 'action_amp_add_outbrain'));
		add_action('pmc_amp_content_before_footer', array($this, 'get_ad'), 10, 2);
		add_action('pmc_amp_content_before_footer', [$this, 'add_next_page_component'], 11); // Priority 11 to keep the next page articles at the end.
		add_action('pre_amp_render_post', array($this, 'action_amp_add_custom_actions'));
		add_action('amp_post_template_footer', array($this, 'action_amp_add_copyright'));
		add_action('amp_display_social_share', array($this, 'amp_action_add_social_bar'));
		add_action('amp_post_template_head', array($this, 'amp_google_font'));
		add_action('amp_display_comments_link', array($this, 'amp_action_add_comments_link'));
		add_action('after_setup_theme', array($this, 'register_nav_menus'));
		add_action('pmc_amp_content_before_header', array($this, 'pmc_amp_content_before_header'));
		add_action('pmc_amp_content_before_footer', array($this, 'action_amp_add_skimlinks_code'));
		add_action('wp_default_scripts', [$this, 'amp_register_custom_scripts']);
		add_action('pmc_amp_content_before_footer', [$this, 'add_permutive_section_code']);

		// Filter hooks
		add_filter('amp_post_template_analytics', array($this, 'action_amp_template_analytics_tags'));
		add_filter('amp_post_template_file', array($this, 'filter_amp_set_default_template'), 10, 3);
		add_filter('amp_post_template_data', array($this, 'filter_amp_set_site_icon_url'));
		add_filter('amp_content_max_width', array($this, 'filter_amp_change_content_width'));
		add_filter('pmc_post_amp_content', array($this, 'insert_mid_article_ad_units'));
		add_filter('amp_post_template_meta_parts', array($this, 'amp_set_remove_meta_taxonomy_from_template'));
		add_filter('amp_post_template_data', array($this, 'amp_component_scripts'));
		add_filter('amp_content_embed_handlers', array($this, 'maybe_remove_oembeds'), 20, 2);
		add_filter('the_content', array($this, 'maybe_remove_oembed_embeddables'), 1);
		add_filter('amp_post_template_analytics', [$this, 'add_permutive_analytics']);

		// add acion hook to remove related article hook as late as possible
		// This hook must add before template_redirect where amp plugin do the rendering
		add_action('wp', array($this, 'action_remove_related_articles_hook'), 20);
		add_filter('redirect_canonical', array($this, 'redirect_amp_endpoint'), 10, 2);

		// add action to Change existing shortcode handlers for specific shortcodes if needed
		add_action('wp', array($this, 'maybe_change_shortcode_handler'));
		add_filter('pmc_cheezcap_groups', array($this, 'filter_pmc_cheezcap_groups'));
		add_filter('amp_post_template_metadata', array($this, 'filter_amp_update_json_metadata'), 10, 2);
		add_action('amp_post_template_head', array($this, 'amp_post_template_head'));
		add_action('amp_post_template_head', [$this, 'add_amp_experiment_meta']);
		add_filter('pmc_google_amp_ga_event_tracking', array($this, 'add_ga_tracking'));
		add_filter('pmc_google_amp_ad_rtc_config', [$this, 'update_amp_ad_rtc_config'], 10, 2);
		add_filter('amp_post_template_data', array($this, 'amp_remove_inline_stylesheets'));

		/**
		 * To move "amp-header" ad slot to before content.
		 * Bind late because some of theme use init action to reset cheez-cap option.
		 * Like "pmc-variety-2014"
		 */
		add_action('init', array($this, 'move_header_ad_before_content'), 15);
		add_action('wp', array($this, 'should_overwrite_related_link_shortcode_markup'));
		add_action('init', array($this, 'init_ad_manager'), 10);
	}

	/**
	 * To add filter to overwrite related link shortcode markup
	 * Overwrite only when it's AMP request.
	 *
	 * @hook wp
	 *
	 * @since 2017-07-25 Dhaval Parekh CDWE-446
	 *
	 * @return void
	 */
	public function should_overwrite_related_link_shortcode_markup()
	{

		if ($this->_is_amp()) {
			add_filter('pmc-related-link-shortcode-markup', array($this, 'get_related_link_markup'), 10, 4);
		}
	}

	/**
	 * Conditional method to check if current URL is AMP URL or not
	 *
	 * @since 2016-12-05 - Amit Gupta
	 *
	 * @return boolean Returns TRUE if current URL is AMP URL else FALSE
	 */
	protected function _is_amp()
	{

		if (function_exists('amp_is_request') && amp_is_request()) {
			return true;
		}

		return false;
	}

	/**
	 * init_ad_manager
	 *
	 * Adds custom ad types fields to ad-manager on the backend of WordPress
	 * This is so we can easily target an ad name across different LOBs
	 *
	 */
	public function init_ad_manager()
	{
		if (function_exists('\pmc_adm_add_locations')) {
			\pmc_adm_add_locations(self::$ad_locations);
		}
	}

	/*
	 * Filter to stop canonical redirect if we are on AMP endpoint
	 *
	 * @since 2016-05-13
	 * @version 2016-05-13 Archana Mandhare PMCVIP-1008
	 * @version 2019-09-30 Mike Auteri BR-417
	 *
	 */
	public function redirect_amp_endpoint($redirect_url, $requested_url)
	{

		if (PMC::is_amp()) {

			$redirect_url = $this->amplify_url((string) $redirect_url);
			$return       = false; // prevent canonical redirect and cyclic redirect.

			// Compares a few things here.
			// - Is true if AMP is missing trailing slash since $redirect_url is amplified.
			// - Is true if $redirect_url is different from $requested_url after being amplified.
			// If these do not match, we know we have to redirect, so we'll
			// return the "amplified" redirect URL.
			if (
				PMC::is_current_domain((string) $redirect_url)
				&& PMC::is_current_domain((string) $requested_url)
				&& strtolower($requested_url) !== strtolower($redirect_url)
			) {
				$return = $redirect_url;
			}

			// Returns either "amplified" $redirect_url or false to
			// prevent canonical redirect and cyclic redirect
			return $return;
		}

		return $redirect_url;
	}

	/**
	 * "Amplifies" the URL by appending `/amp/`
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function amplify_url(string $url): string
	{

		return sprintf(
			'%s/amp/',
			untrailingslashit(
				preg_replace('/\/amp\/?$/', '', $url)
			)
		);
	}

	/*
	 * Action hook to remove the Related Articles that get inserted into the content.
	 *
	 * @since 2016-03-12
	 * @version 2016-03-12 Archana Mandhare PMCVIP-1008
	 * @version 2016-03-15  Archana Mandhare PMCVIP-1012
	 */
	public function action_remove_related_articles_hook()
	{
		if (function_exists('is_amp_endpoint') && is_amp_endpoint()) {
			remove_all_filters('pmc_dom_insertions');
		}
	}

	/**
	 * To inject dynamic ad unit in AMP article.
	 *
	 * @since 2017-11-10 - Dhaval Parekh - CDWE-832
	 *
	 * @param string $content Post content.
	 *
	 * @return string
	 */
	public function insert_mid_article_ad_units($content)
	{

		if (empty($content)) {
			return $content;
		}

		if (false === apply_filters('pmc_google_amp_dynamic_ad_units', true)) {
			return $content;
		}

		// Cheezcap instance.
		$cheezcap_instance = \PMC_Cheezcap::get_instance();

		// Clean the content.
		$content = preg_replace('/<p[^>]*>(\s|\W+)?<\/p[^>]*>/', '', $content);

		$content_array = explode('</p>', $content);

		$total_ads_inserted  = 0;
		$last_ad_inserted_at = 0;
		$char_count          = 0;

		// Using ads position setting from Mobile Ads placeholder setting.
		$ad_unit_position = array(
			1 => absint($cheezcap_instance->get_option('pmc-ad-placeholders-first-pos-mobile')),
			2 => absint($cheezcap_instance->get_option('pmc-ad-placeholders-second-pos-mobile')),
		);

		// This is 3rd ad unit pos from last ad unit added.
		$repeated_ad_pos = absint($cheezcap_instance->get_option('pmc-ad-placeholders-x-pos-mobile'));

		$ad_locations = array(
			array(
				'id'       => 'amp-mid-article',
				'position' => max(500, $ad_unit_position[1]),
				'inserted' => false,
			),
			array(
				'id'       => 'amp-mid-article-1',
				'position' => max(2300, $ad_unit_position[2]),
				'inserted' => false,
			),
		);

		for ($index = 0; $index < count($content_array); $index++) {

			if (empty($content_array[$index])) {
				continue;
			}

			$char_count += strlen(strip_tags($content_array[$index], '<p>'));

			$content_array[$index] .= "</p>\n";

			for ($ad_index = 0; $ad_index < count($ad_locations); $ad_index++) {

				$ad_data = $ad_locations[$ad_index];

				// If ad is already inserted than ignore.
				if (true === $ad_data['inserted']) {
					continue;
				}

				$ad_key = $ad_data['id'];

				if ($char_count >= $ad_data['position']) {

					$ad_locations[$ad_index]['inserted'] = true;
					$content_array[$index]              .= $this->get_ad($ad_key, false);
					$last_ad_inserted_at                   = $char_count;

					$total_ads_inserted++;

					// Do not add two ads after one paragraph back to back.
					// they should have atleast distance of one paragraph.
					continue 2;
				}
			}

			// Handling repeated inline article ad unit 'amp-mid-article-x'
			// Calculating new char limit for ad unit to insert.
			$repeated_ad_char_limit = $last_ad_inserted_at + $repeated_ad_pos;

			// Must be added only after first two ad unit are added.
			if (!empty($repeated_ad_pos) && $char_count >= $repeated_ad_char_limit && 2 <= $total_ads_inserted) {
				$ad_html                  = $this->get_ad('amp-mid-article-x', false);
				$ad_html                  = str_replace('amp-mid-article-x', 'amp-mid-article-x-' . $total_ads_inserted, $ad_html);
				$content_array[$index] .= $ad_html;
				$last_ad_inserted_at      = $char_count;

				$total_ads_inserted++;
			}
		}

		$content = implode('', $content_array);

		return $content;
	}


	/*
	* Action hook to renders the HTML and other script tags to after the header
	*
	* @since 2016-03-23
	* @version 2016-03-23 Archana Mandhare PMCVIP-1011
	*/
	public function get_ad($ad_slot = '', $echo = false)
	{

		if (empty($ad_slot)) {
			return false;
		}

		$pmc_cheezcap = PMC_Cheezcap::get_instance();

		// Get the ad meta for the ad.
		$meta = $this->get_ad_meta($ad_slot);

		$provider          = (!empty($meta['provider'])) ? $meta['provider'] : false;
		$provider_instance = PMC_Ads::get_instance()->get_provider($provider);

		if (false === $provider_instance) {
			return false;
		}

		$provider_key      = $provider_instance->get_key();

		if (empty($meta) || empty($provider_key)) {
			return false;
		}

		$template_suffix = ('boomerang' === $provider && !defined('PMC_ADM_V2')) ? '-boomerang' : '';
		$template        = apply_filters('pmc_google_amp_ad_template', PMC_GOOGLE_AMP_ROOT . '/templates/ad-slot' . $template_suffix . '.php', $ad_slot);
		$ad_div_id       = sprintf('ad-div-%d', self::$divid_count++);

		$amp_sticky_ad_status = $pmc_cheezcap->get_option(self::AMP_STICKY_AD_STATUS);

		// Common settings.
		$renderer_options = [
			'ad_div_id'            => $ad_div_id,
			'height'               => intval($meta['height']),
			'width'                => intval($meta['width']),
			'css_class'            => (!empty($meta['css-class'])) ? $meta['css-class'] : '',
			'ad_slot'              => $ad_slot,
			'amp_sticky_ad_status' => $amp_sticky_ad_status,
			'is_sticky_ad'         => ('yes' === $amp_sticky_ad_status && 'amp-adhesion' === $ad_slot),
		];

		$json = $this->prep_ad_json($ad_slot);

		if ('boomerang' === $provider && PMC_GOOGLE_AMP_ROOT . '/templates/ad-slot-boomerang.php' === $template) {

			$settings = $provider_instance->prepare_boomerang_global_settings();

			if (!$settings || empty($meta['ad-display-type'])) {
				return false;
			}

			$renderer_options = array_merge(
				$renderer_options,
				[
					'boomerang_path' => dirname(wp_parse_url($settings['header_script_url'], PHP_URL_PATH)),
					'slot_type'      => $meta['ad-display-type'],
				]
			);

			$json['targeting'] = array_merge($settings['targeting_data'] ?? [], $json['targeting'] ?? []);

			$json['boomerangConfig'] = [
				'vertical' => $settings['vertical'],
			];
		} else {

			$provider_key = (defined('PMC_ADM_V2')) ? '8352' : $provider_key;

			$slot = sprintf('/%s/%s/%s', $provider_key, $meta['sitename'], $meta['zone']);

			// Ad realtime config for krux.
			$rtc_config = [
				'urls' => [
					'https://cdn.krxd.net/userdata/v2/amp/c500aa57-d425-43d5-867c-ffa47fd2e0dd?segments_key=ksg&kuid_key=kuid',
				],
			];

			$rtc_config       = apply_filters('pmc_google_amp_ad_rtc_config', $rtc_config, $ad_slot, $ad_div_id, $renderer_options);
			$multi_size       = $this->get_formatted_ad_sizes($meta);
			$refresh_interval = self::AMP_AD_REFRESH_INTERVAL;

			if ('YES' === $meta['is-ad-rotatable'] && 'yes' === $pmc_cheezcap->get_option(self::AMP_AUTO_REFRESH_ADS)) {
				if (isset($meta['ad-refresh-time'])) {
					$refresh_interval = $meta['ad-refresh-time'];
				}
			} elseif ('NO' === $meta['is-ad-rotatable'] || 'no' === $pmc_cheezcap->get_option(self::AMP_AUTO_REFRESH_ADS)) {
				$refresh_interval = 0;
			}

			$renderer_options = array_merge(
				$renderer_options,
				[
					'slot'             => $slot,
					'rtc_config'       => (!empty($rtc_config)) ? $rtc_config : [],
					'multi_size'       => $multi_size,
					'refresh_interval' => $refresh_interval,
				]
			);
		}

		$renderer_options['json'] = apply_filters('pmc_google_amp_json_config', $json, $ad_slot);

		$html = PMC::render_template($template, $renderer_options);

		if (true !== $echo) {
			return $html;
		}

		echo $html;
	}


	/*
	* Action hook to render the krux analytics template in the footer
	* Action hook to renders the analytics, ads and other script tags to the bottom of the page just before closing body tag
	* Add all the scripts to tag-bottom.php file
	*
	* @see https://github.com/ampproject/amphtml/blob/master/extensions/amp-analytics/amp-analytics.md#analytics-vendors
	* @since 2016-03-12
	* @version 2016-03-12 Archana Mandhare PMCVIP-1008
	* @version 2016-03-15  Archana Mandhare PMCVIP-1012
	* @version 2017-05-11  Dhaval Parekh CDWE-348
	*/
	public function action_amp_template_analytics_tags($analytics)
	{
		if (!is_array($analytics)) {
			$analytics = array();
		}

		$ga_id = $this->get_ga_id();

		if (!empty($ga_id)) {

			$analytics['ga'] = array(

				'type'        => 'googleanalytics',

				'attributes'  => array(
					// 'data-credentials' => 'include',
				),

				'config_data' => array(

					'vars' => array(
						'account' => $ga_id
					),

					'extraUrlParams' => $this->get_ga_custom_dimensions(),

					'triggers' => array(

						'trackPageviewWithCustomData' => array(
							'label'   => 'trackPageviewWithCustomData',
							'on'      => 'visible',
							'request' => 'pageview',
						),

					),

				),

			);

			/**
			 * Filter to add events for google analytics.
			 *
			 * @since  2017-05-11 Dhaval Parekh
			 * @ticket CDWE-348
			 */
			$events = apply_filters('pmc_google_amp_ga_event_tracking', $analytics['ga']['config_data']['triggers']);

			$events = (!empty($events) && is_array($events)) ? $events : array();

			if (!empty($events)) {

				foreach ($events as $event) {

					$event['on']       = (!empty($event['on'])) ? esc_js($event['on']) : 'click';
					$event['request']  = (!empty($event['request'])) ? esc_js($event['request']) : 'event';
					$event['label']    = (!empty($event['label'])) ? esc_js($event['label']) : '';

					/**
					 * Do not escape this value here.
					 * Because this may contain value that have single/double quotes
					 * i.g. 'a[href='https://example.com']'
					 * Escaping of that will lead to invalid value.
					 * and result will break inline link GA event tracking.
					 */
					$event['selector'] = (!empty($event['selector'])) ? $event['selector'] : '';

					$event['category'] = (!empty($event['category'])) ? esc_js($event['category']) : '';

					$analytics['ga']['config_data']['triggers'][$event['label']] = array(
						'on'		 => $event['on'],
						'selector'	 => $event['selector'],
						'request'	 => $event['request'],
						'vars'		 => array(
							'eventCategory'	 => $event['category'],
							'eventAction'	 => $event['on'],
							'eventLabel'	 => $event['label'],
						),
					);

					if (!empty($event['scrollSpec'])) {

						$analytics['ga']['config_data']['triggers'][$event['label']]['scrollSpec'] = $event['scrollSpec'];
					}
				}
			}
		}

		$comscore_id = $this->get_comscore_id();

		if (!empty($comscore_id)) {

			$analytics['comscore'] = array(

				'type' => 'comscore',
				'attributes' => array(),
				'config_data' => array(
					'vars' => array(
						'c2' => $comscore_id,
					),
					'extraUrlParams' => array(
						'comscorekw' => 'amp',
					),
				),

			);
		}

		$krux_params = $this->get_krux_params();

		if (!empty($krux_params)) {
			$analytics['krux'] = $krux_params;
		}

		$fp_page_data    = [];
		$fp_article_data = [];

		if (class_exists('PMC_Page_Meta')) {
			$fp_page_data    = \PMC_Page_Meta::get_page_data();
			$fp_article_data = \PMC_Page_Meta::get_article_data();
		}

		$analytics['treasuredata'] = [
			'type'        => 'treasuredata',
			'attributes'  => [],
			'config_data' => [
				'vars'     => [
					'host'     => self::TREASUREDATA_HOST,
					'writeKey' => self::TREASUREDATA_API_KEY,
					'database' => self::TREASUREDATA_DATABASE,
				],
				'triggers' => [
					'trackPageview' => [
						'on'             => 'visible',
						'request'        => 'pageview',
						'vars'           => [
							'table' => 'pageviews',
						],
						'extraUrlParams' => [
							'pagetype' => 'amp',
						],
					],
				],
			],
		];

		if (!empty($fp_page_data)) {
			$analytics['treasuredata']['config_data']['triggers']['trackPageview']['extraUrlParams']['page'] = wp_json_encode($fp_page_data);
		}

		if (!empty($fp_article_data)) {
			$analytics['treasuredata']['config_data']['triggers']['trackPageview']['extraUrlParams']['article'] = wp_json_encode($fp_article_data);
		}

		return $analytics;
	}


	/*
	* Modify the Page Meta values as per Krux requirements
	*
	* @since 2016-03-31
	* @version 2016-03-31 Archana Mandhare PMCVIP-1012
	*/
	private function _filter_page_meta_for_krux($page_meta)
	{

		if (empty($page_meta) || !is_array($page_meta)) {
			return false;
		}

		$page_meta['keywords'] = wp_list_pluck((array) get_the_tags(get_the_ID()), 'name');

		$krux_meta = array();

		foreach ($page_meta as $key => $value) {

			$krux_value = $value;
			if (is_array($krux_value)) {
				$krux_value = implode(',', $krux_value);
			}

			switch ($key) {

				case 'logged-in':
				case 'subscriber-type':
				case 'country':
					$krux_meta['user.' . $key] = $krux_value;
					break;

				case 'page-type':
					$krux_meta['page.type'] = $krux_value;
					break;

				default:
					$krux_meta['page.' . $key] = $krux_value;
					break;
			}
		}

		return $krux_meta;
	}

	/**
	 * Set up a new customized default template.
	 *
	 * @since 2016-03-23
	 * @version 2016-03-23 Archana Mandhare PMCVIP-1011
	 * @version 2016-12-15 Debabrata Karfa  PMCVIP-2659
	 * @version 2017-01-31 Chandra Patel  CDWE-124
	 *
	 * @param string  $file template file.
	 * @param string  $type template type.
	 * @param WP_Post $post WP_Post instance.
	 *
	 * @return string $file template file.
	 */
	public function filter_amp_set_default_template($file, $type, $post)
	{

		$original_file = $file;

		/**
		 * Allow other post types to use overridden template file.
		 *
		 * @since 2017-03-02 Chandra Patel CDWE-140
		 */
		$post_types = apply_filters('pmc_google_amp_template_allow_post_types', array('post', 'pmc-gallery'), $type, $post);

		if (!empty($post->post_type) && in_array($post->post_type, $post_types, true)) {

			if ('single' === $type) {
				$file = sprintf('%s/templates/single.php', untrailingslashit(PMC_GOOGLE_AMP_ROOT));
			} elseif ('meta-author' === $type) {
				$file = sprintf('%s/templates/meta-author.php', untrailingslashit(PMC_GOOGLE_AMP_ROOT));
			} elseif ('meta-time' === $type) {
				$file = sprintf('%s/templates/meta-time.php', untrailingslashit(PMC_GOOGLE_AMP_ROOT));
			}
		}

		$file = apply_filters('pmc-google-amp-template-path', $file, $type, $post);

		if (empty($file) || !file_exists($file) || validate_file($file) !== 0) {
			$file = $original_file;
		}

		return $file;
	}

	/**
	 * get_ad_meta
	 *
	 * @param string $slot
	 */
	public function get_ad_meta($slot = '')
	{

		// Get the ad meta by render slot in templates
		$meta = PMC_Ads::get_instance()->get_ads_to_render($slot);

		if (empty($meta) || !is_array($meta)) {
			return false;
		}

		foreach ($meta as $ad_meta) {
			if (!isset($meta['div-id'])) {
				$meta['div-id'] = '';
			}

			if (!isset($meta['height'])) {
				$meta['height'] = '';
			}

			if (!isset($meta['width'])) {
				$meta['width'] = '';
			}

			return (array) $ad_meta;
		}
	}


	/**
	 * prep_ad_json
	 *
	 */
	public function prep_ad_json($ad_slot = '')
	{

		$json      = array();
		$post_tags = array();

		$tags = get_the_tags(get_the_ID());

		if (!empty($tags) && is_array($tags)) {
			$post_tags = array_filter(wp_list_pluck((array) $tags, 'name'));
		}

		if (!empty($post_tags) && is_array($post_tags)) {
			$json['targeting']['kw'] = $post_tags;
		}

		if (!empty($ad_slot)) {

			// Get the ad meta for the ad.
			$ad_meta = $this->get_ad_meta($ad_slot);

			if (!empty($ad_meta['targeting_data'])) {

				foreach ($ad_meta['targeting_data'] as $data) {

					$json['targeting'][$data['key']][] = $data['value'];
				}
			}
		}

		$json['targeting']['plat'][] = 'amp';

		return $json;
	}


	/*
	* Action hook to flush rewrite rules and add the rewrite rules
	*
	* @since 2016-03-21
	* @version 2016-03-21 Archana Mandhare PMCVIP-1008
	*
	*/
	public function action_rewrite_rules_and_flush_rules()
	{
		add_rewrite_rule('([0-9]{4})/([^/]+)/([^/]+)/([^/]+)-([0-9]+)/amp/?$', 'index.php?year=$matches[1]&vertical=$matches[2]&category_name=$matches[3]&name=$matches[4]&p=$matches[5]&amp=1', 'top');
		if (!PMC::is_production()) {
			flush_rewrite_rules();
		}
	}


	/*
	* Filter to Set the max width of the content
	* @since 2016-03-12
	* @version 2016-03-12 Archana Mandhare PMCVIP-1008
	*
	* @param string $content_max_width
	* @return string
	*/
	public function filter_amp_change_content_width($content_max_width)
	{
		return $this->content_max_width;
	}


	/*
	* Setup the logo
	*
	* @since 2016-03-15
	* @version 2016-03-15 Archana Mandhare PMCVIP-1010
	*
	* @param array $data
	*
	* @return array $data
	*
	*/
	public function filter_amp_set_site_icon_url($data)
	{
		$data['canonical_url'] = \PMC\SEO_Tweaks\Helpers::canonical(false, true, get_the_ID());
		return $data;
	}


	/*
	* Modify css and add new styles to the page from here
	*
	* @since 2016-03-12
	* @version 2016-03-12 Archana Mandhare PMCVIP-1008
	*
	* @param string $amp_template
	*/
	public function action_amp_additional_css_styles($amp_template)
	{

		echo PMC::render_template(sprintf('%s/templates/core-css.php', untrailingslashit(PMC_GOOGLE_AMP_ROOT)), array(

			'content_max_width' => $this->content_max_width,
			'bg_img_url'        => sprintf('%s/assets/images/%s.png', untrailingslashit(PMC_GOOGLE_AMP_URL), PMC::get_current_site_name()),

		));

		$styles = apply_filters('pmc-google-amp-styles', '');

		if (!empty($styles)) {
			echo $styles;
		}
	}

	/**
	 * Modify post content using this hook
	 *
	 * @since 2016-03-12
	 * @version 2016-03-12 Archana Mandhare PMCVIP-1008
	 * @version 2017-05-11 Dhaval Parekh CDWE-348
	 * @version 2018-02-16 Dhaval Parekh READS-881
	 *
	 * @return void
	 */
	public function action_amp_add_custom_actions()
	{

		if (!$this->_is_amp()) {
			return;
		}

		/**
		 * If in theme option "Add Launch Galley" set yes, only then
		 * Add "Launch Gallery" button in content.
		 */
		if ($this->_get_launch_gallery_status()) {
			add_filter('the_content', array($this, 'amp_add_gallery_links'));
		}

		/**
		 * If in theme option "Add Gallery Teaser" option is enable,
		 * then add gallery teaser.
		 */
		if ($this->_is_gallery_teaser_enabled()) {
			add_filter('the_content', array($this, 'add_gallery_teaser'));
		}

		add_filter('the_content', array($this, 'amp_add_featured_image'));
	}


	/**
	 * Filter to Add a featured Video/Image above the content.
	 *
	 * @since   2016-03-15
	 * @version 2016-03-15 Archana Mandhare PMCVIP-1010
	 * @version 2017-03-30 Chandra Patel CDWE-154
	 *
	 * @param string $content The post content.
	 *
	 * @return string
	 */
	public function amp_add_featured_image($content)
	{

		$featured_content = '';

		if (class_exists('PMC_Featured_Video_Override') && PMC_Featured_Video_Override::has_featured_video(get_the_id()) === true) {

			$featured_content = PMC_Featured_Video_Override::get_video_html5(get_the_id(), array('width' => 568));
		} elseif (has_post_thumbnail()) {

			$default_template = sprintf('%s/templates/featured-image.php', untrailingslashit(PMC_GOOGLE_AMP_ROOT));
			// To give more flexibility added filter to change template.
			$template = apply_filters('pmc_google_amp_featured_image_template', $default_template);

			if (empty($template) || !file_exists($template)) {
				$template = $default_template;
			}

			$post_id = get_the_ID();
			$size    = 'post-thumbnail'; // This is the default
			$attr    = ['data-hero' => ''];

			// Just add the raw <img /> tag; our sanitizer will take care of it later.
			$featured_content = PMC::render_template($template, array(
				'image'        => get_the_post_thumbnail($post_id, $size, $attr),
				'image_credit' => get_post_meta(get_post_thumbnail_id(), '_image_credit', true),
			));
		}

		$content = $featured_content . $content;

		return $content;
	}


	/*
	* Action hook to Add a outbrain widget in the footer
	*
	* @since 2016-03-15
	* @version 2016-03-15 Archana Mandhare PMCVIP-1012
	* @version 2018-07-16 Jignesh Nakrani READS-1356
	*
	* @param string $amp_template
	*
	*/
	public function action_amp_add_outbrain($amp_template)
	{

		if (false === apply_filters('pmc_google_amp_outbrain_is_enabled', true)) {
			return;
		}

		$canonical = \PMC\SEO_Tweaks\Helpers::canonical(false, true, get_the_ID());

		$args = array(
			'html_url'   => PMC_Feature::make_link_https($canonical),
			'amp_url'    => function_exists('amp_get_permalink') ? amp_get_permalink(get_the_ID()) : get_permalink(get_the_ID()),
			'widget_ids' => 'AMP_1',
		);

		$template = PMC_GOOGLE_AMP_ROOT . '/templates/outbrain.php';
		\PMC::render_template($template, $args, true);
	}

	/**
	 * Add next page component.
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function add_next_page_component(): void
	{

		if (!is_single() || false === $this->is_amp_next_page_feature_enabled()) {
			return;
		}

		$template = PMC_GOOGLE_AMP_ROOT . '/templates/next-page.php';

		\PMC::render_template(
			$template,
			[
				'next_page_data' => $this->get_next_page_data(),
			],
			true
		);
	}


	/*
	* Action hook to add text and html to the footer
	*
	* @since 2016-03-15
	* @version 2016-03-15 Archana Mandhare PMCVIP-1010
	*/
	public function action_amp_add_copyright($amp_template)
	{
		$next_page_hide_attr = (\PMC\Google_Amp\Plugin::get_instance()->is_at_least_version('2.0.4')) ? 'next-page-hide' : '';

		printf( /* translators: %1$s - Starting <p>, %2$s - Current year, %3$s - Ending </p> */
			esc_html__('%1$s &copy; %2$s PMC. All rights reserved.%3$s', 'pmc-google-amp'),
			'<p class="copyright" ' . esc_attr($next_page_hide_attr) . '>',
			esc_html(gmdate('Y')),
			'</p>'
		);
	}


	/**
	 * Helper function to return comscore id
	 * @version 2016-10-03 Hau Vong PPT-7030
	 */
	public function get_comscore_id()
	{
		// allow theme to override the value
		return apply_filters('pmc_comscore_id', self::COMSORE_ID);
	}


	/**
	 * Helper function to return ga_id
	 * @version 2016-10-03 Hau Vong PPT-7030
	 */
	public function get_ga_id()
	{
		return get_option('pmc_google_analytics_account');
	}


	/**
	 * Helper function to return krux params array
	 * @version 2016-10-03 Hau Vong PPT-7030
	 */
	public function get_krux_params()
	{
		// Get krux config id from cheezcap setting
		$krux_config_id = PMC_Cheezcap::get_instance()->get_option('pmc_krux_tag_config_id');

		if (!empty($krux_config_id) && class_exists('PMC_Page_Meta') && class_exists('PMC_Krux_Tag')) {
			$meta     = \PMC_Page_Meta::get_page_meta();
			$meta     = $this->_filter_page_meta_for_krux($meta);

			return array(
				'type' => 'krux',
				'attributes' => array('config' => esc_url("https://cdn.krxd.net/controltag/amp/{$krux_config_id}.json")),
				'config_data' => array(
					'vars' => array(
						'extraUrlParams' => $meta,
					)
				),
			);
		}
		return false;
	}

	/**
	 * Modification on AMP Template output, to remove Meta Taxonomy from Content Template Part
	 *
	 * @ticket PMCVIP-2506
	 * @since  2016-11-16 - Debabrata Karfa
	 * @param  array $meta_parts Contains meta data of header_meta.
	 * @return array             Modified header_meta data.
	 */
	public function amp_set_remove_meta_taxonomy_from_template($meta_parts = '')
	{

		$remove_meta  = array_keys($meta_parts, 'meta-taxonomy', true);
		foreach ($remove_meta as $key) {
			unset($meta_parts[$key]);
		}
		return $meta_parts;
	}

	/**
	 * Modification on AMP Template output, to Add Meta Taxonomy at Template Footer Part
	 *
	 * @ticket PMCVIP-2506
	 * @since  2016-11-16 - Debabrata Karfa
	 * @param  string $amp_template Contains the file.
	 * @return void
	 */
	public function amp_action_add_meta_tax($amp_template = '')
	{

		if (!get_the_ID()) {
			return;
		}

		echo PMC::render_template(PMC_GOOGLE_AMP_ROOT . '/templates/meta-custom-tax.php', array('post_id' => get_the_ID()));
	}

	/**
	 * Add the Google Font to load on header for AMP Theme
	 *
	 * @ticket PMCVIP-2426
	 * @since 2016-11-16 - Debabrata Karfa
	 * @param string  $amp_template Contains a string of the amp html template view
	 * @return void No return sent, though the output is printed to the screen.
	 */
	public function amp_google_font($amp_template = '')
	{
		echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,700">';
	}

	/**
	 * Adds the JS files to be loaded in header of AMP theme
	 * Note:in newer version of amp plugin, builtin extensionss are autoload, no need to manually added here
	 *
	 * @ticket PMCVIP-2489
	 * @since 2016-11-16 - Debabrata Karfa
	 * @param  array $data Array of javascript entries, which want to output at header.
	 * @return array Return array AMP data with added javascript entries.
	 */
	public function amp_component_scripts($data = [])
	{

		$data['amp_component_scripts']['amp-iframe'] = 'https://cdn.ampproject.org/v0/amp-iframe-0.1.js';
		$data['amp_component_scripts']['amp-ad'] = 'https://cdn.ampproject.org/v0/amp-ad-0.1.js';
		$data['amp_component_scripts']['amp-social-share'] = 'https://cdn.ampproject.org/v0/amp-social-share-0.1.js';

		// Only render the jwplayer script when there is a jwplayer embded in the post content.
		if (false !== strpos($data['post_amp_content'], '<amp-jwplayer')) {
			$data['amp_component_scripts']['amp-jwplayer'] = 'https://cdn.ampproject.org/v0/amp-jwplayer-0.1.js';
			if (false !== strpos($data['post_amp_content'], ' dock')) {
				$data['amp_component_scripts']['amp-video-docking'] = 'https://cdn.ampproject.org/v0/amp-video-docking-0.1.js';
			}
		}

		$pmc_cheezcap = PMC_Cheezcap::get_instance();
		$amp_sticky_ad_status = $pmc_cheezcap->get_option(self::AMP_STICKY_AD_STATUS);
		if ('yes' === $amp_sticky_ad_status) {
			$data['amp_component_scripts']['amp-sticky-ad'] = 'https://cdn.ampproject.org/v0/amp-sticky-ad-1.0.js';
		}

		if ($this->_is_gallery_teaser_enabled()) {
			$data['amp_component_scripts']['amp-anim'] = 'https://cdn.ampproject.org/v0/amp-anim-0.1.js';
		}

		if ($this->_is_skimlinks_enabled()) {
			$data['amp_component_scripts']['amp-skimlinks'] = 'https://cdn.ampproject.org/v0/amp-skimlinks-0.1.js';
		}

		if (true === $this->is_amp_next_page_feature_enabled()) {

			// PMCP-2576 follow up: Remove else when AMP is updated
			if (\PMC\Google_Amp\Plugin::get_instance()->is_at_least_version('2.0.4')) {
				$data['amp_component_scripts']['amp-next-page'] = 'https://cdn.ampproject.org/v0/amp-next-page-1.0.js';
			} else {
				$data['amp_component_scripts']['amp-next-page'] = 'https://cdn.ampproject.org/v0/amp-next-page-0.1.js';
			}
		}

		return $data;
	}

	/**
	 * Register custom scripts for AMP components.
	 *
	 * @param \WP_Scripts $wp_scripts Scripts.
	 */
	public function amp_register_custom_scripts($wp_scripts)
	{

		// amp-skimlinks AMP API.
		$handle = 'amp-skimlinks';
		$wp_scripts->add(
			$handle,
			'https://cdn.ampproject.org/v0/amp-skimlinks-0.1.js',
			array(),
			null
		);
	}

	/**
	 * Called on 'wp' hook, this method removes existing handlers for specific
	 * shortcodes and adds custom handlers if the current post URL is for AMP version
	 *
	 * @ticket PMCVIP-2489
	 *
	 * @since 2016-11-03 - Amit Gupta
	 *
	 * @return void
	 */
	public function maybe_change_shortcode_handler()
	{

		if (!$this->_is_amp()) {
			return;
		}

		/*
		 * Add custom handler for [jwplatform] & [jwplayer] shortcodes
		 */
		remove_shortcode('jwplatform');
		add_shortcode('jwplatform', array($this, 'jwplayer_handle_shortcode_for_amp'));

		remove_shortcode('jwplayer');
		add_shortcode('jwplayer', array($this, 'jwplayer_handle_shortcode_for_amp'));
	}

	/**
	 * This method is a custom handler for the [jwplatform] shortcode which outputs
	 * embed code for AMP
	 *
	 * @ticket PMCVIP-2489
	 *
	 * @since 2016-11-03 - Amit Gupta
	 *
	 * @param array $atts Attributes of shortcode
	 * @return string AMP version of JWPlayer embed code else empty string if JWPlatform is not activated on site or shortcode is malformed
	 */
	public function jwplayer_handle_shortcode_for_amp($atts)
	{

		$matches = array();

		if (!empty($atts[0])) {

			// RegEx to break the first attr into video ID
			// and (if its there) player ID
			$regex = '/([0-9a-z]{8})(?:[-_])?([0-9a-z]{8})?/i';
			if (!preg_match($regex, $atts[0], $matches)) {
				// Invalid shortcode
				return '';
			}
		}

		$video_hash = (!empty($matches[1])) ? $matches[1] : '';
		$player_hash = (!empty($matches[2])) ? $matches[2] : get_option('jwplayer_player', '');

		if (
			class_exists('\PMC\JW_YT_Video_Migration\Post_Migration')
			&& class_exists('\PMC\JW_YT_Video_Migration\Cheez_Options')
			&& true === \PMC\JW_YT_Video_Migration\Cheez_Options::is_migration_enabled()
		) {

			return \PMC\JW_YT_Video_Migration\Post_Migration::get_instance()->output_shortcode(array(
				$video_hash,
				$player_hash
			));
		}

		// Check if the current site is logged into JWPlatform or not
		$login = get_option('jwplayer_api_key');
		if (empty($login)) {
			// Current site not logged into JWPlatform, bail out
			// This is as per JWPlayer plugin & should remain as is
			return '';
		}

		return self::get_amp_jwplayer(
			[
				$video_hash,
				$player_hash,
			]
		);
	}

	/**
	 * Add Social Share bar to header and footer
	 *
	 * @ticket PMCVIP-2582
	 * @since  2016-11-22 - Debabrata Karfa
	 *
	 *
	 * @return void No return sent, display the Social share icon.
	 */
	public function amp_action_add_social_bar($location = 'top')
	{
		$defaults_args      = array('facebook', 'twitter', 'pinterest', 'email');
		$social_share_icons = apply_filters('amp_social_share_icons', $defaults_args);

		$amp_social_share_template = apply_filters(
			'amp_social_share_template',
			sprintf('%s/templates/social-share.php', untrailingslashit(PMC_GOOGLE_AMP_ROOT)),
			$location
		);

		if (file_exists($amp_social_share_template) && 0 === validate_file($amp_social_share_template)) {

			echo PMC::render_template($amp_social_share_template, array(
				'fb_data_param_app_id' => apply_filters('amp-fb-app-id', ''),
				'social_share_icons'   => $social_share_icons,
				'location'             => $location,
			));
		}
	}

	/**
	 * Display comments link below bottom social icons and above Outbrain module.
	 *
	 * @ticket CDWE-96
	 * @since  2017-01-19 - Chandra Patel
	 *
	 * @return void No return sent, display the comments link.
	 */
	public function amp_action_add_comments_link()
	{

		if ($this->_is_comment_button_enabled()) {

			echo PMC::render_template(PMC_GOOGLE_AMP_ROOT . '/templates/comments-link.php', array(
				'comments_link'   => get_permalink() . '#respond',
				'comments_number' => get_comments_number(),
			));
		}
	}

	/**
	 * Called by 'amp_content_embed_handlers' filter, this function removes
	 * oembed handlers as needed per LoB
	 *
	 * @ticket PMCVIP-2489
	 *
	 * @since 2016-12-05 - Amit Gupta
	 *
	 * @param array $embed_handlers oEmbed Handlers
	 * @param WP_Post $post Post object passed by the filter
	 * @return array oEmbed Handlers
	 */
	public function maybe_remove_oembeds($embed_handlers = array(), $post = null)
	{

		if (!$this->_is_amp()) {
			return $embed_handlers;
		}

		// Remove Instagram oEmbed handler if LoB theme so desires
		if (apply_filters('pmc-google-amp-remove-instagram-embed', false) === true) {

			unset($embed_handlers['AMP_Instagram_Embed_Handler']);
		}

		return $embed_handlers;
	}

	/**
	 * Method to remove Instagram embeddables from a text block
	 *
	 * @ticket PMCVIP-2489
	 *
	 * @since 2016-12-05 - Amit Gupta
	 *
	 * @param string $content Text from which Instagram embeddables are to be removed
	 * @return string Text with Instagram embeddables removed
	 */
	protected function _remove_instagram_embeddables($content)
	{

		if (empty($content) || !is_string($content)) {
			return $content;
		}

		$pattern = '#http(s?)://(www\.)?instagr(\.am|am\.com)/p/(.+)?#im';

		$has_matches = (int) preg_match_all($pattern, $content, $matches);

		if ($has_matches < 1 || empty($matches[0]) || !is_array($matches[0])) {
			return $content;
		}

		for ($i = 0; $i < count($matches[0]); $i++) {

			$content = str_replace($matches[0][$i], '', $content);
		}

		return $content;
	}

	/**
	 * Called by 'the_content' filter, this function removes
	 * oembed embeddables from post content as needed per LoB
	 *
	 * @ticket PMCVIP-2489
	 *
	 * @since 2016-12-05 - Amit Gupta
	 *
	 * @param string $content Content of current post
	 * @return string Cleaned up content of current post
	 */
	public function maybe_remove_oembed_embeddables($content = '')
	{

		if (!$this->_is_amp()) {
			return $content;
		}

		// Remove Instagram oEmbed embeddable if LoB theme so desires
		if (apply_filters('pmc-google-amp-remove-instagram-embed', false) === true) {
			$content = $this->_remove_instagram_embeddables($content);
		}

		return $content;
	}

	/*
	 * Add cheezcap options fot amp openx header bidder
	 *
	 * @param array $cheezcap_groups adding new cheezcap group options.
	 *
	 * @return array
	 */
	public function filter_pmc_cheezcap_groups($cheezcap_groups = array())
	{

		if (empty($cheezcap_groups) || !is_array($cheezcap_groups)) {
			$cheezcap_groups = array();
		}

		$cheezcap_options = [];

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Enable Sticky Ad', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('By enabling this option sticky Ad unit is added at the bottom of amp pages', 'pmc-google-amp'), true),
			self::AMP_STICKY_AD_STATUS,
			array('no', 'yes'),
			0, // first option => No.
			array(wp_strip_all_tags(__('No', 'pmc-google-amp'), true), wp_strip_all_tags(__('Yes', 'pmc-google-amp'), true))
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Move "amp-header" ad slot before post content', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('By enabling this option "amp-header" ad slot will move to before post content instead of right after header', 'pmc-google-amp'), true),
			self::AMP_MOVE_HEADER_AD_BEFORE_CONTENT_STATUS,
			array('no', 'yes'),
			0, // first option => No.
			array(wp_strip_all_tags(__('No', 'pmc-google-amp'), true), wp_strip_all_tags(__('Yes', 'pmc-google-amp'), true))
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Add "Launch Gallery" button on AMP Pages', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('By enabling this option "Launch Gallery" button will added before content if any gallery is related to post', 'pmc-google-amp'), true),
			self::AMP_LAUNCH_GALLERY_STATUS,
			array('no', 'yes'),
			0, // first option => No.
			array(wp_strip_all_tags(__('No', 'pmc-google-amp'), true), wp_strip_all_tags(__('Yes', 'pmc-google-amp'), true))
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Enable gallery teaser', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('By enabling this option "Gallery Teaser" will added before content if any gallery is related to post', 'pmc-google-amp'), true),
			self::AMP_GALLERY_TEASER_STATUS,
			array('no', 'yes'),
			0, // first option => No.
			array(wp_strip_all_tags(__('No', 'pmc-google-amp'), true), wp_strip_all_tags(__('Yes', 'pmc-google-amp'), true))
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Include thumbnail in "Related link"', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('To show post thumbnail in related link if available.', 'pmc-google-amp'), true),
			self::AMP_INCLUDE_THUMBNAIL_IN_RELATED_LINK_STATUS,
			array('no', 'yes'),
			0, // first option => Disable.
			array(wp_strip_all_tags(__('No', 'pmc-google-amp'), true), wp_strip_all_tags(__('Yes', 'pmc-google-amp'), true))
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Gallery depth tracking', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('To enable gallery depth event tracking', 'pmc-google-amp'), true),
			self::AMP_GALLERY_DEPTH_TRACKING_STATUS,
			array('no', 'yes'),
			0, // first option => No.
			array(wp_strip_all_tags(__('No', 'pmc-google-amp'), true), wp_strip_all_tags(__('Yes', 'pmc-google-amp'), true))
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Enable Gallery thumbnail', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('To show gallery thumbnail below gallery in AMP page.', 'pmc-google-amp'), true),
			self::AMP_GALLERY_THUMBNAIL_STATUS,
			array('no', 'yes'),
			0, // first option => No.
			array(wp_strip_all_tags(__('No', 'pmc-google-amp'), true), wp_strip_all_tags(__('Yes', 'pmc-google-amp'), true))
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Enable inline link event tracking for AMP page ?', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('By enabling this option inline link event tracking will start', 'pmc-google-amp'), true),
			self::AMP_INLINE_LINK_EVENT_TRACKING_STATUS,
			array('no', 'yes'),
			0, // first option => No.
			array(wp_strip_all_tags(__('No', 'pmc-google-amp'), true), wp_strip_all_tags(__('Yes', 'pmc-google-amp'), true))
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Enable scroll event tracking for AMP page', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('By enabling this option scroll event tracking will start', 'pmc-google-amp'), true),
			self::AMP_SCROLL_EVENT_TRACKING_STATUS,
			array('no', 'yes'),
			0, // first option => No.
			array(wp_strip_all_tags(__('No', 'pmc-google-amp'), true), wp_strip_all_tags(__('Yes', 'pmc-google-amp'), true))
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Enable event tracking on bread crumbs', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('By enabling this option Google event tracking will start for breadcrumbs', 'pmc-google-amp'), true),
			self::AMP_BREADCRUMBS_EVENT_TRACKING,
			array('no', 'yes'),
			0, // first option => No.
			array(wp_strip_all_tags(__('No', 'pmc-google-amp'), true), wp_strip_all_tags(__('Yes', 'pmc-google-amp'), true))
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Enable event tracking for Hamburger Menu', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('By enabling this option Google event tracking will start for Hamburger menu', 'pmc-google-amp'), true),
			self::AMP_SIDE_HAMBURGER_MENU_EVENT_TRACKING,
			array('no', 'yes'),
			0, // first option => No.
			array(wp_strip_all_tags(__('No', 'pmc-google-amp'), true), wp_strip_all_tags(__('Yes', 'pmc-google-amp'), true))
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('AMP side menu Position', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('AMP side menu position', 'pmc-google-amp'), true),
			self::AMP_SIDE_MENU_STATUS,
			array('disable', 'left', 'right'),
			0, // first option => Disable.
			array(wp_strip_all_tags(__('Disable', 'pmc-google-amp'), true), wp_strip_all_tags(__('Left', 'pmc-google-amp'), true), wp_strip_all_tags(__('Right', 'pmc-google-amp'), true))
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Enable "More from category" section', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('Enable/Disable "More from Category" section and it\'s options.', 'pmc-google-amp'), true),
			self::AMP_MORE_FROM_CATEGORY_STATUS,
			array('disable', 'with-image', 'without-image'),
			0, // first option => Disable.
			array(wp_strip_all_tags(__('Disable', 'pmc-google-amp'), true), wp_strip_all_tags(__('With Image', 'pmc-google-amp'), true), wp_strip_all_tags(__('Without Image', 'pmc-google-amp'), true))
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Enable "Leave Comment" button', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('Enable/Disable "Leave Comment" button', 'pmc-google-amp'), true),
			self::AMP_COMMENT_BUTTON_STATUS,
			array('yes', 'no'),
			0, // first option => Yes.
			array(wp_strip_all_tags(__('Yes', 'pmc-google-amp'), true), wp_strip_all_tags(__('No', 'pmc-google-amp'), true))
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Add AMP Skimlinks scripts', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('Add the amp-skimlinks extension to AMP pages', 'pmc-google-amp'), true),
			self::AMP_SKIMLINKS_SCRIPT,
			array('no', 'yes'),
			0, // first option => No.
			array(wp_strip_all_tags(__('No', 'pmc-google-amp'), true), wp_strip_all_tags(__('Yes', 'pmc-google-amp'), true))
		);

		$cheezcap_options[] = new \CheezCapTextOption(
			wp_strip_all_tags(__('Google AMP Publisher Image', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('Enter publisher image URL. The image should be in 600x60px resolution', 'pmc-google-amp'), true),
			self::AMP_PUBLISHER_LOGO_OPTION_NAME,
			''
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Enable AMP ad refreshing', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('Switch on/off AMP ad refreshing on AMP pages', 'pmc-google-amp'), true),
			self::AMP_AUTO_REFRESH_ADS,
			array('no', 'yes'),
			0, // first option => No.
			array(wp_strip_all_tags(__('No', 'pmc-google-amp'), true), wp_strip_all_tags(__('Yes', 'pmc-google-amp'), true))
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Enable AMP next page feature', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('Enabling this option would load two more articles on page scroll. Should be enabled only on sites using AMP plugin version above 1.2', 'pmc-google-amp'), true),
			self::AMP_ENABLE_PAGE_NEXT_FEATURE,
			['no', 'yes'],
			0, // first option => No.
			[wp_strip_all_tags(__('No', 'pmc-google-amp'), true), wp_strip_all_tags(__('Yes', 'pmc-google-amp'), true)]
		);

		//Ignoring the error - Variable $cheezcap_options is undefined.
		$cheezcap_options[] = new \CheezCapTextOption( // // phpcs:ignore
			wp_strip_all_tags(__('Google AMP JWPlayer Id', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('Enter JWPlayer Id to overwrite default', 'pmc-google-amp'), true),
			self::AMP_JWPLAYER_ID,
			''
		);

		$cheezcap_options[] = new \CheezCapDropdownOption(
			wp_strip_all_tags(__('Google AMP JWPlayer Floating', 'pmc-google-amp'), true),
			wp_strip_all_tags(__('When floating enabled, AMP JWPlayer will shrink and float/dock to the corner on scroll', 'pmc-google-amp'), true),
			self::AMP_JWPLAYER_DOCKING,
			array('dock', 'nodock'),
			0, // first option => nodock.
			array(wp_strip_all_tags(__('Floating', 'pmc-google-amp'), true), wp_strip_all_tags(__('No Floating', 'pmc-google-amp'), true))
		);

		$cheezcap_options = apply_filters('pmc_google_amp_cheezcap_options', $cheezcap_options);

		$cheezcap_groups[] = new \CheezCapGroup('AMP', 'pmc_amp_group', $cheezcap_options);

		return $cheezcap_groups;
	}

	/**
	 * To get status of "Launch Gallery" option.
	 *
	 * @return bool return true if "Launch Gallery" button option is set otherwise false.
	 */
	protected function _get_launch_gallery_status()
	{
		$launch_gallery_status = PMC_Cheezcap::get_instance()->get_option(self::AMP_LAUNCH_GALLERY_STATUS);
		return ('yes' === strtolower($launch_gallery_status));
	}

	/**
	 * To get status of "Launch Teaser" option.
	 *
	 * @return bool return true if "Gallery Teaser" button option is set otherwise false.
	 */
	protected function _is_gallery_teaser_enabled()
	{
		return ('yes' === strtolower(PMC_Cheezcap::get_instance()->get_option(self::AMP_GALLERY_TEASER_STATUS)));
	}

	/**
	 * To get status of include thumbnail in related link.
	 *
	 * @return bool return true if yes otherwise false.
	 */
	protected function _should_include_thumbnail_in_related_link()
	{
		return ('yes' === strtolower(PMC_Cheezcap::get_instance()->get_option(self::AMP_INCLUDE_THUMBNAIL_IN_RELATED_LINK_STATUS)));
	}

	/**
	 * To get status of "amp-header" position option.
	 *
	 * @return bool return true if "amp-header" position option is set Yes otherwise return false.
	 */
	protected function _should_move_header_ad_before_content()
	{
		return ('yes' === strtolower(PMC_Cheezcap::get_instance()->get_option(self::AMP_MOVE_HEADER_AD_BEFORE_CONTENT_STATUS)));
	}

	/**
	 * To get status of "Inline link event tracking"  theme options.
	 *
	 * @return bool return true if "Inline link event tracking" is set  to "Yes" otherwise false.
	 */
	protected function _get_inline_link_event_tracking_status()
	{
		return ('yes' === strtolower(PMC_Cheezcap::get_instance()->get_option(self::AMP_INLINE_LINK_EVENT_TRACKING_STATUS)));
	}

	/**
	 * To get status of "Gallery Depth Tracking" theme options.
	 *
	 * @return bool return true if "Gallery depth event tracking" is set to "Yes" otherwise false.
	 */
	public function get_gallery_depth_event_tracking_status()
	{
		return ('yes' === strtolower(PMC_Cheezcap::get_instance()->get_option(self::AMP_GALLERY_DEPTH_TRACKING_STATUS)));
	}

	/**
	 * To get status of "AMP side menu Position" option.
	 *
	 * @return boolean|string return FALSE if it disable, otherwise return position of menu.
	 */
	public function get_side_menu_position()
	{

		$status = strtolower(PMC_Cheezcap::get_instance()->get_option(self::AMP_SIDE_MENU_STATUS));

		if (!in_array($status, array('left', 'right'), true)) {
			$status = false;
		}

		return $status;
	}

	/**
	 * To get status of "Enable event tracking on bread crumbs" theme options.
	 *
	 * @return bool return TRUE if "Enable event tracking on bread crumbs" is set to "Yes" otherwise FALSE.
	 */
	protected function _is_breadcrumbs_event_tracking_enabled()
	{
		return ('yes' === strtolower(PMC_Cheezcap::get_instance()->get_option(self::AMP_BREADCRUMBS_EVENT_TRACKING)));
	}

	/**
	 * To get status of "Enable event tracking for Hamburger Menu" theme options.
	 *
	 * @return bool return TRUE if option set to "Yes" otherwise FALSE.
	 */
	protected function _is_hamburger_menu_event_tracking_enabled()
	{
		return ('yes' === strtolower(PMC_Cheezcap::get_instance()->get_option(self::AMP_SIDE_HAMBURGER_MENU_EVENT_TRACKING)));
	}

	/**
	 * To get status of "More from category" option.
	 *
	 * @return boolean boolean|string return FALSE if it disable, otherwise return option detail.
	 */
	protected function _get_more_from_category_type()
	{

		$status = strtolower(PMC_Cheezcap::get_instance()->get_option(self::AMP_MORE_FROM_CATEGORY_STATUS));

		if (!in_array($status, array('without-image', 'with-image'), true)) {
			$status = false;
		}

		return $status;
	}

	/**
	 * To get status of "Leave Comment" theme options.
	 *
	 * @return bool return true if "Leave Comment" is set  to "Yes" otherwise false.
	 */
	protected function _is_comment_button_enabled()
	{
		return ('yes' === strtolower(PMC_Cheezcap::get_instance()->get_option(self::AMP_COMMENT_BUTTON_STATUS)));
	}

	/**
	 * Validation callback function of cheez cap option.
	 *
	 * @param string $id Cheez cap option id that need to validate.
	 * @param mix    $value New value of cheez cap option.
	 *
	 * @return boolean|int New value of cheez cap option.
	 */
	public function validate_cheezcap_numeric($id, $value)
	{

		if (!empty($value) && is_numeric($value)) {
			return absint($value);
		}

		return false;
	}

	/**
	 * Filter the AMP template metadata.
	 *
	 * I.e.:
	 *
	 * <script type="application/ld+json">
	 *     ...the json metadata here...
	 * </script>
	 *
	 * @param array   $metadata An array of metadata which is injected into the top of AMP templates.
	 * @param WP_Post $post     The current $post object being displayed.
	 *
	 * @return array $metadata A possibly modified array of metadata.
	 */
	public function filter_amp_update_json_metadata($metadata = array(), $post = array())
	{

		// Updating image width in amp JSON metadata script if it is
		// less than 697 to make sure it passes google validation
		$amp_minimum_width = 697;
		$image = empty($metadata['image']) ? array() : $metadata['image'];
		if (!empty($image) && !empty($image['width'])) {
			$image_width = (int) $image['width'];
			if ($image_width < $amp_minimum_width) {
				//making width minimum 700 in json object so that it passes google amp structure validation
				$metadata['image']['width'] = 700;
			}
		}

		// PMCRS-141 - Adjust our AMP templates to use the NewsArticle schema:
		// http://schema.org/NewsArticle
		// https://developers.google.com/search/docs/data-types/articles#article_types
		//
		// This allows our articles to be indexed by Google Realtime Indexing.
		// See pmc-plugins/pmc-google-breaking-news and 'Settings > Google Indexing Status'
		$metadata['@type'] = 'NewsArticle';

		$post_publisher_img = PMC_Cheezcap::get_instance()->get_option(self::AMP_PUBLISHER_LOGO_OPTION_NAME, false, 'esc_url_raw');

		$width  = 60;
		$height = 60;

		if (!empty($post_publisher_img)) {

			$url    = $post_publisher_img;
			$width  = 600;
		} else if (function_exists('blavatar_url')) {

			$url    = blavatar_url(site_url(), 'img', 60);
		} else {

			$url    = get_site_icon_url('60');
		}

		if (!empty($url)) {

			$metadata['publisher']['logo'] = array(
				'@type'  => 'ImageObject',
				'url'    => $url,
				'width'  => $width,
				'height' => $height,
			);
		}

		// PMCRS-141 - Add an article descipriotn field to the metadata
		// This is another required param for article ingestion with
		// Google's Realtime Indexing/Breaking News. The breaking news
		// description needs to be 160 characters, as such, we use the
		// SEO Description because it's limited to 140 characters, and
		// won't require us to truncate the text.
		$description = apply_filters('pmc_google_amp_metadata_description', get_post_meta($post->ID, 'mt_seo_description', true));

		if (!empty($description)) {
			$metadata['description'] = $description;
		}

		$metadata['datePublished'] = date('c', strtotime($post->post_date_gmt));
		$metadata['dateModified']  = date('c', strtotime($post->post_modified_gmt));

		return $metadata;
	}

	/**
	 * Method to get Google analytics custom dimensions
	 *
	 * @return array
	 */
	public function get_ga_custom_dimensions()
	{

		return PMC_Google_Universal_Analytics::get_instance()->get_mapped_dimensions();
	}

	/**
	 * Add sticky Ad to the amp page if it is enabled and set.
	 */
	public function get_sticky_ad()
	{
		$pmc_cheezcap = PMC_Cheezcap::get_instance();
		$amp_sticky_ad = $pmc_cheezcap->get_option(self::AMP_STICKY_AD_STATUS);
		if ('yes' === $amp_sticky_ad) {
			$this->get_ad('amp-adhesion', true);
		}
	}

	/**
	 * Convert img tag to amp image tag using AMP_Img_Santizer
	 *
	 * @since 2017-07-05 CDWE-446
	 *
	 * @param string $content_html HTML content.
	 *
	 * @return string $content_html Converted img tag to amp image tag.
	 */
	protected function _get_image_html($content_html = '')
	{

		if ($this->_is_amp()) {

			// VIP: Stopping fatal errors "Uncaught Error: Class 'AMP_DOM_Utils' not found"
			if (!class_exists('AMP_DOM_Utils')) {
				return false;
			}

			$dom = AMP_DOM_Utils::get_dom_from_content($content_html);
			$amp_image_sanitizer = new AMP_Img_Sanitizer($dom);
			$amp_image_sanitizer->sanitize();
			$content_html = AMP_DOM_Utils::get_content_from_dom($dom);
		}

		return $content_html;
	}

	/**
	 * Fetch a given gallery's teaser image
	 *
	 * Teaser image = the featured image if there is one,
	 * otherwise, the first gallery image
	 *
	 * @param int $gallery_id The gallery attachment post ID.
	 *
	 * @return bool|string|int False on failure, Interger teaser image ID or string image URL on success.
	 */
	protected function _get_gallery_teaser_image($gallery_id = 0)
	{

		if (empty($gallery_id)) {
			return false;
		}

		$gallery_items = PMC::get_gallery_items($gallery_id);

		if (empty($gallery_items) || !is_array($gallery_items)) {
			return false;
		}

		$first_gallery_attachment_id = current($gallery_items);

		$teaser_attachment_id = $gallery_teaser_attachment = $gallery_teaser_attachment_url = false;

		// Does this gallery have a featured image?
		$featured_attachment_id = get_post_thumbnail_id($gallery_id);

		// If so, use the featured image as the teaser..
		if (!empty($featured_attachment_id)) {
			$teaser_attachment_id = $featured_attachment_id;
		} else {
			// ..if not, use the first gallery item
			if (!empty($first_gallery_attachment_id)) {
				$teaser_attachment_id = $first_gallery_attachment_id;
			}
		}

		if (!empty($teaser_attachment_id)) {
			return $teaser_attachment_id;
		}

		return false;
	}

	/**
	 * To add gallery teaser in post content.
	 *
	 * @since  2017-07-05 CDWE-446
	 *
	 * @hook   the_content
	 *
	 * @param  string $content Post Content.
	 *
	 * @return string Post Content.
	 */
	public function add_gallery_teaser($content = '')
	{

		// If it is not amp page then do not proceed.
		if (!$this->_is_amp()) {
			return $content;
		}

		// Allow theme to decide on which post type gallery teaser should be display.
		$allow_post_types = apply_filters('pmc_google_amp_allow_gallery_teaser_post_types', array('post'));

		if (!is_singular($allow_post_types)) {
			return $content;
		}

		$post_id = get_the_id();

		if (!PMC::has_linked_gallery($post_id)) {
			return $content;
		}

		$gallery = PMC::get_linked_gallery($post_id, true);

		if (empty($gallery->url) || empty($gallery->items)) {
			return $content;
		}

		$linked_gallery = '';

		if (!has_post_thumbnail()) {

			$gallery_teaser_image = $this->_get_gallery_teaser_image($gallery->id);

			if (!empty($gallery_teaser_image)) {
				$size = apply_filters('post_thumbnail_size', 'post-thumbnail');
				$allowed_html = array(
					'img' => array(
						'width'  => array(),
						'height' => array(),
						'src'    => array(),
						'class'  => array(),
						'alt'    => array(),
						'srcset' => array(),
						'sizes'  => array(),
					),
				);

				/**
				 * @TODO This need to change after deployment of CDWE-372.
				 * CDWE-372 is for add image credit for featured image.
				 * which in not implemented here.
				 * and after deployment of CDWE-372 this most likely move to
				 * '$this->amp_add_featured_image()'
				 */
				$linked_gallery = sprintf('<p class="featured-image">%s</p>', wp_kses(wp_get_attachment_image($gallery_teaser_image, $size), $allowed_html));
			}
		}

		$size = apply_filters('pmc_google_amp_gallery_teaser_thumbnail_size', 'thumbnail');

		$linked_gallery .= PMC::render_template(
			sprintf('%s/templates/gallery-teaser.php', untrailingslashit(dirname(__DIR__))),
			array(
				'gallery'              => $gallery,
				'image_size'           => $size,
				'num_items_to_display' => 4,
			)
		);

		$linked_gallery = $this->_get_image_html($linked_gallery);

		return $linked_gallery . $content;
	}

	/**
	 * Function is used to add gallery link on article in amp mode.
	 *
	 * @ticket	#CDWE-348
	 * @param	string $content Post content.
	 * @return  string $content Post content.
	 */
	public function amp_add_gallery_links($content)
	{

		// If it is not amp page then do not proceed.
		if (!function_exists('is_amp_endpoint') || !is_amp_endpoint()) {
			return $content;
		}

		$linked_gallery = get_post_meta(get_the_ID(), 'pmc-gallery-linked-gallery', true);

		if (empty($linked_gallery)) {
			return $content;
		}

		$linked_gallery = json_decode($linked_gallery, true);
		$gallery_id = (!empty($linked_gallery['id']) && is_numeric($linked_gallery['id'])) ? intval($linked_gallery['id']) : false;

		if ($gallery_id) {

			if (defined('ENABLE_AMP_GALLERY') && true === ENABLE_AMP_GALLERY && function_exists('amp_get_permalink')) {
				$amp_link = amp_get_permalink($gallery_id);
			} else {
				$amp_link = get_permalink($gallery_id);
			}

			$amp_link = apply_filters('pmc_google_amp_gallery_button_link', $amp_link, $gallery_id, get_the_ID());

			$template_path = untrailingslashit(PMC_GOOGLE_AMP_ROOT) . '/templates/amp-launch-gallery-button.php';
			$html = \PMC::render_template($template_path, array(
				'amp_link' => $amp_link,
			));

			$content = wp_kses_post($html) . $content;
		}

		return $content;
	}

	/**
	 * We use mxcdn to import fontawesome because only whitelisted providers are allowed.
	 * See https://www.ampproject.org/docs/reference/spec
	 */
	public function amp_post_template_head()
	{
		echo '<meta name="amp-google-client-id-api" content="googleanalytics">';
		echo '<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" >';
		echo '<script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>';
		// NOTE: in newer version of amp plugin, builtin extension are autoload, no need to manually added here
	}

	/**
	 * Add amp experiment meta.
	 * This meta added to enable experimental features on site. By default, AMP do not allows experimental features to activate on site.
	 *
	 * @return void
	 */
	public function add_amp_experiment_meta(): void
	{
		if (is_single() && (true === $this->is_amp_next_page_feature_enabled())) {
			echo '<meta name="amp-experiments-opt-in" content="amp-next-page">';
		}
	}

	/**
	 * Function to return inline link event tracking for AMP pages.
	 *
	 * @ticket CDWE-357
	 *
	 * @param array $events List of events.
	 *
	 * @return array List of event.
	 */
	protected function _get_inline_link_event_tracking($events = array())
	{

		if (!$this->_get_inline_link_event_tracking_status()) {
			return $events;
		}

		$post = get_post();
		$post_content = apply_filters('the_content', $post->post_content);

		if (empty($post_content)) {
			return $events;
		}

		$inline_links = $this->_extract_urls_and_text($post_content);

		if (empty($inline_links) || !is_array($inline_links)) {
			return $events;
		}

		if (empty($events) || !is_array($events)) {
			$events = array();
		}

		$content_selector = apply_filters('pmc_google_amp_single_post_content_selector', '.amp-fn-content');

		foreach ($inline_links as $link) {

			if (empty($link['url']) || empty($link['text'])) {
				continue;
			}

			if (false === filter_var($link['url'], FILTER_VALIDATE_URL)) {
				// Bad URL, move on to next one.
				continue;
			}

			$events[] = array(
				'on'       => 'click',
				'category' => 'amp',
				'selector' => sprintf('%s a[href="%s"]', esc_js($content_selector), esc_url_raw($link['url'])),
				'label'    => sprintf('inline-hyperlinks_%s_%s', sanitize_text_field($link['text']), esc_url_raw($link['url'])),
			);
		}

		return $events;
	}

	/**
	 * Function to extract urls and corresponding text for post content.
	 *
	 * @param string $content post content which may have inline links.
	 *
	 * @return array List of inline link detail.
	 */
	protected function _extract_urls_and_text($content)
	{

		if (empty($content)) {
			return array();
		}

		$inline_links = array();

		/**
		 * Note : If you modify this reguler expression.
		 * than you must run test cases.
		 */
		$reg_exp = '/\<[aA][\s]?([^>]+)href\=(["\']?)((?:([\w-]+:)?\/\/?)[^\s()<>]+[.](?:\([\w\d]+\)|(?:[^`!()\[\]{}:\'".,<>Â«Â»â€œâ€â€˜â€™\s]|(?:[:]\d+)?\/?)+))\2(.*)[\s"\']?>(.*)\<\/[aA]\>/Uis';

		preg_match_all($reg_exp, $content, $post_links);

		/**
		 * If any of empty than make both empty.
		 * because it will lead to invalid data for GA Event tracking.
		 */
		if (empty($post_links[3]) || empty($post_links[6])) {
			$post_links[3] = array();
			$post_links[6] = array();
		}

		$links  = array_map('html_entity_decode', $post_links[3]);
		$titles = array_map('html_entity_decode', $post_links[6]);

		foreach ($links as $index => $url) {

			$inline_links[] = array(
				'url'  => $url,
				'text' => $titles[$index],
			);
		}

		return $inline_links;
	}

	/**
	 * To get status of "Scroll event tracking"  theme options.
	 *
	 * @version 2017-06-20 CDWE-421
	 *
	 * @return bool return true if 'Scroll event tracking' is set  to 'Yes' else false.
	 */
	protected function _get_scroll_event_tracking_status()
	{

		return ('yes' === strtolower(PMC_Cheezcap::get_instance()->get_option(self::AMP_SCROLL_EVENT_TRACKING_STATUS)));
	}

	/**
	 * Returns events payload for tracking scroll on amp page
	 *
	 * @version 2017-06-20 CDWE-421
	 *
	 * @param array $events List of events.
	 *
	 * @return array
	 */
	protected function _get_page_scroll_event_tracking($events = array())
	{

		if (!$this->_get_scroll_event_tracking_status()) {
			return $events;
		}

		if (empty($events) || !is_array($events)) {
			$events = array();
		}

		return array_merge(

			$events,

			array(

				array(
					'on'                  => 'scroll',
					'scrollSpec'          => array(
						'verticalBoundaries'  => array(10),
					),
					'category'            => 'amp-article-page',
					'label'               => 'headline-view',
				),
				array(
					'on'                  => 'scroll',
					'scrollSpec'          => array(
						'verticalBoundaries'  => array(25),
					),
					'category'            => 'amp-article-page',
					'label'               => 'top-post-view',
				),
				array(
					'on'                  => 'scroll',
					'scrollSpec'          => array(
						'verticalBoundaries'  => array(50),
					),
					'category'            => 'amp-article-page',
					'label'               => 'mid-post-view',
				),
				array(
					'on'                  => 'scroll',
					'scrollSpec'          => array(
						'verticalBoundaries'  => array(75),
					),
					'category'            => 'amp-article-page',
					'label'               => 'bottom-post-view',
				),
				array(
					'on'                  => 'scroll',
					'scrollSpec'          => array(
						'verticalBoundaries'  => array(90),
					),
					'category'            => 'amp-article-page',
					'label'               => 'footer-view',
				),
				array(
					'on'                  => 'scroll',
					'scrollSpec'          => array(
						'verticalBoundaries'  => array(50),
					),
					'category'            => 'amp-article-page',
					'label'               => 'content-consumed',
				),

			)

		);
	}

	/**
	 * Returns various ga events
	 *
	 * @hook pmc_google_amp_ga_event_tracking
	 *
	 * @version 2017-06-21 CDWE-421
	 *
	 * @param array $events List of events.
	 *
	 * @return array
	 */
	public function add_ga_tracking($events = array())
	{

		if (empty($events) || !is_array($events)) {
			$events = array();
		}

		// Gallery Teaser Event tracking.
		$events[] = array(
			'on'       => 'click',
			'label'    => 'gallery_thumbs',
			'selector' => '.gallery-image-section .gallery-thumbnails a',
			'category' => 'amp',
		);

		$events[] = array(
			'on'       => 'click',
			'category' => 'amp',
			'selector' => '.amp-category-posts-container ul.list li a .post-title',
			'label'    => 'related-links_text_after',
		);

		$events[] = array(
			'on'       => 'click',
			'category' => 'amp',
			'selector' => '.amp-category-posts-container ul.list li a .post-image',
			'label'    => 'related-links_thumbnail_after',
		);

		$events = $this->_get_inline_link_event_tracking($events);

		$events = $this->_get_page_scroll_event_tracking($events);

		$events = $this->_should_add_bradcrumbs_event_tracking($events);

		$events = $this->_should_add_hamburger_menu_event_tracking($events);

		return $events;
	}

	/**
	 * Add more posts from category.
	 *
	 * @hook amp_post_template_footer
	 *
	 * @return void
	 */
	public function more_posts_from_category()
	{

		$status = $this->_get_more_from_category_type();

		if (empty($status)) {
			return;
		}

		$categories = get_the_category();
		$parent_category = false;

		if (empty($categories) || !is_array($categories)) {
			return;
		}

		// Find parent category.
		foreach ($categories as $category) {

			if (empty($categories->parent)) {
				$parent_category = $category;
				continue;
			}
		}

		if (empty($parent_category->term_id)) {
			return;
		}

		$args = array(
			'posts_per_page'   => 5,
			'category'         => $parent_category->term_id,
			'suppress_filters' => false,
		);

		ksort($args);
		$cache_key = md5(serialize($args));
		$expiry_time = (30 * MINUTE_IN_SECONDS);

		$pmc_cache = new PMC_Cache($cache_key);
		$posts = $pmc_cache->expires_in($expiry_time)
			->updates_with('get_posts', array($args))
			->get();

		if (empty($posts) || !is_array($posts)) {
			return;
		}

		if (1 === count($posts) && get_the_ID() === $posts[0]->ID) {
			return;
		}

		$path = sprintf('%s/templates/more-posts.php', untrailingslashit(dirname(__DIR__)));
		$size = apply_filters('pmc_google_amp_more_from_category_thumbnail_size', 'post-thumbnail');

		$content = PMC::render_template($path, array(
			'posts'	 => $posts,
			'term'	 => $parent_category,
			'status' => $status,
			'size'   => $size,
		));

		// It contains AMP content.
		echo $this->_get_image_html($content);
	}

	/**
	 * To register navigation menus.
	 *
	 * @hook after_setup_theme
	 *
	 * @since 2017-07-03 CDWE-446
	 *
	 * @return void
	 */
	public function register_nav_menus()
	{

		register_nav_menu('amp_side_menu', 'AMP article side menu.');
	}

	/**
	 * To add side menu.
	 *
	 * @hook pmc_amp_content_before_header
	 *
	 * @since 2017-07-03 CDWE-446
	 *
	 * @return void
	 */
	public function pmc_amp_content_before_header()
	{

		$side_menu_status = $this->get_side_menu_position();

		if (!empty($side_menu_status)) {

			// Add side menu.
			$menu_path = sprintf('%s/templates/side-menu.php', untrailingslashit(PMC_GOOGLE_AMP_ROOT));

			echo PMC::render_template($menu_path, array(
				'location' => $side_menu_status,
			));
		}
	}

	/**
	 * To remove header add from top header.
	 * And Show that just before post content.
	 *
	 * @since 2017-07-06 CDWE-446
	 *
	 * @return void
	 */
	public function move_header_ad_before_content()
	{

		if ($this->_should_move_header_ad_before_content()) {

			// Remove header add from top header.
			remove_action('pmc_amp_content_after_header', array($this, 'get_ad'), 10);
			remove_action('pmc_amp_content_after_header', array($this, 'get_sticky_ad'), 10);

			// Show header ad just before post content.
			add_action('pmc_amp_before_post_content', array($this, 'show_header_ad_before_content'));
		}
	}

	/**
	 * Show header ad just before post content.
	 *
	 * @since 2017-07-06 CDWE-446
	 *
	 * @return void
	 */
	public function show_header_ad_before_content()
	{

		$this->get_ad('amp-header', true);

		$this->get_sticky_ad();
	}

	/**
	 * To overwrite HTML markup of Related link.
	 *
	 * @param string $markup HTML markup of related link.
	 * @param array  $attrs Shortcode attributes.
	 * @param string $content Shortcode content.
	 * @param string $type_slug Type of related link.
	 *
	 * @return string HTML markup of related link.
	 */
	public function get_related_link_markup($markup, $attrs, $content, $type_slug)
	{

		$include_thumbnail = $this->_should_include_thumbnail_in_related_link();
		$image_url = '';

		if ($include_thumbnail && !empty($attrs['href'])) {

			$post_id = url_to_postid($attrs['href']);

			if (!empty($post_id) && has_post_thumbnail($post_id)) {

				$size = apply_filters('pmc_google_amp_related_article_thumbnail_size', 'thumbnail');
				$image_url = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), $size);
				$image_url = $image_url[0];
			}
		}

		$include_thumbnail = ($include_thumbnail && !empty($image_url));

		$markup = PMC::render_template(sprintf('%s/templates/pmc-related-link.php', untrailingslashit(PMC_GOOGLE_AMP_ROOT)), array(
			'attrs'             => $attrs,
			'content'           => $content,
			'type_slug'         => $type_slug,
			'include_thumbnail' => $include_thumbnail,
			'image_url'         => $image_url,
		));

		$markup = $this->_get_image_html($markup);

		return $markup;
	}

	/**
	 * To get breadcrumbs elements.
	 *
	 * @since   2017-07-28 - Dhaval Parekh - CDWE-446
	 * @version 2017-08-16 - Dhaval Parekh - CDWE-588
	 *
	 * @return array Breadcrumbs elements.
	 */
	protected function _get_breadcrumbs()
	{

		$breadcrumbs = apply_filters('pmc_google_amp_get_breadcrumbs', array());

		/**
		 * To get number of allowed brumbs in AMP page.
		 * FALSE will show all breadcrumbs.
		 * Default is 3.
		 *
		 * @author Dhaval Parekh <dhaval.parekh@rtcamp.com>
		 *
		 * @since 2017-08-24 CDWE-588
		 */
		$allow_breadcrumbs_no = apply_filters('pmc_google_amp_allow_no_of_breadcrumbs', 3);

		$breadcrumbs = array_slice($breadcrumbs, 0, $allow_breadcrumbs_no);

		if (!empty($breadcrumbs) && is_array($breadcrumbs)) {

			foreach ($breadcrumbs as $index => $crumb) {

				if (empty($crumb['label'])) {
					continue;
				}

				$breadcrumbs[$index]['class'] = str_replace(' ', '-', trim(strtolower($crumb['label'])));
				$breadcrumbs[$index]['class'] = str_replace(array('"', '\''), '', $breadcrumbs[$index]['class']);
			}
		}

		return $breadcrumbs;
	}

	/**
	 * To render breadcrumb after header.
	 *
	 * @hook   pmc_amp_content_after_header
	 *
	 * @since  2017-07-27
	 *
	 * @return void
	 */
	public function render_breadcrumbs()
	{

		$breadcrumbs = $this->_get_breadcrumbs();

		$template_path = sprintf('%s/templates/breadcrumbs.php', untrailingslashit(dirname(__DIR__)));

		if (!empty($breadcrumbs)) {

			echo wp_kses_post(PMC::render_template($template_path, array(
				'breadcrumbs' => $breadcrumbs,
			)));
		}
	}

	/**
	 * To add event tracking elements for breadcrumbs.
	 *
	 * @param array $events Event tacking elements.
	 *
	 * @return array Event tacking elements.
	 */
	protected function _should_add_bradcrumbs_event_tracking($events = array())
	{

		if (!$this->_is_breadcrumbs_event_tracking_enabled()) {
			return $events;
		}

		if (empty($events) || !is_array($events)) {
			$events = array();
		}

		$breadcrumbs = $this->_get_breadcrumbs();

		foreach ($breadcrumbs as $crumb) {
			$events[] = array(
				'on'       => 'click',
				'category' => 'amp',
				'selector' => sprintf('.article-breadcrumb-container .article-header__breadcrumbs .%s a', esc_html($crumb['class'])),
				'label'    => sprintf('breadcrumbs_%s_%s', esc_html($crumb['class']), esc_url($crumb['href'])),
			);
		}

		return $events;
	}

	/**
	 * To add event tracking elements for hamburger menu.
	 *
	 * @param array $events Event tacking elements.
	 *
	 * @return array Event tacking elements.
	 */
	protected function _should_add_hamburger_menu_event_tracking($events = array())
	{

		if (!$this->_is_hamburger_menu_event_tracking_enabled()) {
			return $events;
		}

		if (empty($events) || !is_array($events)) {
			$events = array();
		}

		$theme_locations = get_nav_menu_locations();
		$menu_id = false;

		if (!empty($theme_locations['amp_side_menu']) && is_numeric($theme_locations['amp_side_menu'])) {
			$menu_id = absint($theme_locations['amp_side_menu']);
		} else {
			return $events;
		}

		$menu_items = wp_get_nav_menu_items($menu_id);

		foreach ($menu_items as $item) {

			$label = str_replace(' ', '-', trim(strtolower($item->title)));
			$label = substr($label, 0, 30);

			$events[] = array(
				'on'       => 'click',
				'category' => 'amp',
				'selector' => sprintf('#amp_side_menu .menu #menu-item-%d a', esc_html($item->ID)),
				'label'    => sprintf('hamburger_%s_%s', wp_kses($label, array()), esc_url($item->url)),
			);
		}

		return $events;
	}

	/**
	 * Filter and update rtc config to add vendor bidder configurations.
	 *
	 * @param array  $rtc_config vendor config array.
	 * @param string $ad_slot    ad slot info.
	 *
	 * @return array $rtc_config
	 */
	public function update_amp_ad_rtc_config($rtc_config, $ad_slot)
	{

		$rtc_config['vendors'] = [
			'aps' => [
				'PUB_ID' => '3157',
				'PARAMS' => [
					'amp' => '1',
				],
			],
		];

		$this->ix_amp_ad_slot_ids = apply_filters('pmc_google_amp_ix_ad_slot_ids', $this->ix_amp_ad_slot_ids);

		if (empty($rtc_config) || !is_array($rtc_config)) {
			$rtc_config = [];
		}

		if (!empty($ad_slot) && array_key_exists($ad_slot, $this->ix_amp_ad_slot_ids)) {
			$rtc_config['vendors']['indexexchange']['SITE_ID'] = $this->ix_amp_ad_slot_ids[$ad_slot];
		}

		if (true === apply_filters('pmc_amp_permutive_analytics', false)) {
			$rtc_config['urls'][] = 'https://pmc.amp.permutive.com/rtc?type=doubleclick';
		}

		return $rtc_config;
	}

	/**
	 * Helper function to format ad sizes.
	 *
	 * @param array $meta ad slot data
	 *
	 * @return mixed
	 */
	public function get_formatted_ad_sizes($meta = [])
	{
		$formatted_sizes = [];

		if (empty($meta['ad-width'])) {
			return '';
		}

		$ad_sizes = json_decode(sprintf('[%s]', $meta['ad-width']));
		if (!empty($ad_sizes) && is_array($ad_sizes) && 1 < count($ad_sizes)) {
			foreach ($ad_sizes as $ad_size) {
				if (is_array($ad_size)) {
					$formatted_sizes[] = implode('x', $ad_size);
				}
			}
		}

		if (!empty($formatted_sizes) && is_array($formatted_sizes)) {
			return implode(',', $formatted_sizes);
		} else {
			return '';
		}
	}

	/**
	 * To get AMP markup of Jwplayer.
	 *
	 * @param array $atts shortcode attributes.
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	public static function get_amp_jwplayer($atts)
	{

		/**
		 * Regex Reference: https://regex101.com/r/BllPjM/1/
		 */
		$regex   = '/(?P<media>[0-9a-z]{8})(?:[-_])?(?P<player>[0-9a-z]{8})?/i';
		$matches = [];
		$attr    = [
			'video_hash'  => '',
			'player_hash' => '',
		];

		if (!empty($atts[0])) {
			if (!empty($atts[1])) { // Both video and player id is set.
				$attr = [
					'video_hash'  => $atts[0],
					'player_hash' => $atts[1],
				];
			} elseif (preg_match($regex, $atts[0], $matches)) { // Only media id is set or need to separate out both ids.
				$attr = [
					'video_hash'  => (!empty($matches['media'])) ? $matches['media'] : '',
					'player_hash' => (!empty($matches['player'])) ? $matches['player'] : get_option('jwplayer_player', ''),
				];
			}
		}

		$amp_player_id       = PMC_Cheezcap::get_instance()->get_option(self::AMP_JWPLAYER_ID);
		$attr['player_hash'] = (!empty($amp_player_id)) ? $amp_player_id : $attr['player_hash'];

		$attr = apply_filters('pmc_google_amp_jwplayer_override', $attr, $atts);

		if (empty($attr) || !is_array($attr)) {
			return '';
		}

		return PMC::render_template(
			PMC_GOOGLE_AMP_ROOT . '/templates/jw-player.php',
			$attr
		);
	}

	/**
	 * To get status of "amp-skimlinks extension" theme options.
	 *
	 * @return bool return TRUE if option set to "Yes" otherwise FALSE.
	 */
	protected function _is_skimlinks_enabled()
	{
		return ('yes' === strtolower(PMC_Cheezcap::get_instance()->get_option(self::AMP_SKIMLINKS_SCRIPT)));
	}

	/**
	 * Add skimlinks code to amp
	 */
	public function action_amp_add_skimlinks_code()
	{

		$publisher_code = apply_filters('pmc_google_amp_skimlinks_site_id', false);

		if ($this->_is_amp() && $this->_is_skimlinks_enabled() && !empty($publisher_code)) {
			printf('<amp-skimlinks layout="nodisplay" publisher-code="%s" ></amp-skimlinks>', esc_attr($publisher_code));
		}
	}


	/**
	 *  Add Permutive Analytics tags
	 *
	 * @param $analytics array
	 *
	 * @return array
	 */
	public function add_permutive_analytics($analytics): array
	{

		if (true === apply_filters('pmc_amp_permutive_analytics', false)) {

			$permutive = [];
			$ga_data   = PMC_Google_Universal_Analytics::get_instance()->get_custom_dimensions();

			$permutive['type']       = 'permutive';
			$permutive['attributes'] = [];

			$permutive['config_data']['vars'] = [
				'namespace' => 'pmc',
				'key'       => '2aed5ae2-5875-450b-9e5e-34ac932123da',
			];

			$categories = (!empty($ga_data['category']) && is_array($ga_data['category'])) ? implode(',', $ga_data['category']) : '';
			$page_type  = (!empty($ga_data['page-type'])) ? $ga_data['page-type'] : '';
			$title      = (!empty($ga_data['id'])) ? get_the_title($ga_data['id']) : '';
			$id         = (!empty($ga_data['id'])) ? (string) $ga_data['id'] : '';
			$author     = (!empty($ga_data['author'])) ? $ga_data['author'] : '';
			$section    = (!empty($ga_data['primary-category'])) ? $ga_data['primary-category'] : '';
			$keywords   = (!empty($ga_data['tag'])) ? implode(',', $ga_data['tag']) : '';
			$pub_date   = (!empty($id)) ? get_post_time('c', true, $ga_data['id']) : '';
			$verticals  = (!empty($ga_data['vertical']) && is_array($ga_data['vertical'])) ? implode(',', $ga_data['vertical']) : '';
			$page_data  = (!empty($categories)) ? $categories : '';
			$page_data  = (!empty($verticals)) ? $page_data . ',' . $verticals : '';
			$page_data  = (!empty($keywords)) ? $page_data . ',' . $keywords : '';

			$permutive['config_data']['extraUrlParams'] = [
				'properties.article.categories!list[string]' => $categories,
				'properties.type'                          => $page_type,
				'properties.article.title'                 => $title,
				'properties.article.id'                    => $id,
				'properties.article.authors!list[string]'  => $author,
				'properties.article.section'               => $section,
				'properties.article.keywords!list[string]' => $keywords,
				'properties.article.publishedAt'           => $pub_date,
				'properties.article.verticals!list[string]' => $verticals,
				'properties.article.pageLevelData!list[string]' => $page_data,
				'properties.article.watson.taxonomy'       => '$alchemy_taxonomy',
				'properties.article.watson.keywords'       => '$alchemy_keywords',
				'properties.article.watson.entities'       => '$alchemy_entities',
				'properties.article.watson.concepts'       => '$alchemy_concepts',
			];

			$analytics['permutive'] = $permutive;
		}

		return $analytics;
	}

	/**
	 *  Render Permutive amp iframe tag
	 *
	 * @return void
	 *
	 */
	public function add_permutive_section_code(): void
	{

		if (true === apply_filters('pmc_amp_permutive_analytics', false)) {
			$template = PMC_GOOGLE_AMP_ROOT . '/templates/permutive.php';
			\PMC::render_template($template, [], true);
		}
	}

	/**
	 * Removing newly added inline style sheets in 1.4.0 - Not required in reader mode
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function amp_remove_inline_stylesheets($data = [])
	{

		$data['post_amp_stylesheets'] = [];
		return $data;
	}

	/**
	 * Gets status of amp next page feature from theme settings.
	 *
	 * @return bool return TRUE if option set to "Yes" otherwise FALSE.
	 */
	public function is_amp_next_page_feature_enabled(): bool
	{
		return ('yes' === strtolower(PMC_Cheezcap::get_instance()->get_option(self::AMP_ENABLE_PAGE_NEXT_FEATURE)));
	}

	/**
	 * Get AMP next page data.
	 *
	 * @return array
	 */
	public function get_next_page_data(): array
	{

		$next_page_data = apply_filters('pmc_amp_next_page_data', []);

		if (is_array($next_page_data) && !empty($next_page_data)) {
			return $next_page_data;
		}

		$next_pages = [];

		$args = [
			'post_status'            => 'publish',
			'posts_per_page'         => (self::AMP_NEXT_FEATURE_PAGE_COUNT + 1),
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
			'order'                  => 'DESC',
			'orderby'                => 'date',
			'post_type'              => get_post_type(),
			'suppress_filters'       => false,
		];

		ksort($args);
		$cache_key   = md5(maybe_serialize($args));
		$expiry_time = (15 * MINUTE_IN_SECONDS);

		$pmc_cache = new \PMC_Cache($cache_key);

		$next_page_post_ids = $pmc_cache->expires_in($expiry_time)->updates_with('get_posts', [$args])->get();

		if (is_array($next_page_post_ids) && !empty($next_page_post_ids)) {

			$current_post_id = get_the_ID();

			foreach ($next_page_post_ids as $next_page_post_id) {
				if ($current_post_id !== $next_page_post_id) {

					$thumbnail_url         = get_the_post_thumbnail_url($next_page_post_id);
					$default_thumbnail_url = sprintf('%s/assets/images/placeholder-thumbnail.png', untrailingslashit(PMC_GOOGLE_AMP_URL));

					// PMCP-2576 follow up: Remove else when AMP is updated
					if (\PMC\Google_Amp\Plugin::get_instance()->is_at_least_version('2.0.4')) {
						$next_pages[] = [
							'title' => html_entity_decode(get_the_title($next_page_post_id)),
							'image' => (!empty($thumbnail_url)) ? $thumbnail_url : $default_thumbnail_url,
							'url'   => amp_get_permalink($next_page_post_id),
						];
					} else {
						$next_pages[] = [
							'title'  => html_entity_decode(get_the_title($next_page_post_id)),
							'image'  => (!empty($thumbnail_url)) ? $thumbnail_url : $default_thumbnail_url,
							'ampUrl' => amp_get_permalink($next_page_post_id),
						];
					}
				}
			}
		}

		// Remove last item if current post was not found in the results.
		if (count($next_pages) > self::AMP_NEXT_FEATURE_PAGE_COUNT) {
			array_pop($next_pages);
		}

		// PMCP-2576 follow up: Remove else when AMP is updated
		if (\PMC\Google_Amp\Plugin::get_instance()->is_at_least_version('2.0.4')) {
			$next_page_data = [
				'pages' => $next_pages,
			];
		} else {
			$next_page_data = [
				'pages'         => $next_pages,
				'hideSelectors' => ['.amp-wp-title-bar', '.copyright', '.amp-footer-logo'],
			];
		}

		return (!empty($next_pages)) ? $next_page_data : [];
	}
}

// EOF
