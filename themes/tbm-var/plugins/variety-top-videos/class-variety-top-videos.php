<?php

/*
Plugin Name: Variety Top Videos
Plugin URI: http://www.variety.com
Version: 1.0
Author: Hau Vong, PMC
Author URI: http://www.pmc.com
Author Email: hvong@pmc.com
License: PMC proprietary. All rights reserved.

Updated for pmc-variety-2017 by XWP.

This plugin adds a new menu to the WP admin dashboard, which allows to create new post types for adding videos.
*/

use \PMC\Global_Functions\Traits\Singleton;

class Variety_Top_Videos
{

	use Singleton;

	const POST_TYPE_NAME = 'variety_top_video';

	/**
	 * Class constructor.
	 *
	 * @param void
	 *
	 * @return void.
	 */
	protected function __construct()
	{
		$this->_setup_hooks();
	}

	/**
	 * Setup hooks
	 */
	protected function _setup_hooks()
	{
		add_action('init', array($this, 'register_post_type'));
		add_action('save_post', array($this, 'save_post'));
		add_action('pre_get_posts', array($this, 'filter_query'));
		add_action('draft_to_publish', array($this, 'refresh_recent_videos'));
		add_action('publish_to_trash', array($this, 'refresh_recent_videos'));
		add_action('init', array($this, 'action_init'));
		add_filter('mmc_add_custom_keywords_ad', array($this, 'filter_ad_keywords'));
		add_filter('is_protected_meta', array($this, 'hide_post_meta_from_custom_fields'), 10, 2);
		add_filter('rest_prepare_variety_top_video', [$this, 'add_image_to_rest_response'], 10, 2);
	}

	public function action_init()
	{
		global $wp;
		// add query var to avoid using $_GET['f']
		$wp->add_query_var('f');
	}

	// helper functions
	public static function get_available_channels($channel = false, $default = false)
	{
		// get_option is efficiently retrieve and store value via class variable, so get_option only get call once
		$channels = Variety_Top_Videos_Settings::get_option('available_channels', array());
		return $channel ? (isset($channels[$channel]) ? $channels[$channel] : $default) : $channels;
	}

	public static function get_active_channels($channel = false, $default = false)
	{
		// get_option is efficiently retrieve and store value via class variable, so get_option only get call once
		$channels = Variety_Top_Videos_Settings::get_option('active_channels', array());
		return $channel ? (isset($channels[$channel]) ? $channels[$channel] : $default) : $channels;
	}

	public function filter_ad_keywords($ad_keywords)
	{
		if (is_tax('vertical', 'video') || (is_single() && get_post_type() === 'variety_top_video')) {
			$playlist = get_query_var('f');
			if (!empty($playlist)) {
				$ad_keywords[] = sanitize_title($playlist) . '-playlist';
			}
		}
		return $ad_keywords;
	}

	/**
	 * Registers different post types and taxonomies under the plugin.
	 *
	 * @param void
	 * @return void.
	 */
	public function register_post_type()
	{
		register_post_type(self::POST_TYPE_NAME, array(
			'labels'               => array(
				'name'               => __('Videos', 'pmc-variety'),
				'singular_name'      => __('Video', 'pmc-variety'),
				'add_new'            => _x('Add New', 'Video', 'pmc-variety'),
				'add_new_item'       => __('Add New Video', 'pmc-variety'),
				'edit_item'          => __('Edit Video', 'pmc-variety'),
				'new_item'           => __('New Video', 'pmc-variety'),
				'view_item'          => __('View Video', 'pmc-variety'),
				'search_items'       => __('Search Videos', 'pmc-variety'),
				'not_found'          => __('No Videos found.', 'pmc-variety'),
				'not_found_in_trash' => __('No Videos found in Trash.', 'pmc-variety'),
				'all_items'          => __('Videos', 'pmc-variety'),
			),
			'public'               => true,
			'supports'             => array('title', 'author', 'comments', 'editor', 'thumbnail'),
			'show_in_rest'         => true,
			'has_archive'          => 'videos',
			'rewrite'              => array('slug' => 'video'),
			'register_meta_box_cb' => array($this, 'add_meta_boxes'),
			'taxonomies'           => array('category', 'post_tag', 'vcategory'),
			'menu_icon'            => 'dashicons-format-video',
		));

		register_taxonomy(
			'vcategory',
			self::POST_TYPE_NAME,
			[
				'labels'            => [
					'name'          => _x('Playlists', 'taxonomy general name', 'pmc-variety'),
					'singular_name' => _x('Playlist', 'taxonomy singular name', 'pmc-variety'),
				],
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_in_nav_menus' => true,
				'show_admin_column' => true,
			]
		);
	}

