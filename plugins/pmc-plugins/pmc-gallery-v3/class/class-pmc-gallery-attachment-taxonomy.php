<?php
/**
 * Version 2015-03-26 Hau - Add bulk action add attachment tags
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Gallery_Attachment_Taxonomy {

	use Singleton;

	/**
	 * Define Constants
	 */
	const SLUG = 'pmc_attachment_tags';

	public function __construct() {
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
		add_action( 'admin_action_bulk_attachment_tag', array( $this, 'bulk_action_handler' ) ); // Top drowndown
		add_action( 'admin_action_-1', array( $this, 'bulk_action_handler' ) ); // Top drowndown
	}

	public function action_admin_enqueue_scripts( $hook_suffix ) {
		if ( 'upload.php' != $hook_suffix ) {
			return;
		}
		wp_enqueue_script( 'pmc-gallery-admin-bulk-media-js', PMC_GALLERY_PLUGIN_URL . '/js/admin-bulk-media.js', array( 'jquery' ) );
		wp_enqueue_style( 'pmc-gallery-admin-bulk-media-css', PMC_GALLERY_PLUGIN_URL . '/css/admin-bulk-media.css' );
	}

	public function bulk_action_handler() {

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-media' ) ) {
			return;
		}
		if ( empty( $_REQUEST['media'] ) ) {
			return;
		}
		$tags = array();
		if ( 'bulk_attachment_tag' == $_REQUEST['action'] ) {
			$tags = array_merge( $tags, explode( ',', sanitize_text_field( $_REQUEST['bulk-attachment-tag-top'] ) ) );
		}
		if ( 'bulk_attachment_tag' == $_REQUEST['action2'] ) {
			$tags = array_merge( $tags, explode( ',', sanitize_text_field( $_REQUEST['bulk-attachment-tag-bottom'] ) ) );
		}
		$tags = array_unique( array_filter( array_map( 'trim', $tags ) ) );
		if ( empty( $tags ) ) {
			return;
		}

		$ids = array_map( 'intval', $_REQUEST['media'] );

		if ( empty( $ids ) ) {
			return;
		}

		foreach ( $ids as $id ) {
			wp_set_post_terms( $id, $tags, self::SLUG, true );
		}

	}

	public function action_init() {

		$enabled = apply_filters( 'pmc_gallery_attachment_taxonomy_enable', false );

		if ( !$enabled ) {
			return;
		}
		add_action( 'restrict_manage_posts', array( $this, 'filter_by_tags' ) );
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		//PPT-3729 Add custom taxonomy for images
		$this->register_attachment_taxonomy();
		add_action( 'wp_enqueue_media', array( $this, 'enqueue_admin_scripts' ) );
		add_filter( 'attachment_fields_to_edit', array( $this, 'attachment_fields_to_edit' ), 10, 2 );
		add_filter( 'ajax_query_attachments_args', array( $this, 'filter_attachments_in_admin' ), 10, 1 );
		add_action( 'print_media_templates', array( $this, 'print_media_templates' ) );

	}

	public function register_attachment_taxonomy() {

		$labels = array(
			'name'              => 'Attachment Tags',
			'singular_name'     => 'Attachment Tag',
			'search_items'      => 'Search Attachment Tag',
			'all_items'         => 'All Attachment Tags',
			'parent_item'       => 'Parent Attachment Tag',
			'parent_item_colon' => 'Parent Attachment Tag:',
			'edit_item'         => 'Edit Attachment Tag',
			'update_item'       => 'Update Attachment Tag',
			'add_new_item'      => 'Add New Attachment Tag',
			'new_item_name'     => 'New Attachment Tag Name',
			'menu_name'         => 'Attachment Tag',
		);

		$args = array(
			'labels'             => $labels,
			'hierarchical'       => false,
			'query_var'          => true,
			'rewrite'            => false,
			'show_admin_column'  => true,
			'publicly_queryable' => false
		);

		register_taxonomy( self::SLUG, 'attachment', $args );
	}

	public function enqueue_admin_scripts( $hook ) {

		wp_enqueue_script( 'suggest' );
		wp_enqueue_script( self::SLUG . '_admin_script', PMC_GALLERY_PLUGIN_URL . 'js/attachment-taxonomy.js' );

	}

	public function attachment_fields_to_edit( $form_fields, $post ) {


		$mime_array = array(
			'image/jpeg',
			'image/png',
			'image/gif',
			'image/jpg'
		);

		if ( !in_array( $post->post_mime_type, $mime_array ) ) {
			return $form_fields;
		}

		$form_fields["pmc_gallery_attachment_taxonomy"] = array(
			"label" => '',
			"input" => "html",
			"html"  => '<script type="text/javascript">pmc_gallery_attachment.set_suggest("attachments-' . $post->ID . '-' . self::SLUG . '")</script>',
			"helps" => '',
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
			Search Media By Tags:&nbsp;<input type="checkbox" value="1" id="pmc-gallery-media-search-input" name="pmc_attachment_tags">
		</span>
	<?php
	}

	public function filter_attachments_in_admin( $query = array() ) {

		global $pagenow;

		if ( 'admin-ajax.php' == $pagenow
			 && isset( $_GET['action'] )
			 && 'query-attachments' == $_GET['action']
		) {
			return $query;
		}

		if ( empty( $_COOKIE['pmc_gallery_mediainput'] ) ) {
			return $query;
		}

		$query['tax_query'] = array(
			array(
				'taxonomy' => self::SLUG,
				'terms'    => array( sanitize_text_field( $query['s'] ) ),
				'field'    => 'name',
			)
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
		if ( 'upload' == $screen->id ) {
			$taxonomy   = self::SLUG;
			$attach_tag = get_taxonomy( $taxonomy );

			$term = isset( $_GET[self::SLUG] ) ? intval( $_GET[self::SLUG] ) : '';

			wp_dropdown_categories(
				array(
					'show_option_all' => __( "Show All {$attach_tag->label}" ),
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

	public function pre_get_posts( $query ) {

		if ( !is_admin() ) {
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

		if ( 'upload' != $screen->id ) {
			return $query;
		}

		if ( empty( $_GET[self::SLUG] ) ) {
			return $query;
		}

		$tax_query[] = array(
			'taxonomy' => self::SLUG,
			'field'    => 'id',
			'terms'    => array( intval( $_GET[self::SLUG] ) )
		);

		$tax_query['relation'] = 'OR';
		$query->set( 'tax_query', $tax_query );

		return $query;

	}

}

PMC_Gallery_Attachment_Taxonomy::get_instance();

// EOF
