<?php
namespace PMC\Core\Inc\Fieldmanager;

use \PMC\Core\Inc\Admin;

class Fields {

	use \PMC\Global_Functions\Traits\Singleton;

	protected function __construct() {

		if ( ! is_admin() ) {
			// Hooks on frontend go here.
			return;
		}

		if ( function_exists( 'fm_register_submenu_page' ) ) {
			fm_register_submenu_page( 'global_curation', 'curation', __( 'Global Curation', 'pmc-core' ), __( 'Global Curation', 'pmc-core' ), 'edit_posts' );
		}

		add_action( 'pmc_core_global_curation_modules', [ $this, 'default_modules' ] );
		add_action( 'add_meta_boxes', [ $this, 'remove_meta_boxes' ], 20 );
		add_action( 'wp_ajax_pmc_fm_get_subcats', [ $this, 'get_subcategories' ] );
		add_action( 'fm_post_post', [ $this, 'fields_relationships' ] );
		add_action( 'fm_post_pmc-gallery', [ $this, 'fields_relationships' ] );
		add_action( 'fm_post_pmc_list', [ $this, 'fields_relationships' ] );
		add_action( 'fm_post_pmc-list-slideshow', [ $this, 'fields_relationships' ] );
		add_action( 'fm_post_guest-author', [ $this, 'guest_author_attributes' ] );
		add_action( 'fm_post_tout', [ $this, 'fields_tout_fields' ] );
		add_action( 'fm_submenu_global_curation', [ $this, 'fm_global_curation' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function default_modules( $modules ) {
		$modules = is_array( $modules ) ? $modules : [];
		$social_profiles = $this->_prepare_social_profiles_module();

		return array_merge( [
			'social_profiles' => [
				'label'    => __( 'Social Profiles', 'pmc-core' ),
				'children' => [
					'social_profiles' => new \Fieldmanager_Group( [
						'children' => $social_profiles,
					] ),
				],
			],
		], $modules );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'pmc_core_admin_fields', get_template_directory_uri() . '/assets/build/js/fields.bundle.js', false, null, true );

		$localize = [
			'nonce' =>  wp_create_nonce( 'pmc-fm-nonce' ),
		];

		wp_localize_script( 'pmc_core_admin_fields', 'pmc_core_admin_fields', $localize );
	}

	/**
	 * Prepares the Social Profiles curation module.
	 * Child theme can use pmc_core_social_profiles_module to add/remove/reorder social profiles.
	 *
	 * @return array
	 */
	private function _prepare_social_profiles_module() {
		$social_profiles = apply_filters( 'pmc_core_social_profiles_module', [
			'facebook'  => __( 'Facebook URL', 'pmc-core' ),
			'twitter'   => __( 'Twitter URL', 'pmc-core' ),
			'instagram' => __( 'Instagram URL', 'pmc-core' ),
			'pinterest' => __( 'Pinterest URL', 'pmc-core' ),
			'youtube'   => __( 'Youtube URL', 'pmc-core' ),
		] );
		$return = [];

		if ( is_array( $social_profiles ) ) {
			foreach ( $social_profiles as $key => $value ) {

				if ( ! empty( $key ) && ! empty( $value ) ) {
					$return[ sanitize_key( $key ) ] = new \Fieldmanager_Link( sanitize_text_field( $value ) );
				}
			}
		}

		return $return;
	}

	public function fields_relationships() {
		$post_type = 'post';

		if ( ! empty( $GLOBALS['post_type'] ) ) {
			$post_type = $GLOBALS['post_type'];
		} elseif ( ! empty( $_POST['post_type'] ) ) { // WPCS: CSRF ok.
			$post_type = sanitize_text_field( $_POST['post_type'] );
		} elseif ( ! empty( $_GET['post_type'] ) ) {
			$post_type = sanitize_text_field( $_GET['post_type'] );
		} elseif ( ! empty( $_GET['post'] ) ) {
			$post_type = get_post_type( intval( $_GET['post'] ) );
		} elseif ( ! empty( $_POST['fm_subcontext'] ) ) { // WPCS: CSRF ok.
			$post_type = sanitize_text_field( $_POST['fm_subcontext'] );
		}

		$tabs = [];
		$tabs['category'] = $this->category_tab_fields();

		// Specify all taxonomies we're listing in order to control the order
		$taxonomies = apply_filters( 'pmc_core_relationship_taxonomies', [ 'post_tag' ] );

		if ( is_array( $taxonomies ) ) {
			foreach ( $taxonomies as $tax ) {

				$tax_obj = get_taxonomy( $tax );

				if ( empty( $tax_obj->object_type ) ) {
					continue;
				}

				if ( ! in_array( $post_type, $tax_obj->object_type, true ) ) {
					continue;
				}

				$tabs[ $tax ] = new \Fieldmanager_Group( [
					'label'    => sanitize_text_field( $tax_obj->labels->name ),
					'children' => [
						'terms' => new \Fieldmanager_Select( [
							'remove_default_meta_boxes' => true,
							'label'                     => sanitize_text_field( $tax_obj->labels->name ),
							'limit'                     => 0,
							'sortable'                  => true,
							'one_label_per_item'        => false,
							'add_more_label'            => sprintf( __( 'Add %s', 'pmc-core' ), sanitize_text_field( $tax_obj->labels->singular_name ) ),
							'datasource'                => new \Fieldmanager_Datasource_Term( [
								'taxonomy'              => [ $tax ],
								'only_save_to_taxonomy' => true,
							] ),
						] ),
					],
				] );
			}
		}

		/**
		 * Filter the relationship tabs prior to output.
		 *
		 * @param array $tabs The list of relationship tabs. Note that since this is
		 *                    tabbed, there's an extra group layer.
		 * @param string $post_type The current post type.
		 */
		$tabs = apply_filters( 'pmc_core_fm_relationships_tabs', $tabs, $post_type );

		if ( ! empty( $tabs ) ) {
			$fm = new \Fieldmanager_Group( [
				'name'           => 'relationships',
				'tabbed'         => 'vertical',
				'serialize_data' => false,
				'add_to_prefix'  => false,
				'children'       => $tabs,
			] );

			$fm->add_meta_box( __( 'Relationships', 'pmc-core' ), $post_type );
		}
	}

	/**
	 * Guest Author (CAP) Fieldmanager fields.
	 */
	public function guest_author_attributes() {
		$fm = new \Fieldmanager_Group( [
			'name' => 'attributes',
			'serialize_data' => false,
			'add_to_prefix' => false,
			'children' => [
				'role' => new \Fieldmanager_Select( [
					'label' => __( 'Role', 'pmc-core' ),
					'default_value' => 'contributor',
					'options' => [
						'author' => __( 'Author', 'pmc-core' ),
						'contributor' => __( 'Contributor', 'pmc-core' ),
					],
				] ),
				'occupation' => new \Fieldmanager_TextField( __( 'Occupation', 'pmc-core' ) ),
			],
		] );

		$fm->add_meta_box( __( 'Attributes', 'pmc-core' ), [ 'guest-author' ], 'side' );
	}

	/**
	 * Remove CAP meta box for post types where we replaced it.
	 */
	public function remove_meta_boxes() {
		global $coauthors_plus;
		if ( in_array( get_post_type(), [ 'pmc-gallery' ], true ) ) {
			remove_meta_box( $coauthors_plus->coauthors_meta_box_name, get_post_type(), apply_filters( 'coauthors_meta_box_context', 'normal' ) );
			remove_meta_box( 'postexcerpt', get_post_type(), 'normal' );
		}
	}

	/**
	 * `tout_fields` Fieldmanager fields.
	 */
	function fields_tout_fields() {
		$fm = new \Fieldmanager_Link( [
			'name' => 'tout_link',
			'attributes' => [ 'style' => 'width:100%;max-width:600px' ],
		] );
		$fm->add_meta_box( __( 'Tout Link', 'pmc-core' ), [ 'tout' ] );

		$fm = new \Fieldmanager_Group( [
			'name' => 'relationships',
			'serialize_data' => false,
			'add_to_prefix' => false,
			'children' => [
				'category' => new \Fieldmanager_Select( [
					'label' => __( 'Category', 'pmc-core' ),
					'first_empty' => true,
					'remove_default_meta_boxes' => true,
					'datasource' => new \Fieldmanager_Datasource_Term( [
						'taxonomy' => 'category',
						'only_save_to_taxonomy' => true,
					] ),
				] ),
			],
		] );

		$fm->add_meta_box( __( 'Relationships', 'pmc-core' ), [ 'tout' ] );
	}

	/**
	 * `global_curation` Fieldmanager fields.
	 */
	public function fm_global_curation() {
		$modules = apply_filters( 'pmc_core_global_curation_modules', [] );
		$children = [];

		foreach ( $modules as $key => $module ) {
			$children[ 'tab_' . $key ] = new \Fieldmanager_Group( $module );
		}

		$fm = new \Fieldmanager_Group( [
			'name'     => 'global_curation',
			'tabbed'   => 'vertical',
			'children' => $children,
		] );

		$fm->activate_submenu_page();
	}

	/**
	 * Create a category and subcategory group which allows one of each.
	 * Borrowed from another PMC project.
	 */
	public function category_tab_fields() {
		return new \Fieldmanager_Group( [
			'label'          => __( 'Category', 'pmc_core' ),
			'serialize_data' => false,
			'add_to_prefix'  => false,
			'children' => [
				'categories'     => new \Fieldmanager_Select( [
					'label'              => __( 'Category', 'pmc_core' ),
					'add_more_label'     => __( 'Add category', 'pmc_core' ),
					'one_label_per_item' => true,
					'first_empty'        => true,
					'limit'              => 1,
					'sortable'           => false,
					'remove_default_meta_boxes' => true,
					'datasource' => new \Fieldmanager_Datasource_Term( [
						'taxonomy'               => 'category',
						'taxonomy_save_to_terms' => true,
						'use_ajax'               => false,
						'taxonomy_args'          => [ 'parent' => 0, 'hide_empty' => false ],
					] ),
				] ),
				'subcategories' => new \Fieldmanager_Select( [
					'label'              => __( 'Sub Category', 'pmc_core' ),
					'add_more_label'     => __( 'Add Sub category', 'pmc_core' ),
					'one_label_per_item' => true,
					'first_empty'        => true,
					'limit'              => 1,
					'sortable'           => false,
					'remove_default_meta_boxes' => true,
					'datasource' => new \Fieldmanager_Datasource_Term( [
						'taxonomy'               => 'category',
						'taxonomy_save_to_terms' => true,
						'taxonomy_args'          => [ 'parent' => Admin::get_instance()->get_current_parent_category(), 'hide_empty' => false ],
					] ),
				] ),
			],
		] );
	}

	public function get_subcategories() {
		if ( ! current_user_can( 'edit_posts' ) || empty( $_POST['pmc_fm_nonce'] ) ) {
			wp_send_json_error();
		}

		$nonce = sanitize_text_field( $_POST['pmc_fm_nonce'] );

		if ( ! wp_verify_nonce( $nonce, 'pmc-fm-nonce' ) ) {
			wp_send_json_error();
		}

		if ( ! empty( $_POST['cat_parent_id'] ) ) {
			$parent_id = intval( $_POST['cat_parent_id'] );
		} else {
			wp_send_json_error();
		}

		$terms = get_terms( 'category', [ 'parent' => $parent_id, 'hide_empty' => false, 'orderby' => 'name' ] );
		$term_arr = [];

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) && is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$term_arr[ $term->term_id ] = $term->name;
			}
		}
		asort( $term_arr, 4 );

		wp_send_json_success( $term_arr );
	}

}

//EOF
