<?php

/**
 * Class Admin
 *
 * @since 2018-03-14 Jignesh Nakrani READS-1104, READS-1142
 *
 * @package pmc-plugins
 */

namespace PMC\PMC_Video_Playlist;

use PMC\Global_Functions\Traits\Singleton;

class Admin
{

	use Singleton;

	/**
	 * Stores post type slug for PMC Video Playlist CPT
	 */
	const POST_TYPE = 'pmc-video-playlist';

	/**
	 * Cache group key to group throughout plugin
	 */
	const CACHE_GROUP = 'pmc_video_playlist_manager';

	/**
	 * Cache Time out for common cache
	 */
	const CACHE_LIFE = 900;    //15 minutes

	/**
	 * @var string Posts per page.
	 */
	const POSTS_PER_PAGE = 10;

	/**
	 * Current timezone or GMT Offset set in WordPress
	 */
	public $timezone;

	/**
	 * Fallback timezone when none is set in the site's General settings
	 *
	 * @var string
	 */
	public $default_timezone = 'UTC+0';

	/**
	 * @var string Stores the Playlist taxonomy slug
	 */
	protected $_playlist_taxonomy = 'vcategory';

	/**
	 * @var string Stores the Video postType slug
	 */
	protected $_video_posttype = 'pmc-top-video';

	/**
	 * Example: array(
	 *     'name to display on admin page' => 'taxonomy_slug',
	 * )
	 *
	 * @var array Stores the Targeted taxonomies
	 */
	protected $_targeted_taxonomies = array(
		'verticals' => 'vertical',
		'tags'      => 'post_tag',
	);

	/**
	 * @var string Stored menu_slug to load admin scripts on specific page only
	 */
	protected $_menu_slug;


	/**
	 * __construct method.
	 */
	protected function __construct()
	{

		add_action('init', [$this, 'init']);
	}

	/**
	 * Initialize things?
	 */
	public function init()
	{

		/**
		 * Filter: pmc_video_playlist_manager_targeted_taxonomies to override 'targeted taxonomies' data
		 */
		$this->_targeted_taxonomies = wp_parse_args(apply_filters('pmc_video_playlist_manager_targeted_taxonomies', $this->_targeted_taxonomies), $this->_targeted_taxonomies);

		/**
		 * Filter: pmc_video_playlist_manager_playlist_taxonomy to override 'playlist taxonomy' data
		 */
		$this->_playlist_taxonomy = apply_filters('pmc_video_playlist_manager_playlist_taxonomy', $this->_playlist_taxonomy);

		/**
		 * Filter: pmc_video_playlist_manager_video_posttype to override 'video post type' data
		 */
		$this->_video_posttype = apply_filters('pmc_video_playlist_manager_video_posttype', $this->_video_posttype);

		$this->timezone = get_option('timezone_string');
		$this->timezone = (empty($this->timezone)) ? get_option('gmt_offset') : $this->timezone;
		$this->timezone = (empty($this->timezone)) ? $this->default_timezone : $this->timezone;

		$this->setup_post_type();

		$this->setup_admin();
	}

	/**
	 * @return string returns _playlist_taxonomy value
	 */
	public function get_playlist_taxonomy()
	{
		return $this->_playlist_taxonomy;
	}

	/**
	 * @return string returns _video_posttype value
	 */
	public function get_video_posttype()
	{
		return $this->_video_posttype;
	}

	/**
	 * @return array returns _targeted_taxonomies value
	 */
	public function get_targeted_taxonomies()
	{
		return $this->_targeted_taxonomies;
	}

	/**
	 * Create custom post type.
	 */
	public function setup_post_type()
	{
		register_post_type(self::POST_TYPE, array(
			'label'               => wp_strip_all_tags(__('Video Playlist Manager', 'pmc-video-playlist-manager')),
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'has_archive'         => false,
			'hierarchical'        => false,
			'supports'            => false,
			'rewrite'             => false,
		));
	}

