<?php
/**
 * Class Dirt_Redirect
 *
 * Handler for the Dirt redirects.
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Dirt_Redirect
 */
class Dirt_Redirect {

	use Singleton;

	/**
	 * @codeCoverageIgnore Constructor to call main function.
	 * Class constructor.
	 */
	protected function __construct() {
		$data_file = get_stylesheet_directory() . '/inc/data/dirt-redirects.php';

		if ( ! file_exists( $data_file ) ) {
			return;
		}

		$dirt_redirects = require_once $data_file;

		$this->redirect( $dirt_redirects );
	}

	/**
	 * Redirect galleries from Dirt vertical to dirt.com.
	 *
	 * @param array $dirt_redirects Array of redirects.
	 */
	public function redirect( $dirt_redirects = array() ) {

		if ( empty( $dirt_redirects ) || ! is_array( $dirt_redirects ) ) {
			return;
		}

		// Redirection block for the site's 301 redirects.
		if ( function_exists( 'vip_substr_redirects' ) ) {
			vip_substr_redirects( $dirt_redirects, false );
		}
	}

}
