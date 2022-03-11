<?php
/**
 * Breaking News Alerts widget
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Inc\Widgets;

class Breaking_News_Alerts extends \WP_Widget {
	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct() {
		parent::__construct(
			'breaking-news-alerts',
			__( 'Variety - Breaking News Alerts', 'pmc-variety' ),
			array( 'description' => __( 'Displays a sign up form for breaking news alerts', 'pmc-variety' ) )
		);
	}

	/**
	 * @param array $args
	 * @param array $instance
	 *
	 * @throws \Exception
	 */
	public function widget( $args, $instance ) {
		\PMC::render_template(
			sprintf( '%s/template-parts/widgets/breaking-news-alerts.php', untrailingslashit( CHILD_THEME_PATH ) ),
			[],
			true
		);
	}
}
