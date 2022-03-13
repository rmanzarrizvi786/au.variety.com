<?php
/**
 * Taxonomy for Featured Program Group.
 *
 * @package pmc-featured-program
 */

namespace PMC\Featured_Program;

use PMC\Global_Functions\Traits\Singleton;

class Taxonomy_Featured_Program_Group extends Taxonomy {

	use Singleton;

	/**
	 * Build the gender taxonomy object.
	 *
	 * Taxonomy_Featured_Program_Group constructor.
	 *
	 * @codeCoverageIgnore Ignored in pmc-featured-program
	 * 
	 */
	public function __construct() {
		
		$this->name         = Config::get_instance()->category_group();
		$this->object_types = [ Config::get_instance()->post_type() ];

		parent::__construct();
	}

	/**
	 * Creates the taxonomy.
	 *
	 * @codeCoverageIgnore Ignored in pmc-featured-program
	 */
	public function create_taxonomy() {

		register_taxonomy(
			$this->name,
			$this->object_types,
			[
				'labels'            => [
					'name'                       => _x( 'Featured Program Groups', 'taxonomy general name', 'pmc-featured-program' ),
					'singular_name'              => _x( 'Featured Program Group', 'taxonomy singular name', 'pmc-featured-program' ),
					'search_items'               => __( 'Search Featured Program Group', 'pmc-featured-program' ),
					'popular_items'              => __( 'Popular Featured Program Groups', 'pmc-featured-program' ),
					'all_items'                  => __( 'All Featured Program Groups', 'pmc-featured-program' ),
					'parent_item'                => __( 'Parent Category', 'pmc-featured-program' ),
					'parent_item_colon'          => __( 'Parent Category:', 'pmc-featured-program' ),
					'edit_item'                  => __( 'Edit Featured Program Group', 'pmc-featured-program' ),
					'update_item'                => __( 'Update Featured Program Group', 'pmc-featured-program' ),
					'add_new_item'               => __( 'Add New Featured Program Group', 'pmc-featured-program' ),
					'new_item_name'              => __( 'New Featured Program Group Name', 'pmc-featured-program' ),
					'separate_items_with_commas' => __( 'Separate Featured Program Groups with commas', 'pmc-featured-program' ),
					'add_or_remove_items'        => __( 'Add or remove Featured Program Group', 'pmc-featured-program' ),
					'choose_from_most_used'      => __( 'Choose from the most used Featured Program Group', 'pmc-featured-program' ),
					'not_found'                  => __( 'No Featured Program Groups found.', 'pmc-featured-program' ),
					'menu_name'                  => __( 'Featured Program Groups', 'pmc-featured-program' ),
				],
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'rewrite'           => [ 'slug' => 'featured-program-group' ],
				'show_tagcloud'     => false,
			] 
		);

	}
}
