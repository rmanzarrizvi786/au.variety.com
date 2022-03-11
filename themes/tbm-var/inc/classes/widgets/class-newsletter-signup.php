<?php
/**
 * Newsletter Sign Up widget
 *
 * @package pmc-variety
 */

namespace Variety\Inc\Widgets;

class Newsletter_Signup extends \WP_Widget {
	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct() {
		parent::__construct(
			'newsletter-signup',
			__( 'Variety - Newsletter Signup', 'pmc-variety' ),
			array( 'description' => __( 'Displays a sign up form for the newsletter', 'pmc-variety' ) )
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
			sprintf( '%s/template-parts/widgets/newsletter-signup.php', untrailingslashit( CHILD_THEME_PATH ) ),
			[],
			true
		);
	}
}
