<?php
/*

This class implement a generic widget that allow any carousel data to be render as a widget
with a custom template provided by theme via a filter.  This is a generic widget that require
theme to provide a list of template via a filters for the widget to work.

Example:

add_filter( 'pmc_carousel_widget_templates', function ( $templates ) {
	$templates['templates/widget-featured-gallery'] = 'Feature Gallery Widget';
	return $templates;
} );

Where 'templates/widget-featured-gallery' is the template located at: <theme root>/templates/widget-featured-gallery.php

*/

class PMC_Carousel_Widget extends WP_Widget {
	const DEFAULT_LIMIT = 1;

	public function __construct() {
		parent::__construct( 'pmc-carousel-widget', 'PMC Carousel Widget', array(
			'description' => __( 'Render the carousel data with a widget template' )
		));
	}

	public function widget( $args, $instance ) {
		$widget = $this;

		// is widget setting meets all requirements?
		if ( empty( $instance['template'] )
			|| empty( $instance['module'] )
			|| empty( $instance['limit'] )
			|| empty( $instance['thumbsize'] )
			 ) {
			return;
		}

		// could be numeric term
		if ( is_numeric( $instance['module'] ) ) {
			$term = get_term( $instance['module'], PMC_Carousel::modules_taxonomy_name );

			// error, let's bail out
			if ( empty( $term ) || is_wp_error( $term ) ) {
				return;
			}

			$carousel_data = pmc_render_carousel( PMC_Carousel::modules_taxonomy_name, $term->slug , $instance['limit'], $instance['thumbsize'], array( 'exclude-author' => true ) );
		} else {
			$carousel_data = pmc_render_carousel( PMC_Carousel::modules_taxonomy_name, $instance['module'] , $instance['limit'], $instance['thumbsize'], array( 'exclude-author' => true ) );
		}

		// There is no data?
		if ( empty( $carousel_data ) ) {
			return;
		}

		// let's locate the template
		if ( ( 0 === validate_file( $instance['template'] ) ) && $template = locate_template( array( $instance['template'] . '.php' ) ) ) {
			// we don't want to load the template via wp, so we have to manually load it ourself.
			$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
			require $template;
		}

	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		if (isset($new_instance['title'])) {
			$instance['title'] = sanitize_text_field( $new_instance['title'] );
		}

		if (isset($new_instance['template']) && ( 0 === validate_file( $new_instance['template'] ) ) ) {
			$instance['template'] = sanitize_text_field( $new_instance['template'] );
		}

		if (isset($new_instance['module'])) {
			$instance['module'] = sanitize_title_with_dashes( $new_instance['module'] );
		}

		if (isset($new_instance['thumbsize'])) {
			$instance['thumbsize'] = sanitize_text_field( $new_instance['thumbsize'] );
		}

		if (isset($new_instance['limit']) && is_numeric($new_instance['limit'])) {
			$instance['limit'] = PMC::numeric_range( (int) $new_instance['limit'], 1, 5 );
		}
		return $instance;
	}

	public function form( $instance ) {
		$widget = $this;
		require __DIR__ . '/templates/pmc-carousel-widget-admin.php';
	}

}

add_action(
	'widgets_init',
	function () {
		register_widget( 'PMC_Carousel_Widget' );
	}
);

// EOF
