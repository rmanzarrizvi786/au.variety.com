<?php
wpcom_vip_load_plugin('pmc-global-functions', 'pmc-plugins');
pmc_load_plugin('custom-metadata');
pmc_load_plugin('multiple-post-thumbnails');

use PMC\Post_Options\API as Post_Options_API;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Class PMC_Featured_Video_Override
 * Functionality to save youtube video url[or ndn Id] for the post.
 * This will be used to show video intead of featured image in the river/single post.
 */
class PMC_Featured_Video_Override
{

	use Singleton;

	const KEY      = "_pmc_featured_video_override";
	const META_KEY = "_pmc_featured_video_override_data";
	const META_KEY_END = "_pmc_featured_video_override_data_end";
	const OPTION_NAME = "render-featured-video-in-river";
	const OPTION_LABEL = "Render Featured Video In River";
	const CACHE_GROUP = '_render_featured_video_in_River';
	const FEATURED_VIDEO_LIST_OPTION_NAME = 'PMC_Featured_Video_List_In_River';

	protected function __construct()
	{

		add_filter('pmc_global_cheezcap_options', array($this, 'featured_video_cheezcap_groups'));
		add_filter('admin_init', array($this, 'filter_admin_init'));
		add_action('after_setup_theme', array($this, 'after_theme_setup'));

		add_action('pmc_larva_do_featured_video_override', [$this, 'do_larva_output']);
	}

	public function filter_admin_init()
	{
		if (is_admin()) {
			add_action('custom_metadata_manager_init_metadata', array($this, 'create'));
			$featured_video_in_blogroll = \PMC_Cheezcap::get_instance()->get_option('pmc_enable_featured_video_in_blogroll');

			if (isset($featured_video_in_blogroll) && 'yes' === $featured_video_in_blogroll) {
				add_action('admin_init', array($this, 'add_post_options'));
				add_action('save_post', array($this, 'save_post'));
			}
		}
	}

	public static function after_theme_setup()
	{
		/**
		 * define image sizes for flv shortcode
		 */
		add_image_size('flv-shortcode-image', 300, 208, true);
		add_image_size('flv-shortcode-image-lrg', 600, 338, true);

		require_once WP_PLUGIN_DIR . '/multiple-post-thumbnails/multi-post-thumbnails.php';
		new MultiPostThumbnails(array(
			'label' => 'FLV Shortcode image',
			'id'		=> 'flv_shortcode_image',
		));
	}

	/**
	 * @param $post_id
	 * @since 01-12-2017 Adaeze Esiobu PMCBA-59 HL - Play video in river
	 * Upon post save, check the featured video in river option. if it is set
	 * keep a running list of maximum of 10 post IDs that have been selected to
	 * play video in the river.
	 */
	public static function save_post($post_id)
	{
		if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || (defined('DOING_AJAX') && DOING_AJAX)
			|| !current_user_can('edit_post', $post_id)
		) {
			return;
		}
		// get the list of postids with option to play featured video in the river
		$featured_videos_in_river = pmc_get_option(PMC_Featured_Video_Override::FEATURED_VIDEO_LIST_OPTION_NAME, PMC_Featured_Video_Override::CACHE_GROUP);

		$featured_video_in_river_list = array();

		if (!empty($featured_videos_in_river)) {

			$featured_video_in_river_list = explode(',', $featured_videos_in_river);
		}

		//if the current post being saved, is on the list of postids that can play a video in the river, remove it.
		if (($key = array_search($post_id, $featured_video_in_river_list)) !== false) {
			unset($featured_video_in_river_list[$key]);
		}

		// if the user has selected to play this post featured video on the river, add it to our list of ID. Make sure to add it to the front of the list
		if (has_term(PMC_Featured_Video_Override::OPTION_NAME,  '_post-options', $post_id)) {
			array_unshift($featured_video_in_river_list, $post_id);
		}

