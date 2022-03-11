<?php
/**
 * Special Reports
 *
 * Responsible for special reports functionality.
 *
 * @package pmc-variety-2020
 */

namespace Variety\Plugins\Variety_VIP;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Special_Reports
 */
class Special_Reports {

	use Singleton;

	/**
	 * Post type name for VIP special report.
	 */
	const POST_TYPE = 'variety_vip_report';

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
	protected function _setup_hooks() {

		add_action( 'init', [ $this, 'register_post_types' ], 23 );
		add_action( 'fm_post_variety_vip_report', [ $this, 'add_special_fields' ] );
		add_action( 'pre_get_posts', [ $this, 'special_reports_archive_query' ] );

	}

	/**
	 * Create post types for VIP.
	 *
	 * @codeCoverageIgnore
	 */
	public function register_post_types() {

		$labels = array(
			'name'                  => _x( 'VIP Special Reports', 'Post type general name', 'pmc-variety' ),
			'singular_name'         => _x( 'VIP Special Report', 'Post type singular name', 'pmc-variety' ),
			'menu_name'             => _x( 'VIP Special Reports', 'Admin Menu text', 'pmc-variety' ),
			'name_admin_bar'        => _x( 'VIP Special Report', 'Add New on Toolbar', 'pmc-variety' ),
			'add_new'               => __( 'Add New', 'pmc-variety' ),
			'add_new_item'          => __( 'Add New VIP Special Report', 'pmc-variety' ),
			'new_item'              => __( 'New VIP Special Report', 'pmc-variety' ),
			'edit_item'             => __( 'Edit VIP Special Report', 'pmc-variety' ),
			'view_item'             => __( 'View VIP Special Report', 'pmc-variety' ),
			'all_items'             => __( 'All VIP Special Reports', 'pmc-variety' ),
			'search_items'          => __( 'Search VIP Special Reports', 'pmc-variety' ),
			'parent_item_colon'     => __( 'Parent VIP Special Reports:', 'pmc-variety' ),
			'not_found'             => __( 'No VIP Special Reports found.', 'pmc-variety' ),
			'not_found_in_trash'    => __( 'No VIP Special Reports found in Trash.', 'pmc-variety' ),
			'featured_image'        => _x( 'Featured Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'pmc-variety' ),
			'archives'              => _x( 'VIP Special Report archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'pmc-variety' ),
			'insert_into_item'      => _x( 'Insert into VIP Special Report', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'pmc-variety' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this VIP Special Report', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'pmc-variety' ),
			'filter_items_list'     => _x( 'Filter VIP Special Reports list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'pmc-variety' ),
			'items_list_navigation' => _x( 'VIP Special Reports list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'pmc-variety' ),
			'items_list'            => _x( 'VIP Special Reports list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'pmc-variety' ),
		);

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'vip-special-reports' ],
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'taxonomies'         => [ 'post_tag', Content::VIP_CATEGORY_TAXONOMY, Content::VIP_TAG_TAXONOMY ],
			'supports'           => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ],
		];

		register_post_type( self::POST_TYPE, $args );

	}

	public function add_special_fields() {

		$fm = new \Fieldmanager_Group(
			[
				'name'     => 'variety_special_report',
				'children' => [
					'report_details' => new \Fieldmanager_Group(
						[
							'label'       => esc_html__( 'Special Report Details', 'pmc-variety' ),
							'collapsible' => true,
							'children'    => [
								'cover_image' => new \Fieldmanager_Media(
									[
										'name'         => 'cover_image',
										'button_label' => esc_html__( 'Set Cover Image ', 'pmc-variety' ),
										'modal_title'  => esc_html__( 'Cover Image ', 'pmc-variety' ),
										'mime_type'    => 'image',
									]
								),
								'offsite_url' => new \Fieldmanager_Link( esc_html__( 'Offsite URL', 'pmc-variety' ) ),
							],
						]
					),
					'tease'          => new \Fieldmanager_Group(
						[
							'label'       => esc_html__( 'Read On Tease', 'pmc-variety' ),
							'collapsible' => true,
							'children'    => [
								'tease_list' => new \Fieldmanager_Group(
									[
										'label'          => esc_html__( 'Tease', 'pmc-variety' ),
										'limit'          => 3,
										'add_more_label' => esc_html__( 'Add Another Tease', 'pmc-variety' ),
										'sortable'       => true,
										'collapsible'    => true,
										'children'       => [
											'tease_text' => new \Fieldmanager_TextArea( esc_html__( 'Tease Copy', 'pmc-variety' ) ),
										],
									]
								),
							],
						]
					),
				],
			]
		);

		return $fm->add_meta_box( esc_html__( 'Special Report', 'pmc-variety' ), self::POST_TYPE );

	}

	/**
	 * Modify the VIP special reports archive query to show 16 reports in an
	 * equal grid.
	 * @codeCoverageIgnore
	 *
	 * @param \WP_Query $query The WP Query object.
	 *
	 * @return \WP_Query
	 */
	public function special_reports_archive_query( $query ) {
		if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( self::POST_TYPE ) ) {
			$query->set( 'posts_per_page', 16 );
		}

		return $query;
	}

}

// EOF.
