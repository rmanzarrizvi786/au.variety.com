<?php
namespace PMC\Gallery;

/**
 * Version 2015-03-26 Hau - Add bulk action add attachment tags
 */

use \PMC\Global_Functions\Traits\Singleton;

/**
 * @codeCoverageIgnore
 */
class Attachment_Taxonomy {

	use Singleton;

	/**
	 * Define Constants
	 */
	const SLUG = 'pmc_attachment_tags';

	public function _init() {
		add_action( 'init', array( $this, 'action_init' ) );
		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
	}

	public function action_admin_init() {
		if ( ! apply_filters( 'pmc_gallery_attachment_taxonomy_enable', false ) ) {
			return;
		}

		if ( ! current_user_can( 'upload_files' ) ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
		add_action( 'admin_action_bulk_attachment_tag', array( $this, 'bulk_action_handler' ) ); // Top drop-down
		add_action( 'admin_action_-1', array( $this, 'bulk_action_handler' ) ); // Top drop-down
	}

	public function action_admin_enqueue_scripts( $hook_suffix ) {
		if ( 'upload.php' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_script( 'pmc-gallery-admin-bulk-media-js', PMC_GALLERY_PLUGIN_URL . '/assets/build/js/admin-bulk-media.js', array( 'jquery' ) );
		wp_enqueue_style( 'pmc-gallery-admin-bulk-media-css', PMC_GALLERY_PLUGIN_URL . '/assets/build/css/admin-bulk-media.css' );
	}

	public function bulk_action_handler() {
		$nonce      = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );
		$media      = filter_input( INPUT_GET, 'media', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$action1    = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
		$action2    = filter_input( INPUT_GET, 'action2', FILTER_SANITIZE_STRING );
		$tag_top    = filter_input( INPUT_GET, 'bulk-attachment-tag-top', FILTER_SANITIZE_STRING );
		$tag_bottom = filter_input( INPUT_GET, 'bulk-attachment-tag-bottom', FILTER_SANITIZE_STRING );

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'bulk-media' ) ) {
			return;
		}

		if ( empty( $media ) ) {
			return;
		}

		$tags = array();

		if ( 'bulk_attachment_tag' === $action1 ) {
			$tags = array_merge( $tags, explode( ',', sanitize_text_field( $tag_top ) ) );
		}

		if ( 'bulk_attachment_tag' === $action2 ) {
			$tags = array_merge( $tags, explode( ',', sanitize_text_field( $tag_bottom ) ) );
		}

		$tags = array_unique( array_filter( array_map( 'trim', (array) $tags ) ) );

		if ( empty( $tags ) ) {
			return;
		}

		$ids = array_map( 'intval', (array) $media );

		if ( empty( $ids ) ) {
			return;
		}

		foreach ( $ids as $id ) {
			wp_set_post_terms( $id, $tags, self::SLUG, true );
		}

	}

	public function action_init() {

		$enabled = apply_filters( 'pmc_gallery_attachment_taxonomy_enable', false );

		if ( ! $enabled ) {
			return;
		}

		add_action( 'restrict_manage_posts', array( $this, 'filter_by_tags' ) );
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		// PPT-3729 Add custom taxonomy for images
		$this->register_attachment_taxonomy();
		add_action( 'wp_enqueue_media', array( $this, 'enqueue_admin_scripts' ) );
		add_filter( 'attachment_fields_to_edit', array( $this, 'attachment_fields_to_edit' ), 10, 2 );
		add_filter( 'ajax_query_attachments_args', array( $this, 'filter_attachments_in_admin' ), 10, 1 );
		add_action( 'print_media_templates', array( $this, 'print_media_templates' ) );

	}

	public function register_attachment_taxonomy() {

		$labels = array(
			'name'              => esc_html__( 'Attachment Tags', 'pmc-gallery-v4' ),
			'singular_name'     => esc_html__( 'Attachment Tag', 'pmc-gallery-v4' ),
			'search_items'      => esc_html__( 'Search Attachment Tag', 'pmc-gallery-v4' ),
			'all_items'         => esc_html__( 'All Attachment Tags', 'pmc-gallery-v4' ),
			'parent_item'       => esc_html__( 'Parent Attachment Tag', 'pmc-gallery-v4' ),
			'parent_item_colon' => esc_html__( 'Parent Attachment Tag:', 'pmc-gallery-v4' ),
			'edit_item'         => esc_html__( 'Edit Attachment Tag', 'pmc-gallery-v4' ),
			'update_item'       => esc_html__( 'Update Attachment Tag', 'pmc-gallery-v4' ),
			'add_new_item'      => esc_html__( 'Add New Attachment Tag', 'pmc-gallery-v4' ),
			'new_item_name'     => esc_html__( 'New Attachment Tag Name', 'pmc-gallery-v4' ),
			'menu_name'         => esc_html__( 'Attachment Tag', 'pmc-gallery-v4' ),
		);

		$args = array(
			'labels'             => $labels,
			'hierarchical'       => false,
			'query_var'          => true,
			'rewrite'            => false,
			'show_admin_column'  => true,
			'publicly_queryable' => false,
		);

		register_taxonomy( self::SLUG, 'attachment', $args );

	}

