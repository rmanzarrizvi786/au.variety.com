<?php
/**
 * Class containing Swiftype search widget.
 *
 * @since 2015-11-19 - Mike Auteri - PPT-6376
 * @version 2015-11-19 - Mike Auteri - PPT-6376
 *
 */
namespace PMC\Swiftype;

use \PMC;

class Widget extends \WP_Widget {

	const widget_id = 'pmc_swiftype_widget';
	/*
	 * Defines the widget name
	 */
	public function __construct() {
		// Instantiate the parent object
		parent::__construct( self::widget_id, __( 'Swiftype Site Search', 'pmc-swiftype' ), array(
			'description' => __( 'Render Swiftype Search Form', 'pmc-swiftype' ),
		) );
	} // __construct

	/**
	 * Outputs the content of the widget
	 *
	 * @since 2015-11-19 - Mike Auteri - PPT-6376
	 * @version 2015-11-19 - Mike Auteri - PPT-6376
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
		?>
		<section id="swiftype-search-widget" class="swiftype">
			<div class="search_form block search_form_widget">
				<div data-st-search-form="small_search_form"></div>
			</div>
		</section>
		<?php
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @since 2015-11-19 - Mike Auteri - PPT-6376
	 * @version 2015-11-19 - Mike Auteri - PPT-6376
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		// outputs the options form on admin
		?>
		<p></p>
		<?php
	}

}

//EOF
