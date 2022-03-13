<?php
/**
 * Styled_Heading class.
 *
 * @package pmc-styled-heading
 * @since 2018-5-15
 */

namespace PMC\Styled_Heading;

use Fieldmanager_Group;
use Fieldmanager_Select;
use Fieldmanager_TextField;
use Fieldmanager_Colorpicker;
use Fieldmanager_Hidden;

class Fields {

	/**
	 * A human-readable name for the meta box.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * A generated unique ID for the meta field group.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * The metabox field group.
	 *
	 * @var \Fieldmanager_Group
	 */
	public $field_group;

	/**
	 * Class constructor.
	 *
	 * @param string $name A human-readable name for the meta box.
	 * @param string $id A generated unique ID for the meta field group.
	 */
	public function __construct( $name, $id ) {

		$this->name = $name;
		$this->id   = $id;

		add_action( 'fm_post_post', array( $this, 'init' ) );

	}

	/**
	 * Provides a filter to allow opting out of the metabox after the global post is set up.
	 */
	public function maybe_remove_metabox() {

		/**
		 * Filters whether to show the styled heading meta box.
		 *
		 * @param string The registered styled heading ID.
		 */
		$show_meta_box = apply_filters( Styled_Heading::FILTER_PREFIX . 'show_meta_box', $this->id );

		if ( false === $show_meta_box ) {
			remove_meta_box( 'fm_meta_box_' . Styled_Heading::FILTER_PREFIX . $this->id, 'post', 'normal' );
		}

	}

	/**
	 * Creates the meta box.
	 */
	public function init() {

		$this->field_group = new Fieldmanager_Group( array(
			'name'     => Styled_Heading::FILTER_PREFIX . $this->id,
			'children' => $this->get_group_fields(),
		) );

		$this->field_group->add_meta_box( $this->name, 'post' );

		add_action( 'add_meta_boxes', array( $this, 'maybe_remove_metabox' ), 999 );

	}

