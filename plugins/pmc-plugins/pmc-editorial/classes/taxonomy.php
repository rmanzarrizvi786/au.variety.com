<?php
namespace PMC\Editorial;

use PMC\Global_Functions\Traits\Singleton;

class Taxonomy {
	use Singleton;

	/**
	 * Name of the taxonomy.
	 */
	const NAME = 'editorial';

	/**
	 * Default supported post types
	 *
	 * @var array
	 */
	protected $_post_types = [
		'post',
		'pmc-gallery',
		'pmc_top_video',
		'pmc_list',
		'tout',
	];

	protected function __construct() {
		// Using late action since this object is instantiated from Plugin init priority 15
		add_action( 'init', [ $this, 'create_taxonomy' ], 20 );
	}

	/**
	 * Creates the taxonomy.
	 */
	public function create_taxonomy(): void {

		register_taxonomy(
			self::NAME,
			apply_filters( 'pmc_editorial_taxonomy_post_types', $this->_post_types ),
			[
				'label'             => __( 'Editorial', 'pmc-editorial' ),
				'labels'            => [
					'name'               => _x( 'Editorials', 'taxonomy general name', 'pmc-editorial' ),
					'singular_name'      => _x( 'Editorial', 'taxonomy singular name', 'pmc-editorial' ),
					'add_new_item'       => __( 'Add New Editorial', 'pmc-editorial' ),
					'edit_item'          => __( 'Edit Editorial', 'pmc-editorial' ),
					'new_item'           => __( 'New Editorial', 'pmc-editorial' ),
					'view_item'          => __( 'View Editorial', 'pmc-editorial' ),
					'search_items'       => __( 'Search Editorials', 'pmc-editorial' ),
					'not_found'          => __( 'No Editorials found.', 'pmc-editorial' ),
					'not_found_in_trash' => __( 'No Editorials found in Trash.', 'pmc-editorial' ),
					'all_items'          => __( 'Editorials', 'pmc-editorial' ),
				],
				'query_var'         => true,
				'show_ui'           => true,
				'hierarchical'      => true,
				'rewrite'           => [
					'slug'       => apply_filters( 'pmc_editorial_taxonomy_rewrite_slug', 'e' ),
					'with_front' => false,
				],
				'capabilities'      => [
					'manage_terms' => 'manage_options',
					'edit_terms'   => 'manage_options',
					'delete_terms' => 'manage_options',
					'assign_terms' => 'edit_posts',
				],
				'show_in_menu'      => true,
				'show_in_nav_menus' => true,
				'show_admin_column' => false,
			]
		);

	}
}
