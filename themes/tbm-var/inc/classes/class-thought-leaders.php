<?php
/**
 * Class Thought_Leaders
 *
 * @since 2015-11-24 Amit Sannad PMCVIP-315 Archive class
 *
 * @version Updated 2017-08-08 Milind More CDWE-475
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

class Thought_Leaders {

	use Singleton;

	const SLUG = 'vy-thought-leaders';

	//array containing the image sizes.
	protected static $_a__img = array();

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

		static::$_a__img = array(
			'thought_leader_archive_thumb' => array(
				'w' => 490,
				'h' => 276,
			),
		);

		//add image size
		add_image_size( 'thought_leader_archive_thumb', static::$_a__img['thought_leader_archive_thumb']['w'], static::$_a__img['thought_leader_archive_thumb']['h'], true );

	}

	/**
	 * Initialize actions and filters.
	 */
	protected function _setup_hooks() {

		add_action( 'init', array( $this, 'thought_leader_cpt_init' ) );
		add_action( 'custom_metadata_manager_init_metadata', array( $this, 'init_custom_fields' ) );
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );

	}

	/**
	 * Initialize custom post type
	 */
	public function thought_leader_cpt_init() {

		register_post_type( self::SLUG, array(
			'labels'              => array(
				'name'          => esc_html__( 'Thought Leaders', 'pmc-variety' ),
				'singular_name' => esc_html__( 'Thought Leader', 'pmc-variety' ),
				'add_new'       => esc_html_x( 'Add New Thought Leader', 'thought-leader', 'pmc-variety' ),
				'add_new_item'  => esc_html_x( 'Add New Thought Leader', 'thought-leader', 'pmc-variety' ),
				'edit'          => esc_html__( 'Edit Thought Leader', 'pmc-variety' ),
				'edit_item'     => esc_html__( 'Edit Thought Leader', 'pmc-variety' ),
				'new_item'      => esc_html__( 'New Thought Leader', 'pmc-variety' ),
				'view'          => esc_html__( 'View Thought Leader', 'pmc-variety' ),
				'view_item'     => esc_html__( 'View Thought Leader', 'pmc-variety' ),
				'search_items'  => esc_html__( 'Search Thought Leader', 'pmc-variety' ),
			),
			'show_ui'             => true,
			'public'              => false,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'has_archive'         => true,
			'hierarchical'        => false,
			'supports'            => array(
				'title',
				'editor',
				'excerpt',
				'thumbnail',
				'revisions',
				'page-attributes',
				'author',
			),
			'rewrite'             => array(
				'slug' => 'thought-leaders',
			),
		) );
	}

	/**
	 * Modifies query object to change posts per page.
	 *
	 * @param object $query
	 */
	public function pre_get_posts( $query ) {

		if ( ! is_admin() && $query->is_main_query() && $query->is_post_type_archive( self::SLUG ) ) {
			$query->set( 'posts_per_page', 12 );
		}
	}

	/**
	 * Initialize Custom Fields with custom metadata manager.
	 */
	public function init_custom_fields() {

		if ( ! function_exists( 'x_add_metadata_field' ) && ! function_exists( 'x_add_metadata_group' ) ) {
			return;
		}

		$grp_args = array(
			'label' => esc_html__( 'Thought Leaders Meta Data', 'pmc-variety' ),
		);

		$grp = self::SLUG . '_grp';

		x_add_metadata_group( $grp, self::SLUG, $grp_args );

		$args = array(
			'group'      => $grp,
			'field_type' => 'text',
			'label'      => esc_html__( 'Publication date to show', 'pmc-variety' ),
		);

		x_add_metadata_field( self::SLUG . '_publication_date', self::SLUG, $args );

		$args = array(
			'group'      => $grp,
			'field_type' => 'upload',
			'label'      => esc_html__( 'Thought Leaders PDF', 'pmc-variety' ),
		);

		x_add_metadata_field( self::SLUG . '_tl_pdf', self::SLUG, $args );

	}

	/**
	 * function will return post meta.
	 *
	 * @param string $name
	 * @param int $post_id
	 *
	 * @return boolean|mixed
	 */
	public function get_meta( $name, $post_id ) {

		if ( empty( $name ) || empty( $post_id ) || ! is_numeric( $post_id ) ) {
			return false;
		}

		$meta_value = get_post_meta( $post_id, self::SLUG . $name, true );

		if ( ! empty( $meta_value ) ) {
			return $meta_value;
		}

	}

	/**
	 * Redirect non logged in users.
	 */
	public function template_redirect() {

		if ( is_post_type_archive( self::SLUG ) && ! \PMC\Uls\Session::get_instance()->can_access( 'vy-digital' ) ) {
			wp_safe_redirect( '/subscribe-us/', 302 );
			exit;
		}
	}

}