	/**
	 * Provides the fields passed as children to the main meta box.
	 *
	 * @return array An associative array of fields.
	 */
	public function get_group_fields() {

		$fields = array(
			'text_lines'       => new Fieldmanager_Group( array(
				'label'                     => esc_html__( 'Lines', 'pmc-styled-heading' ),
				'description'               => esc_html__( 'Add up to five lines of text with customized styles for each.', 'pmc-styled-heading' ),
				'description_after_element' => false,
				'collapsible'               => true,
				'label_element'             => 'h3',
				'children'                  => array(
					'text_line' => new Fieldmanager_Group( array(
						'label'          => esc_html__( 'Line', 'pmc-styled-heading' ),
						'limit'          => 5,
						'label_element'  => 'h4',
						'add_more_label' => esc_html__( 'Add Line', 'pmc-styled-heading' ),
						'collapsible'    => true,
						'children'       => $this->get_line_fields(),
					) ),
				),
			) ),

			'container_fields' => new Fieldmanager_Group( array(
				'label'                     => esc_html__( 'Container Styles', 'pmc-styled-heading' ),
				'description'               => esc_html__( 'Style customization for the text container.', 'pmc-styled-heading' ),
				'description_after_element' => false,
				'label_element'             => 'h3',
				'collapsible'               => true,
				'children'                  => array(

					'max-width'        => new Fieldmanager_TextField( array(
						'label'         => esc_html__( 'Maximum Width (in Pixels)', 'pmc-styled-heading' ),
						'description'   => esc_html__( 'The maximum width of the element. Leave blank to set no maximum.', 'pmc-styled-heading' ),
						'label_element' => 'h4',
						'input_type'    => 'number',
						'attributes'    => array(
							'step' => 1,
						),
					) ),

					'padding-top'      => new Fieldmanager_TextField( array(
						'label'         => esc_html__( 'Top Padding (in Pixels)', 'pmc-styled-heading' ),
						'description'   => esc_html__( 'White space above the text.', 'pmc-styled-heading' ),
						'label_element' => 'h4',
						'input_type'    => 'number',
						'default_value' => '24',
						'attributes'    => array(
							'step' => 1,
						),
					) ),

					'padding-right'    => new Fieldmanager_TextField( array(
						'label'         => esc_html__( 'Right Padding (in Pixels)', 'pmc-styled-heading' ),
						'description'   => esc_html__( 'White space to the right of the text.', 'pmc-styled-heading' ),
						'label_element' => 'h4',
						'input_type'    => 'number',
						'default_value' => '24',
						'attributes'    => array(
							'step' => 1,
						),
					) ),

					'padding-bottom'   => new Fieldmanager_TextField( array(
						'label'         => esc_html__( 'Padding Bottom (in Pixels)', 'pmc-styled-heading' ),
						'description'   => esc_html__( 'White space below the text.', 'pmc-styled-heading' ),
						'label_element' => 'h4',
						'input_type'    => 'number',
						'default_value' => '24',
						'attributes'    => array(
							'step' => 1,
						),
					) ),

					'padding-left'     => new Fieldmanager_TextField( array(
						'label'         => esc_html__( 'Left Padding (in Pixels)', 'pmc-styled-heading' ),
						'description'   => esc_html__( 'White space to the leftt of the text.', 'pmc-styled-heading' ),
						'label_element' => 'h4',
						'input_type'    => 'number',
						'default_value' => '24',
						'attributes'    => array(
							'step' => 1,
						),
					) ),

					'text-align'       => new Fieldmanager_Select( array(
						'label'         => esc_html__( 'Text alignment', 'pmc-styled-heading' ),
						'description'   => esc_html__( 'Text alignment within its container.', 'pmc-styled-heading' ),
						'label_element' => 'h4',
						'default_value' => '400',
						'first_empty'   => false,
						'options'       => array(
							'left'    => esc_html__( 'Left', 'pmc-styled-heading' ),
							'right'   => esc_html__( 'Right', 'pmc-styled-heading' ),
							'center'  => esc_html__( 'Center', 'pmc-styled-heading' ),
							'justify' => esc_html__( 'Justified', 'pmc-styled-heading' ),
						),
					) ),

					'background-color' => new Fieldmanager_Colorpicker( array(
						'label'         => esc_html__( 'Background Color', 'pmc-styled-heading' ),
						'description'   => esc_html__( 'Leave this field blank for a transparent background.', 'pmc-styled-heading' ),
						'label_element' => 'h4',
					) ),

					'border-color'     => new Fieldmanager_Colorpicker( array(
						'label'         => esc_html__( 'Border Color', 'pmc-styled-heading' ),
						'description'   => esc_html__( 'Leave this field blank to have no border.', 'pmc-styled-heading' ),
						'label_element' => 'h4',
					) ),

					'border-width'     => new Fieldmanager_TextField( array(
						'label'         => esc_html__( 'Border Width (in Pixels)', 'pmc-styled-heading' ),
						'description'   => esc_html__( 'Leave this field blank to have no border.', 'pmc-styled-heading' ),
						'label_element' => 'h4',
						'input_type'    => 'number',
						'default_value' => 1,
						'attributes'    => array(
							'step' => 1,
						),
					) ),

					'margin'           => new Fieldmanager_Select( array(
						'label'         => esc_html__( 'Title Location', 'pmc-styled-heading' ),
						'description'   => esc_html__( 'Select the location of the title on the image.', 'pmc-styled-heading' ),
						'label_element' => 'h4',
						'default_value' => '400',
						'first_empty'   => false,
						'options'       => array(
							'0 auto auto 0'    => esc_html__( 'Top Left', 'pmc-styled-heading' ),
							'0 auto auto auto' => esc_html__( 'Top Center', 'pmc-styled-heading' ),
							'0 0 auto auto'    => esc_html__( 'Top Right', 'pmc-styled-heading' ),
							'auto auto auto 0' => esc_html__( 'Middle Left', 'pmc-styled-heading' ),
							'auto'             => esc_html__( 'Middle Center', 'pmc-styled-heading' ),
							'auto 0 auto auto' => esc_html__( 'Middle Right', 'pmc-styled-heading' ),
							'auto auto 0 0'    => esc_html__( 'Bottom Left', 'pmc-styled-heading' ),
							'auto auto 0 auto' => esc_html__( 'Bottom Center', 'pmc-styled-heading' ),
							'auto 0 0 auto'    => esc_html__( 'Bottom Right', 'pmc-styled-heading' ),
						),
					) ),

					'border-style'     => new Fieldmanager_Hidden( array(
						'default_value' => 'solid',
					) ),
				),
			) ),
		);

		/**
		 * Filters the fields registered for the styled heading metabox.
		 *
		 * @param array The fields to register.
		 * @param string The metabox's name.
		 * @param string The metabox's ID.
		 */
		return apply_filters( Styled_Heading::FILTER_PREFIX . 'fields', $fields, $this->name, $this->id );

	}