	public function enqueue_admin_scripts( $hook ) {

		wp_enqueue_script( 'suggest' );
		wp_enqueue_script( self::SLUG . '_admin_script', PMC_GALLERY_PLUGIN_URL . 'assets/build/js/admin-attachment-taxonomy.js' );

	}

	public function attachment_fields_to_edit( $form_fields, $post ) {

		$mime_array = array(
			'image/jpeg',
			'image/png',
			'image/gif',
			'image/jpg',
		);

		if ( ! in_array( $post->post_mime_type, $mime_array, true ) ) {
			return $form_fields;
		}

		$form_fields['pmc_gallery_attachment_taxonomy'] = array(
			'label' => '',
			'input' => 'html',
			'html'  => '<script type="text/javascript">pmc_gallery_attachment.set_suggest( "attachments-' . $post->ID . '-' . self::SLUG . '" )</script>',
			'helps' => '',
		);

		return $form_fields;

	}

	public function print_media_templates() {
		?>
		<style type="text/css">
			.ac_results {
				z-index: 160000 !important;
			}

			.media-toolbar-primary.search-form {
				max-width: 50%;
			}
		</style>

		<span id="tmpl-pmc-gallery-attachment-tax-settings">
			<?php esc_html_e( 'Search Media By Tags:', 'pmc-gallery-v4' ); ?>&nbsp;<input type="checkbox" value="1" id="pmc-gallery-media-search-input" name="pmc_attachment_tags">
		</span>
		<?php
	}

	public function filter_attachments_in_admin( $query = array() ) {
		global $pagenow;

		$action      = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
		$media_input = filter_input( INPUT_COOKIE, 'pmc_gallery_mediainput', FILTER_SANITIZE_STRING );

		if ( 'admin-ajax.php' === $pagenow && $action && 'query-attachments' === $action ) {
			return $query;
		}

		if ( empty( $media_input ) ) {
			return $query;
		}

		$query['tax_query'] = array( // @WPCS Slow query okay.
			array(
				'taxonomy' => self::SLUG,
				'terms'    => array( sanitize_text_field( $query['s'] ) ),
				'field'    => 'name',
			),
		);

		unset( $query['s'] );

		return $query;
	}


	public function filter_by_tags() {

		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
		} else {
			$screen = '';
		}

		$_term = filter_input( INPUT_GET, self::SLUG, FILTER_SANITIZE_STRING );

		if ( 'upload' === $screen->id ) {
			$taxonomy   = self::SLUG;
			$attach_tag = get_taxonomy( $taxonomy );

			$term = intval( $_term );

			wp_dropdown_categories(
				array(
					'show_option_all' => esc_html__( 'Show All', 'pmc-gallery-v4' ) . esc_html( $attach_tag->label ),
					'taxonomy'        => $taxonomy,
					'name'            => self::SLUG,
					'orderby'         => 'name',
					'selected'        => $term,
					'hierarchical'    => true,
					'hide_empty'      => false,
					'show_count'      => true,
				)
			);
		}

	}

	public function pre_get_posts( \WP_Query $query ) {

		if ( ! is_admin() ) {
			return $query;
		}

		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
		} else {
			$screen = '';
		}

		if ( empty( $screen->id ) ) {
			return $query;
		}

		if ( 'upload' !== $screen->id ) {
			return $query;
		}

		$_term = \PMC::filter_input( INPUT_GET, self::SLUG, FILTER_SANITIZE_STRING );

		if ( empty( $_term ) ) {
			return $query;
		}

		$tax_query[] = array(
			'taxonomy' => self::SLUG,
			'field'    => 'id',
			'terms'    => array( intval( $_term ) ),
		);

		$tax_query['relation'] = 'OR'; // @codingStandardsIgnoreLine - getText okay.
		$query->set( 'tax_query', $tax_query );

		return $query;

	}

}

// EOF
