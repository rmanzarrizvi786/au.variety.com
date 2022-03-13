<?php

namespace PMC\Cxense;

class Widget extends \FM_Widget {

	public function __construct() {

		parent::__construct(
			'pmc-cxense',
			__( 'Cxense Widget', 'pmc-cxense' ),
			[ 'description' => __( 'Widget for cxense', 'pmc-cxense' ) ]
		);
	}

	public function widget( $args, $instance ) {

		wp_enqueue_script(
			'pmc_cxense_widget_js',
			sprintf( '%s/assets/js/pmc-cxense-widget.js', untrailingslashit( PMC_CXENSE_URI ) ),
			[ 'pmc_cxense_js' ],
			false,
			true
		);

		echo wp_kses_post( $args['before_widget'] );

		$this->_output( $instance );

		echo wp_kses_post( $args['after_widget'] );
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
			PMC_CXENSE_DIR . '/templates/cxense-widget.php',
			$instance,
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
	protected function fieldmanager_children() {

		return [
			'widget_id' => new \Fieldmanager_TextField( __( 'Widget ID', 'pmc-cxense' ) ),
			'id'        => new \Fieldmanager_TextField( __( 'Target Element ID', 'pmc-cxense' ) ),
			'classes'   => new \Fieldmanager_TextField( __( 'Additional CSS classes', 'pmc-cxense' ) ),
		];

	}
}
//EOF
