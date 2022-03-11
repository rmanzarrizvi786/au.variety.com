<?php
namespace PMC\Core\Inc\Widgets;

/**
 * Newsletter widget
 */
class Newsletter extends \WP_Widget {

	use Traits\Templatize;

	/**
	 * Newsletter widget constructor.
	 */
	public function __construct() {
		parent::__construct( 'newsletter', __( 'Newsletter Signup', 'pmc-core' ), [
				'classname'   => 'newsletter-widget',
				'description' => __( 'Add the Newsletter widget.', 'pmc-core' ),
			] );
	}
}

//EOF
