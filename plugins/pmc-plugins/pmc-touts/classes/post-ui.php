<?php
namespace PMC\Touts;

use PMC\Global_Functions\Traits\Singleton;

class Post_UI {
	use Singleton;

	protected function __construct() {
		// Create the post type
		add_action( 'fm_post_tout', array( $this, 'fields_tout_fields' ) );
	}

	/**
	 * `tout_fields` Fieldmanager fields.
	 */
	function fields_tout_fields() {
		$fm = new \Fieldmanager_Link(
			array(
				'name'       => 'tout_link',
				'attributes' => array( 'style' => 'width:100%;max-width:600px' ),
			)
		);
		$fm->add_meta_box( __( 'Tout Link', 'pmc-tours' ), array( Tout::POST_TYPE_NAME ) );

		$fm = new \Fieldmanager_Group(
			array(
				'name'           => 'relationships',
				'serialize_data' => false,
				'add_to_prefix'  => false,
				'children'       => array(
					'category' => new \Fieldmanager_Select(
						array(
							'label'                     => __( 'Category', 'pmc-tours' ),
							'first_empty'               => true,
							'remove_default_meta_boxes' => true,
							'datasource'                => new \Fieldmanager_Datasource_Term(
								array(
									'taxonomy' => 'category',
									'only_save_to_taxonomy' => true,
								)
							),
						)
					),
				),
			)
		);
		$fm->add_meta_box( __( 'Relationships', 'pmc-tours' ), array( Tout::POST_TYPE_NAME ) );
	}

}