	/**
	 * Setup the admin interface.
	 */
	public function setup_admin()
	{

		if (!is_admin()) {
			return;
		}

		// To add this menu under curation (pmc-core) priority used as 20. All action for submenu added under this menu has 20 priority in pmc-core.
		add_action('admin_menu', [$this, 'add_admin_menu'], 20);
		add_action('admin_enqueue_scripts', [$this, 'setup_admin_assets']);
		add_action('wp_ajax_pvm_view', [$this, 'ajax_render_form']);
		add_action('wp_ajax_pvm_crud', [$this, 'ajax_handle_form']);
		add_action('wp_ajax_search_playlist', [$this, 'ajax_search_playlist']);
		add_action('wp_ajax_get_playlist_details', [$this, 'ajax_get_playlist_details']);
		add_action('wp_ajax_search_post_term', [$this, 'ajax_search_post_term']);
	}

	/**
	 * Adds PMC Video Module menu to admin dashboard
	 */
	function add_admin_menu()
	{

		$parent_menu_slug = apply_filters('pmc_video_playlist_manager_parent_menu_slug', 'tools.php');

		$this->_menu_slug = add_submenu_page($parent_menu_slug, wp_strip_all_tags(__('PMC Video Module', 'pmc-video-playlist-manager')), wp_strip_all_tags(__('PMC Video Module', 'pmc-video-playlist-manager')), 'edit_posts', 'video-manager', [
			$this,
			'render_admin',
		]);
	}

	/**
	 * Render the view template for pmc video management.
	 */
	public function render_admin()
	{

		$total_post_count = wp_count_posts(self::POST_TYPE);

		$total_post_count = (!empty($total_post_count->publish) && intval($total_post_count->publish) > 0) ? intval($total_post_count->publish) : 0;

		$total_page_count = ceil($total_post_count / self::POSTS_PER_PAGE);

		$paged = \PMC::filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT);
		$paged = (!empty($paged) && intval($paged) > 0) ? intval($paged) : 1;

		$args = array(
			'posts_per_page' => self::POSTS_PER_PAGE,
			'paged'          => $paged,
			'post_status'    => array('publish', 'draft'),
		);

