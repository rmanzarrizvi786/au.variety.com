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
 * @since 1/1/2013 Vicky Biswas
 */

namespace PMC\Gallery\Admin;
/**
 * @codeCoverageIgnore
 */

use PMC\Global_Functions\Traits\Singleton;

class Media_Manager {

	use Singleton;

	/**
	 * Manages the Settings for PMC Gallery
	 */
	protected function __construct() {
		// For adding checkbox to enable zoom feature
		add_action( 'save_post', array( $this, 'save_post' ) );

		//UI Simplification
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_ui_simplification_stuff' ), 12 );
		add_action( 'hidden_meta_boxes', array( $this, 'hide_unneeded_metaboxes' ), 12, 3 );

		// Enhanced captioning for posts
		add_action( 'admin_footer-post-new.php', array( $this, 'replace_tmpl_attachment' ) );
		add_action( 'admin_footer-post.php', array( $this, 'replace_tmpl_attachment' ) );

		// Rendering the inline media manager on pmc-gallery edit post pages
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'media_manager_meta_box' ) );
		add_action( 'wp_ajax_pmc_gallery_update', array( $this, 'update_gallery_data' ) );
		add_filter( 'media_view_settings', array( $this, 'data_to_gallery' ), 10, 2 );

		//@ticket PPT-4241 WWD - Add Order by Filename button to Gallery Builder
		//@since 2015-02-27 Archana Mandhare
		add_filter( 'media_view_strings', array( $this, 'add_sort_buttons_to_gallery' ), 10, 2 );

		add_filter( 'manage_pmc-gallery_posts_columns', array( $this, 'add_gallery_featured_image_columns' ), 10, 1 );
		add_action( 'manage_pmc-gallery_posts_custom_column', array( $this, 'custom_gallery_featured_image_column' ), 10, 2 );

		add_action( 'before_delete_post', array( $this, 'before_delete_post' ) );
		/**
		 * If request is from pmc-gallery page then and then modify response of
		 * `query-attachment` other wise fallback to default WordPress function.
		 */
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_REQUEST['action'] ) && ( 'query-attachments' === $_REQUEST['action'] || 'save-attachment' === $_REQUEST['action'] || 'upload-attachment' === $_REQUEST['action'] ) ) {
			if ( isset( $_REQUEST['post_id'] ) && is_numeric( $_REQUEST['post_id'] ) && \PMC_Gallery_Defaults::name === get_post_type( $_REQUEST['post_id'] ) ) {
				remove_action( 'wp_ajax_query-attachments', 'wp_ajax_query_attachments' );
				add_action( 'wp_ajax_query-attachments', array( $this, 'wp_ajax_query_attachments' ), 1 );
				add_filter( 'posts_search', array( $this, 'post_search' ), 10, 2 );
				add_filter( 'wp_prepare_attachment_for_js', array( $this, 'wp_prepare_attachment_for_js' ), 1, 1 );
			}
			add_action( 'wp_ajax_save-attachment', array( $this, 'pre_save_attachment' ), 0 );
		}

		// To enable image url search in media.
		add_filter( 'wp_insert_attachment_data', array( $this, 'wp_insert_attachment_data' ), 15, 1 );

		add_filter( 'ajax_query_attachments_args', array( $this, 'maybe_disable_es_query' ), 15, 1 );
	}

	/**
	 * Pre attachment search filter. to modify search query.
	 *
	 * @global	object $wpdb
	 * @param	string	  $sql search sql of.
	 * @param	\WP_Query $query WP_Query object of attachment search.
	 * @return	string
	 */
	public function post_search( $sql, $query ) {
		if ( ! empty( $query->query['s'] ) ) {
			global $wpdb;
			$term = $wpdb->esc_like( $query->query['s'] );
			$like = "%{$term}%";
			$sql = $wpdb->prepare( " AND ((({$wpdb->posts}.post_title LIKE %s) OR ({$wpdb->posts}.post_excerpt LIKE %s) OR ({$wpdb->posts}.post_content LIKE %s) OR ({$wpdb->posts}.post_content_filtered LIKE %s)))", $like, $like, $like, $like );
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
	public function wp_insert_attachment_data( $data ) {

		if ( empty( $data['guid'] ) ) {
			return $data;
		}

		if ( false === strpos( $data['post_content_filtered'], $data['guid'] ) ) {
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
	public function wp_prepare_attachment_for_js( $response ) {
		static $attachment_counts = array();
		$response['attachment_id'] = intval( $response['id'] );
		$response['gallery_id'] = false;
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
	 */
	public function pre_save_attachment() {
		if ( isset( $_REQUEST['gallery_id'] ) && is_numeric(  $_REQUEST['gallery_id'] ) ) {
			$gallery_id = intval( $_REQUEST['gallery_id'] );
			$attachment_id = isset( $_REQUEST['attachment_id'] ) && is_numeric(  $_REQUEST['attachment_id'] ) ? intval( $_REQUEST['attachment_id'] ) : false;
			$attachment = array();
			$response = array();
			$gallery_attachment = \PMC\Gallery\Attachment\Detail::get_instance();
			if ( ! $attachment_id ) {
				$attachment_id = isset( $_REQUEST['id'] ) && is_numeric( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : false;
			}
			foreach ( $_POST['changes'] as $key => $value ) {
				$attachment[ $key ] = $value;
			}
			unset( $attachment['modified'], $attachment['modified_gmt'] );
			$gallery_meta = get_post_meta( $gallery_id, \PMC_Gallery_Defaults::name, true );
			if ( ! empty( $gallery_meta ) && is_array( $gallery_meta ) ) {
				$gallery_meta = array_map( 'intval', $gallery_meta );

				// Update post of private CPT associated with gallery post's attachment.
				if ( in_array( $attachment_id, $gallery_meta, true ) ) {
					$variant_id = array_search( $attachment_id, $gallery_meta, true );
					$variant_id = $gallery_attachment->update_attachment_variant( $variant_id, $attachment );
				}
			}
			$response['success'] = true;
			wp_send_json( $response );
		} elseif ( isset( $_REQUEST['attachment_id'] ) && is_numeric( $_REQUEST['attachment_id'] ) ) {
			$_REQUEST['id'] = absint( $_REQUEST['attachment_id'] );
		}
		if ( isset( $_REQUEST['id'] ) && is_numeric( $_REQUEST['id'] ) ) {
			$changes = $_REQUEST['changes'];
			$post = \get_post( $_REQUEST['id'], ARRAY_A );
			$content = $post['post_content_filtered'];
			$gallery_attachment = \PMC\Gallery\Attachment\Detail::get_instance();
			$keywords = $gallery_attachment->get_unique_word( $changes );
			$keywords = array_merge( $keywords, explode( ' ', $content ) );
			$keywords = array_unique( $keywords );
			$content = implode( ' ', $keywords );
			$post_array = array(
				'ID'					 => absint( $_REQUEST['id'] ),
				'post_content_filtered'	 => sanitize_text_field( $content ),
				'meta_input'			 => array(
					'search_keyword' => sanitize_text_field( $content ),
				),
			);
			wp_update_post( $post_array );
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
	public function wp_ajax_query_attachments() {
		$query = isset( $_REQUEST['query'] ) ? (array) $_REQUEST['query'] : array();
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error();
		}
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
		foreach ( get_taxonomies_for_attachments( 'objects' ) as $t ) {
			if ( $t->query_var && isset( $query[ $t->query_var ] ) ) {
				$keys[] = $t->query_var;
			}
		}
		$query = array_intersect_key( $query, array_flip( $keys ) );
		$query['post_type'] = array(
			'attachment',
			\PMC\Gallery\Attachment\Detail::name,
		);
		if ( MEDIA_TRASH && ! empty( $query['post_status'] ) && 'trash' === $query['post_status'] ) {
			$query['post_status'] = 'trash';
		} else {
			$query['post_status'] = 'inherit';
		}

		if ( current_user_can( get_post_type_object( 'attachment' )->cap->read_private_posts ) ) {
			$query['post_status'] .= ',private';
		}
		$query['post_status'] .= ',publish';
		unset( $query['post_mime_type'] );

		// Filter query clauses to include filenames.
		if ( isset( $query['s'] ) ) {
			add_filter( 'posts_clauses', '_filter_query_attachment_filenames' );
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
		$current_post_id = isset( $_POST['post_id'] ) && is_numeric( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : false;
		if ( $current_post_id && is_numeric( $current_post_id ) && empty( $query['post__in'] ) ) {
			if ( get_post_type( $current_post_id ) == \PMC_Gallery_Defaults::name ) {
				$escape_attachment_ids = get_post_meta( $current_post_id, \PMC_Gallery_Defaults::name, true );
				if ( is_array( $escape_attachment_ids ) ) {
					$escape_attachment_ids = array_values( $escape_attachment_ids );
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
		$query = apply_filters( 'ajax_query_attachments_args', $query );
		$query = new \WP_Query( $query );

		foreach ( $query->posts as $post ) {
			$_post = $this->filter_gallery_attachment( $post, $escape_attachment_ids );
			if ( $_post ) {
				$posts[] = $_post;
			}
		}
		$posts = array_filter( $posts );
		$attachment_ids = array_column( $posts, 'attachment_id' );
		$attachment_ids = array_map( 'intval', $attachment_ids );
		$attachment_ids = array_unique( $attachment_ids );
		$page = 0;
		$attachment_counts = array();
		do {
			$page++;
			$args = array(
				'post_type'			 => \PMC\Gallery\Attachment\Detail::name,
				'post_parent__in'	 => $attachment_ids,
				'orderby'			 => false,
				'post_status'		 => 'publish',
				'paged'				 => $page,
			);
			$query = new \WP_Query( $args );
			foreach ( $query->posts as $post ) {
				if ( empty( $attachment_counts[ $post->post_parent ] ) || ! is_numeric( $attachment_counts[ $post->post_parent ] ) ) {
					$attachment_counts[ $post->post_parent ] = 0;
				}
				$attachment_counts[ $post->post_parent ] ++;
			}
		} while ( $page < $query->max_num_pages );
		foreach ( $posts as $key => $post ) {
			$attachment_counts[ $post['attachment_id'] ] = ( isset( $attachment_counts[ $post['attachment_id'] ] ) && is_numeric( $attachment_counts[ $post['attachment_id'] ] ) ) ? $attachment_counts[ $post['attachment_id'] ] : 0;
			$posts[ $key ]['attachment_count'] = $attachment_counts[ $post['attachment_id'] ];
		}
		wp_send_json_success( $posts );
	}

	/**
	 * Function is used to process attachment's (or its variant's of attachment)
	 * data serve to front end. it will proccess with `wp_prepare_attachment_for_js()` (WordPress default function).
	 * If add attachment id and gallery_id (if any).
	 *
	 * @param WP_Post $post attachment or private post (variant of attachment for gallery).
	 * @param array   $escape_attachment_ids List of attachment will be escaped from result.
	 * @return boolean|array false on fail. processed array of attachment and their variant.
	 */
	public function filter_gallery_attachment( $post, $escape_attachment_ids = array() ) {
		$_post = array();
		$escape_attachment_ids = array_map( 'intval', $escape_attachment_ids );
		if ( 'attachment' === $post->post_type ) {
			if ( in_array( $post->ID, $escape_attachment_ids, true ) ) {
				return false;
			}
			$_post = wp_prepare_attachment_for_js( $post );
			$attachment_meta_data = get_post_meta( $post->ID, '_wp_attachment_metadata', true );
			$image_meta = isset( $attachment_meta_data['image_meta'] ) && is_array( $attachment_meta_data['image_meta'] ) ? $attachment_meta_data['image_meta'] : array();
			$_post['attachment_id'] = intval( $post->ID );
			$_post['gallery_id'] = false;
			$_post['attachment_created_timestamp'] = ! empty( $image_meta['created_timestamp'] ) ? sanitize_text_field( $image_meta['created_timestamp'] ) : 0;
		} elseif ( \PMC\Gallery\Attachment\Detail::name === $post->post_type ) {
			if ( in_array( $post->post_parent, $escape_attachment_ids, true ) ) {
				return false;
			}
			$gallery_handle = \PMC\Gallery\Attachment\Detail::get_instance();
			$variant_meta = $gallery_handle->get_variant_meta( $post->ID );
			$variant_meta['gallery_id'] = intval( $variant_meta['gallery_id'] );

			$attachment = get_post( $post->post_parent );
			if ( is_null( $attachment ) || 'attachment' !== $attachment->post_type ) {
				return false;
			}
			$attachment_meta_data = get_post_meta( $attachment->ID, '_wp_attachment_metadata', true );
			$image_meta = isset( $attachment_meta_data['image_meta'] ) && is_array( $attachment_meta_data['image_meta'] ) ? $attachment_meta_data['image_meta'] : array();
			$allow_html_in = array(
				'caption',
			);
			$allowed_tags = array(
				'strong' => array(),
				'em' => array(),
				'h3' => array(),
				'span' => array(
					'style' => array(),
				),
				'a' => array(
					'href' => array(),
					'target' => array(),
				),
			);

			$_post = wp_prepare_attachment_for_js( $attachment );
			$_post['attachment_id'] = intval( $post->post_parent );
			$_post['attachment_created_timestamp'] = ! empty( $image_meta['created_timestamp'] ) ? sanitize_text_field( $image_meta['created_timestamp'] ) : 0;
			foreach ( $variant_meta as $key => $value ) {
				if ( in_array( $key, $allow_html_in, true ) ) {
					$_post[ $key ] = wp_kses( $value, $allowed_tags );
				} else {
					$_post[ $key ] = sanitize_text_field( $value );
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
	 * then it won't prvide original attachment data.
	 *
	 * @param int|WP_Post $attachment_id Attachment ID or Post.
	 * @param string	  $search Optional search string.
	 * @param int		  $gallery_id Optional gallery id.
	 * @return array	  array of processed data on success.
	 */
	public function get_gallery_attachments( $attachment_id, $search = false, $gallery_id = false ) {
		if ( ! isset( $attachment_id ) ) {
			return false;
		}
		// If it is not instance of WP_Post then return.
		if ( $attachment_id instanceof WP_Post ) {
			$attachment_id = $attachment_id->ID;
		}
		$posts = array();
		$query = array();

		// Post type.
		$query['post_type'] = array(
			\PMC\Gallery\Attachment\Detail::name,
		);

		// Post statue.
		$query['post_status'] = 'inherit';
		$query['post_status'] .= ',publish';
		if ( current_user_can( get_post_type_object( 'attachment' )->cap->read_private_posts ) ) {
			$query['post_status'] .= ',private';
		}

		// Filter query clauses to include filenames.
		if ( ! empty( $search ) ) {
			$query['s'] = $search;
			add_filter( 'posts_clauses', '_filter_query_attachment_filenames' );
		}

		// Post parent.
		$query['post_parent'] = $attachment_id;

		$query = apply_filters( 'ajax_query_attachments_args', $query );

		$query = new \WP_Query( $query );

		foreach ( $query->posts as $post ) {
			$_post = $this->filter_gallery_attachment( $post );
			if ( $_post ) {
				$posts[] = $_post;
			}
		}
		$posts = array_filter( $posts );
		return $posts;
	}

	/**
	 * When any galery remove from trash. then also delete attachment variants
	 * (private posts) related to that gallery.
	 *
	 * @global string $post_type Post type of current post.
	 * @param int $post_id Post id of post that is going to delete.
	 */
	public function before_delete_post( $post_id ) {
		global $post_type;
		if ( \PMC_Gallery_Defaults::name === $post_type ) {
			$gallery_variants = get_post_meta( $post_id, \PMC_Gallery_Defaults::name, true );
			if ( ! empty( $gallery_variants ) && is_array( $gallery_variants ) ) {
				foreach ( $gallery_variants as $key => $value ) {
					// Remove Post from private CPT.
					// $key has the post ID
					wp_delete_post( intval( $key ), true );
				}
			}
		}
	}

	/**
	 * Adds the meta box container
	 */
	public function media_manager_meta_box() {
		if ( !post_type_supports( \PMC_Gallery_Defaults::name, 'editor' ) ) {
			return;
		}

		add_meta_box(
				\PMC_Gallery_Defaults::name . '_meta_box', 'Gallery Images and Copy', array( $this, 'media_manager_render_meta_box_content' ), \PMC_Gallery_Defaults::name, 'normal', 'high'
		);
		remove_post_type_support( \PMC_Gallery_Defaults::name, 'editor' );
	}

	/**
	 * Render Meta Box content
	 */
	public function media_manager_render_meta_box_content() {
		echo '<div id="pmc-gallery-images"></div>'; // Give the media manager a home
		wp_editor( $GLOBALS['post']->post_content, 'post_content' );
	}

	/**
	 * Enqueue JS and CSS for rendering the inline media manager on
	 * pmc-gallery edit post pages
	 *
	 * @param string $hook Admin hook name
	 *
	 * @return void
	 */
	public function action_admin_enqueue_scripts( $hook ) {

		if ( ( 'post.php' == $hook || 'post-new.php' == $hook ) && in_array( get_post_type(), array( \PMC_Gallery_Defaults::name, 'post' ), true ) ) {

			wp_enqueue_style( \PMC_Gallery_Defaults::name . '-admin-post' );
			wp_enqueue_script( \PMC_Gallery_Defaults::name . '-admin' );

			if ( \PMC_Gallery_Defaults::name == get_post_type() ) {
				wp_localize_script( \PMC_Gallery_Defaults::name . '-admin-post', 'pmc_gallery_admin_options', array(
					'add_gallery'		 => 'enabled' == cheezcap_get_option( 'pmc_gallery_prepend' ) ? 'prepend' : '',
					'ajaxurl'			 => admin_url( 'admin-ajax.php' ),
					'sortOrderNonce'	 => wp_create_nonce( 'get-images-sorted-nonce' ),
					'pmc_gallery_update' => wp_create_nonce( 'pmc_gallery_update' ),
						)
				);
				$current_use = wp_get_current_user();
				$user_data = isset( $current_use->data ) ? (array) $current_use->data : array();
				wp_localize_script( \PMC_Gallery_Defaults::name . '-admin-post', 'pmc_gallery_admin_user', $user_data );
				wp_enqueue_script( \PMC_Gallery_Defaults::name . '-admin-post' );
			}
		}

		if ( ( $hook == 'post-new.php' || $hook == 'post.php' ) && ( get_post_type() == \PMC_Gallery_Defaults::name ) ) {
			wp_enqueue_script( 'radlikewhoa-countable', PMC_GALLERY_PLUGIN_URL . '/js/countable.js', array(), false, false );
			wp_enqueue_script( \PMC_Gallery_Defaults::name . '-admin-ui-improvements-js', PMC_GALLERY_PLUGIN_URL . 'js/admin-ui-improvements.js', array( 'jquery', 'radlikewhoa-countable' ) );
			wp_enqueue_script( 'jquery-ui-tooltip', array( 'jquery' ) );
		}
	}

	/**
	 * Saves gallery image IDs to postmeta
	 *
	 * @return void
	 */
	public function update_gallery_data() {

		$post_id = ( isset( $_POST['post_id'] ) ) ? (int) $_POST['post_id'] : 0;
		if (
			( ! $post_id ) ||
			( ! isset( $_POST['nonce'] ) ) ||
			( ! wp_verify_nonce( $_POST['nonce'], 'update-post_' . $post_id ) ) ||
			( ! current_user_can( 'edit_posts', $post_id ) )
		) {
			return;
		}
		$response = array();
		check_ajax_referer( 'pmc_gallery_update', 'security' );
		$gallery_attachment = \PMC\Gallery\Attachment\Detail::get_instance();
		$action = ! empty( $_POST['sub_action'] ) ? trim( strtolower( $_POST['sub_action'] ) ) : 'update';

		$ids = isset( $_POST['ids'] ) ? (array) $_POST['ids'] : array();
		$ids = array_map( 'intval', $ids ); // Convert values to int
		$ids = array_filter( $ids ); // Remove empty values (including 0, which isn't a valid post ID anyway).

		$gallery_attachments = isset( $_POST['data'] ) && is_array( $_POST['data'] ) ? (array) $_POST['data'] : array();

		// get attachment ids of gallery post.
		$gallery_meta = get_post_meta( $post_id, \PMC_Gallery_Defaults::name, true );
		$gallery_meta = is_array( $gallery_meta ) ? $gallery_meta : array();
		$gallery_meta = array_map( 'intval', $gallery_meta );

		switch ( $action ) {
			case 'get':
				$content = array();
				foreach ( $gallery_meta as $variant_id => $attachment_id ) {
					// Fetch gallery custom data from attachment.
					$attachment_meta = $gallery_attachment->get_variant_meta( $variant_id );
					/**
					 * If custom data of gallery attachment available,
					 * then get those data, otherwise fallback to default data
					 * on backend not need to handle fallback to default data
					 * because on front-end it will autometically handled.
					 */
					if ( isset( $attachment_meta ) && $attachment_meta && is_array( $attachment_meta ) ) {
						$variant = get_post( $variant_id );
						if ( !is_null( $variant ) ) {
							$attachment_meta['id'] = $attachment_id;
							$attachment_meta['author'] = $variant->post_author;
							$attachment_meta['modified'] = $variant->post_modified;
							$attachment_meta['modified_gmt'] = $variant->post_modified_gmt;
							$content[] = $attachment_meta;
						}
					}
				}
				$response = array(
					'success'	 => true,
					'data'		 => $content,
				);
				wp_send_json( $response );
				break;
			case 'add':
				$is_prepend = isset( $_POST['is_prepend'] ) && 1 == $_POST['is_prepend'] ? true : false;
				$new_ids = array();
				// Add gallery into attachment with custom data.
				foreach ( $gallery_attachments as $attachment ) {
					$variant_id = false;
					$attachment_id = intval( $attachment['id'] );
					// Get image creadit from default attachment if it is not pass.
					if ( ! isset( $attachment['image_credit'] ) ) {
						$attachment['image_credit'] = get_post_meta( $attachment_id, '_image_credit', true );
					}
					/**
					 * Get varinat id from attachment id.
					 * which is stored in gallery's meta field.
					 *
					 * NOTE : in gallery meta field variant_id  as key and attachment_id as value is stored
					 * Like, array( ['variant_id'] => attachment_id );
					 * where variant_id is private post id which is created for
					 * storeing attachment's custom data for gallery.
					 */
					if ( in_array( $attachment_id, $gallery_meta, true ) ) {
						$variant_id = array_search( $attachment_id, $gallery_meta, true );
					}
					$variant_id = $gallery_attachment->add_attachment_variant( $post_id, $attachment, $variant_id );
					$new_ids[$variant_id] = $attachment_id;
				}
				if ( $is_prepend ) {
					foreach ( $gallery_meta as $key => $value ) {
						$new_ids[$key] = $value;
					}
					$gallery_meta = $new_ids;
				} else {
					foreach ( $new_ids as $key => $value ) {
						$gallery_meta[ $key ] = $value;
					}
				}
				$response['success'] = update_post_meta( $post_id, \PMC_Gallery_Defaults::name, $gallery_meta );
				break;
			case 'remove':
				$data = array();
				foreach ( $ids as $id ) {
					if ( in_array( $id, $gallery_meta, true ) ) {
						// Get Variant Id.
						$variant_id = array_search( $id, $gallery_meta, true );

						// Unset from Gallery post meta.
						unset( $gallery_meta[ $variant_id ] );

						// Remove Post from private CPT.
						wp_delete_post( $variant_id, true );

						/**
						 * When and attachment remove from gallery,
						 * we need to show original attachment and variant of
						 * attachment for others gallery.
						 * Original attachment it handled by front-end but for
						 * variant from other gallery we need to sent.
						 */
						$variants = $this->get_gallery_attachments( $id, false, $post_id );
						if ( count( $variants ) ) {
							$data = array_merge( $data, $variants );
						}
					}
				}
				$response['success'] = update_post_meta( $post_id, \PMC_Gallery_Defaults::name, $gallery_meta );
				if ( $response['success'] ) {
					$response['data'] = $data;
				}
				break;
			case 'reorder':
				$new_ids = array();
				// Reorder search gallery post meta data according to new order.
				foreach ( $ids as $id ) {
					if ( in_array( $id, $gallery_meta, true ) ) {
						$variant_id = array_search( $id, $gallery_meta, true );
						$new_ids[ $variant_id ] = $id;
					}
				}
				$response['success'] = update_post_meta( $post_id, \PMC_Gallery_Defaults::name, $new_ids );
				break;
			case 'edit':
				$attachment = array();
				// Get changes.
				foreach ( $_POST['changes'] as $key => $value ) {
					$attachment[ $key ] = $value;
				}
				unset( $attachment['modified'], $attachment['modified_gmt'] );
				// Update post of private CPT associated with gallery post's attachment.
				foreach ( $ids as $id ) {
					if ( in_array( $id, $gallery_meta, true ) ) {
						$variant_id = array_search( $id, $gallery_meta, true );
						$variant_id = $gallery_attachment->update_attachment_variant( $variant_id, $attachment );
					}
				}
				$response['success'] = true;
				break;
			case 'update':
			default:
				$ids = array();
				// Save custom data in attachment itself.
				foreach ( $gallery_attachments as $attachment ) {
					$variant_id = false;
					$attachment_id = intval( $attachment['id'] );
					if ( in_array( $attachment_id, $gallery_meta, true ) ) {
						$variant_id = array_search( $attachment_id, $gallery_meta, true );
					}
					/**
					 * If variant id is not set (or invalid) when data is update,
					 * which mean front-end not able to send image_credit data
					 * for variant of that gallery.
					 * This will raise when gallery data is migrate
					 * from version 2 to version 3.
					 */
					if ( ! ( $variant_id && is_numeric( $variant_id ) && get_post_type( $variant_id ) === \PMC\Gallery\Attachment\Detail::name ) ) {
						$attachment['image_credit'] = get_post_meta( $attachment_id, '_image_credit', true );
					}
					$variant_id = $gallery_attachment->add_attachment_variant( $post_id, $attachment, $variant_id );
					$ids[ $variant_id ] = $attachment_id;
				}
				// Store order in gallery post.
				$response['success'] = update_post_meta( $post_id, \PMC_Gallery_Defaults::name, $ids );
				break;
		}
		wp_send_json( $response );
	}

	/**
	 * Send [gallery] shortcode with image IDs to inline media manager on pmc-gallery posts
	 *
	 * @see wp_enqueue_media()
	 *
	 * @param array $settings Media view settings
	 * @param obj|null $post Post object
	 *
	 * @return array $settings
	 */
	public function data_to_gallery( $settings, $post ) {
		if ( ! is_object( $post ) ) {
			return $settings;
		}

		$image_ids = get_post_meta( $post->ID, \PMC_Gallery_Defaults::name, true );
		if ( $image_ids ) {
			$shortcode = '[gallery ids="' . esc_attr( implode( ',', $image_ids ) ) . '"]';
			$settings['pmc_gallery'] = array( 'shortcode' => $shortcode );
		}

		return $settings;
	}

	/*
	 * @ticket PPT-4241 WWD - Add Sort order buttons to Gallery Builder
	 * @since 2015-02-27 Archana Mandhare
	 * @return string
	 */

	public function add_sort_buttons_to_gallery( $strings, $post ) {
		if ( ! is_object( $post ) ) {
			return $strings;
		}

		$strings['editmetadata'] = __( 'Edit Metadata' );
		$strings['sortNumerically'] = __( 'Sort by #' );
		$strings['sortAlphabetically'] = __( 'Sort A-Z' );
		$strings['sortCreatedDate'] = __( 'Sort by Created Date' );
		$strings['selectAll'] = __( 'Select All' );

		return $strings;
	}

	/**
	 * Enhanced captioning for Galleries
	 *
	 * This customizes the caption field in the pop-up media manager on posts so
	 * that the caption box expands when the image gains focus.
	 */
	public function replace_tmpl_attachment() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				jQuery("script#tmpl-attachment:first").remove();
				jQuery("script#tmpl-attachment-details-two-column:first").remove();
				jQuery("script#tmpl-attachment:first").remove();
			});
		</script>
		<script type="text/html" id="tmpl-attachment">
			<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">
				<div class="thumbnail">
					<# if ( data.uploading ) { #>
					<div class="media-progress-bar"><div style="width: {{ data.percent }}%"></div></div>
					<# } else if ( 'image' === data.type && data.sizes ) { #>
					<div class="centered">
						<img src="{{ data.size.url }}" draggable="false" alt="" />
					</div>
					<# } else { #>
					<div class="centered">
						<# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
						<img src="{{ data.image.src }}" class="thumbnail" draggable="false" />
						<# } else { #>
						<img src="{{ data.icon }}" class="icon" draggable="false" />
						<# } #>
					</div>
					<div class="filename">
						<div>{{ data.filename }}</div>
					</div>
					<# } #>
				</div>
				<# if ( data.buttons.close ) { #>
				<a class="close media-modal-icon" href="javascript:" title="<?php esc_attr_e( 'Remove', 'pmc-gallery-v3' ); ?>"></a>
				<# } #>
			</div>

			<# if ( data.buttons.check ) { #>
			<a class="check" href="javascript:" title="<?php esc_attr_e( 'Deselect', 'pmc-gallery-v3' ); ?>" tabindex="-1"><div class="media-modal-icon"></div></a>
			<# } #>
			<#
			var maybeReadOnly = data.can.save || data.allowLocalEdits ? '' : 'readonly';
			if ( data.describe ) {
			if ( 'image' === data.type ) { #>
			<textarea value="{{ data.caption }}" class="describe caption" data-setting="caption" placeholder="<?php esc_attr_e( 'Caption this image&hellip;', 'pmc-gallery-v3' ); ?>" {{ maybeReadOnly }}>{{ data.caption }}</textarea>
			<# } else { #>
			<input type="text" value="{{ data.title }}" class="describe" data-setting="title"
				   <# if ( 'video' === data.type ) { #>
				   placeholder="<?php esc_attr_e( 'Describe this video&hellip;', 'pmc-gallery-v3' ); ?>"
				   <# } else if ( 'audio' === data.type ) { #>
				   placeholder="<?php esc_attr_e( 'Describe this audio file&hellip;', 'pmc-gallery-v3' ); ?>"
				   <# } else { #>
				   placeholder="<?php esc_attr_e( 'Describe this media file&hellip;', 'pmc-gallery-v3' ); ?>"
				   <# } #> {{ maybeReadOnly }} />
				   <# }
				   }
				   #>
		</script>
		<script type="text/html" id="tmpl-attachment-details-two-column">
			<div class="attachment-media-view {{ data.orientation }}">
				<# var hideOnBulkEdit = data.bulkEdit ? 'hidden' : ''; #>
				<# if( data.bulkEdit ) {#>
				<# data.modelIds.forEach(function(d){ var img = wp.media.model.Attachment.get(d);#>
				<# if( img.attributes.id === data.id ){ var selected = data.id === img.attributes.id ? 'selected' : '';}#>
				<div class="thumbnail thumbnail-{{ data.type }} {{selected}}">
					<# if ( data.uploading ) { #>
					<div class="media-progress-bar"><div></div></div>
					<# } else if ( data.sizes && img.attributes.sizes.thumbnail ) { #>
					<img class="details-image" src="{{ img.attributes.sizes.thumbnail.url }}" draggable="false" alt="" />
					<# } else if ( data.sizes && img.attributes.sizes.full ) { #>
					<img class="details-image" src="{{ img.attributes.sizes.full.url }}" draggable="false" alt="" />
					<# } #>
				</div>
				<# });#>
				<#} else{#>
				<div class="thumbnail thumbnail-{{ data.type }}">
					<# if ( data.uploading ) { #>
					<div class="media-progress-bar"><div></div></div>
					<# } else if ( data.sizes && data.sizes.large ) { #>
					<img class="details-image" src="{{ data.sizes.large.url }}" draggable="false" alt="" />
					<# } else if ( data.sizes && data.sizes.full ) { #>
					<img class="details-image" src="{{ data.sizes.full.url }}" draggable="false" alt="" />
					<# } else if ( -1 === jQuery.inArray( data.type, [ 'audio', 'video' ] ) ) { #>
					<img class="details-image icon" src="{{ data.icon }}" draggable="false" alt="" />
					<# } #>

					<# if ( 'audio' === data.type ) { #>
					<div class="wp-media-wrapper">
						<audio style="visibility: hidden" controls class="wp-audio-shortcode" width="100%" preload="none">
							<source type="{{ data.mime }}" src="{{ data.url }}"/>
						</audio>
					</div>
					<# } else if ( 'video' === data.type ) {
					var w_rule = '';
					if ( data.width ) {
					w_rule = 'width: ' + data.width + 'px;';
					} else if ( wp.media.view.settings.contentWidth ) {
					w_rule = 'width: ' + wp.media.view.settings.contentWidth + 'px;';
					}
					#>
					<div style="{{ w_rule }}" class="wp-media-wrapper wp-video">
						<video controls="controls" class="wp-video-shortcode" preload="metadata"
							   <# if ( data.width ) { #>width="{{ data.width }}"<# } #>
							   <# if ( data.height ) { #>height="{{ data.height }}"<# } #>
							   <# if ( data.image && data.image.src !== data.icon ) { #>poster="{{ data.image.src }}"<# } #>>
							   <source type="{{ data.mime }}" src="{{ data.url }}"/>
						</video>
					</div>
					<# } #>

					<div class="attachment-actions">
						<# if ( 'image' === data.type && ! data.uploading && data.sizes && data.can.save ) { #>
						<button type="button" class="button edit-attachment"><?php _e( 'Edit Image', 'pmc-gallery-v3' ); ?></button>
						<# } else if ( 'pdf' === data.subtype && data.sizes ) { #>
						<?php _e( 'Document Preview', 'pmc-gallery-v3' ); ?>
						<# } #>
					</div>
				</div>
				<#}#>
			</div>
			<div class="attachment-info">
					<# var lastModified = new Date(data.modified_gmt); #>
					<# var month = ['Jan','Feb','March', 'April', 'May', 'June', 'July', 'August', 'Sept.', 'Oct.', 'Nov.', 'Dec.'];#>
					<# var timeStart = lastModified.getTime(); #>
					<# var timeEnd = new Date().getTime(); #>
					<# var offSet = new Date().getTimezoneOffset() * 60000; #>
					<# var hourDiff = timeEnd - timeStart + offSet; #>
					<# var secDiff = hourDiff / 1000; //in s #>
					<# var minDiff = hourDiff / 60 / 1000; //in minutes #>
					<# var hDiff = hourDiff / 3600 / 1000; //in hours #>
					<# var humanReadable = {}; #>
					<# humanReadable.hours = Math.floor(hDiff); #>
					<# humanReadable.minutes = Math.floor(minDiff - 60 * humanReadable.hours); #>
					<# humanReadable.sec = Math.floor( secDiff - 60 * humanReadable.minutes ); #>
					<# var displayStr = '';#>
					<# if( humanReadable.hours && humanReadable.hours > 23 ){ displayStr = month[lastModified.getMonth()] + ' ' + lastModified.getDate() + ', ' + lastModified.getFullYear()+' '+lastModified.getHours()+':'+lastModified.getMinutes();}else if( humanReadable.hours && humanReadable.hours < 23 ){#>
					<#  displayStr = humanReadable.hours + ' hours ago.'; }else if( humanReadable.minutes && humanReadable.minutes < 59 ){#>
					<#  displayStr = humanReadable.minutes + ' minutes ago.'; }else if( humanReadable.sec && humanReadable.sec < 59 ){ #>
					<# displayStr = humanReadable.sec + ' seconds ago.'; }#>

				<div class="details ">
					<div class="left">
						<# if( data.bulkEdit ){#>
							<span class="total-editing "><?php _e( 'Number of Images Editing: ', 'pmc-gallery-v3' ); ?>{{data.modelIds.length}}</span>
						<#} else { #>
							<div class="filename"><strong><?php _e( 'File name:', 'pmc-gallery-v3' ); ?></strong> {{ data.filename }}</div>
							<div class="filename"><strong><?php _e( 'File type:', 'pmc-gallery-v3' ); ?></strong> {{ data.mime }}</div>
							<div class="uploadedby"><strong><?php _e( 'Uploaded by:', 'pmc-gallery-v3' ); ?></strong> {{ data.authorName }}</div>
							<div class="uploaded"><strong><?php _e( 'Uploaded on:', 'pmc-gallery-v3' ); ?></strong> {{ data.dateFormatted }}</div>
							<# if ( ! data.bulkEdit ) { #>
								<div class="attachmentusedin"><strong><?php esc_html_e( 'Attachment used in', 'pmc-gallery-v3' ); ?></strong> {{ data.attachment_count }} <?php esc_html_e( 'galleries', 'pmc-gallery-v3' ); ?></div>
							<# } #>
							<div class="file-size"><strong><?php _e( 'File size:', 'pmc-gallery-v3' ); ?></strong> {{ data.filesizeHumanReadable }}</div>
							<# if ( 'image' === data.type && ! data.uploading ) { #>
							<# if ( data.width && data.height ) { #>
							<div class="dimensions"><strong><?php _e( 'Dimensions:', 'pmc-gallery-v3' ); ?></strong> {{ data.width }} &times; {{ data.height }}</div>
							<# } #>
							<# } #>

							<# if ( data.fileLength ) { #>
							<div class="file-length"><strong><?php _e( 'Length:', 'pmc-gallery-v3' ); ?></strong> {{ data.fileLength }}</div>
							<# } #>

							<# if ( 'audio' === data.type && data.meta.bitrate ) { #>
							<div class="bitrate">
								<strong><?php _e( 'Bitrate:', 'pmc-gallery-v3' ); ?></strong> {{ Math.round( data.meta.bitrate / 1000 ) }}kb/s
								<# if ( data.meta.bitrate_mode ) { #>
								{{ ' ' + data.meta.bitrate_mode.toUpperCase() }}
								<# } #>
							</div>
							<# } #>
							<div class="compat-meta">
								<# if ( data.compat && data.compat.meta ) { #>{{{ data.compat.meta }}}<# } #>
							</div>
						<# } #>
					</div>
					<div class="right">
						<span class="settings-save-status">
							<span class="spinner"></span>
							<span class="saved"><?php esc_html_e( 'Saved.', 'pmc-gallery-v3' ); ?></span>
							<span class="required"><?php esc_html_e( 'Please fill in the required fields.', 'pmc-gallery-v3' ); ?></span>
						</span>
						<# if( ! _.isEmpty(displayStr) ){ #>
							<span class="settings-modified">last saved {{displayStr}} </span>
						<# } #>
					</div>
				</div>

				<div class="settings">
					<label class="setting {{hideOnBulkEdit}}" data-setting="url">
						<span class="name"><?php _e( 'URL', 'pmc-gallery-v3' ); ?></span>
						<input type="text" value="{{ data.url }}" readonly />
					</label>
					<# var maybeReadOnly = data.can.save || data.allowLocalEdits ? '' : 'readonly'; #>
					<?php if ( post_type_supports( 'attachment', 'title' ) ) : ?>
						<label class="setting" data-setting="title">
							<span class="name"><?php _e( 'Title', 'pmc-gallery-v3' ); ?>
								<a href="#" class="dashicons dashicons-editor-help imgedit-help-toggle" title="<?php esc_attr_e( 'The Photo Title can be the similar to the Gallery Title. It indicates that the image is part of the series of images that makes up the gallery and should contain the focus keyphrase. Ex.: '.date('Y').' Grammy Awards Red Carpet', 'pmc-gallery-v3' ); ?>">
								</a>
							</span>
							<input type="text" value="{{ data.title }}" {{ maybeReadOnly }} />
						</label>
					<?php endif; ?>
					<# if ( 'audio' === data.type ) { #>
					<?php
					foreach ( array(
				'artist' => __( 'Artist' ),
				'album'	 => __( 'Album' ),
					) as $key => $label ) :
						?>
						<label class="setting" data-setting="<?php echo esc_attr( $key ) ?>">
							<span class="name"><?php echo esc_html( $label ); ?></span>
							<input type="text" value="{{ data.<?php echo esc_attr( $key ); ?> || data.meta.<?php echo esc_attr( $key ); ?> || '' }}" />
						</label>
					<?php endforeach; ?>
					<# } #>
					<div class="setting" data-setting="caption">
						<span class="name"><?php _e( 'Caption', 'pmc-gallery-v3' ); ?>
							<a href="#" class="dashicons dashicons-editor-help imgedit-help-toggle" title="<?php esc_attr_e( 'This is the text that accompanies the image. It should not be the same as the Photo Title. When possible, the photo caption should contain the focus keyphrase. If the photo is of a person or persons, their names should be in the caption. Ex: Adele and Beyonce at the '.date('Y').' Grammys', 'pmc-gallery-v3' ); ?>">
							</a>
						</span>
						<textarea {{ maybeReadOnly }}>{{ data.caption }}</textarea>
					</div>
					<# if ( 'image' === data.type ) { #>
					<label class="setting" data-setting="alt">
						<span class="name"><?php _e( 'Alt Text', 'pmc-gallery-v3' ); ?>
							<a href="#" class="dashicons dashicons-editor-help imgedit-help-toggle" title="<?php esc_attr_e( 'The Alt Text tells the search engines what the photo is, is used in image search, and can bring more traffic to the gallery. When writing the Alt Text: Describe what the image is. Do not use punctuation or hyphens. If the image contains a person or persons, use their names. Ex: '.date('Y').' Grammy Awards Adele And Beyonce On The Red Carpet', 'pmc-gallery-v3' ); ?>">
							</a>
						</span>
						<input type="text" value="{{ data.alt }}" {{ maybeReadOnly }} />
					</label>
					<?php if ( 'yes' === cheezcap_get_option( 'pmc_gallery_enable_pinterest_description' ) ) : ?>
						<label class="setting" data-setting="pinterest_description">
							<span class="name"><?php esc_html_e( 'Pinterest Description', 'pmc-gallery-v3' ); ?>
							</span>
							<input type="text" value="{{ data.pinterest_description }}" {{ maybeReadOnly }} />
						</label>
					<?php endif; ?>
					<# } #>
					<label class="setting" data-setting="description">
						<span class="name"><?php _e( 'Description', 'pmc-gallery-v3' ); ?>
							<a href="#" class="dashicons dashicons-editor-help imgedit-help-toggle" title="<?php esc_attr_e( 'this is the Description.', 'pmc-gallery-v3' ); ?>">
							</a>
						</span>
						<textarea {{ maybeReadOnly }}>{{ data.description }}</textarea>
					</label>
					<# if ( data.uploadedToTitle && !data.bulkEdit ) { #>
					<label class="setting">
						<span class="name"><?php _e( 'Uploaded To', 'pmc-gallery-v3' ); ?></span>
						<# if ( data.uploadedToLink ) { #>
						<span class="value"><a href="{{ data.uploadedToLink }}">{{ data.uploadedToTitle }}</a></span>
						<# } else { #>
						<span class="value">{{ data.uploadedToTitle }}</span>
						<# } #>
					</label>
					<# } #>
					<div class="attachment-compat"></div>
				</div>

				<# if ( ! data.bulkEdit ) { #>
				<div class="actions">
					<a class="view-attachment" href="{{ data.link }}"><?php _e( 'View attachment page', 'pmc-gallery-v3' ); ?></a>
					<# if ( data.can.save ) { #> |
					<a href="post.php?post={{ data.id }}&action=edit"><?php _e( 'Edit more details', 'pmc-gallery-v3' ); ?></a>
					<# } #>
					<# if ( ! data.uploading && data.can.remove ) { #> |
					<?php if ( MEDIA_TRASH ): ?>
						<# if ( 'trash' === data.status ) { #>
						<button type="button" class="button-link untrash-attachment"><?php _e( 'Untrash', 'pmc-gallery-v3' ); ?></button>
						<# } else { #>
						<button type="button" class="button-link trash-attachment"><?php _ex( 'Trash', 'verb' ); ?></button>
						<# } #>
					<?php else: ?>
						<button type="button" class="button-link delete-attachment"><?php _e( 'Delete Permanently', 'pmc-gallery-v3' ); ?></button>
					<?php endif; ?>
					<# } #>
				</div>
				<# } #>
			</div>
		</script>
		<script type="text/html" id="tmpl-attachment">
			<#
				var created_data_label = 'Created time is not available.';
				if ( 'undefined' !== typeof data.attachment_created_timestamp && 0 != data.attachment_created_timestamp ) {
					data.attachment_created_timestamp = parseInt(data.attachment_created_timestamp,0);
					var date = new Date( data.attachment_created_timestamp * 1000 ); // Convert into Seconds.
					created_data_label = date.toUTCString();
					created_data_label = created_data_label.replace('GMT','');
					created_data_label = created_data_label.trim();
					created_data_label = 'Created on ' + created_data_label;
				}
			#>
			<div class="attachment-preview js--select-attachment type-{{ data.type }} subtype-{{ data.subtype }} {{ data.orientation }}">
				<div class="thumbnail">
					<# if ( data.uploading ) { #>
						<div class="media-progress-bar"><div style="width: {{ data.percent }}%"></div></div>
					<# } else if ( 'image' === data.type && data.sizes ) { #>
						<div class="centered">
							<img src="{{ data.size.url }}" draggable="false" alt="" />
						</div>
					<# } else { #>
						<div class="centered">
							<# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
								<img src="{{ data.image.src }}" class="thumbnail" draggable="false" alt="" />
							<# } else if ( data.sizes && data.sizes.medium ) { #>
								<img src="{{ data.sizes.medium.url }}" class="thumbnail" draggable="false" alt="" />
							<# } else { #>
								<img src="{{ data.icon }}" class="icon" draggable="false" alt="" />
							<# } #>
						</div>
						<div class="filename">
							<div>{{ data.filename }}</div>
						</div>
					<# } #>
				</div>
				<# if ( 'undefined' !== typeof data.attachment_created_timestamp && 0 != data.attachment_created_timestamp ) { #>
					<span href="javascript:" class="button-link attachment-calender media-modal-icon imgedit-help-toggle" title="{{created_data_label}}"><span class="dashicons dashicons-calendar"></span><span class="screen-reader-text"><?php _e( 'Created Date' ); ?></span></span>
				<# } #>
				<# if ( data.buttons.close ) { #>
					<button type="button" class="button-link attachment-close media-modal-icon"><span class="screen-reader-text"><?php _e( 'Remove' ); ?></span></button>
				<# } #>
			</div>
			<# if ( data.buttons.check ) { #>
				<button type="button" class="button-link check" tabindex="-1"><span class="media-modal-icon"></span><span class="screen-reader-text"><?php _e( 'Deselect' ); ?></span></button>
			<# } #>
			<#
			var maybeReadOnly = data.can.save || data.allowLocalEdits ? '' : 'readonly';
			if ( data.describe ) {
				if ( 'image' === data.type ) { #>
					<input type="text" value="{{ data.caption }}" class="describe" data-setting="caption"
						placeholder="<?php esc_attr_e('Caption this image&hellip;'); ?>" {{ maybeReadOnly }} />
				<# } else { #>
					<input type="text" value="{{ data.title }}" class="describe" data-setting="title"
						<# if ( 'video' === data.type ) { #>
							placeholder="<?php esc_attr_e('Describe this video&hellip;'); ?>"
						<# } else if ( 'audio' === data.type ) { #>
							placeholder="<?php esc_attr_e('Describe this audio file&hellip;'); ?>"
						<# } else { #>
							placeholder="<?php esc_attr_e('Describe this media file&hellip;'); ?>"
						<# } #> {{ maybeReadOnly }} />
				<# }
			} #>
		</script>
		<?php
	}

	function save_post( $post_id ) {
		if (
				defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || defined( 'DOING_AJAX' ) && DOING_AJAX || !current_user_can( 'edit_post', $post_id ) || \PMC_Gallery_Defaults::name !== get_post_type()
		) {
			return;
		}

		// If featured image is not set, then set first attachment of gallery as featured image.
		$gallery_featured_id = get_post_thumbnail_id( $post_id );
		if ( empty( $gallery_featured_id ) ) {
			// get attachment ids of gallery post.
			$gallery_meta = get_post_meta( $post_id, \PMC_Gallery_Defaults::name, true );
			if ( is_array( $gallery_meta ) && count( $gallery_meta ) > 0 ) {
				// Pick the first image from the list to be the featured image
				set_post_thumbnail( $post_id, intval( $gallery_meta[0] ) );
			}
		}
	}

	public function enqueue_admin_ui_simplification_stuff( $hook ) {

		if ( $hook !== 'post-new.php' && $hook !== 'post.php' ) {
			return;
		}

		wp_enqueue_style( \PMC_Gallery_Defaults::name . '-admin-ui-simplification-css', PMC_GALLERY_PLUGIN_URL . 'css/admin-ui-simplification.css' );
	}

	/**
	 * Hooked into 'hidden_meta_boxes' filter, this method hides all un-needed
	 * metaboxes on Gallery add/edit pages in wp-admin.
	 *
	 * @ticket PPT-6845
	 *
	 * @param array $hidden Array containing IDs of all metaboxes that will be hidden
	 * @param WP_Screen $current_screen Current screen object
	 * @param bool $use_defaults Whether defaults are in use or not (for hiding/displaying metaboxes)
	 * @return array Array containing IDs of all metaboxes that will be hidden
	 */
	public function hide_unneeded_metaboxes( $hidden, $current_screen, $use_defaults ) {

		if ( empty( $current_screen->id ) || $current_screen->id !== \PMC_Gallery_Defaults::name ) {
			return $hidden;
		}

		if ( !is_array( $hidden ) ) {
			$hidden = array();
		}

		$metaboxes_to_hide = array(
			'postexcerpt',
			'trackbacksdiv',
			'commentstatusdiv',
			'likes_meta',
			'post-meta-inspector',
		);

		return array_filter( array_unique( array_merge( $hidden, $metaboxes_to_hide ) ) );
	}

	/**
	 * Adds new 'Featured Image' column to gallery list table
	 *
	 * @param array $columns Array of column.
	 * @return array
	 */
	public function add_gallery_featured_image_columns( $columns ) {
		$custom_columns = array(
			'cb'				 => '', // This to make sure checkboxes are shown first.
			'gallery_thumbnail'	 => __( 'Feature Image', 'pmc-gallery-v3' ),
		);
		return array_merge( $custom_columns, $columns );
	}

	/**
	 * Adds featured image to featured image column on gallery list,
	 * if featured image is set for the gallery,
	 * else sets first image of the gallery as featured image.
	 *
	 * @version 2018-02-09 brandoncamenisch - feature/PMCVIP-2977:
	 * - Suppressing errors where array is expected for attachement_id
	 *
	 * @param string $column Give current column.
	 * @param int	 $post_id Current post id.
	 */
	public function custom_gallery_featured_image_column( $column, $post_id ) {
		$size = array(
			60,
			60,
		);
		switch ( $column ) {
			case 'gallery_thumbnail' :
				$gallery_featured_id = get_post_thumbnail_id( $post_id );
				// Checks if featured image is set, then return featured images else, get first image from the gallery.
				if ( !empty( $gallery_featured_id ) && is_numeric( $gallery_featured_id ) ) {
					echo wp_kses_post( wp_get_attachment_image( $gallery_featured_id, $size ) );
				} else {
					// Get gallery attachments for $post_id.
					$gallery_featured = get_post_meta( $post_id, \PMC_Gallery_Defaults::name, true );
					if ( ! empty( $gallery_featured ) && is_array( $gallery_featured ) ) {
						// Get first image from gallery stored.
						$attachment_id = array_shift( $gallery_featured );
						if ( is_numeric( $attachment_id ) ) {
							echo wp_kses_post( wp_get_attachment_image( $attachment_id, $size ) );
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
	 * @todo Code coverage has been temporarily disabled for this method as its added as part of a 911. Unit test(s) should be added for this method later.
	 *
	 * @codeCoverageIgnore
	 */
	public function maybe_disable_es_query( array $query_args ) : array {

		if ( ! empty( $query_args['post__in'] ) && isset( $query_args['orderby'] ) && 'post__in' === $query_args['orderby'] ) {
			$query_args['es'] = false;
		}

		return $query_args;
	}

}

Media_Manager::get_instance();
