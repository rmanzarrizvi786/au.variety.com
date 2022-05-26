<?php

/**
 * Gallery Posts & Media Management
 *
 * This class adds the Gallery Images and Copy box
 * Enqueues ths required css and js
 * Also has customizations for expanding Title Box (Enhanced Captioning)
 * and Zoom Feature
 *
 * @package PMC Gallery Plugin
 * @since   1/1/2013 Vicky Biswas
 */

namespace PMC\Gallery\Admin;

use PMC\Global_Functions\Traits\Singleton;

class Media_Manager
{

	use Singleton;

	/**
	 * Manages the Settings for PMC Gallery
	 */
	protected function _init()
	{

		// For adding checkbox to enable zoom feature
		add_action('save_post', array($this, 'save_post'));

		// UI Simplification
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_ui_simplification_stuff'), 12);
		add_action('hidden_meta_boxes', array($this, 'hide_unneeded_metaboxes'), 12, 3);

		// Enhanced captioning for posts
		add_action('admin_footer-post-new.php', array($this, 'replace_tmpl_attachment'));
		add_action('admin_footer-post.php', array($this, 'replace_tmpl_attachment'));

		// Rendering the inline media manager on pmc-gallery edit post pages
		add_action('admin_enqueue_scripts', array($this, 'action_admin_enqueue_scripts'));
		add_action('add_meta_boxes', array($this, 'media_manager_meta_box'));
		add_action('wp_ajax_pmc_gallery_update', array($this, 'update_gallery_data'));
		add_filter('media_view_settings', array($this, 'data_to_gallery'), 10, 2);

		/**
		 * @ticket PPT-4241 WWD - Add Order by Filename button to Gallery Builder
		 * @since 2015-02-27 Archana Mandhare
		 */
		add_filter('media_view_strings', array($this, 'add_sort_buttons_to_gallery'), 10, 2);

		add_filter('manage_pmc-gallery_posts_columns', array($this, 'add_gallery_featured_image_columns'), 10, 1);
		add_action('manage_pmc-gallery_posts_custom_column', array($this, 'custom_gallery_featured_image_column'), 10, 2);

		add_action('before_delete_post', array($this, 'before_delete_post'));

		if (wp_doing_ajax()) {
			$this->add_attachment_hooks();
		}
	}

	/**
	 * Add attachment hooks.
	 */
	public function add_attachment_hooks()
	{
		$action  = \PMC::filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
		$post_id = \PMC::filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);

		/**
		 * If request is from pmc-gallery page then and then modify response of
		 * `query-attachment` other wise fallback to default WordPress function.
		 */
		if (!empty($action) && ('query-attachments' === $action || 'save-attachment' === $action || 'upload-attachment' === $action)) {
			if (!empty($post_id) && \PMC\Gallery\Defaults::NAME === get_post_type(intval($post_id))) {
				remove_action('wp_ajax_query-attachments', 'wp_ajax_query_attachments');
				add_action('wp_ajax_query-attachments', array($this, 'wp_ajax_query_attachments'), 1);
				add_filter('posts_search', array($this, 'post_search'), 10, 2);
				add_filter('wp_prepare_attachment_for_js', array($this, 'wp_prepare_attachment_for_js'), 1, 1);
			}
			add_action('wp_ajax_save-attachment', array($this, 'wp_ajax_save_attachment'), 0);
		}

