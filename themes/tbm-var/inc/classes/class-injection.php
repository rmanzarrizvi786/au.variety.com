<?php
/**
 * Class Injection
 *
 * Handlers for the Injection functionality.
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;
use \Variety\Plugins\Config\PMC_Related_Link;

/**
 * Class Injection
 *
 * @since 2017.1.0
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Injection {

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct() {
		// Inject to posts.
		\PMC_Inject_Content::get_instance()->register_post_type( 'post' );

		// Remove core's filter.
		remove_filter( 'pmc_inject_content_paragraphs', [ \PMC\Core\Inc\Injection::get_instance(), 'inject' ] );
	}
}

Injection::get_instance();