		if (!empty($featured_video_in_river_list)) {
			// we only care for 10 IDs. if we find out at this point, we have more than 10 IDs we should only keep the first 10
			$featured_video_in_river_list = array_slice($featured_video_in_river_list, 0, 10);
			$featured_videos = implode(',', $featured_video_in_river_list);
			pmc_update_option(PMC_Featured_Video_Override::FEATURED_VIDEO_LIST_OPTION_NAME, $featured_videos, PMC_Featured_Video_Override::CACHE_GROUP);
		}
	}

	/**
	 * @param $post_id
	 * @return bool
	 * @since 01-12-2017 Adaeze Esiobu PMCBA-59 HL - Play video in river
	 * decide if the featured video attached to a post can be played in the river.
	 * A featured video can be played in the river if an editor has selected the post option to allow this and it is one of the
	 * first 2 posts on the list of posts that can play their featured video on the river.
	 */
	public static function can_show_video_in_river($post_id)
	{
		$featured_video_in_blogroll =  PMC_Cheezcap::get_instance()->get_option('pmc_enable_featured_video_in_blogroll');
		if (!isset($featured_video_in_blogroll) || 'no' == $featured_video_in_blogroll) {
			return false;
		}

		$featured_videos_in_river = pmc_get_option(PMC_Featured_Video_Override::FEATURED_VIDEO_LIST_OPTION_NAME, PMC_Featured_Video_Override::CACHE_GROUP);

		if (empty($featured_videos_in_river)) {
			return false;
		}

		$featured_video_in_river_list = explode(',', $featured_videos_in_river);

		$key = array_search($post_id, $featured_video_in_river_list);
		if (($key  !== false) && $key < 2) {
			return true;
		}

		return false;
	}

	/**
	 * @since 01-12-2017 Adaeze Esiobu PMCBA-59 HL - Play video in river
	 * Add Post Options to allow Editors flag a post to play it's featured video in the river.
	 */
	public static function add_post_options()
	{
		$post_options_api = Post_Options_API::get_instance();

		$post_options_api->register_options(array(
			self::OPTION_NAME => self::OPTION_LABEL

		));
	}


	//Create Meta Boxes
	public static function create($meta_boxes)
	{

		$grp_args = array(
			'label'   => 'Featured Video',
			'context' => 'side',
		);


		/**
		 * pmc_feature_video_post_types filter allows you to whitelist a CPT to use the Feature Video meta box
		 *
		 * @version 2015-08-10
		 * @since 2015-08-10 - Mike Auteri - PPT-5070: Platform - Introduce "Content" custom post-type on all sites
		 */
		$post_array = apply_filters('pmc_feature_video_post_types', array('post'));

		$group = self::KEY . "-grp";

		x_add_metadata_group($group, $post_array, $grp_args);

		x_add_metadata_field(
			self::META_KEY,
			$post_array,
			array(
				'group'             => $group,
				'field_type'        => 'text',
				'values'            => array(),
				'label'             => __('Top of Post Video', 'pmc-featured-video-override'),
				'description'       => sprintf('Enter a video URL supported by <a href="%s" title="WordPress oEmbed" target="_blank">oEmbed</a> (e.g. YouTube, Vimeo, etc.), or JW Player shortcode. JWPlayer shortcodes must be formatted like [jwplayer videoID] (ex: [jwplayer rhBqkhKq])', esc_url('https://wordpress.org/support/article/embeds/#okay-so-what-sites-can-i-embed-from')),
				'sanitize_callback' => array('PMC_Featured_Video_Override', 'sanitize_video'),
			)
		);

		$featured_end_video =  PMC_Cheezcap::get_instance()->get_option('pmc_enable_featured_video_end_of_article_video');
		if ($featured_end_video) {
			// Set a group for the end of article video
			$grp_args_end = array(
				'label'   => 'Featured End Video',
				'context' => 'side',
			);
			x_add_metadata_group($group, $post_array, $grp_args_end);
			x_add_metadata_field(
				self::META_KEY_END,
				$post_array,
				array(
					'group'             => $group,
					'field_type'        => 'text',
					'values'            => array(),
					'label'             => 'End of Post Video',
					'description'       => 'Append a featured video to the end of the post',
					'sanitize_callback' => array('PMC_Featured_Video_Override', 'sanitize_video'),
				)
			);
		}
	}

	public static function flv_shortcode_set_default_image($post_id)
	{

		add_filter('shortcode_atts_pmc_flv', array('PMC_Featured_Video_Override', 'set_default_image'), 10, 3);
	}

	public static function set_default_image($out, $pairs, $atts)
	{
		if (empty($out["image"])) {

			$post_thumbnail = PMC_Featured_Video_Override::get_multipostthumbnail_data(get_the_ID(), 'flv_shortcode_image', 'flv-shortcode-image-lrg');

			if (!empty($post_thumbnail['image_url'])) {

				$out["image"] = $post_thumbnail['image_url'];
			} elseif (has_post_thumbnail(get_the_ID())) {

				$thumbnail_id = get_post_thumbnail_id(get_the_ID());

				$thumbnail    = wp_get_attachment_image_src($thumbnail_id, 'flv-shortcode-image-lrg');

				if (!empty($thumbnail)) {
					$out["image"] = $thumbnail[0];
				}
			}
		}
		return $out;
	}

	/**
	 * @static
	 *
	 * @param $shortcode
	 * tests if the given shortcode is an flv shortcode.
	 */
	private static function _is_flv_shortcode($shortcode)
	{


		$pattern = get_shortcode_regex();
		preg_match('/' . $pattern . '/s', $shortcode, $matches);

		if (is_array($matches) && !empty($matches[2]) && ('flv' == $matches[2])) {
			return true;
		}

		return false;
	}

	private static function _is_youtube($url)
	{
		$youtube_domain = array(
			'youtu.be',
			'www.youtube.com',
			'youtube.com'
		);

		if (wpcom_vip_is_valid_domain($url, $youtube_domain)) {

			return true;
		}

		return false;
	}

	private static function _get_ombed($url, $post_id)
	{

		$host = parse_url($url, PHP_URL_HOST);

		if (empty($host)) {
			return false;
		}

		$dimensions = self::_get_dimensions(array());

		$oembed_video = wpcom_vip_wp_oembed_get(
			$url,
			array(
				'width'  => $dimensions['width'],
				'height' => $dimensions['height'],
				'id'     => 'pmc-featured-id-' . intval($post_id)
			)
		);

		return $oembed_video;
	}


	private static function _get_shortcode($text)
	{


		$pattern = get_shortcode_regex();
		preg_match('/' . $pattern . '/s', $text, $matches);

		if (is_array($matches) && !empty($matches[2])) {
			return $matches[0];
		}

		return false;
	}

	public static function validate($value, $post_id)
	{

		if (is_numeric($value)) {
			return intval($value);
		}

		if (true === self::_is_youtube($value)) {
			return esc_url_raw($value);
		}

		$shortcode = self::_get_shortcode($value);
		if (!empty($shortcode)) {
			return sanitize_text_field($shortcode);
		}

		$oembed = self::_get_ombed($value, $post_id);
		if (!empty($oembed)) {
			return esc_url_raw($value);
		}

		if (!empty($GLOBALS['wp_embed'])) {
			$auto_oembed = $GLOBALS['wp_embed']->autoembed($value);
			if ($auto_oembed != $value) {
				return esc_url_raw($value);
			}
		}

		return false;
	}

	public static function sanitize_video($field_slug, $field, $object_type, $object_id, $value)
	{

		return self::validate($value, $object_id);
	}

	/**
	 * Get video url
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 */
	private static function _get_video($post_id, $pos = false)
	{

		if (empty($post_id)) {
			return;
		}

		if ($pos && 'end' === $pos) {
			return get_post_meta($post_id, self::META_KEY_END, true);
		} else {
			return get_post_meta($post_id, self::META_KEY, true);
		}
	}

	private static function  _prepare_ndn_shortcode($post_id, $video_id, $dimensions = array(), $args)
	{

		if (isset($args['trackinggroup'])) {
			$trackinggroup = $args['trackinggroup'];
		} else {
			$trackinggroup = '';
		}

		$default = array(
			'width'         => $dimensions['width'],
			'height'        => $dimensions['height'],
			'id'            => 'pmc-featured-id-' . intval($post_id),
			'sitesection'   => PMC_SITE_NAME,
			'trackinggroup' => $trackinggroup,
		);

		$params = wp_parse_args($args, $default);

		$att = "";
		foreach ($params as $key => $value) {
			$att .= $key . "='" . esc_attr($value) . "' ";
		}
		$att .= "videoid='" . intval($video_id) . "'";

		return "[pmc-ndn class=ndn_featured_video embedtype=script {$att}]";
	}

	private static function _get_dimensions($args)
	{

		//width = args > content_width > 600
		if (isset($args['width'])) {
			$width = $args['width'];
		} else {
			$width = (isset($GLOBALS['content_width'])) ? $GLOBALS['content_width'] : 600;
		}

		//height = args ratio > height > 16:9 default
		if (isset($args['ratio'])) {
			$ratio = explode(":", $args['ratio']);
			unset($args['height']);
		} else {
			$ratio = array(16, 9);
		}

		$height = round(($ratio[1] * $width) / $ratio[0]);

		return array('width' => $width, 'height' => $height);
	}

	/**
	 * Get HTML for youtube/NDN saved in meta. This will be removed after updating themes to use get_clean_video_html
	 *
	 * @param $post_id
	 * @param $args
	 *
	 * @return bool|string
	 */
	public static function get_video_html($post_id, $args = array())
	{
		return self::get_clean_video_html($post_id, $args);
	}

	/**
	 * Get HTML from shorcodes or embeds stored in meta by running do_shortcode.
	 *
	 * @param $post_id
	 * @param $args
	 *
	 * @return bool|string
	 */
	public static function get_clean_video_html($post_id, $args = array())
	{
		if (empty($post_id)) {
			return;
		}

		if (isset($args['video']) && '' != $args['video']) {

			$video = $args['video'];
		} elseif (!empty($args['position'])) {
			$video = self::_get_video($post_id, $args['position']);
		} else {
			$video = self::_get_video($post_id);
		}

		if (empty($video)) {
			return;
		}

		$dimensions = self::_get_dimensions($args);

		$width  = $dimensions['width'];
		$height = $dimensions['height'];


		//This means its ndn
		if (is_numeric($video)) {
			$ndn_shortcode = self::_prepare_ndn_shortcode($post_id, intval($video), $dimensions, $args);

			return do_shortcode($ndn_shortcode);
		}

		//Check if youtube
		if (self::_is_youtube($video)) {
			$youtube_url = esc_url("{$video}&w={$width}&h={$height}");

			return do_shortcode("[youtube=$youtube_url]");
		}

		//Shortcodes.
		if (self::_get_shortcode($video)) {
			//If a user has entered one of the many other video shortcodes we suport ( e.g. flv ) then we will hit this point in the code.
			//test if the shortcode given is an FLV shortcode. and if it is you want to add the filter to add the featured image. ppt-2797
			if (self::_is_flv_shortcode($video)) {

				PMC_Featured_Video_Override::flv_shortcode_set_default_image($post_id);
			}

			return do_shortcode($video);
		}

		//Final check if its oembed
		$oembed_video = wpcom_vip_wp_oembed_get(
			$video,
			array(
				'width'  => $width,
				'height' => $height,
				'id'     => 'pmc-featured-id-' . intval($post_id)
			)
		);

		if (!empty($oembed_video)) {
			return $oembed_video;
		}

		if (!empty($GLOBALS['wp_embed'])) {
			$auto_oembed = $GLOBALS['wp_embed']->autoembed($video);
			if ($auto_oembed != $video) {
				return $auto_oembed;
			}
		}
	}


	/**
	 * Get HTML for youtube/JWPlayer saved in meta.
	 *
	 * @since 2015-12-09
	 * @version 2015-12-09 Archana Mandhare PMCVIP-411
	 *
	 * @param $post_id int
	 * @param $args array dimensions
	 *
	 * @return bool|string
	 */
	public static function get_video_html5($post_id, $args = array())
	{
		if (empty($post_id)) {
			return;
		}

		$video = self::_get_video($post_id);

		if (empty($video)) {
			return false;
		}

		$dimensions = self::_get_dimensions($args);

		$default = array(
			'width'         => $dimensions['width'],
			'height'        => $dimensions['height'],
		);

		$params = wp_parse_args($args, $default);

		$width  = $params['width'];
		$height = $params['height'];

		//Check if youtube
		if (self::_is_youtube($video)) {

			// Trim from right side if there is any '?' at the end.
			$video = rtrim($video, '?');

			/**
			 * Check if url has query string or not
			 * If no than use `?` otherwise '&' as starter for next query argument.
			 */
			$query_string_starter = empty(wp_parse_url($video, PHP_URL_QUERY)) ? '?' : '&';

			$youtube_url = esc_url("{$video}{$query_string_starter}w={$width}&h={$height}");

			$youtube_video = do_shortcode("[youtube=$youtube_url]");
			if (false === strpos($youtube_video, 'op-social')) {
				$youtube_video = '<figure class="op-social">' . $youtube_video . '</figure>';
			}
			return $youtube_video;
		}

		// check if jwplatform Shortcode.
		if (self::_get_shortcode($video) && (false !== strpos($video, 'jwplatform') || false !== strpos($video, 'jwplayer'))) {

			$pattern = get_shortcode_regex();
			preg_match_all('/' . $pattern . '/s', $video, $matches);

			if (!empty($matches[3]) && !empty($matches[3][0])) {

				if (
					class_exists('\PMC\JW_YT_Video_Migration\Post_Migration')
					&& class_exists('\PMC\JW_YT_Video_Migration\Cheez_Options')
					&& true === \PMC\JW_YT_Video_Migration\Cheez_Options::is_migration_enabled()
				) {

					$youtube_video = \PMC\JW_YT_Video_Migration\Post_Migration::get_instance()->output_shortcode(array($matches[3][0]));

					if (!empty($youtube_video) && false === strpos($youtube_video, 'op-social')) {
						$youtube_video = '<figure class="op-social">' . $youtube_video . '</figure>';
					}

					if (!empty($youtube_video)) {
						return $youtube_video;
					}
				}

				return self::get_jwplayer_video_html5($matches[3][0], $args);
			}
		}

		return false;
	}


	/**
	 * Get HTML5 for JWPlayer based on the $video id
	 *
	 * @since 2015-12-11
	 * @version 2015-12-11 Archana Mandhare PMCVIP-411
	 *
	 * @param $video string
	 * @param $args array - array('height'=> '', 'width' => '');
	 *
	 * @return bool|string
	 */
	public static function get_jwplayer_video_html5($video_id, $args = array())
	{

		$dimensions = self::_get_dimensions($args);

		$default = array(
			'width'         => $dimensions['width'],
			'height'        => $dimensions['height']
		);

		$params = wp_parse_args($args, $default);

		$video_id = trim($video_id);

		if (!empty($video_id)) {

			if (function_exists('is_amp_endpoint') && is_amp_endpoint() && class_exists('\PMC\Google_Amp\Single_Post')) {
				return \PMC\Google_Amp\Single_Post::get_instance()->jwplayer_handle_shortcode_for_amp(array($video_id));
			}

			$jw_player_url = 'https://' . JWPLAYER_CONTENT_MASK . '/players/' . $video_id . '.html';
			$video_html = sprintf('<figure class="op-interactive"><iframe src="%s" width="%s" height="%s" frameborder="0" scrolling="auto"></iframe></figure>', esc_url($jw_player_url), esc_attr($params['width']), esc_attr($params['height']));
			return $video_html;
		}

		return false;
	}

	public static function has_featured_video($post_id = null)
	{
		if (empty($post_id)) {
			return false;
		}

		$video = get_post_meta($post_id, self::META_KEY, true);

		if (!empty($video)) {
			return true;
		}

		return false;
	}

	/**
	 * @param        $post_id
	 * @param string $mpt_name
	 * @param string $size
	 *
	 * @return array
	 */
	public static function get_multipostthumbnail_data($post_id, $mpt_name = "flv_shortcode_image", $size = "flv-shortcode-image")
	{

		if (empty($post_id)) {
			return;
		}

		$current_post = get_post($post_id);

		if (empty($current_post)) {
			return;
		}

		// See if a MPT image exists for the Primary, fallback to Alternate
		$image_id = 0;
		if (MultiPostThumbnails::has_post_thumbnail($current_post->post_type, $mpt_name, $current_post->ID)) {
			$image_id = MultiPostThumbnails::get_post_thumbnail_id($current_post->post_type, $mpt_name, $current_post->ID);
		}

		if (empty($image_id)) {
			return;
		}

		$teaser = get_post($image_id);
		list($image_url) = wp_get_attachment_image_src($image_id, $size);
		$caption = $teaser->post_excerpt;
		$alt     = get_post_meta($image_id, '_wp_attachment_image_alt', true);
		$credit  = get_post_meta($image_id, '_image_credit', true);

		return array(
			'image_url' => $image_url,
			'caption'   => $caption,
			'alt'       => $alt,
			'credit'    => $credit
		);
	}

	/*
	 * Function to check if the given featured video is JWPlayer Video or Youtube Video
	 * This function is being used in pmc-custom-feed-v2 instant articles to know the video type
	 *
	 * @since 2016-02-01
	 * @version 2016-02-01 Archana Mandhare PMCVIP-876
	 *
	 * @param $post_id int
	 * @return bool
	 *
	 */
	public static function is_jwplayer_or_youtube_video($post_id)
	{

		$video           = self::_get_video($post_id);
		$is_youtube      = self::_is_youtube($video);
		$video_shortcode = self::_get_shortcode($video);

		if ($is_youtube || ($video_shortcode && (false !== stripos($video, 'jwplatform') || false !== stripos($video, 'youtube')))) {
			return true;
		}

		return false;
	}

	public static function render_video($post_id = 0, $args = array())
	{
		if (0 === $post_id || empty($args)) {
			return false;
		}

		$play_video_in_river =  PMC_Featured_Video_Override::can_show_video_in_river($post_id);
		$video = PMC_Featured_Video_Override::get_video_html($post_id, $args);
		if ($play_video_in_river && !empty($video)) {
			return $video;
		} else {
			return false;
		}
	}

	/*
	 * Adds cheez option the the global theme options page in theme setttings.
	 *
	 * @since 2016-02-01
	 * @version 2017-04-21 -
	 * Adding in an option to enable end or article metabox for featured video.
	 *
	 * @param $post_id int
	 * @return bool
	 *
	 */
	public static function featured_video_cheezcap_groups($cheezcap_options)
	{
		$cheezcap_options[] = new CheezCapDropdownOption(
			'Enable Featured Video In River',
			'Enable Featured Video In River',
			'pmc_enable_featured_video_in_blogroll',
			array('yes', 'no'),
			1, // 1sts option => yes
			array('Yes', 'No')
		);

		$cheezcap_options[] = new CheezCapBooleanOption(
			'Enable Featured video end of article video',
			'Enable Featured video end of article video',
			'pmc_enable_featured_video_end_of_article_video',
			false
		);

		return $cheezcap_options;
	}

	/**
	 * Render video in a Larva template.
	 *
	 * @param int $id Post ID.
	 */
	public function do_larva_output(int $id = 0): void
	{
		if (empty($id)) {
			return;
		}

		$is_mobile = PMC::is_mobile();

		$args = [
			'trackinggroup' => 91925, // '91925' is used by all buy VY and TVL.
			'width'         => $is_mobile ? 320 : 810,
			'position'      => '', // TODO: is there a default for this?
		];

		$args = apply_filters(
			'pmc_larva_featured_video_override_args',
			$args,
			$id,
			$is_mobile
		);

		$markup = static::get_video_html($id, $args);

		if (empty($markup)) {
			return;
		}

		// Method returns escaped output.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $markup;
	}
}

PMC_Featured_Video_Override::get_instance();
//EOF
