<?php
/**
 * Content hub functionalities.
 *
 * @package pmc-content-hub
 */

namespace PMC\Content_Hub;

use \PMC\Global_Functions\Traits\Singleton;


/**
 * Class Content_Hub
 */
class Content_Hub {

	use Singleton;

	/**
	 * Initialize the class and set its properties.
	 */
	protected function __construct() {
		$this->_setup_hooks();

		if ( function_exists( 'fm_register_submenu_page' ) ) {
			fm_register_submenu_page( 'carousel_terms', 'curation', __( 'Carousel terms', 'pmc-content-hub' ), __( 'Carousel terms', 'pmc-content-hub' ), 'edit_posts' );
		}
	}

	/**
	 * Setup Hooks
	 */
	protected function _setup_hooks() {
		add_action( 'fm_term_post_tag', [ $this, 'add_custom_term_meta_fields' ] );
		add_action( 'fm_submenu_carousel_terms', [ $this, 'add_carousel_terms_submenu' ] );
		add_filter( 'pmc_carousel_terms_list_args', [ $this, 'filter_terms_list_args' ], 10, 2 );
	}

	/**
	 * Add custom post tag meta fields.
	 *
	 * @param string $name title.
	 *
	 * @return \Fieldmanager_Group
	 */
	public function add_custom_term_meta_fields( $name ) {

		$fm = new \Fieldmanager_Group(
			[
				'name'     => '_pmc_content_hub_data',
				'children' => [
					'load_category_template' => new \Fieldmanager_Checkbox(
						[
							'label' => __( 'Check to load category template for the tag term.', 'pmc-content-hub' ),
						]
					),
					'featured_embed'         => new \Fieldmanager_Media(
						[
							'label' => __( 'Featured Image', 'pmc-content-hub' ),
						]
					),
					'ceros_embed'            => new \Fieldmanager_RichTextArea(
						[
							'label'           => __( 'Ceros Embed', 'pmc-content-hub' ),
							'editor_settings' => [
								'media_buttons' => false,
							],
						]
					),
					'description_text'       => new \Fieldmanager_RichTextArea(
						[
							'label'           => __( 'Description Text', 'pmc-content-hub' ),
							'editor_settings' => [
								'media_buttons' => false,
							],
						]
					),
				],
			]
		);

		$fm->add_term_meta_box( __( 'Content hub data', 'pmc-content-hub' ), $name );

		return $fm;
	}

	/**
	 * Add carousel terms submenu.
	 *
	 * @return \Fieldmanager_Group
	 */
	public function add_carousel_terms_submenu() {

		$fm = new \Fieldmanager_Group(
			[
				'name'        => 'carousel_terms',
				'label'       => __( 'Post tags', 'pmc-content-hub' ),
				'collapsible' => true,
				'children'    => [
					'post_tags' => new \Fieldmanager_Group(
						[
							'label'          => __( 'Tag', 'pmc-content-hub' ),
							'add_more_label' => esc_html__( 'Add Another Tag', 'pmc-content-hub' ),
							'limit'          => false,
							'children'       => [
								'post_tag' => new \Fieldmanager_Autocomplete(
									[
										'label'      => esc_html__( 'Tag', 'pmc-content-hub' ),
										'datasource' => new \Fieldmanager_Datasource_Term(
											[
												'taxonomy' => 'post_tag',
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

		$fm->activate_submenu_page();

		return $fm;
	}

	/**
	 * Filters the carousel terms list arguments.
	 *
	 * @param array $args An array of terms list arguments.
	 *
	 * @return array
	 */
	public function filter_terms_list_args( $args ) {

		if ( 'post_tag' !== $args['taxonomy'] ) {
			return $args;
		}

		$carousel_terms_array = get_option( 'carousel_terms', [] );

		$terms_array = [];

		foreach ( $carousel_terms_array['post_tags'] as $term ) {
			$terms_array[] = $term['post_tag'];
		}

		if ( empty( $terms_array ) ) {
			return $args;
		}

		$args['include'] = $terms_array;

		return $args;
	}

}
