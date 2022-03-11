<?php
/**
 * Streamers Section Header widget
 *
 * @package pmc-variety
 */

namespace Variety\Inc\Widgets;

class Streamers_Section_Header extends \FM_Widget {
	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct() {
		parent::__construct(
			'streamers-section-header',
			__( 'Variety - Streamers section header', 'pmc-variety' ),
			array( 'description' => __( 'Displays streamers section header', 'pmc-variety' ) )
		);
	}

	/**
	 * Echoes the widget content
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @throws \Exception
	 *
	 * @codeCoverageIgnore just rendering template.
	 */
	public function widget( $args, $instance ) {

		\PMC::render_template(
			sprintf( '%s/template-parts/widgets/streamers-section-header.php', untrailingslashit( CHILD_THEME_PATH ) ),
			[
				'data' => $instance,
			],
			true
		);
	}

	/**
	 * Define the fields that should appear in the widget.
	 *
	 * @return array Fieldmanager fields.
	 */
	protected function fieldmanager_children() {
		return [
			'section_heading' => new \Fieldmanager_TextField( esc_html__( 'Section heading', 'pmc-variety' ) ),
			'section_tagline' => new \Fieldmanager_TextField( esc_html__( 'Section tagline', 'pmc-variety' ) ),
		];
	}
}
