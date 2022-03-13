<?php
/**
 * Custom post type for Featured Program.
 *
 * @package pmc-featured-program
 */

namespace PMC\Featured_Program;

use \PMC\Global_Functions\Traits\Singleton;
use \Fieldmanager_Group;

class Post_Type_Featured_Program {

	use Singleton;

	/**
	 * Post_Type_Featured_Program constructor.
	 *
	 * @codeCoverageIgnore Ignored in pmc-featured-program
	 */
	protected function __construct() {

		$this->name = Config::get_instance()->post_type();

		// Hook to add custom metaboxes for featured programs.
		add_action( 'init', [ $this, 'create_post_type' ] );
		add_action( 'wp_ajax_pmc_get_subcategories', [ $this, 'get_subcategories' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'pmc_core_field_overrides', [ $this, 'field_overrides' ], 10, 2 );
		add_action( 'fm_post_' . $this->name, [ $this, 'register_custom_fields' ] );
	}

	/**
	 * Creates the post type.
	 *
	 * @codeCoverageIgnore Ignored in pmc-featured-program
	 */
	public function create_post_type() {

		$slug = Config::get_instance()->post_slug();

		$args = apply_filters(
			'pmc_fp_post_args',
			[
				'labels'       => [
					'name'          => _x( 'Featured Programs', 'pmc-featured-program', 'pmc-featured-program' ),
					'singular_name' => __( 'Featured Program', 'pmc-featured-program' ),
					'add_new'       => esc_html_x( 'Add New Featured Program', 'pmc-featured-program', 'pmc-featured-program' ),
					'add_new_item'  => esc_html_x( 'Add New Featured Program', 'pmc-featured-program', 'pmc-featured-program' ),
					'edit'          => esc_html__( 'Edit Featured Program', 'pmc-featured-program' ),
					'edit_item'     => esc_html__( 'Edit Featured Program', 'pmc-featured-program' ),
					'new_item'      => esc_html__( 'New Featured Program', 'pmc-featured-program' ),
					'view'          => esc_html__( 'View Featured Program', 'pmc-featured-program' ),
					'view_item'     => esc_html__( 'View Featured Program', 'pmc-featured-program' ),
					'search_items'  => esc_html__( 'Search Featured Program', 'pmc-featured-program' ),
				],
				'supports'     => [ 'title', 'editor', 'thumbnail' ],
				'public'       => true,
				'has_archive'  => $slug,
				'rewrite'      => [ 'slug' => $slug ],
				'show_ui'      => true,
				'show_in_menu' => true,
				'taxonomies'   => [ Config::get_instance()->category_group(), Config::get_instance()->tag_group() ],
			]
		);

		register_post_type(
			$this->name,
			$args
		);

	}

	/**
	 * @codeCoverageIgnore Ignored in pmc-featured-program
	 */
	public function enqueue_scripts() {

		$current_screen = get_current_screen();

		if ( ( ! empty( $current_screen->post_type ) ) && $this->name === $current_screen->post_type ) {

			wp_enqueue_script( 'fp_admin_fields', PMC_FP_URL . 'assets/js/fields.js', [ 'jquery' ], filemtime( PMC_FP_ROOT . 'assets/js/fields.js' ), true );

			$localize = [
				'nonce' => wp_create_nonce( 'pmc-fp-nonce' ),
				'i10n'  => [
					'loading' => esc_html__( 'Loading...', 'pmc-featured-program' ),
				],
			];

			wp_localize_script( 'fp_admin_fields', 'pmc_fp_admin_fields', $localize );


		}

	}

	/**
	 * Get subcategories.
	 *
	 * @codeCoverageIgnore Ignored in pmc-featured-program
	 */
	public function get_subcategories() {

		$pmc_nonce = \PMC::filter_input( INPUT_POST, 'pmc_nonce', FILTER_SANITIZE_STRING );

		if ( ! current_user_can( 'edit_posts' ) || null === $pmc_nonce ) {
			wp_send_json_error( 'A' );
		}

		$nonce = sanitize_text_field( $pmc_nonce );

		if ( ! wp_verify_nonce( $nonce, 'pmc-fp-nonce' ) ) {
			wp_send_json_error( 'B' );
		}

		$cat_parent_id = \PMC::filter_input( INPUT_POST, 'cat_parent_id', FILTER_SANITIZE_STRING );

		if ( null !== $cat_parent_id ) {
			$parent_id = intval( $cat_parent_id );
		} else {
			wp_send_json_error( 'C' );
		}

		$terms    = get_terms(
			[
				'taxonomy'   => Config::get_instance()->category_group(),
				'parent'     => $parent_id,
				'hide_empty' => false,
				'orderby'    => 'name',
			]
		);
		$term_arr = [];

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) && is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$term_arr[ $term->term_id ] = $term->name;
			}
		}
		asort( $term_arr, 4 );

		wp_send_json_success( $term_arr );
	}

	/**
	 * Post Meta fields for featured programs.
	 *
	 * @codeCoverageIgnore Ignored in pmc-featured-program
	 */
	public function register_custom_fields() {

		$fm = new Fieldmanager_Group(
			[
				'name'           => 'pmc-featured-program-group',
				'serialize_data' => false,
				'add_to_prefix'  => false,
				'tabbed'         => 'vertical',
				'children'       => [

					'featured_program_tag_group'          => new \Fieldmanager_Group(
						[
							'label'          => __( 'Tag Groups', 'pmc-featured-program' ),
							'limit'          => 1,
							'serialize_data' => false,
							'add_to_prefix'  => false,
							'children'       => [
								Config::get_instance()->prefix() . '_tag_groups' => new \Fieldmanager_Autocomplete(
									[
										'label'          => __( 'Search and Add Tag Groups', 'pmc-featured-program' ),
										'limit'          => 0,
										'sortable'       => true,
										'one_label_per_item' => false,
										'extra_elements' => 0,
										'minimum_count'  => 1,
										'add_more_label' => __( 'Add Tag Group', 'pmc-featured-program' ),
										'remove_default_meta_boxes' => true,
										'datasource'     => new \Fieldmanager_Datasource_Term(
											[
												'taxonomy' => Config::get_instance()->tag_group(),
												'only_save_to_taxonomy' => true,
											]
										),
									]
								),
							],
						]
					),
					'featured_program_description_group'  => new \Fieldmanager_Group(
						[
							'label'          => __( 'Description', 'pmc-featured-program' ),
							'serialize_data' => false,
							'add_to_prefix'  => false,
							'children'       => [
								Config::get_instance()->prefix() . '_fp_description' => new \Fieldmanager_TextField( __( 'Program Description', 'pmc-featured-program' ) ),
							],
						]
					),
					'featured_program_category_group'     => new \Fieldmanager_Group(
						[
							'label'          => __( 'Category', 'pmc-featured-program' ),
							'serialize_data' => false,
							'add_to_prefix'  => false,
							'children'       => [
								'categories'    => new \Fieldmanager_Select(
									[
										'label'          => __( 'Category', 'pmc-featured-program' ),
										'add_more_label' => __( 'Add category', 'pmc-featured-program' ),
										'one_label_per_item' => true,
										'first_empty'    => true,
										'limit'          => 1,
										'sortable'       => false,
										'remove_default_meta_boxes' => true,
										'datasource'     => new \Fieldmanager_Datasource_Term(
											[
												'taxonomy' => Config::get_instance()->category_group(),
												'taxonomy_save_to_terms' => true,
												'use_ajax' => true,
												'taxonomy_args' => [
													'parent'     => 0,
													'hide_empty' => false,
												],
											]
										),
									]
								),
								'subcategories' => new \Fieldmanager_Select(
									[
										'label'          => __( 'Sub Category', 'pmc-featured-program' ),
										'add_more_label' => __( 'Add Sub category', 'pmc-featured-program' ),
										'one_label_per_item' => true,
										'first_empty'    => true,
										'limit'          => 1,
										'sortable'       => false,
										'remove_default_meta_boxes' => true,
										'datasource'     => new \Fieldmanager_Datasource_Term(
											[
												'taxonomy' => Config::get_instance()->category_group(),
												'taxonomy_save_to_terms' => true,
												'taxonomy_args' => [
													'parent'     => Utils::get_instance()->get_current_parent_category(),
													'hide_empty' => false,
												],
											]
										),
									]
								),
							],
						]
					),
					'featured_program_sponsorship_group'  => new \Fieldmanager_Group(
						[
							'label'          => __( 'Sponsorship', 'pmc-featured-program' ),
							'serialize_data' => false,
							'add_to_prefix'  => false,
							'children'       => [
								Config::get_instance()->prefix() . '_fp_sponsor'   => new \Fieldmanager_TextField(
									__( 'Sponsor', 'pmc-featured-program' ),
									[
										'attributes' => [
											'size' => '100',
										],
									]
								),
								Config::get_instance()->prefix() . '_fp_dfp_value' => new \Fieldmanager_TextField(
									__( 'Sponsored DFP Value', 'pmc-featured-program' ),
									[
										'attributes' => [
											'size' => '100',
										],
									]
								),
							],
						]
					),
					'featured_program_dates_group'        => new \Fieldmanager_Group(
						[
							'label'          => __( 'Date Range', 'pmc-featured-program' ),
							'serialize_data' => false,
							'add_to_prefix'  => false,
							'children'       => [
								Config::get_instance()->prefix() . '_fp_start' => new \Fieldmanager_Datepicker(
									__( 'Start Date', 'pmc-featured-program' ),
									[
										'date_format' => 'd-m-Y',
										'js_opts'     => [
											'dateFormat' => 'dd-mm-yy',
										],
										'attributes'  => [
											'size' => '100',
										],
									]
								),
								Config::get_instance()->prefix() . '_fp_end'   => new \Fieldmanager_Datepicker(
									__( 'End Date', 'pmc-featured-program' ),
									[
										'date_format' => 'd-m-Y',
										'js_opts'     => [
											'dateFormat' => 'dd-mm-yy',
										],
										'attributes'  => [
											'size' => '100',
										],
									]
								),
							],
						]
					),
					'featured_program_images_group'       => new \Fieldmanager_Group(
						[
							'label'          => __( 'Header Image Details', 'pmc-featured-program' ),
							'serialize_data' => false,
							'add_to_prefix'  => false,
							'children'       => [
								Config::get_instance()->prefix() . '_fp_banner_link'       => new \Fieldmanager_TextField(
									__( 'Header Image Link', 'pmc-featured-program' ),
									[
										'attributes' => [
											'size' => '100',
										],
									]
								),
								Config::get_instance()->prefix() . '_fp_banner_image_size' => new \Fieldmanager_Radios(
									__( 'Header Image Size', 'pmc-featured-program' ),
									[
										'options'    => [
											'1000x100' => '1000x100',
											'1024x320' => '1024x320',
										],
										'attributes' => [
											'size' => '100',
										],
									]
								),
								Config::get_instance()->prefix() . '_fp_banner_image'      => new \Fieldmanager_Media(
									__( 'Banner Image', 'pmc-featured-program' ),
									[
										'mime_type'    => 'image',
										'button_label' => 'Upload Image',
									]
								),
							],
						]
					),
					'featured_program_metaimage_group'    => new \Fieldmanager_Group(
						[
							'label'          => __( 'Meta Data', 'pmc-featured-program' ),
							'serialize_data' => false,
							'add_to_prefix'  => false,
							'children'       => [
								Config::get_instance()->prefix() . '_fp_meta_image_id' => new \Fieldmanager_Media(
									__( 'Meta Image', 'pmc-featured-program' ),
									[
										'mime_type'    => 'image',
										'button_label' => 'Upload Image',
									]
								),
							],
						]
					),
					'featured_program_extra_fields_group' => new \Fieldmanager_Group(
						[
							'label'          => __( 'Extra Details', 'pmc-featured-program' ),
							'serialize_data' => false,
							'add_to_prefix'  => false,
							'children'       => [
								Config::get_instance()->prefix() . '_fp_tiles_display'    => new \Fieldmanager_Radios(
									__( 'Tiles Display', 'pmc-featured-program' ),
									[
										'options' => [
											1 => 'Yes',
											0 => 'No',
										],
									]
								),
								Config::get_instance()->prefix() . '_fp_hide_leaderboard' => new \Fieldmanager_Radios(
									__( 'Hide Leaderboard Ad', 'pmc-featured-program' ),
									[
										'options' => [
											1 => 'Yes',
											0 => 'No',
										],
									]
								),
							],
						]
					),
					'featured_program_posts_group'        => new \Fieldmanager_Group(
						[
							'label'          => __( 'Featured Contents', 'pmc-featured-program' ),
							'limit'          => 1,
							'serialize_data' => false,
							'add_to_prefix'  => false,
							'children'       => [
								Config::get_instance()->prefix() . '_featured_program_contents' => new \Fieldmanager_Autocomplete(
									[
										'label'          => __( 'Search and Add Feature Contents', 'pmc-featured-program' ),
										'limit'          => 10,
										'sortable'       => true,
										'one_label_per_item' => false,
										'extra_elements' => 0,
										'minimum_count'  => 1,
										'add_more_label' => __( 'Add Featured Content', 'pmc-featured-program' ),
										'datasource'     => new \Fieldmanager_Datasource_Post(
											[
												'query_args' => [
													'post_type'   => [ 'post', 'pmc_top_video', 'pmc-gallery' ],
													'post_status' => 'publish',
												],
											]
										),
									]
								),
							],
						]
					),
				],
			]
		);

		$fm = apply_filters( 'pmc_fp_post_custom_args', $fm );
		$fm->add_meta_box( __( 'More Fields', 'pmc-featured-program' ), [ $this->name ] );

		return $fm;
	}

}
