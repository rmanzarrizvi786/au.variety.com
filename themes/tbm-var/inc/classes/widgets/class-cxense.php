<?php
/**
 * Cxense developed modules
 */

namespace Variety\Inc\Widgets;

class Cxense extends Variety_Base_Widget {

	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct() {

		parent::__construct(
			'cxense',
			__( 'Variety - Cxense Widget', 'pmc-variety' ),
			[ 'description' => __( 'Widgets developed by cxense', 'pmc-variety' ) ]
		);
	}

	/**
	 * To render output of widget.
	 *
	 * @param array $instance Widget instance data.
	 *
	 * @return void
	 * @throws \Exception
	 *
	 */
	protected function _output( $instance ) {

		\PMC::render_template(
			CHILD_THEME_PATH . '/template-parts/widgets/cxense.php',
			[
				'instance' => $instance,
			],
			true
		);

	}

	/**
	 * Get Fields.
	 *
	 * Get the option fields for this widget.
	 *
	 * @return array
	 *
	 */
	protected function get_fields() {

		return [
			'widget_id' => [
				'label' => __( 'Widget ID', 'pmc-variety' ),
				'type'  => 'text',
			],
			'id'        => [
				'label' => __( 'Target Element ID', 'pmc-variety' ),
				'type'  => 'text',
			],
			'classes'   => [
				'label' => __( 'Additional CSS classes', 'pmc-variety' ),
				'type'  => 'text',
			],
		];
	}
}