	/**
	 * Hide Post Meta from Custom Fields Meta Box
	 *
	 * We tap into the is_protected_meta filter to flag our post meta keys as fake private,
	 * this way the meta will not display in the Custom Fields post meta box as editable.
	 * This is cleaner than prefixing our meta keys with an underscore
	 *
	 * @internal                 Called via is_protected_meta filter
	 * @param  bool   $protected Whether the key is protected. Default false.
	 * @param  string $meta_key  Meta key.
	 * @return bool   $protected
	 */
	function hide_post_meta_from_custom_fields($protected, $meta_key)
	{
		// Hide the video source and duration meta
		// by marking it as protected
		if (
			'variety_top_video_source' === $meta_key ||
			'variety_top_video_duration' === $meta_key
		) {

			$protected = true;
		}

		return $protected;
	} // hide_post_meta_from_custom_fields

	/**
	 * Build the Top Video Meta Boxes
	 *
	 * @param  object $post The $post being edited
	 * @return null
	 */
	public function add_meta_boxes($post)
	{

		// Create the Video Information Meta Box
		add_meta_box('variety-top-video-link', esc_html__('Video Information', 'pmc-variety'), array($this, 'top_video_information_meta_box'), $post->post_type, 'normal');
	} // add_meta_boxes

	/**
	 * Top Video Post Meta Box
	 *
	 * Capture any video URL (YouTube, Vimeo, etc.) or any video shortcode (Uvideo, JW Player, etc.).
	 * Capture secondary video data (duration, etc.)
	 *
	 * @param  object $post The $post object currently being edited
	 * @return null
	 */
	public function top_video_information_meta_box($post)
	{
		// Upgrade the top video post meta
		// During version 2 of this module we revised the post meta names
		// using Variety_Top_Videos::upgrade_top_video_post_meta( $post );

		// Fetch the video's additional information
		$video_source = get_post_meta($post->ID, 'variety_top_video_source', true);
		$video_duration = get_post_meta($post->ID, 'variety_top_video_duration', true);

		// Set a nonce to verify upon saving meta
		wp_nonce_field('variety-top-video-nonce', 'variety_top_video_nonce_name');

		/**
		 * @since 2017-09-01 Milind More CDWE-499
		 */
		echo \PMC::render_template(
			CHILD_THEME_PATH . '/plugins/variety-top-videos/templates/top-videos-information-metabox.php',
			array(
				'video_source'   => $video_source,
				'video_duration' => $video_duration,
			)
		);
	} // top_video_information_meta_box

	public function vcategory_meta_box($post)
	{
		$channels = $this->get_available_channels();
		if (count($channels) > 0) {
			wp_nonce_field('variety_vcategories_nonce', '_variety_vcategories_nonce_name');
			foreach ($channels as $slug => $name) {
				$checked = (has_term($slug, 'vcategory', $post)) ? 'checked="checked" ' : '';
				printf('<p>');
				printf('<input type="checkbox" name="vcategory[%1$s]" id="vcategory[%1$s]" %2$s />', esc_attr($slug), esc_attr($checked));
				printf('<label for="vcategory[%1$s]">%2$s</label>', esc_attr($slug), wp_kses_post($name));
				printf('</p>');
			}
		} else {
			echo 'No categories currently available.';
		}
	}

