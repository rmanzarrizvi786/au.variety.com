<?php
/**
 * Taxonomy for Featured Program Group.
 *
 * @package pmc-featured-program
 */

namespace PMC\Featured_Program;

use PMC\Global_Functions\Traits\Singleton;

class Taxonomy_Tag_Group extends Taxonomy {

	use Singleton;

	/**
	 * Build the gender taxonomy object.
	 *
	 * Taxonomy_Tag_Group constructor.
	 *
	 * @codeCoverageIgnore Ignored in pmc-featured-program
	 */
	public function __construct() {

		$this->name         = Config::get_instance()->tag_group();
		$this->object_types = [ Config::get_instance()->post_type() ];
		
		// Hook to add custom meta-boxes for tag groups.
		add_action( 'fm_term_' . $this->name, [ $this, 'register_custom_fields' ] );

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
					'name'                       => _x( 'Tag Groups', 'taxonomy general name', 'pmc-featured-program' ),
					'singular_name'              => _x( 'Tag Group', 'taxonomy singular name', 'pmc-featured-program' ),
					'search_items'               => __( 'Search Tag Group', 'pmc-featured-program' ),
					'popular_items'              => __( 'Popular Tag Groups', 'pmc-featured-program' ),
					'all_items'                  => __( 'All Tag Groups', 'pmc-featured-program' ),
					'edit_item'                  => __( 'Edit Tag Group', 'pmc-featured-program' ),
					'update_item'                => __( 'Update Tag Group', 'pmc-featured-program' ),
					'add_new_item'               => __( 'Add New Tag Group', 'pmc-featured-program' ),
					'new_item_name'              => __( 'New Tag Group Name', 'pmc-featured-program' ),
					'separate_items_with_commas' => __( 'Separate Tag Groups with commas', 'pmc-featured-program' ),
					'add_or_remove_items'        => __( 'Add or remove Tag Group', 'pmc-featured-program' ),
					'choose_from_most_used'      => __( 'Choose from the most used Tag Group', 'pmc-featured-program' ),
					'not_found'                  => __( 'No Tag Groups found.', 'pmc-featured-program' ),
					'menu_name'                  => __( 'Tag Groups', 'pmc-featured-program' ),
				],
				'show_ui'           => true,
				'show_admin_column' => true,
				'rewrite'           => false,
				'show_tagcloud'     => false,
			] 
		);
	}

	/**
	 * Post Meta fields for Tag Group.
	 *
	 * @codeCoverageIgnore Ignored in pmc-featured-program
	 */
	public function register_custom_fields() {

		$fm = new \Fieldmanager_Group(
			[
				'name'           => 'pmc-featured-program-tag-group',
				'label'          => __( 'Search and Add Tags', 'pmc-featured-program' ),
				'limit'          => 1,
				'serialize_data' => false,
				'add_to_prefix'  => false,
				'children'       => [
					'tags' => new \Fieldmanager_Autocomplete(
						[
							'limit'              => 0,
							'sortable'           => true,
							'one_label_per_item' => false,
							'extra_elements'     => 0,
							'minimum_count'      => 1,
							'add_more_label'     => __( 'Add Tag', 'pmc-featured-program' ),
							'datasource'         => new \Fieldmanager_Datasource_Term(
								[
									'taxonomy' => 'post_tag',
									'only_save_to_taxonomy' => true,
								] 
							),
						] 
					),
				],
			] 
		);

		$fm->add_term_meta_box( __( 'Tags', 'pmc-featured-program' ), [ $this->name ] );

		return $fm;

	}

}
