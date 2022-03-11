<?php
/**
 * Trending widget
 *
 * @package pmc-variety
 */

namespace Variety\Inc\Widgets;

class Trending extends \WP_Widget {
	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct() {
		parent::__construct(
			'trending',
			__( 'Variety - Trending', 'pmc-variety' ),
			array( 'description' => __( 'Displays the trending taxonomies menu', 'pmc-variety' ) )
		);
	}

	/**
	 * Render widget
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Widget instance.
	 *
	 * @throws \Exception
	 */
	public function widget( $args, $instance ) {
		\PMC::render_template(
			sprintf( '%s/template-parts/widgets/trending.php', untrailingslashit( CHILD_THEME_PATH ) ),
			[],
			true
		);
	}
}