	/**
	 * Upgrade existing post meta fields
	 *
	 * Version 2 of this module moves YouTube integration out of the core
	 * functionality, and into a tertiary process. Now we're collecting any
	 * oEmbed URL and Shortcode. Secondly, we're removing the preceeding
	 * underscore from the post meta names. This meta is not 'protected' and
	 * using an underscore is a dirty solution to simply hide the meta from
	 * being editable within the Custom Fields post meta box. We'll use a filter
	 * instead to hide these meta from that meta box.
	 *
	 * This function and it's callers must remain intact until all instances of the
	 * old meta names have been removed.
	 *
	 * Remove preceeding underscore & migrate old
	 * 	+ _variety_top_video_link to variety_top_video_source
	 *  + _variety_top_video_data to variety_top_video_duration
	 *  	+ migrate the video duration
	 *  	+ migrate the video thumbnail
	 *  + delete _variety_top_video_id
	 *
	 * @internal               Called whenever a video is displayed (in single, sliders, sidebars, etc.)
	 * @param     object $post The $post to upgrade
	 * @return    null
	 */
	static function upgrade_top_video_post_meta($post)
	{
		// Rename the old post meta where the YouTube link was stored
		$youtube_video_link = get_post_meta($post->ID, '_variety_top_video_link', true);

		if (!empty($youtube_video_link)) {
			update_post_meta($post->ID, 'variety_top_video_source', $youtube_video_link);
			delete_post_meta($post->ID, '_variety_top_video_link');
		}

		// Rename the old post meta where YouTube data was previously stored
		// Previously any/all YT data for the video was captured and stored
		// However, only the duration was readily used. There was code which
		// could fallback and display the YT video placeholder image when no
		// featured image was set, however--EVERY video has a featured image
		$youtube_video_data = get_post_meta($post->ID, '_variety_top_video_data', true);

		/*
			Sample of what's in _variety_top_video_data:

			a:8:{s:2:"id";s:11:"gvk6TwmF0cw";s:5:"title";s:74:"Tyler Posey talks to Variety about being honored at Variety Power of Youth";s:4:"link";s:42:"http://www.youtube.com/watch?v=gvk6TwmF0cw";s:9:"thumbnail";s:47:"http://i.ytimg.com/vi/gvk6TwmF0cw/hqdefault.jpg";s:4:"desc";s:75:"Tyler Posey talks to Variety about being honored at Variety Power of Youth.";s:8:"duration";i:33;s:9:"published";s:24:"2013-07-30T16:52:41.000Z";s:9:"viewcount";i:1;}

			unserialized version:

			array(
	            'id'        => 'gvk6TwmF0cw',
	            'title'     => 'Tyler Posey talks to Variety about being honored at Variety Power of Youth',
	            'link'      => 'http://www.youtube.com/watch?v=gvk6TwmF0cw',
	            'thumbnail' => 'http://i.ytimg.com/vi/gvk6TwmF0cw/hqdefault.jpg',
	            'desc'      => 'Tyler Posey talks to Variety about being honored at Variety Power of Youth.',
	            'duration'  => 33,
	            'published' => '2013-07-30T16:52:41.000Z',
	            'viewcount' => 1,
	        )
		*/

		if (!empty($youtube_video_data) && is_array($youtube_video_data)) {

			// The YouTube Duration was previously stored as just seconds
			$youtube_video_duration = gmdate('H:i:s', $youtube_video_data['duration']);
			update_post_meta($post->ID, 'variety_top_video_duration', $youtube_video_duration);

			/*
			// Import the YouTube video thumbnail as the post's featured image
			// This sideloading is causing too many issues (can only be done in admin, and via POST)
			// Leaving for posterity if needed in the future

			// Only proceed if there is a youtube video thumbnail
			if ( isset( $youtube_video_data['thumbnail'] ) ) {
				if ( ! empty( $youtube_video_data['thumbnail'] ) ) {

					// Does the post already have a featured image?
					if ( has_post_thumbnail( $post->ID ) ) {
						// Yes, it does have a featured image, DO NOT bring the image from YouTube over
						//
					} else {
						// NO, it does not have a featured image, let's import the image from YouTube

						// Import the YouTube image
						require_once( ABSPATH . 'wp-admin/includes/media.php' );
						require_once( ABSPATH . 'wp-admin/includes/file.php' );
						require_once( ABSPATH . 'wp-admin/includes/image.php' );

						print_r($_SERVER);

						// Download the image from URL
						// Attach to the post
						$attachment_id = wpcom_vip_download_image(
							$youtube_video_data['thumbnail'],
							$post->ID,
							$youtube_video_data['title']
						);

						print_r($attachment_id);

					} // if has featured image
				} // if yt image not empty
			} // if yt image isset
			*/

			// Delete the old post meta item
			delete_post_meta($post->ID, '_variety_top_video_data');
		}	// End if()

		// Lastly, remove the stored YouTube video ID
		// We're using oEmbed now, so this won't be necessary
		$youtube_video_id = get_post_meta($post->ID, '_variety_top_video_id', true);

		if (!empty($youtube_video_id)) {
			delete_post_meta($post->ID, '_variety_top_video_id');
		}
	} // upgrade_top_video

