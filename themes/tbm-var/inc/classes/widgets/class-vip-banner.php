<?php
/**
 * VIP Banner widget
 *
 * @package pmc-variety
 */

namespace Variety\Inc\Widgets;

class VIP_Banner extends \WP_Widget {
	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct() {
		parent::__construct(
			'vip-banner',
			__( 'Variety - VIP Banner', 'pmc-variety' ),
			array( 'description' => __( 'Displays a VIP banner', 'pmc-variety' ) )
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
			sprintf( '%s/template-parts/widgets/vip-banner.php', untrailingslashit( CHILD_THEME_PATH ) ),
			[],
			true
		);
	}
}
