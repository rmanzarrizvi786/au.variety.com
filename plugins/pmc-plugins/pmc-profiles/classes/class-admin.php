<?php
namespace PMC\PMC_Profiles;

use \PMC\Global_Functions\Traits\Singleton;


/**
 * The admin-specific functionality of the plugin.
 */
class Admin {

	use Singleton;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $profiles_post_type The ID of this plugin.
	 */
	private $profiles_post_type_data;

	/**
	 * The Post type for the landing page.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $profiles_post_type The Post type for the landing page.
	 */
	private $landing_post_type_data;


	/**
	 * The Capabilities for the settings page.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $capabilities.  Default 'manage_options'
	 */
	private $profiles_sponsor_settings_capabilities;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	protected function __construct() {

		add_action( 'init', [ $this, 'init' ] );

	}

	/**
	 * Add init function
	 *
	 * @codeCoverageIgnore Ignoring because testing will throw submenu already registered error in pipeline.
	 */
	public function init() {

		if ( ! function_exists( 'fm_register_submenu_page' ) ) {
			return;
		}

		$post_args = Post_Type::get_instance()->post_args;

		$this->profiles_post_type_data = $post_args['profile_post_type'];
		$this->landing_post_type_data  = $post_args['landing_page_post_type'];

		$this->category_taxonomy                      = apply_filters( 'pmc_profiles_category_taxonomy', 'primary-topic' );
		$this->source_taxonomy                        = apply_filters( 'pmc_profiles_source_taxonomy', 'primary-topic' );
		$this->profiles_sponsor_settings_capabilities = apply_filters( 'pmc_profiles_sponsor_settings_capabilities', 'manage_options' );

		add_action( 'fm_post_' . $this->landing_post_type_data['slug'], [ $this, 'add_landing_page_fields' ] );

		add_action( 'fm_post_' . $this->profiles_post_type_data['slug'], [ $this, 'add_profile_meta_fields' ] );

		add_action( 'fm_term_' . $this->category_taxonomy, [ $this, 'add_category_taxonomy_fields' ] );

		fm_register_submenu_page( 'profiles_sponsor_settings', 'edit.php?post_type=' . $this->profiles_post_type_data['slug'], $this->profiles_post_type_data['plural'] . ' Settings', $this->profiles_post_type_data['plural'] . ' Settings', $this->profiles_sponsor_settings_capabilities );

		add_action( 'fm_submenu_profiles_sponsor_settings', [ $this, 'sponsor_submenu_fields' ] );

	}

	/**
	 * Add Sponsor submenu page fields.
	 */
	public function sponsor_submenu_fields() {

		$sponsor_fields = [
			'sponsor_header_title' => new \Fieldmanager_Textfield( 'Sponsor Header Title' ),
			'sponsor_logo'         => new \Fieldmanager_Media(
				[
					'label'        => 'Sponsor Logo',
					'button_label' => 'Upload/Select Sponsor Logo',
				]
			),
			'sponsor_url'          => new \Fieldmanager_Link( esc_html__( 'Sponsor URL', 'pmc-profiles' ) ),
			'back_to_index_text'   => new \Fieldmanager_TextField( esc_html__( 'Back to Index Page Text', 'pmc-profiles' ) ),
			'back_to_index_url'    => new \Fieldmanager_Link( esc_html__( 'Back to Index Page URL', 'pmc-profiles' ) ),
		];

		$sponsor_fields = apply_filters( 'pmc_profiles_sponsor_fields', $sponsor_fields );

		$fm = new \Fieldmanager_Group(
			[
				'name'     => 'profiles_sponsor_settings',
				'children' => $sponsor_fields,
			]
		);

		$fm->activate_submenu_page();
		return $fm;
	}

	/**
	 * Add landing page fields.
	 *
	 * @return void
	 */
	public function add_landing_page_fields() {

		$fm = new \Fieldmanager_Group(
			[
				'name'     => 'landing-page-details',
				'children' => [
					'explore_list_button_text' => new \Fieldmanager_TextField( esc_html__( 'Explore the list Button Text', 'pmc-profiles' ) ),
					'explore_list_button_url'  => new \Fieldmanager_Link( esc_html__( 'Explore the list Button URL', 'pmc-profiles' ) ),
					'source_taxonomy'          => new \Fieldmanager_Select(
						[
							'label'                     => esc_html__( 'Year', 'pmc-profiles' ),
							'first_empty'               => true,
							'remove_default_meta_boxes' => true,
							'datasource'                => new \Fieldmanager_Datasource_Term(
								[
									'taxonomy' => $this->source_taxonomy,
								]
							),
						]
					),
					'category_list'            => new \Fieldmanager_Group(
						[
							'label'          => esc_html__( 'Category list', 'pmc-profiles' ),
							'limit'          => 6,
							'add_more_label' => esc_html__( 'Add Another Category', 'pmc-profiles' ),
							'children'       => [
								'category' => new \Fieldmanager_Autocomplete(
									[
										'label'      => esc_html__( 'Category', 'pmc-profiles' ),
										'datasource' => new \Fieldmanager_Datasource_Term(
											[
												'taxonomy' => [
													$this->category_taxonomy,
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

		return $fm->add_meta_box( esc_html__( 'Landing page information', 'pmc-profiles' ), 'profile-landing-page' );

	}

	/**
	 * Add Icon field to category taxonomy.
	 *
	 * @param string $name
	 */
	public function add_category_taxonomy_fields( $name ) {

		$fm = new \Fieldmanager_Media(
			[
				'name'         => 'category_icon',
				'button_label' => 'Upload/Select Category Icon',
			]
		);
		$fm->add_term_meta_box( 'Select Category Icon', $name );

		return $fm;
	}

	/**
	 * Get category Taxonomy.
	 *
	 * @return string
	 */
	public function get_category_taxonomy() {
		return $this->category_taxonomy;
	}

	/**
	 * Get source taxonomy.
	 *
	 * @return string
	 */
	public function get_source_taxonomy() {
		return $this->source_taxonomy;
	}

	/**
	 * Add fields for first and last name.
	 *
	 * @return void
	 */
	public function add_profile_meta_fields() {

		$profile_fields = [
			'first_name' => new \Fieldmanager_TextField(
				[
					'label' => 'First Name',
					'name'  => 'first_name',
				]
			),
			'last_name' => new \Fieldmanager_TextField(
				[
					'label' => 'Last Name',
					'name'  => 'last_name',
				]
			)
		];
		$filtered_profile_fields = apply_filters( 'pmc_profiles_meta_fields', $profile_fields );

		$fm = new \Fieldmanager_Group(
			[
				'label'    => __( sprintf( "%s Fields", $this->profiles_post_type_data['plural'] ), 'pmc-profiles' ),
				'name'     => __( $this->profiles_post_type_data['slug'] . '_fields', 'pmc-profiles' ),
				'children' => $filtered_profile_fields,
			]
		);

		$fm->add_meta_box( esc_html__( sprintf( "%s Details", $this->profiles_post_type_data['plural'] ), 'pmc-profiles' ) , $this->profiles_post_type_data['slug'] );

	}

}
