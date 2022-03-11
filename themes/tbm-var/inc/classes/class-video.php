<?php

/**
 * Class Video
 *
 * Handlers for the Video templates.
 *
 * Terminology:
 *
 * The Video "Landing Page" refers to the
 * variety_top_video CPT archive template.
 *
 * A "vcategory" taxonomy term is synonymous with
 * a "Video Category" or "Playlist."
 *
 * Additionally, a video "Section Front" refers
 * to a vcategory term archive.
 *
 * In this file, "vcategory" is always used.
 *
 * @see Variety_Top_Video
 * @package pmc-variety-2017
 * @since 2017.1.0
 *
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC;

/**
 * Class Video
 *
 * @since 2017.1.0
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Video
{

	use Singleton;

	/**
	 * "Per page" value of the "Explore All Categories"
	 * block on the Video Archive template.
	 */
	const EXPLORE_ALL_GRID_PER_PAGE = 8;

	/**
	 * "Per page" value of the Grid sections
	 * block on the Vcategory Archive template.
	 */
	const TAX_ARCHIVE_GRID_PER_PAGE = 3;


	/**
	 * "per page" value on vcat archive pages
	 * @var integer
	 */
	const TAX_ARCHIVE_POSTS_PER_PAGE = 15;

	/**
	 * Simplified way to grab the vcategory slug.
	 *
	 * @var string
	 */
	public $tax = 'vcategory';

	/**
	 * Term Meta Fields Nonce.
	 *
	 * @var array Nonce data.
	 */
	private $_meta_nonce = array(
		'name'   => 'vcategory-meta-nonce',
		'action' => 'update-fields',
	);

	/**
	 * Ajax load Nonce.
	 *
	 * @var string Nonce data.
	 */
	protected $_ajax_nonce = 'video-ajax-nonce';

	/**
	 * Class constructor.
	 */
	protected function __construct()
	{

		// Term Meta fields.
		add_action($this->tax . '_add_form_fields', [$this, 'add_term_fields']);
		add_action($this->tax . '_edit_form_fields', [$this, 'edit_term_fields']);
		add_action('created_' . $this->tax, [$this, 'save_term_meta']);
		add_action('edited_' . $this->tax, [$this, 'save_term_meta']);
		add_action('admin_enqueue_scripts', [$this, 'action_admin_enqueue_scripts']);
		add_action('pre_get_posts', [$this, 'modify_main_vcat_query']);
		add_action('pre_get_posts', [$this, 'add_video_to_home_tag_author_pages'], 11);
		add_action('wp_footer', [$this, 'enqueue_assets'], 11);
	}

	/**
	 * modifies the query for pagination
	 * @param  object $query query object
	 * @return object modified query (only used in unit test)
	 */
	public function modify_main_vcat_query($query)
	{

		if (!is_admin() && $query->is_main_query() && is_tax('vcategory')) {
			$query->set('post_type', 'variety_top_video');
			$query->set('posts_per_page', self::TAX_ARCHIVE_POSTS_PER_PAGE);
		}

		return $query;
	}

	/**
	 * Get vcategory Posts
	 *
	 * \WP_Query to get posts from a specific vcategory term.
	 *
	 * Note: Fetches 30 posts.  Use the $count param to trim down
	 * the query result.
	 *
	 * The remove_first param exists to account for the first article appearing
	 * as the featured video in the vcategory taxonomy template.
	 *
	 * @since 2017.1.0
	 *
	 * @param int  $term_id A \WP_Term ID.
	 * @param int  $count Optional. Count of Posts.
	 * @param int  $page Optional.  Used to calculate the offset.
	 * @param bool $remove_first Optional.  Maybe remove the first article.
	 *
	 * @return array Array of posts, else an empty array.
	 */
	public function get_vcat_posts($term_id, $count = 0, $page = 0, $remove_first = false)
	{
		if (!is_numeric($term_id)) {
			return array();
		}

		$count     = (intval($count) > 0) ? intval($count) : self::TAX_ARCHIVE_GRID_PER_PAGE;
		$offset    = $count * $page;
		$offset    = (true === $remove_first) ? $offset + 1 : $offset;
		$cache_key = sanitize_key('variety_vcat_posts_term_id_' . $term_id . $offset . $count);
		$pmc_cache = new \PMC_Cache($cache_key);

		// Cache for 5 min.
		$cache_data = $pmc_cache->expires_in(300)
			->updates_with(
				[
					$this,
					'vcat_posts_query',
				],
				[
					'term_id'        => $term_id,
					'offset'         => $offset,
					'posts_per_page' => $count,
				]
			)
			->get();

		if (empty($cache_data) || !is_array($cache_data) || is_wp_error($cache_data)) {
			return array();
		}

		return $cache_data;
	}

	/**
	 * vcategory Posts Query
	 *
	 * A WP_Query to fetch posts to be cached in get_vcat_posts.
	 *
	 * Fetches 50 posts by default so that the results can be manipulated later
	 * by other methods, instead of running a query for each desired post count.
	 *
	 * Note that this returns "none" to be cached if there are no results so that
	 * this query isn't run continually if the query result is empty.
	 *
	 * @since 2017.1.0
	 * @see $this->get_vcat_posts
	 * @param int $term_id A \WP_Term term_id.
	 *
	 * @return array Array of posts, else "none".
	 */
	public function vcat_posts_query($term_id, $offset, $posts_per_page)
	{
		if (!is_numeric($term_id)) {
			return array();
		}

		$query = new \WP_Query(
			array(
				'post_type' => \Variety_Top_Videos::POST_TYPE_NAME,
				'posts_per_page' => $posts_per_page,
				'offset'         => $offset,
				'tax_query'      => array(
					array(
						'taxonomy'         => $this->tax,
						'field'            => 'term_id',
						'terms'            => array($term_id),
						'include_children' => false,
					),
				),
			)
		);

		if (!empty($query->posts) && is_array($query->posts)) {
			return $query->posts;
		}

		return array();
	}

	/**
	 * Get Taxonomy vcategory Data
	 *
	 * Fetches posts for the taxonomy-vcategory.php template,
	 * 5 posts at a time, and formats them to be used in featured
	 * grid cards.
	 *
	 * @param int $grid_count Optional. The number of Feature Grids to generate.
	 * @param int $page Optional. The page of posts to return.
	 * @param int $term_id Optional. The Term ID.
	 *
	 * @return array Array of data to use to populate Featured Grids.
	 */
	public function get_taxonomy_vcat_data($grid_count = 0, $page = 0, $term_id = 0, $first = false, $posts_per_grid = 5): array
	{
		if (empty($term_id) || !is_numeric($term_id)) {
			$term = get_queried_object();
		} else {
			$term = get_term_by('id', $term_id, $this->tax);
		}

		if (empty($term->term_id) || is_wp_error($term)) {
			return array();
		}

		if (empty($grid_count)) {
			$grid_count = self::TAX_ARCHIVE_GRID_PER_PAGE;
		}

		$start_slice_pos = 0;
		$post_count      = $grid_count * $posts_per_grid;

		$grids = array();
		$posts = $this->get_vcat_posts($term->term_id, $post_count, $page, $first);

		if (empty($posts) || !is_array($posts)) {
			return array();
		}

		// If we're on an even page number, switch the layout styles.
		$invert = (0 === $page % 2);

		// Loop through posts and set grid layout options.
		for ($i = 1; $i <= $grid_count; $i++) {

			$grids[] = array(
				'is_inverted'      => ((0 === $i % 2) === $invert),
				'is_reduced'       => true,
				'posts'            => array_slice($posts, $start_slice_pos, $posts_per_grid),
				'show_leaderboard' => (0 === $i % 2),
			);

			// Stop the loop if there are no more grids to create.
			if (count($posts) < ($i * $posts_per_grid)) {
				break;
			}

			$start_slice_pos = $start_slice_pos + $posts_per_grid;
		}

		return $grids;
	}

	/**
	 * Get Video Archive vcategory Data
	 *
	 * Fetches the Active Playlists from the Video Settings page and
	 * returns their data formatted so that it can be used on the Video CPT
	 * archive.
	 *
	 * @since 2017.1.0
	 * @return array Array of card data.
	 */
	public function get_video_archive_vcat_data($channel)
	{
		$archive_vcats = \Variety_Top_Videos_Settings::get_option($channel);
		if (empty($archive_vcats) || !is_array($archive_vcats)) {
			return array();
		}

		$data = array();
		/*
		 * This integer is used to determine if the added vcat should have
		 * a background and if it should be inverted, by checking if the integer is even.
		 */
		$i = 1;
		foreach ($archive_vcats as $name) {

			if (empty($name)) {
				continue;
			}

			$term = get_term_by('name', $name, $this->tax);

			if (empty($term->term_id)) {
				// Since this item in the array doesn't exist, do not increment counter.
				continue;
			}

			$data[] = array(
				'heading'        => ucwords($term->name),
				'more_text'      => sprintf(__('More %s', 'pmc-variety'), ucwords($term->name)),
				'more_link'      => get_term_link($term, $this->tax),
				'has_background' => (0 !== $i % 2),
				'is_inverted'    => (0 === $i % 2),
				'is_reduced'     => false,
				'posts'          => $this->get_vcat_posts($term->term_id, 5),
				'has_sponsor'    => !empty(get_term_meta($term->term_id, 'vcat-sponsored-text', true)),
				'sponsor'        => array(
					'text'    => get_term_meta($term->term_id, 'vcat-sponsored-text', true),
					'name'    => get_term_meta($term->term_id, 'vcat-sponsor-name', true),
					'link'    => get_term_meta($term->term_id, 'vcat-sponsor-link', true),
					'logo_id' => get_term_meta($term->term_id, 'vcat-logo-id', true),
				),
			);
			$i++;
		}

		if (!empty($data) && is_array($data)) {
			return $data;
		}

		return array();
	}

	/**
	 * Get Grid vcategory Terms
	 *
	 * Fetches all vcategory terms and returns the
	 * data in a form that can be used by other cards, which
	 * typically use \WP_Post data.
	 *
	 * This is used in the "Explore all Categories" grid
	 * at the bottom of the Video CPT archive.
	 *
	 * @since 2017.1.0
	 * @param int $page Optional.  The page number to fetch.
	 * @param int $count Optional. The number of term cards to eventually render.
	 *
	 * @return array Array of Term data in object form.
	 */
	public function get_grid_vcat_terms($page = 0, $count = 0)
	{
		if (empty($count) || !is_numeric($count)) {
			$count = self::EXPLORE_ALL_GRID_PER_PAGE;
		}
		$output = array();
		$offset = $page * $count;

		// Hhis hides empty terms.
		$terms = get_terms($this->tax, array('number' => $count, 'offset' => $offset));
		if (empty($terms) || !is_array($terms)) {
			return $output;
		}
		foreach ($terms as $term) {
			if (empty($term->term_id)) {
				continue;
			}
			// This matches format from variety_normalize_post() for continuity.
			$term_obj             = new \stdClass();
			$term_obj->url        = get_term_link($term, $this->tax);
			$term_obj->ID         = $term->term_id;
			$term_obj->image      = $this->get_term_image_src($term);
			$term_obj->post_title = $term->name;
			$term_obj->normalized = true;
			$output[] = $term_obj;
		}
		return $output;
	}

	/**
	 * Get Term Image src
	 *
	 * Finds the Playlist Image URL.
	 *
	 * This value is set in the "Edit Term" screen
	 * in the "vcategory" taxonomy.
	 *
	 * This is used in the "Explore all Categories" grid
	 * at the bottom of the Video CPT archive.
	 *
	 * @since 2017.1.0
	 * @param object $term A vcategory \WP_Term object.
	 *
	 * @return string An image URL.
	 */
	public function get_term_image_src($term)
	{
		$playlist_image_id = get_term_meta($term->term_id, 'vcat-image-id', true);
		$playlist_image_id = !empty($playlist_image_id) ? $playlist_image_id : '';
		$url = wp_get_attachment_image_url($playlist_image_id, 'landscape-small');
		if (!empty($url) && is_string($url)) {
			return $url;
		}

		// Return the default Photon image.
		if (function_exists('jetpack_photon_url')) {
			$url = jetpack_photon_url('https://pmcvariety.files.wordpress.com/2013/02/defaultwebimage_640-480.png');
			if (!empty($url)) {
				return $url;
			}
		}

		// Return the PMC Core transparent image.
		return get_template_directory_uri() . '/static/images/trans.gif';
	}

	/**
	 * Add Term Fields
	 *
	 * Adds a Sponsor uploader to the "Add New VCategory" section
	 * of the Video Categories taxonomy screen.
	 *
	 * @since 2017.1.0
	 * @action vcategory_add_form_fields
	 */
	public function add_term_fields()
	{
		echo PMC::render_template(CHILD_THEME_PATH . '/template-parts/admin/term-vcategory-add.php', array(
			'nonce' => $this->_meta_nonce,
		));
	}

	/**
	 * Edit Term Fields
	 *
	 * Renders the Sponsor image uploader to the Video Category term edit
	 * screen.
	 *
	 * @since 2017.1.0
	 * @action vcategory_edit_form_fields
	 *
	 * @param object $term The current \WP_Term object.
	 */
	public function edit_term_fields($term)
	{
		if (isset($term->term_id)) {
			return;
		}
		$playlist_img_id = get_term_meta($term->term_id, 'vcat-image-id', true);
		$playlist_img_id = !empty($playlist_img_id) ? $playlist_img_id : '';
		$playlist_img_src  = wp_get_attachment_image_url($playlist_img_id, 'landscape-large');

		$sponsor_img_id = get_term_meta($term->term_id, 'vcat-logo-id', true);
		$sponsor_img_id = !empty($sponsor_img_id) ? $sponsor_img_id : '';
		$sponsor_img_src = wp_get_attachment_image_url($sponsor_img_id, 'landscape-large');

		// These will be validated in the template part.
		$options = array(
			'playlist' => array(
				'img_id'  => $playlist_img_id,
				'img_src' => $playlist_img_src,
			),
			'sponsor'  => array(
				'text'    => get_term_meta($term->term_id, 'vcat-sponsored-text', true),
				'name'    => get_term_meta($term->term_id, 'vcat-sponsor-name', true),
				'link'    => get_term_meta($term->term_id, 'vcat-sponsor-link', true),
				'img_id'  => $sponsor_img_id,
				'img_src' => $sponsor_img_src,
			),
		);

		echo PMC::render_template(CHILD_THEME_PATH . '/template-parts/admin/term-vcategory-edit.php', array(
			'options' => $options,
			'nonce'   => $this->_meta_nonce,
		));
	}

	/**
	 * Save Term Fields
	 *
	 * Saves the Term Sponsor image when either creating or editing a
	 * Video Category Term.
	 *
	 * @since 2017.1.0
	 * @action created_vcategory
	 * @action edited_vcategory
	 *
	 * @param int $term_id The present \WP_Term ID.
	 */
	public function save_term_meta($term_id)
	{
		if (
			empty($_POST[$this->_meta_nonce['name']]) // WPCS: Input var okay.
			|| !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$this->_meta_nonce['name']])), $this->_meta_nonce['action']) // WPCS: Input var okay.
		) {
			return;
		}

		$fields = array(
			'vcat-image-id',
			'vcat-sponsored-text',
			'vcat-sponsor-name',
			'vcat-sponsor-link',
			'vcat-logo-id',
		);

		foreach ($fields as $field) {
			$value = '';
			if (!empty($_POST[$field])) { // WPCS: Input var okay.
				$value = sanitize_text_field(wp_unslash($_POST[$field])); // WPCS: Input var okay.
			}
			update_term_meta($term_id, $field, $value);
		}
	}

	/**
	 * Admin Enqueue Scripts
	 *
	 * @since 2017.1.0
	 * @action admin_enqueue_scripts
	 *
	 * @param string $page The current Admin Screen.
	 */
	public function action_admin_enqueue_scripts($page)
	{
		if ('term.php' !== $page && 'edit-tags.php' !== $page) {
			return;
		}
		wp_register_script('variety-vcat-term-js', get_stylesheet_directory_uri() . '/plugins/variety-top-videos/js/edit-term.js', array('jquery', 'wp-util'), false, true);

		$exports = array(
			'modalTitle' => __('Select or Upload an Image', 'pmc-variety'),
			'buttonText' => __('Insert Image', 'pmc-variety'),
			'elements'    => array(
				'.vcat-image',
				'.vcat-logo',
			),
		);

		wp_scripts()->add_data(
			'variety-vcat-term-js',
			'data',
			sprintf('var _varietyVideoCategoryExports = %s;', wp_json_encode($exports))
		);
		wp_add_inline_script('variety-vcat-term-js', 'varietyVideoCategory.init();', 'after');
		wp_enqueue_script('variety-vcat-term-js');
	}

	/**
	 * Populate a video data node from a post object.
	 *
	 * @param array    $item The node to populate/override.
	 * @param \WP_Post $_post The WP Post object.
	 *
	 * @return array
	 *
	 */
	public function populate_video_data($item, $_post)
	{
		$item['o_video_card_permalink_url']  = get_the_permalink($_post);
		$item['c_heading']['c_heading_text'] = get_the_title($_post);
		$item['c_heading']['c_heading_url']  = get_the_permalink($_post);

		if (!empty($_post->image_id)) {
			$thumbnail = $_post->image_id;
		} else {
			$thumbnail = get_post_thumbnail_id($_post);
		}

		if (!empty($thumbnail)) {
			$image = \PMC\Core\Inc\Media::get_instance()->get_image_data($thumbnail, 'landscape-large');

			$item['o_video_card_alt_attr']       = $image['image_alt'];
			$item['o_video_card_image_url']      = $image['src'];
			$item['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
			$item['o_video_card_caption_text']   = $image['image_caption'];
		} else {
			$item['o_video_card_alt_attr']       = '';
			$item['o_video_card_image_url']      = '';
			$item['o_video_card_lazy_image_url'] = \PMC\Core\Inc\Media::get_instance()->get_placeholder_img_url();
			$item['o_video_card_caption_text']   = '';
		}

		$video_source = \Variety\Inc\Video::get_instance()->get_video_source($_post->ID);

		if (!empty($video_source)) {
			$item['o_video_card_link_showcase_trigger_data_attr'] = $video_source;
		} else {
			$item['o_video_card_link_showcase_trigger_data_attr'] = '';
		}

		$category = \PMC_Primary_Taxonomy::get_instance()->get_primary_taxonomy($_post->ID, $this->tax);

		if (!empty($category)) {
			$item['o_indicator']['c_span']['c_span_text'] = $category->name;
			$item['o_indicator']['c_span']['c_span_url']  = get_term_link($category);
		}

		$item['c_span']['c_span_text'] = get_post_meta($_post->ID, 'variety_top_video_duration', true);

		return $item;
	}

	/**
	 * Get the formatted video source for a post.
	 *
	 * @param \WP_Post|null $post_id The ID of the post.
	 *
	 * @return string
	 *
	 */
	public function get_video_source($post_id = null)
	{
		if (empty($post_id)) {
			$post_id = get_the_ID();
		}

		$video_source = get_post_meta($post_id, 'variety_top_video_source', true);
		$video_source = variety_filter_youtube_url($video_source);

		// For YouTube, apply an iFrame. Caters for youtu.be links.
		if (strpos($video_source, 'youtu') !== false) {
			$video_source = str_replace('www.', '', $video_source);

			if (strpos($video_source, 'youtu.be')) {
				$video_source = preg_replace('~^https?://youtu\.be/([a-z-\d_]+)$~i', 'https://www.youtube.com/embed/$1', $video_source);
			} elseif (strpos($video_source, 'youtube.com/watch')) {
				$video_source = preg_replace('~^https?://youtube\.com\/watch\?v=([a-z-\d_]+)$~i', 'https://www.youtube.com/embed/$1', $video_source);
			}
			$video_source .= '?enablejsapi=1&#038;origin=' . esc_url(site_url()) . '&#038;version=3&#038;rel=1&#038;fs=1&#038;autohide=2&#038;showsearch=0&#038;showinfo=1&#038;iv_load_policy=1&#038;wmode=transparentd&#038;autoplay=1';

			$video_source = "<div class='embed-youtube'><iframe type='text/html' width='670' height='407' src='" . esc_url($video_source) . "' allowfullscreen='true' allow='autoplay' style='border:0;'></iframe></div>";
		} elseif (strpos($video_source, 'jwplayer') !== false || strpos($video_source, 'jwplatform') !== false) {
			global $jwplayer_shortcode_embedded_players;

			$regex = '/\[jwplayer (?P<media>[0-9a-z]{8})(?:[-_])?(?P<player>[0-9a-z]{8})?\]/i';
			preg_match($regex, $video_source, $matches, null, 0);

			$player = (!empty($matches['player'])) ? $matches['player'] : false;
			$media  = (!empty($matches['media'])) ? $matches['media'] : false;
			$player = (false === $player) ? get_option('jwplayer_player') : $player;

			$content_mask = jwplayer_get_content_mask();
			$protocol     = (is_ssl() && defined('JWPLAYER_CONTENT_MASK') && JWPLAYER_CONTENT_MASK === $content_mask) ? 'https' : 'http';

			$json_feed = "$protocol://$content_mask/feeds/$media.json";

			if (false !== $player && !in_array($player, (array) $jwplayer_shortcode_embedded_players, true)) {
				$js_lib = "$protocol://$content_mask/libraries/$player.js";

				$jwplayer_shortcode_embedded_players[] = $player;
				// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				printf('<script onload="pmc_jwplayer.add()" type="text/javascript" src="%s"></script>', esc_url($js_lib));
			}

			$video_source = sprintf('<div id="jwplayer_%1$s_div" data-videoid="%1$s" data-player="%2$s" data-jsonfeed="%3$s"></div>', esc_attr($media), esc_attr($player), esc_url($json_feed));
		} else {
			// Run it through the_content filter to process any oEmbed or Shortcode
			$video_source = apply_filters('the_content', $video_source);

			$allowed_tags = array(
				'span'   => array(
					'class' => array(),
					'style' => array(),
				),
				'iframe' => array(
					'class'           => array(),
					'type'            => array(),
					'width'           => array(),
					'height'          => array(),
					'src'             => array(),
					'data-src'        => array(),
					'allowfullscreen' => array(),
					'style'           => array(),
				),
			);

			$video_source = wp_kses($video_source, $allowed_tags);
		}

		return $video_source;
	}

	/**
	 * Check if a video is a JW Player video.
	 *
	 * @param string $video_source The video source or shortcode.
	 *
	 * @return bool
	 */
	public static function is_jw_player($video_source)
	{

		self::register_video_player_on_page($video_source);

		return strpos($video_source, 'jwplayer') !== false || strpos($video_source, 'jwplatform') !== false;
	}

	/**
	 * Check if a video is a YouTube video url.
	 *
	 * @param string $url The video url.
	 *
	 * @return bool
	 *
	 */
	public static function is_youtube($url)
	{
		$youtube_domain = [
			'youtu.be',
			'www.youtube.com',
			'youtube.com',
		];

		if (wpcom_vip_is_valid_domain($url, $youtube_domain)) {
			return true;
		}

		return false;
	}

	/**
	 * Return the ID of a JW video from a shortcode.
	 *
	 * @param string $shortcode The JW shortcode to parse.
	 * @return mixed|string
	 */
	public static function get_jw_id($shortcode)
	{
		$regex = '/\[jwplayer (?P<media>[0-9a-z]{8})(?:[-_])?(?P<player>[0-9a-z]{8})?\]/i';
		preg_match($regex, $shortcode, $matches, null, 0);
		return (!empty($matches['media'])) ? $matches['media'] : '';
	}

	/**
	 * Adds autoplay to a YouTube URL or embed.
	 *
	 * @param string $src The YouTube URL or embed code
	 *
	 * @return string
	 *
	 */
	public static function force_youtube_autoplay($src)
	{

		if (false === strpos($src, 'autoplay')) {
			if (self::is_youtube($src)) {
				$src = add_query_arg('autoplay', 1, $src);
			} elseif (!empty($src)) {
				// Find the src url.
				$doc = new \DOMDocument();
				$doc->loadHTML($src, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
				$iframes = $doc->getElementsByTagName('iframe');

				foreach ($iframes as $iframe) {
					$url = $iframe->getAttribute('src');

					if (self::is_youtube($url)) {
						$iframe->setAttribute('src', esc_url(add_query_arg('autoplay', 1, $url)));
					}
				}

				return trim($doc->saveHTML());
			}
		}

		return $src;
	}

	/**
	 * Add Videos to Home, Tag, and Author Pages
	 *
	 * @param $video_source
	 */
	public function add_video_to_home_tag_author_pages($query): void
	{

		if (is_admin() || !$query->is_main_query()) {
			return;
		}

		if (is_home() || is_tag() || is_author()) {

			$post_types = array_filter((array) $query->get('post_type'));

			if (empty($post_types)) {
				array_push($post_types, 'post');
			}

			array_push($post_types, 'variety_top_video');

			//The default is 'any' so all post types including variety top post will show by default
			$query->set('post_type', $post_types);
		}
	}

	/**
	 * Register JW Videos on the page, so we can render script for them.
	 *
	 * @param $video_source
	 */
	public static function register_video_player_on_page($video_source = '')
	{

		if (strpos($video_source, 'jwplayer') !== false || strpos($video_source, 'jwplatform') !== false) {

			global $jwplayer_shortcode_embedded_players;

			$regex = '/\[jwplayer (?P<media>[0-9a-z]{8})(?:[-_])?(?P<player>[0-9a-z]{8})?\]/i';
			preg_match($regex, $video_source, $matches, null, 0);

			$player = (!empty($matches['player'])) ? $matches['player'] : false;
			$player = (false === $player) ? get_option('jwplayer_player') : $player;

			if (false !== $player && !in_array($player, (array) $jwplayer_shortcode_embedded_players, true)) {
				$jwplayer_shortcode_embedded_players[] = $player;
			}
		}
	}

	/**
	 * Enqueue JWPlayer Scripts.
	 */
	public function enqueue_assets()
	{

		/* global $jwplayer_shortcode_embedded_players;

		$content_mask = \jwplayer_get_content_mask();
		$protocol     = ( is_ssl() && defined( 'JWPLAYER_CONTENT_MASK' ) && JWPLAYER_CONTENT_MASK === $content_mask ) ? 'https' : 'http';

		foreach ( (array) $jwplayer_shortcode_embedded_players as $player ) {
			$js_lib = "$protocol://$content_mask/libraries/$player.js";

			$jwplayer_shortcode_embedded_players[] = $player;
			wp_enqueue_script( 'variety-vip-jwscript-' . $player, $js_lib, [], '', true );
		} */
	}
}
