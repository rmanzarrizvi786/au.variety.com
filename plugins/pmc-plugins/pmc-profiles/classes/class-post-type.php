<?php
/**
 * Artnews post types.
 *
 * @package pmc-profiles
 */

namespace PMC\PMC_Profiles;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class to register custom post types.
 *
 * @package pmc-profiles
 */
class Post_Type {

	use Singleton;

	/**
	 * The Post type slug for the landing page.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The Post type slug for the profiles.
	 */
	private $profiles_post_type;

	/**
	 * The post arguments array.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    array The post args contains the slugs and names of the different PMC Profiles entities.
	 */
	public $post_args;

	/**
	 * The Post type slug for the landing page.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The Post type slug for the landing page.
	 */
	private $landing_post_type;

	/**
	 * Class constructor.
	 *
	 * Initializes the theme.
	 */
	protected function __construct() {

		$this->_setup_hooks();
	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks() {

		add_action( 'init', [ $this, 'init' ] );

		add_filter( 'pmc_gallery_link_post_types', [ $this, 'filter_allow_pmc_profiles' ] );
		add_filter( 'pmc_field_override_post_types', [ $this, 'filter_field_overrides_post_type' ] );

		add_filter( 'single_template', [ $this, 'template_single' ] );
		add_filter( 'template_include', [ $this, 'template_archive' ] );
	}

	/**
	 * Initialisation by registering custom post types.
	 */
	public function init() {

		$this->add_post_type();
		$this->add_taxonomy();

	}

	/**
	 * Register required post types.
	 *
	 * @return void
	 */
	public function add_post_type() {

		$default_labels = [
			'profile_post_type'      => [
				'slug'     => 'pmc_profiles',
				'singular' => esc_html__( 'Profile', 'pmc-profiles' ),
				'plural'   => esc_html__( 'Profiles', 'pmc-profiles' ),
			],
			'landing_page_post_type' => [
				'slug'     => 'profile-landing-page',
				'singular' => esc_html__( 'Profiles Landing Page', 'pmc-profiles' ),
				'plural'   => esc_html__( 'Profiles Landing Pages', 'pmc-profiles' ),
			],
		];

		$this->post_args = apply_filters( 'pmc_profiles_post_type_arguments', $default_labels );

		$this->profiles_post_type = $this->post_args['profile_post_type']['slug'];
		$this->landing_post_type  = $this->post_args['landing_page_post_type']['slug'];

		// profiles Post type.
		$labels = [
			'name'          => $this->post_args['profile_post_type']['plural'],
			'singular_name' => $this->post_args['profile_post_type']['singular'],
			// translators: Profiles.
			'add_new'       => sprintf( esc_html__( 'Add New %1$s', 'pmc-profiles' ), $this->post_args['profile_post_type']['singular'] ),
			// translators: Profiles.
			'add_new_item'  => sprintf( esc_html__( 'Add New %1$s', 'pmc-profiles' ), $this->post_args['profile_post_type']['singular'] ),
			// translators: Profiles.
			'edit_item'     => sprintf( esc_html__( 'Edit %1$s', 'pmc-profiles' ), $this->post_args['profile_post_type']['singular'] ),
			// translators: Profiles.
			'new_item'      => sprintf( esc_html__( 'New %1$s', 'pmc-profiles' ), $this->post_args['profile_post_type']['singular'] ),
			// translators: Profiles.
			'view_item'     => sprintf( esc_html__( 'View %1$s', 'pmc-profiles' ), $this->post_args['profile_post_type']['singular'] ),
			// translators: Profiles.
			'search_items'  => sprintf( esc_html__( 'Search %1$s', 'pmc-profiles' ), $this->post_args['profile_post_type']['plural'] ),
			'all_items'     => $this->post_args['profile_post_type']['plural'],
		];

		$profiles_args = [
			'labels'      => $labels,
			'public'      => true,
			'has_archive' => true,
			'show_in_rest' => true,
			'supports'    => [ 'title', 'author', 'comments', 'editor', 'thumbnail', 'excerpt', 'zoninator_zones' ],
			'taxonomies'  => [ 'post_tag' ],
		];

		$profiles_args = array_merge( $profiles_args, $this->post_args['profile_post_type']['args'] ?? [] );

		// Landing page post type.
		$labels = [
			'name'          => $this->post_args['landing_page_post_type']['plural'],
			'singular_name' => $this->post_args['landing_page_post_type']['singular'],
			// translators: landing page post type.
			'add_new'       => sprintf( esc_html__( 'Add New %1$s', 'pmc-profiles' ), $this->post_args['landing_page_post_type']['singular'] ),
			// translators: landing page post type.
			'add_new_item'  => sprintf( esc_html__( 'Add New %1$s', 'pmc-profiles' ), $this->post_args['landing_page_post_type']['singular'] ),
			// translators: landing page post type.
			'edit_item'     => sprintf( esc_html__( 'Edit %1$s', 'pmc-profiles' ), $this->post_args['landing_page_post_type']['singular'] ),
			// translators: landing page post type.
			'new_item'      => sprintf( esc_html__( 'New %1$s', 'pmc-profiles' ), $this->post_args['landing_page_post_type']['singular'] ),
			// translators: landing page post type.
			'view_item'     => sprintf( esc_html__( 'View %1$s', 'pmc-profiles' ), $this->post_args['landing_page_post_type']['singular'] ),
			// translators: landing page post type.
			'search_items'  => sprintf( esc_html__( 'Search %1$s', 'pmc-profiles' ), $this->post_args['landing_page_post_type']['plural'] ),
			'all_items'     => $this->post_args['landing_page_post_type']['plural'],
		];

		$landing_page_args = [
			'labels'            => $labels,
			'description'       => 'Landing pages for each year of the PMC profiles',
			'public'            => true,
			'menu_position'     => 5,
			'show_ui'           => true,
			'supports'          => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
			'has_archive'       => true,
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => true,
			'show_in_menu'      => 'edit.php?post_type=' . $this->profiles_post_type,
			'query_var'         => 'pmc-profiles-lp',
			'taxonomies'        => [ 'post_tag' ],
		];

		$landing_page_args = array_merge( $landing_page_args, $this->post_args['landing_page_post_type']['args'] ?? [] );

		register_post_type( $this->profiles_post_type, $profiles_args );

		register_post_type( $this->landing_post_type, $landing_page_args );

	}

	/**
	 * Add profiles taxonomy
	 *
	 * @return void
	 */
	public function add_taxonomy() {

		$default_labels = [
			'primary_taxonomy'    => [
				'slug'     => 'primary-topic',
				'singular' => esc_html__( 'Primary Taxonomy', 'pmc-profiles' ),
				'plural'   => esc_html__( 'Primary Taxonomy', 'pmc-profiles' ),
			],
			'secondary_taxonomy'  => [
				'slug'     => 'secondary-topic',
				'singular' => esc_html__( 'Secondary Taxonomy', 'pmc-profiles' ),
				'plural'   => esc_html__( 'Secondary Taxonomy', 'pmc-profiles' ),
			],
			'tertiary_taxonomy'   => [
				'slug'     => 'tertiary-topic',
				'singular' => esc_html__( 'Tertiary Taxonomy', 'pmc-profiles' ),
				'plural'   => esc_html__( 'Tertiary Taxonomy', 'pmc-profiles' ),
			],
			'quaternary_taxonomy' => [
				'slug'     => 'quaternary-topic',
				'singular' => esc_html__( 'Quaternary Taxonomy', 'pmc-profiles' ),
				'plural'   => esc_html__( 'Quaternary Taxonomy', 'pmc-profiles' ),
			],
		];

		$tax_args = apply_filters( 'pmc_profiles_taxonomy_args', $default_labels );

		$this->primary_taxonomy    = $tax_args['primary_taxonomy']['slug'];
		$this->secondary_taxonomy  = $tax_args['secondary_taxonomy']['slug'];
		$this->tertiary_taxonomy   = $tax_args['tertiary_taxonomy']['slug'];
		$this->quaternary_taxonomy = $tax_args['quaternary_taxonomy']['slug'];

		$primary_taxonomy_arguments = [
			'labels'       => [
				'name'          => $tax_args['primary_taxonomy']['plural'],
				'singular_name' => $tax_args['primary_taxonomy']['singular'],
				// translators: Taxonomy name.
				'add_new_item'  => sprintf( esc_html__( 'Add New %1$s', 'pmc-profiles' ), $tax_args['primary_taxonomy']['singular'] ),
				// translators: Taxonomy name.
				'edit_item'     => sprintf( esc_html__( 'Edit New %1$s', 'pmc-profiles' ), $tax_args['primary_taxonomy']['singular'] ),
				// translators: Taxonomy name.
				'new_item'      => sprintf( esc_html__( 'New %1$s', 'pmc-profiles' ), $tax_args['primary_taxonomy']['singular'] ),
				// translators: Taxonomy name.
				'view_item'     => sprintf( esc_html__( 'View %1$s', 'pmc-profiles' ), $tax_args['primary_taxonomy']['singular'] ),
			],
			'public'       => true,
			'show_ui'      => true,
			'show_in_rest' => true,
			'hierarchical' => true,
			'capabilities' => [
				'manage_terms' => 'manage_options',
				'edit_terms'   => 'manage_options',
				'delete_terms' => 'manage_options',
				'assign_terms' => 'edit_posts',
			],
		];

		$primary_taxonomy_arguments = apply_filters( 'pmc_profiles_primary_taxonomy_arguments', $primary_taxonomy_arguments );

		if ( ! empty( $tax_args['primary_taxonomy']['slug'] ) ) {
			register_taxonomy(
				$tax_args['primary_taxonomy']['slug'],
				[ $this->profiles_post_type ],
				$primary_taxonomy_arguments
			);
		}

		$secondary_taxonomy_arguments = [
			'labels'       => [
				'name'          => $tax_args['secondary_taxonomy']['plural'],
				'singular_name' => $tax_args['secondary_taxonomy']['singular'],
				// translators: Taxonomy name.
				'add_new_item'  => sprintf( esc_html__( 'Add New %1$s', 'pmc-profiles' ), $tax_args['secondary_taxonomy']['singular'] ),
				// translators: Taxonomy name.
				'edit_item'     => sprintf( esc_html__( 'Edit New %1$s', 'pmc-profiles' ), $tax_args['secondary_taxonomy']['singular'] ),
				// translators: Taxonomy name.
				'new_item'      => sprintf( esc_html__( 'New %1$s', 'pmc-profiles' ), $tax_args['secondary_taxonomy']['singular'] ),
				// translators: Taxonomy name.
				'view_item'     => sprintf( esc_html__( 'View %1$s', 'pmc-profiles' ), $tax_args['secondary_taxonomy']['singular'] ),
			],
			'public'       => true,
			'show_ui'      => true,
			'show_in_rest' => true,
			'hierarchical' => true,
			'capabilities' => [
				'manage_terms' => 'manage_options',
				'edit_terms'   => 'manage_options',
				'delete_terms' => 'manage_options',
				'assign_terms' => 'edit_posts',
			],
		];

		$secondary_taxonomy_arguments = apply_filters( 'pmc_profiles_secondary_taxonomy_arguments', $secondary_taxonomy_arguments );

		if ( ! empty( $tax_args['secondary_taxonomy']['slug'] ) ) {
			register_taxonomy(
				$tax_args['secondary_taxonomy']['slug'],
				[ $this->profiles_post_type ],
				$secondary_taxonomy_arguments
			);
		}

		$tertiary_taxonomy_arguments = [
			'labels'       => [
				'name'          => $tax_args['tertiary_taxonomy']['plural'],
				'singular_name' => $tax_args['tertiary_taxonomy']['singular'],
				// translators: Taxonomy name.
				'add_new_item'  => sprintf( esc_html__( 'Add New %1$s', 'pmc-profiles' ), $tax_args['tertiary_taxonomy']['singular'] ),
				// translators: Taxonomy name.
				'edit_item'     => sprintf( esc_html__( 'Edit New %1$s', 'pmc-profiles' ), $tax_args['tertiary_taxonomy']['singular'] ),
				// translators: Taxonomy name.
				'new_item'      => sprintf( esc_html__( 'New %1$s', 'pmc-profiles' ), $tax_args['tertiary_taxonomy']['singular'] ),
				// translators: Taxonomy name.
				'view_item'     => sprintf( esc_html__( 'View %1$s', 'pmc-profiles' ), $tax_args['tertiary_taxonomy']['singular'] ),
			],
			'public'       => true,
			'show_ui'      => true,
			'show_in_rest' => true,
			'hierarchical' => true,
			'capabilities' => [
				'manage_terms' => 'manage_options',
				'edit_terms'   => 'manage_options',
				'delete_terms' => 'manage_options',
				'assign_terms' => 'edit_posts',
			],
		];

		$tertiary_taxonomy_arguments = apply_filters( 'pmc_profiles_tertiary_taxonomy_arguments', $tertiary_taxonomy_arguments );

		if ( ! empty( $tax_args['tertiary_taxonomy']['slug'] ) ) {
			register_taxonomy(
				$tax_args['tertiary_taxonomy']['slug'],
				[ $this->profiles_post_type ],
				$tertiary_taxonomy_arguments
			);
		}

		$quaternary_taxonomy_arguments = [
			'labels'       => [
				'name'          => $tax_args['quaternary_taxonomy']['plural'],
				'singular_name' => $tax_args['quaternary_taxonomy']['singular'],
				// translators: Taxonomy name.
				'add_new_item'  => sprintf( esc_html__( 'Add New %1$s', 'pmc-profiles' ), $tax_args['quaternary_taxonomy']['singular'] ),
				// translators: Taxonomy name.
				'edit_item'     => sprintf( esc_html__( 'Edit New %1$s', 'pmc-profiles' ), $tax_args['quaternary_taxonomy']['singular'] ),
				// translators: Taxonomy name.
				'new_item'      => sprintf( esc_html__( 'New %1$s', 'pmc-profiles' ), $tax_args['quaternary_taxonomy']['singular'] ),
				// translators: Taxonomy name.
				'view_item'     => sprintf( esc_html__( 'View %1$s', 'pmc-profiles' ), $tax_args['quaternary_taxonomy']['singular'] ),
			],
			'public'       => true,
			'show_ui'      => true,
			'show_in_rest' => true,
			'hierarchical' => true,
			'capabilities' => [
				'manage_terms' => 'manage_options',
				'edit_terms'   => 'manage_options',
				'delete_terms' => 'manage_options',
				'assign_terms' => 'edit_posts',
			],
		];

		$quaternary_taxonomy_arguments = apply_filters( 'pmc_profiles_quaternary_taxonomy_arguments', $quaternary_taxonomy_arguments );

		if ( ! empty( $tax_args['quaternary_taxonomy']['slug'] ) ) {
			register_taxonomy(
				$tax_args['quaternary_taxonomy']['slug'],
				[ $this->profiles_post_type ],
				$quaternary_taxonomy_arguments
			);
		}

		$extra_taxonomies = apply_filters( 'pmc_profiles_extra_taxonomy_list', [] );

		if ( ! empty( $extra_taxonomies ) && is_array( $extra_taxonomies ) ) {

			foreach ( $extra_taxonomies as $taxonomy ) {

				if ( empty( $taxonomy['taxonomy'] ) ) {
					continue;
				}

				register_taxonomy(
					$taxonomy['taxonomy'],
					[ $this->profiles_post_type ],
					$taxonomy['arguments'] ?? []
				);
			}
		}

	}

	/**
	 * Allow gallery linking to top200 post type.
	 *
	 * @param array $post_types Post type array.
	 *
	 * @return array
	 */
	public function filter_allow_pmc_profiles( $post_types ) {
		$post_types[] = $this->profiles_post_type;

		return $post_types;
	}

	/**
	 * Allow gallery linking to top200 post type.
	 *
	 * @param array $post_types Post type array.
	 *
	 * @return array
	 */
	public function filter_field_overrides_post_type( $post_types ) {
		$post_types[] = $this->profiles_post_type;
		$post_types[] = $this->landing_post_type;

		return $post_types;
	}

	/**
	 * Get taxonomy slug.
	 *
	 * @param string $type
	 * @return string
	 */
	public function get_taxonomy_slug( $type ) {
		switch ( $type ) {
			case 'primary':
				$taxonomy_slug = $this->primary_taxonomy ?? false;
				break;
			case 'secondary':
				$taxonomy_slug = $this->secondary_taxonomy ?? false;
				break;
			case 'tertiary':
				$taxonomy_slug = $this->tertiary_taxonomy ?? false;
				break;
			case 'quaternary':
				$taxonomy_slug = $this->quaternary_taxonomy ?? false;
				break;
			default:
				$taxonomy_slug = false;
		}

		return $taxonomy_slug;
	}

	/**
	 * Get profiles post type slug
	 *
	 * @return void
	 */
	public function get_profile_post_type_slug() {

		return $this->profiles_post_type;
	}

	/**
	 * Get landing page post type slug
	 *
	 * @return void
	 */
	public function get_landing_page_post_type_slug() {

		return $this->landing_post_type;
	}

	/**
	 * Filter template to be used for archive pages.
	 *
	 * @param string $template
	 * @return string
	 */
	public function template_archive( $template ) {

		if ( is_post_type_archive( $this->profiles_post_type ) ) {

			$template = PROFILES_ROOT . '/template-parts/archive-profiles.php';

			return apply_filters( 'pmc_profiles_archive_template', $template );
		}

		return $template;
	}


	/**
	 * Filter single page template path.
	 *
	 * @param string $single single page template path
	 * @return string
	 */
	public function template_single( $path ) {

		global $post;

		/* Checks for single template by post type */
		if ( $post->post_type === $this->profiles_post_type ) {

			$path = PROFILES_ROOT . '/template-parts/single-profile.php';

			return apply_filters( 'pmc_profiles_single_profile_template', $path );
		} elseif ( $post->post_type === $this->landing_post_type ) {
			$path = PROFILES_ROOT . '/template-parts/single-landing.php';

			return apply_filters( 'pmc_profiles_single_landing_template', $path );
		}

		return $path;
	}


}
