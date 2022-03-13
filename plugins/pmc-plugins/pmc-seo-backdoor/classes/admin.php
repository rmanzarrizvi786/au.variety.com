<?php

namespace PMC\SEO_Backdoor;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

/**
 *
 *
 * @since 0.9.0
 * @version 0.9.0
 */
class Admin {

	use Singleton;

	/**
	 * Hook into actions and filters here, along with any other global setup
	 * that needs to run when this plugin is invoked
	 *
	 * @since 0.9.0
	 * @version 0.9.0
	 */
	protected function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'wp_ajax_pmc-seo-inline-save', array( $this, 'inline_save' ) );

		// Early save_post to compare old vs new content
		add_action( 'save_post', array( $this, 'save_post_early' ), 1, 2 );

		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

		add_action( 'publish_post', array( $this, 'publish_post' ), 10, 2 );
	}

	/**
	 *
	 * @since 0.9.0
	 * @version 0.9.0
	 */
	public function edit_post_init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'edit_post_enqueue' ) );
	}

	/**
	 *
	 * @since 0.9.0
	 * @version 0.9.0
	 */
	public function edit_post_enqueue() {
		wp_register_script( 'pmc-seo-poll', plugins_url( 'js/pmc-seo-poll.js', __FILE__ ), array( 'jquery' ), '0.9.0' );
		wp_enqueue_script( 'pmc-seo-poll' );

		wp_localize_script( 'pmc-seo-poll', 'pmc_seo_poll_l10n', array(
			'_pmc_seo_poll' => wp_create_nonce( 'pmc-seo-poll-nonce' ),
		) );
	}

	/**
	 *
	 * @since 0.9.0
	 * @version 0.9.0
	 */
	public function update_seo() {
		check_ajax_referer( 'pmc-seo-poll-nonce', '_pmc_seo_poll' );

		$post_id = ( ! empty( $_GET['post_ID'] ) ) ? intval( $_GET['post_ID'] ) : 0;
		if ( ! $post_id ) {
			wp_die();
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die();
		}

		// Always populate from the _pmc_seo_* values, they are the truth
		$post_meta = array(
			'_pmc_seo_title',
			'_pmc_seo_description',
			'_pmc_seo_keywords',
			'_pmc_seo_slug',
		);

		$return = array();

		foreach ( $post_meta as $meta_key ) {
			// Only return values that exist
			$meta_value = get_post_meta( $post_id, $meta_key, true );
			if ( $meta_value ) {
				$return[ $meta_key ] = $this->sanitize_meta( $meta_key, $meta_value );
			}
		}

		wp_send_json( $return );
	}

	/**
	 * Add the admin menu page
	 *
	 * @since 0.9.0
	 * @version 0.9.0
	 */
	public function admin_menu() {
		// The text to be displayed in the title tags of the page when the menu is selected
		$page_title = __( 'PMC Post SEO', 'pmc-seo-backdoor' );

		// The text to be used for the menu
		$menu_title = __( 'SEO', 'pmc-seo-backdoor' );

		// The capability required for this menu to be displayed to the user.
		$capability = 'edit_others_posts';

		// The slug name to refer to this menu by (should be unique for this menu)
		$menu_slug = 'PMC_SEO_Backdoor';

		// The function to be called to output the content for this page.
		$callback_function = array( $this, 'view_posts_page' );

		// Add the page to the Posts menu
		$page_hook = add_posts_page( $page_title, $menu_title, $capability, $menu_slug, $callback_function );

		// Hook in an init action to for this specific admin page
		add_action( 'load-' . $page_hook, array( $this, 'page_init' ) );
	}

	/**
	 * Initialize stuff for this specific admin page.  This action is hooked-in
	 * in $this->admin_menu()
	 *
	 * @since 0.9.0
	 * @version 0.9.0
	 */
	public function page_init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_for_page' ) );
	}

	/**
	 * Enqueue scripts for this specific admin page
	 *
	 * @since 0.9.0
	 * @version 0.9.0
	 */
	public function enqueue_scripts_for_page() {
		wp_register_script( 'pmc-seo-inline-edit', plugins_url( '/js/pmc-seo-inline-edit.js', __DIR__ ), array(
			'jquery',
			'suggest'
		), '0.9.1' );
		wp_enqueue_script( 'pmc-seo-inline-edit' );

		wp_localize_script( 'pmc-seo-inline-edit', 'pmc_seo_inline_edit_l10n', array(
			'error'   => __( 'Error while saving the changes.', 'pmc-seo-backdoor' ),
			'notitle' => __( '(no title)', 'pmc-seo-backdoor' ),
		) );

	}

	/**
	 * Populate SEO array with meta depending on which SEO plugin is enabled
	 *
	 * @since 2015-08-05 - Mike Auteri - PPT-5235: Track use of SEO Overrides
	 * @version 2015-08-05
	 *
	 * @return array
	 */
	private function get_seo_array() {
		$seo_array = array(
			'title'       => '',
			'description' => '',
			'keywords'    => '',
		);
		if ( class_exists( 'Add_Meta_Tags' ) ) {
			$seo_array['title']       = 'mt_seo_title';
			$seo_array['description'] = 'mt_seo_description';
			$seo_array['keywords']    = 'mt_seo_keywords';
		} else if ( function_exists( 'wpseo_get_value' ) ) {
			$seo_array['title']       = '_yoast_wpseo_title';
			$seo_array['description'] = '_yoast_wpseo_metadesc';
			$seo_array['keywords']    = '_yoast_wpseo_metakeywords';
		}

		return $seo_array;
	}

	/**
	 * Ensure the post has the latest _pmc_seo_* meta when saving
	 *
	 * @since 0.9.0
	 * @version 0.9.0
	 *
	 * @param int $post_id
	 * @param obj $post
	 *
	 * @return void
	 */
	public function save_post( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions
		if ( empty( $post->post_type ) || 'post' !== $post->post_type || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Post Stats class for taxonomy and helper functions
		$post_stats = PMC\SEO_Backdoor\Post_Stats::get_instance();

		$seo_array = $this->get_seo_array();

		// Sanitize and save
		// Since it's possible that a key doesn't exist based on the SEO plugin
		// used, do a sanity check to make sure there's a key.
		$seo_title = get_post_meta( $post_id, '_pmc_seo_title', true );
		if ( $seo_title && $seo_array['title'] ) {
			$this->save_post_meta( $post_id, $seo_array['title'], $seo_title );
			delete_post_meta( $post_id, '_pmc_seo_title' );
		}

		$seo_description = get_post_meta( $post_id, '_pmc_seo_description', true );
		if ( $seo_description && $seo_array['description'] ) {
			$this->save_post_meta( $post_id, $seo_array['description'], $seo_description );
			delete_post_meta( $post_id, '_pmc_seo_description' );
		}

		$seo_keywords = get_post_meta( $post_id, '_pmc_seo_keywords', true );
		if ( $seo_keywords && $seo_array['keywords'] ) {
			$this->save_post_meta( $post_id, $seo_array['keywords'], $seo_keywords );
			delete_post_meta( $post_id, '_pmc_seo_keywords' );
		}

		// PPT-5178: SEO Flagging drafts for min level of completion
		// * 100 words or more in post body
		// * Has a headline
		// * All non-Published or non-Scheduled statuses
		$cached_count = wp_cache_get( 'pmc_seo_count', $post->post_type );
		$has_seo_meta = get_post_meta( $post_id, 'pmc_seo_ready' );

		$statuses = $this->get_allowed_statuses(); // Use pmc_seo_backdoor_status_whitelist filter to hook into this

		if ( in_array( $post->post_status, $statuses, true ) && ! empty( $post->post_title ) && str_word_count( $post->post_content ) >= 100 ) {
			update_post_meta( $post_id, 'pmc_seo_ready', 1 );
			if ( ! $has_seo_meta && $cached_count !== false ) {
				$cached_count ++;
			}
		} else {
			delete_post_meta( $post_id, 'pmc_seo_ready' );
			if ( $has_seo_meta && $cached_count !== false ) {
				$cached_count --;
			}
		}
		if ( $cached_count !== false ) {
			wp_cache_set( 'pmc_seo_count', $cached_count, $post->post_type, 3600 );
		}
	}

	/**
	 * Early hook to save Post Status taxonomy if set by the editor
	 *
	 * @since 08-07-2015 - Mike Auteri - PPT-5235: Track use of SEO Overrides
	 * @version 08-07-2015
	 *
	 * @param integer $post_id
	 * @param object $post
	 */
	public function save_post_early( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions
		if ( empty( $post->post_type ) || 'post' !== $post->post_type || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Post Stats class for taxonomy and helper functions
		$post_stats = PMC\SEO_Backdoor\Post_Stats::get_instance();

		$seo_array       = $this->get_seo_array();
		$original_status = ! empty( $_POST['original_post_status'] ) ? sanitize_text_field( $_POST['original_post_status'] ) : '';
		$seo_title       = get_post_meta( $post_id, '_pmc_seo_title', true );
		$post_title      = ! empty( $_POST[ $seo_array['title'] ] ) ? sanitize_text_field( $_POST[ $seo_array['title'] ] ) : '';
		if ( ! $seo_title && ! empty( $_POST ) && $post_stats->editor_check( $post_id, $seo_array['title'], $post_title, $original_status ) ) {
			$post_stats->add_post_tracker( $post_id, $seo_array['title'], 'editor' );
		}

		$seo_description  = get_post_meta( $post_id, '_pmc_seo_description', true );
		$post_description = ! empty( $_POST[ $seo_array['description'] ] ) ? sanitize_text_field( $_POST[ $seo_array['description'] ] ) : '';
		if ( ! $seo_description && ! empty( $_POST ) && $post_stats->editor_check( $post_id, $seo_array['description'], $post_description, $original_status ) ) {
			$post_stats->add_post_tracker( $post_id, $seo_array['description'], 'editor' );
		}

		$seo_keywords  = get_post_meta( $post_id, '_pmc_seo_keywords', true );
		$post_keywords = ! empty( $_POST[ $seo_array['keywords'] ] ) ? sanitize_text_field( $_POST[ $seo_array['keywords'] ] ) : '';
		if ( ! $seo_keywords && ! empty( $_POST ) && $post_stats->editor_check( $post_id, $seo_array['keywords'], $post_keywords, $original_status ) ) {
			$post_stats->add_post_tracker( $post_id, $seo_array['keywords'], 'editor' );
		}

		// The SEO Backdoor slug needs to be saved early or it won't save
		$seo_slug = get_post_meta( $post_id, '_pmc_seo_slug', true );
		if ( $seo_slug && ( 'publish' !== $post->post_status ) && ( $seo_slug !== $post->post_name ) ) {
			global $wpdb;
			$post->post_name = sanitize_title( $seo_slug );
			// Pass a fake status to wp_unique_post_slug()
			// @see http://core.trac.wordpress.org/ticket/20419
			$post->post_name = wp_unique_post_slug( $post->post_name, $post->ID, 'publish', $post->post_type, $post->post_parent );
			$data            = array( 'post_name' => $post->post_name );
			$where           = array( 'ID' => $post_id );
			$wpdb->update( $wpdb->posts, $data, $where );
			$post_stats->add_post_tracker( $post_id, 'post_name', 'seo' );
			delete_post_meta( $post_id, '_pmc_seo_slug' );
			// Not deleting _pmc_seo_slug yet, need to check it in save_post method. It is deleted there.
		}
	}

	/**
	 * On publish, check if slug was changed by SEO or if it is the same as post title.
	 *
	 * @since 2015-08-25 - Mike Auteri
	 * @version 2015-08-25 - Mike Auteri - PPT-5235
	 *
	 * @param integer $post_id
	 * @param object $post
	 *
	 * @return void
	 */
	public function publish_post( $post_id, $post ) {
		// Post Stats class for taxonomy and helper functions
		$post_stats = PMC\SEO_Backdoor\Post_Stats::get_instance();

		// Remove any dash plus number appended to slug for a better comparison
		$post_name            = preg_replace( '/(-\d+)$/', '', $post->post_name );
		$original_post_status = empty( $_POST['original_post_status'] ) ? '' : $_POST['original_post_status'];

		if ( ! has_term( 'seo_backdoor_override_url', 'pmc_post_stats', $post ) && sanitize_title( $post->post_title ) !== $post_name && $original_post_status !== 'publish' ) {
			$post_stats->add_post_tracker( $post_id, 'post_name', 'editor' );
		}
	}

	/**
	 * Filtered list of whitelisted post statuses for SEO Ready
	 *
	 * @since 2015-07-29 - Mike Auteri
	 * @version 2015-07-29 - Mike Auteri - PPT-5178
	 *
	 * @return array List of whitelisted post statuses. By default, non-Scheduled and non-Published
	 */
	protected static function get_allowed_statuses() {
		$status_array = array(
			'in-progress',
			'assigned',
			'pitch',
			'draft',
			'pending',
		);

		/**
		 * The filter pmc_seo_backdoor_status_whitelist gives the ability to hook in and whitelist custom statuses for SEO Ready in the SEO Backdoor plugin. Typically (and by default) this includes statuses that are of non-Scheduled and non-Published types.
		 *
		 * @since 2015-07-29 - Mike Auteri
		 * @version 2015-07-29 - Mike Auteri - PPT-5178
		 */
		$statuses = apply_filters( 'pmc_seo_backdoor_status_whitelist', $status_array );

		// In case filter returns something other than an array...
		if ( ! is_array( $statuses ) ) {
			$statuses = $status_array;
		}

		return $statuses;
	}

	/**
	 * AJAX save action
	 *
	 * @since 0.9.0
	 * @version 0.9.0
	 */
	public function inline_save() {
		global $hook_suffix;

		check_ajax_referer( 'pmc-seo-inline-edit-nonce', '_pmc_seo_inline_edit' );

		$post_id = ( ! empty( $_POST['post_ID'] ) ) ? intval( $_POST['post_ID'] ) : 0;
		if ( ! $post_id ) {
			wp_die();
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( __( 'You are not allowed to edit this post.', 'pmc-seo-backdoor' ) );
		}

		if ( wp_check_post_lock( $post_id ) ) {
			wp_die( __( 'Another user is editing this post' ) );
		}

		set_current_screen( $_POST['screen'] );

		// Sanitize and save

		// Populates SEO array with meta depending on which SEO plugin is enabled
		$seo_array = $this->get_seo_array();

		// Sanitize and save.  Save both _pmc_seo_* and the actual post meta so
		// that we can protect against potential race conditions when someone
		// saves a post.
		if ( ! empty( $_POST['pmc_seo_title'] ) ) {
			if ( $seo_array['title'] ) {
				$this->save_post_meta( $post_id, $seo_array['title'], $_POST['pmc_seo_title'] );
			}
			$this->save_post_meta( $post_id, '_pmc_seo_title', $_POST['pmc_seo_title'] );
		}

		if ( ! empty( $_POST['pmc_seo_description'] ) ) {
			if ( $seo_array['description'] ) {
				$this->save_post_meta( $post_id, $seo_array['description'], $_POST['pmc_seo_description'] );
			}
			$this->save_post_meta( $post_id, '_pmc_seo_description', $_POST['pmc_seo_description'] );
		}

		if ( ! empty( $_POST['pmc_seo_keywords'] ) ) {
			if ( $seo_array['keywords'] ) {
				$this->save_post_meta( $post_id, $seo_array['keywords'], $_POST['pmc_seo_keywords'] );
			}
			$this->save_post_meta( $post_id, '_pmc_seo_keywords', $_POST['pmc_seo_keywords'] );
		}

		if ( ! empty( $_POST['pmc_seo_slug'] ) && ! empty( $_POST['pmc_seo_slug'] ) ) {
			$pmc_seo_slug = sanitize_title( $_POST['pmc_seo_slug'] );
			// Pass a fake status to wp_unique_post_slug()
			// @see http://core.trac.wordpress.org/ticket/20419
			$pmc_seo_slug = wp_unique_post_slug( $pmc_seo_slug, $post_id, 'publish', 'post', 0 );

			$args = array(
				'ID'        => $post_id,
				'post_name' => $pmc_seo_slug,
			);
			$this->save_post_meta( $post_id, '_pmc_seo_slug', $pmc_seo_slug );
			// Since post_name isn't meta, need to track it here for slug.
			$post_stats = PMC\SEO_Backdoor\Post_Stats::get_instance();
			$post_stats->add_post_tracker( $post_id, 'post_name', 'seo' );
			wp_update_post( $args );
		}

		$wp_list_table = new PMC\SEO_Backdoor\List_Posts();

		set_current_screen( 'pmc-seo-list-posts' );
		get_current_screen()->post_type = 'post';

		global $mode;
		$mode = sanitize_text_field( $_POST['post_view'] );

		$wp_list_table->display_rows( array( get_post( $post_id ) ) );

		wp_die();
	}

	/**
	 * Get instance of PMC\SEO_Backdoor\List_Posts for phpunit
	 *
	 * @since 2015-07-20
	 * @version 2015-07-20 Mike Auteri - PPT-5178
	 *
	 * @return object
	 */
	public function get_list_table_instance() {
		return new PMC\SEO_Backdoor\List_Posts();
	}

	/**
	 * Sanitize and save the post meta.
	 *
	 * @see mt_seo_save_meta_field()
	 * @since 0.9.0
	 * @version 0.9.0
	 *
	 * @param int $post_id
	 * @param string $meta_key
	 * @param mixed $meta_value
	 *
	 * @return void
	 */
	public function save_post_meta( $post_id, $meta_key, $meta_value ) {
		// Already have data?
		$old_value = get_post_meta( $post_id, $meta_key, true );

		$meta_value = $this->sanitize_meta( $meta_key, $meta_value );

		// Nothing new, and we're not deleting the old
		if ( ! $meta_value && ! $old_value ) {
			return;
		}

		// Nothing new, and we're deleting the old
		if ( ! $meta_value && $old_value ) {
			delete_post_meta( $post_id, $meta_key );

			return;
		}

		// Nothing to change
		if ( $meta_value === $old_value ) {
			return;
		}

		// Save the data
		if ( $old_value ) {
			update_post_meta( $post_id, $meta_key, sanitize_text_field( $meta_value ) );
		} else {
			$success = add_post_meta( $post_id, $meta_key, sanitize_text_field( $meta_value ), true );
			if ( ! $success ) {
				// Just in case it was deleted and saved as ""
				update_post_meta( $post_id, $meta_key, sanitize_text_field( $meta_value ) );
			}
		}
		// Post Stats class for taxonomy and helper functions
		$post_stats = PMC\SEO_Backdoor\Post_Stats::get_instance();

		// Populates SEO array with meta depending on which SEO plugin is enabled
		$seo_array = $this->get_seo_array();
		if ( in_array( $meta_key, $seo_array, true ) ) {
			$post_stats->add_post_tracker( $post_id, $meta_key, 'seo' );
		}
	}

	/**
	 * Sanitize the post meta values
	 *
	 * @since 0.9.0
	 * @version 0.9.0
	 *
	 * @param string $meta_key Used to determine a sanitization strategy
	 * @param mixed $meta_value
	 *
	 * @return mixed $meta_value
	 */
	public function sanitize_meta( $meta_key, $meta_value ) {
		$meta_value = trim( stripslashes( $meta_value ) );
		$meta_value = wp_filter_post_kses( $meta_value );

		return $meta_value;
	}

	/**
	 * Output the post list
	 *
	 * @since 0.9.0
	 * @version 0.9.0
	 */
	public function view_posts_page() {
		global $typenow, $post_type, $post_type_object;
		$typenow          = 'post';
		$post_type        = $typenow;
		$post_type_object = get_post_type_object( $post_type );

		set_current_screen( 'pmc-seo-list-posts' );
		get_current_screen()->post_type = $post_type;

		$wp_list_table = new PMC\SEO_Backdoor\List_Posts();

		$wp_list_table->prepare_items();

		$title = $post_type_object->labels->name;

		add_screen_option( 'per_page', array( 'label' => $title, 'default' => 20 ) );

		?>
		<div class="wrap">
			<?php screen_icon( 'edit' ); ?>
			<h2>
				<?php
				esc_html_e( 'PMC Post SEO', 'pmc-seo-backdoor' );

				if ( ! empty( $_GET['s'] ) && $_GET['s'] ) {
					printf( '<span class="subtitle">' . esc_html__( 'Search results for &#8220;%s&#8221;', 'pmc-seo-backdoor' ) . '</span>', esc_html( get_search_query() ) );
				}
				?>
			</h2>

			<?php $wp_list_table->views(); ?>

			<form id="posts-filter" action="" method="get">

				<?php $wp_list_table->search_box( $post_type_object->labels->search_items, 'post' ); ?>
				<?php
				// Output the page name to the search box, so that searches land back on this page
				?>
				<input type="hidden" name="page" value="<?php echo esc_attr( 'PMC_SEO_Backdoor' ); ?>"/>

				<?php $wp_list_table->display(); ?>

			</form>

			<?php
			if ( $wp_list_table->has_items() ) {
				$wp_list_table->inline_edit();
			}
			?>

			<div id="ajax-response"></div>
			<br class="clear"/>
		</div>
		<?php
	}

}

// EOF