	/**
	 * Saves the post, along with autosave function
	 *
	 * @todo  Expand the YouTube API fetching to instead check first if the video duration was manually entered (or if it's blank). If it's blank, sniff the provider (i.e. shortcode, vimeo url, oEmbed) and fetch the video duration, storing it in post meta (which in turn is then displayed in the duration metabox input field)
	 *
	 * @param int $post_id. The ID of the post to be saved
	 * @return void.
	 */
	public function save_post($post_id)
	{
		// Bail on autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		// Check for appropriate capabilities
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		$variety_top_video_nonce_name = filter_input(INPUT_POST, 'variety_top_video_nonce_name');

		// Save the Top Video Information (Video source, duration, etc.)
		// Ensure our meta boxes nonce is set and valid
		if (!empty($variety_top_video_nonce_name) && wp_verify_nonce($variety_top_video_nonce_name, 'variety-top-video-nonce')) {

			$variety_top_video_source   = filter_input(INPUT_POST, 'variety_top_video_source');
			$variety_top_video_duration = filter_input(INPUT_POST, 'variety_top_video_duration');

			// Ensure that the video source fields isn't empty
			if (!empty($variety_top_video_source)) {
				// Store the video's source
				$video_source = sanitize_text_field($variety_top_video_source);
				$video_source = variety_filter_youtube_url($video_source);
				update_post_meta($post_id, 'variety_top_video_source', $video_source);

				// Store the video's duration
				$video_duration = sanitize_text_field($variety_top_video_duration);
				update_post_meta($post_id, 'variety_top_video_duration', $video_duration);

				// sniff the video source/link and acertain it's platform
				// check if a duration was entered
				// query the platform for additional video data, i.e. duration if none was given
				//|| !preg_match( "#v=([\w|-]+)#", $_POST['_variety_top_video_link'], $matches )
				/*
				// process data
				$data = wpcom_vip_file_get_contents( 'http://gdata.youtube.com/feeds/api/videos/' . $matches[1] . '?v=2&alt=json' );

				if ( $data = json_decode( $data ) ) {
					$entry = $data->entry;
					$base_data = $this->prepare_data( $entry );
				}

				$this->save_data( $post_id, $base_data, esc_url_raw( $_POST['_variety_top_video_link'] ) );
				*/
			} else {
				// However, if the video source field is empty..
				// Remove any/all video information from post meta
				delete_post_meta($post_id, '_variety_top_video_link');
				delete_post_meta($post_id, '_variety_top_video_duration');
				delete_post_meta($post_id, '_variety_top_video_data');
				delete_post_meta($post_id, '_variety_top_video_id');
			}	// End if()
		}

		$variety_vcategories_nonce_name = filter_input(INPUT_POST, '_variety_vcategories_nonce_name');

		// Save Top Video vcategory data
		if (!empty($variety_vcategories_nonce_name) && wp_verify_nonce($variety_vcategories_nonce_name, 'variety_vcategories_nonce')) {

			$vcategory = filter_input(INPUT_POST, 'vcategory', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
			$terms = array();

			$vcategories = (!empty($vcategory) && is_array($vcategory)) ? $vcategory : array();

			$vcategories = array_keys(array_map('sanitize_text_field', $vcategories));
			$available_channels = $this->get_available_channels();

			for ($i = 0; $i < count($vcategories); $i++) {
				if (array_key_exists($vcategories[$i], $available_channels)) {
					$terms[] = $vcategories[$i];
				}
			}

			wp_set_object_terms($post_id, $terms, 'vcategory');

			//refreshes the next up video cache for this video
			foreach ($terms as $term) {
				$this->refresh_next_videos($post_id, $term);
			}
		}
	}

	/**
	 * Prepares data before saving the the post. Sanitize all the data came from the post form
	 *
	 * @param obj $entry. The post entry object, which has all the attributes like id, title, link etc.
	 * @return obj $base_data. Sanitized data ready to be saved into db tables
	 */
	private function prepare_data($entry)
	{

		$base_data = array();

		if (!empty($entry)) {
			$id = explode(':', $entry->id->{'$t'});
			$id = $id[count($id) - 1];

			$base_data = array(
				'id'         => sanitize_text_field($id),
				'title'      => sanitize_text_field($entry->title->{'$t'}),
				'link'       => esc_url_raw(str_replace('&feature=youtube_gdata_player', '', $entry->{'media$group'}->{'media$player'}->url)),
				'thumbnail'  => esc_url_raw($entry->{'media$group'}->{'media$thumbnail'}[2]->url), // value would be like esc_url_raw( 'http://i.ytimg.com/vi/' . $id . '/mqdefault.jpg' ),
				'desc'       => sanitize_text_field($entry->{'media$group'}->{'media$description'}->{'$t'}),
				'duration'   => intval($entry->{'media$group'}->{'yt$duration'}->seconds),
				'published'  => sanitize_text_field($entry->{'published'}->{'$t'}),
				'viewcount'  => intval($entry->{'yt$statistics'}->viewCount),
			);
		}

		return $base_data;
	}

	/**
	 * Save the sanitized data for the video post.
	 *
	 * @param int $post_id. The ID of the video post.
	 * @param array $data. Post data in an array format. For Eg: id, link, title etc.
	 * @param str $video_link (Optional). Link of the post to be saved.
	 * @return void
	 */
	private function save_data($post_id, $data, $video_link = '')
	{

		if (is_array($data)) {

			if (empty($video_link)) {
				$video_link = $data['link'];
			}

			update_post_meta($post_id, '_variety_top_video_data', $data);

			if (isset($data['id'])) {
				$id = sanitize_text_field($data['id']);
				update_post_meta($post_id, '_variety_top_video_id', $id);
			}
		} else {

			delete_post_meta($post_id, '_variety_top_video_data');
			delete_post_meta($post_id, '_variety_top_video_id');
		}

		if (!empty($video_link)) {
			update_post_meta($post_id, '_variety_top_video_link', esc_url($video_link));
		}
	}


	public function insert_post($data)
	{

		if (!empty($data)) {
			$data = $this->prepare_data($data);
		}

		if (!is_array($data)) {
			return;
		}

		// Usage of meta_query is required to check if post with same video id exists.
		$args = array(
			'post_type'   => self::POST_TYPE_NAME,
			'post_status' => array('draft', 'publish'),
			'numberposts' => 1,
			'meta_query'  => array(
				array(
					'key'   => '_variety_top_video_id',
					'value' => $data['id'],
				),
			),
			'suppress_filters' => false,
		);

		$has_posts = get_posts($args);
		$post_id = 0;

		if (empty($has_posts)) {
			$post_date = '';
			$gmt_date = '';

			if (isset($data['published'])) {
				$post_date = new DateTime($data['published']);
				$gmt_date = $post_date->format('Y-m-d H:i:s');
				$post_date->setTimezone(new DateTimeZone('America/Los_Angeles'));
				$post_date = $post_date->format('Y-m-d H:i:s');
			}

			$args = array(
				'post_type'     => self::POST_TYPE_NAME,
				'post_status'   => 'draft',
				'post_title'    => $data['title'],
				'post_content'  => $data['desc'],
				'numberposts'   => 1,
				'post_date'     => $post_date,
				'post_date_gmt' => $gmt_date,
			);

			$author = get_user_by('login', 'varietystaff');
			if (false !== $author) {
				$args['post_author'] = $author->ID;
			}

			$post_id = wp_insert_post($args);

			if (!is_wp_error($post_id)) {
				$this->save_data($post_id, $data);
			}
		}

		return $post_id;
	}

	/**
	 * WP_Query wrapper specifically for top videos post type
	 *
	 * @param array $args Arguments to be passed in and override our default WP_Query
	 * @return WP_Query object $posts An object from the WP_Query operation
	 */
	public function get_posts($args = array())
	{
		// Setup our default query arguments
		$defaults = array(
			'post_type'      => self::POST_TYPE_NAME,
			'post_status'    => 'publish',
			'order'          => 'DESC',
			'orderby'        => 'post_date',
		);

		// Merge our defaults array with the passed-in $args array
		$args = wp_parse_args($args, $defaults);

		// Query for the requested posts
		$posts = new WP_Query($args);

		// Return the WP_Query object containing the found posts
		return $posts;
	} // get_posts

	/**
	 * Tells us if the videos are currently being filtered by a channel.
	 *
	 * @return bool True if being filtered, false if not
	 */
	private function _is_filtered()
	{
		$f = get_query_var('f');
		if (!empty($f)) {
			return false !== $this->get_active_channels($f);
		}

		return false;
	}

	/**
	 * Makes sure the correct content displays on the video page.
	 *
	 * Sets the video page to mirror the archive page for the variety_top_video
	 * CPT, but due to nav requirements it needs to be this vertical archive. This
	 * also allows for the filtering of videos based on $_GET parameters
	 *
	 * @param obj $wp_query The current WP_Query object
	 * @return obj The filtered WP_Query object
	 */
	public function filter_query($wp_query)
	{
		if (!is_tax('vertical', 'video') || !$wp_query->is_main_query()) {
			return;
		}

		//pretend we are actually the variety_top_video archive
		$wp_query->set('vertical', '');
		$wp_query->set('tax_query', '');
		$wp_query->set('post_type', 'variety_top_video');
		$wp_query->set('posts_per_page', 12);

		if ($this->_is_filtered()) {
			$wp_query->set('tax_query', array(
				array(
					'taxonomy'         => 'vcategory',
					'field'            => 'name',
					'terms'            => esc_sql(get_query_var('f')),
					'include_children' => false,
				),
			));
		}
	}

	/**
	 * Gets information about the current video's channel and returns it for use
	 *
	 * @param int $id Optional. The ID of the video in question.
	 * @return array The information about the video in an asscoiative array.
	 */
	public function get_channel_info($id = null)
	{
		$id = (empty($id)) ? get_the_id() : intval($id);

		// Channel Info
		$terms = get_the_terms($id, 'vcategory');
		$term = (is_array($terms)) ? array_shift($terms) : null;
		$channel = (!empty($term)) ? $term->name : 'none';
		if ('none' !== $channel) {
			if (!is_wp_error(get_term_link('video', 'vertical'))) {
				$channel_link = get_term_link('video', 'vertical') . $channel . '/';
			}
			$channel_name = $this->get_active_channels($channel, '');
		} else {
			$channel_link = null;
			$channel_name = 'All';
		}
		return compact('channel', 'channel_name', 'channel_link');
	}

	/**
	 * Returns a properly formatted video timecode for a given duration value
	 *
	 * @param int The duration of the video in seconds.
	 * @return string The properly formatted timestamp
	 */
	public function get_timecode($duration)
	{
		$duration = intval($duration);

		$hours = floor($duration / 3600);
		$minutes = floor(($duration % 3600) / 60);
		$seconds = $duration % 60;

		$timecode = '';
		//hours
		if (0 < $hours) {
			$timecode .= $hours . ':';
		}
		//minutes
		if (9 > $minutes && 0 < $hours) {
			$timecode .= '0' . $minutes . ':';
		} else {
			$timecode .= $minutes . ':';
		}
		//seconds
		if (9 > $seconds) {
			$timecode .= '0' . $seconds;
		} else {
			$timecode .= $seconds;
		}

		return $timecode;
	}

	/**
	 * Builds out the Featured navigation based on available YouTube channels
	 *
	 * @return string The ul for featured navigation.
	 */
	public function featured_navigation()
	{
		$channels = $this->get_active_channels();

		$sponsored_channels = array(
			'autograph-collection-hotels',
		);

		if (0 < count($channels)) {
			$current = ($this->_is_filtered()) ? get_query_var('f') : 'all';
			$nav = '<ul>';
			$all_active = ('all' === $current) ? ' active' : '';
			$nav .= sprintf('<li class="%1$s"><a href="%2$s">All Videos<i class="fa fa-caret-down"></i></a></li>', esc_attr($all_active), esc_url(get_term_link('video', 'vertical')));
			foreach ($channels as $slug => $channel) {
				$playlist_css_classes = array();

				if ($current === $slug) {
					$playlist_css_classes[] = 'active';
				}

				if (in_array($slug, $sponsored_channels, true)) {
					$playlist_css_classes[] = 'sponsored';
				}

				$url = get_term_link('video', 'vertical') . $slug . '/';
				$nav .= sprintf('<li class="%1$s"><a href="%2$s">%3$s<i class="fa fa-caret-down"></i></a><span class="flag-sponsored">%4$s</span></li>', esc_attr(implode(' ', $playlist_css_classes)), esc_url($url), esc_html($channel), esc_html__('SPONSORED', 'pmc-variety'));
			}
			$nav .= '</ul>';

			return $nav;
		}
	}
	/**
	 * Gets the appropriate link for the playlist context we find ourselved in
	 *
	 * @param int $id Optional. The ID of the video in question.
	 * @param string $filter Optional. The desired playlist. passs null to autmatically decide.
	 * @return string The permalink to the vidoe with the appropriate playlist attached.
	 */
	public function get_playlist_link($id = null, $filter = null)
	{
		$id = (empty($id)) ? get_the_id() : intval($id);
		$link = get_permalink($id);
		return $link;
	}
	/**
	 * Gets the next videos in a spcific video playlist
	 *
	 * @param
	 * @param
	 * @return
	 */
	function get_next_videos($number = 3, $id = null)
	{
		$filter = false;
		$use_filter = false;

		if ($this->_is_filtered()) {
			$playlist = sanitize_text_field(get_query_var('f'));
			$filter = get_term_by('name', $playlist, 'vcategory')->term_id;
			$use_filter = true;
		}

		return $this->get_adjacent_posts($number, $id, $use_filter, 'vcategory', $filter);
	}
	/**
	 * Forces a refresh of the next 3 videos cache for an id
	 *
	 * We will probably need to extend the cache busting scheme for these as the system
	 * starts to contain more videos, but this will work and an easy fast solution to get
	 * us started.
	 *
	 * @param int $id The ID of the post to refresh.
	 * @return void.
	 */
	function refresh_next_videos($id, $term)
	{
		$filter = false;

		if (array_key_exists($term, $this->get_active_channels())) {
			$filter = get_term_by('name', $term, 'vcategory')->term_id;
		}

		$this->get_adjacent_posts(3, $id, true, 'vcategory', $filter,  true);
	}
	/**
	 * Duplicates and modifies core's method for retrieving an adjacent post set.
	 *
	 * In core you can only get one post and filter with categories. Here we allow any number of posts
	 * and you can filter by any taxonomy. We are only grabbing IDs, not post objects. You can either
	 * grab next or previous posts.
	 *
	 * @param int $number Optional. The number of posts to try and get.
	 * @param int $id Optional. The ID of the post to use as the starting point.
	 * @param bool $in_same_tax Optional. Whether post should be in a same taxonomy.
	 * @param string $tax Optional. If including posts in the same taxonomy, this is the taxonomy to use.
	 * @param int $term Optional. Allows filtering to a specific term in the taxonomy by ID.
	 * @param bool $previous Optional. Whether to retrieve previous post.
	 * @param boot $force Optional. Whether to force a cache refresh.
	 * @return mixed Post objects if successful. Null if global $post is not set. Empty string if no corresponding posts exists.
	 */
	function get_adjacent_posts($number = 1, $id = null, $in_same_tax = false, $tax = false, $term = false, $previous = true, $force = false)
	{
		global $wpdb;

		if (!$post = get_post($id)) {
			return null;
		}

		$current_post_date = $post->post_date;
		$number = intval($number);

		$join = '';
		$posts_in_ex_taxes_sql = '';
		if ($in_same_tax && $tax) {
			$tax = esc_sql($tax);
			$join = " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";

			if (!is_object_in_taxonomy($post->post_type, $tax)) {
				return '';
			}

			$term_exists = (function_exists('term_exists')) ? 'term_exists' : 'term_exists';
			if ($term && $term_exists(intval($term), $tax)) {
				$tax_array = (array($term));
			} else {
				$tax_array = wp_get_object_terms($post->ID, $tax, array('fields' => 'ids'));
				$tax_array = get_the_terms($post->ID, $tax);
			}

			if (!$tax_array || is_wp_error($tax_array)) {
				$join = '';
				$in_same_tax = false;
				$tax = false;
			} else {
				$tax_array = wp_list_pluck($tax_array, 'term_id');
				$join .= " AND tt.taxonomy = '$tax' AND tt.term_id IN (" . implode(',', array_map('intval', $tax_array)) . ")";
				$posts_in_ex_taxes_sql = "AND tt.taxonomy = '$tax'";
			}
		}

		$adjacent = $previous ? 'previous' : 'next';
		$op = $previous ? '<' : '>';
		$order = $previous ? 'DESC' : 'ASC';
		$number = (is_int($number) && 0 !== $number) ? $number : 1;

		$join = apply_filters(sprintf('get_%s_posts_join', sanitize_title($adjacent)), $join, $in_same_tax, $tax);

		$where = apply_filters(
			sprintf('get_%s_posts_where', sanitize_title($adjacent)),
			$wpdb->prepare(
				sprintf("WHERE p.post_date %s %%s AND p.post_type = %%s AND p.post_status = 'publish' %s", $op, $posts_in_ex_taxes_sql),
				$current_post_date,
				$post->post_type
			),
			$in_same_tax,
			$tax
		);

		$sort = apply_filters(
			sprintf('get_%s_posts_sort', sanitize_title($adjacent)),
			sprintf('ORDER BY p.post_date %s LIMIT %d', $order, $number)
		);

		$query = 'SELECT p.id FROM ' . $wpdb->posts . ' AS p ' . $join . ' ' . $where . ' ' . $sort;

		$query_key = 'adjacent_posts_' . md5($query);

		$result = wp_cache_get($query_key, 'counts');

		if (false === $result || $force) {
			// SQL is created in parts & wpdb::prepare() has been used to escape them all.
			$result = $wpdb->get_col($query);
			if (null === $result) {
				$result = '';
			}

			wp_cache_set($query_key, $result, 'counts', 3600);
		}
		return $result;
	}

	/**
	 * Builds the HTML required to display a most recent Top Videos slider
	 *
	 * @param bool $force_recache = false Whether or not to force the refresh of the cache.
	 * @return null
	 */
	function output_recent_top_video_slider($force_recache = false)
	{
		// Ensure $post is available within the scope of this function
		global $post;

		$html = '';

		// Fetch a cache of the HTML we'll be generating below
		$html = wp_cache_get('recent_videos_slider');

		// Is there any cached HTML we can use?
		// BAIL if there is..
		if (false !== $html) {

			if (!empty($html) && !$force_recache) {

				// Yes there is, echo the cached HTML
				return $html;
			}
		}

		// Cache a refernce to the video currently being viewed
		// prior to us mucking up $post with our query below
		$current_video = $post;

		// Query for the posts we want to display
		$videos = Variety_Top_Videos::get_instance()->get_posts(array(
			'posts_per_page' => 12,

			// Speed up the query, we don't need pagination
			'no_found_rows'  => true,

			// Speed up the query, we don't need a taxonomy term query
			'update_post_term_cache' => false,

			// Speed up the query, we don't need a post meta query
			'update_post_meta_cache' => false,
		));

		if ($videos->have_posts()) {

			// We'll use PHP Output Buffering to make our code below easier to read/manage
			ob_start();

			while ($videos->have_posts()) {
				$videos->the_post();

				// Upgrade the top video post meta
				// During version 2 of this module we revised the post meta names
				Variety_Top_Videos::upgrade_top_video_post_meta($post);

				// Fetch the video's duration
				$video_duration = get_post_meta($post->ID, 'variety_top_video_duration', true);

				// Fetch the video's featured image
				$video_thumbnail = variety_get_card_image_url($post, array(146, 82));

				$title = get_the_title();
				$permalink = get_permalink();

				/**
				 * @since 2017-09-01 Milind More CDWE-499
				 */
				echo \PMC::render_template(
					CHILD_THEME_PATH . '/plugins/variety-top-videos/templates/top-videos-slider.php',
					array(
						'videos'          => $videos,
						'video_duration'  => $video_duration,
						'video_thumbnail' => $video_thumbnail,
						'post'            => $post,
						'current_video'   => $current_video,
						'title'           => $title,
						'permalink'       => $permalink,
					)
				);
			} // end while()

			// Gather the content we printed to the screen and caught by PHP's output buffering
			$html = ob_get_clean();

			// Reset the $post object to the one before our query
			wp_reset_postdata();

			// Set a cache of the generated HTML
			wp_cache_set('recent_videos_slider', $html, '', 3600);

			// Output the final HTML
			return $html;
		} // if have posts
	} // output_recent_top_video_slider

	/**
	 * Forces a refresh of the most recent twelve videos used in a single view slider
	 *
	 * @param obj $post The post object being transitioned.
	 * @return void.
	 */
	function refresh_recent_videos($post)
	{
		if (self::POST_TYPE_NAME !== $post->post_type) {
			return;
		}
		$this->output_recent_top_video_slider(true);
	}
	/**
	 * Limits posts to only the last 30 days
	 *
	 * We only need to get most viewed posts for the past 30 day. This allows us to filter for that range.
	 *
	 * @param string $where The current where clause
	 * @return string The filtered where clause with the date range added
	 */
	public function where_last_30_days($where = '')
	{
		// posts in the last 30 days
		global $wpdb;
		$where .= $wpdb->prepare(' AND post_date > %s', date('Y-m-d', strtotime('-30 days')));
		return $where;
	}

	/**
	 * Include featured image URL in response, used for rendering block previews
	 * in Gutenberg.
	 *
	 * @param WP_REST_Response $response Response object.
	 * @param WP_Post          $post     Post object.
	 * @return WP_REST_Response
	 */
	public function add_image_to_rest_response(
		WP_REST_Response $response,
		WP_Post $post
	): WP_REST_Response {
		$data = $response->get_data();

		$data['featured_image'] = get_the_post_thumbnail_url(
			$post,
			'medium'
		);

		$response->set_data($data);

		return $response;
	}
}
/**
 * Wraper function for the Variety Top Videos get_next_vidoes method
 *
 * @param int $number Optional. The number of videos to fetch.
 * @param int $id Optional. The ID of the post to start with.
 * @return array The video post objects requested.
 */
function pmc_variety_next_videos($number = 3, $id = null)
{
	$instance = Variety_Top_Videos::get_instance();
	return $instance->get_next_videos($number, $id);
}

/**
 * Wraper function for the Variety Top Videos get_timecode method
 *
 * @param int The duration of the video in seconds.
 * @return string The properly formatted timestamp
 */
function pmc_variety_timecode($duration)
{
	$instance = Variety_Top_Videos::get_instance();
	return $instance->get_timecode($duration);
}
/**
 * Wraper function for the Variety Top Videos get_channel_info
 *
 * @param int The ID of the video in we want channel info for.
 * @return array An associative array of channel information.
 */
function pmc_variety_channel_info($id = null)
{
	$instance = Variety_Top_Videos::get_instance();
	return $instance->get_channel_info($id);
}
/**
 * Prints out the Featured navigation for the Variety video browse page
 *
 * @return void.
 */
function pmc_variety_top_videos_featured_nav()
{
	$instance = Variety_Top_Videos::get_instance();
	// Escaped variables previously and moved to template in CDWE-499
	// @codingStandardsIgnoreLine
	echo $instance->featured_navigation();
}

/**
 * Prints the appropriate link for the playlist context we find ourselved in
 *
 * @param int $id Optional. The ID of the video in question.
 * @param string $filter Optional. The desired playlist. passs null to autmatically decide.
 * @return string The permalink to the vidoe with the appropriate playlist attached.
 */
function pmc_variety_playlist_link($id = null, $filter = null)
{
	$instance = Variety_Top_Videos::get_instance();
	// Escaped previously and moved to template in CDWE-499
	// @codingStandardsIgnoreLine
	echo esc_url($instance->get_playlist_link());
}

/**
 * Filters and prints the return from fecth_recent_for_slider method for the current video.
 *
 * @return void.
 */
function pmc_variety_recent_video_slider()
{
	$instance = Variety_Top_Videos::get_instance();
	echo $instance->output_recent_top_video_slider(false);
}

function pmc_variety_generate_pagination($context = 'browse')
{
	global $paged, $wp_query;
	if (1 < $wp_query->max_num_pages) {
		$current_page = (0 === $paged) ? 1 : $paged;

		switch ($context):
			case 'browse':
				$link = get_term_link('video', 'vertical');
				break;
			default:
				$link = get_term_link('video', 'vertical');
				break;
		endswitch;

		$f = get_query_var('f');

		$links = paginate_links(array(
			'base'      => $link . (!empty($f) ? sanitize_text_field($f) . '/' : '') . '%_%',
			'format'    => 'page/%#%/',
			'total'     => (int) $wp_query->max_num_pages,
			'current'   => $current_page,
			'type'      => 'list',
			'prev_text' => '<i class="fa fa-caret-left"></i>',
			'next_text' => '<i class="fa fa-caret-right"></i>',
		));

		//modify class names
		$links = str_replace('<li><a class="prev', '<li class="prev"><a class="', $links);
		$links = str_replace('<li><a class="next', '<li class="next"><a class="', $links);
		$links = str_replace('<li><span class="page-numbers dots', '<li class="ellipsis"><span', $links);

		// Paginate Link is already escaped.
		// @codingStandardsIgnoreLine
		echo $links;
	}
}


//EOF