	/**
	 * Provides fields within the box of repeatable text lines.
	 *
	 * @return array An associative array of fields.
	 */
	public function get_line_fields() {

		$line_fields = array(
			'text'           => new Fieldmanager_TextField( array(
				'label'         => esc_html__( 'Text', 'pmc-styled-heading' ),
				'description'   => esc_html__( 'Enter the text for this line.', 'pmc-styled-heading' ),
				'label_element' => 'h5',
				'attributes'    => array(
					'size' => false,
				),
			) ),

			'display'        => new Fieldmanager_Hidden( array(
				'default_value' => 'block',
			) ),

			'color'          => new Fieldmanager_Colorpicker( array(
				'label'         => 'Font Color',
				'description'   => esc_html__( 'Enter or select a hex value for this line\'s font color.', 'pmc-styled-heading' ),
				'label_element' => 'h5',
				'default_value' => '#000',
			) ),

			'font-size'      => new Fieldmanager_TextField( array(
				'label'         => esc_html__( 'Font Size (in Pixels)', 'pmc-styled-heading' ),
				'description'   => esc_html__( 'Enter the font size. Minimum: 12. Maximum: 64.', 'pmc-styled-heading' ),
				'label_element' => 'h5',
				'input_type'    => 'number',
				'default_value' => 24,
				'attributes'    => array(
					'min'  => 12,
					'max'  => 64,
					'step' => 1,
				),
			) ),

			'font-weight'    => new Fieldmanager_Select( array(
				'label'         => esc_html__( 'Font Weight', 'pmc-styled-heading' ),
				'description'   => esc_html__( 'Select the weight of the text.', 'pmc-styled-heading' ),
				'label_element' => 'h5',
				'default_value' => '400',
				'first_empty'   => false,
				'options'       => array(
					'200' => esc_html__( 'Light', 'pmc-styled-heading' ),
					'400' => esc_html__( 'Normal', 'pmc-styled-heading' ),
					'700' => esc_html__( 'Bold', 'pmc-styled-heading' ),
				),
			) ),

			'letter-spacing' => new Fieldmanager_TextField( array(
				'label'         => esc_html__( 'Letter Spacing (in Pixels)', 'pmc-styled-heading' ),
				'description'   => esc_html__( 'Sets the amount of spacing between individual letters. Minimum: -5. Maximum: 5.', 'pmc-styled-heading' ),
				'label_element' => 'h5',
				'input_type'    => 'number',
				'default_value' => '0',
				'attributes'    => array(
					'min'  => -5,
					'max'  => 5,
					'step' => 0.01,
				),
			) ),

			'line-height'    => new Fieldmanager_TextField( array(
				'label'         => esc_html__( 'Line Height (in Pixels)', 'pmc-styled-heading' ),
				'description'   => esc_html__( 'Sets the height of this line. Leave blank to use the default. Minimum: 12. Maximum: 64.', 'pmc-styled-heading' ),
				'label_element' => 'h5',
				'input_type'    => 'number',
				'attributes'    => array(
					'min' => 12,
					'max' => 64,
				),
			) ),

		);

		/**
		 * Filters the fields registered for an individual line of text.
		 *
		 * @param array The fields to register.
		 * @param string The metabox's name.
		 * @param string The metabox's ID.
		 */
		return apply_filters( Styled_Heading::FILTER_PREFIX . 'line_fields', $line_fields, $this->name, $this->id );

	}

	/**
	 * Retrieves fields by field group name and post ID.
	 *
	 * @param string $field_id A field ID.
	 * @param int $post_id A post ID.
	 * @return array The fields.
	 */
	public static function get_fields( $field_id, $post_id = null ) {

		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		$fields = get_post_meta( $post_id, Styled_Heading::FILTER_PREFIX . $field_id, true );

		return $fields;

	}
}
