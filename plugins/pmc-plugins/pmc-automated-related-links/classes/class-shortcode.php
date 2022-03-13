<?php
/**
 * To remove shortcode for related link.
 *
 * @author Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @package pmc-automated-related-links
 */

namespace PMC\Automated_Related_Links;

use PMC\Global_Functions\Traits\Singleton;

class Shortcode {

	use Singleton;

	/**
	 * Shortcode constructor.
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'register_shortcodes' ), 11 );
	}

	/**
	 * Register Shortcodes
	 *
	 * Loads with priority 11 so that it removes the
	 * pmc-related shortcode after it is registered in pmc-core.
	 *
	 * @action init, 11
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		remove_shortcode( 'pmc-related-link' );
		add_shortcode( 'pmc-related-link', '__return_empty_string' );
	}

}