		\PMC::render_template(PMC_VIDEO_PLAYLIST_MANAGER_ROOT . '/templates/admin.php', array(
			'video_posts'  => $this->get_video_playlist_manager_posts($args),
			'manager'      => $this,
			'post_count'   => $total_post_count,
			'current_page' => $paged,
			'total_page'   => $total_page_count,
		), true);
	}

	/**
	 * Add JS and CSS for manager page only.
	 *
	 * @param string $hook Current page name
	 */
	public function setup_admin_assets($hook)
	{

		if ($this->_menu_slug !== $hook) {
			return;
		}

		$js_ext = (\PMC::is_production()) ? '.min.js' : '.js';

		wp_enqueue_style('pmc-video-manager-style-admin', sprintf('%sassets/css/pmc-video-playlist-manager-admin.min.css', PMC_VIDEO_PLAYLIST_MANAGER_URL), array(), PMC_VIDEO_PLAYLIST_MANAGER_ROOT);

		wp_enqueue_script('pmc-video-playlist-manager', sprintf('%sassets/js/pmc-video-playlist-manager-admin%s', PMC_VIDEO_PLAYLIST_MANAGER_URL, $js_ext), array(
			'jquery',
			'jquery-ui-autocomplete',
		), PMC_VIDEO_PLAYLIST_MANAGER_VERSION);

		wp_localize_script('pmc-video-playlist-manager', 'PMC_VIDEO_PLAYLIST_MANAGER', array(
			'url'   => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('pvm-admin-action'),
		));
	}

	/**
	 * Return all posts with the type of "pmc-video-manager" and unserialize their data.
	 *
	 * @param  array $args To modify argument which will pass in get_posts().
	 * @param  bool  $cache Whether from cache or not.
	 *
	 * @return array
	 */
	public function get_video_playlist_manager_posts(array $args = [], $cache = false)
	{

		if (true === $cache) {
			ksort($args);
			$cache_key = 'get_pvm-' . md5(maybe_serialize($args));

			$cache = new \PMC_Cache($cache_key, self::CACHE_GROUP);

			return $cache->expires_in(self::CACHE_LIFE)->updates_with([
				$this,
				'get_uncached_video_playlist_manager_posts',
			], [$args])->get();
		}

		return $this->get_uncached_video_playlist_manager_posts($args);
	}

	/**
	 * Return all posts with the type of "pmc-video-manager" and unserialize their data. (uncached version)
	 *
	 * @param  array $args To modify argument which will pass in get_posts().
	 * @return array
	 */
	public function get_uncached_video_playlist_manager_posts($args = [])
	{

		$posts = array();

		$default_args = array(
			'posts_per_page'      => self::POSTS_PER_PAGE,
			'post_type'           => self::POST_TYPE,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
		);

		$args = wp_parse_args($args, $default_args);

		$query = new \WP_Query($args);

		if ($query->have_posts()) {

			$posts = $query->posts;

			if ($posts) {
				foreach ($posts as $post) {
					$post->post_content = json_decode($post->post_content, true);
				}
			}
		}

		return $posts;
	}

	/**
	 * Get an Video Manager by ID.
	 *
	 * @param int $post_id | WP_Post object
	 *
	 * @return \WP_Post | null
	 */
	public function get_video_playlist_manager_post($post_id)
	{

		if (!$post_id) {
			return null;
		}

		$post = get_post($post_id);

		if ($post) {
			$post->post_content = json_decode($post->post_content, true);

			return $post;
		}

		return null;
	}

	/**
	 * Render the form for creating/updating Video Playlist.
	 */
	public function ajax_render_form()
	{

		$nonce = \PMC::filter_input(INPUT_GET, 'nonce', FILTER_SANITIZE_STRING);

		if (!wp_verify_nonce($nonce, 'pvm-admin-action')) {
			wp_die('<div class="error"><p><strong>' . esc_html__('Nonce Verification failed please try again!', 'pmc-video-playlist-manager') . '</strong></p></div>');
		}

		$post_id         = \PMC::filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
		$video           = $this->get_video_playlist_manager_post($post_id);
		$featured_videos = false;
		$playlist        = empty($video->post_content['playlist']) ? '' : $video->post_content['playlist'];
		$featured_video  = empty($video->post_content['featured-video']) ? '' : $video->post_content['featured-video'];
		$video_count     = empty($video->post_content['video-count']) ? 5 : absint($video->post_content['video-count']);

		if ($video && !empty($playlist)) {
			$featured_videos = $this->get_playlist_details($playlist, $video_count, $featured_video);
			$featured_videos = (is_array($featured_videos) && key_exists('items', $featured_videos)) ? $featured_videos['items'] : array();
		}

		$template_vars = array(
			'video'               => $video,
			'manager'             => $this,
			'featured_videos'     => $featured_videos,
			'targeted_taxonomies' => $this->get_targeted_taxonomies(),
		);

		\PMC::render_template(PMC_VIDEO_PLAYLIST_MANAGER_ROOT . '/templates/edit-form.php', $template_vars, true);

		exit();
	}

	/**
	 * Handle AJAX form submission: create Video Playlist, update Video Playlist, etc.
	 */
	public function ajax_handle_form()
	{

		$success = true;
		$message = null;
		$nonce   = \PMC::filter_input(INPUT_POST, 'nonce', FILTER_SANITIZE_STRING);

		try {

			if (!wp_verify_nonce($nonce, 'pvm-admin-action')) {
				throw new \Exception(__(' - Nonce Verification failed please try again!', 'pmc-video-playlist-manager'));
			}

			$term_array = array();
			$query      = array(
				'post_type' => self::POST_TYPE,
			);
			$method     = \PMC::filter_input(INPUT_POST, 'method', FILTER_SANITIZE_STRING);
			$pvm_id     = \PMC::filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
			$pvm_title  = \PMC::filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
			$pvm_status = \PMC::filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

			switch (sanitize_title($method)) {

				case 'edit':
					$query['ID'] = $pvm_id;
					// fall-through -- Skipped break;

				case 'add':
					$query['post_title']  = (!empty($pvm_title)) ? $pvm_title : 'Related Videos';
					$query['post_status'] = (!empty($pvm_status)) ? 'publish' : 'draft';

					// Save shared data
					$data                   = array();
					$data['title']          = sanitize_title($query['post_title']);   //for saving in post data
					$data['playlist']       = \PMC::filter_input(INPUT_POST, 'playlist', FILTER_SANITIZE_STRING);
					$data['video-count']    = \PMC::filter_input(INPUT_POST, 'video-count', FILTER_SANITIZE_NUMBER_INT);
					$data['priority']       = \PMC::filter_input(INPUT_POST, 'priority', FILTER_SANITIZE_NUMBER_INT);
					$data['featured-video'] = \PMC::filter_input(INPUT_POST, 'featured-video', FILTER_SANITIZE_STRING);
					$data['start']          = null;
					$data['end']            = null;

					//save targeting pairs
					$targeting_tax  = \PMC::filter_input(INPUT_POST, 'target-tax', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
					$targeting_term = \PMC::filter_input(INPUT_POST, 'target-term', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
					$post_excerpt   = '';

					if (!empty($targeting_tax)) {

						$count          = count($targeting_tax);
						$targeting_data = array();

						for ($i = 0; $i < $count; $i++) {

							$taxonomy = (!empty($targeting_tax[$i])) ? sanitize_text_field($targeting_tax[$i]) : '';
							$terms    = (!empty($targeting_term[$i])) ? sanitize_text_field($targeting_term[$i]) : '';

							if (empty($taxonomy) || empty($terms)) {
								continue;
							}

							$targeted_taxonomies = $this->get_targeted_taxonomies();
							$term_list           = explode(',', $terms);
							$term_list           = array_filter($term_list);
							$tax                 = (is_array($targeted_taxonomies) && array_key_exists($taxonomy, $targeted_taxonomies)) ? $targeted_taxonomies[$taxonomy] : '';

							foreach ($term_list as $term) {
								$term_id = term_exists(trim($term), $tax);

								if (is_array($term_id)) {
									$term_array[$tax][$term_id['term_id']] = $term;
								} else {
									throw new \Exception(sprintf('"%s" does not exist. Please select valid term from list.', trim($term)));
								}
							}

							$targeting_data[] = array(
								'taxonomy' => $taxonomy,
								'terms'    => htmlentities($terms),
							);

							$post_excerpt .= $taxonomy . ': ' . $terms . ' | ';
							unset($tax, $terms);
						}

						$data['targeting_data'] = $targeting_data;
					}

					//time magic
					$start = \PMC::filter_input(INPUT_POST, 'start', FILTER_SANITIZE_STRING);
					$end   = \PMC::filter_input(INPUT_POST, 'end', FILTER_SANITIZE_STRING);

					if (date('Y-m-d', strtotime($start)) === $start) {
						$start = date('Y-m-d H:i', strtotime($start . ' 00:00'));
					}

					if (date('Y-m-d', strtotime($end)) === $end) {
						$end = date('Y-m-d H:i', strtotime($end . ' 23:59'));
					}

					if (date('Y-m-d H:i', strtotime($start)) === $start && date('Y-m-d H:i', strtotime($end)) === $end) {
						if (date('U', strtotime($start)) > date('U', strtotime($end))) {
							$data['start'] = $end;
							$data['end']   = $start;
						} else {
							$data['start'] = $start;
							$data['end']   = $end;
						}
					}

					// Add to query
					$query['post_content'] = wp_json_encode($data);
					$query['menu_order']   = $data['priority'];
					$query['post_excerpt'] = $post_excerpt;
					$this->save_video_playlist($query, $term_array);
					break;

				case 'delete':
					$pvm_ids = \PMC::filter_input(INPUT_POST, 'post_ids', FILTER_SANITIZE_STRING);

					if (!empty($pvm_ids)) {

						$post_ids = explode(',', $pvm_ids);

						foreach ($post_ids as $post_id) {

							$post_id = intval($post_id);
							if ($post_id) {
								$this->_delete_video_playlist($post_id);
							}
						}
					} else {
						$pvm_id = \PMC::filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
						$this->_delete_video_playlist($pvm_id);
					}
					break;
			}
		} catch (\Exception $e) {

			$success = false;
			$message = $e->getMessage();
		}

		// Output response
		echo wp_json_encode([
			'success' => $success,
			'message' => $message,
		]);

		exit();
	}

	/**
	 * Insert or update the pvm in the posts table.
	 *
	 * @param $query array Data to save in post
	 * @param $term_array array to assign terms to post
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function save_video_playlist($query, $term_array)
	{

		if (empty($query['ID'])) {
			$log     = array();
			$post_id = wp_insert_post($query, true);
		} else {
			$log     = $this->get_last_modified_log($query['ID'], true);
			$post_id = wp_update_post($query, true);
		}

		if (is_wp_error($post_id)) {
			throw new \Exception($post_id->get_error_message());
		} else {
			update_post_meta($post_id, '_pvm_last_modified_log', $log);

			/*
			 * removing all terms from a post.
			 */
			foreach ($this->get_targeted_taxonomies() as $taxonomy) {
				wp_set_object_terms($post_id, null, $taxonomy);
			}

			foreach ($term_array as $taxonomy => $terms) {

				if (is_array($terms)) {
					$terms = array_keys((array) $terms);
					wp_set_object_terms($post_id, $terms, $taxonomy);
				}
			}
		}

		return true;
	}

	/**
	 * Delete the post by ID.
	 *
	 * @param int $post_id
	 *
	 * @return bool
	 */
	protected function _delete_video_playlist($post_id)
	{

		$pvm = $this->get_video_playlist_manager_post($post_id);

		if ($pvm) {
			wp_delete_post($pvm->ID, true);
		}

		return true;
	}

	/**
	 * Return an array containing a list of last 10 user IDs (with timestamps) who modified the PVM config
	 *
	 * @param int  $post_id
	 * @param bool $to_save
	 *
	 * @return array
	 */
	public function get_last_modified_log($post_id, $to_save = false)
	{

		$post_id = intval($post_id);

		if ($post_id < 1) {
			return array();
		}

		$log = get_post_meta($post_id, '_pvm_last_modified_log', true);

		if (empty($log) || !is_array($log)) {

			$log              = array();
			$video_playlist   = $this->get_video_playlist_manager_post($post_id);
			$last_modified_by = get_post_meta($post_id, '_pvm_modified_by', true);

			if (!empty($video_playlist) && !empty($last_modified_by)) {
				$last_modified_time         = \PMC_TimeMachine::create($this->timezone)->from_time('Y-m-d H:i:s', $video_playlist->post_modified)->format_as('U');
				$log[$last_modified_time] = $last_modified_by;
			}
		}

		if (true !== $to_save) {
			return $log;
		}

		$log[\PMC_TimeMachine::create($this->timezone)->format_as('U')] = get_current_user_id();
		ksort($log);
		$log = array_slice($log, -10, 10, true);    //just keep last 10 at most

		//delete old meta key, not needed anymore
		if (!empty($last_modified_by)) {
			delete_post_meta($post_id, '_pvm_modified_by');
		}

		return $log;
	}

	/**
	 * Handle Ajax request for auto-complete playlist search feature.
	 *
	 * @since 1.0.0
	 */
	public function ajax_search_playlist()
	{

		$nonce  = \PMC::filter_input(INPUT_POST, 'nonce', FILTER_SANITIZE_STRING);
		$search = \PMC::filter_input(INPUT_POST, 'search', FILTER_SANITIZE_STRING);

		if (!wp_verify_nonce($nonce, 'pvm-admin-action')) {
			wp_send_json_error([
				[
					'value' => '',
					'label' => __('Nonce Verification failed please try again!', 'pmc-video-playlist-manager'),
				],
			]);
		}

		$terms = get_terms([
			'taxonomy'   => $this->get_playlist_taxonomy(),
			'hide_empty' => true,
			'name__like' => $search,
		]);

		$items = array();
		if (!empty($terms) && !is_wp_error($terms)) {
			foreach ($terms as $term) {
				$items[] = array(
					'label' => $term->slug,
					'value' => $term->name,
				);
			}
		}
		wp_send_json_success($items);
	}

	/**
	 * Handle Ajax request to get List of video posts for a playlist.
	 */
	public function ajax_get_playlist_details()
	{

		$nonce = \PMC::filter_input(INPUT_POST, 'nonce', FILTER_SANITIZE_STRING);

		if (!wp_verify_nonce($nonce, 'pvm-admin-action')) {
			wp_send_json_error(array('msg' => __('Nonce Verification failed please try again!', 'pmc-video-playlist-manager')));
		}

		$count    = \PMC::filter_input(INPUT_POST, 'count', FILTER_SANITIZE_STRING);
		$playlist = \PMC::filter_input(INPUT_POST, 'playlist', FILTER_SANITIZE_STRING);

		if (empty($playlist)) {
			wp_send_json_error();
		}

		$items = $this->get_playlist_details($playlist, $count);

		if (empty($items)) {
			wp_send_json_error(array('msg' => __('No video found in selected playlist!', 'pmc-video-playlist-manager')));
		}

		wp_send_json_success($items);
	}

	/**
	 * Handle Ajax request to get List of terms.
	 */
	public function ajax_search_post_term()
	{

		$nonce = \PMC::filter_input(INPUT_POST, 'nonce', FILTER_SANITIZE_STRING);

		if (!wp_verify_nonce($nonce, 'pvm-admin-action')) {
			wp_send_json_error(array('msg' => __('Nonce Verification failed please try again!', 'pmc-video-playlist-manager')));
		}

		$search   = \PMC::filter_input(INPUT_POST, 'search', FILTER_SANITIZE_STRING);
		$taxonomy = \PMC::filter_input(INPUT_POST, 'taxonomy', FILTER_SANITIZE_STRING);

		$targeted_taxonomies = $this->get_targeted_taxonomies();

		if (is_array($targeted_taxonomies) && array_key_exists($taxonomy, $targeted_taxonomies)) {
			$taxonomy = $targeted_taxonomies[$taxonomy];
		} else {
			wp_send_json_error(array('msg' => __('Unknown taxonomy please try again!', 'pmc-video-playlist-manager')));
		}

		$terms = get_terms(array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'number'     => self::POSTS_PER_PAGE,
			'name__like' => $search,
		));

		$items = array();
		if (!empty($terms) && !is_wp_error($terms)) {
			foreach ($terms as $term) {
				$items[] = array(
					'label' => $term->slug,
					'value' => $term->name,
				);
			}
		}
		wp_send_json_success($items);
	}

	/**
	 * Fetch the video lists for specific playlist
	 * If video_id given than fetch it to show in featured list
	 *
	 * @param string $playlist term slug
	 * @param int    $count Numbeer of video posts to fetch from given playlist
	 * @param string $video_id ID to fetch in related video list
	 *
	 * @return \WP_Query|null
	 */
	public function get_playlist_videos($playlist, $count = 10, $video_id = '')
	{

		if (empty($playlist)) {
			return null;
		}

		$args = array(
			'posts_per_page'      => absint($count),
			'post_type'           => $this->get_video_posttype(),
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'tax_query'           => array( // WPCS: slow query ok.
				array(
					'taxonomy' => $this->get_playlist_taxonomy(),
					'field'    => 'name',
					'terms'    => $playlist,
				),
			),
		);

		if (!empty($video_id) && is_integer($video_id)) {
			$args['post__in'] = array($video_id);
			$args['orderby']  = 'post__in';
		}

		$video_posts = new \WP_Query($args);

		return $video_posts;
	}

	/**
	 * Fetch the video lists for specific playlist
	 * If video_id given than fetch it to show in featured list
	 *
	 * @param string $playlist term slug
	 * @param int    $count Number of video posts to fetch from given playlist
	 * @param string $video_id ID to fetch in related video list (featured video)
	 *
	 * @return array
	 */
	function get_playlist_details($playlist, $count = 10, $video_id = '')
	{

		$items = array();

		if (empty($playlist)) {
			return $items;
		}

		$video_posts = $this->get_playlist_videos($playlist, $count, $video_id);

		if (!empty($video_posts->posts)) {

			$term             = $video_posts->get_queried_object();
			$items['count']   = (isset($term->count)) ? $term->count : 0;
			$items['items'][] = __('Select video from list...', 'pmc-video-playlist-manager');

			foreach ($video_posts->posts as $key => $post) {
				$items['items'][$post->ID] = $post->post_title;
			}
		}

		return $items;
	}
}
