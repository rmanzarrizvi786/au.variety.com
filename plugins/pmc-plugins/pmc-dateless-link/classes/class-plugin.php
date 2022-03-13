<?php
namespace PMC\Dateless_Link;

use PMC\Dateless_Link\Permalink;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Main plugin class.
 */
class Plugin {

	use Singleton;

	/**
	 * Class initialization routine.
	 *
	 * @codeCoverageIgnore All the classes are initialized here, no need for testing.
	 */
	protected function __construct() {
		Permalink::get_instance();
	}
}