		// To enable image url search in media.
		add_filter('wp_insert_attachment_data', array($this, 'wp_insert_attachment_data'), 15, 1);
		add_filter('ajax_query_attachments_args', array($this, 'maybe_disable_es_query'), 15, 1);
	}

	/**
	 * Pre attachment search filter. to modify search query.
	 *
	 * @global  object $wpdb
	 * @param   string    $sql search sql of.
	 * @param   \WP_Query $query WP_Query object of attachment search.
	 * @return  string
	 */
	public function post_search($sql, $query)
	{
		global $wpdb;

		if (!empty($query->query['s'])) {
			$term = $wpdb->esc_like($query->query['s']);
			$like = "%{$term}%";
			$sql  = $wpdb->prepare(" AND ((({$wpdb->posts}.post_title LIKE %s) OR ({$wpdb->posts}.post_excerpt LIKE %s) OR ({$wpdb->posts}.post_content LIKE %s) OR ({$wpdb->posts}.post_content_filtered LIKE %s)))", $like, $like, $like, $like);
		}

		return $sql;
	}

	/**
	 * Function will execute when any attachment created.
	 * It will add attachment url in description, it will search in media libraey search.
	 *
	 * @param array $data Attachment post data.
	 * @return array Attachment post data.
	 */
	public function wp_insert_attachment_data($data)
	{

		if (empty($data['guid'])) {
			return $data;
		}

		if (false === strpos($data['post_content_filtered'], $data['guid'])) {
			$data['post_content_filtered'] .= ' ' . $data['guid'];
		}
		return $data;
	}

	/**
	 * Prepares an attachment post object for JS, where it is expected
	 * to be JSON-encoded and fit into an Attachment model.
	 *
	 * @hooked wp_prepare_attachment_for_js
	 * @param array $response proccesed attachment data.
	 * @return array proccesed attachment data.
	 */
	public function wp_prepare_attachment_for_js($response)
	{
		static $attachment_counts     = array();
		$response['attachment_id']    = intval($response['id']);
		$response['gallery_id']       = false;
		$response['attachment_count'] = 0;
		return $response;
	}

	/**
	 * Function is pre hook of `wp_ajax_save-attachment`.
	 * It is used to handle 2 cases,
	 * CASE 1:
	 * It will change the change is made in custom gallery's attachment or not
	 * If yes then it will make update on perticuler gallery's attachment and
	 * prevent original attachment to being modify.
	 *
	 * CASE 2:
	 * Since, we are showing attachment and there variant,
	 * For that we need to modify id attribute of attachment @see $this->wp_ajax_query_attachments()
	 * So, when user modify content of attachment in request may have change of invalid id attribute.
	 * For that additional attribute `attachment_id` is passed in request,
	 * Therefore, in this function we are just replacing request's id attribute with attachment_id attribute,
	 * so default function can work perfectlly.
	 *
	 */
	public function wp_ajax_save_attachment()
	{
		$gallery_id    = \PMC::filter_input(INPUT_POST, 'gallery_id', FILTER_SANITIZE_NUMBER_INT);
		$attachment_id = \PMC::filter_input(INPUT_POST, 'attachment_id', FILTER_SANITIZE_NUMBER_INT);
		$id            = \PMC::filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
		$changes       = \PMC::filter_input(INPUT_POST, 'changes', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

		if ($gallery_id) {

			$attachment         = array();
			$response           = array();
			$gallery_attachment = \PMC\Gallery\Attachment_Detail::get_instance();

			$attachment_id = !$attachment_id ? intval($id) : false;

			foreach ($changes as $key => $value) {
				$attachment[$key] = $value;
			}

			unset($attachment['modified'], $attachment['modified_gmt']);

			$gallery_meta = get_post_meta($gallery_id, \PMC\Gallery\Defaults::NAME, true);

			if (!empty($gallery_meta) && is_array($gallery_meta)) {
				$gallery_meta = array_map('intval', (array) $gallery_meta);

				// Update post of private CPT associated with gallery post's attachment.
				if (in_array($attachment_id, (array) $gallery_meta, true)) {
					$variant_id = array_search($attachment_id, (array) $gallery_meta, true);
					$gallery_attachment->update_attachment_variant($variant_id, $attachment);
				}
			}

			$response['success'] = true;

			wp_send_json($response);
		} elseif ($attachment_id) {
			$id = absint($attachment_id);
		}

		// @see PMCP-1411
		// We need to continue process additional ajax callback events
		// do not terminate the process by calling wp_send_json

		if ($id) {
			$post               = \get_post($id, ARRAY_A);
			$content            = $post['post_content_filtered'];
			$gallery_attachment = \PMC\Gallery\Attachment_Detail::get_instance();
			$keywords           = $gallery_attachment->get_unique_word($changes);
			$keywords           = array_merge($keywords, explode(' ', $content));
			$keywords           = array_unique((array) $keywords);
			$content            = implode(' ', $keywords);
			$post_array         = array(
				'ID'                    => absint($id),
				'post_content_filtered' => sanitize_text_field($content),
				'meta_input'            => array(
					'search_keyword' => sanitize_text_field($content),
				),
			);

			wp_update_post($post_array);
		}
	}

	/**
	 * Gallery Post type media search operation callback function.
	 * This will only execute If it is search operation only in `Media Library`
	 * tab.
	 * Otherwise it will fallback to default `wp_ajax_query_attachments()`
	 * This function is replica of default WordPress `wp_ajax_query_attachments()`
	 * with custom changes.
	 *
	 * @hooked wp_ajax_query-attachments
	 */
	public function wp_ajax_query_attachments()
	{
		$_query  = \PMC::filter_input(INPUT_POST, 'query', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
		$post_id = \PMC::filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
		$query   = (!empty($_query)) ? (array) $_query : array();

		if (!current_user_can('upload_files')) {
			wp_send_json_error();
		}

		// No nonce here because the ajax request is made by core and wp_ajax_query_attachments() doesn't verify nonce in core.

		$posts = array();

		$keys = array(
			's',
			'order',
			'orderby',
			'posts_per_page',
			'paged',
			'post_mime_type',
			'post_parent',
			'post__in',
			'post__not_in',
			'year',
			'monthnum',
		);

		$search = false;

		foreach (get_taxonomies_for_attachments('objects') as $t) {
			if ($t->query_var && isset($query[$t->query_var])) {
				$keys[] = $t->query_var;
			}
		}

		$query = array_intersect_key($query, array_flip($keys));

		$query['post_type'] = array(
			'attachment',
			\PMC\Gallery\Attachment_Detail::NAME,
		);

		if (MEDIA_TRASH && !empty($query['post_status']) && 'trash' === $query['post_status']) {
			$query['post_status'] = 'trash';
		} else {
			$query['post_status'] = 'inherit';
		}

		if (current_user_can(get_post_type_object('attachment')->cap->read_private_posts)) {
			$query['post_status'] .= ',private';
		}

		$query['post_status'] .= ',publish';

		$query['no_found_rows'] = true;

		unset($query['post_mime_type']);

		// Filter query clauses to include filenames.
		if (isset($query['s'])) {
			add_filter('posts_clauses', '_filter_query_attachment_filenames');
			$search = $query['s'];
		}

		/**
		 * When request is from `Media Library` then
		 * remove attachment and there variant which is already in `Gallery`
		 * from result.
		 * Since, `Edit Gallery` tab executing same query,
		 * we won't perform in escaping.
		 * `post__in` checking that 'Is request for Edit gallery section ?'.
		 * so when `post__in` is available then we won't remove
		 * attachment and there variants
		 */
		$escape_attachment_ids = array();
		$current_post_id       = $post_id ? intval($post_id) : false;

		if ($current_post_id && is_numeric($current_post_id) && empty($query['post__in'])) {
			if (get_post_type($current_post_id) === \PMC\Gallery\Defaults::NAME) {
				$escape_attachment_ids = get_post_meta($current_post_id, \PMC\Gallery\Defaults::NAME, true);

				if (is_array($escape_attachment_ids)) {
					$escape_attachment_ids = array_values($escape_attachment_ids);
				} else {
					$escape_attachment_ids = array();
				}
			}
		}
		/**
		 * Filters the arguments passed to WP_Query during an Ajax
		 * call for querying attachments.
		 *
		 * @since 3.7.0
		 *
		 * @see WP_Query::parse_query()
		 *
		 * @param array $query An array of query variables.
		 */
		$query                      = apply_filters('ajax_query_attachments_args', $query);
		$generate_attachments_count = apply_filters('pmc_gallery_v4_query_attachments_generate_count', true, $query);
		$query                      = new \WP_Query($query);

		foreach ($query->posts as $post) {
			$_post = $this->filter_gallery_attachment($post, $escape_attachment_ids);

			if ($_post) {
				$posts[] = $_post;
			}
		}

		$posts             = array_filter($posts);
		$attachment_ids    = array_column($posts, 'attachment_id');
		$attachment_ids    = array_map('intval', (array) $attachment_ids);
		$attachment_ids    = array_unique((array) $attachment_ids);
		$page              = 0;
		$attachment_counts = array();

		if (!$generate_attachments_count) {
			wp_send_json_success($posts);
		}

		do {
			$page++;
			$args  = array(
				'post_type'              => \PMC\Gallery\Attachment_Detail::NAME,
				'post_parent__in'        => $attachment_ids,
				'orderby'                => false,
				'post_status'            => 'publish',
				'paged'                  => $page,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			);
			$query = new \WP_Query($args);
			foreach ($query->posts as $post) {
				if (empty($attachment_counts[$post->post_parent]) || !is_numeric($attachment_counts[$post->post_parent])) {
					$attachment_counts[$post->post_parent] = 0;
				}
				$attachment_counts[$post->post_parent]++;
			}
		} while ($page < $query->max_num_pages);

		foreach ($posts as $key => $post) {
			$attachment_counts[$post['attachment_id']] = (isset($attachment_counts[$post['attachment_id']]) && is_numeric($attachment_counts[$post['attachment_id']])) ? $attachment_counts[$post['attachment_id']] : 0;
			$posts[$key]['attachment_count']           = $attachment_counts[$post['attachment_id']];
		}

		wp_send_json_success($posts);
	}

	/**
	 * Function is used to process attachment's (or its variant's of attachment)
	 * data serve to front end. It will process with wp_prepare_attachment_for_js() (WordPress default function).
	 * If add attachment id and gallery_id (if any).
	 *
	 * @param \WP_Post $post                  attachment or private post (variant of attachment for gallery).
	 * @param array    $escape_attachment_ids List of attachment will be escaped from result.
	 *
	 * @return boolean|array false on fail. processed array of attachment and their variant.
	 */
	public function filter_gallery_attachment($post, $escape_attachment_ids = array())
	{
		$_post                 = array();
		$escape_attachment_ids = array_map('intval', (array) $escape_attachment_ids);

		if ('attachment' === $post->post_type) {

			if (in_array($post->ID, (array) $escape_attachment_ids, true)) {
				return false;
			}

			$_post                                 = wp_prepare_attachment_for_js($post);
			$attachment_meta_data                  = get_post_meta($post->ID, '_wp_attachment_metadata', true);
			$image_meta                            = isset($attachment_meta_data['image_meta']) && is_array($attachment_meta_data['image_meta']) ? $attachment_meta_data['image_meta'] : array();
			$_post['attachment_id']                = intval($post->ID);
			$_post['gallery_id']                   = false;
			$_post['attachment_created_timestamp'] = !empty($image_meta['created_timestamp']) ? sanitize_text_field($image_meta['created_timestamp']) : 0;
		} elseif (\PMC\Gallery\Attachment_Detail::NAME === $post->post_type) {

			if (in_array($post->post_parent, (array) $escape_attachment_ids, true)) {
				return false;
			}

			$gallery_handle             = \PMC\Gallery\Attachment_Detail::get_instance();
			$variant_meta               = $gallery_handle->get_variant_meta($post->ID);
			$variant_meta['gallery_id'] = intval($variant_meta['gallery_id']);
			$attachment                 = get_post($post->post_parent);

			if (is_null($attachment) || 'attachment' !== $attachment->post_type) {
				return false;
			}

			$attachment_meta_data = get_post_meta($attachment->ID, '_wp_attachment_metadata', true);
			$image_meta           = isset($attachment_meta_data['image_meta']) && is_array($attachment_meta_data['image_meta']) ? $attachment_meta_data['image_meta'] : array();
			$allow_html_in        = array(
				'caption',
			);
			$allowed_tags         = array(
				'strong' => array(),
				'em'     => array(),
				'h3'     => array(),
				'span'   => array(
					'style' => array(),
				),
				'a'      => array(
					'href'   => array(),
					'target' => array(),
				),
			);

			$_post = wp_prepare_attachment_for_js($attachment);

			$_post['attachment_id']                = intval($post->post_parent);
			$_post['attachment_created_timestamp'] = !empty($image_meta['created_timestamp']) ? sanitize_text_field($image_meta['created_timestamp']) : 0;

			foreach ($variant_meta as $key => $value) {
				if (in_array($key, (array) $allow_html_in, true)) {
					$_post[$key] = wp_kses($value, $allowed_tags);
				} else {
					$_post[$key] = sanitize_text_field($value);
				}
			}

			$_post['id'] = $_post['attachment_id'] . '-' . $_post['gallery_id'];
		}

		return $_post;
	}

	/**
	 * Function is used to fetch gallery's variant of attachment.
	 * It will return list attachment data including variant of gallery after
	 * processing attachment data with `wp_prepare_attachment_for_js()` (WordPress default).
	 * If search string provided then will perform search in all gallery variant
	 * including attachment data itself, And return only those which data match search string.
	 * NOTE : If attachment itself data are not matching with search string,
	 * then it won't provide original attachment data.
	 *
	 * @param int|\WP_Post   $attachment_id Attachment ID or Post.
	 * @param string|boolean $search        Optional search string.
	 * @param int|boolean    $gallery_id    Optional gallery id.
	 *
	 * @return array|boolean      array of processed data on success.
	 */
	public function get_gallery_attachments($attachment_id, $search = false, $gallery_id = false)
	{

		if (!isset($attachment_id)) {
			return false;
		}

		// If it is not instance of WP_Post then return.
		if ($attachment_id instanceof \WP_Post) {
			$attachment_id = $attachment_id->ID;
		}

		$posts = array();
		$query = array();

		// Post type.
		$query['post_type'] = array(
			\PMC\Gallery\Attachment_Detail::NAME,
		);

		// Post statue.
		$query['post_status']  = 'inherit';
		$query['post_status'] .= ',publish';

		$query['no_found_rows'] = true;

		if (current_user_can(get_post_type_object('attachment')->cap->read_private_posts)) {
			$query['post_status'] .= ',private';
		}

		// Filter query clauses to include file names.
		if (!empty($search)) {
			$query['s'] = $search;
			add_filter('posts_clauses', '_filter_query_attachment_filenames');
		}

		// Post parent.
		$query['post_parent'] = $attachment_id;

		$query = apply_filters('ajax_query_attachments_args', $query);

		$query = new \WP_Query($query);

		foreach ($query->posts as $post) {
			$_post = $this->filter_gallery_attachment($post);

			if ($_post) {
				$posts[] = $_post;
			}
		}

		$posts = array_filter($posts);

		return $posts;
	}

	/**
	 * When any gallery remove from trash. then also delete attachment variants
	 * (private posts) related to that gallery.
	 *
	 * @global string $post_type Post type of current post.
	 *
	 * @param int     $post_id   Post id of post that is going to delete.
	 */
	public function before_delete_post($post_id)
	{
		global $post_type;

		if (\PMC\Gallery\Defaults::NAME === $post_type) {
			$gallery_variants = get_post_meta($post_id, \PMC\Gallery\Defaults::NAME, true);

			if (!empty($gallery_variants) && is_array($gallery_variants)) {
				foreach ($gallery_variants as $key => $value) {
					// Remove Post from private CPT.
					// $key has the post ID
					wp_delete_post(intval($key), true);
				}
			}
		}
	}

	/**
	 * Adds the meta box container
	 */
	public function media_manager_meta_box()
	{
		if (!post_type_supports(\PMC\Gallery\Defaults::NAME, 'editor')) {
			return;
		}

		add_meta_box(
			\PMC\Gallery\Defaults::NAME . '_meta_box',
			esc_html__('Gallery Images and Copy', 'pmc-gallery-v4'),
			array($this, 'media_manager_render_meta_box_content'),
			\PMC\Gallery\Defaults::NAME,
			'normal',
			'high'
		);

		remove_post_type_support(\PMC\Gallery\Defaults::NAME, 'editor');
	}

	/**
	 * Render Meta Box content
	 */
	public function media_manager_render_meta_box_content()
	{
		echo '<div id="pmc-gallery-images"></div>'; // Give the media manager a home
		wp_editor($GLOBALS['post']->post_content, 'post_content');
	}

	/**
	 * Enqueue JS and CSS for rendering the inline media manager on
	 * pmc-gallery edit post pages
	 *
	 * @param string $hook Admin hook name
	 *
	 * @return void
	 */
	public function action_admin_enqueue_scripts($hook)
	{

		if (('post.php' === $hook || 'post-new.php' === $hook) && in_array(get_post_type(), array(\PMC\Gallery\Defaults::NAME, 'post'), true)) {

			wp_enqueue_style(\PMC\Gallery\Defaults::NAME . '-admin-post');
			wp_enqueue_script(\PMC\Gallery\Defaults::NAME . '-admin');

			if (\PMC\Gallery\Defaults::NAME === get_post_type()) {
				wp_localize_script(
					\PMC\Gallery\Defaults::NAME . '-admin-post',
					'pmc_gallery_admin_options',
					array(
						'add_gallery'        => 'enabled' === cheezcap_get_option('pmc_gallery_prepend') ? 'prepend' : '',
						'ajaxurl'            => admin_url('admin-ajax.php'),
						'sortOrderNonce'     => wp_create_nonce('get-images-sorted-nonce'),
						'pmc_gallery_update' => wp_create_nonce('pmc_gallery_update'),
					)
				);
				$current_use = wp_get_current_user();
				$user_data   = isset($current_use->data) ? (array) $current_use->data : array();
				wp_localize_script(\PMC\Gallery\Defaults::NAME . '-admin-post', 'pmc_gallery_admin_user', $user_data);
				wp_enqueue_script(\PMC\Gallery\Defaults::NAME . '-admin-post');
			}
		}

		if (('post-new.php' === $hook || 'post.php' === $hook) && (get_post_type() === \PMC\Gallery\Defaults::NAME)) {
			wp_enqueue_script(\PMC\Gallery\Defaults::NAME . '-admin-ui-improvements-js', PMC_GALLERY_PLUGIN_URL . 'assets/build/js/admin-ui-improvements.js', array('jquery'));
			wp_enqueue_script('jquery-ui-tooltip', array('jquery'));
		}
	}

	/**
	 * Saves gallery image IDs to post meta
	 *
	 * @return void
	 */
	public function update_gallery_data()
	{

		$post_id    = \PMC::filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
		$sub_action = \PMC::filter_input(INPUT_POST, 'sub_action', FILTER_SANITIZE_STRING);
		// @TODO: This, need fixing. We're expecting an array of array values, the PMC::filter_input return a string, this data will always be empty!
		$data       = \PMC::filter_input(INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
		$ids        = \PMC::filter_input(INPUT_POST, 'ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
		$changes    = \PMC::filter_input(INPUT_POST, 'changes', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
		$is_prepend = \PMC::filter_input(INPUT_POST, 'is_prepend', FILTER_SANITIZE_NUMBER_INT);
		$nonce      = \PMC::filter_input(INPUT_POST, 'nonce', FILTER_SANITIZE_STRING);

		$post_id = ($post_id) ? intval($post_id) : 0;

		if (
			(!$post_id) ||
			(!isset($nonce)) ||
			(!wp_verify_nonce($nonce, 'update-post_' . $post_id)) ||
			(!current_user_can('edit_posts', $post_id))
		) {
			return;
		}

		$response = array();

		check_ajax_referer('pmc_gallery_update', 'security');

		$gallery_attachment = \PMC\Gallery\Attachment_Detail::get_instance();
		$action             = !empty($sub_action) ? trim(strtolower($sub_action)) : 'update';

		$ids = (!empty($ids)) ? (array) $ids : array();
		$ids = array_map('intval', (array) $ids);
		$ids = array_filter($ids); // Remove empty values (including 0, which isn't a valid post ID anyway).

		$gallery_attachments = !empty($data) ? (array) $data : array();

		// get attachment ids of gallery post.
		$gallery_meta = get_post_meta($post_id, \PMC\Gallery\Defaults::NAME, true);
		$gallery_meta = is_array($gallery_meta) ? $gallery_meta : array();
		$gallery_meta = array_map('intval', (array) $gallery_meta);

		switch ($action) {
			case 'get':
				$content = array();
				foreach ($gallery_meta as $variant_id => $attachment_id) {
					// Fetch gallery custom data from attachment.
					$attachment_meta = $gallery_attachment->get_variant_meta($variant_id);

					/**
					 * If custom data of gallery attachment available,
					 * then get those data, otherwise fallback to default data
					 * on backend not need to handle fallback to default data
					 * because on front-end it will autometically handled.
					 */
					if (isset($attachment_meta) && $attachment_meta && is_array($attachment_meta)) {
						$variant = get_post($variant_id);

						if (!is_null($variant)) {
							$attachment_meta['id']           = $attachment_id;
							$attachment_meta['author']       = $variant->post_author;
							$attachment_meta['modified']     = $variant->post_modified;
							$attachment_meta['modified_gmt'] = $variant->post_modified_gmt;
							$content[]                       = $attachment_meta;
						}
					}
				}

				$response = array(
					'success' => true,
					'data'    => $content,
				);

				wp_send_json($response);

				break;
			case 'add':
				$is_prepend = $is_prepend && 1 === $is_prepend;
				$new_ids    = array();

				// Add gallery into attachment with custom data.
				foreach ($gallery_attachments as $attachment) {
					$variant_id    = false;
					$attachment_id = intval($attachment['id']);

					// Get image credit from default attachment if it is not pass.
					if (!isset($attachment['image_credit'])) {
						$attachment['image_credit'] = get_post_meta($attachment_id, '_image_credit', true);
					}

					/**
					 * Get variant id from attachment id.
					 * which is stored in gallery's meta field.
					 *
					 * NOTE : in gallery meta field variant_id  as key and attachment_id as value is stored
					 * Like, array( ['variant_id'] => attachment_id );
					 * where variant_id is private post id which is created for
					 * storing attachment's custom data for gallery.
					 */
					if (in_array($attachment_id, (array) $gallery_meta, true)) {
						$variant_id = array_search($attachment_id, (array) $gallery_meta, true);
					}

					$variant_id             = $gallery_attachment->add_attachment_variant($post_id, $attachment, $variant_id);
					$new_ids[$variant_id] = $attachment_id;
				}

				if ($is_prepend) {
					foreach ($gallery_meta as $key => $value) {
						$new_ids[$key] = $value;
					}

					$gallery_meta = $new_ids;
				} else {
					foreach ($new_ids as $key => $value) {
						$gallery_meta[$key] = $value;
					}
				}

				// @codeCoverageIgnoreStart
				$this->_update_restricted_image_meta_for_gallery($post_id, $gallery_meta);
				// @codeCoverageIgnoreEnd

				$response['success'] = update_post_meta($post_id, \PMC\Gallery\Defaults::NAME, $gallery_meta);

				break;
			case 'remove':
				$data = array();

				foreach ($ids as $id) {
					if (in_array($id, (array) $gallery_meta, true)) {
						// Get Variant Id.
						$variant_id = array_search($id, (array) $gallery_meta, true);

						// Unset from Gallery post meta.
						unset($gallery_meta[$variant_id]);

						// Remove Post from private CPT.
						wp_delete_post($variant_id, true);

						/**
						 * When and attachment remove from gallery,
						 * we need to show original attachment and variant of
						 * attachment for others gallery.
						 * Original attachment it handled by front-end but for
						 * variant from other gallery we need to sent.
						 */
						$variants = $this->get_gallery_attachments($id, false, $post_id);

						if (!empty($variants) && is_array($variants) && count($variants)) {
							$data = array_merge($data, $variants);
						}
					}
				}

				// @codeCoverageIgnoreStart
				$this->_update_restricted_image_meta_for_gallery($post_id, $gallery_meta);
				// @codeCoverageIgnoreEnd

				$response['success'] = update_post_meta($post_id, \PMC\Gallery\Defaults::NAME, $gallery_meta);

				if ($response['success']) {
					$response['data'] = $data;
				}

				break;
			case 'reorder':
				$new_ids = array();

				// Reorder search gallery post meta data according to new order.
				if (!empty($ids)) {
					foreach ($ids as $id) {
						if (in_array($id, (array) $gallery_meta, true)) {
							$variant_id             = array_search($id, (array) $gallery_meta, true);
							$new_ids[$variant_id] = $id;
						}
					}
				}

				// @codeCoverageIgnoreStart
				$this->_update_restricted_image_meta_for_gallery($post_id, $new_ids);
				// @codeCoverageIgnoreEnd

				$response['success'] = update_post_meta($post_id, \PMC\Gallery\Defaults::NAME, $new_ids);

				break;
			case 'edit':
				$attachment = array();

				// Get changes.
				if (!empty($changes)) {
					foreach ($changes as $key => $value) {
						$attachment[$key] = $value;
					}
				}

				unset($attachment['modified'], $attachment['modified_gmt']);

				// Update post of private CPT associated with gallery post's attachment.
				if (!empty($ids)) {
					foreach ($ids as $id) {

						// @codeCoverageIgnoreStart
						if (is_array($attachment) && class_exists('\PMC\Geo_Restricted_Content\Restrict_Image_Uses') && array_key_exists('image_restriction', $attachment)) {
							update_post_meta($id, \PMC\Geo_Restricted_Content\Restrict_Image_Uses::META_IMAGE_RESTRICTED_TYPE, $attachment['image_restriction']);
						}

						if (is_array($attachment) && class_exists('\PMC\Geo_Restricted_Content\Restrict_Image_Uses') && array_key_exists('image_allowed_in_feed', $attachment)) {
							update_post_meta($id, \PMC\Geo_Restricted_Content\Restrict_Image_Uses::META_IMAGE_ALLOWED_IN_FEED, $attachment['image_allowed_in_feed']);
						}
						// @codeCoverageIgnoreEnd

						if (in_array($id, (array) $gallery_meta, true)) {
							$variant_id = array_search($id, (array) $gallery_meta, true);
							$gallery_attachment->update_attachment_variant($variant_id, $attachment);
						}
					}
				}

				$response['success'] = true;

				break;
			case 'update':
			default:
				$ids = array();

				// Save custom data in attachment itself.
				if (!empty($gallery_attachments)) {
					foreach ($gallery_attachments as $attachment) {
						$variant_id    = false;
						$attachment_id = intval($attachment['id']);

						if (in_array($attachment_id, (array) $gallery_meta, true)) {
							$variant_id = array_search($attachment_id, (array) $gallery_meta, true);
						}

						/**
						 * If variant id is not set (or invalid) when data is update,
						 * which mean front-end not able to send image_credit data
						 * for variant of that gallery.
						 * This will raise when gallery data is migrate
						 * from version 2 to version 3.
						 */
						if (!($variant_id && is_numeric($variant_id) && get_post_type($variant_id) === \PMC\Gallery\Attachment_Detail::NAME)) {
							$attachment['image_credit'] = get_post_meta($attachment_id, '_image_credit', true);
						}

						$variant_id         = $gallery_attachment->add_attachment_variant($post_id, $attachment, $variant_id);
						$ids[$variant_id] = $attachment_id;
					}
				}

				// @codeCoverageIgnoreStart
				$this->_update_restricted_image_meta_for_gallery($post_id, $ids);
				// @codeCoverageIgnoreEnd

				// Store order in gallery post.
				$response['success'] = update_post_meta($post_id, \PMC\Gallery\Defaults::NAME, $ids);
				break;
		}

		// Method has no coverage, and testing this on its own provides no value.
		// @codeCoverageIgnoreStart
		// Rebuild cached data.
		\PMC\Gallery\Defaults::get_instance()->rebuild_gallery_cache_on_save(
			$post_id,
			get_post(
				$post_id
			),
			true
		);
		// @codeCoverageIgnoreEnd

		wp_send_json($response);
	}

	/**
	 * Send [gallery] shortcode with image IDs to inline media manager on pmc-gallery posts
	 *
	 * @see wp_enqueue_media()
	 *
	 * @param array         $settings Media view settings
	 * @param \WP_Post|null $post     Post object
	 *
	 * @return array $settings
	 */
	public function data_to_gallery($settings, $post)
	{
		if (!is_object($post)) {
			return $settings;
		}

		$image_ids = get_post_meta($post->ID, \PMC\Gallery\Defaults::NAME, true);

		if ($image_ids) {
			$shortcode               = '[gallery ids="' . esc_attr(implode(',', $image_ids)) . '"]';
			$settings['pmc_gallery'] = array('shortcode' => $shortcode);
		}

		return $settings;
	}

	/*
	 * @ticket PPT-4241 WWD - Add Sort order buttons to Gallery Builder
	 * @since 2015-02-27 Archana Mandhare
	 * @return string
	 */

	public function add_sort_buttons_to_gallery($strings, $post)
	{
		if (!is_object($post)) {
			return $strings;
		}

		$strings['editmetadata']       = __('Edit Metadata', 'pmc-gallery-v4');
		$strings['sortNumerically']    = __('Sort by #', 'pmc-gallery-v4');
		$strings['sortAlphabetically'] = __('Sort A-Z', 'pmc-gallery-v4');
		$strings['sortCreatedDate']    = __('Sort by Created Date', 'pmc-gallery-v4');
		$strings['selectAll']          = __('Select All', 'pmc-gallery-v4');

		return $strings;
	}

	/**
	 * Enhanced captioning for Galleries
	 *
	 * This customizes the caption field in the pop-up media manager on posts so
	 * that the caption box expands when the image gains focus.
	 */
	public function replace_tmpl_attachment()
	{
?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				var tmplAttachmentFirst = $("script#tmpl-attachment:first");
				tmplAttachmentFirst.remove();
				$("script#tmpl-attachment-details-two-column:first").remove();
				tmplAttachmentFirst.remove();
			});
		</script>
<?php

		include_once __DIR__ . '/../../template-parts/js-templates/attachment.php';
		include_once __DIR__ . '/../../template-parts/js-templates/attachment-details-two-column.php';
		include_once __DIR__ . '/../../template-parts/js-templates/attachment-two.php';
	}

	/**
	 * Save post
	 *
	 * @param int $post_id Post id.
	 *
	 * @return void
	 */
	function save_post($post_id)
	{
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || wp_doing_ajax() || !current_user_can('edit_post', $post_id) || \PMC\Gallery\Defaults::NAME !== get_post_type()) {
			return;
		}

		// get attachment ids of gallery post.
		$gallery_meta = get_post_meta($post_id, \PMC\Gallery\Defaults::NAME, true);
		// If featured image is not set, then set first attachment of gallery as featured image.
		$gallery_featured_id = get_post_thumbnail_id($post_id);

		$this->_update_restricted_image_meta_for_gallery($post_id, $gallery_meta);

		if (empty($gallery_featured_id)) {

			if (!empty($gallery_meta) && is_array($gallery_meta) && count($gallery_meta) > 0) {
				// Pick the first image from the list to be the featured image
				// set_post_thumbnail($post_id, intval($gallery_meta[0]));
				set_post_thumbnail($post_id, intval(reset($gallery_meta)));
			}
		}
	}

	/**
	 * Enqueue admin ui simplification stuff.
	 *
	 * @param string $hook Hook
	 *
	 * @return void
	 */
	public function enqueue_admin_ui_simplification_stuff($hook)
	{

		if ('post-new.php' !== $hook && 'post.php' !== $hook) {
			return;
		}

		wp_enqueue_style(\PMC\Gallery\Defaults::NAME . '-admin-ui-simplification-css', PMC_GALLERY_PLUGIN_URL . 'assets/build/css/admin-ui-simplification.css');
	}

	/**
	 * Hooked into 'hidden_meta_boxes' filter, this method hides all un-needed
	 * meta boxes on Gallery add/edit pages in wp-admin.
	 *
	 * @ticket PPT-6845
	 *
	 * @param array      $hidden         Array containing IDs of all meta boxes that will be hidden
	 * @param \WP_Screen $current_screen Current screen object
	 * @param bool       $use_defaults   Whether defaults are in use or not (for hiding/displaying meta boxes)
	 *
	 * @return array Array containing IDs of all meta boxes that will be hidden
	 */
	public function hide_unneeded_metaboxes($hidden, $current_screen, $use_defaults)
	{

		if (empty($current_screen->id) || \PMC\Gallery\Defaults::NAME !== $current_screen->id) {
			return $hidden;
		}

		if (!is_array($hidden)) {
			$hidden = array();
		}

		$metaboxes_to_hide = array(
			'postexcerpt',
			'trackbacksdiv',
			'commentstatusdiv',
			'likes_meta',
			'post-meta-inspector',
		);

		return array_filter(array_unique(array_merge($hidden, $metaboxes_to_hide)));
	}

	/**
	 * Adds new 'Featured Image' column to gallery list table
	 *
	 * @param array $columns Array of column.
	 *
	 * @return array
	 */
	public function add_gallery_featured_image_columns($columns)
	{
		$custom_columns = array(
			'cb'                => '', // This to make sure checkboxes are shown first.
			'gallery_thumbnail' => __('Feature Image', 'pmc-gallery-v4'),
		);

		return array_merge($custom_columns, $columns);
	}

	/**
	 * Adds featured image to featured image column on gallery list,
	 * if featured image is set for the gallery,
	 * else sets first image of the gallery as featured image.
	 *
	 * @version 2018-02-09 brandoncamenisch - feature/PMCVIP-2977:
	 * - Suppressing errors where array is expected for attachment_id
	 *
	 * @param string $column  Give current column.
	 * @param int    $post_id Current post id.
	 */
	public function custom_gallery_featured_image_column($column, $post_id)
	{

		$size = array(60, 60);

		switch ($column) {
			case 'gallery_thumbnail':
				$gallery_featured_id = get_post_thumbnail_id($post_id);

				// Checks if featured image is set, then return featured images else, get first image from the gallery.
				if (!empty($gallery_featured_id) && is_numeric($gallery_featured_id)) {
					echo wp_kses_post(wp_get_attachment_image($gallery_featured_id, $size));
				} else {
					// Get gallery attachments for $post_id.
					$gallery_featured = get_post_meta($post_id, \PMC\Gallery\Defaults::NAME, true);

					if (!empty($gallery_featured) && is_array($gallery_featured)) {
						// Get first image from gallery stored.
						$attachment_id = array_shift($gallery_featured);

						if (is_numeric($attachment_id)) {
							echo wp_kses_post(wp_get_attachment_image($attachment_id, $size));
						}
					}
				}

				break;
		}
	}

	/**
	 * Explicitly disable ES when query already has 'post__in'.
	 *
	 * @param array $query_args
	 *
	 * @return array
	 *
	 * @TODO Code coverage has been temporarily disabled for this method as its added as part of a 911. Unit test(s) should be added for this method later.
	 *
	 */
	public function maybe_disable_es_query(array $query_args): array
	{

		if (!empty($query_args['post__in']) && isset($query_args['orderby']) && 'post__in' === $query_args['orderby']) {
			$query_args['es'] = false;
		}

		return $query_args;
	}

	/**
	 * function for gallery to update post_meta for 'single use'(restricted) images.
	 *
	 * @param $post_id
	 * @param $attachments
	 */
	protected function _update_restricted_image_meta_for_gallery($post_id, $attachments)
	{

		$current_images = [];

		if (class_exists('\PMC\Geo_Restricted_Content\Restrict_Image_Uses') && true === apply_filters('pmc_restricted_image_check_enabled', false) && isset($post_id) && is_int($post_id) && is_array($attachments)) {

			$existing_images = get_post_meta($post_id, '_restricted_single_use_image_used_post', true);
			$featured_image  = get_post_thumbnail_id($post_id);

			if ($featured_image) {
				$attachments[] = $featured_image;
			}

			foreach ($attachments as $attachment_id) {

				// Go through each images to check if it's "single use" image or not
				if ('single_use' === get_post_meta($attachment_id, \PMC\Geo_Restricted_Content\Restrict_Image_Uses::META_IMAGE_RESTRICTED_TYPE, true)) {

					update_post_meta($attachment_id, '_restricted_single_use_image_used', $post_id);

					// Add image-id to $current_images list
					$current_images[] = $attachment_id;
				} else {
					delete_post_meta($attachment_id, '_restricted_single_use_image_used');
				}
			}

			$current_images = array_unique((array) $current_images);

			// Add meta entry of all "single use images" to current-post for cross references
			if (!empty($current_images)) {
				update_post_meta($post_id, '_restricted_single_use_image_used_post', $current_images);
			} else {
				// If there is no single use images for current post then delete meta entry
				delete_post_meta($post_id, '_restricted_single_use_image_used_post');
			}

			/**
			 * Remove meta entries from (single-use) images those are removed from current post
			 */
			if (is_array($existing_images)) {

				$existing_images = array_diff(array_unique((array) $existing_images), $current_images);

				foreach ($existing_images as $image_id) {

					delete_post_meta($image_id, '_restricted_single_use_image_used');
				}
			}
		}
	}
}
