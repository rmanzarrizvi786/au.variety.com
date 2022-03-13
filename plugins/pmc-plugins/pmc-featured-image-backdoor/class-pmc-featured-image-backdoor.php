<?php

/**
 *
 *
 * @since 0.1.0
 * @version 0.1.0
 */
class PMC_Featured_Image_Backdoor {

	private $pagename = 'PMC_Featured_Image_Backdoor';

	/**
	 * Hook into actions and filters here, along with any other global setup
	 * that needs to run when this plugin is invoked
	 *
	 * @since 0.1.0
	 * @version 0.1.0
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'wp_ajax_pmc-featured-image-inline-save', array( $this, 'set_featured_image' ) );
		add_filter( 'heartbeat_received', array( $this, 'heartbeat_received' ), 10, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'heartbeat_enqueue' ) );
	}

	public function heartbeat_enqueue( $hook_suffix ) {
		wp_enqueue_script( 'heartbeat' );
	}

	public function heartbeat_received( $response, $data, $screen_id ) {
		$response = wp_check_locked_posts( $response, $data, $screen_id );

		return $response;
	}


	/**
	 * Add the admin menu page
	 *
	 * @since 0.9.0
	 * @version 0.9.0
	 */
	public function admin_menu() {
		// The text to be displayed in the title tags of the page when the menu is selected
		$page_title = __( 'PMC Featured Image', 'pmc-featured-image-backdoor' );

		// The text to be used for the menu
		$menu_title = __( 'Featured Image', 'pmc-featured-image-backdoor' );

		// The capability required for this menu to be displayed to the user.
		$capability = 'edit_others_posts';

		// The slug name to refer to this menu by (should be unique for this menu)
		$menu_slug = __CLASS__;

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
		wp_enqueue_script( 'pmc-featured-image-inline-edit', plugins_url( 'js/pmc-featured-image-inline-edit.js', __FILE__ ), array( 'jquery' ), '0.1.0', true );
		wp_localize_script( 'pmc-featured-image-inline-edit', 'pmc_featured_image_inline_edit_l10n', array(
			'nonce' => wp_create_nonce( '_pmc_featured_image_inline_edit' ),
		) );
		wp_enqueue_media();
	}


	/**
	 * AJAX save action
	 *
	 * @since 0.9.0
	 * @version 0.9.0
	 */
	public function set_featured_image() {
		check_ajax_referer( '_pmc_featured_image_inline_edit', 'backdoor_nonce' );

		header( 'Content-Type: application/json' );

		if ( ! current_user_can( 'edit_others_posts' ) ) {
			wp_die( json_encode( array(
				'error'   => true,
				'message' => 'Are your sure you want to do that?',
			) ) );
		}

		if ( empty( $_POST['attachment_id'] ) || empty( $_POST['post_id'] ) ) {
			wp_die( json_encode( array(
				'error'   => true,
				'message' => 'Valid IDs were not sent.',
			) ) );
		}

		$attachment_id = (int) $_POST['attachment_id'];
		$post_id       = (int) $_POST['post_id'];


		if ( wp_check_post_lock( $post_id ) ) {
			wp_die( json_encode( array(
				'error'   => true,
				'message' => 'Another user is editing this post'
			) ) );
		}

		if ( 'post' !== get_post_type( $post_id ) || 'attachment' !== get_post_type( $attachment_id ) ) {
			wp_die( json_encode( array(
				'error'   => true,
				'message' => 'The correct post types were not sent.',
			) ) );
		}

		if ( set_post_thumbnail( $post_id, $attachment_id ) ) {
			$new_thumb_attr = array( 'style' => 'width:35px;height:35px;float:left;padding: 0 10px 5px 0;' );
			$new_thumb_html = wp_get_attachment_image( $attachment_id, 'thumbnail', false, $new_thumb_attr );
			wp_die( json_encode( array(
				'error'   => false,
				'message' => 'The featured image was updated successfully.',
				'id'      => $post_id,
				'markup'  => $new_thumb_html,
			) ) );
		} else {
			if ( $attachment_id === get_post_thumbnail_id( $post_id ) ) {
				wp_die( json_encode( array(
					'error'   => false,
					'message' => 'The existing featured image was already set, nothing change.',
				) ) );
			} else {
				wp_die( json_encode( array(
					'error'   => true,
					'message' => 'The featured image was unable to be set at this time.',
				) ) );
			}
		}
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

		set_current_screen( 'pmc-featured-image-list-posts' );
		get_current_screen()->post_type = $post_type;

		$wp_list_table = new PMC_Featured_Image_Backdoor_List_Table();

		$wp_list_table->prepare_items();

		$title = $post_type_object->labels->name;

		add_screen_option( 'per_page', array( 'label' => $title, 'default' => 20 ) );

		?>
		<div class="wrap">
			<?php screen_icon( 'edit' ); ?>
			<h2>
				<?php
				_e( 'PMC Set Featured Images', 'pmc-featured-image-backdoor' );

				if ( ! empty( $_REQUEST['s'] ) ) {
					printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;', 'pmc-featured-image-backdoor' ) . '</span>', get_search_query( true ) );
				}
				?>
			</h2>

			<?php $wp_list_table->views(); ?>

			<form id="posts-filter" action="" method="get">

				<?php $wp_list_table->search_box( $post_type_object->labels->search_items, 'post' ); ?>
				<?php
				// Output the page name to the search box, so that searches land back on this page
				?>
				<input type="hidden" name="page" value="<?php echo esc_attr( $this->pagename ); ?>"/>

				<?php $wp_list_table->display(); ?>

			</form>
			<br class="clear"/>
		</div>
		<?php
	}

}
