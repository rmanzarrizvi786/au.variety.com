<?php
namespace PMC\Outbrain;

use PMC;

/**
 * PMC Outbrain Widget
 *
 * Renders the Outbrain HTML
 *
 * @since 2015-10-12
 * @version 2015-10-12 Archana Mandhare PMCVIP-309
 *
 */
class Widget extends \WP_Widget {

	const widget_id = "pmc_outbrain_widget";    //unique widget ID
	const MOBILE_PREFIX = 'MB_';
	const DESKTOP_PREFIX = 'AR_';
	const NUMBER_OF_WIDGETS = 2;

	/**
	 * Widget Constructor
	 *
	 * @since 2015-10-12
	 * @version 2015-10-12 Archana Mandhare PMCVIP-309
	 *
	 */
	public function __construct() {
		// Instantiate the parent object
		parent::__construct( self::widget_id, __( 'PMC - Outbrain Widget', 'pmc-plugins' ), array(
			'description' => 'Render Outbrain HTML and enqueue Outbrain javascript',
		) );
	} // __construct

	/**
	 *
	 * Render the widget form in the admin
	 *
	 * @since 2015-10-12
	 * @version 2015-10-12 Archana Mandhare PMCVIP-309
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {

		// Set some default values
		$sidebar      = 0;
		$sidebar_name = $this->get_field_name( 'sidebar' );
		$sidebar_id   = $this->get_field_id( 'sidebar' );

		// Override the default value with one previously saved
		if ( isset( $instance['sidebar'] ) ) {
			$sidebar = intval( $instance['sidebar'] );
		}

		$template_path = PMC_OUTBRAIN_ROOT . '/templates/admin-ui.php';

		if ( ! file_exists( $template_path ) ) {
			return;
		}

		echo PMC::render_template( $template_path, array(
			'sidebar'      => $sidebar,
			'sidebar_name' => $sidebar_name,
			'sidebar_id'   => $sidebar_id,
		) );

		unset( $template_path, $sidebar, $sidebar_name, $sidebar_id );

	} // form

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @since 2015-10-12
	 * @version 2015-10-12 Archana Mandhare PMCVIP-309
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		// Store the updated widget sidebar value
		if ( ! empty( $new_instance['sidebar'] ) ) {
			$instance['sidebar'] = intval( $new_instance['sidebar'] );
		}

		// Return the new widget instance
		return $instance;
	} // update

	/**
	 * Outbrain HTML Widget
	 *
	 * This widget displays the HTML for the partner outbrain and enqueues its javascript
	 * file which the partner populates with sponsored content
	 *
	 * @since 2015-10-12
	 * @version 2015-10-12 Archana Mandhare PMCVIP-309
	 *
	 * @param  array $args The Arguments passed in from the sidebar (i.e. before widget markup)
	 * @param  array $instance Any data stored in the widget, i.e. sidebar true or false
	 *
	 * @return null
	 */
	public function widget( $args, $instance ) {

		// Set a default sidebar value
		$sidebar = 0;

		// Override the default post count with the option saved in the widget settings
		if ( isset( $instance['sidebar'] ) ) {
			if ( ! empty( $instance['sidebar'] ) ) {
				$sidebar = intval( $instance['sidebar'] );
			}
		}

		// Set a default template value
		$template = apply_filters( 'pmc_outbrain_data_ob_template', '' );

		if ( empty( $template ) ) {
			return;
		}

		wp_enqueue_script( 'pmc-async-outbrain-partner-js', 'https://widgets.outbrain.com/outbrain.js', array(), false, true ); // @codeCoverageIgnore

		$widget_ids = $this->_get_data_widget_id( $sidebar );

		if ( is_single() ) {
			$permalink = get_permalink( get_queried_object() );
		} else {
			$permalink = pmc_canonical( false );
		}

		$template_path = apply_filters( 'pmc_outbrain_data_ob_template_path', PMC_OUTBRAIN_ROOT . '/templates/outbrain-ui-permalink.php' );

		if ( ! file_exists( $template_path ) ) {
			return;
		}

		$html = PMC::render_template( $template_path, array(
			'widget_ids' => $widget_ids,
			'template'   => $template,
			'permalink'  => esc_url_raw( $permalink ),
		) );

		if ( ! empty( $html ) ) {
			echo wp_kses_post( $args['before_widget'] );
			echo $html;
			echo wp_kses_post( $args['after_widget'] );
		}

		unset( $html, $template, $widget_ids );

	} // widget

	/**
	 * Return the data-widget-id attribute based on the placement of the widget
	 * For example - mobile article or mobile gallery or desktop article or desktop gallery or sidebar
	 *
	 * Below values provided by partner outbrain :
	 * For sidebar it is SB_1 , article pages AR_1 and AR_2, gallery AR_3 and AR_4
	 * For Mobile articles MB_1 and MB_2 and Mobile galleries MB_3 and MB_4
	 *
	 * @since 2015-10-12
	 * @version 2015-10-12 Archana Mandhare PMCVIP-309
	 *
	 * @param  int $sidebar - whether it is placed in sidebar. If 0 false else true
	 *
	 * @return array - array containing the data-widget-id attributes required for HTML
	 */
	private function _get_data_widget_id( $sidebar ) {

		global $post;
		
		// If the widget needs to be placed in sidebar
		if ( ! empty( $sidebar ) ) {
			return array( 'SB_1' );
		}

		// If the widget needs to be rendered on mobile the Prefix id is MB_
		if ( PMC::is_mobile() ) {
			$prefix = self::MOBILE_PREFIX;
		} else {
			$prefix = self::DESKTOP_PREFIX;
		}

		$number_of_widgets = apply_filters( 'pmc_outbrain_post_widgets_count', self::NUMBER_OF_WIDGETS, $post );

		if ( 1 === $number_of_widgets ){
			$widget_ids = array( $prefix . '1' );
		} else {
			$widget_ids = array( $prefix . '1', $prefix . '2' );
		}


		// if gallery the widget has div ids 3 and 4 with the respective prefix for mobile or desktop
		if ( is_single() && 'pmc-gallery' === get_post_type( $post ) ) {

			if ( 1 === $number_of_widgets ){
				$widget_ids = array( $prefix . '3' );
			} else {
				$widget_ids = array( $prefix . '3', $prefix . '4' );
			}

		}

		return $widget_ids;
	}

}

//EOF
