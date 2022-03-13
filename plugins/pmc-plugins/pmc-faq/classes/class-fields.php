<?php
namespace PMC\FAQ;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Fields
 *
 * Adds meta boxes to post types in the allow list
 * handled by `pmc_faq_post_types` filter for the FAQ.
 */
class Fields {

	use Singleton;

	protected $_post_types = [];

	/**
	 * Fields constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks() : void {
		add_action( 'init', [ $this, 'after_init_hooks' ] );
	}

	/**
	 * Added FAQ meta box to post types. By default, just post.
	 */
	public function after_init_hooks() : void {
		$this->_post_types = [ 'post' ];
		$post_types        = apply_filters( 'pmc_faq_post_types', $this->_post_types );

		if ( is_array( $post_types ) ) {
			$this->_post_types = array_filter( array_unique( (array) $post_types ) );
		}

		foreach ( $this->_post_types as $post_type ) {
			if ( post_type_exists( $post_type ) ) {
				$fm_action = sprintf( 'fm_post_%s', $post_type );

				add_action( $fm_action, [ $this, 'add_faq_meta_box' ] );
			}
		}
	}

	/**
	 * Add FAQ meta box.
	 *
	 * @return \Fieldmanager_Context_Post
	 */
	public function add_faq_meta_box() : \Fieldmanager_Context_Post {
		$fm = new \Fieldmanager_Group(
			[
				'name'           => 'pmc_faq',
				'serialize_data' => false,
				'add_to_prefix'  => false,
				'description'    => __( 'Use the shortcode [pmc-faq] to include FAQ in the post content.', 'pmc-faq' ),
				'children'       => [
					'pmc_faq_title'       => new \Fieldmanager_TextField(
						[
							'label' => __( 'Title', 'pmc-faq' ),
							'name'  => 'pmc_faq_title',
						]
					),
					'pmc_faq_description' => new \Fieldmanager_TextArea(
						[
							'label' => __( 'Description (optional)', 'pmc-faq' ),
							'name'  => 'pmc_faq_description',
						]
					),
					'pmc_faq_questions'   => new \Fieldmanager_Group(
						[
							'name'           => 'pmc_faq_questions',
							'limit'          => 0,
							'label'          => __( 'Add Question', 'pmc-faq' ),
							/* translators: %s: question title. */
							'label_macro'    => [ __( 'Question: %s', 'pmc-faq' ), 'pmc_faq_question' ],
							'add_more_label' => __( 'Add Another Question', 'pmc-faq' ),
							'sortable'       => true,
							'collapsible'    => true,
							'collapsed'      => false,
							'extra_elements' => 0,
							'children'       => [
								'pmc_faq_question' => new \Fieldmanager_Textfield(
									[
										'label'            => __( 'Question', 'pmc-faq' ),
										'name'             => 'pmc_faq_question',
										'validation_rules' => [
											'required' => true,
										],
									]
								),
								'pmc_faq_answer'   => new \Fieldmanager_RichTextarea(
									[
										'label'            => __( 'Answer', 'pmc-faq' ),
										'name'             => 'pmc_faq_answer',
										'validation_rules' => [
											'required' => true,
										],
									]
								),
							],
						]
					),
				],
			]
		);

		return $fm->add_meta_box( __( 'Frequently Asked Questions', 'pmc-faq' ), $this->_post_types );
	}

}
