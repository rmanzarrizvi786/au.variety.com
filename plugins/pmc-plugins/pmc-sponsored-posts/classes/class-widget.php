<?php
namespace PMC\Sponsored_Posts;

class Widget extends \WP_Widget {

	const ID = 'pmc_sponsored_posts_widget';

	public function __construct() {
		parent::__construct(
			self::ID,
			__( 'Sponsored Posts', 'pmc-sponsored-posts' ),
			[
				'description' => __( 'Displays scheduled sponsored content configured in Global Curation. Adding this new widget to the configurations should display the module.', 'pmc-sponsored-posts' ),
			]
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) { // phpcs:ignore
		/* translators: %s: Sponsor Name */
		$sponsor_text = (string) apply_filters( 'pmc_sponsored_posts_widget_text', __( 'In partnership with %s', 'pmc-sponsored-posts' ) );

		do_action(
			'pmc_sponsored_posts_placement',
			'widget',
			$sponsor_text
		);
	}

}
