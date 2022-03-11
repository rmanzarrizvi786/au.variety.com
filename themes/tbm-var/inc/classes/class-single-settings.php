<?php
/*
 * Plugin Name: Variety Single Settings
 * Plugin URI: http://pmc.com/
 *
 * Description: Adding variety Single Settings.
 * 1. Post Meta to Take SubHeadding
 * 2. Post Meta to accept Super Image
 * 3. General Settings option to stop Super Image throughout site
 * 4. Post Meta to add Video
 *
 * Usage
 * get_post_meta( $post->ID, '_variety-use-superimage', true )
 * get_post_meta( $post->ID, '_variety-sub-heading', true )
 *
 *
 * Version: 0.0.0.1
 * Author: Vicky Biswas
 * Author URI: http://pmc.com/
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Single_Settings
 *
 * @since 2017.1.0
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Single_Settings {

	use Singleton;

	/**
	 * Class constructor.
	 *
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup hooks.
	 *
	 * @codeCoverageIgnore
	 */
	public function _setup_hooks() {
		if ( is_admin() ) {
			add_action( 'load-post.php', array( $this, 'meta_box_setup' ) );
			add_action( 'load-post-new.php', array( $this, 'meta_box_setup' ) );
			add_action( 'fm_post_post', [ $this, 'add_takeaway_fields' ] );
			add_action( 'fm_post_post', [ $this, 'fields_article_features' ] );
			add_action( 'fm_post_pmc-gallery', [ $this, 'fields_article_features' ] );
		}
	}

	/**
	 * Add actions to add meta boxes
	 */
	function meta_box_setup() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
	}

	/**
	 * Add meta box to posts
	 */
	function add_meta_boxes() {
		add_meta_box(
			'variety-sub-heading', 'Sub Heading', array( $this, 'meta_box_sub_heading' ), 'post', 'normal', 'core'
		);
		add_meta_box(
			'variety-sub-heading', 'Sub Heading', array( $this, 'meta_box_sub_heading' ), 'pmc-content', 'normal', 'core'
		);
		add_meta_box(
			'variety-sub-heading', 'Sub Heading', array( $this, 'meta_box_sub_heading' ), 'pmc-gallery', 'normal', 'core'
		);
		add_meta_box(
			'variety-sub-heading', 'Sub Heading', array( $this, 'meta_box_sub_heading' ), 'exclusive', 'normal', 'core'
		);
	}

	/**
	 * Takeaway fields.
	 *
	 * @return \Fieldmanager_Context_Post
	 *
	 * @throws \FM_Developer_Exception
	 */
	public function add_takeaway_fields() {

		$fm = new \Fieldmanager_Group(
			[
				'name'     => 'variety_takeaways',
				'children' => [
					'takeaway_list' => new \Fieldmanager_Group(
						[
							'label'          => esc_html__( 'Takeaway', 'pmc-variety' ),
							'limit'          => 10,
							'add_more_label' => esc_html__( 'Add Another Takeaway', 'pmc-variety' ),
							'sortable'       => true,
							'collapsible'    => true,
							'children'       => [
								'takeaway_text' => new \Fieldmanager_TextArea( esc_html__( 'Takeaway Copy', 'pmc-variety' ) ),
							],
						]
					),
				],
			]
		);

		return $fm->add_meta_box( esc_html__( 'Key Takeaways', 'pmc-variety' ), [ 'post' ] );

	}

	/**
	 * Converts the article URL to needed data and saves it
	 *
	 * Keeping all fully seperate including noonce so that its easy to remove if needed
	 *
	 * @param int $post_id
	 * @return void
	 */
	function save_post( $post_id ) {
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		     || ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		     || ! (
				(isset( $_POST['variety-video-url_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['variety-video-url_nonce'] ) ), basename( __FILE__ ) ) ) // WPCS: Input var okay.
				|| (isset( $_POST['variety-use-superimage_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['variety-use-superimage_nonce'] ) ), basename( __FILE__ ) ) ) // WPCS: Input var okay.
				|| (isset( $_POST['variety-sub-heading_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['variety-sub-heading_nonce'] ) ), basename( __FILE__ ) ) ) // WPCS: Input var okay.
			)
		     || ! current_user_can( 'edit_post', $post_id )
		) {
			return;
		}

		//Sub Heading
		$new_sub_heading = '';

		if ( isset( $_POST['variety-sub-heading'] ) ) { // WPCS: Input var okay.
			$new_sub_heading = sanitize_text_field( wp_unslash( $_POST['variety-sub-heading'] ) ); // WPCS: Input var okay.
		}

		//clear field by sending '' to avoid unnecessary data
		if ( '' === $new_sub_heading ) {
			delete_post_meta( $post_id, '_variety-sub-heading' );
		} elseif ( get_post_meta( $post_id, '_variety-sub-heading', true ) !== $new_sub_heading ) {
			update_post_meta( $post_id, '_variety-sub-heading', $new_sub_heading );
		}

	}

	/**
	 * Meta box for Sub Heading
	 *
	 * @param object $post
	 */
	function meta_box_sub_heading( $post ) {
		wp_nonce_field( basename( __FILE__ ), 'variety-sub-heading_nonce' );

		// We store the ID for easier frontend retrieval, but we should show the URL
		// because normal authors don't know what a post ID is nor what to do
		// with it.
		$linked_data = get_post_meta( $post->ID, '_variety-sub-heading', true );

		if ( $linked_data ) {
			$linked_value = esc_html( $linked_data );
		} else {
			$linked_value = '';
		}

		/**
		 * @since 2017-09-01 Milind More CDWE-499
		 */
		echo \PMC::render_template( CHILD_THEME_PATH . '/template-parts/admin/single-settings-metabox.php',
			array(
				'linked_value' => $linked_value,
			)
		);

	}

	/**
	 * @codeCoverageIgnore
	 * @throws \FM_Developer_Exception
	 */
	public function fields_article_features() {

		$fm = new \Fieldmanager_Group(
			[
				'name'           => 'features',
				'tabbed'         => 'vertical',
				'serialize_data' => false,
				'add_to_prefix'  => false,
				'children'       => [

					'byline_tab'     => new \Fieldmanager_Group(
						[
							'label'          => __( 'Byline', 'pmc-variety' ),
							'serialize_data' => false,
							'add_to_prefix'  => false,
							'children'       =>
								[
									'authors'      => new \Fieldmanager_Autocomplete(
										[
											'label'      => __( 'Authors', 'pmc-variety' ),
											'limit'      => 0,
											'sortable'   => true,
											'extra_elements' => 0,
											'minimum_count' => 1,
											'add_more_label' => __( 'Add author', 'pmc-variety' ),
											'datasource' => new \Variety_Datasource_CAP(
												[
													'set_default' => true,
												]
											),
											'one_label_per_item' => false,
										]
									),
									'contributors' => new \Fieldmanager_Autocomplete(
										[
											'label'      => __( 'Contributors', 'pmc-variety' ),
											'limit'      => 0,
											'sortable'   => true,
											'extra_elements' => 0,
											'add_more_label' => __( 'Add contributor', 'pmc-variety' ),
											'datasource' => new \Variety_Datasource_CAP,
											'one_label_per_item' => false,
										]
									),
									'styled_by'    => new \Fieldmanager_Autocomplete(
										[
											'label'      => __( 'Stylists', 'pmc-variety' ),
											'limit'      => 0,
											'sortable'   => true,
											'extra_elements' => 0,
											'add_more_label' => __( 'Add stylist', 'pmc-variety' ),
											'datasource' => new \Variety_Datasource_CAP,
											'one_label_per_item' => false,
										]
									),
								],
						]
					),
					'photos_tagline' => new \Fieldmanager_Group(
						[
							'label'          => __( 'Photos Tagline', 'pmc-variety' ),
							'serialize_data' => false,
							'add_to_prefix'  => false,
							'children'       =>
								[
									'_variety_photos_tagline' => new \Fieldmanager_TextField( __( 'Photos Tagline', 'pmc-variety' ) ),
								],
						]
					),
				],
			]
		);

		$fm->add_meta_box( __( 'Features', 'pmc-variety' ), [ 'post', 'pmc-gallery' ] );
	}
}
